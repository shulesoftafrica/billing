<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Organization;
use App\Models\OrganizationPaymentGatewayIntegration;
use App\Models\Merchant;
use App\Models\ControlNumber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    // EcoBank API Configuration
    protected $username = 'ETZSHULESOFT';
    protected $password = '$2a$10$jdNZI4uiE86yRhcFNrBenOo0nBQji9zqy9IVa.roj0ST5EhlE4sVe';
    protected $labId = 'KmiqL3yCLf1V68oRQrIv';
    protected $baseUrl = 'https://payservice.ecobank.com';
    protected $origin = 'https://payservice.ecobank.com/PayPortal';
    protected $callBackUrl = 'https://billing.shulesoft.africa/api/ecobank/notification';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate required parameters
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Fetch related records
            $organization = Organization::findOrFail($request->organization_id);
            $product = Product::with('pricePlans')->findOrFail($request->product_id);
            $customer = Customer::findOrFail($request->customer_id);

            // Create invoice
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => 'draft',
                'description' => 'Invoice for ' . $product->name,
                'subtotal' => 0,
                'tax_total' => 0,
                'total' => 0,
                'due_date' => Carbon::now()->addDays(30),
                'issued_at' => Carbon::now(),
            ]);

            // Create invoice items with default values
            $pricePlan = $product->pricePlans()->first();
            $invoiceItem = InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'subscription_id' => null, // No subscription for this flow
                'price_plan_id' => $pricePlan ? $pricePlan->id : null,
                'quantity' => 1,
                'unit_price' => $pricePlan ? $pricePlan->amount : 0,
                'total' => $pricePlan ? $pricePlan->amount : 0,
            ]);

            // Get all organization integrated gateways
            $organizationGateways = OrganizationPaymentGatewayIntegration::with(['paymentGateway', 'merchants'])
                ->where('organization_id', $organization->id)
                ->get();

            $paymentGateways = [];

            // Loop through all gateways
            foreach ($organizationGateways as $orgGateway) {
                $gatewayData = [
                    'id' => $orgGateway->id,
                    'payment_gateway_id' => $orgGateway->payment_gateway_id,
                    'gateway_name' => $orgGateway->paymentGateway->name,
                    'status' => $orgGateway->status,
                ];

                // Check if gateway is Universal Control Number
                if (strtolower($orgGateway->paymentGateway->name) === 'universal control number') {
                    // Fetch merchant record
                    $merchant = $orgGateway->merchants()->first();

                    if ($merchant) {
                        // Create control number via EcoBank API
                        $controlNumberData = $this->createControlNumber($merchant, $product, $customer, $orgGateway);

                        if ($controlNumberData['success']) {
                            $gatewayData['references'] = $controlNumberData['control_number'];
                        } else {
                            $gatewayData['references'] = [
                                'error' => $controlNumberData['message']
                            ];
                        }
                    } else {
                        $gatewayData['references'] = [
                            'error' => 'Merchant not found for this gateway'
                        ];
                    }
                } else {
                    $gatewayData['references'] = null;
                }

                $paymentGateways[] = $gatewayData;
            }

            DB::commit();

            // Return response
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => [
                    'invoice' => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'customer_id' => $invoice->customer_id,
                        'status' => $invoice->status,
                        'description' => $invoice->description,
                        'subtotal' => $invoice->subtotal,
                        'tax_total' => $invoice->tax_total,
                        'total' => $invoice->total,
                        'due_date' => $invoice->due_date,
                        'issued_at' => $invoice->issued_at,
                    ],
                    'payment_gateways' => $paymentGateways,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Invoice creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create control number via EcoBank API
     */
    private function createControlNumber($merchant, $product, $customer, $orgGateway)
    {
        try {
            // Get EcoBank token
            $token = $this->createEcobankToken();
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Failed to get EcoBank token'
                ];
            }

            // Prepare request data
            $requestId = "TERMINAL_" .$customer->id.$product->id;
            $postData = [
                "requestId" => $requestId,
                "affiliateCode" => "ETZ",
                "merchantCode" => $merchant->merchant_code,
                "terminalMobileNo" => $customer->phone ?? "0765406008",
                "terminalName" => $customer->name,
                "terminalEmail" => $customer->email ?? "support@shulesoft.africa",
                "productCode" => $product->id . time(),
                "dynamicQr" => "Y",
                "callBackUrl" => $this->callBackUrl,
            ];

            // Generate secure hash
            $payloadPart = implode('', array_values($postData));
            $secureHash = $this->generateSecureHash($payloadPart);
            
            if (!$secureHash) {
                return [
                    'success' => false,
                    'message' => 'Failed to generate secure hash'
                ];
            }

            $postData['secureHash'] = $secureHash;
            $url = $this->baseUrl . '/corporateapi/merchant/createaddQr';

            // Make API request
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Accept: application/json',
                'Origin: ' . $this->origin,
            ]);

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('EcoBank API cURL Error: ' . $curlError);
                return [
                    'success' => false,
                    'message' => 'API request failed: ' . $curlError
                ];
            }

            $responseData = json_decode($response, true);
            Log::info('EcoBank Control Number Response: ' . $response);

            // Process successful response
            if (isset($responseData['response_code']) && $responseData['response_code'] === 200) {
                $content = $responseData['response_content'];
                
                // Insert control number record
                $controlNumber = ControlNumber::create([
                    'customer_id' => $customer->id,
                    'reference' => $content['terminalId'],
                    'organization_payment_gateway_integration_id' => $orgGateway->id,
                    'product_id' => $product->id,
                    'type_id' => 9,
                    'header_response' => $content['headerResponse'] ?? null,
                    'qr_code' => $content['qrBase64String'] ?? null,
                    'notified' => 1,
                ]);

                return [
                    'success' => true,
                    'control_number' => [
                        'id' => $controlNumber->id,
                        'reference' => $controlNumber->reference,
                        'qr_code' => $controlNumber->qr_code,
                        'terminal_id' => $content['terminalId'],
                        'created_at' => $controlNumber->created_at,
                    ]
                ];
            } else {
                $errorMessage = $responseData['response_message'] ?? 'Unknown error occurred';
                Log::error('EcoBank API Error: ' . $errorMessage);
                
                return [
                    'success' => false,
                    'message' => 'Failed to create control number: ' . $errorMessage
                ];
            }

        } catch (\Exception $e) {
            Log::error('Control number creation exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create EcoBank token
     */
    private function createEcobankToken()
    {
        try {
            $data = [
                'userId' => $this->username,
                'password' => $this->password
            ];
            
            $url = $this->baseUrl . '/corporateapi/user/token';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'Origin: ' . $this->origin,
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $responseData = json_decode($response, true);
            
            if (isset($responseData['token'])) {
                return $responseData['token'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('EcoBank token creation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate secure hash for EcoBank API
     */
    private function generateSecureHash($payload)
    {
        try {
            $data = $payload . $this->labId;
            $binaryHash = hash('sha512', $data, true);

            $hexString = '';
            foreach (str_split($binaryHash) as $char) {
                $hex = dechex(ord($char));
                if (strlen($hex) < 2) {
                    $hex = '0' . $hex;
                }
                $hexString .= $hex;
            }

            return $hexString;
        } catch (\Exception $e) {
            Log::error('Secure hash generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = Carbon::now()->format('Ymd');
        
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . $date . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastSequence = (int) substr($lastInvoice->invoice_number, -4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $prefix . $date . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

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
use App\Models\Subscription;
use App\Models\PricePlan;
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
    protected $callBackUrl = 'https://safariapi.safaribook.africa/api/ecobank/notification';

    /**
     * Display a listing of the resource.
     * Request parameters:
     * - organization_id: Filter invoices by organization
     * - product_id: Filter invoices by product (returns invoices that contain this product)
     * - per_page: Number of results per page (default: 15)
     * - page: Page number (default: 1)
     */
    public function index(Request $request)
    {
        try {
            $organizationId = $request->input('organization_id');
            $productId = $request->input('product_id');
            $perPage = $request->input('per_page', 15);

            // Validate at least one filter is provided
            if (!$organizationId && !$productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either organization_id or product_id must be provided'
                ], 422);
            }

            // Validate organization exists if provided
            if ($organizationId && !Organization::find($organizationId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organization not found'
                ], 404);
            }

            // Validate product exists if provided
            if ($productId && !Product::find($productId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $query = Invoice::with(['customer', 'invoiceItems.pricePlan.product']);

            // Filter by organization
            if ($organizationId) {
                $query->whereHas('customer', function ($q) use ($organizationId) {
                    $q->where('organization_id', $organizationId);
                });
            }

            // Filter by product
            if ($productId) {
                $query->whereHas('invoiceItems.pricePlan.product', function ($q) use ($productId) {
                    $q->where('products.id', $productId);
                });
            }

            // Paginate results
            $invoices = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Format response
            $data = $invoices->map(function ($invoice) {
                return $this->formatInvoiceDetailResponse($invoice);
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoices retrieved successfully',
                'data' => $data,
                'pagination' => [
                    'total' => $invoices->total(),
                    'per_page' => $invoices->perPage(),
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Invoice retrieval failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Invoice retrieval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate required parameters
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,id',
            'customer' => 'required|array',
            'customer.name' => 'required|string',
            'customer.email' => 'required|email',
            'customer.phone' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.price_plan_id' => 'required|integer|exists:price_plans,id',
            'products.*.amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'currency' => 'nullable|string|max:5',
            'status' => 'nullable|string|in:draft,issued,paid,cancelled',
            'due_date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $organizationId = $request->organization_id;
            $customerData = $request->customer;
            $productsData = $request->products;
            $description = $request->description ?? 'Invoice for products';
            $currency = $request->currency ?? 'TZS';
            $status = $request->status ?? 'issued';
            $dueDate = $request->due_date ?? null;

            // Step 2: Check if customer exists in the organization by phone or email
            $customer = Customer::where('organization_id', $organizationId)
                ->where(function ($query) use ($customerData) {
                    $query->where('email', $customerData['email'])
                        ->orWhere('phone', $customerData['phone']);
                })
                ->first();

            // If customer doesn't exist, create a new one
            if (!$customer) {
                $customer = Customer::create([
                    'organization_id' => $organizationId,
                    'name' => $customerData['name'],
                    'email' => $customerData['email'],
                    'phone' => $customerData['phone'],
                    'status' => 'active',
                ]);
            }

            // Step 3 & 4: Process products and determine product types
            $subscriptions = [];
            $invoiceItems = [];
            $totalAmount = 0;

            foreach ($productsData as $productData) {
                // Get price plan and its product
                $pricePlan = PricePlan::with('product')->findOrFail($productData['price_plan_id']);
                $product = $pricePlan->product;

                // Validate product belongs to organization
                if ($product->organization_id != $organizationId) {
                    throw new \Exception('Product does not belong to the specified organization');
                }

                $shouldCreateInvoiceItem = true;
                $subscription = null;

                // Check if product is not a one-time product (product_type_id != 1)
                if ($product->product_type_id != 1) {
                    // Check if subscription already exists with pending status
                    $existingSubscription = Subscription::where('customer_id', $customer->id)
                        ->where('price_plan_id', $pricePlan->id)
                        ->where('status', 'pending')
                        ->first();

                    if ($existingSubscription) {
                        $invoice = Invoice::where('customer_id', $customer->id)
                            ->whereIn('id', InvoiceItem::where('subscription_id', $existingSubscription->id)->pluck('invoice_id'))
                            ->first();
                        // Subscription already exists - skip invoice item creation for this product
                        $shouldCreateInvoiceItem = false;
                    } else {
                        // Create subscription record for recurring products
                        $subscription = Subscription::create([
                            'customer_id' => $customer->id,
                            'price_plan_id' => $pricePlan->id,
                            'status' => 'pending',
                            'start_date' => null,
                            'next_billing_date' => null,
                        ]);
                        $subscriptions[] = [
                            'id' => $subscription->id,
                            'customer_id' => $subscription->customer_id,
                            'price_plan_id' => $subscription->price_plan_id,
                            'status' => $subscription->status,
                            'start_date' => $subscription->start_date,
                            'next_billing_date' => $subscription->next_billing_date,
                            'end_date' => $subscription->end_date,
                            'created_at' => $subscription->created_at,
                        ];
                    }
                }

                // Prepare invoice item data only if subscription doesn't already exist
                if ($shouldCreateInvoiceItem) {
                    $invoiceItems[] = [
                        'price_plan_id' => $pricePlan->id,
                        'subscription_id' => ($product->product_type_id != 1) ? ($subscription->id ?? null) : null,
                        'quantity' => 1,
                        'unit_price' => $productData['amount'],
                        'total' => $productData['amount'],
                    ];

                    $totalAmount += $productData['amount'];
                }
            }

            // If no invoice items to create, return response without creating invoice
            if (empty($invoiceItems)) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'All products have pending subscriptions - no invoice created',
                    'data' => [
                        'invoice' => null,
                        'subscriptions' => $subscriptions,
                        'payment_gateways' => [],
                    ]
                ], 201);
            }

            if (!$shouldCreateInvoiceItem) {
                 DB::commit();
                 $data =$this->formatInvoiceDetailResponse($invoice);

                return response()->json([
                    'success' => true,
                    'message' => 'All products have pending subscriptions - no invoice created',
                    'data' => $data
                ], 200);
            } else {
               
                // Create invoice
                $invoice = Invoice::create([
                    'customer_id' => $customer->id,
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'status' => $status,
                    'description' => $description,
                    'subtotal' => $totalAmount,
                    'tax_total' => 0,
                    'total' => $totalAmount,
                    'due_date' => $dueDate,
                    'issued_at' => Carbon::now(),
                ]);

                // Create invoice items only if invoice was newly created
                foreach ($invoiceItems as $itemData) {
                    $itemData['invoice_id'] = $invoice->id;
                    InvoiceItem::create($itemData);
                }
            }

            // Get all organization integrated gateways
            $organizationGateways = OrganizationPaymentGatewayIntegration::with(['paymentGateway', 'merchants'])
                ->where('organization_id', $organizationId)
                ->get();

            $paymentGateways = [];

            // Get all products associated with the price plans
            $pricePlanIds = collect($productsData)->pluck('price_plan_id')->unique();
            $products = Product::whereIn(
                'id',
                PricePlan::whereIn('id', $pricePlanIds)->pluck('product_id')
            )->get();

            if ($products->isEmpty()) {
                throw new \Exception('No products found for the provided price plans');
            }

            // Loop through products to all gateways
            foreach ($products as $product) {
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

                        if (!$merchant) {
                            throw new \Exception('Merchant not found for Universal Control Number gateway');
                        }
                        // Create control number for each product
                        $gatewayData['references'] = [];

                        $controlNumberData = $this->createControlNumber($merchant, $product, $customer, $orgGateway);

                        if (!$controlNumberData['success']) {
                            throw new \Exception('Control number creation failed: ' . $controlNumberData['message']);
                        }
                        $gatewayData['references'] = $controlNumberData['control_number']['reference'];
                    } else {
                        $gatewayData['references'] = [];
                    }
                    $paymentGateways[$product->id][] = $gatewayData;
                }
            }

            DB::commit();
            $data = $this->formatInvoiceDetailResponse($invoice);

            // Return response
                    return response()->json([
                        'success' => true,
                        'message' => 'Invoice created successfully',
                        'data' => $data,
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

            // Check if control number already exists for this product, customer, and gateway
            $existingControlNumber = ControlNumber::where('product_id', $product->id)
                ->where('customer_id', $customer->id)
                ->where('organization_payment_gateway_integration_id', $orgGateway->id)
                ->first();

            if ($existingControlNumber) {
                return [
                    'success' => true,
                    'control_number' => [
                        'id' => $existingControlNumber->id,
                        'reference' => $existingControlNumber->reference,
                        'metadata' => $existingControlNumber->metadata,
                        'terminal_id' => $existingControlNumber->reference,
                        'created_at' => $existingControlNumber->created_at
                    ],
                    'message' => 'Existing control number returned'
                ];
            }

            // // Get EcoBank token
            // $token = $this->createEcobankToken();
            // if (!$token) {
            //     return [
            //         'success' => false,
            //         'message' => 'Failed to get token'
            //     ];
            // }

            // // Prepare request data
            // $requestId = "TERMINAL_" . $customer->id . $product->id;
            // $postData = [
            //     "requestId" => $requestId,
            //     "affiliateCode" => "ETZ",
            //     "merchantCode" => $merchant->merchant_code,
            //     "terminalMobileNo" => $customer->phone ?? "0765406008",
            //     "terminalName" => $customer->name,
            //     "terminalEmail" => $customer->email ?? "support@shulesoft.africa",
            //     "productCode" => $product->id . time(),
            //     "dynamicQr" => "Y",
            //     "callBackUrl" => $this->callBackUrl,
            // ];

            // // Generate secure hash
            // $payloadPart = implode('', array_values($postData));
            // $secureHash = $this->generateSecureHash($payloadPart);

            // if (!$secureHash) {
            //     return [
            //         'success' => false,
            //         'message' => 'Failed to generate secure hash'
            //     ];
            // }

            // $postData['secureHash'] = $secureHash;
            // $url = $this->baseUrl . '/corporateapi/merchant/createaddQr';

            // // Make API request
            // $ch = curl_init($url);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_POST, true);
            // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            // curl_setopt($ch, CURLOPT_HTTPHEADER, [
            //     'Authorization: Bearer ' . $token,
            //     'Content-Type: application/json',
            //     'Accept: application/json',
            //     'Origin: ' . $this->origin,
            // ]);

            // $response = curl_exec($ch);
            // $curlError = curl_error($ch);

            // if ($curlError) {
            //     Log::error('EcoBank API cURL Error: ' . $curlError);
            //     return [
            //         'success' => false,
            //         'message' => 'API request failed: ' . $curlError
            //     ];
            // }
            // // $response = json_encode([
            // //     "response_code" => 200,
            // //     "response_message" => "Success",
            // //     "response_content" => [
            // //         "terminalId" => "00012345",
            // //         "headerResponse" => "Control number generated successfully",
            // //         "qrBase64String" => "iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAYAAAC1n1..."
            // //     ]
            // // ]);


            // $responseData = json_decode($response, true);
            // Log::info('EcoBank Control Number Response: ' . $response);

            // Process successful response
            $responseData = [
                "response_code" => 200,
                "response_message" => "Success",
                "response_content" => [
                    "terminalId" => rand(100000, 9999999999),
                    "headerResponse" => "Control number generated successfully",
                    "qrBase64String" => "iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAYAAAC1n1..."
                ]
            ];
            if (isset($responseData['response_code']) && $responseData['response_code'] === 200) {
                $content = $responseData['response_content'];
                // Insert control number record
                $data = [
                    'customer_id' => $customer->id,
                    'reference' => $content['terminalId'],
                    'organization_payment_gateway_integration_id' => $orgGateway->id,
                    'product_id' => $product->id,
                    'metadata' => json_encode(['qr_code' => $content['qrBase64String'] ?? null, 'header_response' => $content['headerResponse'] ?? null]),
                ];
                $controlNumber = ControlNumber::create($data);

                return [
                    'success' => true,
                    'control_number' => [
                        'id' => $controlNumber->id,
                        'reference' => $controlNumber->reference,
                        'metadata' => $controlNumber->metadata,
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
     * Returns detailed information about a single invoice including items and subscriptions
     */
    public function show(string $id)
    {
        try {
            $invoice = Invoice::with(['customer', 'invoiceItems.pricePlan.product', 'invoiceItems.subscription'])
                ->find($id);

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice details retrieved successfully',
                'data' => $this->formatInvoiceDetailResponse($invoice)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Invoice detail retrieval failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Invoice detail retrieval failed: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Format invoice response for list view
     */
    private function formatInvoiceResponse($invoice)
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'customer_id' => $invoice->customer_id,
            'customer_name' => $invoice->customer->name,
            'customer_email' => $invoice->customer->email,
            'status' => $invoice->status,
            'description' => $invoice->description,
            'subtotal' => $invoice->subtotal,
            'tax_total' => $invoice->tax_total,
            'total' => $invoice->total,
            'due_date' => $invoice->due_date,
            'issued_at' => $invoice->issued_at,
            'items_count' => $invoice->invoiceItems->count(),
            'price_plans' => $invoice->invoiceItems->map(function ($item) {
                return [
                    'id' => $item->pricePlan->id,
                    'name' => $item->pricePlan->name,
                    'subscription_type' => $item->pricePlan->subscription_type,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->total,
                    'product_id' => $item->pricePlan->product_id,
                    'product_name' => $item->pricePlan->product->name,
                ];
            })->unique('id')->values(),
            'created_at' => $invoice->created_at,
            'updated_at' => $invoice->updated_at,
        ];
    }

    /**
     * Format invoice response for detail view
     */
    private function formatInvoiceDetailResponse($invoice)
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'description' => $invoice->description,
            'subtotal' => $invoice->subtotal,
            'tax_total' => $invoice->tax_total,
            'total' => $invoice->total,
            'due_date' => $invoice->due_date,
            'issued_at' => $invoice->issued_at,
            'created_at' => $invoice->created_at,
            'updated_at' => $invoice->updated_at,
            'customer' => [
                'id' => $invoice->customer->id,
                'name' => $invoice->customer->name,
                'email' => $invoice->customer->email,
                'phone' => $invoice->customer->phone,
                'organization_id' => $invoice->customer->organization_id,
            ],
            
            'price_plans' => $invoice->invoiceItems->map(function ($item) use ($invoice) {
                $product = $item->pricePlan->product;
                $customerId = $invoice->customer->id;       
                // Fetch control numbers for this customer and product to get payment gateways
                $controlNumbers = ControlNumber::where('customer_id', $customerId)
                    ->where('product_id', $product->id)
                    ->with('organizationPaymentGatewayIntegration.paymentGateway')
                    ->get();
                
                // Map control numbers to payment gateways
                $paymentGateways = $controlNumbers->map(function ($controlNumber) {
                    $integration = $controlNumber->organizationPaymentGatewayIntegration;
                    return [
                        'id' => $integration->id,
                        'payment_gateway_id' => $integration->payment_gateway_id,
                        'gateway_name' => $integration->paymentGateway->name,
                        'status' => $integration->status,
                        'references' => $controlNumber->reference,
                    ];
                })->values();
                
                return [
                    'id' => $item->pricePlan->id,
                    'name' => $item->pricePlan->name,
                    'subscription_type' => $item->pricePlan->subscription_type,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->total,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'payment_gateways' => $paymentGateways->toArray()
                ];
            })->unique('id')->values(),
            'subscriptions' => $invoice->invoiceItems
                ->filter(function ($item) {
                    return $item->subscription !== null;
                })
                ->map(function ($item) {
                    $subscription = $item->subscription;
                    return [
                        'id' => $subscription->id,
                        'product_id' => $subscription->pricePlan->product_id,
                        'product_name' => $subscription->pricePlan->product->name,
                        'price_plan_id' => $subscription->pricePlan->id,
                        'price_plan_name' =>$subscription->pricePlan->name,
                        'subscription_type' => $subscription->pricePlan->subscription_type,
                        'price_plan_id' => $subscription->price_plan_id,
                        'customer_id' => $subscription->customer_id,
                        'status' => $subscription->status,
                        'start_date' => $subscription->start_date,
                        'end_date' => $subscription->end_date,
                        'next_billing_date' => $subscription->next_billing_date,
                        'created_at' => $subscription->created_at,
                        'updated_at' => $subscription->updated_at,
                    ];
                })
                ->unique('id')
                ->values(),
        ];
    }
}

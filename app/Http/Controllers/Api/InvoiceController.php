<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Organization;
use App\Models\OrganizationPaymentGatewayIntegration;
use App\Models\ControlNumber;
use App\Models\TaxRate;
use App\Models\Subscription;
use App\Models\PricePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\SubscriptionService;
use App\Services\FlutterwaveService;
use App\Services\Stripe\PaymentIntentService;
use App\Services\Stripe\StripeAmountHelper;
use App\Traits\ValidatePhoneNumber;
use App\Jobs\Payments\CreateEcobankReferenceJob;
use App\Jobs\Payments\CreateFlutterwaveReferenceJob;
use App\Jobs\Payments\CreateStripeReferenceJob;
use App\Models\AdvancePayment;
use App\Models\InvoicePayment;
use App\Models\Payment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Stripe\Exception\ApiErrorException;

use function Pest\Laravel\json;

class InvoiceController extends Controller
{
    use ValidatePhoneNumber;

    // EcoBank API Configuration
    protected $username = 'ETZSHULESOFT';
    protected $password = '$2a$10$jdNZI4uiE86yRhcFNrBenOo0nBQji9zqy9IVa.roj0ST5EhlE4sVe';
    protected $labId = 'KmiqL3yCLf1V68oRQrIv';
    protected $baseUrl = 'https://payservice.ecobank.com';
    protected $origin = 'https://payservice.ecobank.com/PayPortal';
    protected $callBackUrl = 'https://api.safaribank.africa/api/v1/webhooks/ecobank/notification';

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
            $customerId = $request->input('customer_id') ?? null;
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

            $query = Invoice::with([
                'customer',
                'payments',
                'invoiceTaxes.taxRate',
                'invoiceItems.pricePlan.product',
                'invoiceItems.subscription.pricePlan.product',
            ]);

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

            // Filter by product
            if ($customerId) {
                $query->whereHas('invoiceItems.pricePlan.product', function ($q) use ($customerId) {
                    $q->where('invoices.customer_id', $customerId);
                });
            }


            // Paginate results
            $invoices = $query->where('status', '!=', 'cancelled')->orderBy('created_at', 'desc')->paginate($perPage);

            $controlNumbersMap = $this->buildControlNumbersMap($invoices->getCollection());

            // Format response
            $data = $invoices->getCollection()->map(function ($invoice) use ($controlNumbersMap) {
                return $this->formatInvoiceDetailResponse($invoice, $controlNumbersMap);
            })->values();

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
            'tax_rate_ids' => 'nullable|array',
            'tax_rate_ids.*' => 'integer|distinct|exists:tax_rates,id',
            'description' => 'nullable|string',
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'status' => 'nullable|string|in:draft,issued,paid,cancelled',
            'date' => 'nullable|date_format:Y-m-d',
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
            $currency = strtoupper((string) $request->currency);
            $status = $request->status ?? 'issued';
            $date = $request->date ?? now()->format('Y-m-d');
            // Default due_date to today for wallet top-ups and prepaid invoices, or 7 days from now for others
            $dueDate = $request->due_date ?? now()->format('Y-m-d');
            $requestedTaxRateIds = collect($request->input('tax_rate_ids', []))
                ->filter(fn($id) => $id !== null)
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

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
            $subscriptionData = [];
            $totalAmount = 0;
            $oneTimeInvoiceItems = [];
            $existingInvoiceToReturn = null;

            foreach ($productsData as $productData) {
                // Get price plan and its product
                $pricePlan = PricePlan::with('product')->findOrFail($productData['price_plan_id']);
                $product = $pricePlan->product;

                // Validate product belongs to organization
                if ($product->organization_id != $organizationId) {
                    throw new \Exception('Product does not belong to the specified organization');
                }

                $subscription = null;

                // Check if product is not a one-time product (product_type_id != 1) and not a wallet product (product_type_id != 3)
                // Wallet products (top-ups) should always create new invoices regardless of pending subscriptions
                if ($product->product_type_id != 1 && $product->product_type_id != 3) {
                    // Check if ANY subscription already exists with pending status for this product
                    $existingSubscription = Subscription::where('customer_id', $customer->id)
                        ->whereHas('pricePlan', function($q) use ($product) {
                            $q->where('product_id', $product->id);
                        })
                        ->where('status', 'pending')
                        ->with('pricePlan')
                        ->first();

                    if ($existingSubscription) {
                        // Check if it's the SAME price plan or DIFFERENT
                        if ($existingSubscription->price_plan_id == $pricePlan->id) {
                            // SAME plan - return existing invoice
                            $existingInvoice = Invoice::where('customer_id', $customer->id)
                                ->whereIn('id', InvoiceItem::where('subscription_id', $existingSubscription->id)->pluck('invoice_id'))
                                ->where('status', '!=', 'cancelled')
                                ->first();
                            
                            if ($existingInvoice) {
                                $existingInvoiceToReturn = $existingInvoice;
                                Log::info('Found existing pending subscription with same plan', [
                                    'customer_id' => $customer->id,
                                    'price_plan_id' => $pricePlan->id,
                                    'subscription_id' => $existingSubscription->id,
                                    'invoice_id' => $existingInvoice->id
                                ]);
                                continue; // Skip to next product
                            }
                        } else {
                            // DIFFERENT plan (upgrade/downgrade) - cancel old subscription
                            Log::info('Cancelling old pending subscription for upgrade/downgrade', [
                                'customer_id' => $customer->id,
                                'old_price_plan_id' => $existingSubscription->price_plan_id,
                                'new_price_plan_id' => $pricePlan->id,
                                'subscription_id' => $existingSubscription->id
                            ]);
                            
                            // Cancel the old subscription
                            $existingSubscription->update(['status' => 'cancelled']);
                            
                            // Cancel the old invoice if it exists
                            $oldInvoice = Invoice::where('customer_id', $customer->id)
                                ->whereIn('id', InvoiceItem::where('subscription_id', $existingSubscription->id)->pluck('invoice_id'))
                                ->where('status', '!=', 'cancelled')
                                ->first();
                            
                            if ($oldInvoice) {
                                $oldInvoice->update(['status' => 'cancelled']);
                                Log::info('Cancelled old invoice', ['invoice_id' => $oldInvoice->id]);
                            }
                            
                            // Create new subscription for the new plan
                            $subscription = Subscription::create([
                                'customer_id' => $customer->id,
                                'price_plan_id' => $pricePlan->id,
                                'status' => 'pending',
                                'start_date' => null,
                                'next_billing_date' => null,
                            ]);
                            $subscriptionData[$subscription->id] = [
                                'id' => $subscription->id,
                                'amount' => $productData['amount'],
                            ];
                        }
                    } else {
                        // No existing subscription - create new one
                        $subscription = Subscription::create([
                            'customer_id' => $customer->id,
                            'price_plan_id' => $pricePlan->id,
                            'status' => 'pending',
                            'start_date' => null,
                            'next_billing_date' => null,
                        ]);
                        $subscriptionData[$subscription->id] = [
                            'id' => $subscription->id,
                            'amount' => $productData['amount'],
                        ];
                    }
                }

                // Prepare invoice item data (skip only if we're returning existing invoice)
                if (!$existingInvoiceToReturn || $existingInvoiceToReturn->customer_id != $customer->id) {
                    $invoiceItems[] = [
                        'price_plan_id' => $pricePlan->id,
                        // Only recurring products (product_type_id == 2) should have subscription_id
                        // One-time (1) and wallet (3) products don't need subscriptions
                        'subscription_id' => ($product->product_type_id == 2) ? ($subscription->id ?? null) : null,
                        'quantity' => 1,
                        'unit_price' => $productData['amount'],
                        'total' => $productData['amount'],
                    ];
                    // filter onetime product and wallet product  
                    if ($product->product_type_id == 1 || $product->product_type_id == 3) {
                        $oneTimeInvoiceItems[$pricePlan->id] = [
                            'amount' =>  $productData['amount']
                        ];
                    }

                    $totalAmount += $productData['amount'];
                }
            }

            // If we found an existing invoice for the same pending subscription, return it
            if ($existingInvoiceToReturn && empty($invoiceItems)) {
                DB::commit();
                $existingInvoiceToReturn->load([
                    'customer',
                    'payments',
                    'invoiceTaxes.taxRate',
                    'invoiceItems.pricePlan.product',
                    'invoiceItems.subscription.pricePlan.product',
                ]);
                $controlNumbersMap = $this->buildControlNumbersMap(collect([$existingInvoiceToReturn]));
                $data = $this->formatInvoiceDetailResponse($existingInvoiceToReturn, $controlNumbersMap);

                return response()->json([
                    'success' => true,
                    'message' => 'Pending subscription already exists - returning existing invoice',
                    'data' => $data
                ], 200);
            }

            // Create new invoice for new subscriptions or upgrades/downgrades
            $taxRates = $this->resolveActiveTaxRates($requestedTaxRateIds);
            $taxBreakdown = $this->calculateTaxBreakdown($totalAmount, $taxRates);
            $taxTotal = collect($taxBreakdown)->sum('amount');
            $grandTotal = round($totalAmount + $taxTotal, 2);

            // Create invoice
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'currency' => $currency,
                'status' => $status,
                'description' => $description,
                'subtotal' => $totalAmount,
                'tax_total' => $taxTotal,
                'total' => $grandTotal,
                'date' => $date,
                'due_date' => $dueDate,
                'issued_at' => Carbon::now(),
            ]);

            // Create invoice items only if invoice was newly created
            foreach ($invoiceItems as $itemData) {
                $itemData['invoice_id'] = $invoice->id;
                InvoiceItem::create($itemData);
            }

            if (!empty($taxBreakdown)) {
                $invoice->invoiceTaxes()->createMany(
                    collect($taxBreakdown)->map(function ($taxRow) {
                        return [
                            'tax_rate_id' => $taxRow['tax_rate_id'],
                            'amount' => $taxRow['amount'],
                        ];
                    })->all()
                );
            }

            $subscriptionService = new SubscriptionService();
            if (!empty($subscriptionData)) {
                $subscriptions = Subscription::whereIn('id', collect($subscriptionData)->pluck('id'))->get();
                if (!$subscriptions->isEmpty()) {
                    foreach ($subscriptions as $subscription) {
                        $subscriptionService->enableSubscription($invoice->id, $subscription, $subscriptionData[$subscription->id]['amount']);
                    }
                }
            }
            if (!empty($oneTimeInvoiceItems)) {
                foreach ($oneTimeInvoiceItems as $key => $oneTimeInvoiceItem) {
                    $subscriptionService->clearOnTimeProductPayment($invoice->id, $customer->id, $oneTimeInvoiceItem['amount']);
                }
            }

            // Get all organization integrated gateways
            $organizationGateways = OrganizationPaymentGatewayIntegration::with(['paymentGateway', 'merchants'])
                ->where('organization_id', $organizationId)
                ->get();

            // Get all products associated with the price plans
            $pricePlanIds = collect($productsData)->pluck('price_plan_id')->unique();
            $products = Product::whereIn(
                'id',
                PricePlan::whereIn('id', $pricePlanIds)->pluck('product_id')
            )->get();

            if ($products->isEmpty()) {
                throw new \Exception('No products found for the provided price plans');
            }

            $gatewayJobs = [];

            foreach ($products as $product) {
                foreach ($organizationGateways as $orgGateway) {
                    $gatewayName = strtolower(trim((string) $orgGateway->paymentGateway->name));

                    if (
                        $gatewayName === 'universal control number'
                        || $gatewayName === 'flutterwave'
                        || $gatewayName === 'stripe'
                    ) {
                        $gatewayJobs[] = [
                            'product_id' => $product->id,
                            'organization_gateway_id' => $orgGateway->id,
                            'gateway_name' => $gatewayName,
                        ];
                    }
                }
            }

            $totalGatewayJobs = count($gatewayJobs);

            DB::commit();

            // Process payment gateway references synchronously to include URLs in response
            if ($totalGatewayJobs > 0) {
                foreach ($gatewayJobs as $jobData) {
                    $successUrl = $request->input('success_url') ?: config('app.url') . '/payment/callback';
                    $product = Product::find($jobData['product_id']);
                    $orgGateway = OrganizationPaymentGatewayIntegration::with(['paymentGateway', 'merchants'])
                        ->find($jobData['organization_gateway_id']);

                    if (!$product || !$orgGateway) {
                        Log::warning('Skipping payment reference due to missing product/gateway', [
                            'invoice_id' => $invoice->id,
                            'product_id' => $jobData['product_id'],
                            'organization_gateway_id' => $jobData['organization_gateway_id'],
                        ]);
                        continue;
                    }

                    $mockRequest = Request::create('/', 'POST', [
                        'success_url' => $successUrl,
                        'tx_ref' => $request->input('tx_ref'),
                        'redirect_url' => $request->input('redirect_url'),
                        'customizations' => $request->input('customizations', []),
                        'meta' => $request->input('meta', []),
                    ]);

                    if ($jobData['gateway_name'] === 'universal control number') {
                        $this->createControlNumber(
                            $orgGateway->merchants->first(),
                            $product,
                            $customer,
                            $orgGateway
                        );
                        continue;
                    }

                    if ($jobData['gateway_name'] === 'flutterwave') {
                        $this->createFlutterWaveReference($invoice, $product, $customer, $mockRequest, $orgGateway);
                        continue;
                    }

                    if ($jobData['gateway_name'] === 'stripe') {
                        $this->createStripeReference($invoice, $product, $customer, $mockRequest, $orgGateway);
                    }
                }
            }

            // Reload invoice with all relationships including newly created payment references
            $invoice->load([
                'customer',
                'payments',
                'invoiceTaxes.taxRate',
                'invoiceItems.pricePlan.product',
                'invoiceItems.subscription.pricePlan.product',
            ]);
            $controlNumbersMap = $this->buildControlNumbersMap(collect([$invoice]));
            $data = $this->formatInvoiceDetailResponse($invoice, $controlNumbersMap);

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
    public function createControlNumber($merchant, $product, $customer, $orgGateway)
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

            // Get EcoBank token
            $token = $this->createEcobankToken();
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Failed to get token'
                ];
            }

            // Prepare request data
            $requestId = "TERMINAL_" . $customer->id . $product->id;
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

            if ($curlError) {
                Log::error('EcoBank API cURL Error: ' . $curlError);
                return [
                    'success' => false,
                    'message' => 'API request failed: ' . $curlError
                ];
            }
            // $response = json_encode([
            //     "response_code" => 200,
            //     "response_message" => "Success",
            //     "response_content" => [
            //         "terminalId" => "00012345",
            //         "headerResponse" => "Control number generated successfully",
            //         "qrBase64String" => "iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAYAAAC1n1..."
            //     ]
            // ]);


            $responseData = json_decode($response, true);
            // Log::info('EcoBank Control Number Response: ' . $response);

            // Process successful response
            // $responseData = [
            //     "response_code" => 200,
            //     "response_message" => "Success",
            //     "response_content" => [
            //         "terminalId" => rand(100000, 9999999999),
            //         "headerResponse" => "Control number generated successfully",
            //         "qrBase64String" => "iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAYAAAC1n1..."
            //     ]
            // ];
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

    private function resolveActiveTaxRates(array $taxRateIds)
    {
        if (empty($taxRateIds)) {
            return collect();
        }

        $taxRates = TaxRate::whereIn('id', $taxRateIds)
            ->where('active', true)
            ->get();

        if ($taxRates->count() !== count($taxRateIds)) {
            throw new \InvalidArgumentException('One or more selected tax rates are invalid or inactive');
        }

        return $taxRates;
    }

    private function calculateTaxBreakdown(float $subtotal, $taxRates): array
    {
        if ($taxRates->isEmpty()) {
            return [];
        }

        return $taxRates->map(function ($taxRate) use ($subtotal) {
            $amount = round($subtotal * ((float) $taxRate->rate / 100), 2);

            return [
                'tax_rate_id' => $taxRate->id,
                'amount' => $amount,
            ];
        })->values()->all();
    }

    private function buildControlNumbersMap($invoices): array
    {
        $invoiceCollection = collect($invoices);

        if ($invoiceCollection->isEmpty()) {
            return [];
        }

        $customerIds = $invoiceCollection->pluck('customer_id')->filter()->unique()->values();
        $productIds = $invoiceCollection
            ->flatMap(fn($invoice) => $invoice->invoiceItems->map(fn($item) => $item->pricePlan?->product_id))
            ->filter()
            ->unique()
            ->values();

        if ($customerIds->isEmpty() || $productIds->isEmpty()) {
            return [];
        }

        $controlNumbers = ControlNumber::with('organizationPaymentGatewayIntegration.paymentGateway')
            ->whereIn('customer_id', $customerIds)
            ->whereIn('product_id', $productIds)
            ->get();

        $map = [];
        foreach ($controlNumbers as $controlNumber) {
            $key = $this->controlNumbersMapKey($controlNumber->customer_id, $controlNumber->product_id);
            $map[$key] ??= collect();
            $map[$key]->push($controlNumber);
        }

        return $map;
    }

    private function controlNumbersMapKey($customerId, $productId): string
    {
        return $customerId . ':' . $productId;
    }

    /**
     * Display the specified resource.
     * Returns detailed information about a single invoice including items and subscriptions
     */
    public function show(string $id)
    {
        try {
            $invoice = Invoice::with([
                'customer',
                'payments',
                'invoiceTaxes.taxRate',
                'invoiceItems.pricePlan.product',
                'invoiceItems.subscription.pricePlan.product',
            ])
                ->find($id);

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            $controlNumbersMap = $this->buildControlNumbersMap(collect([$invoice]));

            return response()->json([
                'success' => true,
                'message' => 'Invoice details retrieved successfully',
                'data' => $this->formatInvoiceDetailResponse($invoice, $controlNumbersMap)
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
        return $this->cancel($id);
    }

    /**
     * Cancel an invoice and reverse related pending operations.
     */
    public function cancel(string $id)
    {
        try {
            $invoice = $this->cancelInvoice((int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled successfully',
                'data' => $invoice,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Invoice cancellation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Invoice cancellation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function cancelInvoice(int $invoiceId): array
    {
        return DB::transaction(function () use ($invoiceId) {
            $invoice = Invoice::with([
                'customer',
                'payments',
                'invoiceTaxes.taxRate',
                'invoiceItems.pricePlan.product',
                'invoiceItems.subscription.pricePlan.product',
            ])->lockForUpdate()->find($invoiceId);

            if (!$invoice) {
                throw new ModelNotFoundException('Invoice not found');
            }

            if ($invoice->status === 'cancelled') {
                throw new \InvalidArgumentException('Invoice is already cancelled');
            }

            $hasActiveSubscriptions = $invoice->invoiceItems->contains(function ($item) {
                $subscription = $item->subscription;
                return $subscription && $subscription->status === 'active';
            });

            if ($hasActiveSubscriptions) {
                throw new \InvalidArgumentException('Cannot cancel invoice with active subscriptions');
            }

            foreach ($invoice->invoiceItems as $item) {
                $subscription = $item->subscription;

                if ($subscription && in_array($subscription->status, ['pending', 'partial'], true)) {
                    $subscription->update(['status' => 'cancelled']);
                }
            }

            $fallbackProductId = $invoice->invoiceItems->first()?->pricePlan?->product_id;

            $invoicePayments = InvoicePayment::where('invoice_id', $invoice->id)
                ->lockForUpdate()
                ->get();

            foreach ($invoicePayments as $invoicePayment) {
                $payment = Payment::lockForUpdate()->find($invoicePayment->payment_id);

                if ($payment) {
                    $fullPaymentAllocated = ((float) $payment->amount === (float) $invoicePayment->amount);

                    if ($fullPaymentAllocated) {
                        $payment->update(['status' => 'pending']);
                    } elseif ($fallbackProductId) {
                        AdvancePayment::create([
                            'payment_id' => $payment->id,
                            'customer_id' => $invoice->customer_id,
                            'product_id' => $fallbackProductId,
                            'reminder' => (int) round((float) $invoicePayment->amount),
                            'amount' => (float) $invoicePayment->amount,
                        ]);
                    }
                }

                $invoicePayment->delete();
            }

            $invoice->update(['status' => 'cancelled']);

            $invoice->refresh()->load([
                'customer',
                'payments',
                'invoiceTaxes.taxRate',
                'invoiceItems.pricePlan.product',
                'invoiceItems.subscription.pricePlan.product',
            ]);

            $controlNumbersMap = $this->buildControlNumbersMap(collect([$invoice]));

            return $this->formatInvoiceDetailResponse($invoice, $controlNumbersMap);
        });
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
            'currency' => $invoice->currency,
            'description' => $invoice->description,
            'subtotal' => $invoice->subtotal,
            'tax_total' => $invoice->tax_total,
            'total' => $invoice->total,
            'date' => $invoice->date,
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
    private function formatInvoiceDetailResponse($invoice, array $controlNumbersMap = [])
    {
        $grandTotal = $invoice->total;
        $paid = $invoice->payments->sum('pivot.amount');
        $balance = $grandTotal - $paid;

        $taxBreakdown = $invoice->invoiceTaxes->map(function ($invoiceTax) {
            $taxRate = $invoiceTax->taxRate;

            return [
                'invoice_tax_id' => $invoiceTax->id,
                'tax_rate_id' => $invoiceTax->tax_rate_id,
                'name' => $taxRate?->name,
                'country' => $taxRate?->country,
                'rate' => $taxRate?->rate,
                'amount' => $invoiceTax->amount,
            ];
        })->values();

        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'currency' => $invoice->currency,
            'description' => $invoice->description,
            'subtotal' => $invoice->subtotal,
            'tax_breakdown' => $taxBreakdown,
            'tax_total' => $invoice->tax_total,
            'grand_total' => $grandTotal,
            'invoiced_amount' => $grandTotal,
            'paid_amount' => $paid,
            'outstanding_amount' => $balance,
            'date' => $invoice->date,
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

            'price_plans' => $invoice->invoiceItems
                ->filter(function ($item) {
                    // Skip items with null pricePlan or null product
                    return $item->pricePlan !== null && $item->pricePlan->product !== null;
                })
                ->map(function ($item) use ($invoice, $controlNumbersMap) {
                $product = $item->pricePlan->product;
                $customerId = $invoice->customer->id;
                $mapKey = $this->controlNumbersMapKey($customerId, $product->id);
                $controlNumbers = $controlNumbersMap[$mapKey] ?? collect();

                // Map control numbers to payment gateways
                $paymentGateways = $controlNumbers
                    ->filter(function ($controlNumber) use ($invoice) {
                        return $this->shouldIncludeControlNumberForInvoice($controlNumber, $invoice->id);
                    })
                    ->map(function ($controlNumber) use ($invoice) {
                        $integration = $controlNumber->organizationPaymentGatewayIntegration;

                        if (!$integration || !$integration->paymentGateway) {
                            return null;
                        }

                        $gatewayName = (string) $integration->paymentGateway->name;

                        $gatewayData = [
                            'id' => $integration->id,
                            'payment_gateway_id' => $integration->payment_gateway_id,
                            'gateway_name' => $gatewayName,
                            'status' => $integration->status,
                            'references' => $controlNumber->reference,
                        ];

                        if (strtolower(trim($gatewayName)) === 'stripe') {
                            $gatewayData['client_secret'] = $this->extractClientSecretFromControlNumberMetadata($controlNumber->metadata);
                            $gatewayData['payment_link'] = url('/billing/pay/' . $invoice->id);
                        }

                        if (strtolower(trim($gatewayName)) === 'flutterwave') {
                            $gatewayData['payment_link'] = $this->extractPaymentLinkFromControlNumberMetadata($controlNumber->metadata);
                        }

                        return $gatewayData;
                    })->filter()->values();

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
                    // Skip items without subscription, pricePlan, or product
                    return $item->subscription !== null 
                        && $item->subscription->pricePlan !== null
                        && $item->subscription->pricePlan->product !== null;
                })
                ->map(function ($item) {
                    $subscription = $item->subscription;
                    return [
                        'id' => $subscription->id,
                        'product_id' => $subscription->pricePlan->product_id,
                        'product_name' => $subscription->pricePlan->product->name,
                        'price_plan_id' => $subscription->pricePlan->id,
                        'price_plan_name' => $subscription->pricePlan->name,
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

    private function shouldIncludeControlNumberForInvoice($controlNumber, $invoiceId): bool
    {
        $integration = $controlNumber->organizationPaymentGatewayIntegration;

        if (!$integration || !$integration->paymentGateway) {
            return false;
        }

        $gatewayName = strtolower(trim((string) $integration->paymentGateway->name));

        // Keep existing UCN mapping behavior (customer + product based)
        if ($gatewayName === 'universal control number') {
            return true;
        }

        // Flutterwave and Stripe are invoice-scoped via metadata
        if ($gatewayName === 'flutterwave' || $gatewayName === 'stripe') {
            $metadataInvoiceId = $this->extractInvoiceIdFromControlNumberMetadata($controlNumber->metadata);

            if ($metadataInvoiceId === null) {
                return false;
            }

            return (int) $metadataInvoiceId === (int) $invoiceId;
        }

        return true;
    }

    private function extractInvoiceIdFromControlNumberMetadata($metadata): ?int
    {
        $metadata = $this->normalizeControlNumberMetadata($metadata);

        // Supports new structure: {"meta": {"invoice_id": ...}}
        // and legacy structure: {"invoice_id": ...}
        $invoiceId = data_get($metadata, 'meta.invoice_id', data_get($metadata, 'invoice_id'));

        return is_numeric($invoiceId) ? (int) $invoiceId : null;
    }

    private function extractClientSecretFromControlNumberMetadata($metadata): ?string
    {
        $metadata = $this->normalizeControlNumberMetadata($metadata);

        $clientSecret = data_get(
            $metadata,
            'client_secret',
            data_get($metadata, 'meta.client_secret', data_get($metadata, 'payment_intent.client_secret'))
        );

        return is_string($clientSecret) && trim($clientSecret) !== '' ? $clientSecret : null;
    }

    private function extractPaymentLinkFromControlNumberMetadata($metadata): ?string
    {
        $metadata = $this->normalizeControlNumberMetadata($metadata);

        $paymentLink = data_get(
            $metadata,
            'payment_link',
            data_get($metadata, 'data.link', data_get($metadata, 'flutterwave_response.data.link'))
        );

        return is_string($paymentLink) && trim($paymentLink) !== '' ? $paymentLink : null;
    }

    private function normalizeControlNumberMetadata($metadata): array
    {
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($metadata) ? $metadata : [];
    }
    public function getByProduct(Request $request, ?int $product_id = null)
    {
        $resolvedProductId = $product_id ?? $request->input('product_id');

        $validator = Validator::make([
            'product_id' => $resolvedProductId,
        ], [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $invoices = Invoice::with([
                'customer',
                'payments',
                'invoiceTaxes.taxRate',
                'invoiceItems.pricePlan.product',
                'invoiceItems.subscription.pricePlan.product',
            ])
                ->whereHas('invoiceItems.pricePlan', function ($query) use ($resolvedProductId) {
                    $query->where('product_id', $resolvedProductId);
                })
                ->where('status', '!=', 'cancelled')
                ->orderBy('created_at', 'desc')
                ->get();

            $controlNumbersMap = $this->buildControlNumbersMap($invoices);

            $data = $invoices->map(function ($invoice) use ($controlNumbersMap) {
                return $this->formatInvoiceDetailResponse($invoice, $controlNumbersMap);
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoices retrieved successfully',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get invoices by product failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve invoices',
            ], 500);
        }
    }

    /**
     * Return invoices created for the provided subscription ids.
     * Request body: { subscription_ids: [1,2,3] }
     */
    public function getBySubscriptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_ids' => 'required|array|min:1',
            'subscription_ids.*' => 'integer|exists:subscriptions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subscriptionIds = $request->input('subscription_ids');

            // Get invoice ids from invoice_items using subscription_id
            $invoiceIds = InvoiceItem::whereIn('subscription_id', $subscriptionIds)
                ->pluck('invoice_id')
                ->unique()
                ->values()
                ->toArray();

            if (empty($invoiceIds)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No invoices found for provided subscription ids',
                    'data' => []
                ], 200);
            }

            // Eager load relations used by formatInvoiceDetailResponse
            $invoices = Invoice::with([
                'customer',
                'payments',
                'invoiceTaxes.taxRate',
                'invoiceItems.pricePlan.product',
                'invoiceItems.subscription.pricePlan.product',
            ])
                ->whereIn('id', $invoiceIds)
                ->where('status', '!=', 'cancelled')
                ->get();

            $controlNumbersMap = $this->buildControlNumbersMap($invoices);

            $data = $invoices->map(function ($invoice) use ($controlNumbersMap) {
                return $this->formatInvoiceDetailResponse($invoice, $controlNumbersMap);
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoices retrieved successfully',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get invoices by subscriptions failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve invoices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return payment gateways/references for a specific invoice grouped by price plan/product.
     */
    public function getPaymentGatewaysByInvoice(string $invoiceId)
    {
        try {
            $invoice = Invoice::with([
                'customer',
                'payments',
                'invoiceTaxes.taxRate',
                'invoiceItems.pricePlan.product',
                'invoiceItems.subscription.pricePlan.product',
            ])
                ->where('id', $invoiceId)
                ->where('status', '!=', 'cancelled')
                ->first();

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            $controlNumbersMap = $this->buildControlNumbersMap(collect([$invoice]));
            $formattedInvoice = $this->formatInvoiceDetailResponse($invoice, $controlNumbersMap);

            return response()->json([
                'success' => true,
                'message' => 'Invoice payment gateways retrieved successfully',
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'price_plans' => $formattedInvoice['price_plans'],
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Invoice payment gateways retrieval failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve invoice payment gateways: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createFlutterWaveReference($invoice, $product, $customer, $request, $orgGateway)
    {
        try {
            $existingControlNumber = $this->findExistingControlNumberForInvoiceGateway(
                $invoice->id,
                $product->id,
                $customer->id,
                $orgGateway->id
            );

            if ($existingControlNumber) {
                return [
                    'success' => true,
                    'control_number' => [
                        'id' => $existingControlNumber->id,
                        'reference' => $existingControlNumber->reference,
                        'metadata' => $existingControlNumber->metadata,
                        'created_at' => $existingControlNumber->created_at,
                    ],
                    'message' => 'Existing flutterwave reference returned',
                ];
            }

            $flutterwaveService = new FlutterwaveService();

            if (!$flutterwaveService->isActive()) {
                Log::warning('Flutterwave gateway not active', [
                    'invoice_id' => $invoice->id,
                ]);
                return [
                    'success' => false,
                    'message' => 'Flutterwave gateway not configured or inactive',
                ];
            }

            $txRef = (string) ($request->input('tx_ref')
                ?: ($invoice->invoice_number . '-' . $product->id . '-' . Str::lower(Str::random(10))));
            $redirectUrl = (string) ($request->input('redirect_url')
                ?: $request->input('success_url')
                ?: (config('app.url') . '/payment/callback'));

            $meta = [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'local_customer_id' => $customer->id,
                'organization_id' => $customer->organization_id,
                'product_id' => $product->id,
            ];

            $payload = [
                'amount' => (float) $invoice->total,
                'tx_ref' => $txRef,
                'currency' => (string) ($invoice->currency ?: 'NGN'),
                'redirect_url' => $redirectUrl,
                'customer' => [
                    'email' => (string) ($customer->email ?: 'customer@example.com'),
                    'phone_number' => (string) ($customer->phone ?: ''),
                    'name' => (string) ($customer->name ?: 'Customer'),
                ],
                'customizations' => [
                    'title' => (string) ($request->input('customizations.title') ?: ('Invoice ' . $invoice->invoice_number)),
                ],
                'meta' => array_merge($meta, (array) $request->input('meta', [])),
            ];

            if ($request->filled('customizations.logo')) {
                $payload['customizations']['logo'] = (string) $request->input('customizations.logo');
            }

            if ($request->filled('configuration.session_duration')) {
                $payload['configuration'] = [
                    'session_duration' => (int) $request->input('configuration.session_duration'),
                ];
            }

            foreach (['max_retry_attempt', 'payment_plan', 'payment_options', 'link_expiration'] as $optionalField) {
                if ($request->filled($optionalField)) {
                    $payload[$optionalField] = $request->input($optionalField);
                }
            }

            $flutterwaveResponse = $flutterwaveService->createHostedPaymentLink($payload);
            $paymentLink = (string) $flutterwaveResponse['link'];

            $controlNumber = ControlNumber::create([
                'customer_id' => $customer->id,
                'reference' => $txRef,
                'organization_payment_gateway_integration_id' => $orgGateway->id,
                'product_id' => $product->id,
                'metadata' => json_encode([
                    'payment_link' => $paymentLink,
                    'reference' => $txRef,
                    'status' => $flutterwaveResponse['status'] ?? null,
                    'message' => $flutterwaveResponse['message'] ?? null,
                    'data' => $flutterwaveResponse['data'] ?? null,
                    'instructions' => 'Click the payment link to pay via card, mobile money, or bank transfer',
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_id' => $customer->id,
                    'organization_id' => $customer->organization_id,
                    'product_id' => $product->id,
                    'meta' => [
                        'invoice_id' => $invoice->id,
                    ],
                ]),
            ]);

            Log::info('Flutterwave payment link generated successfully', [
                'invoice_id' => $invoice->id,
                'payment_link' => $paymentLink,
                'tx_ref' => $txRef,
                'status' => $flutterwaveResponse['status'] ?? null,
            ]);

            return [
                'success' => true,
                'control_number' => [
                    'id' => $controlNumber->id,
                    'reference' => $controlNumber->reference,
                    'metadata' => $controlNumber->metadata,
                    'created_at' => $controlNumber->created_at,
                ],
                'message' => 'Flutterwave reference stored successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Flutterwave integration error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while generating payment link',
            ];
        }
    }

    public function createStripeReference($invoice, $product, $customer, $request, $orgGateway)
    {
        try {
            $existingControlNumber = $this->findExistingControlNumberForInvoiceGateway(
                $invoice->id,
                $product->id,
                $customer->id,
                $orgGateway->id
            );

            if ($existingControlNumber) {
                return [
                    'success' => true,
                    'control_number' => [
                        'id' => $existingControlNumber->id,
                        'reference' => $existingControlNumber->reference,
                        'metadata' => $existingControlNumber->metadata,
                        'created_at' => $existingControlNumber->created_at,
                    ],
                    'message' => 'Existing Stripe reference returned',
                ];
            }

            $paymentIntentService = app(PaymentIntentService::class);


            $stripeAmount = StripeAmountHelper::toStripeAmount(round($invoice->total), (string) ($invoice->currency ?: 'TZS'));
            if ($stripeAmount <= 0 || (StripeAmountHelper::countDigits($stripeAmount)) > 8) {
                Log::warning('Calculated Stripe amount is invalid', [
                    'invoice_id' => $invoice->id,
                    'calculated_amount' => $stripeAmount,
                    'currency' => $invoice->currency,
                ]);
                return [
                    'success' => false,
                    'message' => 'Invalid invoice total for Stripe PaymentIntent',
                ];
            }

            $currency = strtolower((string) ($invoice->currency ?: 'TZS'));
            $orderId = $invoice->invoice_number . '-' . $product->id;
            $intent = $paymentIntentService->create([
                'amount' => $stripeAmount,
                'currency' => $currency,
                'description' => 'Invoice ' . $invoice->invoice_number . ' payment',
                'receipt_email' => $customer->email,
                'metadata' => [
                    'order_id' => $orderId,
                    'user_id' => (string) $customer->id,
                    'invoice_id' => (string) $invoice->id,
                    'invoice_number' => (string) $invoice->invoice_number,
                    'organization_id' => (string) $customer->organization_id,
                    'product_id' => (string) $product->id,
                ],
            ]);

            $reference = (string) ($intent->id ?: $orderId);

            $controlNumber = ControlNumber::create([
                'customer_id' => $customer->id,
                'reference' => $reference,
                'organization_payment_gateway_integration_id' => $orgGateway->id,
                'product_id' => $product->id,
                'metadata' => json_encode([
                    'payment_intent_id' => $intent->id,
                    'client_secret' => $intent->client_secret,
                    'status' => $intent->status,
                    'amount' => $intent->amount,
                    'currency' => $intent->currency,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_id' => $customer->id,
                    'organization_id' => $customer->organization_id,
                    'product_id' => $product->id,
                    'meta' => [
                        'invoice_id' => $invoice->id,
                    ],
                ]),
            ]);

            return [
                'success' => true,
                'control_number' => [
                    'id' => $controlNumber->id,
                    'reference' => $controlNumber->reference,
                    'metadata' => $controlNumber->metadata,
                    'created_at' => $controlNumber->created_at,
                ],
                'message' => 'Stripe PaymentIntent reference stored successfully',
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe integration error while creating reference', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Stripe error while generating payment reference',
            ];
        } catch (\Throwable $e) {
            Log::error('Unexpected Stripe reference error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while generating Stripe payment reference',
            ];
        }
    }

    private function generateRandomCardTestPayload(): array
    {
        $month = str_pad((string) random_int(1, 12), 2, '0', STR_PAD_LEFT);
        $year = (string) random_int((int) Carbon::now()->format('Y') + 1, (int) Carbon::now()->format('Y') + 8);
        $cardNumber = '4' . str_pad((string) random_int(0, 999999999999999), 15, '0', STR_PAD_LEFT);
        $cvv = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);

        return [
            'card_number' => '5505580003319001',
            'expiry_month' => '02',
            'expiry_year' => '2030',
            'cvv' => 761,
            'cof' => [
                'enabled' => false,
            ],
        ];
    }

    private function findExistingControlNumberForInvoiceGateway(
        int $invoiceId,
        int $productId,
        int $customerId,
        int $organizationGatewayId
    ): ?ControlNumber {
        return ControlNumber::where('product_id', $productId)
            ->where('customer_id', $customerId)
            ->where('organization_payment_gateway_integration_id', $organizationGatewayId)
            ->get()
            ->first(function ($controlNumber) use ($invoiceId) {
                return $this->extractInvoiceIdFromControlNumberMetadata($controlNumber->metadata) === $invoiceId;
            });
    }

    /**
     * Upgrade subscription to a higher-tier plan
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upgradeSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|integer|exists:subscriptions,id',
            'new_price_plan_id' => 'required|integer|exists:price_plans,id',
            'payment_gateway' => 'nullable|string|in:flutterwave,control_number,both',
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $gatewayConfig = [
                'payment_gateway' => $request->input('payment_gateway', 'both'),
                'success_url' => $request->input('success_url'),
                'cancel_url' => $request->input('cancel_url'),
            ];

            $subscriptionService = new SubscriptionService();
            $invoice = $subscriptionService->upgradeSubscription(
                $request->input('subscription_id'),
                $request->input('new_price_plan_id'),
                $gatewayConfig
            );

            // Get subscription with updated plan
            $subscription = Subscription::with(['customer', 'pricePlan'])
                ->find($request->input('subscription_id'));

            // Process payment gateway references synchronously
            $successUrl = $request->input('success_url') ?: config('app.url') . '/payment/callback';
            $cancelUrl = $request->input('cancel_url') ?: config('app.url') . '/payment/cancel';

            // Get organization gateways for the invoice's product
            $invoiceItem = $invoice->invoiceItems->first();
            if ($invoiceItem && $invoiceItem->pricePlan) {
                $product = $invoiceItem->pricePlan->product;
                $customer = $invoice->customer;

                $organizationGateways = OrganizationPaymentGatewayIntegration::with('paymentGateway')
                    ->where('organization_id', $customer->organization_id)
                    ->where('status', 'active')
                    ->get();

                $mockRequest = Request::create('/', 'POST', [
                    'success_url' => $successUrl,
                    'redirect_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                    'customizations' => $request->input('customizations', []),
                    'meta' => $request->input('meta', []),
                ]);

                foreach ($organizationGateways as $orgGateway) {
                    $gatewayName = strtolower(trim((string) $orgGateway->paymentGateway->name));

                    if ($gatewayName === 'universal control number') {
                        $this->createControlNumber(
                            $orgGateway->merchants->first(),
                            $product,
                            $customer,
                            $orgGateway
                        );
                    } elseif ($gatewayName === 'flutterwave') {
                        $this->createFlutterWaveReference($invoice, $product, $customer, $mockRequest, $orgGateway);
                    } elseif ($gatewayName === 'stripe') {
                        $this->createStripeReference($invoice, $product, $customer, $mockRequest, $orgGateway);
                    }
                }
            }

            // Reload invoice with all relationships including newly created payment references
            $invoice->load([
                'customer',
                'payments',
                'invoiceTaxes.taxRate',
                'invoiceItems.pricePlan.product',
                'invoiceItems.subscription.pricePlan.product',
            ]);

            // Build control numbers map for payment details
            $controlNumbersMap = $this->buildControlNumbersMap(collect([$invoice]));

            // Format invoice with payment details
            $invoiceData = $this->formatInvoiceDetailResponse($invoice, $controlNumbersMap);

            return response()->json([
                'success' => true,
                'message' => 'Subscription upgraded successfully',
                'data' => [
                    'invoice' => $invoiceData,
                    'subscription' => [
                        'id' => $subscription->id,
                        'status' => $subscription->status,
                        'previous_plan_id' => $subscription->previous_plan_id,
                        'current_plan' => [
                            'id' => $subscription->pricePlan->id,
                            'name' => $subscription->pricePlan->name,
                            'amount' => $subscription->pricePlan->amount,
                            'billing_interval' => $subscription->pricePlan->billing_interval,
                        ],
                        'next_billing_date' => $subscription->next_billing_date?->toDateString(),
                    ],
                    'proration' => [
                        'amount_charged' => $invoice->total,
                        'credit_applied' => $invoice->proration_credit,
                        'description' => 'Prorated for remaining billing cycle',
                    ],
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription or price plan not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Subscription upgrade failed', [
                'subscription_id' => $request->input('subscription_id'),
                'new_plan_id' => $request->input('new_price_plan_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upgrade subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Downgrade subscription to a lower-tier plan
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function downgradeSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|integer|exists:subscriptions,id',
            'new_price_plan_id' => 'required|integer|exists:price_plans,id',
            'apply_credit' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $applyCredit = $request->input('apply_credit', true);

            $subscriptionService = new SubscriptionService();
            $result = $subscriptionService->downgradeSubscription(
                $request->input('subscription_id'),
                $request->input('new_price_plan_id'),
                $applyCredit
            );

            $subscription = $result['subscription'];

            // Get available payment gateways for this organization
            $customer = $subscription->customer;
            $product = $subscription->pricePlan->product;

            $organizationGateways = OrganizationPaymentGatewayIntegration::with('paymentGateway')
                ->where('organization_id', $customer->organization_id)
                ->where('status', 'active')
                ->get();

            $paymentGateways = $organizationGateways->map(function ($orgGateway) {
                $gatewayName = $orgGateway->paymentGateway->name ?? 'Unknown';

                return [
                    'id' => $orgGateway->id,
                    'payment_gateway_id' => $orgGateway->payment_gateway_id,
                    'gateway_name' => $gatewayName,
                    'status' => $orgGateway->status,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'subscription' => [
                        'id' => $subscription->id,
                        'status' => $subscription->status,
                        'previous_plan_id' => $subscription->previous_plan_id,
                        'current_plan' => [
                            'id' => $subscription->pricePlan->id,
                            'name' => $subscription->pricePlan->name,
                            'amount' => $subscription->pricePlan->amount,
                            'billing_interval' => $subscription->pricePlan->billing_interval,
                        ],
                        'next_billing_date' => $subscription->next_billing_date?->toDateString(),
                    ],
                    'credit' => [
                        'credit_amount' => $result['credit_details']['credit_amount'],
                        'credit_applied' => $result['credit_applied'],
                        'days_remaining' => $result['credit_details']['days_remaining'],
                        'description' => 'Credit from unused portion of higher plan',
                    ],
                    'payment_details' => [
                        'available_gateways' => $paymentGateways,
                        'note' => 'No payment required for downgrade. These payment methods will be available for your next billing cycle on ' . $subscription->next_billing_date?->toDateString(),
                    ],
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription or price plan not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Subscription downgrade failed', [
                'subscription_id' => $request->input('subscription_id'),
                'new_plan_id' => $request->input('new_price_plan_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to downgrade subscription: ' . $e->getMessage(),
            ], 500);
        }
    }
}

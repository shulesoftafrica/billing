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

use App\Models\User;
use App\Services\FlutterwaveService;
use App\Services\SubscriptionService;

use App\Models\TaxRate;
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
    protected $callBackUrl = 'https://billing.shulesoft.africa/api/ecobank/notification';

    /**
     * Display a listing of the resource.
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
        // Validate required parameters - multi-product support with taxes and payment gateway
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,id',
            'customer' => 'required|array',
            'customer.name' => 'required|string',
            'customer.email' => 'required|email',
            'customer.phone' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.price_plan_id' => 'nullable|integer|exists:price_plans,id',
            'products.*.product_code' => 'nullable|string',
            'products.*.product_id' => 'nullable|integer|exists:products,id',
            'products.*.amount' => 'required|numeric|min:0',
            'tax_rate_ids' => 'nullable|array',
            'tax_rate_ids.*' => 'integer|distinct|exists:tax_rates,id',
            'description' => 'nullable|string',
            'currency' => 'nullable|string|max:5',
            'status' => 'nullable|string|in:draft,issued,paid,cancelled',
            'date' => 'nullable|date_format:Y-m-d',
            'due_date' => 'nullable|date_format:Y-m-d',
            'payment_gateway' => 'nullable|string|in:control_number,flutterwave,both',
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
        ]);

        // Custom validation: Each product must have exactly one identifier
        $validator->after(function ($validator) use ($request) {
            $products = $request->products ?? [];
            foreach ($products as $index => $product) {
                $hasPrice = !empty($product['price_plan_id']);
                $hasCode = !empty($product['product_code']);
                $hasId = !empty($product['product_id']);
                
                $count = ($hasPrice ? 1 : 0) + ($hasCode ? 1 : 0) + ($hasId ? 1 : 0);
                
                if ($count === 0) {
                    $validator->errors()->add(
                        "products.{$index}",
                        'Each product must have either price_plan_id, product_code, or product_id'
                    );
                } elseif ($count > 1) {
                    $validator->errors()->add(
                        "products.{$index}",
                        'Each product must have only one identifier (price_plan_id, product_code, or product_id)'
                    );
                }
            }
        });

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
            $date = $request->date ?? null;
            $dueDate = $request->due_date ?? null;
            $requestedTaxRateIds = collect($request->input('tax_rate_ids', []))
                ->filter(fn($id) => $id !== null)
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            // Check if customer exists in the organization by phone or email
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

            // Process products and determine product types
            $subscriptions = [];
            $invoiceItems = [];
            $subscriptionData = [];
            $totalAmount = 0;
            $oneTimeInvoiceItems = [];

            foreach ($productsData as $index => $productData) {
                // Flexible product lookup: support price_plan_id, product_code, or product_id
                $pricePlan = null;
                $product = null;

                if (!empty($productData['price_plan_id'])) {
                    // Direct price plan lookup
                    $pricePlan = PricePlan::with('product')->find($productData['price_plan_id']);
                    if (!$pricePlan) {
                        throw new \Exception("Price plan not found with ID: {$productData['price_plan_id']}");
                    }
                    $product = $pricePlan->product;
                } elseif (!empty($productData['product_code'])) {
                    // Product code lookup
                    $product = Product::with('pricePlans')
                        ->where('product_code', $productData['product_code'])
                        ->where('organization_id', $organizationId)
                        ->first();
                    
                    if (!$product) {
                        throw new \Exception("Product not found with code: {$productData['product_code']}");
                    }
                    
                    // Get active price plan or first available
                    $pricePlan = $product->pricePlans()->where('active', true)->first()
                        ?? $product->pricePlans()->first();
                    
                    if (!$pricePlan) {
                        throw new \Exception("No price plan available for product: {$productData['product_code']}");
                    }
                } elseif (!empty($productData['product_id'])) {
                    // Product ID lookup
                    $product = Product::with('pricePlans')->find($productData['product_id']);
                    
                    if (!$product) {
                        throw new \Exception("Product not found with ID: {$productData['product_id']}");
                    }
                    
                    // Validate product belongs to organization
                    if ($product->organization_id != $organizationId) {
                        throw new \Exception('Product does not belong to the specified organization');
                    }
                    
                    // Get active price plan or first available
                    $pricePlan = $product->pricePlans()->where('active', true)->first()
                        ?? $product->pricePlans()->first();
                    
                    if (!$pricePlan) {
                        throw new \Exception("No price plan available for product ID: {$productData['product_id']}");
                    }
                }

                // Validate product belongs to organization (for all lookup methods)
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
                            ->where('status', '!=', 'cancelled')
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
                        $subscriptionData[$subscription->id] = [
                            'id' => $subscription->id,
                            'amount' => $productData['amount'],
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
                    // filter onetime product
                    if ($product->product_type_id == 1) {
                        $oneTimeInvoiceItems[$pricePlan->id] = [
                            'amount' =>  $productData['amount']
                        ];
                    }

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
                $invoice->load([
                    'customer',
                    'payments',
                    'invoiceTaxes.taxRate',
                    'invoiceItems.pricePlan.product',
                    'invoiceItems.subscription.pricePlan.product',
                ]);
                $controlNumbersMap = $this->buildControlNumbersMap(collect([$invoice]));
                $data = $this->formatInvoiceDetailResponse($invoice, $controlNumbersMap);

                return response()->json([
                    'success' => true,
                    'message' => 'All products have pending subscriptions - no invoice created',
                    'data' => $data
                ], 200);
            }

            $taxRates = $this->resolveActiveTaxRates($requestedTaxRateIds);
            $taxBreakdown = $this->calculateTaxBreakdown($totalAmount, $taxRates);
            $taxTotal = collect($taxBreakdown)->sum('amount');
            $grandTotal = round($totalAmount + $taxTotal, 2);

            // Create invoice
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'invoice_number' => $this->generateInvoiceNumber(),
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

            DB::commit();
            $invoice->load([
                'customer',
                'payments',
                'invoiceTaxes.taxRate',
                'invoiceItems.pricePlan.product',
                'invoiceItems.subscription.pricePlan.product',
            ]);
            $controlNumbersMap = $this->buildControlNumbersMap(collect([$invoice]));
            $data = $this->formatInvoiceDetailResponse($invoice, $controlNumbersMap);

            // Generate payment gateway links if requested
            $paymentGateway = $request->payment_gateway ?? null;
            $paymentDetails = [];

            // Generate control number if requested
            if (in_array($paymentGateway, ['control_number', 'both'])) {
                $controlNumber = $this->generateControlNumber($invoice, $organizationId);
                $paymentDetails['control_number'] = [
                    'reference' => $controlNumber,
                    'amount' => $grandTotal,
                    'currency' => $currency,
                    'expires_at' => Carbon::now()->addDays(7)->toISOString(),
                    'payment_instructions' => [
                        'mobile_banking' => "Dial *150*01*{$controlNumber}# from your registered mobile number",
                        'internet_banking' => 'Login to your internet banking and pay bill using control number',
                        'agent_banking' => 'Visit any bank agent and provide the control number'
                    ]
                ];
            }

            // Generate Flutterwave payment link if requested
            if (in_array($paymentGateway, ['flutterwave', 'both'])) {
                try {
                    $flutterwaveService = new FlutterwaveService();
                    
                    if ($flutterwaveService->isActive()) {
                        $flutterwavePayload = [
                            'tx_ref' => $invoice->invoice_number . '-' . time(),
                            'amount' => $grandTotal,
                            'currency' => $currency,
                            'redirect_url' => $request->success_url ?: config('app.url') . '/payment/callback',
                            'customer' => [
                                'email' => $customer->email,
                                'name' => $customer->name,
                                'phone' => $customer->phone,
                            ],
                            'title' => 'Invoice Payment',
                            'description' => $description,
                            'meta' => [
                                'invoice_id' => $invoice->id,
                                'invoice_number' => $invoice->invoice_number,
                                'customer_id' => $customer->id,
                                'organization_id' => $organizationId,
                            ],
                        ];
                        
                        $flutterwaveResult = $flutterwaveService->initializePayment($flutterwavePayload);
                        
                        if ($flutterwaveResult['success']) {
                            $paymentDetails['flutterwave'] = [
                                'payment_link' => $flutterwaveResult['data']['payment_link'],
                                'tx_ref' => $flutterwaveResult['data']['tx_ref'],
                                'expires_at' => $flutterwaveResult['data']['expires_at'] ?? null,
                                'instructions' => 'Click the payment link to pay via card, mobile money, or bank transfer'
                            ];
                            
                            Log::info('Flutterwave payment link generated successfully', [
                                'invoice_id' => $invoice->id,
                                'payment_link' => $flutterwaveResult['data']['payment_link'],
                            ]);
                        } else {
                            Log::warning('Flutterwave payment link generation failed', [
                                'invoice_id' => $invoice->id,
                                'error' => $flutterwaveResult['error'] ?? 'Unknown error',
                            ]);
                            
                            $paymentDetails['flutterwave_error'] = $flutterwaveResult['error'] ?? 'Payment link generation failed';
                        }
                    } else {
                        Log::warning('Flutterwave gateway not active', [
                            'invoice_id' => $invoice->id,
                        ]);
                        $paymentDetails['flutterwave_error'] = 'Flutterwave gateway not configured or inactive';
                    }
                } catch (\Exception $e) {
                    Log::error('Flutterwave integration error', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    
                    $paymentDetails['flutterwave_error'] = 'An error occurred while generating payment link';
                }
            }

            // Add payment details to response if any were generated
            if (!empty($paymentDetails)) {
                $data['payment_details'] = $paymentDetails;
            }

            // Add URLs if provided
            if ($request->success_url || $request->cancel_url) {
                $data['urls'] = [
                    'success_url' => $request->success_url,
                    'cancel_url' => $request->cancel_url,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => $data
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Invoice creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Invoice creation failed',
                'error' => $e->getMessage()
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
     * Generate control number for payment
     */
    private function generateControlNumber($invoice, $organizationId)
    {
        // Generate a random control number
        $reference = '99' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        
        // Get organization payment gateway integration for control numbers (EcoBank)
        $orgGateway = OrganizationPaymentGatewayIntegration::where('organization_id', $organizationId)
            ->whereHas('paymentGateway', function($query) {
                $query->where('name', 'EcoBank');
            })
            ->first();
        
        // Save control number to database
        try {
            $controlNumber = ControlNumber::create([
                'customer_id' => $invoice->customer_id,
                'reference' => $reference,
                'organization_payment_gateway_integration_id' => $orgGateway ? $orgGateway->id : null,
                'product_id' => $invoice->invoiceItems->first()->pricePlan->product_id ?? null,
                'type_id' => 9, // Control number type
                'header_response' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->total + $invoice->tax,
                    'generated_at' => now()->toISOString(),
                ],
            ]);
            
            Log::info('Control number saved to database', [
                'control_number_id' => $controlNumber->id,
                'reference' => $reference,
                'invoice_id' => $invoice->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save control number to database', [
                'reference' => $reference,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        return $reference;
    }

    /**
     * Generate invoice description based on product and request data
     */
    private function generateInvoiceDescription($product, $request)
    {
        $description = $product->name;
        
        if ($request->plan_code) {
            $description .= " - " . ucfirst($request->plan_code) . " Plan";
        }
        
        if ($request->billing_cycle) {
            $description .= " (" . ucfirst($request->billing_cycle) . ")";
        }
        
        return $description;
    }

    /**
     * Display the specified invoice.
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
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'error' => $e->getMessage()
            ], 404);
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
     * Create wallet topup invoice
     * POST /api/invoices/wallet-topup
     */
    public function createWalletTopupInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'wallet_type' => 'required|string|max:50',
            'units' => 'required|numeric|min:0.0001',
            'unit_price' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $customer = Customer::findOrFail($request->customer_id);
            $organization = $customer->organization;

            $units = $request->units;
            $unitPrice = $request->unit_price;
            $totalAmount = $units * $unitPrice;

            // Create wallet topup invoice
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_type' => 'wallet_topup',
                'status' => 'issued',
                'description' => $request->description ?: "Wallet topup - {$units} {$request->wallet_type}",
                'subtotal' => $totalAmount,
                'tax_total' => 0.00,
                'total' => $totalAmount,
                'due_date' => now()->addDays(14),
                'issued_at' => now(),
                'metadata' => [
                    'wallet_type' => $request->wallet_type,
                    'units' => $units,
                    'unit_price' => $unitPrice
                ]
            ]);

            // Create invoice item
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'price_plan_id' => null, // Wallet topups don't have price plans
                'description' => "Wallet credits - {$request->wallet_type}",
                'quantity' => $units,
                'unit_price' => $unitPrice,
                'total' => $totalAmount
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wallet topup invoice created successfully',
                'data' => [
                    'invoice' => $invoice->load('items'),
                    'customer' => $customer,
                    'organization' => $organization
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Wallet topup invoice creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create wallet topup invoice',
                'error' => $e->getMessage()
            ], 500);
        }
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

            'price_plans' => $invoice->invoiceItems->map(function ($item) use ($invoice, $controlNumbersMap) {
                $product = $item->pricePlan->product;
                $customerId = $invoice->customer->id;
                $mapKey = $this->controlNumbersMapKey($customerId, $product->id);
                $controlNumbers = $controlNumbersMap[$mapKey] ?? collect();

                // Map control numbers to payment gateways
                $paymentGateways = $controlNumbers->map(function ($controlNumber) {
                    $integration = $controlNumber->organizationPaymentGatewayIntegration;

                    if (!$integration || !$integration->paymentGateway) {
                        return null;
                    }

                    return [
                        'id' => $integration->id,
                        'payment_gateway_id' => $integration->payment_gateway_id,
                        'gateway_name' => $integration->paymentGateway->name,
                        'status' => $integration->status,
                        'references' => $controlNumber->reference,
                    ];
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
                    return $item->subscription !== null;
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

    /**
     * Get all invoices for a given product ID
     * GET /api/invoices?product_id={id}
     */
    public function getByProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $invoices = Invoice::whereHas('invoiceItems', function ($query) use ($request) {
            $query->where('product_id', $request->product_id);
        })
        ->where('status', '!=', 'cancelled')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }

    /**
     * Return invoices created for the provided subscription ids.
     * Request body: { subscription_ids: [1,2,3] }
     */
    public function getBySubscriptions(Request $request)
    {
        $request->validate([
            'subscription_ids' => 'required|array|min:1',
            'subscription_ids.*' => 'integer|exists:subscriptions,id',
        ]);

        $subscriptionIds = $request->subscription_ids;
        $invoiceIds = InvoiceItem::whereIn('subscription_id', $subscriptionIds)
            ->pluck('invoice_id')
            ->unique();

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
            'data' => $data
        ]);
    }

    /**
     * Create plan upgrade invoice
     * POST /api/invoices/plan-upgrade
     */
    public function createPlanUpgradeInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|exists:subscriptions,id',
            'new_price_plan_id' => 'required|exists:price_plans,id',
            'effective_date' => 'required|date|after_or_equal:today',
            'proration_method' => 'required|in:immediate,next_cycle,credit'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $subscription = \App\Models\Subscription::with(['customer', 'pricePlan.currency'])->findOrFail($request->subscription_id);
            $newPricePlan = \App\Models\PricePlan::with('currency')->findOrFail($request->new_price_plan_id);
            
            $currentPlan = $subscription->pricePlan;
            $priceDifference = $newPricePlan->amount - $currentPlan->amount;
            
            // Calculate proration based on method
            $prorationAmount = 0;
            $prorationCredit = 0;
            
            if ($request->proration_method === 'immediate' && $priceDifference > 0) {
                // Calculate remaining days in current billing cycle
                $nextBilling = Carbon::parse($subscription->next_billing_date);
                $daysRemaining = now()->diffInDays($nextBilling);
                $totalDaysInCycle = $currentPlan->billing_interval === 'monthly' ? 30 : 365;
                
                $prorationAmount = ($priceDifference * $daysRemaining) / $totalDaysInCycle;
            } elseif ($request->proration_method === 'credit' && $priceDifference < 0) {
                // Downgrade - create credit for difference
                $prorationCredit = abs($priceDifference);
            }

            // Create upgrade invoice
            $invoice = Invoice::create([
                'customer_id' => $subscription->customer_id,
                'subscription_id' => $subscription->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_type' => $priceDifference > 0 ? 'plan_upgrade' : 'plan_downgrade',
                'status' => 'issued',
                'description' => "Plan " . ($priceDifference > 0 ? 'upgrade' : 'downgrade') . " - {$currentPlan->name} to {$newPricePlan->name}",
                'subtotal' => max(0, $prorationAmount),
                'tax_total' => 0.00,
                'proration_credit' => $prorationCredit,
                'total' => max(0, $prorationAmount - $prorationCredit),
                'due_date' => now()->addDays(7),
                'issued_at' => now(),
                'metadata' => [
                    'old_plan_id' => $currentPlan->id,
                    'new_plan_id' => $newPricePlan->id,
                    'effective_date' => $request->effective_date,
                    'proration_method' => $request->proration_method,
                    'price_difference' => $priceDifference
                ]
            ]);

            if ($prorationAmount > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'price_plan_id' => $newPricePlan->id,
                    'description' => "Plan upgrade proration - {$newPricePlan->name}",
                    'quantity' => 1,
                    'unit_price' => $prorationAmount,
                    'total' => $prorationAmount
                ]);
            }

            // Record subscription change
            DB::table('subscription_changes')->insert([
                'subscription_id' => $subscription->id,
                'change_type' => $priceDifference > 0 ? 'upgrade' : 'downgrade',
                'old_price_plan_id' => $currentPlan->id,
                'new_price_plan_id' => $newPricePlan->id,
                'proration_amount' => $prorationAmount,
                'effective_date' => $request->effective_date,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Plan change invoice created successfully',
                'data' => [
                    'invoice' => $invoice->load('items'),
                    'subscription' => $subscription,
                    'old_plan' => $currentPlan,
                    'new_plan' => $newPricePlan,
                    'proration_amount' => $prorationAmount,
                    'proration_credit' => $prorationCredit
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Plan change invoice creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create plan change invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create plan downgrade invoice
     * POST /api/invoices/plan-downgrade
     */
    public function createPlanDowngradeInvoice(Request $request)
    {
        // Reuse the upgrade logic but ensure we're handling downgrades
        return $this->createPlanUpgradeInvoice($request);
    }

    /**
     * Calculate next billing date based on billing cycle
     */
    private function calculateNextBillingDate($billingCycle)
    {
        switch (strtolower($billingCycle)) {
            case 'weekly':
                return now()->addWeek();
            case 'monthly':
            case 'month':
                return now()->addMonth();
            case 'quarterly':
            case 'quarter':
                return now()->addMonths(3);
            case 'semi-annual':
            case 'semi-annually':
                return now()->addMonths(6);
            case 'annual':
            case 'annually':
            case 'yearly':
                return now()->addYear();
            default:
                return now()->addMonth(); // Default to monthly
        }
    }
}

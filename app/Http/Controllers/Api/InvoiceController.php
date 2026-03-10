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
use Carbon\Carbon;
use App\Services\SubscriptionService;
use App\Services\FlutterwaveService;
use App\Services\Stripe\PaymentIntentService;
use App\Services\Flutterwave\Models\CreateCustomerRequest;
use App\Services\Flutterwave\Models\CreateOrderRequest;
use App\Services\Flutterwave\Models\CreatePaymentMethodRequest;
use App\Services\Stripe\StripeAmountHelper;
use App\Traits\ValidatePhoneNumber;
use App\Jobs\Payments\CreateEcobankReferenceJob;
use App\Jobs\Payments\CreateFlutterwaveReferenceJob;
use App\Jobs\Payments\CreateStripeReferenceJob;
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
            $date = $request->date ?? null;
            $dueDate = $request->due_date ?? null;
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
            } else {

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

            if ($totalGatewayJobs > 0) {
                foreach ($gatewayJobs as $jobData) {
                    $successUrl = $request->input('success_url') ?: config('app.url') . '/payment/callback';

                    if ($jobData['gateway_name'] === 'universal control number') {
                        CreateEcobankReferenceJob::dispatch(
                            $invoice->id,
                            $jobData['product_id'],
                            $customer->id,
                            $jobData['organization_gateway_id'],
                            $successUrl
                        );
                        continue;
                    }

                    if ($jobData['gateway_name'] === 'flutterwave') {
                        CreateFlutterwaveReferenceJob::dispatch(
                            $invoice->id,
                            $jobData['product_id'],
                            $customer->id,
                            $jobData['organization_gateway_id'],
                            $successUrl
                        );
                        continue;
                    }

                    if ($jobData['gateway_name'] === 'stripe') {
                        CreateStripeReferenceJob::dispatch(
                            $invoice->id,
                            $jobData['product_id'],
                            $customer->id,
                            $jobData['organization_gateway_id'],
                            $successUrl
                        );
                    }
                }
            }

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

            'price_plans' => $invoice->invoiceItems->map(function ($item) use ($invoice, $controlNumbersMap) {
                $product = $item->pricePlan->product;
                $customerId = $invoice->customer->id;
                $mapKey = $this->controlNumbersMapKey($customerId, $product->id);
                $controlNumbers = $controlNumbersMap[$mapKey] ?? collect();

                // Map control numbers to payment gateways
                $paymentGateways = $controlNumbers
                    ->filter(function ($controlNumber) use ($invoice) {
                        return $this->shouldIncludeControlNumberForInvoice($controlNumber, $invoice->id);
                    })
                    ->map(function ($controlNumber) {
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

    private function normalizeControlNumberMetadata($metadata): array
    {
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($metadata) ? $metadata : [];
    }
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
        // dd($this->resolveBearerToken());

            if (!$flutterwaveService->isActive()) {
                Log::warning('Flutterwave gateway not active', [
                    'invoice_id' => $invoice->id,
                ]);
                return [
                    'success' => false,
                    'message' => 'Flutterwave gateway not configured or inactive',
                ];
            }

            // $resolvedFlutterwaveCustomerId =  $request->input('flutterwave_customer_id');
            $resolvedFlutterwaveCustomerId = 'cus_WU8C56hDji' ;// $request->input('flutterwave_customer_id');

         
            if (!$resolvedFlutterwaveCustomerId) {
                $customerEmail = $customer->email ?: 'customer@example.com';
                $validatedPhone = $this->validatePhone((string) $customer->phone);
                $phonePayload = null;

                if ($validatedPhone !== false) {
                    $phonePayload = [
                        'country_code' => $validatedPhone['country_code'],
                        'number' => $validatedPhone['national_number'],
                    ];
                }

                $existingCustomers = $flutterwaveService->searchCustomers($customerEmail, 1, 10);
                if (!empty($existingCustomers['data'][0])) {
                    $resolvedFlutterwaveCustomerId = $existingCustomers['data'][0]->id;
                } else {
                    $nameParts = preg_split('/\s+/', trim((string) $customer->name)) ?: ['Customer'];
                    $createdCustomer = $flutterwaveService->createCustomer(new CreateCustomerRequest(
                        email: $customerEmail,
                        name: [
                            'first' => $nameParts[0] ?? 'Customer',
                            'last' => $nameParts[1] ?? 'User',
                        ],
                        phone: $phonePayload,
                        meta: [
                            'local_customer_id' => $customer->id,
                            'organization_id' => $customer->organization_id,
                        ]
                    ));

                    $resolvedFlutterwaveCustomerId = $createdCustomer->id;
                }
            }
           

            $resolvedPaymentMethodId = 'pmd_MxRH2NMrGs'; //TESTING - Remove this line in production

            if (!$resolvedPaymentMethodId) {
                $providedPaymentMethod = $request->input('flutterwave_payment_method', []);
                $paymentMethodType = (string) ($providedPaymentMethod['type'] ?? 'card');
                $paymentMethodPayload = (array) ($providedPaymentMethod['payload'] ?? []);

                if (strtolower($paymentMethodType) === 'card') {
                    $paymentMethodPayload = array_merge(
                        $this->generateRandomCardTestPayload(),
                        $paymentMethodPayload
                    );
                }
                $createdPaymentMethod = $flutterwaveService->createPaymentMethod(new CreatePaymentMethodRequest(
                    type: $paymentMethodType,
                    paymentData: $paymentMethodPayload,
                    customerId: $resolvedFlutterwaveCustomerId,
                    meta: [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                    ],
                ));
                

                $resolvedPaymentMethodId = $createdPaymentMethod->id;
            }
          
            $orderReference = $invoice->invoice_number . '-' . $product->id . '-' . str_replace('.', '', (string) microtime(true));
            $orderMeta = [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'local_customer_id' => $customer->id,
                'flutterwave_customer_id' => $resolvedFlutterwaveCustomerId,
                'flutterwave_payment_method_id' => $resolvedPaymentMethodId,
                'organization_id' => $customer->organization_id,
                'product_id' => $product->id,
            ];

            $order = $flutterwaveService->createOrder(new CreateOrderRequest(
                amount: (float) $invoice->total,
                currency: (string) ($invoice->currency ?: 'TZS'),
                reference: $orderReference,
                customerId: $resolvedFlutterwaveCustomerId,
                paymentMethodId: $resolvedPaymentMethodId,
                redirectUrl: $request->success_url ?: config('app.url') . '/payment/callback',
                meta: $orderMeta,
                merchantVatAmount: (float) $invoice->tax_total,
            ));
            $flutterwaveData = $order->toArray();
            $txRef = $order->reference ?: $orderReference;
            $paymentLink = $order->redirectUrl();

            if (empty($order->id)) {
                Log::warning('Flutterwave order creation returned empty id', [
                    'invoice_id' => $invoice->id,
                    'reference' => $orderReference,
                    'response' => $flutterwaveData,
                ]);

                return [
                    'success' => false,
                    'message' => 'Payment order creation failed',
                ];
            }

            $controlNumber = ControlNumber::create([
                'customer_id' => $customer->id,
                'reference' => $txRef,
                'organization_payment_gateway_integration_id' => $orgGateway->id,
                'product_id' => $product->id,
                'metadata' => json_encode([
                    'order_id' => $order->id,
                    'payment_link' => $paymentLink,
                    'reference' => $txRef,
                    'status' => $order->status,
                    'next_action' => $flutterwaveData['next_action'] ?? null,
                    'processor_response' => $flutterwaveData['processor_response'] ?? null,
                    'payment_method_details' => $flutterwaveData['payment_method_details'] ?? null,
                    'billing_details' => $flutterwaveData['billing_details'] ?? null,
                    'created_datetime' => $flutterwaveData['created_datetime'] ?? null,
                    'expires_at' => $flutterwaveData['expires_at'] ?? null,
                    'instructions' => 'Click the payment link to pay via card, mobile money, or bank transfer',
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_id' => $customer->id,
                    'organization_id' => $customer->organization_id,
                    'product_id' => $product->id,
                ]),
            ]);

            Log::info('Flutterwave payment link generated successfully', [
                'invoice_id' => $invoice->id,
                'order_id' => $order->id,
                'payment_link' => $paymentLink,
                'tx_ref' => $txRef,
                'status' => $order->status,
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

}

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
        // First determine which validation set to use based on provided fields
        $hasProductCode = $request->has('product_code');
        $hasCustomerObject = $request->has('customer');
        $hasProductId = $request->has('product_id');
        $hasCustomerId = $request->has('customer_id');

        // Flexible validation rules
        $rules = [
            'organization_id' => 'required|exists:organizations,id',
            'invoice_type' => 'nullable|in:subscription,wallet_topup,plan_upgrade,plan_downgrade,one_time',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|exists:currencies,code',
            'plan_code' => 'nullable|string',
            'billing_cycle' => 'nullable|string|in:monthly,yearly,weekly,daily',
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
            'metadata' => 'nullable|array',
        ];

        // Conditional validation based on what's provided
        if ($hasProductCode && $hasCustomerObject) {
            // Full format with product_code and customer object
            $rules['product_code'] = 'required|string';
            $rules['customer'] = 'required|array';
            $rules['customer.name'] = 'required|string|max:255';
            $rules['customer.phone'] = 'required|string|max:20';
            $rules['customer.email'] = 'nullable|email|max:255';
            $rules['customer.external_ref'] = 'nullable|string|max:255';
            $rules['customer.customer_type'] = 'nullable|string|in:individual,business,school,organization';
        } elseif ($hasProductId && $hasCustomerId) {
            // Simple format with IDs
            $rules['product_id'] = 'required|exists:products,id';
            $rules['customer_id'] = 'required|exists:customers,id';
        } else {
            // Invalid combination
            return response()->json([
                'success' => false,
                'message' => 'Either provide (product_code + customer object) or (product_id + customer_id)',
                'errors' => [
                    'format' => ['You must provide either product_code with customer object, or product_id with customer_id']
                ]
            ], 422);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $organization = Organization::findOrFail($request->organization_id);
            
            // Handle product resolution
            if ($hasProductCode) {
                $product = Product::with('pricePlans')
                    ->where('product_code', $request->product_code)
                    ->where('organization_id', $request->organization_id)
                    ->first();
                
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => "Product not found with code: {$request->product_code}",
                        'errors' => [
                            'product_code' => ['Product with this code not found in your organization']
                        ]
                    ], 404);
                }
            } else {
                $product = Product::with('pricePlans')->findOrFail($request->product_id);
                
                // Verify product belongs to organization
                if ($product->organization_id != $request->organization_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['product_id' => ['The selected product does not belong to this organization.']]
                    ], 422);
                }
            }

            // Handle customer resolution/creation
            if ($hasCustomerObject) {
                $customerData = $request->customer;
                
                // Try to find existing customer by phone or email
                $customer = Customer::where('organization_id', $request->organization_id)
                    ->where(function($query) use ($customerData) {
                        $query->where('phone', $customerData['phone']);
                        if (isset($customerData['email'])) {
                            $query->orWhere('email', $customerData['email']);
                        }
                    })
                    ->first();
                
                $customerCreated = false;
                if (!$customer) {
                    // Create new customer
                    $customer = Customer::create([
                        'organization_id' => $request->organization_id,
                        'name' => $customerData['name'],
                        'phone' => $customerData['phone'],
                        'email' => $customerData['email'] ?? null,
                        'external_ref' => $customerData['external_ref'] ?? null,
                        'customer_type' => $customerData['customer_type'] ?? 'individual',
                        'status' => 'active'
                    ]);
                    $customerCreated = true;
                }
            } else {
                $customer = Customer::findOrFail($request->customer_id);
                
                // Verify customer belongs to organization
                if ($customer->organization_id != $request->organization_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['customer_id' => ['The selected customer does not belong to this organization.']]
                    ], 422);
                }
                $customerCreated = false;
            }

            // Determine amount (use provided amount or default from first price plan)
            $amount = $request->amount;
            if (!$amount && $product->pricePlans->isNotEmpty()) {
                $amount = $product->pricePlans->first()->amount;
            }
            $amount = $amount ?: 0;

            // Determine if this should create a subscription
            $shouldCreateSubscription = ($request->invoice_type === 'subscription') && 
                                      ($request->billing_cycle || $request->plan_code);

            $subscription = null;
            if ($shouldCreateSubscription) {
                // Find the appropriate price plan
                $pricePlan = $product->pricePlans()->first();
                if ($request->plan_code) {
                    $planByCode = $product->pricePlans()->where('name', 'LIKE', '%' . $request->plan_code . '%')->first();
                    if ($planByCode) {
                        $pricePlan = $planByCode;
                        $amount = $pricePlan->amount; // Use plan amount instead
                    }
                }
                
                if (!$pricePlan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No price plan found for this product to create subscription',
                    ], 400);
                }

                // Create subscription
                $subscription = \App\Models\Subscription::create([
                    'customer_id' => $customer->id,
                    'price_plan_id' => $pricePlan->id,
                    'subscription_number' => 'SUB' . now()->format('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'status' => 'active',
                    'start_date' => now(),
                    'next_billing_date' => $this->calculateNextBillingDate($request->billing_cycle ?: $pricePlan->billing_interval),
                    'trial_ends_at' => $pricePlan->trial_period_days ? now()->addDays($pricePlan->trial_period_days) : null,
                ]);
            }

            // Create invoice
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'subscription_id' => $subscription ? $subscription->id : null,
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_type' => $request->invoice_type ?: 'subscription',
                'status' => 'issued',
                'description' => $this->generateInvoiceDescription($product, $request),
                'subtotal' => $amount,
                'tax_total' => 0,
                'total' => $amount,
                'due_date' => Carbon::now()->addDays(30),
                'issued_at' => Carbon::now(),
                'metadata' => $request->metadata ?: [],
            ]);

            // Create invoice items
            // Try to get price plan from product if available
            $pricePlan = $product->pricePlans()->first();
            
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'price_plan_id' => $pricePlan ? $pricePlan->id : null,
                'description' => $product->name,
                'quantity' => 1,
                'unit_price' => $amount,
                'total' => $amount,
            ]);

            // Generate control number (simplified for now)
            $controlNumber = $this->generateControlNumber();

            // Prepare response data
            $responseData = [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_id' => $customer->id,
                    'subscription_id' => $subscription ? $subscription->id : null,
                    'organization_id' => $organization->id,
                    'invoice_type' => $invoice->invoice_type,
                    'status' => $invoice->status,
                    'description' => $invoice->description,
                    'subtotal' => number_format($invoice->subtotal, 2),
                    'tax_total' => number_format($invoice->tax_total, 2),
                    'total' => number_format($invoice->total, 2),
                    'currency' => $request->currency ?: 'TZS',
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'issued_at' => $invoice->issued_at->toISOString(),
                ],
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'external_ref' => $customer->external_ref,
                    'customer_type' => $customer->customer_type,
                    'created' => $customerCreated
                ],
                'payment_details' => [
                    'control_number' => $controlNumber,
                    'amount' => number_format($amount, 2),
                    'currency' => $request->currency ?: 'TZS',
                    'expires_at' => Carbon::now()->addDays(7)->toISOString(),
                    'payment_instructions' => [
                        'mobile_banking' => "Dial *150*01*{$controlNumber}# from your registered mobile number",
                        'internet_banking' => 'Login to your internet banking and pay bill using control number',
                        'agent_banking' => 'Visit any bank agent and provide the control number'
                    ]
                ]
            ];

            // Add subscription info if created
            if ($subscription) {
                $responseData['subscription'] = [
                    'id' => $subscription->id,
                    'subscription_number' => $subscription->subscription_number,
                    'status' => $subscription->status,
                    'start_date' => $subscription->start_date->format('Y-m-d'),
                    'next_billing_date' => $subscription->next_billing_date->format('Y-m-d'),
                    'trial_ends_at' => $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d H:i:s') : null,
                ];
            }

            if ($request->success_url || $request->cancel_url) {
                $responseData['urls'] = [
                    'success_url' => $request->success_url,
                    'cancel_url' => $request->cancel_url,
                    'payment_url' => "http://localhost:8000/pay/{$controlNumber}"
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Control number generated successfully',
                'data' => $responseData
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

    /**
     * Generate control number for payment
     */
    private function generateControlNumber()
    {
        // Generate a random control number for testing
        // In production, this should integrate with actual payment gateway
        return '99' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
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
                'invoiceItems.pricePlan.product',
                'subscription.pricePlan'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice' => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_type' => $invoice->invoice_type,
                        'status' => $invoice->status,
                        'description' => $invoice->description,
                        'subtotal' => $invoice->subtotal,
                        'tax_total' => $invoice->tax_total,
                        'total' => $invoice->total,
                        'due_date' => $invoice->due_date,
                        'issued_at' => $invoice->issued_at,
                        'metadata' => $invoice->metadata,
                        'created_at' => $invoice->created_at,
                        'updated_at' => $invoice->updated_at
                    ],
                    'customer' => $invoice->customer ? [
                        'id' => $invoice->customer->id,
                        'name' => $invoice->customer->name,
                        'email' => $invoice->customer->email,
                        'phone' => $invoice->customer->phone,
                        'customer_type' => $invoice->customer->customer_type,
                    ] : null,
                    'items' => $invoice->invoiceItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'description' => $item->description,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total' => $item->total,
                            'price_plan' => $item->pricePlan ? [
                                'id' => $item->pricePlan->id,
                                'name' => $item->pricePlan->name,
                                'amount' => $item->pricePlan->amount,
                                'billing_interval' => $item->pricePlan->billing_interval,
                                'product' => $item->pricePlan->product ? [
                                    'id' => $item->pricePlan->product->id,
                                    'name' => $item->pricePlan->product->name,
                                    'product_code' => $item->pricePlan->product->product_code,
                                ] : null
                            ] : null
                        ];
                    }),
                    'subscription' => $invoice->subscription ? [
                        'id' => $invoice->subscription->id,
                        'subscription_number' => $invoice->subscription->subscription_number,
                        'status' => $invoice->subscription->status,
                        'start_date' => $invoice->subscription->start_date,
                        'next_billing_date' => $invoice->subscription->next_billing_date,
                        'price_plan' => $invoice->subscription->pricePlan ? [
                            'id' => $invoice->subscription->pricePlan->id,
                            'name' => $invoice->subscription->pricePlan->name,
                            'amount' => $invoice->subscription->pricePlan->amount,
                            'billing_interval' => $invoice->subscription->pricePlan->billing_interval,
                        ] : null
                    ] : null
                ]
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

    /**
     * Get all invoices for a given product ID
     * GET /api/invoices?product_id={id}
     */
    public function getByProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $invoices = Invoice::whereHas('invoiceItems', function($query) use ($request) {
            $query->where('product_id', $request->product_id);
        })->get();

        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }
}

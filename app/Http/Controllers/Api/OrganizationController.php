<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\PaymentGateway;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organizations = Organization::with(['currency', 'country'])->get();
        
        $organizationsData = [];
        
        foreach ($organizations as $organization) {
            // Fetch payment gateways with configurations and merchants
            $paymentGatewayDetails = [];
            
            $gateways = DB::table('organization_payment_gateway_integrations as opgi')
                ->join('payment_gateways as pg', 'opgi.payment_gateway_id', '=', 'pg.id')
                ->where('opgi.organization_id', $organization->id)
                ->select('pg.*', 'opgi.id as integration_id')
                ->get();
            
            foreach ($gateways as $gateway) {
                // Get configuration
                $configuration = DB::table('configurations')
                    ->where('organization_id', $organization->id)
                    ->where('payment_gateway_id', $gateway->id)
                    ->first();
                
                // Get merchant (only for UCN)
                $merchant = DB::table('merchants')
                    ->where('organization_payment_gateway_integration_id', $gateway->integration_id)
                    ->first();
                
                // Build gateway data
                $gatewayData = [
                    'id' => $gateway->id,
                    'name' => $gateway->name,
                    'type' => $gateway->type,
                    'webhook_secret' => $gateway->webhook_secret,
                    'config' => json_decode($gateway->config),
                    'active' => (bool)$gateway->active,
                    'created_at' => $gateway->created_at,
                    'updated_at' => $gateway->updated_at,
                    'configuration' => $configuration,
                    'merchants' => $merchant,
                ];
                
                $paymentGatewayDetails[] = $gatewayData;
            }
            
            $organizationsData[] = [
                'organization_detail' => $organization,
                'payment_gateways' => $paymentGatewayDetails,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $organizationsData
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'endpoint' => 'nullable|url|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'country_id' => 'required|exists:countries,id',
            'timezone' => 'required|string|max:255',
            'status' => 'required|in:active,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Step 1: Create organization
            $organization = Organization::create($validator->validated());
            $organization->load(['currency', 'country']);

            // Step 2: Fetch all active payment gateways
            $activeGateways = PaymentGateway::where('active', true)->get();

            $paymentGatewayDetails = [];

            // Step 3: Process each gateway
            foreach ($activeGateways as $gateway) {
                $configuration = null;
                $merchants = null;

                $bankAccountId = null;

                // Step 4: Handle UCN gateway specifically
                if (strtolower($gateway->name) === 'universal control number' || strtolower($gateway->name) === 'ucn') {
                    // Fetch virtual account
                    $virtualAccount = DB::table('constant.virtual_accounts')
                        ->where('status', 1)
                        ->select('id', 'account_number', 'refer_bank_id')
                        ->first();

                    if (!$virtualAccount) {
                        throw new Exception('No available virtual account found for UCN gateway');
                    }

                    // Create bank account
                    $bankAccount = BankAccount::create([
                        'name' => $organization->name . ' - UCN Virtual Account',
                        'account_number' => $virtualAccount->account_number,
                        'branch' => 'DAR ES SALAAM',
                        'refer_bank_id' => $virtualAccount->refer_bank_id,
                        'organization_id' => $organization->id,
                    ]);

                    $bankAccountId = $bankAccount->id;

                    // Update virtual account status to 2 (assigned)
                    DB::table('constant.virtual_accounts')
                        ->where('id', $virtualAccount->id)
                        ->update(['status' => 2]);
                }

                // Step 5: Create organization_payment_gateway_integration
                $integrationId = DB::table('organization_payment_gateway_integrations')->insertGetId([
                    'bank_account_id' => $bankAccountId,
                    'payment_gateway_id' => $gateway->id,
                    'organization_id' => $organization->id,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Step 6: Create configurations
                $apiKey = $this->generateApiKey();
                $signatureKey = $this->generateSignatureKey();

                $configurationId = DB::table('configurations')->insertGetId([
                    'env' => 1, // Testing environment
                    'api_key' => $apiKey,
                    'signature_key' => $signatureKey,
                    'api_endpoint' => $organization->endpoint, // Use organization endpoint
                    'organization_id' => $organization->id,
                    'payment_gateway_id' => $gateway->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $configuration = DB::table('configurations')->where('id', $configurationId)->first();

                // Step 7: Handle UCN merchant creation
                if ($bankAccountId && (strtolower($gateway->name) === 'universal control number' || strtolower($gateway->name) === 'ucn')) {
                    try {
                        $merchantData = $this->createMerchantQR($organization, $bankAccount->account_number);

                        if (is_array($merchantData)) {
                            $datas =array_merge($merchantData, [
                                'organization_payment_gateway_integration_id' => $integrationId,
                                'created_at' => now()
                            ]) ;
                            
                            // Store merchant data
                            $merchantId = DB::table('merchants')->insertGetId($datas);

                            // Update integration status to completed
                            DB::table('organization_payment_gateway_integrations')
                                ->where('id', $integrationId)
                                ->update(['status' => 'completed']);

                            $merchant = DB::table('merchants')->where('id', $merchantId)->first();
                            $merchants = $merchant;
                        } else {
                            Log::warning('Merchant creation failed for organization', [
                                'organization_id' => $organization->id,
                                'error' => $merchantData
                            ]);
                        }
                    } catch (Exception $e) {
                        Log::error('Error creating merchant for UCN gateway', [
                            'organization_id' => $organization->id,
                            'error' => $e->getMessage(),
                        ]);
                        // Continue without failing the entire transaction
                    }
                }

                // Build gateway data with nested configuration and merchants
                $gatewayDetails = $gateway->toArray();
                $gatewayDetails['configuration'] = $configuration;
                $gatewayDetails['merchants'] = $merchants;

                $paymentGatewayDetails[] = $gatewayDetails;
            }

            DB::commit();

            Log::info('Organization created successfully with payment gateway integrations', [
                'organization_id' => $organization->id,
                'gateways_count' => count($paymentGatewayDetails),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Organization created successfully',
                'data' => [
                    'organization_detail' => $organization,
                    'payment_gateways' => $paymentGatewayDetails,
                ]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Organization creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Organization creation failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while creating the organization',
            ], 500);
        }
    }

    /**
     * Create merchant QR for UCN gateway (from Partner.php)
     */
    private function createMerchantQR($organization, $accountNumber)
    {
        // UCN/Ecobank credentials (LIVE)
        $username = 'ETZSHULESOFT';
        $password = '$2a$10$jdNZI4uiE86yRhcFNrBenOo0nBQji9zqy9IVa.roj0ST5EhlE4sVe';
        $labId = 'KmiqL3yCLf1V68oRQrIv';
        $baseUrl = 'https://payservice.ecobank.com';
        $origin = 'https://payservice.ecobank.com/PayPortal';
        $callBackUrl = config('app.url') . '/api/ecobank/notification';

        $headerRequest = [
            "requestId" => time() . random_int(1000, 9999),
            "affiliateCode" => "ETZ",
            "requestToken" => "/4mZF42iofzo7BDu0YtbwY6swLwk46Z91xItybhYwQGFpaZNOpsznL/9fca5LkeV",
            "sourceCode" => "ECOBANK_QR_API",
            "sourceChannelId" => "KANZAN",
            "requestType" => "CREATE_MERCHANT"
        ];
        $referralCode = time() . rand(100, 999);

        $body = [
            'merchantAddress' => $organization->name,
            'merchantName' => $organization->name,
            'accountNumber' => $accountNumber,
            'terminalName' => $organization->name,
            'mobileNumber' => $organization->phone,
            'email' => $organization->email,
            'area' => 'DAR ES SALAAM',
            'city' => 'DAR ES SALAAM',
            'referralCode' => $referralCode,
            'mcc' => '6533',
            'dynamicQr' => 'Y',
            'callBackUrl' => $callBackUrl,
        ];

        $bodyHashPayload = implode('', array_values($body));
        $secureHash = $this->generateSecureHash($bodyHashPayload);

        // Generate request body
        $data = ['headerRequest' => $headerRequest];
        foreach ($body as $key => $value) {
            $data[$key] = $value;
        }
        $data['secure_hash'] = $secureHash;

        // Get token
        $token = $this->createEcobankToken($baseUrl, $origin, $username, $password, $labId);
       
        if ($token == 0) {
            return 'Failed to get EcoBank token';
        }

        // API endpoint
        $url = $baseUrl . '/corporateapi/merchant/createqr';
        return [
            'header_response' => json_encode([
                'status' => 'SUCCESS',
                'status_code' => 201,
                'message' => 'UCN merchant and terminal successfully created',
                'request_id' => 'UCN-REQ-20260114-8F3A9C21'
            ]),
            'merchant_code' => 'UCN-MER-240114-000873',
            'qr_code' => '00020101021226280012UCN.TZ0119UCNMER2401140008735204581253038345802TZ5909UCN SHOP 6007DAR ES SALAAM6304A1B2',
            'terminal_id' => rand(100000, 999999),
            'terminal_name' => 'UCN Main POS Terminal',
            'secret_key' => 'ucn_sk_live_9F8A7C6E5D4B3A2C1E0F987654321ABC'
        ];

        // Initialize curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: ' . $origin,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return 'Request error: ' . $error;
        }

        curl_close($ch);

        $responseData = json_decode($response, true);
        Log::info('EcoBank Merchant Creation Response', ['response' => $responseData]);

        // Check if response is successful
        if (!empty($responseData['response_code']) && $responseData['response_code'] == 200) {
            $content = $responseData['response_content'];
            $headerResponse = json_encode($content['headerResponse']);

            return [
                'header_response' => $headerResponse,
                'merchant_code' => $content['merchantCode'] ?? null,
                'qr_code' => $content['qrCode'] ?? null,
                'terminal_id' => $content['terminalId'] ?? null,
                'terminal_name' => $content['terminalName'] ?? null,
                'secret_key' => $content['secretKey'] ?? null,
            ];
        } elseif (!empty($responseData['response_code']) && $responseData['response_code'] == 400 && !empty($responseData['errors'])) {
            $errorMsg = 'Failed to create merchant: ';
            foreach ($responseData['errors'] as $error) {
                $errorMsg .= $error['message'] . '; ';
            }
            return $errorMsg;
        } else {
            return 'Unexpected response from EcoBank API';
        }
    }

    /**
     * Create Ecobank token (from Partner.php)
     */
    private function createEcobankToken($baseUrl, $origin, $username, $password, $labId)
    {
        $data = [
            'userId' => $username,
            'password' => $password,
            'labId' => $labId,
        ];

        $url = $baseUrl . '/corporateapi/user/token';
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: ' . $origin,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return 0;
        }

        curl_close($ch);

        $responseData = json_decode($response, true);
        if (isset($responseData['token'])) {
            return $responseData['token'];
        }

        return 0;
    }

    /**
     * Generate secure hash (from Partner.php)
     */
    private function generateSecureHash($payload)
    {
        try {
            $username = 'ETZSHULESOFT';
            $password = '$2a$10$jdNZI4uiE86yRhcFNrBenOo0nBQji9zqy9IVa.roj0ST5EhlE4sVe';
            $labId = 'KmiqL3yCLf1V68oRQrIv';

            $concatenated = $username . $password . $labId . $payload;
            $hash = hash('sha512', $concatenated, true);
            $hexString = bin2hex($hash);

            return $hexString;
        } catch (Exception $e) {
            Log::error('Error generating secure hash', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Generate API key (from ClientRegistrationController.php)
     */
    private function generateApiKey()
    {
        return 'org_' . bin2hex(random_bytes(16)) . '_' . time();
    }

    /**
     * Generate signature key (from ClientRegistrationController.php)
     */
    private function generateSignatureKey()
    {
        return hash('sha256', uniqid() . random_bytes(32) . microtime(true));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $organization = Organization::with(['currency', 'country'])->find($id);

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $organization
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|max:255',
            'endpoint' => 'nullable|url|max:255',
            'currency_id' => 'sometimes|required|exists:currencies,id',
            'country_id' => 'sometimes|required|exists:countries,id',
            'timezone' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:active,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $organization->update($validator->validated());
        $organization->load(['currency', 'country']);

        return response()->json([
            'success' => true,
            'message' => 'Organization updated successfully',
            'data' => $organization
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        $organization->delete();

        return response()->json([
            'success' => true,
            'message' => 'Organization deleted successfully'
        ], 200);
    }
}

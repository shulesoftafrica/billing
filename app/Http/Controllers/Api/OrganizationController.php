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
        $organizations = Organization::with(['country'])->get();
        
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
            'email' => 'required|email|max:255|unique:organizations',
            'currency' => 'required|array|min:1',
            'country_id' => 'required|exists:countries,id',
            'status' => 'required|in:active,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create organization
            $organization = Organization::create($validator->validated());
            $organization->load(['country']);

            Log::info('Organization created successfully', [
                'organization_id' => $organization->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Organization created successfully',
                'data' => $organization
            ], 201);
        } catch (Exception $e) {
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
     * Integrate payment gateway with organization
     * Supported gateways: Universal Control Number (UCN), Stripe, PayPal, Flutterwave
     * Currently implemented: UCN only
     */
    public function integratePaymentGateway(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'endpoint' => 'required|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $organization = Organization::findOrFail($request->organization_id);
            $gateway = PaymentGateway::findOrFail($request->payment_gateway_id);

            // Check if integration already exists
            $existingIntegration = DB::table('organization_payment_gateway_integrations')
                ->where('organization_id', $organization->id)
                ->where('payment_gateway_id', $gateway->id)
                ->first();

            if ($existingIntegration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway already integrated with this organization'
                ], 422);
            }

            // Route to appropriate gateway integration method
            $gatewayName = strtolower($gateway->name);
            
            if ($gatewayName === 'universal control number' || $gatewayName === 'ucn') {
                $result = $this->integrateUCN($organization, $gateway, $request->endpoint);
            } elseif ($gatewayName === 'stripe') {
                return response()->json([
                    'success' => false,
                    'message' => 'Stripe integration is not yet implemented. Coming soon.'
                ], 422);
            } elseif ($gatewayName === 'paypal') {
                return response()->json([
                    'success' => false,
                    'message' => 'PayPal integration is not yet implemented. Coming soon.'
                ], 422);
            } elseif ($gatewayName === 'flutterwave') {
                return response()->json([
                    'success' => false,
                    'message' => 'Flutterwave integration is not yet implemented. Coming soon.'
                ], 422);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Integration for ' . $gateway->name . ' is not yet supported.'
                ], 422);
            }

            DB::commit();

            Log::info('Payment gateway integrated successfully', [
                'organization_id' => $organization->id,
                'payment_gateway_id' => $gateway->id,
                'gateway_name' => $gateway->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => $gateway->name . ' integrated successfully',
                'data' => [
                    'organization' => $organization,
                    'payment_gateway' => $result,
                ]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Payment gateway integration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment gateway integration failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during integration',
            ], 500);
        }
    }

    /**
     * Integrate Universal Control Number (UCN) gateway
     * 
     * @param Organization $organization
     * @param PaymentGateway $gateway
     * @param string $endpoint API endpoint for the organization
     * @return array Gateway details with configuration and merchant info
     * @throws Exception
     */
    private function integrateUCN($organization, $gateway, $endpoint)
    {
        // Step 1: Fetch virtual account
        $virtualAccount = DB::table('constant.virtual_accounts')
            ->where('status', 1)
            ->select('id', 'account_number', 'refer_bank_id')
            ->first();

        if (!$virtualAccount) {
            throw new Exception('No available virtual account found for UCN gateway');
        }

        // Step 2: Create bank account
        $bankAccount = BankAccount::create([
            'name' => $organization->name . ' - UCN Virtual Account',
            'account_number' => $virtualAccount->account_number,
            'branch' => 'DAR ES SALAAM',
            'refer_bank_id' => $virtualAccount->refer_bank_id,
            'organization_id' => $organization->id,
        ]);

        // Step 3: Update virtual account status to 2 (assigned)
        DB::table('constant.virtual_accounts')
            ->where('id', $virtualAccount->id)
            ->update(['status' => 2]);

        // Step 4: Create organization_payment_gateway_integration
        $integrationId = DB::table('organization_payment_gateway_integrations')->insertGetId([
            'bank_account_id' => $bankAccount->id,
            'payment_gateway_id' => $gateway->id,
            'organization_id' => $organization->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Step 5: Create configurations
        $apiKey = $this->generateApiKey();
        $signatureKey = $this->generateSignatureKey();

        $configurationId = DB::table('configurations')->insertGetId([
            'env' => 'testing', // Testing environment
            'config' => json_encode([
                'api_key' => $apiKey,
                'signature_key' => $signatureKey,
                'api_endpoint' => $endpoint,
            ]),
            'organization_id' => $organization->id,
            'payment_gateway_id' => $gateway->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $configuration = DB::table('configurations')->where('id', $configurationId)->first();
    

        // Step 6: Create UCN merchant via EcoBank API
        /*
        For testing
        */
        $forged = [
            'merchant_code' => '',
            'header_response' => '{
                "affiliateCode":"ETZ",
                "requestId":"1753273503996",
                "responseCode":"000",
                "responseMessage":"Success",
                "sourceCode":"ECOBANK_QR_API"
                }',
            'qr_code' => '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',
            'terminal_id' => '26561424',
            'terminal_name' => 'ShuleSoft High School',
            'secret_key'=>'I._avb?1ph',

        ];
        // $merchantData = $this->createMerchantQR($organization, $bankAccount->account_number);
        $merchantData = $forged;

        if (!is_array($merchantData)) {
            throw new Exception('Merchant creation failed: ' . $merchantData);
        }

        $datas = array_merge($merchantData, [
            'organization_payment_gateway_integration_id' => $integrationId,
            'created_at' => now()
        ]);

        // Step 7: Store merchant data
        $merchantId = DB::table('merchants')->insertGetId($datas);

        // Step 8: Update integration status to completed
        DB::table('organization_payment_gateway_integrations')
            ->where('id', $integrationId)
            ->update(['status' => 'active']);

        $merchant = DB::table('merchants')->where('id', $merchantId)->first();

        // Build and return gateway details
        $gatewayDetails = $gateway->toArray();
        $gatewayDetails['configuration'] = $configuration;
        $gatewayDetails['merchants'] = $merchant;

        return $gatewayDetails;
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
        // return [
        //     'header_response' => json_encode([
        //         'status' => 'SUCCESS',
        //         'status_code' => 201,
        //         'message' => 'UCN merchant and terminal successfully created',
        //         'request_id' => 'UCN-REQ-20260114-8F3A9C21'
        //     ]),
        //     'merchant_code' => 'UCN-MER-240114-000873',
        //     'qr_code' => '00020101021226280012UCN.TZ0119UCNMER2401140008735204581253038345802TZ5909UCN SHOP 6007DAR ES SALAAM6304A1B2',
        //     'terminal_id' => rand(100000, 999999),
        //     'terminal_name' => 'UCN Main POS Terminal',
        //     'secret_key' => 'ucn_sk_live_9F8A7C6E5D4B3A2C1E0F987654321ABC'
        // ];

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
        $organization = Organization::with(['country'])->find($id);

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
            'email' => 'sometimes|required|email|max:255|unique:organizations,email,' . $id,
            'currency' => 'sometimes|required|array|min:1',
            'country_id' => 'sometimes|required|exists:countries,id',
            'status' => 'sometimes|required|in:active,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $organization->update($validator->validated());
            $organization->load(['country']);

            Log::info('Organization updated successfully', [
                'organization_id' => $organization->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Organization updated successfully',
                'data' => $organization
            ], 200);
        } catch (Exception $e) {
            Log::error('Organization update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'organization_id' => $id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Organization update failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while updating the organization',
            ], 500);
        }
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

        try {
            $organization->delete();

            Log::info('Organization deleted successfully', [
                'organization_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Organization deleted successfully'
            ], 200);
        } catch (Exception $e) {
            Log::error('Organization deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'organization_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Organization deletion failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while deleting the organization',
            ], 500);
        }
    }
}

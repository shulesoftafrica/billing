<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class ClientRegistrationController extends Controller
{
    /**
     * Register a new client with their bank details
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'client_detail' => 'required|array',
            'client_detail.name' => 'required|string|max:255',
            'client_detail.phone' => 'required|string|max:20',
            'client_detail.address' => 'required|string|max:500',
            'client_detail.email' => 'required|email|max:255',
            'client_detail.username' => [
                'required',
                'string',
                'max:15',
                'regex:/^[a-zA-Z]+$/', // Only letters, no spaces
                function ($attribute, $value, $fail) {
                    $exists = DB::table('ucn.clients')
                        ->where('username', $value)
                        ->exists();

                    if ($exists) {
                        $fail('The username has already been taken.');
                    }
                },
            ],
            'api_endpoint' => 'required|url|max:255', // Required callback endpoint for testing
        ]);
        try {
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
            }

            $clientDetail = $request->input('client_detail');
            $apiEndpoint = $request->input('api_endpoint');

            // Fetch virtual account details from constant.virtual_accounts where status = 1
            $virtualAccount = DB::table('constant.virtual_accounts')
                ->where('status', 1)
                ->select('id', 'account_number', 'refer_bank_id')
                ->first();

            if (!$virtualAccount) {
                return response()->json([
                    'status' => 400,
                    'success' => false,
                    'message' => 'No active virtual account found for registration.',
                    'error_code' => 'NO_VIRTUAL_ACCOUNT'
                ], 400);
            }

            DB::beginTransaction();

            // Convert username to lowercase for consistency
            $username = strtolower($clientDetail['username']);

            // Insert client into ucn.clients table
            $clientId = DB::table('ucn.clients')->insertGetId([
                'name' => $clientDetail['name'],
                'phone' => $clientDetail['phone'],
                'address' => $clientDetail['address'],
                'email' => $clientDetail['email'],
                'username' => $username,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert bank details into ucn.bank_accounts table using virtual account data
            $bankAccountId = DB::table('ucn.bank_accounts')->insertGetId([
                'name' => $clientDetail['name'] . ' Virtual Account', // Use client name for bank account name
                'account_number' => $virtualAccount->account_number,
                'branch' => 'DAR ES SALAAM', // Fixed branch value
                'refer_bank_id' => $virtualAccount->refer_bank_id,
                'client_id' => $clientId,
                'schema_name' => $username,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Generate API key and signature key for the client
            $apiKey = $this->generateApiKey();
            $signatureKey = $this->generateSignatureKey();

            // Insert into ucn.configurations table
            $configurationId = DB::table('ucn.configurations')->insertGetId([
                'env' => 1, // Testing environment
                'api_key' => $apiKey,
                'signature_key' => $signatureKey,
                'api_endpoint' => $apiEndpoint,
                'client_id' => $clientId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert into ucn.env_config table
            $envConfigId = DB::table('ucn.env_config')->insertGetId([
                'client_id' => $clientId,
                'environment' => 1, // Testing environment
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // UPDATE virtual account status to 2 (assigned/used)
            DB::table('constant.virtual_accounts')->where('id', $virtualAccount->id)
                ->update(['status' => 2]);
            // Commit the transaction
            DB::commit();

            // Fetch the created records
            $client = DB::table('ucn.clients')->where('id', $clientId)->first();
            $bankAccount = DB::table('ucn.bank_accounts')
                ->join('constant.refer_banks', 'ucn.bank_accounts.refer_bank_id', '=', 'constant.refer_banks.id')
                ->where('ucn.bank_accounts.id', $bankAccountId)
                ->select(
                    'ucn.bank_accounts.*',
                    'constant.refer_banks.name as bank_name',
                    'constant.refer_banks.abbreviation as bank_abbreviation'
                )
                ->first();

            $configuration = DB::table('ucn.configurations')->where('id', $configurationId)->first();

            Log::info('Client registered successfully', [
                'client_id' => $clientId,
                'username' => $username,
                'bank_account_id' => $bankAccountId,
                'configuration_id' => $configurationId,
            ]);

            return response()->json([
                'status' => 201,
                'success' => true,
                'message' => 'Client registered successfully',
                'data' => [
                    'client' => [
                        'id' => $client->id,
                        'name' => $client->name,
                        'phone' => $client->phone,
                        'address' => $client->address,
                        'email' => $client->email,
                        'username' => $client->username,
                        'created_at' => $client->created_at,
                    ],
                    'bank_account' => [
                        'id' => $bankAccount->id,
                        'name' => $bankAccount->name,
                        'account_number' => $bankAccount->account_number,
                        'refer_bank_id' => $bankAccount->refer_bank_id,
                        'bank_name' => $bankAccount->bank_name,
                        'bank_abbreviation' => $bankAccount->bank_abbreviation,
                        'created_at' => $bankAccount->created_at,
                    ],
                    'configuration' => [
                        'environment' => 1, // Testing environment
                        'api_key' => $configuration->api_key,
                        'signature_key' => $configuration->signature_key,
                        'api_endpoint' => $configuration->api_endpoint,
                    ],
                    'notices' => [
                        'message' => 'For live environment setup, please contact shulesoft support.',
                        'email' => 'shulesoftafrica@gmail.com',
                        'phone' => '+255748771580'
                    ]
                ]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Client registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            // Determine appropriate status code based on exception type
            $statusCode = 500;
            $errorMessage = 'Client registration failed due to a system error';

            // Check for specific database errors
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $statusCode = 409; // Conflict
                $errorMessage = 'A record with this information already exists';
            } elseif (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $statusCode = 400; // Bad Request
                $errorMessage = 'Invalid reference to related data';
            } elseif (
                strpos($e->getMessage(), 'Connection refused') !== false ||
                strpos($e->getMessage(), 'database') !== false
            ) {
                $statusCode = 503; // Service Unavailable
                $errorMessage = 'Database service temporarily unavailable';
            }

            return response()->json([
                'status' => $statusCode,
                'success' => false,
                'message' => $errorMessage,
                'error_code' => 'REGISTRATION_ERROR',
                'error_details' => config('app.debug') ? $e->getMessage() : null
            ], $statusCode);
        }
    }

    /**
     * Query control numbers based on bank account ID
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function queryControlNumbers(Request $request)
    {
        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'limit' => 'integer|min:1|max:250',
                'bank_account_id' => [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) use ($request) {
                        $exists = DB::table('ucn.bank_accounts')
                            ->where('id', $value)
                            ->where('client_id', $request->client_id)  // check relationship
                            ->exists();

                        if (! $exists) {
                            $fail('The bank account does exists.');
                        }
                    }
                ],
                'client_id' => [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) {
                        $exists = DB::table('ucn.clients')
                            ->where('id', $value)
                            ->exists();

                        if (!$exists) {
                            $fail('The client does not exists.');
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
            }

            $bankAccountId = $request->input('bank_account_id');
            $page = $request->input('page', 1);
            $limit = min($request->input('limit', 50), 250); // Maximum 250
            $offset = ($page - 1) * $limit;

            // Find client_bank_integration based on bank_account_id
            $clientBankIntegration = DB::table('ucn.client_bank_integrations as cbi')
                ->where('cbi.bank_account_id', $bankAccountId)
                ->first();

            if (!$clientBankIntegration) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'The provided bank account is not yet integrated  .',
                    'error_code' => 'CLIENT_BANK_INTEGRATION_NOT_FOUND'
                ], 404);
            }

            // Get total count for pagination
            $totalCount = DB::table('ucn.control_numbers as cn')
                ->where('cn.client_bank_integration_id', $clientBankIntegration->id)
                ->count();

            // Fetch control numbers with user data
            $controlNumbers = DB::table('ucn.control_numbers as cn')
                ->join('ucn.users as u', 'cn.user_id', '=', 'u.id')
                ->join('ucn.clients as c', 'u.client_id', '=', 'c.id')
                ->where('cn.client_bank_integration_id', $clientBankIntegration->id)
                ->where('c.id', $request->client_id)
                ->select(
                    'cn.id as control_number_id',
                    'cn.reference as control_number',
                    'u.id as user_id',
                    'u.name as user_name',
                    'c.id as client_id',
                    'c.name as client_name',
                    'c.username as client_username',
                    'cn.qr_code'
                )
                ->orderBy('cn.created_at', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            // Calculate pagination data
            $totalPages = ceil($totalCount / $limit);

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Control numbers retrieved successfully',
                'data' => [
                    'control_numbers' => $controlNumbers->map(function ($item) {
                        return [
                            'user_name' => $item->user_name,
                            'user_id' => $item->user_id,
                            'control_number' => $item->control_number,
                            'control_number_qr_code' => $item->qr_code,
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $totalCount,
                        'total_pages' => $totalPages,
                        'has_more' => $page < $totalPages
                    ],
                    'bank_account_id' => $bankAccountId,
                    'client_bank_integration_id' => $clientBankIntegration->id
                ]
            ], 200);
        } catch (Exception $e) {
            Log::error('UCN Control Numbers Query Error', [
                'bank_account_id' => $request->input('bank_account_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'An error occurred while retrieving control numbers',
                'error_code' => 'SYSTEM_ERROR'
            ], 500);
        }
    }

    /**
     * Generate a secure API key
     *
     * @return string
     */
    private function generateApiKey()
    {
        return 'ucn_' . bin2hex(random_bytes(16)) . '_' . time();
    }

    /**
     * Generate a secure signature key
     *
     * @return string
     */
    private function generateSignatureKey()
    {
        return hash('sha256', uniqid() . random_bytes(32) . microtime(true));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\IntegrationRequest;
use Illuminate\Support\Facades\DB;
// use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;

class Partner extends Controller
{


    public $headerRequest;
    //sandbox
    // protected $password = '$2a$10$Wmame.Lh1FJDCB4JJIxtx.3SZT0dP2XlQWgj9Q5UAGcDLpB0yRYCC';
    // protected $username = 'iamaunifieddev103';
    // protected $labId = '0C/5F7QHdMv40uVGaTbt5nXdJOxi105k2LN9goPRqTUrwZrdYOYbvC0sJz7G0iT9';
    // public $baseUrl = 'https://developer.ecobank.com';
    // public $origin = 'developer.ecobank.com';


    //UAT
    // protected $username = 'ESICIALTD';
    // protected $password = '$2a$10$fftn7cjPAwFY93SqMPy2TOB15cZrSReypuGv885MUVnqAnw9umFNS';
    // protected $labId = 'zZ0YToOdzQLYbuJNGyci';
    // public $baseUrl = 'https://devtuatnew.ecobank.com/';
    // public $origin = 'developer.ecobank.com';

    //LIVE
    protected $username = 'ETZSHULESOFT';
    protected $password = '$2a$10$jdNZI4uiE86yRhcFNrBenOo0nBQji9zqy9IVa.roj0ST5EhlE4sVe';
    protected $labId = 'KmiqL3yCLf1V68oRQrIv';
    public $baseUrl = 'https://payservice.ecobank.com';
    public $origin = 'https://payservice.ecobank.com/PayPortal';
    public $callBackUrl = 'https://api.shulesoft.africa/api/ecobank/notification';

    public function __construct()
    {
        $this->middleware('auth');
        $this->headerRequest = [
            "requestId" => time() . random_int(1000, 9999),
            "affiliateCode" => "ETZ",
            "requestToken" => "/4mZF42iofzo7BDu0YtbwY6swLwk46Z91xItybhYwQGFpaZNOpsznL/9fca5LkeV",
            "sourceCode" => "ECOBANK_QR_API",
            "sourceChannelId" => "KANZAN",
            "requestType" => "CREATE_MERCHANT"
        ];
    }


    public function ApproveRequest()
    {
        $id = request('id');
        $request = IntegrationRequest::find($id);
        if (empty($request)) {
            abort(404);
        }
        if ($request->type_id == 9 && request('shulesoft_approved') == 1) {
            return $this->approveEcoBank($request);
        }
        $request->update(['shulesoft_approved' => request('shulesoft_approved')]);
        return redirect()->back()->with('success', 'success');
    }

    public function approveEcoBank($request)
    {

        $setting = DB::table('shulesoft.setting')->where('schema_name', $request->schema_name)->first();
        $account_number = DB::table('shulesoft.bank_accounts')->where('id', $request->bank_account_id)->value('number');

        if (empty($setting) || empty($account_number)) {
            return redirect()->back()->with('error', 'School Details or Account number not found');
        }

        $responseData = $this->createMerchantQR($setting, $account_number);
        Log::info($responseData);

        if (!isset($responseData) || !is_array($responseData)) {
            return redirect()->back()->with('error', $responseData);
        }
        try {
            DB::beginTransaction();
            $data = array_merge($responseData, [
                'bank_account_id' => $request->bank_account_id,
                'bank_account_integration_id' => $request->bank_accounts_integration_id,
                'schema_name' => $request->schema_name,
            ]);

            $request->update(['shulesoft_approved' => 1, 'bank_approved' => 1,  'approval_user_id' => Auth::user()->id]);
            DB::table('shulesoft.merchants')->insert($data);

            DB::commit();
            return redirect()->back()->with('success', 'Merchant created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function createMerchantQR($school, $account_number)
    {

        $referralCode = time() . rand(100, 999);
        // Concatenate all values into single string


        $body = [
            // 'headerRequest'=>$this->headerRequest,
            'merchantAddress' => $school->address,
            'merchantName' => $school->sname,
            'accountNumber' => $account_number,
            'terminalName' => $school->sname,
            'mobileNumber' => $school->phone,
            'email' => $school->email,
            'area' => 'DAR ES SALAAM',
            'city' => 'DAR ES SALAAM',
            'referralCode' => $referralCode,
            'mcc' => '6533',
            'dynamicQr' => 'Y',
            'callBackUrl' => $this->callBackUrl,
        ];
        // $headerRequestHashPayload = implode('', array_values($this->headerRequest));

        $bodyHAshPayload = implode('', array_values($body));
        //concatenate all values into single string
        // $hashPayload = $headerRequestHashPayload . $bodyHAshPayload;

        $secure_hash = $this->generateSecureHash($bodyHAshPayload);
        // Generate request body by rearranging it  and Append all key-value pairs from $body to $data then secure_hash
        $data = [
            'headerRequest' => $this->headerRequest,
        ];
        foreach ($body as $key => $value) {
            $data[$key] = $value;
        }

        $data['secure_hash'] = $secure_hash;
        $token = $this->createEcobankToken();
        if ($token == 0) {
            return redirect()->back()->with('error', 'Failed to get EcoBank token');
        }
        // API endpoint
        $url = $this->baseUrl . '/corporateapi/merchant/createqr';
        // Initialize curl
        $ch = curl_init($url);
        // Set curl options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: ' . $this->origin,
        ]);
        $response = curl_exec($ch);
        // Check for errors
        if (curl_errno($ch)) {
            return redirect()->back()->with('error', 'Request error has occured: ' . curl_error($ch));
        }
        // Close curl
        curl_close($ch);

        $responseData = json_decode($response, true);
        Log::info('EcoBank Merchant Creation Response: ' . $response);
        $this->logApiRequest('createMerchantQR', $data, $responseData);

        // Check if response is successful
        if (!empty($responseData['response_code']) && $responseData['response_code'] == 200) {
            $content = $responseData['response_content'];
            $header_response = json_encode($content['headerResponse']);
            // Insert into merchants table
            $returnResponse = [
                'merchant_code' => $content['merchantCode'],
                'header_response' => $header_response,
                'qr_code' => $content['qrCodeBase64'],
                'terminal_id' => $content['terminalId'],
                'terminal_name' => $content['terminalName'],
                'secret_key' => $content['secretKey'],

            ];
            return $returnResponse;
        } elseif (!empty($responseData['response_code']) && $responseData['response_code'] == 400 && !empty($responseData['errors'])) {
            $errorMsg = 'Failed to create merchant: ';
            foreach ($responseData['errors'] as $error) {
                $errorMsg .= $error . ' ';
            }
            // Handle specific error message
            Log::error('EcoBank Merchant Creation Error with response code: ' . $response);
            return $errorMsg;
        } else {
            // Handle error
            Log::error('EcoBank Merchant Creation Error: ' . $response);
            $errorMsg = !empty($responseData['response_message']) ?
                $responseData['response_message'] : 'Failed to create merchant: Unknown error occurred.';
            return $errorMsg;
        }
    }
    public function createEcobankToken()
    {
        $data = [
            'userId' => $this->username,
            'password' => $this->password
        ];
        $url = $this->baseUrl . '/corporateapi/user/token';
        // Initialize curl
        $ch = curl_init($url);

        // Set curl options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: ' . $this->origin,
        ]);
        $response = curl_exec($ch);
        // Check for errors
        if (curl_errno($ch)) {
            return redirect()->back()->with('error', 'Request error has occured: ' . curl_error($ch));
        }
        // Close curl
        curl_close($ch);

        $responseData = json_decode($response, true);
        if (isset($responseData['token'])) {
            return $responseData['token'];
        }
        return 0;
    }
    public function generateSecureHash($payload)
    {
        try {
            // Combine payload + lab key
            $data = $payload . $this->labId;

            // Get raw binary SHA-512 hash
            $binaryHash = hash('sha512', $data, true);

            // Convert binary hash to hex string (like Java's loop logic)
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
            // Log the error if needed, e.g., Log::error($e);
            return null;
        }
    }
    public function logApiRequest($source, $request, $response)
    {
        try {
            // Convert request and response to arrays if they are objects
            $requestData = is_array($request) ? $request : (array) $request;
            $responseData = is_array($response) ? $response : (array) $response;

            // Insert the log entry
            DB::table('api.ucn_requests')->insert([
                'source' => $source,
                'request' => json_encode($requestData),
                'response' => json_encode($responseData),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return true;

        } catch (Exception $e) {
            // Log the error but don't break the main functionality
            Log::error('Failed to log API request: ' . $e->getMessage(), [
                'source' => $source,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    public function logApi($data, $name = null)
    {

        $log = [
            'request_id' => $data['request_id'],
            'token' => $data['token'],
            'header' => $data['header'],
            'post_data' => $data['post_data'],
            'response' => $data['response'],
            'status_code' => $data['status_code'],
            'name' => $data['name'],
            'error' => $data['error'],
            'url' => $data['url']
        ];
        Log::channel('api_logs')->info('API Log Entry for student: ' . $name ?? 'Unknown', $log);
        return true;
    }
}

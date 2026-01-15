<?php

namespace App\Console\Commands;

use App\Http\Controllers\Partner;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class createEcobankTerminals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-ecobank-terminals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send a backgroud process to create ecobank terminals for merchants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $integratedBanks = DB::table('admin.integration_requests')
            ->where('bank_approved', 1)
            ->whereNot('schema_name', 'public')
            ->where('type_id', 9)
            ->get();

        if ($integratedBanks->isEmpty()) {
            return 0;
        }

        $partnerController = new Partner();
        $token = $partnerController->createEcobankToken();
        if ($token == null) {
            Log::error('Ecobank Terminal Token Error: Could not retrieve token.');
            return 0;
        }

        foreach ($integratedBanks as $integratedBank) {
            $merchant = DB::table('shulesoft.merchants')
                ->where('bank_account_integration_id', $integratedBank->bank_accounts_integration_id)
                ->first();

            if (empty($merchant)) {
                Log::warning('No merchant found for bank integration ID: ' . $integratedBank->bank_accounts_integration_id);
                continue; // Skip to the next integrated bank
            }

            $numberOfStudent = DB::table('shulesoft.student')
                ->where('schema_name', $integratedBank->schema_name)
                ->where('status', 1)
                ->count();

            $issuedStudent = DB::table('shulesoft.control_numbers')
                ->join('shulesoft.student', 'shulesoft.control_numbers.student_id', '=', 'shulesoft.student.student_id')
                ->where('shulesoft.control_numbers.schema_name', $integratedBank->schema_name)
                ->where('bank_accounts_integration_id', $integratedBank->bank_accounts_integration_id)
                ->where('shulesoft.student.status', 1)
                ->where('type_id', 9)
                ->count(DB::raw('DISTINCT control_numbers.student_id'));
            Log::info('Processing schema: ' . $integratedBank->schema_name . '. Total students: ' . $numberOfStudent . ', Issued terminals: ' . $issuedStudent);
            if ($issuedStudent >= $numberOfStudent) {
                continue; // All students have control numbers, skip to the next bank
            }

            // ---- MODIFICATION: Start Database Transaction and Locking ----
            DB::beginTransaction();
            try {
                // Select students who do not have a control number for this specific bank integration.
                // lockForUpdate() prevents other running scripts from selecting the same students, avoiding a race condition.
                $students = DB::table('shulesoft.student')
                    ->where('schema_name', $integratedBank->schema_name)
                    ->where('status', 1)
                    ->whereNotIn('student_id', function ($query) use ($integratedBank) {
                        $query->select('student_id')
                            ->from('shulesoft.control_numbers')
                            ->where('schema_name', $integratedBank->schema_name)
                            ->where('bank_accounts_integration_id', $integratedBank->bank_accounts_integration_id)
                            ->where('type_id', 9);
                    })
                    ->orderBy('student_id')
                    ->limit(10)
                    ->lock('FOR UPDATE SKIP LOCKED') // prevents other running scripts from selecting the same students
                    ->get();

                if ($students->isEmpty()) {
                    DB::commit();
                    continue;
                }

                foreach ($students as $student) {
                    $log = [];
                    // Use a nested try-catch to handle errors for a single student
                    // without rolling back the entire batch.
                    Log::warning('Processing student ID: ' . $student->student_id);
                    try {
                        $requestId = "ASTD" . $student->student_id;
                        $postData = [
                            "requestId" => $requestId,
                            "affiliateCode" => $partnerController->headerRequest['affiliateCode'],
                            "merchantCode" => $merchant->merchant_code ?? "422634683",
                            "terminalMobileNo" => "0765406008",
                            "terminalName" => $student->name,
                            "terminalEmail" => "shulesoftcompany@gmail.com",
                            "productCode" => $student->student_id . time(),
                            "dynamicQr" => "Y",
                            "callBackUrl" => $partnerController->callBackUrl,
                        ];

                        $payloadPart = implode('', array_values($postData));
                        $secureHash = $partnerController->generateSecureHash($payloadPart);
                        if ($secureHash == null) {
                            Log::error('Ecobank Terminal Secure Hash Error for student ID: ' . $student->student_id);
                            continue; // Skip this student
                        }

                        $postData['secureHash'] = $secureHash;
                        $url = $partnerController->baseUrl . '/corporateapi/merchant/createaddQr';

                        // Initialize and execute curl
                        $curl = curl_init($url);
                        $headers = [
                            'Authorization: Bearer ' . $token,
                            'Content-Type: application/json',
                            'Accept: application/json',
                            'Origin: ' . $partnerController->origin,
                        ];
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                        $response = curl_exec($curl);
                        $curlError = curl_error($curl);
                        curl_close($curl);
                        $log['token'] = $token;
                        $log['response'] = $response;
                        $log['status_code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        $log['name'] = 'createEcobankTerminals';
                        $log['post_data'] = $postData;
                        $log['header'] = $headers;
                        $log['request_id'] = $requestId;
                        $log['url'] = $url;
                        $log['error'] = $curlError;
                        $partnerController->logApi($log, $student->name);

                        // Handle cURL errors
                        if ($curlError) {
                            $errorResponse = ['curl_error' => $curlError];
                            $partnerController->logApiRequest('createEcobankTerminals', $postData, $errorResponse);
                            Log::error('Ecobank Terminal cURL Error for student ID ' . $student->student_id . ': ' . $curlError);
                            continue; // Skip this student
                        }

                        $responseData = json_decode($response, true);
                        
                        // Log API request and response
                        $partnerController->logApiRequest('createEcobankTerminals', $postData, $responseData);

                        if (isset($responseData['response_code']) && $responseData['response_code'] === 200) {
                            $content = $responseData['response_content'];
                            $data = [
                                'student_id' => $student->student_id,
                                'bank_accounts_integration_id' => $integratedBank->bank_accounts_integration_id,
                                'schema_name' => $integratedBank->schema_name,
                                'type_id' => 9,
                                'qr_code'  => $content['qrBase64String'],
                                'reference' => $content['terminalId'],
                                'header_response' => json_encode($content['headerResponse'])
                            ];

                            DB::table('shulesoft.control_numbers')->insert($data);
                        } else {
                            Log::error('Ecobank Terminal API Error for student ID ' . $student->student_id . ': ' . $response);
                        }
                    } catch (\Throwable $studentError) {
                        // Log exception with request data for debugging
                        $exceptionResponse = [
                            'error' => $studentError->getMessage()
                        ];
                        $log['token'] = isset($token) ? $token : '';
                        $log['response'] = isset($response) ? $response : '';
                        $log['status_code'] = 500;
                        $log['name'] = 'createEcobankTerminals';
                        $log['post_data'] = isset($postData) ? $postData : '';
                        $log['header'] = isset($headers) ? $headers : '';
                        $log['request_id'] = isset($requestId) ? $requestId : '';
                        $log['url'] = isset($url) ? $url : '';
                        $log['error'] = $studentError->getMessage();
                        $partnerController->logApi($log, $student->name);
                        if (isset($postData)) {
                            $partnerController->logApiRequest('createEcobankTerminals', $postData, $exceptionResponse);
                        }
                        Log::error('An exception occurred for student ID ' . $student->student_id . ': ' . $studentError->getMessage());
                        // Continue to the next student
                    }
                }
                DB::commit();

            } catch (\Throwable $e) {
                Log::critical('Control Number Generation Failed for schema ' . $integratedBank->schema_name . '. Transaction rolled back. Error: ' . $e->getMessage());
                DB::rollBack();
            }
        }

        return 0;
    }
}

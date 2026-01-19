<?php

/** REFER /docs/UCN_Transaction_Notifications.txt FOR DOCUMENTATION */

namespace App\Console\Commands;

use App\Traits\ApiRequestLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendUcnTransactionNotifications extends Command
{
    use ApiRequestLogger;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ucn:send-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send UCN transaction notifications to the target API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // \\ LOGIC CHANGED, NOW WE DETERMINE BANK INTEGRATION ENVIRONMENT FROM THE DATABASE 
        // $env = config('app.eco_bank_environment');

        // if (!$env) {
        //     $this->error('ECO_BANK_ENVIRONMENT is not set in your .env file.');
        //     Log::error('ECO_BANK_ENVIRONMENT is not set in your .env file.');
        //     return 1;
        // }

        // $config = DB::table('admin.ucn_configration')->where('env', $env)->first();

        // if (!$config) {
        //     $this->error("UCN configuration for environment '{$env}' not found.");
        //     Log::error("UCN configuration for environment '{$env}' not found.");
        //     return 1;
        // }

        $transactions = DB::table('shulesoft.ucn_transactions_logs')
            ->where('status', '!=', 1)
            ->where('send_trial', '<=', 2)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('No pending transactions to notify.');
            Log::info('No pending transactions to notify.');
            return 0;
        }
        $agentConfig = $transactions->pluck('refer_bank_id')->unique()->toArray();

        //get agent config
        $agentConfig = DB::table('admin.ucn_env_config  as ev')
            ->join('admin.ucn_configration as cf', function ($join) {
                $join->on('cf.refer_bank_id', '=', 'ev.refer_bank_id')
                    ->on('cf.env', '=', 'ev.environment');
            })
            ->whereIn('cf.refer_bank_id', $agentConfig)
            ->select('cf.*')
            ->get()
            ->keyBy('refer_bank_id')
            ->toArray();

        foreach ($transactions as $transaction) {
            $config = $agentConfig[$transaction->refer_bank_id] ?? null;
            if (!$config) {
                $this->error("UCN configuration for refer_bank_id '{$transaction->refer_bank_id}' not found.");
                Log::error("UCN configuration for refer_bank_id '{$transaction->refer_bank_id}' not found.");
                continue;
            }
            if ($this->processTransaction($transaction, (object)$config) === false) {
                $this->error("Failed to process transaction ID: {$transaction->id}");
                Log::error("Failed to process transaction ID: {$transaction->id}");
            } else {
                $this->info("Transaction ID: {$transaction->id} processed successfully.");
                Log::info("Transaction ID: {$transaction->id} processed successfully.");
            }
        }
        return 0;
    }

    private function processTransaction($transaction, $config)
    {
        try {
            $paymentData = DB::table('shulesoft.control_numbers as cn')
                ->join('shulesoft.student as s', 'cn.student_id', '=', 's.student_id')
                ->leftJoin('shulesoft.student_parents as sp', 's.student_id', '=', 'sp.student_id')
                ->leftJoin('shulesoft.users as u', 'sp.parent_id', '=', 'u.sid')
                ->join('admin.integration_requests as ir', 'cn.bank_accounts_integration_id', '=', 'ir.bank_accounts_integration_id')
                ->join('admin.clients as c', 'ir.client_id', '=', 'c.id')
                ->where('cn.reference', $transaction->ec_terminal_id)
                ->select('s.name as payer_name', 'u.phone as payer_phone', 'ir.client_code', 'c.name as client_name')
                ->first();

            $requestBody = [
                "transaction_id" => $transaction->ec_transaction_id,
                "control_number" => $transaction->ec_terminal_id,
                "payer_name" => $paymentData->payer_name ?? null,
                "payer_phone" => $paymentData->payer_phone ?? null,
                "amount_paid" => $transaction->ec_amount_paid,
                "payment_date_time" => now()->toIso8601String(),
                "client_name" => $paymentData->client_name ?? null,
                "client_code" => $paymentData->client_code ?? null,
                "narration" => 'Payment for ' . ($paymentData->payer_name ?? 'N/A'),
                "settlement_reference" => $transaction->payment_ref,
            ];

            // $signature1 = hash_hmac('sha256', json_encode($requestBody, JSON_UNESCAPED_SLASHES), $config->signature_key);
            $signature = $this->generateSignature($requestBody, $config->signature_key);


            $requestBody['signature'] = $signature;
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config->api_key,
                'Content-Type' => 'application/json',
            ])->post($config->api_endpoint . '/payment-notification', $requestBody);
            // log the response for debugging on the database
            DB::table('shulesoft.ucn_transactions_logs')
                ->where('id', $transaction->id)
                ->update([
                    'last_response' =>  $response->body(),
                    'last_attempted_at' => now(),
                ]);

            if ($response->successful() && $response->json('status') === 'success') {
                DB::table('shulesoft.ucn_transactions_logs')
                    ->where('id', $transaction->id)
                    ->update([
                        'status' => 1,
                        'sattlement_transaction_id' => $response->json('settlement_transaction_id')
                    ]);
            } else {
                $this->incrementSendTrial($transaction->id);
            }
            $this->logApiRequest(
                'amana_notification',
                $requestBody,
                $response->body()
            );
            return true;
        } catch (\Exception $e) {
            Log::error("Error processing UCN transaction ID: {$transaction->id} - " . $e->getMessage());
            $this->incrementSendTrial($transaction->id);
            $this->logApiRequest(
                'amana_notification',
                $requestBody,
                ['error' => $e->getMessage()]
            );
            return false;
        }
    }

    private function incrementSendTrial($transactionId)
    {
        DB::table('shulesoft.ucn_transactions_logs')
            ->where('id', $transactionId)
            ->increment('send_trial');
    }

    function generateSignature(array $data, string $secretKey): string
    {
        // Remove signature if present
        unset($data['signature']);

        // Sort by keys
        ksort($data);

        // Encode to JSON
        $jsonString = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Generate HMAC SHA256 hash
        return hash_hmac('sha256', $jsonString, $secretKey);
    }
}

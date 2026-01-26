<?php

namespace App\Services;

use App\Models\ControlNumber;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\Configuration;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UNCPaymentService
{
    /**
     * Process UNC payment webhook
     *
     * @param array $webhookData
     * @return array
     */
    public function processWebhook(array $webhookData): array
    {
        try {
            // Validate required fields
            $this->validateWebhookData($webhookData);

            $ecTerminalId = $webhookData['ec_termianl_id'];
            $cbaReferenceNo = $webhookData['cba_reference_no'];
            $amountPaid = $webhookData['ec_amount_paid'];

            return DB::transaction(function () use ($webhookData, $ecTerminalId, $cbaReferenceNo, $amountPaid) {
                // Step 1: Get control number by reference
                $controlNumber = ControlNumber::with('organizationPaymentGatewayIntegration.paymentGateway')
                    ->where('reference', $ecTerminalId)
                    ->first();

                if (!$controlNumber) {
                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '404',
                        'Control number not found for reference: ' . $ecTerminalId
                    );
                }

                // Check for duplicate transaction
                $existingPayment = Payment::where('gateway_reference', $cbaReferenceNo)->first();
                if ($existingPayment) {
                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '409',
                        'Duplicate transaction'
                    );
                }

                // Step 2: Get product details
                $product = Product::find($controlNumber->product_id);
                if (!$product) {
                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '404',
                        'Product not found'
                    );
                }

                // Step 3: Get customer details
                $customer = Customer::find($controlNumber->customer_id);
                if (!$customer) {
                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '404',
                        'Customer not found'
                    );
                }

                // Step 4: Get payment gateway from organization payment gateway integration
                $integration = $controlNumber->organizationPaymentGatewayIntegration;
                if (!$integration) {
                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '404',
                        'Payment gateway integration not found'
                    );
                }

                $paymentGateway = $integration->paymentGateway;
                if (!$paymentGateway) {
                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '404',
                        'Payment gateway not found'
                    );
                }

                $organizationId = $integration->organization_id;

                // Step 5: Record payment
                $payment = Payment::create([
                    'invoice_id' => null,
                    'gateway_id' => $paymentGateway->id,
                    'customer_id' => $customer->id,
                    'amount' => $amountPaid,
                    'status' => 'pending',
                    'gateway_reference' => $cbaReferenceNo,
                    'paid_at' => Carbon::now(),
                ]);

                // Step 6: Get configuration for organization and payment gateway + know if service or prodct paid is subscription or usage
                $configuration = Configuration::where('organization_id', $organizationId)
                    ->where('payment_gateway_id', $paymentGateway->id)
                    ->first();

                if (!$configuration || !$configuration->config) {
                    $payment->update(['status' => 'failed']);
                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '500',
                        'API endpoint not configured for this organization and payment gateway'
                    );
                }

                $notify_config = json_decode($configuration->config, true);
                if (!isset($notify_config['api_endpoint']) || empty($notify_config['api_endpoint'])) {
                    $payment->update(['status' => 'failed']);
                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '500',
                        'API endpoint not configured for this organization and payment gateway'
                    );
                }

                if (!isset($notify_config['signature_key']) || empty($notify_config['signature_key'])) {
                    $payment->update(['status' => 'failed']);
                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '500',
                        'Signature key not configured for this organization and payment gateway'
                    );
                }

                if ($product->product_type_id != 1) {
                    // Check subscription status for non-standard products
                    $pricePlanIds = $product->pricePlans()->pluck('id');
                    $subscription = Subscription::where('customer_id', $customer->id)
                        ->whereIn('price_plan_id', $pricePlanIds)
                        ->where('status', 'pending')
                        ->first();

                    if (!$subscription) {
                        return $this->failureResponse(
                            $cbaReferenceNo,
                            '404',
                            'No pending subscription found for this product'
                        );
                    }

                    if (!$subscription->pricePlan) {
                        $payment->update(['status' => 'failed']);
                        return $this->failureResponse(
                            $cbaReferenceNo,
                            '500',
                            'Price plan not found for subscription'
                        );
                    }

                    $days_to_add = $subscription->pricePlan->days_to_add;
                    $endDate = match ($days_to_add) {
                        'daily'         => Carbon::now()->addDay(),
                        'weekly'        => Carbon::now()->addWeek(),
                        'monthly'       => Carbon::now()->addMonth(),
                        'quarterly'     => Carbon::now()->addMonths(3),
                        'semi_annually' => Carbon::now()->addMonths(6),
                        'yearly'        => Carbon::now()->addYear(),
                        default         => Carbon::now(),
                    };

                    $subscription->update([
                        'status'   => 'active',
                        'end_date' => $endDate,
                    ]);
                }

                // Step 7: Create API request object
                $notificationPayload = [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ],
                    'payment_gateway' => [
                        'name' => $paymentGateway->name,
                        'type' => $paymentGateway->type,
                    ],
                    'payment' => [
                        'reference' => $ecTerminalId,
                        'transaction_id' => $cbaReferenceNo,
                        'amount' => $amountPaid,
                        'currency' => $webhookData['ec_ccy'] ?? 'TZS',
                    ],
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                    ],
                ];

                // Generate signature for API security
                $signature = $this->generateSignature($notificationPayload, $notify_config['signature_key']);
                $notificationPayload['signature'] = $signature;

                // Step 8: Send API request to organization endpoint from configuration
                try {
                    // Initialize cURL
                    $ch = curl_init($notify_config['api_endpoint']);

                    // Set cURL options
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notificationPayload));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Authorization: Bearer ' . $notify_config['api_key'],
                        'Content-Type: application/json',
                    ]);

                    // Execute request
                    $responseBody = curl_exec($ch);
                    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);

                    // Handle cURL errors
                    if ($responseBody === false) {
                        throw new \Exception('cURL error: ' . $curlError);
                    }

                    $isSuccessful = $statusCode >= 200 && $statusCode < 300;
                    $responseData = json_decode($responseBody, true) ?? [];

                    // Step 9: Update payment status based on response
                    if ($isSuccessful && isset($responseData['success']) && $responseData['success'] === true) {
                        $payment->update(['status' => 'success']);

                        Log::info('UNC payment processed successfully', [
                            'payment_id' => $payment->id,
                            'reference' => $ecTerminalId,
                            'transaction_id' => $cbaReferenceNo,
                        ]);

                        // Step 10: Return success response
                    } else {
                        $payment->update(['status' => 'failed']);

                        Log::error('Organization endpoint returned error', [
                            'payment_id' => $payment->id,
                            'response' => $responseData,
                        ]);
                    }
                    return $this->successResponse($cbaReferenceNo);
                } catch (\Exception $e) {
                    $payment->update(['status' => 'failed']);

                    Log::error('Failed to notify organization endpoint', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                    ]);

                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '503',
                        'Failed to notify organization: ' . $e->getMessage()
                    );
                }
            });
        } catch (\Exception $e) {
            Log::error('UNC webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $webhookData,
            ]);

            return $this->failureResponse(
                $webhookData['cba_reference_no'] ?? 'UNKNOWN',
                '500',
                'Internal server error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Validate webhook data
     *
     * @param array $data
     * @throws \Exception
     */
    private function validateWebhookData(array $data): void
    {
        $requiredFields = [
            'ec_termianl_id',
            'cba_reference_no',
            'ec_amount_paid',
            'responseCode',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Check if payment was successful from gateway
        if ($data['responseCode'] !== '000') {
            throw new \Exception("Payment not approved. Response code: {$data['responseCode']}");
        }
    }

    /**
     * Create success response
     *
     * @param string $cbaReferenceNo
     * @return array
     */
    private function successResponse(string $cbaReferenceNo): array
    {
        return [
            'externalpaymentref' => $cbaReferenceNo,
            'responseCode' => '000',
            'responseMessage' => 'Approved or completed successfully',
        ];
    }

    /**
     * Create failure response
     *
     * @param string $cbaReferenceNo
     * @param string $code
     * @param string $message
     * @return array
     */
    private function failureResponse(string $cbaReferenceNo, string $code, string $message): array
    {
        return [
            'externalpaymentref' => $cbaReferenceNo,
            'responseCode' => $code,
            'responseMessage' => $message,
        ];
    }

    /**
     * Generate signature for API security
     *
     * @param array $data
     * @param string $secretKey
     * @return string
     */
    private function generateSignature(array $data, string $secretKey): string
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

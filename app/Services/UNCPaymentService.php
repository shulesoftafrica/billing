<?php

namespace App\Services;

use App\Models\ControlNumber;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use GuzzleHttp\Client;

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
                $controlNumber = ControlNumber::where('reference', $ecTerminalId)->first();

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

                // Step 4: Get payment gateway
                $paymentGateway = PaymentGateway::where('type', 'control_number')
                    ->where('active', true)
                    ->first();

                if (!$paymentGateway) {
                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '500',
                        'Payment gateway not configured'
                    );
                }

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

                // Step 6: Find organization endpoint
                $organization = $customer->organization;
                if (!$organization || !$organization->endpoint) {
                    $payment->update(['status' => 'failed']);
                    return $this->failureResponse(
                        $cbaReferenceNo,
                        '500',
                        'Organization endpoint not configured'
                    );
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

                // Step 8: Send API request to organization endpoint
                try {
                    $client = new Client();
                    $response = $client->post($organization->endpoint, [
                        'json' => $notificationPayload,
                        'timeout' => 30,
                    ]);

                    $statusCode = $response->getStatusCode();
                    $isSuccessful = $statusCode >= 200 && $statusCode < 300;
                    $responseData = json_decode($response->getBody()->getContents(), true) ?? [];

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
}

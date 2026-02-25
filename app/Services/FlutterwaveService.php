<?php

namespace App\Services;

use App\Models\PaymentGateway;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class FlutterwaveService
{
    protected $client;
    protected $gateway;
    protected $baseUrl = 'https://api.flutterwave.com/v3';

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => true,
        ]);
        
        // Load Flutterwave gateway configuration
        $this->gateway = PaymentGateway::where('name', 'Flutterwave')
            ->where('active', true)
            ->first();
    }

    /**
     * Initialize payment and get hosted payment link
     *
     * @param array $paymentData
     * @return array
     */
    public function initializePayment(array $paymentData): array
    {
        try {
            // Check if gateway is configured
            if (!$this->gateway) {
                Log::warning('Flutterwave gateway not configured or inactive');
                return [
                    'success' => false,
                    'error' => 'Flutterwave gateway not configured',
                ];
            }

            $config = $this->gateway->config;

            // Validate required configuration
            if (empty($config['secret_key'])) {
                Log::error('Flutterwave secret key not configured');
                return [
                    'success' => false,
                    'error' => 'Flutterwave secret key not configured',
                ];
            }

            // Validate required payment data
            $this->validatePaymentData($paymentData);

            // Prepare payment payload
            $payload = [
                'tx_ref' => $paymentData['tx_ref'],
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'TZS',
                'redirect_url' => $paymentData['redirect_url'] ?? config('app.url') . '/payment/callback',
                'customer' => [
                    'email' => $paymentData['customer']['email'],
                    'name' => $paymentData['customer']['name'],
                    'phonenumber' => $paymentData['customer']['phone'] ?? null,
                ],
                'customizations' => [
                    'title' => $paymentData['title'] ?? 'Payment',
                    'description' => $paymentData['description'] ?? 'Payment for invoice',
                    'logo' => $paymentData['logo'] ?? null,
                ],
                'meta' => $paymentData['meta'] ?? [],
            ];

            // Make API request
            $response = $this->client->post($this->baseUrl . '/payments', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $config['secret_key'],
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            // Check response status
            if ($responseData['status'] === 'success' && !empty($responseData['data']['link'])) {
                Log::info('Flutterwave payment initialized successfully', [
                    'tx_ref' => $paymentData['tx_ref'],
                    'payment_link' => $responseData['data']['link'],
                ]);

                return [
                    'success' => true,
                    'data' => [
                        'payment_link' => $responseData['data']['link'],
                        'tx_ref' => $paymentData['tx_ref'],
                        'expires_at' => now()->addHours(24)->toIso8601String(),
                    ],
                ];
            }

            // API returned error
            Log::error('Flutterwave payment initialization failed', [
                'tx_ref' => $paymentData['tx_ref'],
                'response' => $responseData,
            ]);

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Payment initialization failed',
            ];

        } catch (GuzzleException $e) {
            Log::error('Flutterwave API error', [
                'error' => $e->getMessage(),
                'tx_ref' => $paymentData['tx_ref'] ?? 'N/A',
            ]);

            return [
                'success' => false,
                'error' => 'Payment gateway connection error',
            ];
        } catch (\Exception $e) {
            Log::error('Unexpected error during payment initialization', [
                'error' => $e->getMessage(),
                'tx_ref' => $paymentData['tx_ref'] ?? 'N/A',
            ]);

            return [
                'success' => false,
                'error' => 'An unexpected error occurred',
            ];
        }
    }

    /**
     * Verify payment transaction
     *
     * @param string $transactionId
     * @return array
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            if (!$this->gateway) {
                return [
                    'success' => false,
                    'error' => 'Flutterwave gateway not configured',
                ];
            }

            $config = $this->gateway->config;

            // Make verification request
            $response = $this->client->get($this->baseUrl . '/transactions/' . $transactionId . '/verify', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $config['secret_key'],
                    'Content-Type' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($responseData['status'] === 'success' && $responseData['data']['status'] === 'successful') {
                Log::info('Flutterwave payment verified successfully', [
                    'transaction_id' => $transactionId,
                    'amount' => $responseData['data']['amount'],
                ]);

                return [
                    'success' => true,
                    'data' => [
                        'transaction_id' => $responseData['data']['id'],
                        'tx_ref' => $responseData['data']['tx_ref'],
                        'amount' => $responseData['data']['amount'],
                        'currency' => $responseData['data']['currency'],
                        'status' => $responseData['data']['status'],
                        'payment_type' => $responseData['data']['payment_type'] ?? null,
                        'charged_amount' => $responseData['data']['charged_amount'] ?? null,
                    ],
                ];
            }

            return [
                'success' => false,
                'error' => 'Payment verification failed or payment not successful',
                'data' => $responseData['data'] ?? null,
            ];

        } catch (GuzzleException $e) {
            Log::error('Flutterwave verification API error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => false,
                'error' => 'Payment verification error',
            ];
        } catch (\Exception $e) {
            Log::error('Unexpected error during payment verification', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => false,
                'error' => 'An unexpected error occurred',
            ];
        }
    }

    /**
     * Validate payment data
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    protected function validatePaymentData(array $data): void
    {
        $required = ['tx_ref', 'amount', 'customer'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Validate customer data
        if (empty($data['customer']['email']) || empty($data['customer']['name'])) {
            throw new \InvalidArgumentException('Customer email and name are required');
        }

        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new \InvalidArgumentException('Invalid amount');
        }
    }

    /**
     * Get gateway configuration
     *
     * @return array|null
     */
    public function getConfig(): ?array
    {
        return $this->gateway ? $this->gateway->config : null;
    }

    /**
     * Check if gateway is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->gateway && $this->gateway->active;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentGatewayTestController extends Controller
{
    /**
     * Test payment gateway connectivity
     * GET /api/payment-gateways/test-connection
     */
    public function testConnection(Request $request): JsonResponse
    {
        $request->validate([
            'gateway_id' => 'required|exists:payment_gateways,id'
        ]);

        try {
            $gateway = PaymentGateway::findOrFail($request->gateway_id);
            $testResult = $this->performConnectivityTest($gateway);

            return response()->json([
                'success' => true,
                'gateway' => [
                    'id' => $gateway->id,
                    'name' => $gateway->name,
                    'type' => $gateway->type
                ],
                'test_result' => $testResult
            ], 200);

        } catch (\Exception $e) {
            Log::error('Payment gateway connectivity test failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Connectivity test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test all active payment gateways
     * GET /api/payment-gateways/test-all-connections
     */
    public function testAllConnections(): JsonResponse
    {
        try {
            $gateways = PaymentGateway::where('active', true)->get();
            $results = [];

            foreach ($gateways as $gateway) {
                $testResult = $this->performConnectivityTest($gateway);
                $results[] = [
                    'gateway' => [
                        'id' => $gateway->id,
                        'name' => $gateway->name,
                        'type' => $gateway->type
                    ],
                    'test_result' => $testResult
                ];
            }

            return response()->json([
                'success' => true,
                'total_gateways' => count($results),
                'results' => $results
            ], 200);

        } catch (\Exception $e) {
            Log::error('Payment gateway connectivity test-all failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Connectivity test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform connectivity test based on gateway type
     */
    private function performConnectivityTest(PaymentGateway $gateway): array
    {
        $startTime = microtime(true);

        try {
            switch ($gateway->type) {
                case 'stripe':
                    return $this->testStripeConnectivity($gateway);
                
                case 'flutterwave':
                    return $this->testFlutterWaveConnectivity($gateway);
                
                case 'control_number':
                    return $this->testControlNumberConnectivity($gateway);
                
                default:
                    return $this->testGenericConnectivity($gateway);
            }

        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);

            return [
                'status' => 'failed',
                'response_time_ms' => $responseTime,
                'error' => $e->getMessage(),
                'tested_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Test Stripe connectivity
     */
    private function testStripeConnectivity(PaymentGateway $gateway): array
    {
        $startTime = microtime(true);

        // Test Stripe API connectivity
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ($gateway->config['secret_key'] ?? 'sk_test_'),
        ])->get('https://api.stripe.com/v1/account');

        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2);

        if ($response->successful()) {
            return [
                'status' => 'connected',
                'response_time_ms' => $responseTime,
                'api_version' => $response->header('Stripe-Version'),
                'account_id' => $response->json()['id'] ?? null,
                'tested_at' => now()->toISOString()
            ];
        } else {
            return [
                'status' => 'failed',
                'response_time_ms' => $responseTime,
                'error' => 'HTTP ' . $response->status() . ': ' . $response->body(),
                'tested_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Test FlutterWave connectivity
     */
    private function testFlutterWaveConnectivity(PaymentGateway $gateway): array
    {
        $startTime = microtime(true);

        // Test FlutterWave API connectivity
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ($gateway->config['secret_key'] ?? 'FLWSECK_TEST-'),
        ])->get('https://api.flutterwave.com/v3/account');

        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'status' => 'connected',
                'response_time_ms' => $responseTime,
                'account_name' => $data['data']['account_name'] ?? null,
                'account_number' => $data['data']['account_number'] ?? null,
                'tested_at' => now()->toISOString()
            ];
        } else {
            return [
                'status' => 'failed',
                'response_time_ms' => $responseTime,
                'error' => 'HTTP ' . $response->status() . ': ' . $response->body(),
                'tested_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Test control number gateway connectivity
     */
    private function testControlNumberConnectivity(PaymentGateway $gateway): array
    {
        $startTime = microtime(true);

        // Test control number API connectivity
        $apiEndpoint = $gateway->config['api_endpoint'] ?? null;
        if (!$apiEndpoint) {
            throw new \Exception('API endpoint not configured');
        }

        $response = Http::timeout(30)->get($apiEndpoint . '/health');

        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2);

        if ($response->successful()) {
            return [
                'status' => 'connected',
                'response_time_ms' => $responseTime,
                'api_endpoint' => $apiEndpoint,
                'tested_at' => now()->toISOString()
            ];
        } else {
            return [
                'status' => 'failed',
                'response_time_ms' => $responseTime,
                'error' => 'HTTP ' . $response->status() . ': ' . $response->body(),
                'api_endpoint' => $apiEndpoint,
                'tested_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Test generic gateway connectivity
     */
    private function testGenericConnectivity(PaymentGateway $gateway): array
    {
        $startTime = microtime(true);

        // Generic connectivity test
        $apiEndpoint = $gateway->config['api_endpoint'] ?? null;
        if (!$apiEndpoint) {
            throw new \Exception('API endpoint not configured');
        }

        $response = Http::timeout(10)->get($apiEndpoint);

        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2);

        return [
            'status' => $response->successful() ? 'connected' : 'failed',
            'response_time_ms' => $responseTime,
            'http_status' => $response->status(),
            'api_endpoint' => $apiEndpoint,
            'tested_at' => now()->toISOString()
        ];
    }
}
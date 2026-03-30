<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Services\Flutterwave\Models\CreateCustomerRequest;
use App\Services\Flutterwave\Models\CreateOrderRequest;
use App\Services\Flutterwave\Models\CreatePaymentMethodRequest;
use App\Services\Flutterwave\Models\CustomerResponse;
use App\Services\Flutterwave\Models\OrderResponse;
use App\Services\Flutterwave\Models\PaymentMethodResponse;
use App\Services\Flutterwave\Models\UpdateCustomerRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class FlutterwaveService
{
    private const DEFAULT_TIMEOUT = 30;

    private ?PaymentGateway $gateway;

    public function __construct()
    {
        $this->gateway = PaymentGateway::whereRaw('LOWER(name) = ?', ['flutterwave'])
            ->where('active', true)
            ->first();
    }

    /**
     * -----------------------------
     * Customers Management
     * -----------------------------
     */

    /**
     * Create a customer.
     * Endpoint: POST /customers
     * Docs: https://developer.flutterwave.com/reference/customers_create
     */
    public function createCustomer(CreateCustomerRequest|array $request, ?string $idempotencyKey = null): CustomerResponse
    {
        $payload = $request instanceof CreateCustomerRequest ? $request->toArray() : $request;


        try {
            $response = $this->request(
                method: 'POST',
                url: $this->resolveApiEndpoint('/customers'),
                body: $payload,
                headers: [
                    'X-Idempotency-Key' => $idempotencyKey ?? $this->generateIdempotencyKey(),
                ]
            );
            return CustomerResponse::fromArray($this->extractDataOrFail($response->success, $response->message, $response->data));
        } catch (Throwable $exception) {
            Log::error('Flutterwave createCustomer failed', [
                'error' => $exception->getMessage(),
                'payload' => $payload,
            ]);

            throw new RuntimeException('Unable to create Flutterwave customer: ' . $exception->getMessage(), previous: $exception);
        }
    }

    /**
     * Retrieve a customer.
     * Endpoint: GET /customers/{id}
     * Docs: https://developer.flutterwave.com/reference/customers_get
     */
    public function getCustomer(string $customerId): CustomerResponse
    {
        $response = $this->request('GET', $this->resolveApiEndpoint("/customers/{$customerId}"));

        return CustomerResponse::fromArray($this->extractDataOrFail($response->success, $response->message, $response->data));
    }

    /**
     * List customers.
     * Endpoint: GET /customers?page={page}&size={size}
     * Docs: https://developer.flutterwave.com/reference/customers_list
     *
     * @return array{data: array<int, CustomerResponse>, meta: array|null, message: string}
     */
    public function listCustomers(int $page = 1, int $size = 10): array
    {
        $response = $this->request('GET', $this->resolveApiEndpoint('/customers'), [
            'page' => $page,
            'size' => $size,
        ]);

        $items = array_map(
            static fn(array $customer): CustomerResponse => CustomerResponse::fromArray($customer),
            is_array($response->data) ? $response->data : []
        );

        return [
            'data' => $items,
            'meta' => $response->meta,
            'message' => $response->message,
        ];
    }

    /**
     * Update a customer.
     * Endpoint: PUT /customers/{id}
     * Docs: https://developer.flutterwave.com/reference/customers_put
     */
    public function updateCustomer(string $customerId, UpdateCustomerRequest|array $request): CustomerResponse
    {
        $payload = $request instanceof UpdateCustomerRequest ? $request->toArray() : $request;

        $response = $this->request(
            method: 'PUT',
            url: $this->resolveApiEndpoint("/customers/{$customerId}"),
            body: $payload
        );

        return CustomerResponse::fromArray($this->extractDataOrFail($response->success, $response->message, $response->data));
    }

    /**
     * Search customers by email.
     * Endpoint: POST /customers/search?page={page}&size={size}
     * Docs: https://developer.flutterwave.com/reference/customers_search
     *
     * @return array{data: array<int, CustomerResponse>, meta: array|null, message: string}
     */
    public function searchCustomers(string $email, int $page = 1, int $size = 10): array
    {


        try {
            $response = $this->request(
                method: 'POST',
                url: $this->resolveApiEndpoint('/customers/search'),
                query: [
                    'page' => $page,
                    'size' => $size,
                ],
                body: [
                    'email' => $email,
                ]
            );

            $items = array_map(
                static fn(array $customer): CustomerResponse => CustomerResponse::fromArray($customer),
                is_array($response->data) ? $response->data : []
            );

            return [
                'data' => $items,
                'meta' => $response->meta,
                'message' => $response->message,
            ];
        } catch (Throwable $exception) {
            Log::error('Flutterwave createCustomer failed', [
                'error' => $exception->getMessage(),
                'payload' => [
                    'email' => $email,
                ],
            ]);
            return [
                'data' => [],
                'meta' => null,
                'message' => $response->message,
            ];
        }
    }

    /**
     * Delete/Blacklist/Whitelist customer are not documented in current official customer endpoints.
     */
    public function deleteCustomer(string $customerId): array
    {
        return $this->unsupportedCustomerAction('delete', $customerId);
    }

    public function blacklistCustomer(string $customerId): array
    {
        return $this->unsupportedCustomerAction('blacklist', $customerId);
    }

    public function whitelistCustomer(string $customerId): array
    {
        return $this->unsupportedCustomerAction('whitelist', $customerId);
    }

    /**
     * -----------------------------
     * Payment Methods
     * -----------------------------
     *
     * Create payment method.
     * Endpoint: POST /payment-methods
     * Docs: https://developer.flutterwave.com/reference/payment_methods_post
     */
    public function createPaymentMethod(CreatePaymentMethodRequest|array $request, ?string $idempotencyKey = null): PaymentMethodResponse
    {
        $payload1 = $request instanceof CreatePaymentMethodRequest ? $request->toArray() : $request;

        $payload = $this->encryptCardPayloadIfNeeded($payload1);
        $this->assertNoPlainCardData($payload);
        $response = $this->request(
            method: 'POST',
            url: $this->resolveApiEndpoint('/payment-methods'),
            body: $payload,
            headers: [
                'X-Idempotency-Key' => $idempotencyKey ?? $this->generateIdempotencyKey(),
            ]
        );
        dd($response, $payload);

        return PaymentMethodResponse::fromArray($this->extractDataOrFail($response->success, $response->message, $response->data));
    }

    /**
     * Retrieve payment method.
     * Endpoint: GET /payment-methods/{id}
     * Docs: https://developer.flutterwave.com/reference/payment_methods_get
     */
    public function getPaymentMethod(string $paymentMethodId): PaymentMethodResponse
    {
        $response = $this->request('GET', $this->resolveApiEndpoint("/payment-methods/{$paymentMethodId}"));

        return PaymentMethodResponse::fromArray($this->extractDataOrFail($response->success, $response->message, $response->data));
    }

    /**
     * List payment methods.
     * Endpoint: GET /payment-methods?page={page}&size={size}
     * Docs: https://developer.flutterwave.com/reference/payment_methods_list
     *
     * @return array{data: array<int, PaymentMethodResponse>, meta: array|null, message: string}
     */
    public function listPaymentMethods(int $page = 1, int $size = 10): array
    {
        $response = $this->request('GET', $this->resolveApiEndpoint('/payment-methods'), [
            'page' => $page,
            'size' => $size,
        ]);

        $items = array_map(
            static fn(array $method): PaymentMethodResponse => PaymentMethodResponse::fromArray($method),
            is_array($response->data) ? $response->data : []
        );

        return [
            'data' => $items,
            'meta' => $response->meta,
            'message' => $response->message,
        ];
    }

    /**
     * Build card payment method payload using encrypted card details.
     * Endpoint: POST /payment-methods
     * Docs: https://developer.flutterwave.com/reference/payment_methods_post
     *
     * This helper is designed to avoid handling raw PAN/CVV in this service layer.
     */
    public function createEncryptedCardPaymentMethod(
        array $encryptedCardPayload,
        ?string $customerId = null,
        array $meta = [],
        ?string $idempotencyKey = null
    ): PaymentMethodResponse {
        $request = new CreatePaymentMethodRequest(
            type: 'card',
            paymentData: $encryptedCardPayload,
            customerId: $customerId,
            meta: $meta
        );

        return $this->createPaymentMethod($request, $idempotencyKey);
    }

    /**
     * -----------------------------
     * Orders
     * -----------------------------
     *
     * Create an order.
     * Endpoint: POST /orders
     * Docs: https://developer.flutterwave.com/reference/orders_post
     */
    public function createOrder(
        CreateOrderRequest|array $request,
        ?string $idempotencyKey = null,
        ?string $scenarioKey = null
    ): OrderResponse {
        $payload = $request instanceof CreateOrderRequest ? $request->toArray() : $request;

        $headers = [
            'X-Idempotency-Key' => $idempotencyKey ?? $this->generateIdempotencyKey(),
        ];

        if ($scenarioKey !== null) {
            $headers['X-Scenario-Key'] = $scenarioKey;
        }

        // $response = $this->request(
        //     method: 'POST',
        //     url: $this->resolveApiEndpoint('/orders'),
        //     body: $payload,
        //     headers: $headers
        // );
        $response = (object) [
            'success' => true,
            'message' => 'Sample order response',
            'data' => [
                'id' => 'ord_' . Str::upper(Str::random(10)),
                'amount' => (float) ($payload['amount'] ?? 12.34),
                "fees" => [
                    [
                        "type" => "vat",
                        "amount" => 12.3
                    ]
                ],
                "billing_details" => [
                    "email" => $payload['billing_details']['email']
                        ?? $payload['customer']['email']
                        ?? "cornelius@gmail.com",
                    "name" => [
                        "first" => $payload['billing_details']['name']['first']
                            ?? $payload['customer']['name']['first']
                            ?? "King",
                        "middle" => $payload['billing_details']['name']['middle']
                            ?? $payload['customer']['name']['middle']
                            ?? "Leo",
                        "last" => $payload['billing_details']['name']['last']
                            ?? $payload['customer']['name']['last']
                            ?? "LeBron"
                    ],
                    "phone" => [
                        "country_code" => $payload['billing_details']['phone']['country_code']
                            ?? $payload['customer']['phone']['country_code']
                            ?? "234",
                        "number" => $payload['billing_details']['phone']['number']
                            ?? $payload['customer']['phone']['number']
                            ?? "08012345678"
                    ]
                ],
                "currency" => (string) ($payload['currency'] ?? "NGN"),
                "customer_id" => (string) ($payload['customer_id'] ?? "cus_3XarBILKQS"),
                "description" => (string) ($payload['description'] ?? "Payment for a Suit Skirt"),
                "meta" => is_array($payload['meta'] ?? null)
                    ? $payload['meta']
                    : [
                        "additionalProp" => "string"
                    ],
                "next_action" => [
                    "type" => "redirect_url",
                    "redirect_url" => [
                        "url" => $payload['redirect_url']
                            ?? "https://developer-sandbox-ui-sit.flutterwave.cloud/redirects?opay&token=sample"
                    ]
                ],
                "payment_method_details" => [
                    "type" => (string) ($payload['payment_method_type'] ?? "card"),
                    "card" => [
                        "expiry_month" => "09",
                        "expiry_year" => "32",
                        "first6" => "123412",
                        "last4" => "1234",
                        "network" => "MASTERCARD",
                        "billing_address" => [
                            "city" => "New York",
                            "country" => "US",
                            "line1" => "123 Main Street",
                            "line2" => "Apt 4B",
                            "postal_code" => "10001",
                            "state" => "New York"
                        ],
                        "cof" => [
                            "enabled" => true,
                            "agreement_id" => "Agreement00w02W1",
                            "recurring_amount_variability" => "VARIABLE",
                            "agreement_type" => "UNSCHEDULED",
                            "trace_id" => "123456789"
                        ],
                        "card_holder_name" => "Alex James"
                    ],
                    "id" => "pmd_WRq7L4TM8p",
                    "customer_id" => (string) ($payload['customer_id'] ?? "cus_3XarBILKQS"),
                    "meta" => is_array($payload['meta'] ?? null)
                        ? $payload['meta']
                        : [
                            "additionalProp" => "string"
                        ],
                    "device_fingerprint" => "62wd23423rq324323qew1",
                    "client_ip" => "154.123.220.1",
                    "created_datetime" => "2024-12-03T13:54:21.546559974Z"
                ],
                "redirect_url" => (string) ($payload['redirect_url'] ?? "https://flutterwave.com"),
                "reference" => (string) ($payload['reference'] ?? ('tx_' . Str::lower(Str::random(12)))),
                "status" => "completed",
                "processor_response" => [
                    "type" => "string",
                    "code" => "string"
                ],
                "created_datetime" => "2025-03-27T10:00:00Z"
            ],
            'meta' => null,
            'httpStatus' => 200,
            'raw' => null,
        ];

        return OrderResponse::fromArray($this->extractDataOrFail($response->success, $response->message, $response->data));
    }

    /**
     * Retrieve an order.
     * Endpoint: GET /orders/{id}
     * Docs: https://developer.flutterwave.com/reference/orders_get
     */
    public function getOrder(string $orderId): OrderResponse
    {
        $response = $this->request('GET', $this->resolveApiEndpoint("/orders/{$orderId}"));

        return OrderResponse::fromArray($this->extractDataOrFail($response->success, $response->message, $response->data));
    }

    /**
     * List orders.
     * Endpoint: GET /orders
     * Docs: https://developer.flutterwave.com/reference/orders_list
     *
     * @return array{data: array<int, OrderResponse>, meta: array|null, message: string}
     */
    public function listOrders(array $filters = []): array
    {
        $query = array_filter([
            'status' => $filters['status'] ?? null,
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
            'customer_id' => $filters['customer_id'] ?? null,
            'payment_method_id' => $filters['payment_method_id'] ?? null,
            'page' => $filters['page'] ?? 1,
            'size' => $filters['size'] ?? 10,
        ], static fn($value) => $value !== null && $value !== '');

        $response = $this->request('GET', $this->resolveApiEndpoint('/orders'), $query);

        $items = array_map(
            static fn(array $order): OrderResponse => OrderResponse::fromArray($order),
            is_array($response->data) ? $response->data : []
        );

        return [
            'data' => $items,
            'meta' => $response->meta,
            'message' => $response->message,
        ];
    }

    /**
     * Update order action.
     * Endpoint: PUT /orders/{id}
     * Docs: https://developer.flutterwave.com/reference/orders_put
     */
    public function updateOrder(string $orderId, string $action, array $meta = []): OrderResponse
    {
        if (!in_array($action, ['void', 'capture'], true)) {
            throw new RuntimeException('Order action must be either "void" or "capture".');
        }

        $payload = ['action' => $action];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        $response = $this->request(
            method: 'PUT',
            url: $this->resolveApiEndpoint("/orders/{$orderId}"),
            body: $payload
        );

        return OrderResponse::fromArray($this->extractDataOrFail($response->success, $response->message, $response->data));
    }

    /**
     * Resolve redirect URL from Flutterwave order response next_action payload.
     */
    public function resolveOrderRedirectUrl(OrderResponse|array $order): ?string
    {
        if ($order instanceof OrderResponse) {
            return $order->redirectUrl();
        }

        $nextAction = $order['next_action'] ?? null;

        if (is_array($nextAction) && ($nextAction['type'] ?? null) === 'redirect_url') {
            return $nextAction['redirect_url']['url'] ?? $nextAction['url'] ?? null;
        }

        return $order['redirect_url'] ?? null;
    }

    /**
     * Verify order transaction status by retrieving the order.
     */
    public function verifyOrder(string $orderId, array $validStatuses = ['completed', 'authorized']): array
    {
        $order = $this->getOrder($orderId);
        $isValid = in_array($order->status, $validStatuses, true);

        return [
            'success' => $isValid,
            'order_id' => $order->id,
            'status' => $order->status,
            'reference' => $order->reference,
            'data' => $order->toArray(),
        ];
    }

    /**
     * Compatibility helper for current project flow.
     * It now uses Customers + Payment Methods + Orders documented flow.
     */
    public function initializePayment(array $paymentData): array
    {
        try {
            if (!$this->isActive()) {
                return [
                    'success' => false,
                    'error' => 'Flutterwave gateway not configured or inactive',
                ];
            }

            $customerId = $paymentData['customer_id'] ?? null;
            if (!$customerId && !empty($paymentData['customer']['email'])) {
                $customerName = (string) ($paymentData['customer']['name'] ?? 'Customer');
                $nameParts = preg_split('/\s+/', trim($customerName)) ?: ['Customer'];

                $createdCustomer = $this->createCustomer(new CreateCustomerRequest(
                    email: (string) $paymentData['customer']['email'],
                    name: [
                        'first' => $nameParts[0] ?? 'Customer',
                        'last' => $nameParts[1] ?? 'User',
                    ],
                    phone: !empty($paymentData['customer']['phone'])
                        ? ['number' => (string) $paymentData['customer']['phone']]
                        : null,
                    meta: $paymentData['meta'] ?? []
                ));

                $customerId = $createdCustomer->id;
            }

            if (!$customerId) {
                return [
                    'success' => false,
                    'error' => 'customer_id is required, or provide customer details that can be used to create one.',
                ];
            }

            $paymentMethodId = $paymentData['payment_method_id'] ?? null;

            if (!$paymentMethodId && !empty($paymentData['payment_method']) && is_array($paymentData['payment_method'])) {
                $paymentMethodType = (string) ($paymentData['payment_method']['type'] ?? 'card');
                $paymentMethodPayload = (array) ($paymentData['payment_method']['payload'] ?? []);

                $createdMethod = $this->createPaymentMethod(new CreatePaymentMethodRequest(
                    type: $paymentMethodType,
                    paymentData: $paymentMethodPayload,
                    customerId: $customerId,
                    meta: $paymentData['meta'] ?? []
                ));

                $paymentMethodId = $createdMethod->id;
            }

            if (!$paymentMethodId) {
                return [
                    'success' => false,
                    'error' => 'payment_method_id is required, or provide payment_method payload to create one.',
                ];
            }

            $order = $this->createOrder(new CreateOrderRequest(
                amount: (float) ($paymentData['amount'] ?? 0),
                currency: (string) ($paymentData['currency'] ?? 'TZS'),
                reference: (string) ($paymentData['tx_ref'] ?? ('tx_' . Str::random(14))),
                customerId: (string) $customerId,
                paymentMethodId: (string) $paymentMethodId,
                redirectUrl: $paymentData['redirect_url'] ?? config('app.url') . '/payment/callback',
                meta: $paymentData['meta'] ?? []
            ));

            return [
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'tx_ref' => $order->reference,
                    'payment_link' => $order->redirectUrl(),
                    'status' => $order->status,
                    'next_action' => $order->nextAction,
                    'raw' => $order->toArray(),
                ],
            ];
        } catch (Throwable $exception) {
            Log::error('Flutterwave initializePayment failed', [
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Create Flutterwave hosted payment link using v3 payments endpoint.
     * Endpoint: POST /v3/payments
     */
    public function createHostedPaymentLink(array $payload): array
    {
        $response = $this->request(
            method: 'POST',
            url: $this->resolveV3PaymentsEndpoint(),
            body: $payload
        );

        $data = $this->extractDataOrFail($response->success, $response->message, $response->data);
        $link = $data['link'] ?? null;

        if (!is_string($link) || trim($link) === '') {
            throw new RuntimeException('Flutterwave hosted payment response did not include a payment link.');
        }

        return [
            'status' => $response->raw['status'] ?? null,
            'message' => $response->message,
            'link' => $link,
            'data' => $data,
            'http_status' => $response->httpStatus,
            'raw' => $response->raw,
        ];
    }

    /**
     * Compatibility helper for transaction verification.
     * Endpoint: GET /transactions/{id}/verify (legacy transaction verification flow)
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            $response = $this->request('GET', $this->resolveApiEndpoint("/transactions/{$transactionId}/verify"));
            $data = $this->extractDataOrFail($response->success, $response->message, $response->data);

            return [
                'success' => in_array(($data['status'] ?? null), ['successful', 'completed'], true),
                'data' => $data,
            ];
        } catch (Throwable $exception) {
            Log::error('Flutterwave verifyPayment failed', [
                'transaction_id' => $transactionId,
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Get gateway configuration.
     */
    public function getConfig(): ?array
    {
        return $this->gateway?->config;
    }

    /**
     * Check if gateway is active.
     */
    public function isActive(): bool
    {
        return (bool) ($this->gateway?->active ?? false);
    }

    /**
     * Example usage for Customers section.
     */
    public function customersUsageExample(): array
    {
        return [
            'create' => [
                'method' => 'createCustomer',
                'payload' => [
                    'email' => 'customer@example.com',
                    'name' => ['first' => 'Jane', 'last' => 'Doe'],
                    'phone' => ['country_code' => '234', 'number' => '08012345678'],
                ],
            ],
            'get' => ['method' => 'getCustomer', 'customer_id' => 'cus_xxx'],
            'list' => ['method' => 'listCustomers', 'page' => 1, 'size' => 10],
            'update' => ['method' => 'updateCustomer', 'customer_id' => 'cus_xxx'],
        ];
    }

    /**
     * Example usage for Payment Methods section.
     */
    public function paymentMethodsUsageExample(): array
    {
        return [
            'create' => [
                'method' => 'createPaymentMethod',
                'payload' => [
                    'type' => 'card',
                    'card' => ['cof' => ['enabled' => true]],
                    'customer_id' => 'cus_xxx',
                ],
            ],
            'get' => ['method' => 'getPaymentMethod', 'payment_method_id' => 'pmd_xxx'],
            'list' => ['method' => 'listPaymentMethods', 'page' => 1, 'size' => 10],
        ];
    }

    /**
     * Example usage for Orders section.
     */
    public function ordersUsageExample(): array
    {
        return [
            'create' => [
                'method' => 'createOrder',
                'payload' => [
                    'amount' => 1500.00,
                    'currency' => 'NGN',
                    'reference' => 'inv-1001-abc123',
                    'customer_id' => 'cus_xxx',
                    'payment_method_id' => 'pmd_xxx',
                    'redirect_url' => 'https://example.com/payment/callback',
                ],
            ],
            'get' => ['method' => 'getOrder', 'order_id' => 'ord_xxx'],
            'list' => ['method' => 'listOrders', 'filters' => ['page' => 1, 'size' => 10]],
            'verify' => ['method' => 'verifyOrder', 'order_id' => 'ord_xxx'],
        ];
    }

    private function request(
        string $method,
        string $url,
        array $query = [],
        ?array $body = null,
        array $headers = []
    ) {
        $requestUrl = $this->buildRequestUrl($url, $query);
        $requestHeaders = array_merge([
            'Authorization' => 'Bearer ' . $this->resolveBearerToken(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Trace-Id' => (string) Str::uuid(),
        ], $headers);

        $encodedBody = null;
        if ($body !== null) {
            $encodedBody = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($encodedBody === false) {
                throw new RuntimeException('Failed to encode Flutterwave request body as JSON: ' . json_last_error_msg());
            }
        }

        $curlHandle = curl_init();
        if ($curlHandle === false) {
            throw new RuntimeException('Failed to initialize cURL for Flutterwave request.');
        }

        $formattedHeaders = [];
        foreach ($requestHeaders as $key => $value) {
            $formattedHeaders[] = is_int($key)
                ? (string) $value
                : $key . ': ' . (string) $value;
        }
        $curlOptions = [
            CURLOPT_URL => $requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::DEFAULT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $formattedHeaders,
        ];

        if ($encodedBody !== null) {
            $curlOptions[CURLOPT_POSTFIELDS] = $encodedBody;
        }

        curl_setopt_array($curlHandle, $curlOptions);
        $rawResponse = curl_exec($curlHandle);

        if ($rawResponse === false) {
            $curlError = curl_error($curlHandle);
            curl_close($curlHandle);
            throw new RuntimeException('cURL error: ' . $curlError);
        }

        $httpStatus = (int) curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        $decoded = json_decode((string) $rawResponse, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid JSON response from Flutterwave.');
        }

        $status = strtolower((string) ($decoded['status'] ?? 'failed'));
        return (object) [
            'success' => $status === 'success' && $httpStatus >= 200 && $httpStatus < 300,
            'message' => (string) ($decoded['message'] ?? 'No message returned from Flutterwave.'),
            'data' => $decoded['data'] ?? null,
            'meta' => isset($decoded['meta']) && is_array($decoded['meta']) ? $decoded['meta'] : null,
            'httpStatus' => $httpStatus,
            'raw' => $decoded,
        ];
    }

    private function buildRequestUrl(string $url, array $query): string
    {
        if (empty($query)) {
            return trim($url);
        }

        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        if ($queryString === '') {
            return trim($url);
        }

        return str_contains($url, '?')
            ? $url . '&' . $queryString
            : $url . '?' . $queryString;
    }

    private function resolveBearerToken(): string
    {
        return config('services.flutterwave.api_key');
    }

    private function resolveV3PaymentsEndpoint(): string
    {
        $baseUrl = config('services.flutterwave.v3_base_url') ?? 'https://api.flutterwave.com';
        return rtrim($baseUrl, '/') . '/v3/payments';
    }

    private function extractDataOrFail(bool $success, string $message, mixed $data): array
    {
        if (!$success) {
            throw new RuntimeException('Flutterwave API request failed: ' . $message);
        }

        if (!is_array($data)) {
            throw new RuntimeException('Flutterwave API returned an invalid response payload.');
        }

        return $data;
    }

    private function unsupportedCustomerAction(string $action, string $customerId): array
    {
        Log::info('Unsupported Flutterwave customer action requested', [
            'action' => $action,
            'customer_id' => $customerId,
        ]);

        return [
            'success' => false,
            'error' => "Customer {$action} is not currently exposed in official Flutterwave customer API references.",
            'customer_id' => $customerId,
        ];
    }

    private function generateIdempotencyKey(): string
    {
        return (string) Str::uuid();
    }

    private function resolveApiEndpoint(string $path): string
    {
        $baseUrl = trim((string) (
            $this->resolveGatewayConfigValue('base_url')
            ?? $this->resolveGatewayConfigValue('gateway')
            ?? config('services.flutterwave.base_url')
            ?? env('FLUTTERWAVE_BASE_URL')
            ?? 'https://developersandbox-api.flutterwave.com'
        ));

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    private function resolveGatewayConfigValue(string $key): mixed
    {
        $config = $this->gateway?->config;

        if (is_string($config)) {
            $decodedConfig = json_decode($config, true);
            $config = is_array($decodedConfig) ? $decodedConfig : [];
        }

        if (!is_array($config)) {
            return null;
        }

        $value = $config[$key] ?? null;

        return is_string($value) ? trim($value) : $value;
    }

    private function assertNoPlainCardData(array $payload): void
    {
        $sensitiveKeys = ['pan', 'card_number', 'cvv', 'pin'];

        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $payload) || array_key_exists($key, $payload['card'] ?? [])) {
                throw new RuntimeException('Plain card credentials were detected. Use encrypted/secure card payload fields.');
            }
        }
    }

    private function encryptCardPayloadIfNeeded(array $payload): array
    {
        if (strtolower((string) ($payload['type'] ?? '')) !== 'card') {
            return $payload;
        }

        $card = is_array($payload['card'] ?? null) ? $payload['card'] : [];

        $alreadyEncrypted = !empty($card['encrypted_card_number'])
            && !empty($card['encrypted_expiry_month'])
            && !empty($card['encrypted_expiry_year'])
            && !empty($card['nonce']);



        if ($alreadyEncrypted) {
            return $payload;
        }

        $rawCardData = [
            'card_number' => $card['card_number'] ?? $payload['card_number'] ?? null,
            'expiry_month' => $card['expiry_month'] ?? $payload['expiry_month'] ?? null,
            'expiry_year' => $card['expiry_year'] ?? $payload['expiry_year'] ?? null,
            'cvv' => $card['cvv'] ?? $payload['cvv'] ?? null,
        ];

        $hasRawCardDetails = collect($rawCardData)->filter(fn($value) => !empty($value))->isNotEmpty();

        if (!$hasRawCardDetails) {
            return $payload;
        }

        try {
            $base64Key = config('services.flutterwave.encryption_key');
            $encryptionService = new FlutterwaveEncryptionService($base64Key);
            $encryptedCardData = $encryptionService->encryptArray($rawCardData);
        } catch (Throwable $exception) {
            throw new RuntimeException('Unable to encrypt card details: ' . $exception->getMessage(), previous: $exception);
        }


        if (!empty($encryptedCardData['nonce'])) {
            $card['nonce'] = $encryptedCardData['nonce'];
        }

        if (!empty($encryptedCardData['expiry_month'])) {
            $card['encrypted_expiry_month'] = $encryptedCardData['expiry_month'];
        }

        if (!empty($encryptedCardData['expiry_year'])) {
            $card['encrypted_expiry_year'] = $encryptedCardData['expiry_year'];
        }

        if (!empty($encryptedCardData['cvv'])) {
            $card['encrypted_cvv'] = $encryptedCardData['cvv'];
        }
        if (!empty($encryptedCardData['card_number'])) {
            $card['encrypted_card_number'] = $encryptedCardData['card_number'];
        }



        unset($card['card_number'], $card['expiry_month'], $card['expiry_year'], $card['cvv']);
        unset($payload['card_number'], $payload['expiry_month'], $payload['expiry_year'], $payload['cvv']);
        $card = array_merge($card, ['cof' => ['enabled' => true]]); // Example of adding additional card options if needed

        $payload['card'] = $card;
        return $payload;
    }

    private function applyCardPayloadRequirements(array $payload): array
    {
        if (strtolower((string) ($payload['type'] ?? '')) !== 'card') {
            return $payload;
        }

        $card = is_array($payload['card'] ?? null) ? $payload['card'] : [];

        $requiredFieldMap = [
            'encrypted_expiry_year' => ['encrypted_expiry_year', 'card_encrypted_expiry_year'],
            'encrypted_expiry_month' => ['encrypted_expiry_month', 'card_encrypted_expiry_month'],
            'encrypted_card_number' => ['encrypted_card_number', 'card_encrypted_card_number'],
            'nonce' => ['nonce', 'card_nonce'],
        ];

        foreach ($requiredFieldMap as $targetKey => $sourceKeys) {
            if (!empty($card[$targetKey])) {
                continue;
            }

            foreach ($sourceKeys as $sourceKey) {
                if (!empty($payload[$sourceKey])) {
                    $card[$targetKey] = $payload[$sourceKey];
                    break;
                }
            }
        }

        $payload['card'] = $card;

        $missingFields = collect(array_keys($requiredFieldMap))
            ->filter(fn(string $field) => empty($payload['card'][$field]))
            ->map(fn(string $field) => 'card.' . $field)
            ->values()
            ->all();

        if (!empty($missingFields)) {
            throw new RuntimeException('Missing required encrypted card fields: ' . implode(', ', $missingFields));
        }

        return $payload;
    }
}

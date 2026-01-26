<?php

namespace App\Http\Controllers;

use App\Services\UCNPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected UCNPaymentService $ucnPaymentService;

    public function __construct(UCNPaymentService $ucnPaymentService)
    {
        $this->ucnPaymentService = $ucnPaymentService;
    }

    /**
     * Handle UCN payment webhook
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleUCNPayment(Request $request): JsonResponse
    {
        // Log incoming webhook
        Log::info('UCN webhook received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        $webhookData = $request->all();

        // Process the webhook
        $response = $this->ucnPaymentService->processWebhook($webhookData);

        // Determine HTTP status code based on response code
        $httpStatus = $response['responseCode'] === '000' ? 200 : 400;

        return response()->json($response, $httpStatus);
    }

    public function handleUCNPaymentWebhook(Request $request): JsonResponse
    {
        Log::info(['Request recived' => json_encode($request->all())]);
        return response()->json(['success' => true, 'message' => 'Payment webhook received'], 200);
    }
}

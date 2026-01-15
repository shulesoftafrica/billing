<?php

namespace App\Http\Controllers;

use App\Services\UNCPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected UNCPaymentService $uncPaymentService;

    public function __construct(UNCPaymentService $uncPaymentService)
    {
        $this->uncPaymentService = $uncPaymentService;
    }

    /**
     * Handle UNC payment webhook
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleUNCPayment(Request $request): JsonResponse
    {
        // Log incoming webhook
        Log::info('UNC webhook received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        $webhookData = $request->all();

        // Process the webhook
        $response = $this->uncPaymentService->processWebhook($webhookData);

        // Determine HTTP status code based on response code
        $httpStatus = $response['responseCode'] === '000' ? 200 : 400;

        return response()->json($response, $httpStatus);
    }
}

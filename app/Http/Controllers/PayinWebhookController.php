<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayinWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $raw = $request->getContent();
        $payload = $request->all();

        Log::info('PayIn webhook received', ['headers' => $request->headers->all(), 'payload' => $payload]);

        // Optional verification — provider may send a signature header.
        $signatureHeader = $request->header('X-PAYIN-SIGNATURE') ?? $request->header('X-Signature');

        if ($signatureHeader) {
            $computed = hash_hmac('sha256', $raw, config('services.payin.secret_key'));
            if (!hash_equals($computed, $signatureHeader)) {
                Log::warning('PayIn webhook signature mismatch', ['given' => $signatureHeader, 'computed' => $computed]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }
        } else {
            // For local testing without signature — log a note
            Log::info('PayIn webhook: no signature header present (testing mode).');
        }

        // Process payload: you can update DB here (example below is minimal)
        $txnid = $payload['txnid'] ?? ($payload['details']['txnid'] ?? null);
        $status = $payload['status'] ?? ($payload['details']['status'] ?? null);

        // TODO: update your order or transaction record in DB
        Log::info("Webhook txnid={$txnid} status={$status}");

        return response()->json(['message' => 'received']);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PayinService;
use App\Model\PayinTransaction;
use Illuminate\Support\Facades\Http;

class PayinController extends Controller
{
    protected $payin;

    public function __construct(PayinService $payin)
    {
        $this->payin = $payin;
    }
    public function showForm()
    {
        return view('payment.payIn');
    }

    public function createIntent(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string',
            'amount'        => 'required|numeric',
            'mobile'        => 'nullable|string',
            'email'         => 'nullable|email',
            'device_info'   => 'nullable|string',
            'udf1'          => 'nullable|string',
            'udf2'          => 'nullable|string',
            'udf3'          => 'nullable|string',
            'udf4'          => 'nullable|string',
            'udf5'          => 'nullable|string',
            'payin_ref'     => 'nullable|string',
            'order_id'      => 'nullable|integer',
        ]);

        $payload = [
            'customer_name' => $data['customer_name'],
            'amount'        => (float) $data['amount'],
            'mobile'        => $data['mobile'] ?? '',
            'email'         => $data['email'] ?? '',
            'device_info'   => $data['device_info'] ?? request()->header('User-Agent'),
            'udf1'          => $data['udf1'] ?? '',
            'udf2'          => $data['udf2'] ?? '',
            'udf3'          => $data['udf3'] ?? '',
            'udf4'          => $data['udf4'] ?? '',
            'udf5'          => $data['udf5'] ?? '',
            'payin_ref'     => $data['payin_ref'] ?? '',
        ];

        $txn = PayinTransaction::create([
            'order_id'      => $data['order_id'] ?? null,
            'customer_name' => $payload['customer_name'],
            'amount'        => $payload['amount'],
            'mobile'        => $payload['mobile'],
            'email'         => $payload['email'],
            'device_info'   => $payload['device_info'],
            'udf'           => [
                $payload['udf1'],
                $payload['udf2'],
                $payload['udf3'],
                $payload['udf4'],
                $payload['udf5']
            ],
            'status'        => 'initiated',
            'payload'       => $payload,
        ]);

        // Call your Payin API
        $resp = $this->payin->createIntent($payload);

        // store API response
        $txn->update(['response' => (array) $resp]);

        if (!empty($resp->details->intent_url)) {
            $txn->txnid = $resp->details->txnid ?? null;
            $txn->save();
            $upiBase = "upi://pay?" . http_build_query([
                'pa' => 'ASVBCRAFTVISIONPRIVATELIMITED',
                'pn' => $payload['customer_name'],
                'tid' => $txn->id,
                'tr' => $data['order_id'] ?? $txn->id,
                'tn' => 'Payment for Order #' . ($data['order_id'] ?? $txn->id),
                'am' => number_format($payload['amount'], 2, '.', ''),
                'cu' => 'INR',
            ]);

            // 🔹 Build Intent links for apps
            $query = parse_url($upiBase, PHP_URL_QUERY);
            $apps = [
                'gpay'    => "intent://pay?{$query}#Intent;scheme=upi;package=com.google.android.apps.nbu.paisa.user;end",
                'phonepe' => "intent://pay?{$query}#Intent;scheme=upi;package=com.phonepe.app;end",
                'paytm'   => "intent://pay?{$query}#Intent;scheme=upi;package=net.one97.paytm;end",
            ];

            return response()->json([
                'success'    => true,
                'txnid'      => $txn->txnid,
                'intent_url' => $resp->details->intent_url,
                'upi_link'   => $upiBase,
                'apps'       => $apps
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $resp->message ?? 'Failed to create transaction'
        ], 422);
    }
    public function status($txnid)
    {
        $txnid = trim($txnid);
        $url = rtrim(config('services.payin.base_url'), '/') . "/payin/status/{$txnid}";

        $response = Http::withHeaders([
            'AccessKey'    => config('services.payin.access_key'),
            'SecretKey'    => config('services.payin.secret_key'),
            'Content-Type' => 'application/json',
        ])->get($url);

        return response()->json([
            'called_url' => $url,
            'status_code' => $response->status(),
            'payload' => $response->json(),
        ], $response->status());
    }
}

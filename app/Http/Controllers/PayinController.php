<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayinController extends Controller
{

    public function showForm()
    {
        return view('payment.payIn');
    }


    public function createIntent(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string',
            'amount'        => 'required|numeric',
            'mobile'        => 'nullable|string',
            'email'         => 'nullable|email',
            'device_info'   => 'nullable|string',
        ]);

        $payload = [
            'customer_name' => $validated['customer_name'],
            'amount'        => $validated['amount'],
            'mobile'        => $validated['mobile'] ?? '',
            'email'         => $validated['email'] ?? '',
            'device_info'   => $validated['device_info'] ?? 'LaravelApp',
            'udf1' => '',
            'udf2' => '',
            'udf3' => '',
            'udf4' => '',
            'udf5' => '',
            'payin_ref' => '',
        ];

        $response = Http::withHeaders([
            'AccessKey'    => config('services.payin.access_key'),
            'SecretKey'    => config('services.payin.secret_key'),
            'Content-Type' => 'application/json',
        ])->post(rtrim(config('services.payin.base_url'), '/') . '/payin/intent/', $payload);


        return response()->json($response->json(), $response->status());
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

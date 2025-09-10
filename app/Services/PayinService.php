<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PayinService
{
    protected $base;
    protected $access;
    protected $secret;

    public function __construct()
    {
        $this->base = config('services.payin.base_url');
        $this->access = config('services.payin.access_key');
        $this->secret = config('services.payin.secret_key');
    }

    protected function headers()
    {
        return [
            'AccessKey' => $this->access,
            'SecretKey' => $this->secret,
            'Content-Type' => 'application/json',
        ];
    }

    public function createIntent(array $payload)
    {
        $url = rtrim($this->base, '/') . '/payin/intent/';
        $resp = Http::withHeaders($this->headers())->post($url, $payload);
        return $resp->object();
    }

    public function status(string $txnid)
    {
        $url = rtrim($this->base, '/') . "/payin/status/{$txnid}/";
        $resp = Http::withHeaders($this->headers())->get($url);
        return $resp->object();
    }
}

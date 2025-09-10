<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://login.honestpay.in/v/intent/create',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "token" : "KIBztE9f3s7CB49I9v0cGejfwPO9bL",
    "type" : "dynamic",
    "amount" : "100",
    "email"    : "email",
    "name"    : "name",
    "txnid" : "HPUPITEST2025022620ret21",
    "callback" : "https://webhook-test.com/11bb20480dc406a9c30e8a62937e8dca"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

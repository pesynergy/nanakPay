<?php

/**
 * DigyCart Class for PHP Integration Kit
 * 
 * @author    Rakesh Suthar
 * @copyright Copyright (c) 2022, DIGYCART PRIVATE LIMITED https://digycart.com/.
 * @license   http://www.opensource.org/licenses/mit-license.php
 */
class DigyCart {

    protected $openssl_cipher_name = "aes-256-cbc";
    protected $key_len = 35;
    protected $sp_salt_key = 'Asdf@1234';
    protected $PG_URL = 'https://pg.digycart.com/api/seamless/txnReq';
    protected $PGStatusCheck_URL = 'https://pg.digycart.com/apis/seamless/txnStatus';
    // protected $PGCancel_URL = '';
    // protected $PGRefund_URL = '';

    protected $mid; //Provided by DigyCart (ex: 500000000000009)
    protected $secretKey;
    protected $saltKey;
    protected $orderNo; //start with "ORD" (Alphanumeric) example"ORD1245789631542" (Always should be unique for every transaction)
    protected $amount; // "Amount will be in paisa format.Rs. 10/-(10*100=1000)"
    protected $currency; // INR (3 digits)
    protected $txnReqType; // S/P
    protected $undefinedField1;
    protected $undefinedField2;
    protected $undefinedField3;
    protected $undefinedField4;
    protected $undefinedField5;
    protected $undefinedField6;
    protected $undefinedField7;
    protected $undefinedField8;
    protected $undefinedField9;
    protected $undefinedField10;
    protected $emailId;
    protected $mobileNo;
    protected $address;
    protected $city;
    protected $state;
    protected $pincode;
    
    // For Seamless Transaction
    protected $transactionMethod;
    protected $bankCode;
    protected $vpa;
    protected $cardNumber;
    protected $expiryDate;
    protected $cvv;

    protected $customerName;
    protected $respUrl;
    protected $optional1;

    // For Status Check API
    protected $txnRefNo;

    
    // protected $RecurringPeriod; // NA or W or M
    // protected $RecurringDay; // Day means 25th-dec or 10th-may (2 digit)
    // protected $NoOfRecurring;
    
    // protected $Source;
    // protected $EmailInvoice;
    // protected $MobileInvoice;
    // protected $Optional2;

    // protected $Tid;
    // protected $CardHolderName;
    // protected $TxnAmount;


    protected $RequestSequence = "mid|orderNo|amount|currency|txnReqType|undefinedField1|undefinedField2|undefinedField3|undefinedField4|undefinedField5|undefinedField6|undefinedField7|undefinedField8|undefinedField9|undefinedField10|emailId|mobileNo|address|city|state|pincode|transactionMethod|bankCode|vpa|cardNumber|expiryDate|cvv|customerName|respUrl|optional1";
    protected $ResponseSequence = "mid|orderNo|txnRefNo|amount|currency|txnReqType|undefinedField1|undefinedField2|undefinedField3|undefinedField4|undefinedField5|undefinedField6|undefinedField7|undefinedField8|undefinedField9|undefinedField10|emailId|mobileNo|address|city|state|pincode|respCode|respMessage|payAmount|txnRespDate|upiString";
    // For Status Check API
    protected $RequestSequenceStatus = "mid|amount|txnRefNo|orderNo";
    protected $ResponseSequenceStatus = "mid|orderNo|txnRefNo|amount|currency|txnReqType|undefinedField1|undefinedField2|undefinedField3|undefinedField4|undefinedField5|undefinedField6|undefinedField7|undefinedField8|undefinedField9|undefinedField10|emailId|mobileNo|address|city|state|pincode|respCode|respMessage|payAmount|txnRespDate|upiString";
    // For Cancel TRN
    // protected $CancelAmount;
    // protected $CancelOrderNo;
    // protected $RequestSequenceCancel = "Mid|TotalAmount|TxnRefNo|OrderNo|MeTransReqType|CancelAmount|CancelOrderNo";
    // protected $ResponseSequenceCancel = "Mid|OrderNo|TxnRefNo|CancelAmount|CurrencyName|MeTransReqType|RespCode|RespMessage|TxnRespDate|CancelOrderNo|CancelTxnRefNo";
    // For Refund TRN
    // protected $RequestSequenceRefund = "Mid|TotalAmount|TxnRefNo|OrderNo|MeTransReqType|RefundAmount|RefundOrderNo";
    // protected $ResponseSequenceRefund = "Mid|OrderNo|TxnRefNo|RefundAmount|CurrencyName|MeTransReqType|RespCode|RespMessage|TxnRespDate|RefundOrderNo|RefundTxnRefNo";
    // protected $RefundAmount;
    // protected $RefundOrderNo;

    public function setMid($mid) {
        $this->mid = $mid;
        return $this;
    }

    public function setSecretKey($secretKey) {
        $this->secretKey = $secretKey;
        return $this;
    }

    public function setSaltKey($saltKey) {
        $this->saltKey = $saltKey;
        return $this;
    }

    public function setOrderNo($orderNo) {
        $this->orderNo = $orderNo;
        return $this;
    }

    public function setAmount($amount) {
        if (isset($amount) && !empty($amount)) {
            $this->amount = $amount * 100; // Convert it in to paisa
        } else {
            $this->amount = ''; // Convert it in to paisa
        }
        return $this;
    }

    public function setCurrency($currency) {
        $this->currency = $currency;
        return $this;
    }

    public function setTxnReqType($txnReqType) {
        if (isset($txnReqType) && !empty($txnReqType)) {
            $this->txnReqType = $txnReqType;
        } else {
            $this->txnReqType = 'S';
        }
        return $this;
    }

    public function setUndefinedField1($undefinedField1) {
        if (isset($undefinedField1) && !empty($undefinedField1)) {
            $this->undefinedField1 = $undefinedField1;
        } else {
            $this->undefinedField1 = '';
        }
        return $this;
    }

    public function setUndefinedField2($undefinedField2) {
        if (isset($undefinedField2) && !empty($undefinedField2)) {
            $this->undefinedField2 = $undefinedField2;
        } else {
            $this->undefinedField2 = '';
        }
        return $this;
    }

    public function setUndefinedField3($undefinedField3) {
        if (isset($undefinedField3) && !empty($undefinedField3)) {
            $this->undefinedField3 = $undefinedField3;
        } else {
            $this->undefinedField3 = '';
        }
        return $this;
    }

    public function setUndefinedField4($undefinedField4) {
        if (isset($undefinedField4) && !empty($undefinedField4)) {
            $this->undefinedField4 = $undefinedField4;
        } else {
            $this->undefinedField4 = '';
        }
        return $this;
    }

    public function setUndefinedField5($undefinedField5) {
        if (isset($undefinedField5) && !empty($undefinedField5)) {
            $this->undefinedField5 = $undefinedField5;
        } else {
            $this->undefinedField5 = '';
        }
        return $this;
    }

    public function setUndefinedField6($undefinedField6) {
        if (isset($undefinedField6) && !empty($undefinedField6)) {
            $this->undefinedField6 = $undefinedField6;
        } else {
            $this->undefinedField6 = '';
        }
        return $this;
    }

    public function setUndefinedField7($undefinedField7) {
        if (isset($undefinedField7) && !empty($undefinedField7)) {
            $this->undefinedField7 = $undefinedField7;
        } else {
            $this->undefinedField7 = '';
        }
        return $this;
    }

    public function setUndefinedField8($undefinedField8) {
        if (isset($undefinedField8) && !empty($undefinedField8)) {
            $this->undefinedField8 = $undefinedField8;
        } else {
            $this->undefinedField8 = '';
        }
        return $this;
    }

    public function setUndefinedField9($undefinedField9) {
        if (isset($undefinedField9) && !empty($undefinedField9)) {
            $this->undefinedField9 = $undefinedField9;
        } else {
            $this->undefinedField9 = '';
        }
        return $this;
    }

    public function setUndefinedField10($undefinedField10) {
        if (isset($undefinedField10) && !empty($undefinedField10)) {
            $this->undefinedField10 = $undefinedField10;
        } else {
            $this->undefinedField10 = '';
        }
        return $this;
    }

    public function setEmailId($emailId) {
        if (isset($emailId) && !empty($emailId)) {
            $this->emailId = $emailId;
        } else {
            $this->emailId = '';
        }
        return $this;
    }

    public function setMobileNo($mobileNo) {
        if (isset($mobileNo) && !empty($mobileNo)) {
            $this->mobileNo = $mobileNo;
        } else {
            $this->mobileNo = '';
        }
        return $this;
    }

    public function setAddress($address) {
        if (isset($address) && !empty($address)) {
            $this->address = $address;
        } else {
            $this->address = '';
        }
        return $this;
    }

    public function setCity($city) {
        if (isset($city) && !empty($city)) {
            $this->city = $city;
        } else {
            $this->city = '';
        }
        return $this;
    }

    public function setState($state) {
        if (isset($state) && !empty($state)) {
            $this->state = $state;
        } else {
            $this->state = '';
        }
        return $this;
    }

    public function setPincode($pincode) {
        if (isset($pincode) && !empty($pincode)) {
            $this->pincode = $pincode;
        } else {
            $this->pincode = '';
        }
        return $this;
    }

    // For Seamless kit
    public function setTransactionMethod($transactionMethod) {
        if (isset($transactionMethod) && !empty($transactionMethod)) {
            $this->transactionMethod = $transactionMethod;
        } else {
            $this->transactionMethod = '';
        }
        return $this;
    }

    public function setBankCode($bankCode) {
        if (isset($bankCode) && !empty($bankCode)) {
            $this->bankCode = $bankCode;
        } else {
            $this->bankCode = '';
        }
        return $this;
    }

    public function setVPA($vpa) {
        if (isset($vpa) && !empty($vpa)) {
            $this->vpa = $vpa;
        } else {
            $this->vpa = '';
        }
        return $this;
    }

    public function setCardNumber($cardNumber) {
        if (isset($cardNumber) && !empty($cardNumber)) {
            $this->cardNumber = $cardNumber;
        } else {
            $this->cardNumber = '';
        }
        return $this;
    }

    public function setExpiryDate($expiryDate) {
        if (isset($expiryDate) && !empty($expiryDate)) {
            $this->expiryDate = $expiryDate;
        } else {
            $this->expiryDate = '';
        }
        return $this;
    }

    public function setCVV($cvv) {
        if (isset($cvv) && !empty($cvv)) {
            $this->cvv = $cvv;
        } else {
            $this->cvv = '';
        }
        return $this;
    }

    public function setCustomerName($customerName) {
        if (isset($customerName) && !empty($customerName)) {
            $this->customerName = $customerName;
        } else {
            $this->customerName = '';
        }
        return $this;
    }

    public function setRespUrl($respUrl) {
        $this->respUrl = $respUrl;
        return $this;
    }

    public function setOptional1($optional1) {
        if (isset($optional1) && !empty($optional1)) {
            $this->optional1 = $optional1;
        } else {
            $this->optional1 = '';
        }
        return $this;
    }

    // For Status Check API
    public function setTxnRefNo($txnRefNo) {
        if (isset($txnRefNo) && !empty($txnRefNo)) {
            $this->txnRefNo = $txnRefNo;
        } else {
            $this->txnRefNo = '';
        }
        return $this;
    }

    // public function setRecurringPeriod($RecurringPeriod) {
    //     if (isset($RecurringPeriod) && !empty($RecurringPeriod)) {
    //         $this->RecurringPeriod = $RecurringPeriod;
    //     } else {
    //         $this->RecurringPeriod = 'NA';
    //     }
    //     return $this;
    // }

    // public function setRecurringDay($RecurringDay) {
    //     if (isset($RecurringDay) && !empty($RecurringDay)) {
    //         $this->RecurringDay = $RecurringDay;
    //     } else {
    //         $this->RecurringDay = '';
    //     }
    //     return $this;
    // }

    // public function setNoOfRecurring($NoOfRecurring) {
    //     if (isset($NoOfRecurring) && !empty($NoOfRecurring)) {
    //         $this->NoOfRecurring = $NoOfRecurring;
    //     } else {
    //         $this->NoOfRecurring = '';
    //     }
    //     return $this;
    // }

    // public function setSource($Source) {
    //     if (isset($Source) && !empty($Source)) {
    //         $this->Source = $Source;
    //     } else {
    //         $this->Source = '';
    //     }
    //     return $this;
    // }

    // public function setEmailInvoice($EmailInvoice) {
    //     if (isset($EmailInvoice) && !empty($EmailInvoice)) {
    //         $this->EmailInvoice = $EmailInvoice;
    //     } else {
    //         $this->EmailInvoice = '';
    //     }
    //     return $this;
    // }

    // public function setMobileInvoice($MobileInvoice) {
    //     if (isset($MobileInvoice) && !empty($MobileInvoice)) {
    //         $this->MobileInvoice = $MobileInvoice;
    //     } else {
    //         $this->MobileInvoice = '';
    //     }
    //     return $this;
    // }

    // public function setOptional2($Optional2) {
    //     if (isset($Optional2) && !empty($Optional2)) {
    //         $this->Optional2 = $Optional2;
    //     } else {
    //         $this->Optional2 = '';
    //     }
    //     return $this;
    // }

    // public function setTid($Tid) {
    //     if (isset($Tid) && !empty($Tid)) {
    //         $this->Tid = $Tid;
    //     } else {
    //         $this->Tid = '';
    //     }
    //     return $this;
    // }


    // public function setCancelAmount($CancelAmount) {
    //     if (isset($CancelAmount) && !empty($CancelAmount)) {
    //         $this->CancelAmount = $CancelAmount * 100; // Convert in to paisa 
    //     } else {
    //         $this->CancelAmount = '';
    //     }
    //     return $this;
    // }

    // public function setCancelOrderNo($CancelOrderNo) {
    //     if (isset($CancelOrderNo) && !empty($CancelOrderNo)) {
    //         $this->CancelOrderNo = $CancelOrderNo;
    //     } else {
    //         $this->CancelOrderNo = '';
    //     }
    //     return $this;
    // }

    // public function setRefundAmount($RefundAmount) {
    //     if (isset($RefundAmount) && !empty($RefundAmount)) {
    //         $this->RefundAmount = $RefundAmount * 100; // Covert in to paisa
    //     } else {
    //         $this->RefundAmount = '';
    //     }
    //     return $this;
    // }

    // public function setRefundOrderNo($RefundOrderNo) {
    //     if (isset($RefundOrderNo) && !empty($RefundOrderNo)) {
    //         $this->RefundOrderNo = $RefundOrderNo;
    //     } else {
    //         $this->RefundOrderNo = '';
    //     }
    //     return $this;
    // }

    // public function setCardHolderName($CardHolderName) {
    //     if (isset($CardHolderName) && !empty($CardHolderName)) {
    //         $this->CardHolderName = $CardHolderName;
    //     } else {
    //         $this->CardHolderName = '';
    //     }
    //     return $this;
    // }

    public function doPayment() {
        $encryptReq = $this->getEnrypt('TrnPayment');
        $checkSum = $this->getCheckSum($this->saltKey, $this->orderNo, $this->amount, $this->transactionMethod, $this->bankCode, $this->vpa, $this->cardNumber, $this->expiryDate, $this->cvv);
        $mid = $this->mid;
        $url = $this->PG_URL;
        $headers  = [
            'Content-Type: application/json'
        ];
        $data = json_encode(array(
            'encryptReq' => $encryptReq,
            'checkSum' => $checkSum,
            'mid' => $mid,
        ));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public function getResponse($respData,$mid,$checkSum) {
        if ($mid == $this->mid) { 
            $response = $this->decrypt($respData, $this->secretKey);
            $respArray = explode(',', $response);
            $hashRespSeq = explode('|', $this->ResponseSequence);
            $i = 0;
            $returnArray = array();
            foreach ($hashRespSeq as $hash_var) {
                $returnArray[$hash_var] = $respArray[$i];
                $i++;
            }
            $CheckSumTxnResp = $this->createCheckSumTxnResp($this->saltKey, $returnArray['orderNo'], $returnArray['payAmount'], $returnArray['respCode'], $returnArray['respMessage']);
            if (strcmp($checkSum, $CheckSumTxnResp) == 0) {
                $ResponseArray = array();
                $ResponseArray['mid'] = $returnArray['mid'];
                $ResponseArray['orderNo'] = $returnArray['orderNo'];
                $ResponseArray['txnRefNo'] = $returnArray['txnRefNo'];
                $ResponseArray['amount'] = $returnArray['amount'] / 100;
                $ResponseArray['currency'] = $returnArray['currency'];
                $ResponseArray['txnReqType'] = $returnArray['txnReqType'];
                $ResponseArray['undefinedField1'] = $returnArray['undefinedField1'];
                $ResponseArray['undefinedField2'] = $returnArray['undefinedField2'];
                $ResponseArray['undefinedField3'] = $returnArray['undefinedField3'];
                $ResponseArray['undefinedField4'] = $returnArray['undefinedField4'];
                $ResponseArray['undefinedField5'] = $returnArray['undefinedField5'];
                $ResponseArray['undefinedField6'] = $returnArray['undefinedField6'];
                $ResponseArray['undefinedField7'] = $returnArray['undefinedField7'];
                $ResponseArray['undefinedField8'] = $returnArray['undefinedField8'];
                $ResponseArray['undefinedField9'] = $returnArray['undefinedField9'];
                $ResponseArray['undefinedField10'] = $returnArray['undefinedField10'];
                $ResponseArray['emailId'] = $returnArray['emailId'];
                $ResponseArray['mobileNo'] = $returnArray['mobileNo'];
                $ResponseArray['address'] = $returnArray['address'];
                $ResponseArray['city'] = $returnArray['city'];
                $ResponseArray['state'] = $returnArray['state'];
                $ResponseArray['pincode'] = $returnArray['pincode'];
                $ResponseArray['respCode'] = $returnArray['respCode'];
                $ResponseArray['respMessage'] = $returnArray['respMessage'];
                $ResponseArray['payAmount'] = $returnArray['payAmount'] / 100;
                $ResponseArray['txnRespDate'] = $returnArray['txnRespDate'];
                $ResponseArray['upiString'] = $returnArray['upiString'];
                return json_encode($ResponseArray, JSON_UNESCAPED_SLASHES);
            } else {
                return "CheckSum Miss Match!";
            }
        }
    }

    public function getTrnStatus() {
        $encryptStatus = $this->getEnrypt('TrnStatus');
        $statusCheckSum = $this->getApiCheckSum($this->saltKey, $this->orderNo);
        $mid = $this->mid;
        $url = $this->PGStatusCheck_URL;
        $headers  = [
            'Content-Type: application/json'
        ];
        $data = json_encode(array(
            'encryptStatus' => $encryptStatus,
            'statusCheckSum' => $statusCheckSum,
            'mid' => $mid,
        ));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public function parseStatusResp($respData,$checkSum,$mid) {
        if ($mid == $this->mid) {
            $response = $this->decrypt($respData, $this->secretKey);
            $respArray = explode(',', $response);
            $hashRespSeq = explode('|', $this->ResponseSequenceStatus);
            $i = 0;
            $returnArray = array();
            foreach ($hashRespSeq as $hash_var) {
                $returnArray[$hash_var] = $respArray[$i];
                $i++;
            }
            $CheckSumStatusResp = $this->createCheckSumStatusResp($this->saltKey, $returnArray['orderNo'], $returnArray['payAmount'], $returnArray['respCode'], $returnArray['respMessage']);
            if (strcmp($checkSum, $CheckSumStatusResp) == 0) {
                $ResponseArray = array();
                $ResponseArray['mid'] = $returnArray['mid'];
                $ResponseArray['orderNo'] = $returnArray['orderNo'];
                $ResponseArray['txnRefNo'] = $returnArray['txnRefNo'];
                $ResponseArray['amount'] = $returnArray['amount'] / 100;
                $ResponseArray['currency'] = $returnArray['currency'];
                $ResponseArray['txnReqType'] = $returnArray['txnReqType'];
                $ResponseArray['undefinedField1'] = $returnArray['undefinedField1'];
                $ResponseArray['undefinedField2'] = $returnArray['undefinedField2'];
                $ResponseArray['undefinedField3'] = $returnArray['undefinedField3'];
                $ResponseArray['undefinedField4'] = $returnArray['undefinedField4'];
                $ResponseArray['undefinedField5'] = $returnArray['undefinedField5'];
                $ResponseArray['undefinedField6'] = $returnArray['undefinedField6'];
                $ResponseArray['undefinedField7'] = $returnArray['undefinedField7'];
                $ResponseArray['undefinedField8'] = $returnArray['undefinedField8'];
                $ResponseArray['undefinedField9'] = $returnArray['undefinedField9'];
                $ResponseArray['undefinedField10'] = $returnArray['undefinedField10'];
                $ResponseArray['emailId'] = $returnArray['emailId'];
                $ResponseArray['mobileNo'] = $returnArray['mobileNo'];
                $ResponseArray['address'] = $returnArray['address'];
                $ResponseArray['city'] = $returnArray['city'];
                $ResponseArray['state'] = $returnArray['state'];
                $ResponseArray['pincode'] = $returnArray['pincode'];
                $ResponseArray['respCode'] = $returnArray['respCode'];
                $ResponseArray['respMessage'] = $returnArray['respMessage'];
                $ResponseArray['payAmount'] = $returnArray['payAmount'] / 100;
                $ResponseArray['txnRespDate'] = $returnArray['txnRespDate'];
                $ResponseArray['upiString'] = $returnArray['upiString'];
                return json_encode($ResponseArray, JSON_UNESCAPED_SLASHES);
            } else {
                $ResponseArray['Error'] = 'CheckSum Miss Match!';
            }
        } else {
            $ResponseArray['Error'] = 'Mid Miss Match!';
        }
        // } else {
        //     $ResponseArray['Error'] = $response->errorMsg;
        // }
        exit();
    }

    protected function getEnrypt($msg = 'TrnPayment') {
        if ($msg == 'TrnPayment') {
            $hashVarsSeq = explode('|', $this->RequestSequence);
        }
        if ($msg == 'TrnStatus') {
            $hashVarsSeq = explode('|', $this->RequestSequenceStatus);
        }
        // if ($msg == 'TrnCancel') {
        //     $hashVarsSeq = explode('|', $this->RequestSequenceCancel);
        // }
        // if ($msg == 'TrnRefund') {
        //     $hashVarsSeq = explode('|', $this->RequestSequenceRefund);
        // }
        $hash_string = '';
        $i = 1;
        $count = count($hashVarsSeq);
        foreach ($hashVarsSeq as $hash_var) {
            $hash_string = $hash_string . $this->$hash_var;
            if ($i != $count) {
                $hash_string .= ',';
            }
            $i++;
        }
        return $this->encrypt($hash_string, $this->secretKey);
    }

    protected function encrypt($data, $key) {
        $iv = pack("C*", 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $key = $this->fixKey($key);
        $key = $this->derivateKey($key, $this->sp_salt_key, 65536, 256);
        $encodedEncryptedData = base64_encode(openssl_encrypt($data, $this->openssl_cipher_name, $key, OPENSSL_RAW_DATA, $iv));
        return $encodedEncryptedData;
    }

    protected function decrypt($data, $key) {
        $iv = pack("C*", 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $key = $this->fixKey($key);
        $key = $this->derivateKey($key, $this->sp_salt_key, 65536, 256);
        $decryptedData = openssl_decrypt(base64_decode($data), $this->openssl_cipher_name, $key, OPENSSL_RAW_DATA, $iv);
        return $decryptedData;
    }

    protected function derivateKey($password, $salt, $iterations, $keyLengthBits) {
        return hash_pbkdf2('sha256', $password, $salt, $iterations, $keyLengthBits / 8, TRUE);
    }

    protected function fixKey($key) {
        if (strlen($key) < $this->key_len) { //0 pad to len 35
            return str_pad($key, $this->key_len, "0");
        }
        if (strlen($key) > $this->key_len) { //truncate to 35 bytes
            return substr($key, 0, $this->key_len);
        }
        return $key;
    }

    protected function getCheckSum($saltKey, $orderNo, $amount, $TransactionMethod, $BankCode, $VPA, $CardNumber, $ExpiryDate, $CVV) {
        $dataString = $orderNo . "," . $amount . "," . $TransactionMethod . "," . $BankCode . "," . $VPA . "," . $CardNumber . "," . $ExpiryDate . "," . $CVV;
        $saltKey = base64_encode($saltKey);
        $hashValue = hash_hmac('sha512', $dataString, $saltKey, TRUE);
        $hashValue = bin2hex($hashValue);
        $hashValue = strtoupper($hashValue);
        return $hashValue;
    }

    protected function createCheckSumTxnResp($saltKey, $orderNo, $amount, $RespCode, $RespMessage) {
        $dataString = $orderNo . "," . $amount . "," . $RespCode . "," . $RespMessage;
        $saltKey = base64_encode($saltKey);
        $hashValue = hash_hmac('sha512', $dataString, $saltKey, TRUE);
        $hashValue = bin2hex($hashValue);
        $hashValue = strtoupper($hashValue);
        return $hashValue;
    }

    protected function getApiCheckSum($saltKey, $orderNo) {
        $dataString = $orderNo;
        $saltKey = base64_encode($saltKey);
        $hashValue = hash_hmac('sha512', $dataString, $saltKey, TRUE);
        $hashValue = bin2hex($hashValue);
        $hashValue = strtoupper($hashValue);
        return $hashValue;
    }

    protected function createCheckSumStatusResp($saltKey, $orderNo, $amount, $RespCode, $RespMessage) {
        $dataString = $orderNo . "," . $amount . "," . $RespCode . "," . $RespMessage;
        $saltKey = base64_encode($saltKey);
        $hashValue = hash_hmac('sha512', $dataString, $saltKey, TRUE);
        $hashValue = bin2hex($hashValue);
        $hashValue = strtoupper($hashValue);
        return $hashValue;
    }

    // public function getTrnStatus() {
    //     $encryptStatus = $this->getEnrypt('TrnStatus');
    //     $statusCheckSum = $this->getApiCheckSum($this->saltKey, $this->orderNo);
    //     $mid = $this->mid;
    //     $statusArr = array();
    //     $statusArr['mid'] = $mid;
    //     $statusArr['encryptStatus'] = $encryptStatus;
    //     $statusArr['statusCheckSum'] = $statusCheckSum;
    //     $statusArr = http_build_query($statusArr);
    //     $url = $this->PGStatusCheck_URL;
    //     $return = $this->excuteSilentPost($url, $statusArr);
    //     return $this->parseStatusResp($return);
    // }
    
    // protected function excuteSilentPost($url, $param) {
    //     $postUrl = $url;
    //     $result = "";
    //     $curl = curl_init($postUrl);
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //     curl_setopt($curl, CURLOPT_HEADER, 0);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($curl, CURLOPT_POST, TRUE);
    //     curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
    //     $result = curl_exec($curl);
    //     $error = curl_error($curl);
    //     curl_close($curl);
    //     return $result;
    // }

    // public function getTrnCancel() {
    //     $encryptCancel = $this->getEnrypt('TrnCancel');
    //     $cancelCheckSum = $this->getApiCheckSum($this->SaltKey, $this->OrderNo);
    //     $mid = $this->Mid;
    //     $cancelArr = array();
    //     $cancelArr['mid'] = $mid;
    //     $cancelArr['encryptCancel'] = $encryptCancel;
    //     $cancelArr['cancelCheckSum'] = $cancelCheckSum;

    //     $url = $this->PGCancel_URL;
    //     $return = $this->excuteSilentPost($url, $cancelArr);
    //     return $this->parseCancelResp($return);
    // }

    // protected function parseCancelResp($data) {
    //     $ResponseArray = array();
    //     $returnArray = array();
    //     $response = json_decode($data);
    //     if (!isset($response->errorMsg)) {
    //         $respData = $response->respData;
    //         $Mid = $response->mid;
    //         $checkSum = $response->checkSum;
    //         if ($Mid == $this->Mid) {
    //             $response = $this->decrypt($respData, $this->SecretKey);
    //             $respArray = explode(',', $response);
    //             $hashRespSeq = explode('|', $this->ResponseSequenceCancel);
    //             $i = 0;
    //             foreach ($hashRespSeq as $hash_var) {
    //                 $returnArray[$hash_var] = $respArray[$i];
    //                 $i++;
    //             }
    //             $CheckSumStatusResp = $this->createCheckSumCancelResp($this->SaltKey, $returnArray['OrderNo'], $returnArray['CancelAmount'], $returnArray['RespCode'], $returnArray['RespMessage']);
    //             if (strcmp($checkSum, $CheckSumStatusResp) == 0) {
    //                 $ResponseArray['Mid'] = $returnArray['Mid'];
    //                 $ResponseArray['OrderNo'] = $returnArray['OrderNo'];
    //                 $ResponseArray['TxnRefNo'] = $returnArray['TxnRefNo'];
    //                 $ResponseArray['CancelAmount'] = $returnArray['CancelAmount'] / 100;
    //                 $ResponseArray['CurrencyName'] = $returnArray['CurrencyName'];
    //                 $ResponseArray['MeTransReqType'] = $returnArray['MeTransReqType'];
    //                 $ResponseArray['RespCode'] = $returnArray['RespCode'];
    //                 $ResponseArray['RespMessage'] = $returnArray['RespMessage'];
    //                 $ResponseArray['TxnRespDate'] = $returnArray['TxnRespDate'];
    //                 $ResponseArray['CancelOrderNo'] = $returnArray['CancelOrderNo'];
    //                 $ResponseArray['CancelTxnRefNo'] = $returnArray['CancelTxnRefNo'];
    //             } else {
    //                 $ResponseArray['Error'] = 'CheckSum Miss Match!';
    //             }
    //         } else {
    //             $ResponseArray['Error'] = 'Mid Miss Match!';
    //         }
    //     } else {
    //         $ResponseArray['Error'] = $response->errorMsg;
    //     }
    //     return json_encode($ResponseArray, JSON_UNESCAPED_SLASHES);
    //     exit();
    // }

    // public function getTrnRefund() {
    //     $encryptRefund = $this->getEnrypt('TrnRefund');
    //     $refundCheckSum = $this->getApiCheckSum($this->SaltKey, $this->OrderNo);
    //     $mid = $this->Mid;
    //     $statusArr = array();
    //     $statusArr['mid'] = $mid;
    //     $statusArr['encryptRefund'] = $encryptRefund;
    //     $statusArr['refundCheckSum'] = $refundCheckSum;
    //     $url = $this->PGRefund_URL;
    //     $return = $this->excuteSilentPost($url, $statusArr);
    //     return $this->parseRefundResp($return);
    // }

    // protected function parseRefundResp($data) {
    //     $ResponseArray = array();
    //     $returnArray = array();
    //     $response = json_decode($data);
    //     if (!isset($response->errorMsg)) {
    //         $respData = $response->respData;
    //         $Mid = $response->mid;
    //         $checkSum = $response->checkSum;
    //         if ($Mid == $this->Mid) {
    //             $response = $this->decrypt($respData, $this->SecretKey);
    //             $respArray = explode(',', $response);
    //             $hashRespSeq = explode('|', $this->ResponseSequenceRefund);
    //             $i = 0;
    //             foreach ($hashRespSeq as $hash_var) {
    //                 $returnArray[$hash_var] = $respArray[$i];
    //                 $i++;
    //             }
    //             $CheckSumStatusResp = $this->createCheckSumRefundResp($this->SaltKey, $returnArray['RefundOrderNo'], $returnArray['RefundAmount'], $returnArray['RespCode'], $returnArray['RespMessage']);
    //             if (strcmp($checkSum, $CheckSumStatusResp) == 0) {
    //                 $ResponseArray['Mid'] = $returnArray['Mid'];
    //                 $ResponseArray['OrderNo'] = $returnArray['OrderNo'];
    //                 $ResponseArray['TxnRefNo'] = $returnArray['TxnRefNo'];
    //                 $ResponseArray['RefundAmount'] = $returnArray['RefundAmount'] / 100;
    //                 $ResponseArray['CurrencyName'] = $returnArray['CurrencyName'];
    //                 $ResponseArray['MeTransReqType'] = $returnArray['MeTransReqType'];
    //                 $ResponseArray['RespCode'] = $returnArray['RespCode'];
    //                 $ResponseArray['RespMessage'] = $returnArray['RespMessage'];
    //                 $ResponseArray['TxnRespDate'] = $returnArray['TxnRespDate'];
    //                 $ResponseArray['RefundOrderNo'] = $returnArray['RefundOrderNo'];
    //                 $ResponseArray['RefundTxnRefNo'] = $returnArray['RefundTxnRefNo'];
    //             } else {
    //                 $ResponseArray['Error'] = 'CheckSum Miss Match!';
    //             }
    //         } else {
    //             $ResponseArray['Error'] = 'Mid Miss Match!';
    //         }
    //     } else {
    //         $ResponseArray['Error'] = $response->errorMsg;
    //     }
    //     return json_encode($ResponseArray, JSON_UNESCAPED_SLASHES);
    //     exit();
    // }

    // protected function createCheckSumCancelResp($saltKey, $cancelOrderNo, $cancelAmount, $RespCode, $RespMessage) {
    //     $dataString = $cancelOrderNo . "," . $cancelAmount . "," . $RespCode . "," . $RespMessage;
    //     $saltKey = base64_encode($saltKey);
    //     $hashValue = hash_hmac('sha512', $dataString, $saltKey, TRUE);
    //     $hashValue = bin2hex($hashValue);
    //     $hashValue = strtoupper($hashValue);
    //     return $hashValue;
    // }

    // protected function createCheckSumRefundResp($saltKey, $RefundOrderNo, $RefundAmount, $RespCode, $RespMessage) {
    //     $dataString = $RefundOrderNo . "," . $RefundAmount . "," . $RespCode . "," . $RespMessage;
    //     $saltKey = base64_encode($saltKey);
    //     $hashValue = hash_hmac('sha512', $dataString, $saltKey, TRUE);
    //     $hashValue = bin2hex($hashValue);
    //     $hashValue = strtoupper($hashValue);
    //     return $hashValue;
    // }

    // protected function checkSumValidation($saltKey, $orderNo) {
    //     $dataString = $orderNo;
    //     $saltKey = base64_encode($saltKey);
    //     $hashValue = hash_hmac('sha512', $dataString, $saltKey, TRUE);
    //     $hashValue = bin2hex($hashValue);
    //     $hashValue = strtoupper($hashValue);
    //     return $hashValue;
    // }

}

?>
<?php

namespace App\Helpers;

class Sambhavpay
{
    protected $openssl_cipher_name = "aes-256-cbc";
    protected $key_len = 35;
    protected $sp_salt_key = 'Asdf@1234';
    protected $PG_URL = 'https://pg.sambhavpay.com/MotoTxn';
    protected $PGStatusCheck_URL = 'https://pg.sambhavpay.com/StatusPG';
    protected $PGCancel_URL = 'https://pg.sambhavpay.com/CancelPG';
    protected $PGRefund_URL = 'https://pg.sambhavpay.com/RefundPG';
    protected $OrderNo; //start with "ORD" (Alphanumeric) example"ORD1245789631542" (Always should be unique for every transaction)
    protected $TotalAmount; // "Amount will be in paisa format.Rs. 10/-(10*100=1000)"
    protected $CurrencyName; // INR (3 digits)
    protected $MeTransReqType; // S/P
    protected $RecurringPeriod; // NA or W or M
    protected $RecurringDay; // Day means 25th-dec or 10th-may (2 digit)
    protected $NoOfRecurring;
    protected $Mid; //Provided by SambhavPay (ex: 900000000000007)
    protected $AddField1;
    protected $AddField2;
    protected $AddField3;
    protected $AddField4;
    protected $AddField5;
    protected $AddField6;
    protected $AddField7;
    protected $AddField8;
    protected $AddField9;
    protected $AddField10;
    protected $Address;
    protected $City;
    protected $State;
    protected $Pincode;
    protected $Source;
    protected $EmailId;
    protected $MobileNo;
    protected $EmailInvoice;
    protected $MobileInvoice;
    protected $ResponseUrl;
    protected $UPIType;
    protected $Optional2;
    protected $CustomerName;
    protected $Tid;
    protected $SecretKey;
    protected $SaltKey;
    // For Seamless Transaction
    protected $TransactionMethod;
    protected $BankCode;
    protected $VPA;
    protected $CardNumber;
    protected $ExpiryDate;
    protected $CVV;
    protected $CardHolderName;
    // End//
    protected $RequestSequence = "Mid|OrderNo|TotalAmount|CurrencyName|MeTransReqType|AddField1|AddField2|AddField3|AddField4|AddField5|AddField6|AddField7|AddField8|AddField9|AddField10|EmailId|MobileNo|Address|City|State|Pincode|TransactionMethod|BankCode|VPA|CardNumber|ExpiryDate|CVV|CustomerName|ResponseUrl|UPIType";
    protected $ResponseSequence = "Mid|OrderNo|TxnRefNo|TotalAmount|CurrencyName|MeTransReqType|AddField1|AddField2|AddField3|AddField4|AddField5|AddField6|AddField7|AddField8|AddField9|AddField10|EmailId|MobileNo|Address|City|State|Pincode|RespCode|RespMessage|PayAmount|TxnRespDate|UPIString";
    // For Status Check API
    protected $TxnRefNo;
    protected $TxnAmount;
    protected $RequestSequenceStatus = "Mid|TotalAmount|TxnRefNo|OrderNo";
    protected $ResponseSequenceStatus = "Mid|OrderNo|TxnRefNo|TotalAmount|CurrencyName|MeTransReqType|AddField1|AddField2|AddField3|AddField4|AddField5|AddField6|AddField7|AddField8|AddField9|AddField10|EmailId|MobileNo|Address|City|State|Pincode|RespCode|RespMessage|PayAmount|TxnRespDate|UPIString";
    // For Cancel TRN
    protected $CancelAmount;
    protected $CancelOrderNo;
    protected $RequestSequenceCancel = "Mid|TotalAmount|TxnRefNo|OrderNo|MeTransReqType|CancelAmount|CancelOrderNo";
    protected $ResponseSequenceCancel = "Mid|OrderNo|TxnRefNo|CancelAmount|CurrencyName|MeTransReqType|RespCode|RespMessage|TxnRespDate|CancelOrderNo|CancelTxnRefNo";
    // For Refund TRN
    protected $RequestSequenceRefund = "Mid|TotalAmount|TxnRefNo|OrderNo|MeTransReqType|RefundAmount|RefundOrderNo";
    protected $ResponseSequenceRefund = "Mid|OrderNo|TxnRefNo|RefundAmount|CurrencyName|MeTransReqType|RespCode|RespMessage|TxnRespDate|RefundOrderNo|RefundTxnRefNo";
    protected $RefundAmount;
    protected $RefundOrderNo;

    public static function setOrderNo($OrderNo) {
        $this->OrderNo = $OrderNo;
        return $this;
    }

    public static function setTotalAmount($TotalAmount) {
        if (isset($TotalAmount) && !empty($TotalAmount)) {
            $this->TotalAmount = $TotalAmount * 100; // Convert it in to paisa
        } else {
            $this->TotalAmount = ''; // Convert it in to paisa
        }
        return $this;
    }

    public static function setCurrencyName($CurrencyName) {
        $this->CurrencyName = $CurrencyName;
        return $this;
    }

    public static function setMeTransReqType($MeTransReqType) {
        if (isset($MeTransReqType) && !empty($MeTransReqType)) {
            $this->MeTransReqType = $MeTransReqType;
        } else {
            $this->MeTransReqType = 'S';
        }
        return $this;
    }

    public static function setRecurringPeriod($RecurringPeriod) {
        if (isset($RecurringPeriod) && !empty($RecurringPeriod)) {
            $this->RecurringPeriod = $RecurringPeriod;
        } else {
            $this->RecurringPeriod = 'NA';
        }
        return $this;
    }

    public static function setRecurringDay($RecurringDay) {
        if (isset($RecurringDay) && !empty($RecurringDay)) {
            $this->RecurringDay = $RecurringDay;
        } else {
            $this->RecurringDay = '';
        }
        return $this;
    }

    public static function setNoOfRecurring($NoOfRecurring) {
        if (isset($NoOfRecurring) && !empty($NoOfRecurring)) {
            $this->NoOfRecurring = $NoOfRecurring;
        } else {
            $this->NoOfRecurring = '';
        }
        return $this;
    }

    public static function setMid($Mid) {
        $this->Mid = $Mid;
        return $this;
    }

    public static function setAddField1($AddField1) {
        if (isset($AddField1) && !empty($AddField1)) {
            $this->AddField1 = $AddField1;
        } else {
            $this->AddField1 = '';
        }
        return $this;
    }

    public static function setAddField2($AddField2) {
        if (isset($AddField2) && !empty($AddField2)) {
            $this->AddField2 = $AddField2;
        } else {
            $this->AddField2 = '';
        }
        return $this;
    }

    public static function setAddField3($AddField3) {
        if (isset($AddField3) && !empty($AddField3)) {
            $this->AddField3 = $AddField3;
        } else {
            $this->AddField3 = '';
        }
        return $this;
    }

    public static function setAddField4($AddField4) {
        if (isset($AddField4) && !empty($AddField4)) {
            $this->AddField4 = $AddField4;
        } else {
            $this->AddField4 = '';
        }
        return $this;
    }

    public static function setAddField5($AddField5) {
        if (isset($AddField5) && !empty($AddField5)) {
            $this->AddField5 = $AddField5;
        } else {
            $this->AddField5 = '';
        }
        return $this;
    }

    public static function setAddField6($AddField6) {
        if (isset($AddField6) && !empty($AddField6)) {
            $this->AddField6 = $AddField6;
        } else {
            $this->AddField6 = '';
        }
        return $this;
    }

    public static function setAddField7($AddField7) {
        if (isset($AddField7) && !empty($AddField7)) {
            $this->AddField7 = $AddField7;
        } else {
            $this->AddField7 = '';
        }
        return $this;
    }

    public static function setAddField8($AddField8) {
        if (isset($AddField8) && !empty($AddField8)) {
            $this->AddField8 = $AddField8;
        } else {
            $this->AddField8 = '';
        }
        return $this;
    }

    public static function setAddField9($AddField9) {
        if (isset($AddField9) && !empty($AddField9)) {
            $this->AddField9 = $AddField9;
        } else {
            $this->AddField9 = '';
        }
        return $this;
    }

    public static function setAddField10($AddField10) {
        if (isset($AddField10) && !empty($AddField10)) {
            $this->AddField10 = $AddField10;
        } else {
            $this->AddField10 = '';
        }
        return $this;
    }

    public static function setAddress($Address) {
        if (isset($Address) && !empty($Address)) {
            $this->Address = $Address;
        } else {
            $this->Address = '';
        }
        return $this;
    }

    public static function setCity($City) {
        if (isset($City) && !empty($City)) {
            $this->City = $City;
        } else {
            $this->City = '';
        }
        return $this;
    }

    public static function setState($State) {
        if (isset($State) && !empty($State)) {
            $this->State = $State;
        } else {
            $this->State = '';
        }
        return $this;
    }

    public static function setPincode($Pincode) {
        if (isset($Pincode) && !empty($Pincode)) {
            $this->Pincode = $Pincode;
        } else {
            $this->Pincode = '';
        }
        return $this;
    }

    public static function setSource($Source) {
        if (isset($Source) && !empty($Source)) {
            $this->Source = $Source;
        } else {
            $this->Source = '';
        }
        return $this;
    }

    public static function setEmailId($EmailId) {
        if (isset($EmailId) && !empty($EmailId)) {
            $this->EmailId = $EmailId;
        } else {
            $this->EmailId = '';
        }
        return $this;
    }

    public static function setMobileNo($MobileNo) {
        if (isset($MobileNo) && !empty($MobileNo)) {
            $this->MobileNo = $MobileNo;
        } else {
            $this->MobileNo = '';
        }
        return $this;
    }

    public static function setEmailInvoice($EmailInvoice) {
        if (isset($EmailInvoice) && !empty($EmailInvoice)) {
            $this->EmailInvoice = $EmailInvoice;
        } else {
            $this->EmailInvoice = '';
        }
        return $this;
    }

    public static function setMobileInvoice($MobileInvoice) {
        if (isset($MobileInvoice) && !empty($MobileInvoice)) {
            $this->MobileInvoice = $MobileInvoice;
        } else {
            $this->MobileInvoice = '';
        }
        return $this;
    }

    public static function setUPIType($UPIType) {
        if (isset($UPIType) && !empty($UPIType)) {
            $this->UPIType = $UPIType;
        } else {
            $this->UPIType = '';
        }
        return $this;
    }

    public static function setOptional2($Optional2) {
        if (isset($Optional2) && !empty($Optional2)) {
            $this->Optional2 = $Optional2;
        } else {
            $this->Optional2 = '';
        }
        return $this;
    }

    public static function setCustomerName($CustomerName) {
        if (isset($CustomerName) && !empty($CustomerName)) {
            $this->CustomerName = $CustomerName;
        } else {
            $this->CustomerName = '';
        }
        return $this;
    }

    public static function setResponseUrl($ResponseUrl) {
        $this->ResponseUrl = $ResponseUrl;
        return $this;
    }

    public static function setTid($Tid) {
        if (isset($Tid) && !empty($Tid)) {
            $this->Tid = $Tid;
        } else {
            $this->Tid = '';
        }
        return $this;
    }

    public static function setSecretKey($SecretKey) {
        $this->SecretKey = $SecretKey;
        return $this;
    }

    public static function setSaltKey($SaltKey) {
        $this->SaltKey = $SaltKey;
        return $this;
    }

    public static function setTrnRefNo($TrnRefNo) {
        if (isset($TrnRefNo) && !empty($TrnRefNo)) {
            $this->TrnRefNo = $TrnRefNo;
        } else {
            $this->TrnRefNo = 'null';
        }
        return $this;
    }

    public static function setTxnRefNo($TxnRefNo) {
        if (isset($TxnRefNo) && !empty($TxnRefNo)) {
            $this->TxnRefNo = $TxnRefNo;
        } else {
            $this->TxnRefNo = '';
        }
        return $this;
    }

    public static function setCancelAmount($CancelAmount) {
        if (isset($CancelAmount) && !empty($CancelAmount)) {
            $this->CancelAmount = $CancelAmount * 100; // Convert in to paisa 
        } else {
            $this->CancelAmount = '';
        }
        return $this;
    }

    public static function setCancelOrderNo($CancelOrderNo) {
        if (isset($CancelOrderNo) && !empty($CancelOrderNo)) {
            $this->CancelOrderNo = $CancelOrderNo;
        } else {
            $this->CancelOrderNo = '';
        }
        return $this;
    }

    public static function setRefundAmount($RefundAmount) {
        if (isset($RefundAmount) && !empty($RefundAmount)) {
            $this->RefundAmount = $RefundAmount * 100; // Covert in to paisa
        } else {
            $this->RefundAmount = '';
        }
        return $this;
    }

    public static function setRefundOrderNo($RefundOrderNo) {
        if (isset($RefundOrderNo) && !empty($RefundOrderNo)) {
            $this->RefundOrderNo = $RefundOrderNo;
        } else {
            $this->RefundOrderNo = '';
        }
        return $this;
    }

    // For Seamless kit
    public static function setTransactionMethod($TransactionMethod) {
        if (isset($TransactionMethod) && !empty($TransactionMethod)) {
            $this->TransactionMethod = $TransactionMethod;
        } else {
            $this->TransactionMethod = '';
        }
        return $this;
    }

    public static function setBankCode($BankCode) {
        if (isset($BankCode) && !empty($BankCode)) {
            $this->BankCode = $BankCode;
        } else {
            $this->BankCode = '';
        }
        return $this;
    }

    public static function setVPA($VPA) {
        if (isset($VPA) && !empty($VPA)) {
            $this->VPA = $VPA;
        } else {
            $this->VPA = '';
        }
        return $this;
    }

    public static function setCardNumber($CardNumber) {
        if (isset($CardNumber) && !empty($CardNumber)) {
            $this->CardNumber = $CardNumber;
        } else {
            $this->CardNumber = '';
        }
        return $this;
    }

    public static function setExpiryDate($ExpiryDate) {
        if (isset($ExpiryDate) && !empty($ExpiryDate)) {
            $this->ExpiryDate = $ExpiryDate;
        } else {
            $this->ExpiryDate = '';
        }
        return $this;
    }

    public static function setCVV($CVV) {
        if (isset($CVV) && !empty($CVV)) {
            $this->CVV = $CVV;
        } else {
            $this->CVV = '';
        }
        return $this;
    }

    public static function setCardHolderName($CardHolderName) {
        if (isset($CardHolderName) && !empty($CardHolderName)) {
            $this->CardHolderName = $CardHolderName;
        } else {
            $this->CardHolderName = '';
        }
        return $this;
    }

    public static function doPayment() {
        $encryptReq = $this->getEnrypt('TrnPayment');
        $checkSum = $this->getCheckSum($this->SaltKey, $this->OrderNo, $this->TotalAmount, $this->TransactionMethod, $this->BankCode, $this->VPA, $this->CardNumber, $this->ExpiryDate, $this->CVV);
        $mid = $this->Mid;
        echo '<form method="post" name="payment" action="' . $this->PG_URL . '">
            <input type="hidden" name="mid" value="' . $mid . '" />
        <input type="hidden" name="encryptReq" value="' . $encryptReq . '" />
                <input type="hidden" name="checkSum" value="' . $checkSum . '" />
        </form>
        <script language="javascript">document.payment.submit();</script>';
    }

    public static function getResponse() {
        $respData = $_POST['respData'];
        $Mid = $_POST['mid'];
        $checkSum = $_POST['checkSum'];
        if ($Mid == $this->Mid) {            
            $response = $this->decrypt($respData, $this->SecretKey);
            $respArray = explode(',', $response);
            $hashRespSeq = explode('|', $this->ResponseSequence);
            $i = 0;
            $returnArray = array();
            foreach ($hashRespSeq as $hash_var) {
                $returnArray[$hash_var] = $respArray[$i];
                $i++;
            }
            
            $CheckSumTxnResp = $this->createCheckSumTxnResp($this->SaltKey, $returnArray['OrderNo'], $returnArray['PayAmount'], $returnArray['RespCode'], $returnArray['RespMessage']);
            if (strcmp($checkSum, $CheckSumTxnResp) == 0) {
                $ResponseArray = array();
                $ResponseArray['Mid'] = $returnArray['Mid'];
                $ResponseArray['OrderNo'] = $returnArray['OrderNo'];
                $ResponseArray['TxnRefNo'] = $returnArray['TxnRefNo'];
                $ResponseArray['TotalAmount'] = $returnArray['TotalAmount'] / 100;
                $ResponseArray['CurrencyName'] = $returnArray['CurrencyName'];
                $ResponseArray['MeTransReqType'] = $returnArray['MeTransReqType'];
                $ResponseArray['AddField1'] = $returnArray['AddField1'];
                $ResponseArray['AddField2'] = $returnArray['AddField2'];
                $ResponseArray['AddField3'] = $returnArray['AddField3'];
                $ResponseArray['AddField4'] = $returnArray['AddField4'];
                $ResponseArray['AddField5'] = $returnArray['AddField5'];
                $ResponseArray['AddField6'] = $returnArray['AddField6'];
                $ResponseArray['AddField7'] = $returnArray['AddField7'];
                $ResponseArray['AddField8'] = $returnArray['AddField8'];
                $ResponseArray['AddField9'] = $returnArray['AddField9'];
                $ResponseArray['AddField10'] = $returnArray['AddField10'];
                $ResponseArray['EmailId'] = $returnArray['EmailId'];
                $ResponseArray['MobileNo'] = $returnArray['MobileNo'];
                $ResponseArray['Address'] = $returnArray['Address'];
                $ResponseArray['City'] = $returnArray['City'];
                $ResponseArray['State'] = $returnArray['State'];
                $ResponseArray['Pincode'] = $returnArray['Pincode'];
                $ResponseArray['RespCode'] = $returnArray['RespCode'];
                $ResponseArray['RespMessage'] = $returnArray['RespMessage'];
                $ResponseArray['PayAmount'] = $returnArray['PayAmount'] / 100;
                $ResponseArray['TxnRespDate'] = $returnArray['TxnRespDate'];
                $ResponseArray['UPIString'] = $returnArray['UPIString'];
                return json_encode($ResponseArray, JSON_UNESCAPED_SLASHES);
            } else {
                return "CheckSum Miss Match!";
            }
        }
    }

    protected function getEnrypt($msg = 'TrnPayment') {
        if ($msg == 'TrnPayment') {
            $hashVarsSeq = explode('|', $this->RequestSequence);
        }
        if ($msg == 'TrnStatus') {
            $hashVarsSeq = explode('|', $this->RequestSequenceStatus);
        }
        if ($msg == 'TrnCancel') {
            $hashVarsSeq = explode('|', $this->RequestSequenceCancel);
        }
        if ($msg == 'TrnRefund') {
            $hashVarsSeq = explode('|', $this->RequestSequenceRefund);
        }
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
        return $this->encrypt($hash_string, $this->SecretKey);
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

    public static function getTrnStatus() {
        $encryptStatus = $this->getEnrypt('TrnStatus');
        $statusCheckSum = $this->getApiCheckSum($this->SaltKey, $this->OrderNo);
        $mid = $this->Mid;
        $statusArr = array();
        $statusArr['mid'] = $mid;
        $statusArr['encryptStatus'] = $encryptStatus;
        $statusArr['statusCheckSum'] = $statusCheckSum;
        $statusArr = http_build_query($statusArr);
        $url = $this->PGStatusCheck_URL;
        $return = $this->excuteSilentPost($url, $statusArr);
        return $this->parseStatusResp($return);
    }

    protected function parseStatusResp($data) {
        $ResponseArray = array();
        $returnArray = array();
        $response = json_decode($data);
        if (!isset($response->errorMsg)) {
            $respData = $response->respData;
            $Mid = $response->mid;
            $checkSum = $response->checkSum;
            if ($Mid == $this->Mid) {
                $response = $this->decrypt($respData, $this->SecretKey);
                $respArray = explode(',', $response);
                $hashRespSeq = explode('|', $this->ResponseSequenceStatus);
                $i = 0;
                foreach ($hashRespSeq as $hash_var) {
                    $returnArray[$hash_var] = $respArray[$i];
                    $i++;
                }
                $CheckSumStatusResp = $this->createCheckSumStatusResp($this->SaltKey, $returnArray['OrderNo'], $returnArray['PayAmount'], $returnArray['RespCode'], $returnArray['RespMessage']);
                if (strcmp($checkSum, $CheckSumStatusResp) == 0) {
                    $ResponseArray['Mid'] = $returnArray['Mid'];
                    $ResponseArray['OrderNo'] = $returnArray['OrderNo'];
                    $ResponseArray['TxnRefNo'] = $returnArray['TxnRefNo'];
                    $ResponseArray['TotalAmount'] = $returnArray['TotalAmount'] / 100;
                    $ResponseArray['CurrencyName'] = $returnArray['CurrencyName'];
                    $ResponseArray['MeTransReqType'] = $returnArray['MeTransReqType'];
                    $ResponseArray['AddField1'] = $returnArray['AddField1'];
                    $ResponseArray['AddField2'] = $returnArray['AddField2'];
                    $ResponseArray['AddField3'] = $returnArray['AddField3'];
                    $ResponseArray['AddField4'] = $returnArray['AddField4'];
                    $ResponseArray['AddField5'] = $returnArray['AddField5'];
                    $ResponseArray['AddField6'] = $returnArray['AddField6'];
                    $ResponseArray['AddField7'] = $returnArray['AddField7'];
                    $ResponseArray['AddField8'] = $returnArray['AddField8'];
                    $ResponseArray['AddField9'] = $returnArray['AddField9'];
                    $ResponseArray['AddField10'] = $returnArray['AddField10'];
                    $ResponseArray['EmailId'] = $returnArray['EmailId'];
                    $ResponseArray['MobileNo'] = $returnArray['MobileNo'];
                    $ResponseArray['Address'] = $returnArray['Address'];
                    $ResponseArray['City'] = $returnArray['City'];
                    $ResponseArray['State'] = $returnArray['State'];
                    $ResponseArray['Pincode'] = $returnArray['Pincode'];
                    $ResponseArray['RespCode'] = $returnArray['RespCode'];
                    $ResponseArray['RespMessage'] = $returnArray['RespMessage'];
                    $ResponseArray['PayAmount'] = $returnArray['PayAmount'] / 100;
                    $ResponseArray['TxnRespDate'] = $returnArray['TxnRespDate'];
                    $ResponseArray['UPIString'] = $returnArray['UPIString'];
                } else {
                    $ResponseArray['Error'] = 'CheckSum Miss Match!';
                }
            } else {
                $ResponseArray['Error'] = 'Mid Miss Match!';
            }
        } else {
            $ResponseArray['Error'] = $response->errorMsg;
        }
        return json_encode($ResponseArray, JSON_UNESCAPED_SLASHES);
        exit();
    }

    public static function getTrnCancel() {
        $encryptCancel = $this->getEnrypt('TrnCancel');
        $cancelCheckSum = $this->getApiCheckSum($this->SaltKey, $this->OrderNo);
        $mid = $this->Mid;
        $cancelArr = array();
        $cancelArr['mid'] = $mid;
        $cancelArr['encryptCancel'] = $encryptCancel;
        $cancelArr['cancelCheckSum'] = $cancelCheckSum;

        $url = $this->PGCancel_URL;
        $return = $this->excuteSilentPost($url, $cancelArr);
        return $this->parseCancelResp($return);
    }

    protected function parseCancelResp($data) {
        $ResponseArray = array();
        $returnArray = array();
        $response = json_decode($data);
        if (!isset($response->errorMsg)) {
            $respData = $response->respData;
            $Mid = $response->mid;
            $checkSum = $response->checkSum;
            if ($Mid == $this->Mid) {
                $response = $this->decrypt($respData, $this->SecretKey);
                $respArray = explode(',', $response);
                $hashRespSeq = explode('|', $this->ResponseSequenceCancel);
                $i = 0;
                foreach ($hashRespSeq as $hash_var) {
                    $returnArray[$hash_var] = $respArray[$i];
                    $i++;
                }
                $CheckSumStatusResp = $this->createCheckSumCancelResp($this->SaltKey, $returnArray['OrderNo'], $returnArray['CancelAmount'], $returnArray['RespCode'], $returnArray['RespMessage']);
                if (strcmp($checkSum, $CheckSumStatusResp) == 0) {
                    $ResponseArray['Mid'] = $returnArray['Mid'];
                    $ResponseArray['OrderNo'] = $returnArray['OrderNo'];
                    $ResponseArray['TxnRefNo'] = $returnArray['TxnRefNo'];
                    $ResponseArray['CancelAmount'] = $returnArray['CancelAmount'] / 100;
                    $ResponseArray['CurrencyName'] = $returnArray['CurrencyName'];
                    $ResponseArray['MeTransReqType'] = $returnArray['MeTransReqType'];
                    $ResponseArray['RespCode'] = $returnArray['RespCode'];
                    $ResponseArray['RespMessage'] = $returnArray['RespMessage'];
                    $ResponseArray['TxnRespDate'] = $returnArray['TxnRespDate'];
                    $ResponseArray['CancelOrderNo'] = $returnArray['CancelOrderNo'];
                    $ResponseArray['CancelTxnRefNo'] = $returnArray['CancelTxnRefNo'];
                } else {
                    $ResponseArray['Error'] = 'CheckSum Miss Match!';
                }
            } else {
                $ResponseArray['Error'] = 'Mid Miss Match!';
            }
        } else {
            $ResponseArray['Error'] = $response->errorMsg;
        }
        return json_encode($ResponseArray, JSON_UNESCAPED_SLASHES);
        exit();
    }

    public static function getTrnRefund() {
        $encryptRefund = $this->getEnrypt('TrnRefund');
        $refundCheckSum = $this->getApiCheckSum($this->SaltKey, $this->OrderNo);
        $mid = $this->Mid;
        $statusArr = array();
        $statusArr['mid'] = $mid;
        $statusArr['encryptRefund'] = $encryptRefund;
        $statusArr['refundCheckSum'] = $refundCheckSum;
        $url = $this->PGRefund_URL;
        $return = $this->excuteSilentPost($url, $statusArr);
        return $this->parseRefundResp($return);
    }

    protected function parseRefundResp($data) {
        $ResponseArray = array();
        $returnArray = array();
        $response = json_decode($data);
        if (!isset($response->errorMsg)) {
            $respData = $response->respData;
            $Mid = $response->mid;
            $checkSum = $response->checkSum;
            if ($Mid == $this->Mid) {
                $response = $this->decrypt($respData, $this->SecretKey);
                $respArray = explode(',', $response);
                $hashRespSeq = explode('|', $this->ResponseSequenceRefund);
                $i = 0;
                foreach ($hashRespSeq as $hash_var) {
                    $returnArray[$hash_var] = $respArray[$i];
                    $i++;
                }
                $CheckSumStatusResp = $this->createCheckSumRefundResp($this->SaltKey, $returnArray['RefundOrderNo'], $returnArray['RefundAmount'], $returnArray['RespCode'], $returnArray['RespMessage']);
                if (strcmp($checkSum, $CheckSumStatusResp) == 0) {
                    $ResponseArray['Mid'] = $returnArray['Mid'];
                    $ResponseArray['OrderNo'] = $returnArray['OrderNo'];
                    $ResponseArray['TxnRefNo'] = $returnArray['TxnRefNo'];
                    $ResponseArray['RefundAmount'] = $returnArray['RefundAmount'] / 100;
                    $ResponseArray['CurrencyName'] = $returnArray['CurrencyName'];
                    $ResponseArray['MeTransReqType'] = $returnArray['MeTransReqType'];
                    $ResponseArray['RespCode'] = $returnArray['RespCode'];
                    $ResponseArray['RespMessage'] = $returnArray['RespMessage'];
                    $ResponseArray['TxnRespDate'] = $returnArray['TxnRespDate'];
                    $ResponseArray['RefundOrderNo'] = $returnArray['RefundOrderNo'];
                    $ResponseArray['RefundTxnRefNo'] = $returnArray['RefundTxnRefNo'];
                } else {
                    $ResponseArray['Error'] = 'CheckSum Miss Match!';
                }
            } else {
                $ResponseArray['Error'] = 'Mid Miss Match!';
            }
        } else {
            $ResponseArray['Error'] = $response->errorMsg;
        }
        return json_encode($ResponseArray, JSON_UNESCAPED_SLASHES);
        exit();
    }

    protected function excuteSilentPost($url, $param) {
        $postUrl = $url;
        $result = "";
        $curl = curl_init($postUrl);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
        $result = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        return $result;
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

    protected function createCheckSumCancelResp($saltKey, $cancelOrderNo, $cancelAmount, $RespCode, $RespMessage) {
        $dataString = $cancelOrderNo . "," . $cancelAmount . "," . $RespCode . "," . $RespMessage;
        $saltKey = base64_encode($saltKey);
        $hashValue = hash_hmac('sha512', $dataString, $saltKey, TRUE);
        $hashValue = bin2hex($hashValue);
        $hashValue = strtoupper($hashValue);
        return $hashValue;
    }

    protected function createCheckSumRefundResp($saltKey, $RefundOrderNo, $RefundAmount, $RespCode, $RespMessage) {
        $dataString = $RefundOrderNo . "," . $RefundAmount . "," . $RespCode . "," . $RespMessage;
        $saltKey = base64_encode($saltKey);
        $hashValue = hash_hmac('sha512', $dataString, $saltKey, TRUE);
        $hashValue = bin2hex($hashValue);
        $hashValue = strtoupper($hashValue);
        return $hashValue;
    }

    protected function checkSumValidation($saltKey, $orderNo) {
        $dataString = $orderNo;
        $saltKey = base64_encode($saltKey);
        $hashValue = hash_hmac('sha512', $dataString, $saltKey, TRUE);
        $hashValue = bin2hex($hashValue);
        $hashValue = strtoupper($hashValue);
        return $hashValue;
    }
}
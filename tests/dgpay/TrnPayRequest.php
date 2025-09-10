<?php

require 'DigycartConfig.php';
if (isset($_POST) && !empty($_POST)) {
    $error = '';
    $Digycart = new DigyCart;
    if (isset($_POST['mid']) && !empty($_POST['mid'])) {
        $Digycart->setMid($_POST['mid']);
    } else {
        $error = $error . 'Mid Field is required.<br/>';
    }
    if (isset($_POST['secretKey']) && !empty($_POST['secretKey'])) {
        $Digycart->setSecretKey($_POST['secretKey']);
    } else {
        $error = $error . 'SecretKey Field is required.<br/>';
    }
    if (isset($_POST['saltKey']) && !empty($_POST['saltKey'])) {
        $Digycart->setSaltKey($_POST['saltKey']);
    } else {
        $error = $error . 'SaltKey Field is required.<br/>';
    }
    if (isset($_POST['orderNo']) && !empty($_POST['orderNo'])) {
        $Digycart->setOrderNo($_POST['orderNo']);
    } else {
        $error = $error . 'OrderNo. Field is required.<br/>';
    }
    if (isset($_POST['amount']) && !empty($_POST['amount'])) {
        $Digycart->setAmount($_POST['amount']);
    } else {
        $error = $error . 'Amount Field is required.<br/>';
    }
    if (isset($_POST['currency']) && !empty($_POST['currency'])) {
        $Digycart->setCurrency($_POST['currency']);
    } else {
        $error = $error . 'Currency Field is required.<br/>';
    }
    if (isset($_POST['txnReqType']) && !empty($_POST['txnReqType'])) {
        $Digycart->setTxnReqType($_POST['txnReqType']);
    } else {
        $error = $error . 'TxnReqType Field is required.<br/>';
    }

    $Digycart->setUndefinedField1($_POST['undefinedField1']);
    $Digycart->setUndefinedField2($_POST['undefinedField2']);
    $Digycart->setUndefinedField3($_POST['undefinedField3']);
    $Digycart->setUndefinedField4($_POST['undefinedField4']);
    $Digycart->setUndefinedField5($_POST['undefinedField5']);
    $Digycart->setUndefinedField6($_POST['undefinedField6']);
    $Digycart->setUndefinedField7($_POST['undefinedField7']);
    $Digycart->setUndefinedField8($_POST['undefinedField8']);
    $Digycart->setUndefinedField9($_POST['undefinedField9']);
    $Digycart->setUndefinedField10($_POST['undefinedField10']);


    if (isset($_POST['emailId']) && !empty($_POST['emailId'])) {
        $Digycart->setEmailId($_POST['emailId']);
    } else {
        $error = $error . 'EmailId Field is required.<br/>';
    }
    if (isset($_POST['mobileNo']) && !empty($_POST['mobileNo'])) {
        $Digycart->setMobileNo($_POST['mobileNo']);
    } else {
        $error = $error . 'MobileNo Field is required.<br/>';
    }

    $Digycart->setAddress($_POST['address']);
    $Digycart->setCity($_POST['city']);
    $Digycart->setState($_POST['state']);
    $Digycart->setPincode($_POST['pincode']);

    if (isset($_POST['transactionMethod']) && !empty($_POST['transactionMethod'])) {
        $Digycart->setTransactionMethod($_POST['transactionMethod']);
    } else {
        $error = $error . 'TransactionMethod Field is required.<br/>';
    }

    $Digycart->setBankCode($_POST['bankCode']);
    $Digycart->setVPA($_POST['vpa']);
    $Digycart->setCardNumber($_POST['cardNumber']);
    $Digycart->setExpiryDate($_POST['expiryDate']);
    $Digycart->setCVV($_POST['cvv']);

    if (isset($_POST['customerName']) && !empty($_POST['customerName'])) {
        $Digycart->setCustomerName($_POST['customerName']);
    } else {
        $error = $error . 'CustomerName Field is required.<br/>';
    }

    $Digycart->setRespUrl($_POST['respUrl']);

    if (isset($_POST['optional1']) && !empty($_POST['optional1'])) {
        $Digycart->setOptional1($_POST['optional1']);
    } else {
        $error = $error . 'UPIType/ Optional1 Field is required.<br/>';
    }



    if (empty($error)) {
        $json = $Digycart->doPayment();
        respHandler(json_decode($json));
    } else {
        echo $error;
    }
} else {
    echo 'oops, some error!';
}

function respHandler($jsonData){

    $mid = "500000000000020    ";
    $secretKey = "scrhi1AB0QBCoaBi0E3J0WGRBfrNKLWfZ9i";
    $saltKey = "salilQSdyu2FDjN0oV08YxcOsn8mmXqmy";
    
    $Digycart = new DigyCart;
    
    $Digycart->setMid($mid);
    $Digycart->setSecretKey($secretKey);
    $Digycart->setSaltKey($saltKey);

    $data = json_decode($jsonData->data);
    $respData = $data->respData;
    $mid = $data->mid;
    $checkSum = $data->checkSum;
    
    $response = $Digycart->getResponse($respData,$mid,$checkSum);
    printResponse($response);
}

function printResponse($res){
    $resTxnObj = json_decode($res);
    ?>
    <!-- Response Start -->
    <center>
        <table border="1">
            <tr>
                <td><label for="mid">Mid :</label></td>
                <td><?php echo $resTxnObj->mid; ?></td>
            </tr>
            <tr>
                <td><label for="orderNo">Order No :</label></td>
                <td><?php echo $resTxnObj->orderNo; ?></td>
            </tr>
            <tr>
                <td><label for="txnRefNo">Transaction Ref No :</label></td>
                <td><?php echo $resTxnObj->txnRefNo; ?></td>
            </tr>
            <tr>
                <td><label for="amount">Total Amount :</label></td>
                <td><?php echo $resTxnObj->amount; ?></td>
            </tr>
            <tr>
                <td><label for="currency">Currency Name :</label></td>
                <td><?php echo $resTxnObj->currency; ?></td>
            </tr>
            <tr>
                <td><label for="txnReqType">Transaction Req. Type :</label></td>
                <td><?php echo $resTxnObj->txnReqType; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField1">UndefinedField 1 :</label></td>
                <td><?php echo $resTxnObj->undefinedField1; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField2">UndefinedField 2 :</label></td>
                <td><?php echo $resTxnObj->undefinedField2; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField3">UndefinedField 3 :</label></td>
                <td><?php echo $resTxnObj->undefinedField3; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField4">UndefinedField 4 :</label></td>
                <td><?php echo $resTxnObj->undefinedField4; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField5">UndefinedField 5 :</label></td>
                <td><?php echo $resTxnObj->undefinedField5; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField6">UndefinedField 6 :</label></td>
                <td><?php echo $resTxnObj->undefinedField6; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField7">UndefinedField 7 :</label></td>
                <td><?php echo $resTxnObj->undefinedField7; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField8">UndefinedField 8 :</label></td>
                <td><?php echo $resTxnObj->undefinedField8; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField9">UndefinedField 9 :</label></td>
                <td><?php echo $resTxnObj->undefinedField9; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField10">UndefinedField 10 :</label></td>
                <td><?php echo $resTxnObj->undefinedField10; ?></td>
            </tr>
            <tr>
                <td><label for="emailId">Email Id :</label></td>
                <td><?php echo $resTxnObj->emailId; ?></td>
            </tr>
            <tr>
                <td><label for="mobileNo">Mobile No. :</label></td>
                <td><?php echo $resTxnObj->mobileNo; ?></td>
            </tr>
            <tr>
                <td><label for="address">Address :</label></td>
                <td><?php echo $resTxnObj->address; ?></td>
            </tr>
            <tr>
                <td><label for="city">City :</label></td>
                <td><?php echo $resTxnObj->city; ?></td>
            </tr>
            <tr>
                <td><label for="state">State :</label></td>
                <td><?php echo $resTxnObj->state; ?></td>
            </tr>
            <tr>
                <td><label for="pincode">Pincode :</label></td>
                <td><?php echo $resTxnObj->pincode; ?></td>
            </tr>
            <tr>
                <td><label for="respCode">Response Code :</label></td>
                <td><?php echo $resTxnObj->respCode; ?></td>
            </tr>
            <tr>
                <td><label for="respMessage">Response Message :</label></td>
                <td><?php echo $resTxnObj->respMessage; ?></td>
            </tr>
            <tr>
                <td><label for="payAmount">Pay Amount :</label></td>
                <td><?php echo $resTxnObj->payAmount; ?></td>
            </tr>
            <tr>
                <td><label for="txnRespDate">Transaction Response Date :</label></td>
                <td><?php echo $resTxnObj->txnRespDate; ?></td>
            </tr>
            <tr>
                <td><label for="upiString">UPI String :</label></td>
                <td id="upiString"><?php echo $resTxnObj->upiString; ?></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center;padding:5px"><input type="submit" value="Pay Now" id="paynow" style="padding:6px;width:100%;background:#044e78;color:white;cursor:pointer" /></td>
            </tr>
        </table>
    </center>

    <script>
        let upiStr = document.getElementById('upiString').textContent;
        document.getElementById('paynow').onclick  = function(){
            if(upiStr !== 'null'){
                location.href = upiStr;
            }
        }
    </script>
    <!-- Table Response End -->

    <?php 
}

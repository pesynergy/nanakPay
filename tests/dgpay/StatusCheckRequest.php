<?php

require 'DigyCartConfig.php';
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
        if (isset($_POST['txnRefNo']) && !empty($_POST['txnRefNo'])) {
            $Digycart->setTxnRefNo($_POST['txnRefNo']);
        }else {
            $error = $error . 'OrderNo / TxnRefNo. Field is required.<br/>';
        }
    }

    $Digycart->setAmount($_POST['amount']);

    if (empty($error)) {
        $resStatus = $Digycart->getTrnStatus();
        statusRespHandler(json_decode($resStatus));
    } else {
        echo $error;
    }

} else {
    echo 'oops,some error!';
}

function statusRespHandler($jsonData){
    
    $mid = "500000000000009"; // Provided by Digycart
    $secretKey = "scrnXvLdymelqVBMQ9YzyGtB6HWCsBGVlRO"; // Provided by Digycart
    $saltKey = "saltvsByWvDSoY8c8u1yZcEE3DUNmNhfs"; // Provided by Digycart

    $Digycart = new DigyCart;

    $Digycart->setMid($mid);
    $Digycart->setSecretKey($secretKey);
    $Digycart->setSaltKey($saltKey);

    $data = json_decode($jsonData->data);
    $respData = $data->respData;
    $checkSum = $data->checkSum;
    $mid = $data->mid;

    $response = $Digycart->parseStatusResp($respData,$checkSum,$mid);
    printResponse($response);    
}

function printResponse($res){
    $resStatusObj = json_decode($res);
    ?>
    <!-- Response Start -->
    <center> <H3> Transaction Status </H3>
        <table border="1">
            <tr>
                <td><label for="mid">Mid :</label></td>
                <td><?php echo $resStatusObj->mid; ?></td>
            </tr>
            <tr>
                <td><label for="orderNo">Order No :</label></td>
                <td><?php echo $resStatusObj->orderNo; ?></td>
            </tr>
            <tr>
                <td><label for="txnRefNo">Transaction Ref No :</label></td>
                <td><?php echo $resStatusObj->txnRefNo; ?></td>
            </tr>
            <tr>
                <td><label for="amount">Total Amount :</label></td>
                <td><?php echo $resStatusObj->amount; ?></td>
            </tr>
            <tr>
                <td><label for="currency">Currency Name :</label></td>
                <td><?php echo $resStatusObj->currency; ?></td>
            </tr>
            <tr>
                <td><label for="txnReqType">Transaction Req. Type :</label></td>
                <td><?php echo $resStatusObj->txnReqType; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField1">UndefinedField 1 :</label></td>
                <td><?php echo $resStatusObj->undefinedField1; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField2">UndefinedField 2 :</label></td>
                <td><?php echo $resStatusObj->undefinedField2; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField3">UndefinedField 3 :</label></td>
                <td><?php echo $resStatusObj->undefinedField3; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField4">UndefinedField 4 :</label></td>
                <td><?php echo $resStatusObj->undefinedField4; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField5">UndefinedField 5 :</label></td>
                <td><?php echo $resStatusObj->undefinedField5; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField6">UndefinedField 6 :</label></td>
                <td><?php echo $resStatusObj->undefinedField6; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField7">UndefinedField 7 :</label></td>
                <td><?php echo $resStatusObj->undefinedField7; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField8">UndefinedField 8 :</label></td>
                <td><?php echo $resStatusObj->undefinedField8; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField9">UndefinedField 9 :</label></td>
                <td><?php echo $resStatusObj->undefinedField9; ?></td>
            </tr>
            <tr>
                <td><label for="undefinedField10">UndefinedField 10 :</label></td>
                <td><?php echo $resStatusObj->undefinedField10; ?></td>
            </tr>
            <tr>
                <td><label for="emailId">Email Id :</label></td>
                <td><?php echo $resStatusObj->emailId; ?></td>
            </tr>
            <tr>
                <td><label for="mobileNo">Mobile No :</label></td>
                <td><?php echo $resStatusObj->mobileNo; ?></td>
            </tr>
            <tr>
                <td><label for="address">Address :</label></td>
                <td><?php echo $resStatusObj->address; ?></td>
            </tr>
            <tr>
                <td><label for="city">City :</label></td>
                <td><?php echo $resStatusObj->city; ?></td>
            </tr>
            <tr>
                <td><label for="state">State :</label></td>
                <td><?php echo $resStatusObj->state; ?></td>
            </tr>
            <tr>
                <td><label for="pincode">Pincode :</label></td>
                <td><?php echo $resStatusObj->pincode; ?></td>
            </tr>
            <tr>
                <td><label for="respCode">Response Code :</label></td>
                <td><?php echo $resStatusObj->respCode; ?></td>
            </tr>
            <tr>
                <td><label for="respMessage">RespMessage :</label></td>
                <td><?php echo $resStatusObj->respMessage; ?></td>
            </tr>
            <tr>
                <td><label for="payAmount">Pay Amount :</label></td>
                <td><?php echo $resStatusObj->payAmount; ?></td>
            </tr>
            <tr>
                <td><label for="txnRespDate">Transaction Response Date :</label></td>
                <td><?php echo $resStatusObj->txnRespDate; ?></td>
            </tr>
            <tr>
                <td><label for="upiString">UPI String :</label></td>
                <td><?php echo $resStatusObj->upiString; ?></td>
            </tr>        
        </table>
    </center>
    <?php
}

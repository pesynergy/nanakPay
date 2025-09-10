<center> <H3> Transaction Request </H3>

    <form name="payment_form" id="payment_form" method="post" action="TrnPayRequest.php">
    
        <table border="1">
            <tr>
                <td><label for="mid"> Merchant ID </label></td>
                <td><input type="text" name="mid" value="500000000000020" /></td>
            </tr>
            <tr>
                <td><label for="secretKey"> Secret Key </label></td>
                <td><input type="text" name="secretKey" value="scrhi1AB0QBCoaBi0E3J0WGRBfrNKLWfZ9i" /></td>
            </tr>
            <tr>
                <td><label for="saltKey"> Salt Key </label></td>
                <td><input type="text" name="saltKey" value="salilQSdyu2FDjN0oV08YxcOsn8mmXqmy" /></td>
            </tr>
            <tr>
                <td><label for="orderNo"> Order No. </label></td>
                <td><input type="text" name="orderNo" value="Nanak<?php echo rand(11111111, 99999999) ?>" /></td>
            </tr>
            <tr>
                <td><label for="amount"> Total Amount </label></td>
                <td><input type="text" name="amount" value="15" /></td>
            </tr>
            <tr>
                <td><label for="currency"> Currency Name</label></td>
                <td> <input type="text" name="currency" value="INR" /></td>
            </tr>
            <tr>
                <td><label for="txnReqType"> TxnReqType</label></td>
                <td><input type="text" name="txnReqType" value="S" /></td>
            </tr>
                
                <input type="hidden" name="undefinedField1" value="" /> 
                <input type="hidden" name="undefinedField2" value="" />
                <input type="hidden" name="undefinedField3" value="" />
                <input type="hidden" name="undefinedField4" value="" />
                <input type="hidden" name="undefinedField5" value="" />
                <input type="hidden" name="undefinedField6" value="" />
                <input type="hidden" name="undefinedField7" value="" />
                <input type="hidden" name="undefinedField8" value="" />
                <input type="hidden" name="undefinedField9" value="" />
                <input type="hidden" name="undefinedField10" value="" />

            <tr>
                <td><label for="emailId"> Email Id.</label></td>
                <td><input type="text" name="emailId" value="test@gmail.com" /></td>
            </tr>
            <tr>
                <td><label for="mobileNo"> Mobile No. </label></td>
                <td><input type="text" name="mobileNo" value="9324086501" /></td>
            </tr>

                <input type="hidden" name="address" value="" /> 
                <input type="hidden" name="city" value="" />
                <input type="hidden" name="state" value="" />
                <input type="hidden" name="pincode" value="" />

            <tr>
                <td><label for="transactionMethod"> Transaction Method</label></td>
                <td> <input type="text" name="transactionMethod" value="UPI" /></td>
            </tr>

                <input type="hidden" name="bankCode" value="" />
                <input type="hidden" name="vpa" value="" />
                <input type="hidden" name="cardNumber" value="" /> 
                <input type="hidden" name="expiryDate" value="" />
                <input type="hidden" name="cvv" value="" />
            
            <tr>
                <td><label for="customerName"> Customer Name </label></td>
                <td><input type="text" name="customerName" value="Arjun" /></td>
            </tr>

                <input type="hidden" name="respUrl" value="" />

            <tr>
                <td><label for="optional1"> UPI Type </label></td>
                <td><input type="text" name="optional1" value="intent" /></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center;padding:5px"><input type="submit" value="SUBMIT" style="padding:6px;width:100%;background:#044e78;color:white" /></td>
            </tr>

        </table>
    </form
</center>

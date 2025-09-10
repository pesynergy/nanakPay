<center> <H3> Transaction Request </H3>

    <form name="payment_form" id="payment_form" method="post" action="StatusCheckRequest.php">
    
        <table border="1">
            <tr>
                <td><label for="mid"> Merchant ID </label></td>
                <td><input type="text" name="mid" value="500000000000009" /></td>
            </tr>
            <tr>
                <td><label for="secretKey"> Secret Key </label></td>
                <td><input type="text" name="secretKey" value="scrnXvLdymelqVBMQ9YzyGtB6HWCsBGVlRO" /></td>
            </tr>
            <tr>
                <td><label for="saltKey"> Salt Key </label></td>
                <td><input type="text" name="saltKey" value="saltvsByWvDSoY8c8u1yZcEE3DUNmNhfs" /></td>
            </tr>
            <tr>
                <td><label for="orderNo"> Order No. </label></td>
                <td><input type="text" name="orderNo" value="ORD65098902" /></td>
            </tr>
            <tr>
                <td><label for="txnRefNo">Transaction Ref No. </label></td>
                <td><input type="text" name="txnRefNo" value="" /></td>
            </tr>

                <input type="hidden" name="amount" value="" />

            <tr>
                <td colspan="2" style="text-align:center;padding:5px"><input type="submit" value="Check Status" id="paynow" style="padding:6px;width:100%;background:#044e78;color:white;cursor:pointer" /></td>
            </tr>
        </table>
    </form>
</center>

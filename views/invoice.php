<?php

// Define DEBUG Constant
define('DEBUG', false);

// Turn on error reporting if DEBUG is true
if(DEBUG){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// include the controller file
include_once '../controllers/invoice.class.php';

// Create object of controller file
$objScr = new InvoiceController();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create invoice Application</title>
</head>
<body>
    <div class="container">
        <div class="flash-message"><?php
            if(isset($_REQUEST['flgMsg'])){
                switch($_REQUEST['flgMsg']){
                    case "ADS":
                            ?><span style="color:green">Record Added Successfully</span><?php
                            break;
                    case "ADF":
                        ?><span style="color:red">Record Not Added</span><?php
                        break;
                    case "UPS":
                        ?><span style="color:green">Record Updated Successfully</span><?php
                        break;
                    case "UPF":
                        ?><span style="color:red">Record Not Updated Successfully</span><?php
                        break;
                    case "DES":
                        ?><span style="color:green">Record Deleted Successfully</span><?php
                        break;
                    case "DEF":
                        ?><span style="color:red">Record Not Deleted Successfully</span><?php
                        break;
                }
            }
        ?></div>
        <div class="content-wrapper"><?php

            //Add link
            ?><div><?php if($objScr->lineItemId){?><a href="./invoice.php" title="Add">Add</a><?php } ?></div><?php

            switch($objScr->doAction){
                case "":
                        // Fetch the line item list to render in the page
                        $arrLineItem = $objScr->modGetLineItem();

                        // Array for Tax dropdown
                        $arrayTaxDropDown = array();
                        $arrayTaxDropDown[0] = '0';
                        $arrayTaxDropDown[1] = '1';
                        $arrayTaxDropDown[2] = '5';
                        $arrayTaxDropDown[3] = '10';

                        // javaScript Functions
                        ?><script type="text/javascript">
                            /*
                                function to apply discount on total amount
                            */
                            function jsApplyDiscount(){
                                
                                var total = document.getElementById('hdnTotal').value;
                                var discType = document.getElementById('selDiscountType').value;
                                var discount = document.getElementById('txtDiscount').value;
                                var  newTotal = total;

                                if(discType == 'F'){
                                    newTotal = total - discount;
                                }
                                else{
                                    if(discount){
                                        percentage = (total / 100) * discount;
                                    }
                                    newTotal = total - percentage;
                                }

                                document.getElementById('txtTotal').value = newTotal;
                            }


                            /*
                                function to create generateInvoice
                            */
                           function jsCreateInvoice(){
                               document.getElementById('frmInvoice').submit();
                           }
                        </script><?php
                        // Line item Form 
                        ?><form name="frmLineItem" method="GET" action="./invoice.php" onSubmit="">
                                <table>
                                    <tr>
                                        <td>Name</td>
                                        <td><input type="text" name="txtItemName" id="txtItemName" required value="<?php print($arrLineItem['name']); ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Quantity</td>
                                        <td><input type="number" name="txtQuantity" id="txtQuantity" required value="<?php print($arrLineItem['quantity']); ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Unit Price</td>
                                        <td><input type="number" name="txtUnitPrice" id="txtUnitPrice" required value="<?php print($arrLineItem['price']); ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Tax</td>
                                        <td>
                                            <select name="selTax" id="selTax"><?php
                                                foreach($arrayTaxDropDown as $taxVal){
                                                    ?><option <?php if($taxVal == $arrLineItem['tax']){print("selected");}?> value="<?php print($taxVal);?>"><?php print($taxVal . '%'); ?></option><?php
                                                }
                                            ?></select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><input type="hidden" name="doAction" value="<?php if($objScr->lineItemId ){print('Update');}else{print('Add');} ?>"/>
                                        <input type="hidden" name="lineItemId" value="<?php print($objScr->lineItemId );?>"/>
                                        <input type="submit" value="<?php if($objScr->lineItemId){print('Update');}else{print('Add');} ?>"/></td>
                                    </tr>
                                </table>
                            </form>
                        </div><?php

                        // Fetch the line item list to render in the page
                        $rsltCm = $objScr->objInvoice->getAllItems();
                        if(mysqli_num_rows($rsltCm)){
                            $total = $objScr->objInvoice->getTotalWithOutTax();
                            ?><table border="1">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Tax</th>
                                    <th>Sub Total</th>
                                    <th>Sub total(Tax)</th>
                                </tr>
                            </thead>
                            <tbody><?php
                                while($rowGetInfo = $rsltCm->fetch_assoc()){
                                    print('<tr>');
                                    print('<td>' . $rowGetInfo['name'] . '</td>');
                                    print('<td>' . $rowGetInfo['quantity'] . '</td>');
                                    print('<td>' . $rowGetInfo['price'] . '</td>');
                                    print('<td>' . $rowGetInfo['tax'] . '</td>');
                                    print('<td>' . $rowGetInfo['total'] . '</td>');
                                    print('<td>' . $rowGetInfo['totalWithTax'] . '</td>');
                                    print('<td><a href="invoice.php?lineItemId=' . $rowGetInfo['recId'] . '" title="Edit">Edit</a></td>');
                                    print('<td><a href="invoice.php?doAction=Delete&recId= ' . $rowGetInfo['recId'] . '" title="Edit">Delete</a></td>');
                                }
                            ?></tbody>
                        </table>
                        <table>
                            <tr>
                                <td>Discount</td>
                                <td>
                                    <input type="number" name="txtDiscount" id="txtDiscount" required value="0">
                                    <select name="selDiscountType" id="selDiscountType">
                                        <option value="P">%</option>
                                        <option value="F">fixed</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Total
                                </td>
                                <td>
                                <form method="get" name="frmInvoice" id="frmInvoice" action="invoice.php">
                                    <input type="number" name="txtTotal" id="txtTotal" readonly value="<?php print($total); ?>">
                                    <input type="hidden" name="hdnTotal" id="hdnTotal"  value="<?php print($total); ?>">
                                    <input type="hidden" name="doAction" id="doAction"  value="CreateInvoice">
                                </form>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="1"><button onclick="jsApplyDiscount()">Apply Discount</button>
                                <td colspan="1"><button title="Create invoice" onClick="jsCreateInvoice()">Generate Invoice</button>
                            </tr>
                        </table>
                        <div><?php
                        }
                        else{
                            print('No Line Items Added. Add Line Item');
                        }                               
                        ?></div><?php
                    break;

                    case "CreateInvoice":
                        // Fetch the line item list to render in the page
                        $rsltCm = $objScr->objInvoice->getAllItems();
                        ?> 
                        <script type="text/javascript">
                            window.print();
                        </script>
                        <table border="1" width="100%">
                            <caption><?php print("Total = " . $_REQUEST['txtTotal'])?></caption>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Tax</th>
                                <th>Sub Total</th>
                                <th>Sub total(Tax)</th>
                            </tr>
                        </thead>
                        <tbody><?php
                        while($rowGetInfo = $rsltCm->fetch_assoc()){
                            print('<tr>');
                            print('<td>' . $rowGetInfo['name'] . '</td>');
                            print('<td>' . $rowGetInfo['quantity'] . '</td>');
                            print('<td>' . $rowGetInfo['price'] . '</td>');
                            print('<td>' . $rowGetInfo['tax'] . '</td>');
                            print('<td>' . $rowGetInfo['total'] . '</td>');
                            print('<td>' . $rowGetInfo['totalWithTax'] . '</td>');
                        }
                        ?></tbody>
                        </table><?php
                    break;
            }
        ?></div>
    </div>
</body>
</html>
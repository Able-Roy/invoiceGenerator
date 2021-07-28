<?php

class INVOICE{
    function __construct(){
        $this->objDBConn = new DBFUNCTIONS();
    }

    // Function to fetch all items in an invoice
    function getAllItems($invoiceid = null){
        if(DEBUG){
            print("getAllItems($invoiceid)");
        }
        $qrySel = "SELECT i.recid recId, i.name name, i.price price, i.tax tax, i.quantity quantity, i.total total, i.totalwithtx totalWithTax
            FROM lineitems i ";
        
        if(isset($invoiceid) || is_numeric($invoiceid)){
            $qryWhere = "WHERE i.recid = " . addslashes($invoiceid) ."
            AND i.endeffdt is NULL";
        }
        else{
            $qryWhere = "WHERE i.endeffdt is NULL";
        }

        //Combine Queries
        $qrySel = $qrySel . $qryWhere;

        if(DEBUG){
            print(nl2br($qrySel));
        }
        $result = $this->objDBConn->execQuery($qrySel);

        if(!$result){
            print("Unable to fetch from lineitems table");
        }

        return $result;
    }

    // Function to add item in items table
    function addItem($arrPaarams){
        
        if(DEBUG){
            print('addItem(arrPaarams)<br/>');
            print_r($arrPaarams);
        }
        //Validating fields in request
        if( !isset($arrPaarams['txtItemName']) || empty($arrPaarams['txtItemName']) 
        || !isset($arrPaarams['txtQuantity']) || empty($arrPaarams['txtQuantity']) 
        || !isset($arrPaarams['txtUnitPrice']) || empty($arrPaarams['txtUnitPrice'])){
            return false;
        }

        //Server side validation for quantity
        if (!is_numeric($arrPaarams['txtQuantity'])) {
            return false;
        } 
        
        //Server side validation for price
        if (!is_numeric($arrPaarams['txtUnitPrice'])) {
            return false;
        } 
        
        //Server side validation for tax
        if (!is_numeric($arrPaarams['selTax'])) {
            return false;
        } 

        //calculate tax
        $taxAmount = $arrPaarams['txtUnitPrice'] * ($arrPaarams['selTax']/100);

        // Sub total calculation
        $singleItemPrice = (float) $arrPaarams['txtUnitPrice'];
        $total = $singleItemPrice * $arrPaarams['txtQuantity'];

        // Subtotal wit tax
        $singleItemPriceWithTax = (float) $arrPaarams['txtUnitPrice'] + $taxAmount;
        $totalWithTax = $singleItemPriceWithTax * $arrPaarams['txtQuantity'];

        $qryIns = "INSERT INTO `lineitems` (`name`, `price`, `tax`, `quantity`, `total`, `totalwithtx`)
            VALUES ( '" . addslashes($arrPaarams['txtItemName']) . "',
            '" . addslashes($arrPaarams['txtUnitPrice']) . "',
            '" . addslashes($arrPaarams['selTax']) . "',
            '" . addslashes($arrPaarams['txtQuantity']) . "',
            '" . addslashes($total) . "',
            '" . addslashes($totalWithTax) . "')";
        
        if(DEBUG){
            print(nl2br($qryIns));
        }
        $result = $this->objDBConn->execQuery($qryIns);

        if($result){
            return true;
        }
        else{
            
            if(DEBUG){
                print('<br />');
                print('Unable to insert to lineitems table');
            }
            return false;
        }
    }

    // Update items details
    function updateItem($arrPaarams){

        if(DEBUG){
            print('updateItem() <br/>');
            print_r($arrPaarams);
        }
        if(!isset($arrPaarams['recId']) || !is_numeric($arrPaarams['recId'])){
            return false;
        }

        //Validating fields in request
        if( !isset($arrPaarams['txtItemName']) || empty($arrPaarams['txtItemName']) 
        || !isset($arrPaarams['txtQuantity']) || empty($arrPaarams['txtQuantity']) 
        || !isset($arrPaarams['txtUnitPrice']) || empty($arrPaarams['txtUnitPrice'])){
            return false;
        }
        //Server side validation for quantity
        if (!is_numeric($arrPaarams['txtQuantity'])) {
            return false;
        } 
        
        //Server side validation for price
        if (!is_numeric($arrPaarams['txtUnitPrice'])) {
            return false;
        } 
        
        //Server side validation for tax
        if (!is_numeric($arrPaarams['selTax'])) {
            return false;
        } 

        //calculate tax
        $taxAmount = $arrPaarams['txtUnitPrice'] * ($arrPaarams['selTax']/100);

        // Sub total calculation
        $singleItemPrice = (float) $arrPaarams['txtUnitPrice'];
        $total = $singleItemPrice * $arrPaarams['txtQuantity'];

        // Subtotal wit tax
        $singleItemPriceWithTax = (float) $arrPaarams['txtUnitPrice'] + $taxAmount;
        $totalWithTax = $singleItemPriceWithTax * $arrPaarams['txtQuantity'];

        $qryUpd = "UPDATE lineitems i SET
            i.name = '". addslashes($arrPaarams['txtItemName']) . "',
            i.price = '". addslashes($arrPaarams['txtUnitPrice']) . "',
            i.tax = '". addslashes($arrPaarams['selTax']) . "',
            i.quantity = '". addslashes($arrPaarams['txtQuantity']) . "',
            i.total = ". $total .",
            i.totalwithtx = ". $totalWithTax ."
            WHERE i.recid=" . addslashes($arrPaarams['recId']);

        if(DEBUG){
            print(nl2br($qryUpd));
        }
        $result = $this->objDBConn->execQuery($qryUpd);

        if($result){
            return true;
        }
        else{
            return false;
        }

    }

    // Function to fetch total with tax
    function getTotalWithOutTax(){

        $qrySel = "SELECT SUM(price * quantity) total
            FROM lineitems i 
            WHERE i.endeffdt is NULL";

        $result = $this->objDBConn->execQuery($qrySel);
        if($result){
            $row = $result->fetch_assoc(); 
            return $row['total'];
        }
        else{
            return 0;
        }
    }

    //Function to delete item
    function deleteItem($recid){
        
        if(!isset($recid) || !is_numeric($recid)){
            return false;
        } 
        
        $qryUpd = "UPDATE lineitems i SET
        i.endeffdt = NOW()
        WHERE i.recid=" . addslashes($recid);
        $result = $this->objDBConn->execQuery($qryUpd);

        if($result){
            return true;
        }
        else{
            return false;

            if(DEBUG){
                print('unable to delete lineitems table');
            }
        }
    }

    // Function to fetch all items in an invoice
    function getSingleItemInfo($invoiceId){

        //Initialize return aray
        $arrReslt = array();
        $arrReslt['invoiceid'] = ''; 
        $arrReslt['name'] = '';
        $arrReslt['price'] = ''; 
        $arrReslt['tax'] = '';
        $arrReslt['quantity'] = '';
        $arrReslt['total'] = '';

        if(!isset($invoiceId) || !is_numeric($invoiceId)){
            return  $arrReslt;
        } 

        if(!isset($_SESSION['INVOICEID'])){
            return false;
        }

        $qrySel = "SELECT `recid`, `invoiceid`, `name`, `price`, `tax`, `quantity`, `total` 
            FROM items i 
            WHERE i.invoiceid = " . $_SESSION['INVOICEID'] ."
            AND i.recid = " . $invoiceId . "
            AND i.endeffdt is NULL";
        
        $result = $this->objDBConn->execQuery($qrySel);
        
        if($result){
            $row = $result->fetch_assoc(); 
            $arrReslt['invoiceid'] = $row['invoiceid']; 
            $arrReslt['name'] = $row['name']; 
            $arrReslt['price'] = $row['price']; 
            $arrReslt['tax'] = $row['tax']; 
            $arrReslt['quantity'] = $row['quantity']; 
            $arrReslt['total'] = $row['total']; 
        }
        else{
            $arrReslt['invoiceid'] = ''; 
            $arrReslt['name'] = '';
            $arrReslt['price'] = ''; 
            $arrReslt['tax'] = '';
            $arrReslt['quantity'] = '';
            $arrReslt['total'] = '';
        }

        return $arrReslt;
    }
}
?>
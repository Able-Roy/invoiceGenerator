<?php

// include The models
include "../models/invoice.class.php";
include "../models/dbfunction.class.php";

class InvoiceController{
    function __construct(){
        $this->objDBConn = new DBFUNCTIONS();
        $this->objInvoice = new INVOICE();
        
        // Setting doAction for diffrent cases
        $this->doAction = isset($_REQUEST['doAction'])? $_REQUEST['doAction'] : "";

        // Setting line-item-id 
        $this->lineItemId = isset($_REQUEST['lineItemId']) && is_numeric($_REQUEST['lineItemId'])? $_REQUEST['lineItemId'] : 0;

        $flagRedirect = false;

        // Doaction based Actions
        switch($this->doAction){
            case "": 
                break;
            case "Add":

                // Add item
                if($this->objInvoice->addItem($_REQUEST)){
                    $flagRedirect = true;
                    $flagMsg = "ADS";
                }
                else{
                    $flagRedirect = true;
                    $flagMsg = "ADF";
                }
            break;
            case "Update":

                $arrParams = $_REQUEST;
                $arrParams['recId'] = $this->lineItemId;
                
                // Add item
                if($this->objInvoice->updateItem($arrParams)){
                    $flagRedirect = true;
                    $flagMsg = "UPS";
                }
                else{
                    $flagRedirect = true;
                    $flagMsg = "UPF";
                }
            break;
            case "Delete":
                if($this->objInvoice->deleteItem($_REQUEST['recId'])){
                    $flagRedirect = true;
                    $flagMsg = "DES";
                }
                else{
                    $flagRedirect = true;
                    $flagMsg = "DEF";
                }
            break;

            case "CreateInvoice":
            break;
            default: 
                print('exited for doAction' . $this->doAction);
                exit;
        }

        if($flagRedirect){
            $link = "invoice.php?flgMsg=" . $flagMsg;
            header('location:' . $link);
        }
    }

    /*
        function to populate data to the form
    */
    function modGetLineItem(){
        
        if(DEBUG){
            print('modGetLineItem()<br />');
        }

        // Define Array for the line item
        $arrayLineItem = array();

        // For Edit case load data from Database
        if($this->lineItemId){
            // Data from databse
            $rsltCm = $this->objInvoice->getAllItems($this->lineItemId);

            if(mysqli_num_rows ($rsltCm)){
                // Load the data from curser to the associated array
                $rowGetInfo = $rsltCm->fetch_Assoc();

                if(DEBUG){
                    print('<br/>');
                    print('<pre>');
                    print_r($rowGetInfo);
                    print('</pre>');
                    print('<br />');
                }

                // Prepare The data for frontend
                $arrayLineItem['name'] = htmlentities($rowGetInfo['name']);
                $arrayLineItem['price'] = htmlentities($rowGetInfo['price']);
                $arrayLineItem['tax'] = htmlentities($rowGetInfo['tax']);
                $arrayLineItem['quantity'] = htmlentities($rowGetInfo['quantity']);
                $arrayLineItem['total'] = htmlentities($rowGetInfo['total']);
                $arrayLineItem['totalWithTax'] = htmlentities($rowGetInfo['totalWithTax']);
            }
            // No record find for the corresponding id
            else{
                // Prepare The data for frontend
                $arrayLineItem['name'] = '';
                $arrayLineItem['price'] = '';
                $arrayLineItem['tax'] = '';
                $arrayLineItem['quantity'] = '';
                $arrayLineItem['total'] = '';
                $arrayLineItem['totalWithTax'] = '';
            }
        }
        // For Add case load with empty data
        else{
            // Prepare The data for frontend
            $arrayLineItem['name'] = '';
            $arrayLineItem['price'] = '';
            $arrayLineItem['tax'] = '';
            $arrayLineItem['quantity'] = '';
            $arrayLineItem['total'] = '';
            $arrayLineItem['totalWithTax'] = '';
        }

        // Return Data to frontend
        return $arrayLineItem;
    }
}

?>
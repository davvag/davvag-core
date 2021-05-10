<?php

class CartService {

    function __construct(){
        
    } 

    public function postCheckout($req){
        require_once (PLUGIN_PATH . "/transactions/transactions.php");

        $profile = $req->Body(true);
        $location = json_decode($_COOKIE["Location"]);
        $profile->order = new stdClass();
        $profile->order->lat = $location->lat;
        $profile->order->lon = $location->lng;
        $profile->order->profileId = $profile->id;
        $profile->order->name = $profile->name;
        $profile->order->contactno = $profile->contactno;
        $profile->order->orderdate = date("m-d-Y H:i:s");
        $profile->order->details = array();
        $profile->order->deliverydate = $profile->deliverydate;
        $profile->order->paymenttype = $profile->paymenttype;
        $profile->order->status = "new";//$profile->orderstatus;
        $profile->order->remarks = isset($profile->remarks) ?$profile->remarks : "";
        $profile->order->total = 0;
        $profile->order->subtotal = 0;
        $profile->order->paidamount = 0;
        $profile->order->balance = 0;
        $profile->order->tax = 0;
        //$profile->order->tenant = $_SERVER["HTTP_HOST"];
        $profile->order->invoiceDate = date("m-d-Y H:i:s");
        $profile->order->invoiceDueDate = date("m-d-Y H:i:s");
        
        $authData = json_decode($_COOKIE["authData"]);
        $profile->order->email = $authData->email;

        for ($i=0;$i<sizeof($profile->items);$i++){
            $item = $profile->items[$i];
            
            $detail = new stdClass();
            $detail->itemid = $item->itemid;
            $detail->productid = $item->itemid;
            $detail->tid = $item->tid;
            $detail->tenant = $item->tenant;
            $profile->order->tenant =$item->tenant;
            $detail->name = $item->name;
            $detail->uom = $item->uom;
            $detail->qty = $item->qty;
            $detail->price = $item->price;
            $detail->total = $item->qty * $item->price;
            $profile->order->total += ($detail->total);
            $profile->order->subtotal += ($detail->total);
            array_push($profile->order->details, $detail);
        }
        unset ($profile->items);

        $composeStrategy = array("-@OBJ.address"=>"@BAG.address", "-@OBJ.order"=>"@BAG.orderheader", "-@BAG.orderheader.details"=>"@BAG.orderdetails" , "@OBJ" => "@BAG.profile");

        $defaultInventoryTemplate = new stdClass();
        $defaultInventoryTemplate->productid = "@ITEM.productid";
        $defaultInventoryTemplate->qty = 0;
        $defaultInventoryTemplate->status = "";
        $defaultInventoryTemplate->locationid = 0;

        $tranObj = TransactionManager::Create($profile);
        $tranObj->Compose->__invoke($composeStrategy)
                //->Insert->__invoke("address", "@BAG.address", "@BAG.addressResponse")
                //->Compose->__invoke(array("@BAG.addressResponse.generatedId"=>"@BAG.profile.addressid"))
                //->Insert->__invoke("profile", "@BAG.profile", "@BAG.profileResponse")
                //->Compose->__invoke(array("@BAG.profileResponse.generatedId"=>"@BAG.orderheader.profileid", "@BAG.profile.addressid" => "@BAG.orderheader.addressid"))
                ->Insert->__invoke("orderheader_pending", "@BAG.orderheader", "@BAG.orderRespone")
                ->Compose->__invoke(array("@BAG.orderRespone.generatedId"=>"@BAG.orderid"))
                //->Map->__invoke("@BAG.orderdetails", array("@BAG.orderid" => "@ITEM.orderid"))
                ->Map->__invoke("@BAG.orderdetails", array("@BAG.orderid" => "@ITEM.invoiceNo"))
                ->Insert->__invoke("orderdetails_pending", "@BAG.orderdetails")
                ->IterateAndUpdateCounter->__invoke("@BAG.orderdetails", "store_products->qty=#->qty/store_products->itemid=#->itemid", "-", $defaultInventoryTemplate);
        $result = $tranObj->Execute();
        
        // $this->sendEmail($result);

        return "Successfully checked out";
    }

    private function sendEmail($result){
        require_once(PLUGIN_PATH . "/phpmailer/PHPMailerAutoload.php");
        $bag = $result->processData->bag;
        $orderId = $bag->orderid;
        $details = $bag->orderdetails;
        $toEmail = $result->processData->object->email;

        $mail = new PHPMailer();
        
        $mail->IsSMTP(); // set mailer to use SMTP
        $mail->Host = "smtp.elasticemail.com"; // specify main and backup server
        $mail->SMTPAuth = true; // turn on SMTP authentication
        $mail->Port =2525;
        $mail->Username = "orders@mylunch.lk";
        $mail->Password = "ff06c777-490b-4ff3-8856-320cf3652c1f";
        $mail->SMTPSecure = 'tls';  

        $mail->From = "orders@mylunch.lk";
        $mail->FromName = "Mylunch.lk";
        $mail->Subject  = "Order placement Notification";
        $mail->IsHTML(true); 
        $mail->addAddress($toEmail, $toEmail);
        
        /*
        $mail->Host = "103.47.204.4"; // specify main and backup server

        $mail->setFrom('dilshadtheman@gmail.com', 'Mylunch.lk');
        $mail->IsHTML(true); 
        $mail->addAddress($toEmail, $toEmail);
        */
$body = <<<EOT
        <div style="width:500px;font-family:Georgia;overflow:auto;">
            <div style="background:#115FB2;">
                <img src="https://mylunch.lk/assets/mylunch/img/cart.png"/>
            </div>
            <div style="height:100px;clear: both;">
                <h2>Thank you for ordering your meal from Mylunch.lk. Your order will be dispatched shortly (Order Id : @@ORDERID@@)</h2>
                <h3>You have ordered the following items;</h3>
                <table border="1" style="width:100%;border-collapse:collapse">
                    <tr>
                        <th>Item Name</th>
                        <th>Unit Price</th>
                        <th>Qty</th>
                        <th>Total Price</th>
                    </tr>
                        @@DETAILS@@
                    <tr>
                        <td colspan="4">Total Amount : @@TOTAL@@</td>
                    </tr>
                </table>
            </div>

        </div>
EOT;
        $detailStr = "";
        $totalBill = 0;
        $univerasalUom;
        foreach ($details as $detail) {
            $uom = $detail->uom;
            $univerasalUom = $uom;
            $totalBill += ($detail->unitprice * $detail->qty);;
            $detailStr .="<tr><td>". $detail->productid ."</td><td>" .$uom . ". ". $detail->unitprice ."</td><td>".$detail->qty."</td><td>". $uom . ". " . ($detail->unitprice * $detail->qty )."</td></tr>";
        }


        $body = str_replace("@@ORDERID@@", $detailStr, $orderId);
        $body = str_replace("@@DETAILS@@", $detailStr, $body);
        $body = str_replace("@@TOTAL@@", $univerasalUom . ". " . (string)$totalBill, $body);

        $mail->Body = $body;
        
        $mail->Send();

    }

    public function getGrn($req){
        
    }
}

?>
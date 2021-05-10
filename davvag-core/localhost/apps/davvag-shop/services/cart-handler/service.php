<?php

class CartService {

    function __construct(){
        
    } 

    public function postCheckout($req){
        require_once (PLUGIN_PATH_LOCAL . "/davvag-order/davvag-order.php");
        $handler =new Davvag_Order();
        $profile = $req->Body(true);
        $profile->order = new stdClass();
        if(isset($_COOKIE["Location"])){
            $location = json_decode($_COOKIE["Location"]);
            $profile->order->lat = $location->lat;
            $profile->order->lon = $location->lng;
        }
        
        
        $profile->order->profileId = $profile->id;
        $profile->order->name = $profile->name;
        $profile->order->contactno = $profile->contactno;
        $profile->order->orderdate = date("m-d-Y H:i:s");
        $profile->order->InvoiceItems = array();
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
        
       //$authData = json_decode($_COOKIE["authData"]);
        $profile->order->email = $profile->email;

        for ($i=0;$i<sizeof($profile->items);$i++){
            $item = $profile->items[$i];
            
            $detail = new stdClass();
            $detail->itemid = $item->itemid;
            $detail->productid = $item->itemid;
            $detail->invType= $item->invType;
            $detail->tid = $item->tid;
            $detail->tenant = HOST_NAME;
            $profile->order->tenant = HOST_NAME;
            $detail->name = $item->name;
            $detail->uom = $item->uom;
            $detail->qty = $item->qty;
            $detail->price = $item->price;
            $detail->total = $item->qty * $item->price;
            $profile->order->total += ($detail->total);
            $profile->order->subtotal += ($detail->total);
            array_push($profile->order->InvoiceItems, $detail);
        }
        unset ($profile->items);
        try{
            $profile->order= $handler->InvoiceSave($profile->order);
            return $profile;
        }catch(Exception $e){
            $req->SetError($e);
        }
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
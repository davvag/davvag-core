<?php

class GrnService {

    function __construct(){
        
    } 

    public function postUpdateOrder($req){
        require_once (PLUGIN_PATH . "/transactions/transactions.php");
        $order = $req->Body(true);
        $tranObj = TransactionManager::Create($order);
        $tranObj->Update->__invoke("orderheader", "@OBJ");
        $result = $tranObj->Execute();

        $status = $order->status;
        if ($status === "closed"){
            $email = $order->profile->email;
            // $this->sendEmail($email);
        }

        return $result->processData->object;
    }

    private function sendEmail($toEmail){
        require_once(PLUGIN_PATH . "/phpmailer/PHPMailerAutoload.php");

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
        $mail->Subject  = "Order Completion Notification";
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
                <h2>Your order has been delivered. Thank you for using Mylunch.lk</h2>
            </div>

        </div>
EOT;


        $mail->Body = $body;       
        $mail->Send();
    }

    public function getAllOrders($req){
        require_once (PLUGIN_PATH . "/transactions/transactions.php");

        $tranObj = TransactionManager::Create();
        $tranObj->Get->__invoke("orderheader", null, "@OBJ")
                ->IterateAndJoin->__invoke("@OBJ", "_->_=#->details->/orderdetails->orderid=#->id", true)
                ->IterateAndJoin->__invoke("@OBJ", "_->_=#->profile->/profile->id=#->profileid")
                ->IterateAndJoin->__invoke("@OBJ", "_->_=#->address->/address->id=#->addressid");
        $result = $tranObj->Execute();

        return $result->processData->object;
    }

    public function getRiderOrders($req){
        require_once (PLUGIN_PATH . "/transactions/transactions.php");
        if (isset($_GET["riderid"])){
            $tranObj = TransactionManager::Create();
            $tranObj->Get->__invoke("orderheader", "ridername:" . $_GET["riderid"] . ",status:dispatched", "@OBJ")
                    ->IterateAndJoin->__invoke("@OBJ", "_->_=#->details->/orderdetails->orderid=#->id", true)
                    ->IterateAndJoin->__invoke("@OBJ", "_->_=#->profile->/profile->id=#->profileid")
                    ->IterateAndJoin->__invoke("@OBJ", "_->_=#->address->/address->id=#->addressid");
            $result = $tranObj->Execute();

            return $result->processData->object;
        }
    }
    
    
    public function getCancledOrders($req){
        return $this->getOrderByStatus($req,"cancled");
    }

    public function getPendingOrders($req){
        return $this->getOrderByStatus($req,"readytodispatch");
    }

    public function getNextDayOrders($req){
        return $this->getOrderByStatus($req,"nextdayorder");
    }

    public function getDispatchedOrders($req){
        return $this->getOrderByStatus($req,"dispatched");
    }

    public function getClosedOrders($req){
        return $this->getOrderByStatus($req,"closed");
    }

    private function getOrderByStatus($req, $status){
        require_once (PLUGIN_PATH . "/transactions/transactions.php");
        
        $tranObj = TransactionManager::Create();
        $tranObj->Get->__invoke("orderheader", "status:$status", "@OBJ")
                ->IterateAndJoin->__invoke("@OBJ", "_->_=#->details->/orderdetails->orderid=#->invoiceNo", true)
                ->IterateAndJoin->__invoke("@OBJ", "_->_=#->profile->/profile->id=#->profileid");
        $result = $tranObj->Execute();

        return $result->processData->object;
    }

    public function getAllInventory($req){
        require_once (PLUGIN_PATH . "/transactions/transactions.php");

        $tranObj = TransactionManager::Create();
        $tranObj->Get->__invoke("inventory", null, "@OBJ")
                ->IterateAndJoin->__invoke("@OBJ", "_->_=#->productInfo->/products->itemid=#->productid");
        $result = $tranObj->Execute();

        return $result->processData->object;
    }

    public function postNewGrn($req){
        require_once (PLUGIN_PATH . "/transactions/transactions.php");

        $grn = $req->Body(true); 
        $composeStrategy = array("-@OBJ.items"=>"@BAG.items", "@OBJ" => "@BAG.grn");
        $mapArray = array("@BAG.grn.id" => "@ITEM.grnid");

        $defaultInventoryTemplate = new stdClass();
        $defaultInventoryTemplate->productid = "@ITEM.productid";
        $defaultInventoryTemplate->qty = 0;
        $defaultInventoryTemplate->status = "";
        $defaultInventoryTemplate->locationid = 0;

        $tranObj = TransactionManager::Create($grn);
        $tranObj->Compose->__invoke($composeStrategy)
                ->Map->__invoke("@BAG.items", $mapArray)
                ->Insert->__invoke("grnheader", "@BAG.grn")
                ->Insert->__invoke("grnitems", "@BAG.items")
                ->IterateAndUpdateCounter->__invoke("@BAG.items", "inventory->qty=#->qty/inventory->productid=#->productid", "+", $defaultInventoryTemplate);
        $result = $tranObj->Execute();


        return "Successfully created GRN";
    }

    public function getGrn($req){
        
    }
}

?>
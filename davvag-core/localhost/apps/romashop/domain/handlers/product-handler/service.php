<?php
require_once(PLUGIN_PATH . "/auth/auth.php");
class ProductService {
    public function postProductToStore($req){
        //var_dump($req);
        $Transaction=$req->Body(true);
        $tname=$req->headers()->rhost;
        $sosskey=$req->headers()->sosskey;
        
        $user= Auth::AutendicateDomain($tname,$sosskey,"store_products","ProductToStore");
        if(isset($user->userid)){
            require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
            $Transaction->tenant=$tname;
            $Transaction->publishedByID=$user->userid;
            $Transaction->publishedBy=$user->email;
            $result = SOSSData::Query ("store_products", urlencode("tid:".$Transaction->tid.""));
            if(count($result->result)!=0){
                $Transaction->itemid=$result->result[0]->itemid;
                $result=SOSSData::Update ("store_products", $Transaction,$tenantId = null);
                if($result->success){
                    return $Transaction;
                }else{
                    return null;
                }
            }else{
                $result = SOSSData::Insert ("store_products", $Transaction,$tenantId = null);
                if($result->success){
                    $Transaction->itemid=$result->result->generatedId;
                    return $Transaction;
                }else{
                    return null;
                }
            }
        }else{
            return null;
        }
    }

    public function getAllProducts($req){
        if (isset($_GET["lat"]) && isset($_GET["lng"])){
            require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
            $mainObj = new stdClass();
            $mainObj->parameters = new stdClass();
            $mainObj->parameters->lat = $_GET["lat"];
            $mainObj->parameters->lng = $_GET["lng"];
            $mainObj->parameters->catid = isset($_GET["catid"]) ?  $_GET["catid"] : "";
            $resultObj = SOSSData::ExecuteRaw("nearproducts", $mainObj);
            for ($i=0;$i<sizeof($resultObj->result);$i++){
                $obj = $resultObj->result[$i];
                $obj->inventory = new stdClass();
                $obj->inventory->productid=1;
                $obj->inventory->locationid=1;
                $obj->inventory->qty=1;
                $obj->inventory->status="";
            }
            header("Content-type: application/json");
            $outObj = new stdClass();
            $outObj->success = true;
            $outObj->result = $resultObj->result;
            echo json_encode($outObj);
            exit();
            return $resultObj->result;
        } else {
            require_once (PLUGIN_PATH . "/transactions/transactions.php");

            $query = isset($_GET["catid"]) ? "catogory:$_GET[catid]" : null;
            $tranObj = TransactionManager::Create();
            $tranObj->Get->__invoke("products", $query, "@OBJ")
                    ->IterateAndJoin->__invoke("@OBJ", "_->_=#->inventory->/inventory->productid=#->itemid");
            $result = $tranObj->Execute();
    
            $objs = $result->processData->object;
    
            return $objs;
        }
    }
}

?>
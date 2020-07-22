<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");

class ProductServices {

    public function getAllProducts($req){
        if (isset($_GET["page"]) && isset($_GET["size"])){
            require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
            $mainObj = new stdClass();
            $mainObj->parameters = new stdClass();
            $mainObj->parameters->page = $_GET["page"];
            $mainObj->parameters->size = $_GET["size"];
            $mainObj->parameters->search = isset($_GET["q"]) ?  $_GET["q"] : "";
            $mainObj->parameters->rad = '0';
            $mainObj->parameters->lon = '0';
            $mainObj->parameters->lan = '0';
            $mainObj->parameters->cat = isset($_GET["cat"])?$_GET["cat"]:'0';

            $resultObj = SOSSData::ExecuteRaw("profiles_search", $mainObj);
            return $resultObj->result;
        } else {
            
            $mainObj = new stdClass();
            $mainObj->error="Invalied Query";
            return $mainObj;
        }
    }

    public function postPaymentRequest($req,$res){
        $data = $req->Body(true);
        $paymentProfile=null;
        $r = SOSSData::Query ("profile", urlencode("id:".$data->profileid.""));
        if(count($r->result)==0){
            $res->SetError("Profile not file to pay");
            return null;
        }else{
            $paymentProfile=$r->result[0];
            $data->profileId=$paymentProfile->id;
            $data->studentname=$paymentProfile->name;
            $r = SOSSData::Query ("profilestatus", urlencode("profileid:".$data->profileid.""));
            if(count($r->result)==0){
                $res->SetError("No Outstanding amount to pay.");
                return null;
            }else{
                $profileBalance=$r->result[0];
                if($profileBalance->outstanding<$data->amount){
                    $res->SetError("Payment is lager than the required donation.");
                    return null;
                }
                $data->outstandingAmount=$profileBalance->outstanding;
                $data->paymentAmount=$data->amount;
                $data->balanceAmount=$profileBalance->outstanding-$data->amount;
                //$profileBalance->totalPaidUnrealized=$profileBalance->totalPaidUnrealized==null?$data->amount:$profileBalance->totalPaidUnrealized+$data->amount;
                //$result=SOSSData::Update ("profilestatus", $profileBalance);
            }
        }

        $r = SOSSData::Query ("profile", urlencode("email:".$data->email.""));
        if(count($r->result)!=0){
            $data->donorId =$r->result[0]->id;
        }else{
            $result=SOSSData::Insert ("profile", $data);
                    if($result->success){
                        $data->donorId=$result->result->generatedId;
                    }
                    
        }
        //$result=SOSSData::Insert ("profile", $data);
        $result=SOSSData::Insert ("payment_ext_request", $data);
        if($result->success){
            $data->id=$result->result->generatedId;
            require_once(PLUGIN_PATH . "/notify/notify.php");
            return Notify::sendEmailMessage($data->name,$data->email,"com_qti_payment_request",$data);
        }
        
        return $data;

    }
}

?>
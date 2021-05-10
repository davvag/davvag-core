<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");


class appService {

    function __construct(){
        
    } 

    public function getDomain($req,$res){
        if(!isset($_GET["domain"])){
            $res->SetError("Domain Error.");
            return false;
        }
        $data =Auth::CrossDomainAPICall($_GET["domain"],"/components/davvag-app-manager/app-handler/service/UserGroups","GET");
        return $data;
        $r =SOSSData::Query("domain_registrar","domain:".$_GET["domain"]);
        if($r->success){
            if(count($r->result)>0){
                return $r->result;
            }else{
                $data =Auth::CrossDomainAPICall($_GET["domain"],"/components/davvag-app-manager/app-handler/service/UserGroups","GET");
                return $data;
            }
            $data = new stdClass();    
            $data->keytoken = sprintf('%04X%04X%04X%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
            $data->domain=$_GET["domain"];
            //$data->expdate=
            $result=SOSSData::Insert("domains_keytokens",$data);
            if($result->success){

                return true; 
            }else{
                $res->SetError("Error Registering a Token Please try again Later.");
                return false;
            }
        }
    }

    

    


}

?>
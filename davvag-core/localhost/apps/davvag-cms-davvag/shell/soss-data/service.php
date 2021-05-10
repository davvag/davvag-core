<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");

class SearchServices {

    public function postTransferInsertRequest($req){
        return $this->handleTransfer($req ,"insert");        
    }

    public function postTransferUpdateRequest($req){
        return $this->handleTransfer($req ,"udpate");
    }

    public function postTransferDeleteRequest($req){
        return $this->handleTransfer($req ,"delete");
        
    }

    private function handleTransfer($req, $operation){
        $postBody = $req->Body(true);
        $securityToken = $postBody->from->securityToken;

        $authFailData = $this->authorizeAllDomains($req, $postBody, $securityToken, $operation);
        $isAuthorized = sizeof($authFailData) == 0 ;

        $responseMessage = new stdClass();
        $responseMessage->success = false;
        
        if ($isAuthorized){
            $transferFailData = $this->transferDataToDomains($req, $postBody, $securityToken, $operation);
            $isTransfered = sizeof($transferFailData) == 0;

            if ($isTransfered){
                $responseMessage->success = true;
            }else {
                $responseMessage->errors = $transferFailData;
                $responseMessage->errorType = "TRANSFER_ERROR";
            }

        }else {
            $responseMessage->errors = $authFailData;
            $responseMessage->errorType = "AUTH_ERROR";
        }

        return $responseMessage;
    }

    private function authorizeAllDomains($req, $postBody, $securityToken, $operation){
        $authFailData  = array();
        
        foreach ($postBody->domains as $domain) {
            foreach ($postBody->dataStores as $dataStore) {
                $user=$this->authorizeDomain($domain,$securityToken,$dataStore->dataStore,$operation,$req);
                if ($user !== NULL){
                    array_push($authFailData, ["domain" => $domain, "operation" => $operation , "dataStore" => $dataStore->dataStore]);
                }
            }            
        }

        return $authFailData;
    }

    private function authorizeDomain($domain, $securityToken, $class, $operation, $req){

    }

    private function transferDataToDomains($req, $postBody, $securityToken, $operation){
        $transferReport = array();
        
        foreach ($postBody->domains as $domain) {
            $this->transferDataStores($req, $transferReport, $postBody->dataStores, $securityToken, $operation, $domain, NULL, NULL);    
        }
        
        return $transferReport;
    }

    private function transferDataStores($req, &$transferReport, $dataStores, $securityToken, $operation, $domain, $foreignKeyField, $parentKeyValue)
    {
        foreach ($dataStores as $dataStore) {
            foreach ($dataStore->objects as $dataObject){
                $result = $this->transferObject($dataStore->class, $dataObject, $operation, $domain, $foreignKeyField, $parentKeyValue);
                
                if ($result->success === true){
                    if (isset($dataStore->detailStores)){
                        $fkField = ($operation === "insert") ? $dataStore->fkField : NULL;
                        $pkField = ($fkField === NULL) ? NULL : $result->parentId;
                        
                        $this->transferDataStores($req, $transferReport, $dataStore->detailStores, $securityToken, $operation, $domain, $pkField, $fkField);
                    }
                }else {
                    array_push($transferReport, ["dataStoreClass"=>$dataStore->class, "operation"=>$operation, "errorMessage"=> $result->errorMessage, "object"=>$dataObject ]);
                }

            }
        }
    }

    private function transferObject($class, $object, $operation, $domain, $foreignKeyField, $parentKeyValue){
        $result = new stdClass();
        $result->success = true;

        if ($operation === "insert"){
            $osResult = SOSSData::Insert($class, $object, $domain);
            if ($osResult->isSuccess){
                $result->parentId = $osResult->result->generatedId;
            }else {
                $result->errorMessage = $osResult->error;
            }
        }else {
            $osResult = SOSSData::Update($class, $object, $domain);

            if (!$osResult->isSuccess){
                $result->errorMessage = $osResult->error;
            }
        }

        $result->success = $osResult->isSuccess;
        
        return $result;
    }

    public function postq($req){
        $sall=$req->Body(true);
        $f=new stdClass();
        foreach($sall as $s){
            $result= CacheData::getObjects(md5($s->search),$s->storename);
            if(!isset($result)){
                $result=null;
                if($s->search!=""){
                    $result = SOSSData::Query ($s->storename,urlencode($s->search));
                }else{
                    $result = SOSSData::Query ($s->storename,null);
                }
                if($result->success){
                    $f->{$s->storename}=$result->result;
                    if(isset($result->result)){
                        if($s->search==""){
                            $s->search="all";
                        }
                        CacheData::setObjects(md5($s->search),$s->storename,$result->result);
                    }
                }else{
                    $f->{$s->storename}=null;
                }
            }else{
                $f->{$s->storename}= $result;
            }
            
        }
        return $f;
    }
}



    /*
        {
            "from": {
                "domain": "raha.lk",
                "securityToken": "sssss"
            },
            "to": {
                "domains": ["green7.raha.lk", "abultiyalmoratuwa.raha.lk"],
                "securityToken": "sssss"
            },
            "dataStores": [
                {
                    "class": "orderheader",
                    "objects": [
                        {
                            "object": {},
                            "detailStores":  [
                                {
                                    "class":"orderdetails"
                                    "fkField":"orderHeaderId"
                                    "objects":[
                                        {
                                            "object": {}
                                            "detailStores" : null
                                        }                                
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }   
    */

?>
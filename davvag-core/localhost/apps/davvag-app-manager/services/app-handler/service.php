<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");
class BroadcastService {

    function __construct(){
        
    } 

    public function getallApplications($req,$res){
        
        $tenantFile = TENANT_RESOURCE_LOCATION . "/tenant.json";
        $fileToServe;$errorMsg;
        $apps=array();
        //var_dump($req->Params());
        if(!isset($_GET["Group"])){
            $res->SetError("Not a Valied Request");
            return null;
        }
        $Group=$_GET["Group"];
        $allkeys=CacheData::getObjects($Group,"domain_permision_e");
        if($allkeys)
            return $allkeys;
        
        if (file_exists($tenantFile)){
            $jsonContents = file_get_contents($tenantFile);
            $tenantObj = json_decode($jsonContents);

            if (isset($tenantObj)){
                foreach ($tenantObj->apps as $appCode => $appData) {
                    
                    
                    $appLocation = TENANT_RESOURCE_LOCATION_APPS . "/$appCode/app.json" ;
                    if (file_exists($appLocation)){
                        $jsonObj = json_decode(file_get_contents($appLocation));
                        //return $tenantObj->apps;
                        $app=new stdClass();
                        $app->appCode=$appCode;
                        $app->Name=$jsonObj->description->title;
                        $app->Icon=$jsonObj->description->icon;
                        $app->Services=array();
                        $app->Apps=array();
                        $app->Schemas=array();
                        if(isset($jsonObj->schemas)){
                            foreach($jsonObj->schemas as $Code => $Data){
                                $a=new stdClass();
                                $a->Name =$Code;
                                $a->FileName=$Data;
                                $a->selected=$this->Permistion($Group,$app->appCode,"schema",$Code,"");
                                array_push($app->Schemas,$a);
                            }
                        }
                        foreach ($jsonObj->components as $Code => $Data){
                            $a=new stdClass();
                            $a->Code=$Code;
                            $aLocation = TENANT_RESOURCE_LOCATION_APPS . "/$appCode/$Data->location/$Code/component.json";
                            if (file_exists($aLocation)){
                                $aObj = json_decode(file_get_contents($aLocation));
                                
                                $a->Name=$aObj->name;
                                $a->Description=$aObj->description;
                                $a->author=$aObj->author;
                                $a->version=$aObj->version;
                                switch($Data->type){
                                    case "partial":
                                        $a->selected=$this->Permistion($Group,$app->appCode,"app",$Code,"");
                                        array_push($app->Apps,$a);
                                    break;
                                    case "shell":
                                        if(isset($aObj->serviceHandler->methods)){
                                            $a->methods=array();
                                                
                                                foreach ($aObj->serviceHandler->methods as $m=>$md){
                                                    $method=new stdClass();
                                                    $method->name=$m;
                                                    $method->selected=$this->Permistion($Group,$app->appCode,"service",$Code,$m);
                                                    array_push($a->methods,$method);
    
                                                }
                                            }
                                            array_push($app->Services,$a);
                                    break;
                                    case "service":
                                        if(isset($aObj->serviceHandler->methods)){
                                        $a->methods=array();
                                            
                                            foreach ($aObj->serviceHandler->methods as $m=>$md){
                                                $method=new stdClass();
                                                $method->name=$m;
                                                $method->selected=$this->Permistion($Group,$app->appCode,"service",$Code,$m);
                                                array_push($a->methods,$method);

                                            }
                                        }
                                        array_push($app->Services,$a);
                                    break;
                                    case "component":
                                        $a->selected=$this->Permistion($Group,$app->appCode,"app",$Code,"");
                                        array_push($app->Apps,$a);
                                    break;
                                }
                            }else{
                                $app->Error= "This Location '$aLocation' not found.";
                            }
                        }
                        array_push($apps,$app);
                    }
                    
                }
            }
        }
        CacheData::setObjects($Group,"domain_permision_e",$apps);
        return $apps;
    }

    private function Permistion($Group,$App,$Type,$Code,$Method){
        $obj=Auth::GetAccess($Group,$App,$Type,$Code,$Method);
        ///var_dump($obj);
        if(isset($obj->keyid)){
            return true;
        }else{
            return false;
        }
    }

    public function getUserGroups($req,$res){
        return Auth::GetUserGroups();
    }

    public function postSetAccess($req,$res){
        $bodyAccess= $req->Body(true);
        $assdata=array();
        $groupid=$bodyAccess->groupid;
        $descObj =null;
        $descriptorLocation = TENANT_RESOURCE_LOCATION . "/tenant.json" ;
        if (file_exists($descriptorLocation)){
            $jsonFile = file_get_contents($descriptorLocation);
            $descObj = json_decode($jsonFile);
        }else{
            $res->SetError("Not Configured tenant.json missing");
            return null;
        }      
        $tenatjson=new stdClass();
        $tenatjson->apps=new stdClass();
        foreach($bodyAccess->data as $item){
            $appcode=$item->appCode;
            foreach($item->Services as $sitem){
                $code=$sitem->Code;
                $type="service";
                ///var_dump($sitem->methods);
                if(isset($sitem->methods)){
                    foreach($sitem->methods as $ops){
                        if($ops->selected){
                            if(!isset($tenatjson->{$appcode})){
                                $tenatjson->apps->{$appcode}=$descObj->apps->{$appcode};
                            }
                            array_push($assdata,array("groupid"=>$groupid,"appCode"=>$appcode,"type"=>$type,"code"=>$code,"operation"=>$ops->name));
                        }
                    }
                }
            }

            foreach($item->Apps as $sitem){
                $code=$sitem->Code;
                $type="app";
                if($sitem->selected){
                    if(!isset($tenatjson->{$appcode})){
                        $tenatjson->apps->{$appcode}=$descObj->apps->{$appcode};
                    }
                    array_push($assdata,array("groupid"=>$groupid,"appCode"=>$appcode,"type"=>$type,"code"=>$code,"operation"=>""));
                }
            }

            foreach($item->Schemas as $sitem){
                $code=$sitem->Name;
                $type="schema";
                if($sitem->selected){
                    if(!isset($tenatjson->{$appcode})){
                        $tenatjson->apps->{$appcode}=$descObj->apps->{$appcode};
                    }
                    array_push($assdata,array("groupid"=>$groupid,"appCode"=>$appcode,"type"=>$type,"code"=>$code,"operation"=>""));
                }
            }
        }
        CacheData::clearObjects("domain_permision_e");
        $tenatjson->webdock=$descObj->webdock;
        file_put_contents(TENANT_RESOURCE_LOCATION ."/$groupid.json",json_encode($tenatjson));
        
        //Auth::SetAccess($assdata);
        return  Auth::SetAccess($assdata);

    }


    private function changeGroup($userid,$groupid){
        $r = SOSSData::Query ("domain_permision", "userid:$userid");
        //var_dump($r);
        if(count($r->result)>0){
            $save=$r->result[0];
            $save->groupid=$groupid;
            $result = SOSSData::Update("domain_permision", $save);
            //echo json_encode($result);
            return $save;
        }else{
           return null;
        }

    }


    public function postDeleteItem($req,$res){
        $body=$req->Body(true);
        $rd=SOSSData::Delete("schedule_pending", $body);
        if($rd->success){
            return $rd->result;
        }else{
            $res->SetError ($rd->result);
            return $rd->result; 
        }
    }


}

?>
<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");

class keywordService {

    function __construct(){
        
    } 

    public function getallKeywords($req,$res){
        $allkeys=CacheData::getObjects("all","fb_keywords");
        if(isset($allkeys)){
            return $allkeys;
        }else{
            $r = SOSSData::Query ("fb_keywords", null);
            if($r->success){
                return $r->result;
            }else{
                $res->SetError ("Error Loading data");
                return $r->result; 
            }
        }
    }

    public function postSaveKeywords($req,$res){
        $keyword=$req->Body(true);
        $keyword->id=isset($keyword->id)?$keyword->id:0;
        $arr=explode(",",$keyword->keywords);
        $r = SOSSData::Query ("fb_keywords", "id:$keyword->id");
        //return $arr;
        //return $this->validate($arr,$keyword);
        //if(isset($keyword->))
        if(!$this->validate($arr,$keyword)){
            $res->SetError ("duplicate keyword validation failed");
            return $keyword;
        }
        if(count($r->result)==0){
            //insert
            $result=SOSSData::Insert ("fb_keywords", $keyword);
            if($result->success){
                $keyword->id=$result->result->generatedId;
                $this->saveDetails($arr,$keyword);
                return $keyword;
            }
        }else{
                $r = SOSSData::Query ("fb_keywords_detail", "id:$keyword->id");
                $rd=SOSSData::Delete("fb_keywords_detail", $r->result);
                $result=SOSSData::Update ("fb_keywords", $keyword);
                if($result->success){
                    $this->saveDetails($arr,$keyword);
                    return $keyword;
                }else{
                    $res->SetError ($result->result);
                    return $keyword;
                }
            //}else{
                //$res->SetError ($r->result);
                //return $keyword;
            //}
            //return $r;


        }
        $res->SetError ("Not Processed the request");
        //return $keyword;
    }

    public function getKeywordByID($req,$res){
        if(isset($_GET["id"])){
            $r = SOSSData::Query ("fb_keywords", "id:".$_GET["id"]);
            if($r->success){
                return count($r->result)==0?[]:$r->result[0];
            }else{
                $res->SetError ($r->result);
            }
        }else{
            $res->SetError ("Error Loading data");
            exit();
        }
    }

    private function validate($arr,$keyword){
        $keys="";
        foreach ($arr as  $value) {
            $keys.="'".$value."',";
        }
        $keys=substr($keys, 0, -1);
        //return $keys;
        $mainObj = new stdClass();
        $mainObj->parameters = new stdClass();
        $mainObj->parameters->keywords = $keys;
        $mainObj->parameters->id = strval($keyword->id);
        //$mainObj->parameters->search = isset($_GET["q"]) ?  $_GET["q"] : "";
        $resultObj = SOSSData::ExecuteRaw("fb_keywords_detail_pod_keysords", $mainObj);
        if(count($resultObj->result)){
            return false;
        }else{
            return true;
        }
        

    }

    private function saveDetails($arr,$keyword){
        foreach ($arr as  $value) {
            $keydetals[]=array(
                "keywordid"=>md5($value),
                "id"=>$keyword->id,
                "keyword"=>$value,
                "filter"=>$keyword->filter,
                "davvagflow"=>$keyword->davvagflow
            );
        }
        $result=SOSSData::Insert ("fb_keywords_detail", $keydetals);
        //var_dump()
        return $result;
    }

    public function getDavvagFlows($req,$res){
        if(isset($_GET["pageid"])){
            $pagid =$_GET["pageid"];
            if($pagid=="@none")
            $filename=($pagid=="@none"? TENANT_RESOURCE_LOCATION."/davvag-flow/":TENANT_RESOURCE_LOCATION."/davvag-flow/$pagid/");
            $files = scandir($filename);

            foreach ($files as $key => $value) {
                # code...
                list($name,$ext)=explode(".",$value);
                if($name!=""){
                $outdata[]=array(
                    "name"=>$name,"file"=>$value
                );}
            }
            return $outdata;
        }else{
            $res->SetError ("Error Loading data");
            exit();
        }
    }
    
}

?>
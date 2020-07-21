<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");

class SearchServices {

    public function postq($req){
        $sall=$req->Body(true);
        $f=new stdClass();
        foreach($sall as $s){
            $keyvalue=$this::indexkey($s);
            $result= CacheData::getObjects_fullcache(md5($keyvalue),$s->storename);
            if(!isset($result)){
                if(isset($s->search)){
                    if($s->search!=""){
                        $result = SOSSData::Query ($s->storename,urlencode($s->search));
                    }else{
                        $result = SOSSData::Query ($s->storename,null);
                    }
                }else{
                    $result = SOSSData::ExecuteRaw ($s->storename,$s);
                }
                if($result->success){
                    $f->{$s->storename}=$result->result;
                    if(isset($result->result)){
                        CacheData::setObjects(md5($keyvalue),$s->storename,$result->result);
                    }
                }else{
                    //var_dump($result->);
                    //return $result;
                    $f->{$s->storename}=$result;
                }
            }else{
                $f->{$s->storename}= $result;
            }
            
        }
        return $f;
    }

    private function indexkey($s){
        if(isset($s->search)){
            return $s->storename."-".$s->search;
        }else{
            $svalue=$s->storename."-";
            foreach ($s->parameters as $name => $value) {
                $svalue.= "$name:$value";
            }
            return $svalue;
        }
    }

    public function postPodq($req){
        $sall=$req->Body(true);
        $f=new stdClass();

        foreach($sall as $s){
            $svalue="";
            foreach ($s->parameters as $name => $value) {
                $svalue.= "$name:$value";
            }
            $result= CacheData::getObjects(md5($svalu),$s->storename);
            if(!isset($result)){
                $result = SOSSData::ExecuteRaw ($s->storename,$s);
                if($result->success){
                    $f->{$s->storename}=$result->result;
                    if(isset($result->result)){
                        CacheData::setObjects(md5($svalue),$s->storename,$result->result);
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

    function postSettings($req){
        $path = MEDIA_FOLDER."/".HOST_NAME."/global-setting/";
        $saveObj=$req->Body(true);
        if (!file_exists($path))
              mkdir($path, 0777, true);
        
        $filename=$saveObj->name;
        $path=$path."/".$filename.".glb";
        if (file_exists($path)){
                $f = fopen($path, 'r');
                $buffer = '';
                while(!feof($f)) {
                    $buffer .= fread($f, 2048);
                }
                fclose($f);
                return json_decode($buffer);
            
            }else{
            return null;
        }    
    }
}

?>
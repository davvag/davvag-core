<?php
require_once(PLUGIN_PATH."/phpcache/cache.php");
require_once(PLUGIN_PATH."/sossdata/SOSSData.php");
class tags{

    public function addTag($id,$tag){
        $uid=md5($id."-".$tag);
        $obj=CacheData::getObjects($uid,"profile_tags");
        if(!isset($obj)){
            $result = SOSSData::Query ("profile_tags", urlencode("uid:".$uid.""));
            if(count( $result->result)){
                $obj=$result->result[0];
                CacheData::setObjects($uid,"profile_tags",$obj);
            }
        }
        //return $tag;
        //$tags=new tags();
        if(!isset($obj)){
            $obj =array("uid"=>$uid,"id"=>$id,"tag"=>$tag);
            $result=SOSSData::Insert ("profile_tags", $obj);
            if($result->success){
                CacheData::setObjects($uid,"profile_tags",$obj);
                return $obj;
            }else{
                return $result;
            }
        }else{
            return $obj;
        }
    }

    public function removeTag($id,$tag)
    {
        $uid=md5($id."-".$tag);
        $result = SOSSData::Query ("profile_tags", urlencode("uid:".$uid.""));
        if(count($result->result)!=0){
            SOSSData::Delete("profile_tags", $result->result[0]);
            if($result->success){
                CacheData::clearObjects("profile_tags");
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function checkTag($id,$tag)
    {
        $uid=md5($id."-".$tag);
        $obj=CacheData::getObjects($uid,"profile_tags");
        if(isset($obj)){
            return true;
        }
        $result = SOSSData::Query ("profile_tags", urlencode("uid:".$uid.""));
        if(count($result->result)!=0){
            CacheData::setObjects($uid,"profile_tags",$result->result[0]);
            return true;
        }else{
            return false;
        }
    }

    public function getTags($id)
    {
        $obj=CacheData::getObjects($id,"profile_tags");
        if(isset($obj)){
            return $obj;
        }
        $result = SOSSData::Query ("profile_tags", urlencode("id:".$id.""));
        if($result->success){
            CacheData::setObjects($id,"profile_tags",$result->result);
            return $result->result;
        }else{
            return null; 
        }

    }
}
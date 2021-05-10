<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH_LOCAL . "/profile/profile.php");


class appService {

    function __construct(){
        
    } 


    public function postFollow($req,$res){
        $p=$req->Body(true);
        $profile= Profile::getProfile($p->id,$p->pid);
        if($profile){
            if(isset($profile->profile)){
                $userprofile=Profile::getUserProfile();
                if($p->liked){
                    SOSSData::Insert("profile_followers",$p);
                }else{
                    SOSSData::Delete("profile_followers",$p);
                    return $p;
                }
                $id=$userprofile?$userprofile->profile->id:0;
                Profile::getClearProfile($id,$id);
                Profile::getClearProfile($p->id,$id);
                return $p;
            }else{
                $res->SetError ("invalied Follower.");
                return;
            }
        }else{
            $res->SetError ($result);
            return;
        }
    }

    public function getProfile($req,$res){
        //SOSSData::Insert("products_reviews",array("itemid"=>0,"pid"=>"0"));
        $userprofile=Profile::getUserProfile();
        //return $userprofile;
        $id=$userprofile?$userprofile->profile->id:0;
        $profile=Profile::getProfile($_GET["id"],$id);
        if(isset($profile->profile)){
            $outprofile = $profile->profile;
           
            $outprofile->followed= $id==$outprofile->id?-1:$outprofile->followed;
            
            return $outprofile;
        }else{
            return array();
        }
    }

    public function postSendMessage($req,$res){
        $p=$req->Body(true);
        
        $userprofile=Profile::getUserProfile();
        if($userprofile){
            $profile= Profile::getProfile($p->id,$userprofile->profile->id);
            if(isset($profile->profile)){
                $message =new stdClass();
                $message->inboxid=$profile->profile->id;
                $message->userboxid=$userprofile->profile->id;
                $message->messagetype=$p->messagetype;
                $message->messagetext=$p->message;
                $message->m_read=0;
                $message->m_to=$profile->profile->id;
                $message->m_from=$userprofile->profile->id;
                $message->msgdate=date("m-d-Y H:i:s");
               
                $r=SOSSData::Insert("messages",$message);
                $data=array('name' =>$userprofile->profile->name, 'pid' =>$userprofile->profile->id);
                Profile::AddNotify($profile->profile->id,"message_received",$data);
                
                if($r->success){
                    $mybox=$message;
                    $mybox->inboxid=$userprofile->profile->id;
                    $mybox->userboxid=$profile->profile->id;
                    $rm=SOSSData::Insert("messages",$mybox);
                    //Profile::AddNotify($userprofile->profile->id,"message",$message);
                    if($rm->success){
                        $mybox->id=$rm->result->generatedId;
                        profile::Send_Notify();
                        return $mybox;
                    }else{
                        $res->SetError ($rm);
                        return;
                    }
                }else{
                    $res->SetError ($r);
                    return;
                }
            }else{
                $res->SetError ($profile);
                return;
            }
        }else{
            $res->SetError ("User Not Authendicated.");
            return;
        }
    }

    public function getMessages($req,$res){
        $userprofile=Profile::getUserProfile();
        if($userprofile){
            $mainObj = new stdClass();
            $mainObj->parameters = new stdClass();
            $mainObj->parameters->id =isset($_GET["id"])?$_GET["id"]:0;
            $mainObj->parameters->lastid =isset($_GET["lastid"])?$_GET["lastid"]:0;
            $mainObj->parameters->page =isset($_GET["page"])?$_GET["page"]:0;
            $mainObj->parameters->size =isset($_GET["size"])?$_GET["size"]:40;
            $mainObj->parameters->userid =$userprofile->profile->id;
            $resultObj = SOSSData::ExecuteRaw("messages_query", $mainObj);
            if($resultObj->success){
                return $resultObj->result;
            }else{
                $res->SetError ($resultObj);
                return;
            }
        }else{
            $res->SetError ("User Not Authendicated.");
            return;
        }
    }
    

    public function getAllProducts($req,$res){
        if (isset($_GET["page"]) && isset($_GET["size"])){
            require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
            if(!isset($_GET["id"])){
                SOSSData::Insert("products_reviews",array("itemid"=>0,"pid"=>"0"));
                SOSSData::Insert("profile_followers",array("id"=>0,"pid"=>"0"));
            }
            $mainObj = new stdClass();
            $mainObj->parameters = new stdClass();
            $mainObj->parameters->page = $_GET["page"];
            $mainObj->parameters->size = $_GET["size"];
            $mainObj->parameters->id = $_GET["id"];
            $mainObj->parameters->pid = $_GET["pid"];
            $mainObj->parameters->tid = $_GET["tid"];


            $resultObj = SOSSData::ExecuteRaw("products_stelup_2", $mainObj);
            if($resultObj->success){
                return $resultObj->result;
            }else{
                //if(!$result->success){
                //}
                $res->SetError ($resultObj);
                return $resultObj;
            }
        } else {
            
            $mainObj = new stdClass();
            $mainObj->error="Invalied Query";
            return $mainObj;
        }
    }

    


}

?>
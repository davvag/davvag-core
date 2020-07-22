<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");
class BroadcastService {

    function __construct(){
        
    } 

    public function getallusers($req,$res){
        $allkeys=CacheData::getObjects("all","users");
        if(isset($allkeys)){
            return $allkeys;
        }else{
            $r = SOSSData::Query ("users", null);
            if($r->success){
                return $r->result;
            }else{
                $res->SetError ($r->result);
                return $r->result; 
            }
        }
    }

    public function postRegisterUser($req,$res){
        //require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
        $bodyReguser= $req->Body(true);
        $user =new stdclass();
        if(isset($bodyReguser->username)){
            $user->username=$bodyReguser->username;
        }else{
            $user->username=$bodyReguser->email;
        }
        $user->email=$bodyReguser->email;
        $user->name=$bodyReguser->name;
        $user->password=$bodyReguser->password;
        $r = SOSSData::Query ("users", "email:$user->email");
        if(count($r->result)>0){
            $res->SetError ("Aready resgitered cound not be Updated");
            exit();
        }

        $outObject = Auth::SaveUser($user);
        
        if(isset($outObject->userid)){
            $bodyReguser->usergroupRses=Auth::Join(HOST_NAME,$outObject->userid,$bodyReguser->groupid);
            $bodyReguser->createdate=date_format(new DateTime(), 'm-d-Y H:i:s');
            $bodyReguser->userid=$outObject->userid;
            $bodyReguser->linkeduserid=$outObject->userid;
            $bodyReguser->status="tobeactivated";
            if(!isset($bodyReguser->catogory)){
                $bodyReguser->catogory="User";
            }
            $result = SOSSData::Insert ("profile", $bodyReguser,$tenantId = null);
            $bodyReguser->id= $result->result->generatedId;
            return $bodyReguser;
        }else{
            $res->SetError ($outObject);
            return $outObject;
        }
        
    }

    public function getChangeGroup($req,$res){
        if(isset($_GET["userid"]) && isset($_GET["groupid"])){
            return $this->changeGroup($_GET["userid"],$_GET["groupid"]);
        }else{
            $res->SetError("Invalied call");
            return null;
        }
    }
    
    public function getUserGroups($req,$res){
        return Auth::GetUserGroups();
    }

    public function getNewUserGroup($req,$res){
        if(isset($_GET["groupid"])){
            return Auth::NewUserGroup($_GET["groupid"]);
        }else{
            $res->SetError("Invalied call");
            return null;
        }
    }
    private function changeGroup($userid,$groupid){
        return Auth::Join(HOST_NAME,$userid,$groupid);
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
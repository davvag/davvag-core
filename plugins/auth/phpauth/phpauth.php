<?php
class phpauth implements iDavvagAuth{
    public function Login ($username, $password){
        $result=SOSSData::Query("users","email:".$username,null,"asc",20,0,AUTH_DOMAIN);
        $user=null;
        if(count($result->result)!=0){
            $user=$result->result[0];
        }else{

            $result=SOSSData::Query("users","username:".$username,null,"asc",20,0,AUTH_DOMAIN);
            if(count($result->result)!=0)
                $user=$result->result[0];
        }
        if(!empty($user)){
            if($user->password==md5($password)){
                $NotiData=new stdClass();
                $NotiData->domain=AUTH_DOMAIN;
                $NotiData->reqdomain=$_SERVER["HTTP_HOST"];
                $NotiData->ip=$_SERVER['REMOTE_ADDR'];
                $NotiData->remoteuser=empty($_SERVER['REMOTE_USER'])?"-":$_SERVER['REMOTE_USER'];
                $NotiData->remotehost=empty($_SERVER['REMOTE_HOST'])?"-":$_SERVER['REMOTE_HOST'];
                $NotiData->authMode="Davvag Authendication.";
                Notify::sendEmailMessage($user->name,$user->emial,"auth-login",$NotiData);
                return $this->createSession($user,$NotiData);
            }else{
                return $this->error("email or password Incorrect.");
            }
        }else{
            return $this->error("email or password Incorrect.");
        }

    }

    private function createSession($user,$ndata){
        $session =new stdClass();
        $session->userid=$user->userid;
        $session->email=$user->email;
        $session->token=$this->GetNewSessionID();
        $session->clientIP=$ndata->ip;
        $session->jwt="";
        $session->sysgroup=$this->GetUserGroup($session->userid,$ndata->domain);
        $session->group=$session->sysgroup;
        $session->otherdata=$ndata;
        $r=SOSSData::Insert("sessions",$session,AUTH_DOMAIN);
        if($r->success){
            return $session;
        }else{
            return null;
        }


    }

    private function GetUserGroup($userid,$domain)
    {
        $key=md5($domain."-".$userid);
        $result=SOSSData::Query("domain_permision","keyid:".$key,null,"asc",20,0,AUTH_DOMAIN);
        if(count($result->result)>0){
            return $result->result[0]->groupid;
        }else{
            return "Not Autherized";
        }
    }
    public function SocialLogin ($app, $code,$create){throw new Exception("Not Implemented");}
    public function GetResetToken ($email){
        $result=SOSSData::Query("users","email:".$email,null,"asc",20,0,AUTH_DOMAIN);
        $user =$result->success?(count($result->result)>0?$result->result[0]:null):null;
        if(!empty($user)){
            $NotiData=new stdClass();
            $NotiData->domain=AUTH_DOMAIN;
            $NotiData->reqdomain=$_SERVER["HTTP_HOST"];
            $NotiData->ip=$_SERVER['REMOTE_ADDR'];
            $NotiData->remoteuser=empty($_SERVER['REMOTE_USER'])?"-":$_SERVER['REMOTE_USER'];
            $NotiData->remotehost=empty($_SERVER['REMOTE_HOST'])?"-":$_SERVER['REMOTE_HOST'];
            $NotiData->authMode="Reset Token.";
            $NotiData->resetcode=$this->getResetCode($email);
            Notify::sendEmailMessage($user->name,$user->emial,"auth-passwordresetcode",$NotiData);
        }else{
            $this->error("User Not Found.");
        }
    }

    private function getResetCode($email){
        $result=SOSSData::Query("passwordresets","email:".$email,null,"asc",20,0,AUTH_DOMAIN);
        $passrest=new stdClass();
            $passrest->email=$email;
            $passrest->message=uniqid();
        if(count($result->result)>0){
            
            SOSSData::Update("passwordresets",$passrest);
        }else{
            SOSSData::Insert("passwordresets",$passrest);
        }
        return $passrest->message;
    }
    public function SaveUser ($user){
        
        $result=SOSSData::Query("users","email:".$user->email,null,"asc",20,0,AUTH_DOMAIN);
        if(count($result->result)!=0){
            return $this->error("Already registered");
            //throw new Exception("Already registered");
        }else{
            $original=$user->password;
            $user->password=md5($user->password);
            $user->userid=$this->GetNewUserID();
            SOSSData::Insert("users",$user,AUTH_DOMAIN);
            $this->Join(AUTH_DOMAIN, $user->userid,"web_user");
            $NotiData=new stdClass();
            $NotiData->domain=AUTH_DOMAIN;
            $NotiData->reqdomain=$_SERVER["HTTP_HOST"];
            $NotiData->ip=$_SERVER['REMOTE_ADDR'];
            $NotiData->remoteuser=$_SERVER['REMOTE_USER'];
            $NotiData->remotehost=$_SERVER['REMOTE_HOST'];
            $NotiData->password=$original;
            Notify::sendEmailMessage($user->name,$user->emial,"auth-registeruser",$NotiData);

        }
    
    }
    public function NewDomain($data)
    {
        if(empty($data->otherdata->usersname) || empty($data->otherdata->password)){
            throw new Exception("Registration Invalied");
        }else{
            $result=SOSSData::Query("domains","domain:".$data->domain,null,"asc",20,0,AUTH_DOMAIN);
            if(count($result->result)!=0){
                return $this->error("Already registered");
                //throw new Exception("Already registered");
            }else{
                $result=SOSSData::Query("users","email:".$data->email,null,"asc",20,0,AUTH_DOMAIN);
                $user=new stdClass();
                if(count($result->result)!=0)
                {
                    $user=$result->result[0];
                }else{
                    $user->username=$data->otherdata->usersname;
                    $user->password=$data->otherdata->password;
                    $user->name=$data->userfullname;
                    $user->email=$data->email;
                    //$data->otherdata->userid=
                    $userSave=$this->SaveUser($user);
                }
                $data->otherdata->userid=$user->userid;
                $data->createdUser=$user->userid;
                $this->Join($data->domain,$user->userid,"sysadmin");
                unset($data->otherdata->usersname);
                unset($data->otherdata->password);
                $result=SOSSData::Insert("domains",$data,AUTH_DOMAIN);
                if($result->success){
                    return $data;
                }else{
                    return $this->error($result->message);
                }
            }
            
        }
        
    }
    public function Join ($domain,$userid,$usergroup){
        $key=md5($domain."-".$userid);
        $prm =new stdClass();
        $prm->domain=$domain;
        $prm->userid=$userid;
        $prm->groupid=$usergroup;
        $prm->keyid=$key;
        
        $result=SOSSData::Query("domain_permision","keyid:".$key,null,"asc",20,0,AUTH_DOMAIN);
        if(count($result->result)!=0)
        {   
            SOSSData::Update("domain_permision",$prm,AUTH_DOMAIN);
        }else{
            SOSSData::Insert("domain_permision",$prm,AUTH_DOMAIN);
        }
        return $prm;
    }

    public function ResetPassword ($email, $token, $newPassword){
        $result=SOSSData::Query("passwordresets","email:".$email,null,"asc",20,0,AUTH_DOMAIN);
        $message=new stdClass();
        if(count($result->result)>0){
            if($result->result[0]->message==$token){
                $users=SOSSData::Query("users","email".$email,null,"asc",20,0,AUTH_DOMAIN);
                if(count($users->result)>0){
                    $user=$users->result[0];
                    $user->password=md5($newPassword);
                    $users=SOSSData::Update("users",$user,AUTH_DOMAIN);
                    $NotiData=new stdClass();
                    $NotiData->domain=AUTH_DOMAIN;
                    $NotiData->reqdomain=$_SERVER["HTTP_HOST"];
                    $NotiData->ip=$_SERVER['REMOTE_ADDR'];
                    $NotiData->remoteuser=$_SERVER['REMOTE_USER'];
                    $NotiData->remotehost=$_SERVER['REMOTE_HOST'];
                    $NotiData->password=$newPassword;
                    Notify::sendEmailMessage($user->name,$user->emial,"auth-passwordchanged-reset",$NotiData);
                    $message->success=true;
                    $message->message="Password successfully resetted";
                }else{
                    $message->success=false;
                    $message->message="Reset Token is invalied";
                }
            }
        }else{
            $message->success=false;
            $message->message="Reset Token is invalied";
        }
        return $message;
    }

    public function ChangePassword ($oldpassword, $newPassword){
        $message=new stdClass();
        if(isset($_SESSION["authData"])){
            $users=SOSSData::Query("users","userid".$_SESSION["authData"]->userid,null,"asc",20,0,AUTH_DOMAIN);
                if(count($users->result)>0){
                    $user=$users->result[0];
                    if($user->password==md5($oldpassword)){
                        $user->password=md5($newPassword);
                        $users=SOSSData::Update("users",$user,AUTH_DOMAIN);
                        $NotiData=new stdClass();
                        $NotiData->domain=AUTH_DOMAIN;
                        $NotiData->reqdomain=$_SERVER["HTTP_HOST"];
                        $NotiData->ip=$_SERVER['REMOTE_ADDR'];
                        $NotiData->remoteuser=$_SERVER['REMOTE_USER'];
                        $NotiData->remotehost=$_SERVER['REMOTE_HOST'];
                        $NotiData->password=$newPassword;
                        Notify::sendEmailMessage($user->name,$user->emial,"auth-passwordchanged",$NotiData);
                        $message->success=true;
                        $message->message="Password Changed";
                    }else{
                        $message->success=false;
                        $message->message="User not Found";
                    }
                }
        }else{
            $message->success=false;
            $message->message="Session Not Valied";
        }
        return $message;
    }
    public function GetSession ($token){
        $result=SOSSData::Query("sessions","token:".$token,null,"asc",20,0,AUTH_DOMAIN);
        if(count($result->result)>0)
        {
            $session =$result->result[0];
            $session->group=$session->sysgroup;
            return $session;
        }else{
            return $this->error("Error session Not valied");
        }
    }
    public function GetLogout ($token){
        $session =$this->GetSession($token);
        if(empty($session->token)){
            return $session;
        }else{
            SOSSData::Delete("sessions",$session);
            return true;
        }
        
    }
    public function GetUserGroups (){
        $result=SOSSData::Query("usergroups","",null,"asc",20,0,AUTH_DOMAIN);
        if(count($result->result)==0){
            $usergroups=array();
            $group=new stdClass();
            $group->groupid="anonymous";
            array_push($usergroups,$group);
            $group=new stdClass();
            $group->groupid="web_user";
            array_push($usergroups,$group);
            $group=new stdClass();
            $group->groupid="facebook_user";
            array_push($usergroups,$group);
            $group=new stdClass();
            $group->groupid="sysadmin";
            array_push($usergroups,$group);
            $group=new stdClass();
            $group->groupid="sysuser";
            array_push($usergroups,$group);
            SOSSData::Insert("usergroups",$usergroups,AUTH_DOMAIN);
            return $usergroups;
        }else{
            return $result->result;
        }

    }
    public function NewUserGroup ($groupid){
        $result=SOSSData::Query("usergroups","groupid:".$groupid,null,"asc",20,0,AUTH_DOMAIN);
        if(count($result->result)==0){
            $group=new stdClass();
            $group->groupid=$groupid;
            //array_push($usergroups,$group);
            SOSSData::Insert("usergroups",$group,AUTH_DOMAIN);
            return array($group);
        }else{
            return null;
        }
    }

    public function SetAccess ($uapps){
        $uapp=count($uapps)>0?(object)$uapps[0]:null;
        $groupid=isset($uapp)?$uapp->groupid:"";
        $saveObject=array();
        $data=SOSSData::Query("usergroup_permission","domain:".AUTH_DOMAIN.",groupid:".$groupid,null,"asc",20,0,AUTH_DOMAIN);
        SOSSData::Delete("usergroup_permission",$data->result,AUTH_DOMAIN);
        foreach ($uapps as $key => $app) {
            # code...
            $application=(object)$app;
            if($application->groupid!=$groupid)
                $application->groupid=$groupid;
            $application->domain=AUTH_DOMAIN;
            $application->keyid =md5($application->groupid."-".AUTH_DOMAIN."-".$application->appCode."-"."-".$application->type."-".$application->code."-".$application->operation);
            array_push($saveObject,$application);
        }
        SOSSData::Insert("usergroup_permission",$saveObject,AUTH_DOMAIN);
    }

    public function GetAccess ($groupid,$app,$type=null,$code=null,$ops=null){
        if($ops==""){
            $ops="null";
        }
        if(isset($type) && isset($code) && isset($ops)){
            if($groupid=="sysadmin"){
                $application=new stdClass();
                $application->keyid =md5($groupid."-".AUTH_DOMAIN."-".$app."-"."-".$type."-".$code."-".$ops);
                $application->groupid=$groupid;
                $application->domain=AUTH_DOMAIN;
                $application->appCode=$app;
                $application->type=$type;
                $application->code =$code;
                $application->operation=$ops;
                return $application;
            }else{
                $data=SOSSData::Query("usergroup_permission","domain:".AUTH_DOMAIN.",groupid:".$groupid.",appCode:".$app.",type:".$type.",code:".$code.",operation:".$ops,null,"asc",20,0,AUTH_DOMAIN);
                if(count($data->result)>0){
                    return $data->result[0];
                }else{
                    return $this->error("Not Permitted");
                }
            }
        }   
        else{
            if($groupid=="sysadmin"){
                $applist=array();
                $application=new stdClass();
                $application->keyid =md5($groupid."-".AUTH_DOMAIN."-".$app.$type.$code.$ops);
                $application->groupid=$groupid;
                $application->domain=AUTH_DOMAIN;
                $application->appCode=$app;
                $application->type="*";
                $application->code ="*";
                $application->operation="*";
                array_push($applist,$application);
                return $applist;
            }else{
                $data=SOSSData::Query("usergroup_permission","domain:".AUTH_DOMAIN,null,"asc",1000,0,AUTH_DOMAIN);
                return $data->result;
            }

        }
        //throw new Exception("Not Implemented");
    }

    public function GetDomainAttributes (){
        $data=SOSSData::Query("domains","domain:".AUTH_DOMAIN,null,"asc",20,0,AUTH_DOMAIN);
        if($data->success){
            if(count($data->result)!=0){
                return $data->result[0];
            }else{
                return $this->error("Domain dose not exist.");
                //throw new Exception("Domain dose not exist.");
            }
             
        }else{
            return $this->error($data->message);
            //throw new Exception($data->message);
        }
        
    }
    public function CrossDomainAPICall($domain,$url,$method="GET",$data=null){throw new Exception("Not Implemented");}
    public function AutendicateDomain($tname,$securityToken,$appname,$operation){throw new Exception("Not Implemented");}
    private function GetNewUserID(){
        $userid=uniqid();
        $rs=SOSSData::Query("users","userid:".$userid,null,"asc",20,0,AUTH_DOMAIN);
        if(count($rs->result)!=0){
            return $this->GetNewUserID();
        }else{
            return $userid;
        }

    }

    private function GetNewSessionID(){
        $userid=uniqid();
        $rs=SOSSData::Query("sessions","token:".$userid,null,"asc",20,0,AUTH_DOMAIN);
        if(count($rs->result)!=0){
            return $this->GetNewSessionID();
        }else{
            return $userid;
        }

    }

    private function error($message){
        $errorObject=new stdClass();
        $errorObject->timestamp=time();
        $errorObject->status=400;
        $errorObject->error="Bad Request";
        $errorObject->message=$message;
        return $errorObject;

    }
}
?>
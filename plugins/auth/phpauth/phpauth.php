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
    public function GetResetToken ($email){throw new Exception("Not Implemented");}
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
    public function ResetPassword ($email, $token, $newPassword){throw new Exception("Not Implemented");}
    public function ChangePassword ($oldpassword, $newPassword){throw new Exception("Not Implemented");}
    public function GetSession ($token){throw new Exception("Not Implemented");}
    public function GetLogout ($token){throw new Exception("Not Implemented");}
    public function GetUserGroups (){throw new Exception("Not Implemented");}
    public function NewUserGroup ($groupid){throw new Exception("Not Implemented");}
    public function GetAccess ($groupid,$app,$type=null,$code=null,$ops=null){throw new Exception("Not Implemented");}
    public function SetAccess ($uapp){throw new Exception("Not Implemented");}
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
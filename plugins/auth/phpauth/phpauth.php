<?php
class phpauth implements iDavvagAuth{
    public function Login ($username, $password){throw new Exception("Not Implemented");}
    public function SocialLogin ($app, $code,$create){throw new Exception("Not Implemented");}
    public function GetResetToken ($email){throw new Exception("Not Implemented");}
    public function SaveUser ($user){
        
        $result=SOSSData::Query("users","email:".$user->email);
        if(count($result->result)!=0){
            return $this->error("Already registered");
            //throw new Exception("Already registered");
        }else{
            $original=$user->password;
            $user->password=md5($user->password);
            $user->userid=$this->GetNewUserID();
            SOSSData::Insert("users",$user);
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
            $result=SOSSData::Query("domains","domain:".$data->domain);
            if(count($result->result)!=0){
                return $this->error("Already registered");
                //throw new Exception("Already registered");
            }else{
                $result=SOSSData::Query("users","email:".$data->email);
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
                $result=SOSSData::Insert("domains",$data);
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
        
        $result=SOSSData::Query("domain_permision","keyid:".$key);
        if(count($result->result)!=0)
        {   
            SOSSData::Update("domain_permision",$prm);
        }else{
            SOSSData::Insert("domain_permision",$prm);
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
        $data=SOSSData::Query("domains","domain:".AUTH_DOMAIN);
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
        $rs=SOSSData::Query("users","userid:".$userid);
        if(count($rs->result)!=0){
            return $this->GetNewUserID();
        }else{
            return $userid;
        }

    }

    private function error($message){
       /* {
            "timestamp": 1660883255746,
            "status": 400,
            "error": "Bad Request",
            "exception": "com.sossgrid.exceptions.ServiceException",
            "message": "com.mysql.jdbc.exceptions.jdbc4.MySQLNonTransientConnectionException cannot be cast to com.mysql.jdbc.exceptions.jdbc4.MySQLSyntaxErrorException",
            "path": "/login/sss/sss/sss"
            }*/
        $errorObject=new stdClass();
        $errorObject->timestamp=time();
        $errorObject->status=400;
        $errorObject->error="Bad Request";
        $errorObject->message=$message;
        return $errorObject;

    }
}
?>
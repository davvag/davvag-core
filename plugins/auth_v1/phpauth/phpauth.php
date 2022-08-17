<?php
class phpauth implements iDavvagAuth{
    public function Login ($username, $password){throw new Exception("Not Implemented");}
    public function SocialLogin ($app, $code,$create){throw new Exception("Not Implemented");}
    public function GetResetToken ($email){throw new Exception("Not Implemented");}
    public function SaveUser ($user){throw new Exception("Not Implemented");}
    public function Join ($domain,$userid,$usergroup){throw new Exception("Not Implemented");}
    public function ResetPassword ($email, $token, $newPassword){throw new Exception("Not Implemented");}
    public function ChangePassword ($oldpassword, $newPassword){throw new Exception("Not Implemented");}
    public function GetSession ($token){throw new Exception("Not Implemented");}
    public function GetLogout ($token){throw new Exception("Not Implemented");}
    public function GetUserGroups (){throw new Exception("Not Implemented");}
    public function NewUserGroup ($groupid){throw new Exception("Not Implemented");}
    public function GetAccess ($groupid,$app,$type=null,$code=null,$ops=null){throw new Exception("Not Implemented");}
    public function SetAccess ($uapp){throw new Exception("Not Implemented");}
    public function GetDomainAttributes (){throw new Exception("Not Implemented");}
    public function CrossDomainAPICall($domain,$url,$method="GET",$data=null){throw new Exception("Not Implemented");}
    public function AutendicateDomain($tname,$securityToken,$appname,$operation){throw new Exception("Not Implemented");}
}
?>
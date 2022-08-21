<?php
interface iDavvagAuth {
    public function Login ($username, $password);
    public function SocialLogin ($app, $code,$create);
    public function GetResetToken ($email);
    public function SaveUser ($user);
    public function NewDomain ($data);
    public function Join ($domain,$userid,$usergroup);
    public function ResetPassword ($email, $token, $newPassword);
    public function ChangePassword ($oldpassword, $newPassword);
    public function GetSession ($token);
    public function GetLogout ($token);
    public function GetUserGroups ();
    public function NewUserGroup ($groupid);
    public function GetAccess ($groupid,$app,$type=null,$code=null,$ops=null);
    public function SetAccess ($uapp);
    public function GetDomainAttributes ();
    public function CrossDomainAPICall($domain,$url,$method="GET",$data=null);
    public function AutendicateDomain($tname,$securityToken,$appname,$operation);

}
?>
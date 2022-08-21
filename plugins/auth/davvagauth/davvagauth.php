<?php
    require_once("auth.php");

    class davvagauth implements iDavvagAuth{
        public function Login($username, $password)
        {
            return AuthSvr::Login($username,$password);
        }

        public function SocialLogin($app, $code, $create)
        {
            return AuthSvr::SocialLogin($app, $code, $create);
        }

        public function GetResetToken($email)
        {
            return AuthSvr::GetResetToken($email);
        }

        public function NewDomain($data)
        {
            return AuthSvr::NewDomain($data);
        }

        public function SaveUser ($user){
            return AuthSvr::SaveUser($user);
        }
        public function Join ($domain,$userid,$usergroup){
            return AuthSvr::Join($domain,$userid,$usergroup);
        }
        public function ResetPassword ($email, $token, $newPassword){
            return AuthSvr::ResetPassword($email,$token,$newPassword);
        }
        public function ChangePassword ($oldpassword, $newPassword){
            return AuthSvr::ChangePassword($oldpassword,$newPassword);
        }
        public function GetSession ($token){
            return AuthSvr::GetSession($token);
        }
        public function GetLogout ($token){
            return AuthSvr::GetLogout($token);
        }
        public function GetUserGroups (){
            return AuthSvr::GetUserGroups();
        }

        public function NewUserGroup ($groupid){
            return AuthSvr::NewDomain($groupid);
        }
        public function GetAccess ($groupid,$app,$type=null,$code=null,$ops=null){
            return AuthSvr::GetAccess($groupid,$app,$type,$code,$ops);
        }
        public function SetAccess ($uapp){
            return AuthSvr::SetAccess($uapp);
        }
        public function GetDomainAttributes (){
            return AuthSvr::GetDomainAttributes();
        }
        
        public function CrossDomainAPICall($domain,$url,$method="GET",$data=null){
            return AuthSvr::Autendicate($domain,$url,$method,$data);
        }
        public function AutendicateDomain($tname,$securityToken,$appname,$operation){
            return AuthSvr::AutendicateDomain($tname,$securityToken,$appname,$operation);
        }
    }
?>
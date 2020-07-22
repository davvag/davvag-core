<?php
require_once (PLUGIN_PATH . "/auth/auth.php");

class LoginService {

    function __construct(){
        
    } 

    public function getSession($req){
        if(isset($_COOKIE["securityToken"])){
            setcookie("sosskey",$_COOKIE["securityToken"]);
            //return $_GET["token"];
            return AUTH::GetSession($_COOKIE["securityToken"]);
        }else{
            $res->SetError ("Session not Valied");
            return;
        }
    }

    public function getLogin($req){
        $authObject =  AUTH::Login($_GET["email"], $_GET["password"]);
        
        $_SESSION["authData"] = json_encode($authObject);
        return $authObject;
    }

    public function getLogout($req){
        if(isset($_COOKIE['authData']))
        {
            $authdata=json_decode($_COOKIE['authData']);
            $outObject = Auth::GetLogout($authdata->token);
                if(!isset($outObject->error)){
                unset($_SESSION["authData"]);
                unset($_COOKIE['authData']); 
                setcookie('authData', null, -1, '/'); 
                unset($_COOKIE['securityToken']); 
                setcookie('securityToken', null, -1, '/'); 
                session_regenerate_id();
                //$outObject = new stdClass();
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        } 
    }

    public function getGetResetToken($req){
        return AUTH::GetResetToken ($_GET["token"]);
    }

    public function getResetPassword($req){
        return AUTH::ResetPassword ($_GET["email"], $_GET["token"], $_GET["password"]);
    }
}

?>
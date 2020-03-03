<?php
require_once (PLUGIN_PATH . "/auth/auth.php");

class LoginService {

    function __construct(){
        
    } 

    public function getSession($req,$res){
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
        unset ($_COOKIE["securityToken"]);
        unset ($_COOKIE["authData"]);
        setcookie("securityToken", null, -1, "/");
        setcookie("authData", null, -1, "/");
        unset($_SESSION["authData"]);
        session_regenerate_id();
        $outObject = new stdClass();
        return $outObject;
    }

    public function getGetResetToken($req){
        return AUTH::GetResetToken ($_GET["token"]);
    }

    public function getResetPassword($req){
        return AUTH::ResetPassword ($_GET["email"], $_GET["token"], $_GET["password"]);
    }
}

?>
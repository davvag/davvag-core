<?php
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == "GET"){
    if(!isset($_COOKIE["securityToken"])){
        $domain = Auth::GetDomainAttributes();

        if(isset($domain->name)){
            define("DOMAIN",$domain->domain);
            define("DOMAINNAME",$domain->name);
            require_once (dirname(__FILE__) . "/pages/signin.php");

        }else{
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            // Output: m-swm3AP8X50VG4jCi.jpg
            //echo 'm-'.substr(str_shuffle($permitted_chars), 0, 16).'.jpg';
            $_SESSION["regadmin"] = substr(str_shuffle($permitted_chars), 0, 4);
            $data=new stdClass();
            $nameError = $emailError = $passWordErrr = $fullnameError=$addressError=$cityError=$countryError=$error="";
            $validate=true;
            require_once (dirname(__FILE__) . "/pages/signup.php");
            
        }
        
    } else {
        require_once (dirname(__FILE__) . "/pages/index.php");
    }
}else {
    if ($_SERVER['REQUEST_METHOD'] == "POST"){
        $redirectUrl="index.php";
        $redirectUrl = str_replace($redirectUrl,"",$_SERVER['PHP_SELF']);
        $redirectUrl = str_replace($redirectUrl,"admin.php","admin");
        //echo $_SESSION["regadmin"];
        
    
        if(!isset($_POST["domain"])){
            if(isset($_POST["username"]) && isset($_POST["password"])){
                $loginResult = login($_POST["username"], $_POST["password"]);
                
                if (!isset($loginResult)){
                    $redirectUrl .="?success=false";
                }
                header("Location: $redirectUrl");
            }
        }else{
            require_once ("reg.php");
        }
        
    }
}


function login($username, $password){
    //require_once(PLUGIN_PATH . "/auth/auth.php");
    $loginResult = Auth::Login($username,$password, $_SERVER["HTTP_HOST"]);

    if (isset($loginResult)){
        $token = $loginResult->token;
    }

    if (isset($token)){
        setcookie("securityToken", $token, time() + (86400 * 30), "/");
        setcookie("authData", json_encode($loginResult), time() + (86400 * 30), "/");
        return true;
    } 

}


?>
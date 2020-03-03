<?php

require_once (dirname(__FILE__) . "/../../configloader.php");

class Auth {
    public static function Login ($username, $password){
        return Auth::getObjectForGetMethod(AUTH_URL . "/login/$username/$password/$_SERVER[HTTP_HOST]");
    }

    public static function SocialLogin ($app, $code,$create){
        return Auth::getObjectForGetMethod(AUTH_URL . "/social/$app/$code/$create");
    }

    public static function GetResetToken ($email){
        return Auth::getObjectForGetMethod(AUTH_URL . "/getresettoken/$email/123");
    }
	
	public static function SaveUser ($user){
        return json_decode(Auth::callRest(AUTH_URL . "/createuser/","POST",$user));
    }

    public static function Join ($domain,$userid,$usergroup){
        return Auth::getObjectForGetMethod(AUTH_URL . "/join/$domain/$userid/$usergroup");
    }

    public static function ResetPassword ($email, $token, $newPassword){       
        return Auth::getObjectForGetMethod(AUTH_URL . "/resetpassword/$email/$token/$newPassword");
    }

    public static function GetSession ($token){
        return Auth::getObjectForGetMethod(AUTH_URL . "/getsession/$token");
    }

    public static function GetDomainAttributes (){
        return Auth::getObjectForGetMethod(AUTH_URL . "/getdomain/$_SERVER[HTTP_HOST]");
    }

    private static function getObjectForGetMethod($url){
        $output = Auth::callRest($url);
        $outObject = json_decode($output);
        return $outObject;
    }

    public static function Autendicate($appname,$appaction,$res){
        
        if(isset($_COOKIE["authData"])){
            return json_decode($_COOKIE["authData"]);
        }else{
            $res->SetError ("Error Authendicating this Action");
            exit();
            //return null;
        }
    }

    public static function CrossDomainAPICall($domain,$url,$method="GET",$data=null){
        $url= $domain.$url;
        //$headerArray =array();
        $securityToken="no Autherized";
        if(isset($_COOKIE["authData"])){
            $s=json_decode($_COOKIE["authData"]);
            $securityToken = $s->token;
        }
        
        return json_decode(Auth::callRest($url,$method,$data,array("Content-Type: application/json", "rhost:".HOST_NAME,"sosskey: $securityToken")));
    }

    public static function AutendicateDomain($tname,$securityToken,$appname,$operation){
        $user =Auth::GetSession($securityToken);
        if(isset($user->userid)){
            return $user;
        }else{
            //$res->SetError ("Error Authendicating this Action");
            return null;
            //return null;
        }
    }

    private static function callRest($url, $method="GET", $postBody = null, $headerArray=null ,$host = null){
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        $securityToken ="Not Authurized";
        if (!isset($host))
            $host = $_SERVER["HTTP_HOST"];
        
        if(isset($_COOKIE["sosskey"]))
            $securityToken = $_COOKIE["sosskey"];
        
         
        
        if (!isset($headerArray))
            $headerArray = array("Content-Type: application/json", "Host: $host","sosskey: $securityToken");

        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headerArray);
        //if()
        //curl_setopt($ch,CURLOP_CON)
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
        if (isset($postBody)){
            //curl_setopt ($ch, CURLOPT_POST, 1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode($postBody));
        }

        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $response  = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

}

?>
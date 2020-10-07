<?php

//require_once (dirname(__FILE__) . "/../../configloader.php");

class Auth {
    public static function Login ($username, $password){
        return Auth::getObjectForGetMethod(AUTH_URL . "/login/$username/$password/".AUTH_DOMAIN);
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

    public static function GetLogout ($token){
        return Auth::getObjectForGetMethod(AUTH_URL . "/logout/$token");
    }

    public static function GetUserGroups (){
        return Auth::getObjectForGetMethod(AUTH_URL . "/usergroups/");
    }

    public static function NewUserGroup ($groupid){
        return Auth::getObjectForGetMethod(AUTH_URL . "/newusergroup/$groupid");
    }

    public static function GetAccess ($groupid,$app,$type=null,$code=null,$ops=null){
        if($ops===""){
            $ops="null";
        }
        if(isset($type) && isset($code) && isset($ops)){
            $token=md5($groupid."-".$app."-".$type."-".$code."-".$ops);
            $result=CacheData::getObjects($token,"sys_access");
            if(isset($result)){
                return $result;
                
            }else{
                $r=Auth::getObjectForGetMethod(AUTH_URL . "/getacess/$groupid/$app/$type/$code/$ops/");
                if(!isset($r->error)){
                    CacheData::setObjects($token,"sys_access",$r);
                }
                return $r;
            }
        }
        else{
            $token=md5($groupid."-".$app);
            $result=CacheData::getObjects($token,"sys_access");
            if(isset($result)){
                return $result;
            }else{
                $r=Auth::getObjectForGetMethod(AUTH_URL . "/getacess/$groupid/$app/");
                if(!isset($r->error)){
                    CacheData::setObjects($token,"sys_access",$r);
                }
                return $r;
            }
        }
            
    }

    

    public static function SetAccess ($uapp){
        return json_decode(Auth::callRest(AUTH_URL . "/setaccess/","POST",$uapp));
    }

    public static function GetDomainAttributes (){
        return Auth::getObjectForGetMethod(AUTH_URL . "/getdomain/".AUTH_DOMAIN);
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
        
        return json_decode(Auth::callRest($url,$method,$data,array("Content-Type: application/json", "rhost:".AUTH_DOMAIN,"sosskey: $securityToken")));
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
        $securityToken ="NotAuthurized";
        if (!isset($host))
            $host = AUTH_DOMAIN;
        
        if(isset($_COOKIE["securityToken"]))
            $securityToken = $_COOKIE["securityToken"];
        
        if(isset($_COOKIE["sosskey"]))
            $securityToken = $_COOKIE["sosskey"];
        $ip=$_SERVER["REMOTE_ADDR"];
        if (!isset($headerArray))
            $headerArray = array("Content-Type: application/json", "Host: $host","sosskey: $securityToken","Cookie: sosskey=$securityToken;","X-Forwarded-For: $ip");
        //echo json_encode($headerArray);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headerArray);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT , $_SERVER['HTTP_USER_AGENT']);
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
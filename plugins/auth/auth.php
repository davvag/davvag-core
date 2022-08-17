<?php

require_once ("iDavvagAuth.php");

class Auth {

    private static  $Auth;
    
    private static function getAuthSvr(){
        if(!empty(self::$Auth)){
           return self::$Auth;
        }else{
            if(isset($GLOBALS["ENGINE_CONFIG"]->DAVVAG_AUTH)){
                //if(isset($GLOBALS["ENGINE_CONFIG"]->DAVVAG_AUTH->{$tenantId})){
                    $lib=$GLOBALS["ENGINE_CONFIG"]->DAVVAG_AUTH->connector;
                    require_once ($lib."/".$lib.".php");
                    self::$Auth= new $lib();
                    return self::$Auth;
            }else{
                require_once ("davvagauth/davvagauth.php");
                self::$Auth= new davvagauth();
            }
        }
    }

    public static function Login ($username, $password){
        $loginData=self::getAuthSvr()->Login($username,$password);
        if(isset($loginData->token)){
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION["authData"]=$loginData;
            setcookie("securityToken", $loginData->token, time() + (86400 * 1), "/");

        }
        return $loginData; 
    }

    public static function SocialLogin ($app, $code,$create){
        return self::getAuthSvr()->SocialLogin($app,$code,$create);
    }

    public static function GetResetToken ($email){
        return self::getAuthSvr()->SocialLogin($email);
    }
	
	public static function SaveUser ($user){
        return self::getAuthSvr()->SaveUser($user);
    }

    public static function NewDomain ($data){
        return self::getAuthSvr()->NewDomain($data);
    }

    public static function Join ($domain,$userid,$usergroup){
        return self::getAuthSvr()->Join($domain,$userid,$usergroup);
    }

    public static function ResetPassword ($email, $token, $newPassword){       
        return self::getAuthSvr()->ResetPassword($email,$token,$newPassword);
    }


    public static function ChangePassword ($oldpassword, $newPassword){       
        return self::getAuthSvr()->ChangePassword($oldpassword,$newPassword);
    }

    public static function GetSession ($token){
        return self::getAuthSvr()->GetSession($token);
    }

    public static function GetLogout ($token){
        $outObject= self::getAuthSvr()->GetLogout($token);
        //$outObject = Auth::GetLogout($authdata->token);
            if(!isset($outObject->error)){
                session_destroy();
                unset($_SESSION["authData"]);
                unset($_COOKIE['securityToken']); 
                setcookie('securityToken', null, -1, '/'); 
                //session_regenerate_id();
                //$outObject = new stdClass();
                return $outObject;
            }else{
                return $outObject;
            }
         
    }

    public static function GetUserGroups (){
        return self::getAuthSvr()->GetUserGroups();
    }

    public static function NewUserGroup ($groupid){
        return self::getAuthSvr()->NewUserGroup($groupid);
    }

    public static function GetAccess ($groupid,$app,$type=null,$code=null,$ops=null){
        if($ops===""){
            $ops="null";
        }
        
        $token=md5($groupid."-".$app."-".$type."-".$code."-".$ops);
        $result=CacheData::getObjects($token,"sys_access");
        if(isset($result)){
            return $result;
                
        }else{
            $r= self::getAuthSvr()->GetAccess($groupid,$app,$type,$code,$ops);
            if(!isset($r->error)){
                CacheData::setObjects($token,"sys_access",$r);
            }
            return $r;
        }
        
            
    }

    

    public static function SetAccess ($uapp){
        return self::getAuthSvr()->SetAccess($uapp);
    }

    public static function GetDomainAttributes (){
        return self::getAuthSvr()->GetDomainAttributes();
    }



    public static function Autendicate(){
        
        if(isset($_SESSION["authData"])){
            return $_SESSION["authData"];
        }else{
            if(isset($_COOKIE["securityToken"])){
                $authObj=self::GetSession($_COOKIE["securityToken"]);
                if(isset($authObj->token)){
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION["authData"]=$authObj;
                    return $_SESSION["authData"];
                }
            }else{
                return null;
            }
            
            return null;
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
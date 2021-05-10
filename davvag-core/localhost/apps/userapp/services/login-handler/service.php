<?php
require_once (PLUGIN_PATH . "/auth/auth.php");
require_once (PLUGIN_PATH . "/phpcache/cache.php");
require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
class LoginService {
    
    function __construct(){
        
    } 

    public function getGoogleLogin($req,$res){
            //App ID
            require_once (PLUGIN_PATH . "/Google/Client.php");
            $gClient = new Google_Client();
            $gClient->setApplicationName('Login to CodexWorld.com');
            $gClient->setClientId(GOOGLE_ID);
            $gClient->setClientSecret(GOOGLE_S);
            $gClient->setRedirectUri('https://'.FBCAPP_DOMAIN.'/components/userapp/login-handler/service/FacebookLoginCallback');

            //$google_oauthV2 = new Google_Oauth2Service($gClient);
            $loginUrl = $gClient->createAuthUrl();
            return $loginUrl;
    }

    public function getLoginState($req,$res){
        //$url = "http://localhost:9000/getsession/$_GET[token]";
        
        if(isset($_COOKIE["authData"])){
            $outObject=json_decode($_COOKIE["authData"]);
            //echo $outObject->userid;
            //return $outObject->token;
            $result = CacheData::getObjects($outObject->token,"sessions");
            if(isset($result)){
                return $result;
            }
            
            if(isset($outObject->email)){
                $result = SOSSData::Query ("profile", urlencode("linkeduserid:".$outObject->userid.""));

                if ($result->success == true){
                    if (sizeof($result->result) > 0){
                        $outObject->profile = $result->result[0];
                    }
                }
                CacheData::setObjects($outObject->token,"sessions",$outObject);
            }
        }else{
            $res->SetError("Session Expired");
        }
        
    }

    public function getProfileData($req,$res){
        //$url = "http://localhost:9000/getsession/$_GET[token]";
        $profile=new stdClass();
        if(isset($_COOKIE["authData"])){
            $outObject=json_decode($_COOKIE["authData"]);
            if(isset($outObject->userid)){
                $result = SOSSData::Query ("profile", urlencode("linkeduserid:".$outObject->userid.""));

                if ($result->success == true){
                    if (sizeof($result->result) > 0){
                        $profile=$result->result[0];
                        $result = SOSSData::Query ("profile_policy", urlencode("id:".$profile->id.""));
                        $profile->profile_policy=sizeof($result->result) > 0?$result->result[0]:null;
                        $result = SOSSData::Query ("profilestatus", urlencode("profileid:".$profile->id.""));
                        $profile->profilestatus=sizeof($result->result) > 0?$result->result[0]:null;
                        $result = SOSSData::Query ("ledger", urlencode("profileid:".$profile->id.""));
                        $profile->ledger=$result->result;
                        $result = SOSSData::Query ("orderheader_pending", urlencode("profileid:".$profile->id.""));
                        $profile->order_pending=$result->result;
                        $result = SOSSData::Query ("orderheader_rejected", urlencode("profileid:".$profile->id.""));
                        $profile->order_rejected=$result->result;
                        $result = SOSSData::Query ("orderheader_rejected", urlencode("profileid:".$profile->id.""));
                        $profile->order_rejected=$result->result;
                        $result = SOSSData::Query ("orderheader_accepted", urlencode("profileid:".$profile->id.""));
                        $profile->orders=$result->result;
                        //$outObject->profile = $result->result[0];
                        return $profile;
                    }else{
                        $res->SetError("Critical Error Profile Not Registered.");
                    }
                }else{
                    $res->SetError($result);
                }
                //CacheData::setObjects($outObject->token,"sessions",$outObject);
            }
        }else{
            $res->SetError("Not Authorized");
        }
        
    }

    public function getFacebookLogin($req,$res){
        //App ID
            require_once (PLUGIN_PATH . "/Facebook/autoload.php");
            $appid="1495359210619523";
            $appsecret="58222a1cf78f04e2073173f480a5b40b";
            //session_start();
            $fb = new Facebook\Facebook([
            'app_id' => FBLAPP_ID, // Replace {app-id} with your app id
            'app_secret' => FBLAPP_S,
            'default_graph_version' => 'v3.2',
            ]);
            $helper = $fb->getRedirectLoginHelper();
            $permissions = ['email']; // Optional permissions
            $loginUrl = $helper->getLoginUrl('https://'.FBCAPP_DOMAIN.'/components/userapp/login-handler/service/FacebookLoginCallback', $permissions);
            //header("Location: $loginUrl");
            return $loginUrl;
    }

    public function getFacebookLoginCallback($req,$res){
        //App ID
        require_once (PLUGIN_PATH . "/Facebook/autoload.php");
        $fb = new Facebook\Facebook([
            'app_id' => FBLAPP_ID, // Replace {app-id} with your app id
            'app_secret' => FBLAPP_S,
            'default_graph_version' => 'v3.2',
            ]);
            $helper = $fb->getRedirectLoginHelper();
            try {
                $accessToken = $helper->getAccessToken();
              } catch(Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
              } catch(Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
              }
              $securityCert =new stdclass();
              if (! isset($accessToken)) {
                if ($helper->getError()) {
                    $res->SetError ($helper->getErrorDescription());
                    return $securityCert; 
                } else {
                  $res->SetError ('Bad request');
                  return $securityCert;
                }
                exit;
              }

              $oAuth2Client = $fb->getOAuth2Client();

              $tokenMetadata = $oAuth2Client->debugToken($accessToken);
              $tokenMetadata->validateAppId(FBLAPP_ID);
              $tokenMetadata->validateExpiration();
              
              if (! $accessToken->isLongLived()) {
                // Exchanges a short-lived access token for a long-lived one
                try {
                  $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                } catch (Facebook\Exceptions\FacebookSDKException $e) {
                    //$res->SetError ("Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n");
                    //return $securityCert;
                    header('Location: '.APPURL.'/login?err=101');
                }
              
                //echo '<h3>Long-lived</h3>';
                //var_dump($accessToken->getValue());
              }
              
              $_SESSION['fb_access_token'] = (string) $accessToken;
              
              
              //$securityCert->AccessToken=$accessToken->getValue();
              $securityCert->AccessToken=$accessToken->getValue();
              $outObject = Auth::SocialLogin("facebook",$accessToken->getValue(),"reg");
              if(isset($outObject->token)){
                $_SESSION["authData"] = json_encode($outObject);
                setcookie("authData", json_encode($outObject), time() + (86400 * 1), "/"); // 86400 = 1 day
                
                require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
                if(isset($outObject->email)){
                    $result = SOSSData::Query ("profile", urlencode("email:".$outObject->email.""));
    
                    if ($result->success == true){
                        if (sizeof($result->result) > 0){
                            $outObject->profile = $result->result[0];
                            CacheData::setObjects($outObject->token,"sessions",$outObject);
                            header('Location: '.APPURL.'/userapp');
                            exit();
                        }else{
                            
                        }
                    }
                }
                
               }
              //if($outObject->userid)
              header('Location: '.APPURL.'/userapp/error');
              exit();
              //header("APPURL");
              //return $outObject;

    }


    public function postRegisterUser($req,$res){
        $bodyReguser= $req->Body(true);
        $user =new stdclass();
        if(isset($bodyReguser->username)){
            $user->username=$bodyReguser->username;
        }else{
            $user->username=$bodyReguser->email;
        }
        $user->email=$bodyReguser->email;
        $user->name=$bodyReguser->name;
        $user->password=$bodyReguser->password;
        $outObject = Auth::SaveUser($user);

        if(isset($outObject->userid)){
            require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
            $bodyReguser->createdate=date_format(new DateTime(), 'm-d-Y H:i:s');
            $bodyReguser->userid=$outObject->userid;
            $bodyReguser->linkeduserid=$outObject->userid;
            $bodyReguser->status="tobeactivated";
            if(!isset($bodyReguser->catogory)){
                $bodyReguser->catogory="User";
            }

            $result = SOSSData::Query ("profile", urlencode("email:".$bodyReguser->email.""));
            if(count($result->result)==0 && $result->success){
                $result = SOSSData::Insert ("profile", $bodyReguser,$tenantId = null);
                $bodyReguser->id= $result->result->generatedId;
                return $bodyReguser;
            }else{
                $profile = $result->result[0];
                $profile->userid=$outObject->userid;
                $profile->linkeduserid=$outObject->userid;
                $result = SOSSData::Update("profile", $profile,$tenantId = null);
                return $bodyReguser;
            }
        }else{
            //$res->SetError ("User Was not registered");
            return $outObject;
        }
        
    }

    public function postupdatePolicy($req,$res){
        $bodypolicy= $req->Body(true);
        $result = SOSSData::Query ("profile_policy", urlencode("id:".$bodypolicy->id.""));
        if( $result->success){
            $r=null;
            if(count($result->result)==0){
                $r=SOSSData::Insert ("profile_policy", $bodypolicy); 
            }else{
                $r= SOSSData::Update ("profile_policy", $bodypolicy);
            }
            if($r->success){
                CacheData::clearObjects("profile_policy");
                return $bodypolicy;
            }else{
                $res->SetError($r);
            }
        }else{
            return $result;
        }
    }

    public function getGetSession($req){
        //$url = "http://localhost:9000/getsession/$_GET[token]";
        $outObject = Auth::GetSession($_GET["token"]);
        $output = json_encode($outObject);
        $_SESSION["authData"] = $output;
        return $outObject;
    }

    public function getLogin($req){
        //$url = "http://localhost:9000/login/$_GET[email]/$_GET[password]/$_GET[domain]";
        //$output = sendRestRequest($url, "GET");
        
        $outObject = Auth::Login($_GET["email"],$_GET["password"]);
        //return $outObject
        $_SESSION["authData"] = json_encode($outObject);
        setcookie("authData", json_encode($outObject), time() + (86400 * 1), "/"); // 86400 = 1 day
        setcookie("securityToken", $outObject->token, time() + (86400 * 1), "/");
        require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
        if(isset($outObject->email)){
            $result = SOSSData::Query ("profile", urlencode("email:".$outObject->email.""));

            if ($result->success == true){
                if (sizeof($result->result) > 0){
                    $outObject->profile = $result->result[0];
                }
            }
            CacheData::setObjects($outObject->token,"sessions",$outObject);
        }

        return $outObject;
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

    public function postChangePassword($req,$res){
        $bodypass= $req->Body(true);
        return Auth::ChangePassword($bodypass->password,$bodypass->newpassword);
    }

    public function getResetToken($req){
        $outObject = Auth::GetResetToken($_GET["email"]);
        return $outObject;
    }

   

    public function getResetPassword($req){
        $outObject = Auth::ResetPassword($_GET["email"],$_GET["token"],$_GET["password"]);
        return $outObject;
    }
}

?>
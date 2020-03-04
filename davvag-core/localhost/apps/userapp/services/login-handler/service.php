<?php
require_once (PLUGIN_PATH . "/auth/auth.php");
require_once (PLUGIN_PATH . "/phpcache/cache.php");

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

    public function getLoginState($req){
        //$url = "http://localhost:9000/getsession/$_GET[token]";
        
        if(isset($_COOKIE["authData"])){
            $outObject=json_decode($_COOKIE["authData"]);
            //echo $outObject->userid;
            //return $outObject->token;
            $result = CacheData::getObjects($outObject->token,"sessions");
            if(isset($result)){
                return $result;
            }
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
        }
        return $outObject;
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
            $result = SOSSData::Insert ("profile", $bodyReguser,$tenantId = null);
            $bodyReguser->id= $result->result->generatedId;
            return $bodyReguser;
        }else{
            //$res->SetError ("User Was not registered");
            return $outObject;
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
        
        $_SESSION["authData"] = json_encode($outObject);
        setcookie("authData", json_encode($outObject), time() + (86400 * 1), "/"); // 86400 = 1 day
        
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
        unset($_SESSION["authData"]);
        session_regenerate_id();
        $outObject = new stdClass();
        return $outObject;
    }

    public function getResetToken($req){
        $outObject = Auth::GetResetToken($_GET["email"]);
        return $outObject;
    }

    private function sendEmail($toEmail, $resetToken){
        require_once(PLUGIN_PATH . "/phpmailer/PHPMailerAutoload.php");

        $mail = new PHPMailer();
        
        $mail->IsSMTP(); // set mailer to use SMTP
        $mail->Host = "smtp.elasticemail.com"; // specify main and backup server
        $mail->SMTPAuth = true; // turn on SMTP authentication
        $mail->Port =2525;
        $mail->Username = "orders@mylunch.lk";
        $mail->Password = "ff06c777-490b-4ff3-8856-320cf3652c1f";
        $mail->SMTPSecure = 'tls';  

        $mail->From = "orders@mylunch.lk";
        $mail->FromName = "Mylunch.lk";
        $mail->Subject  = "Mylunch.lk password reset";
        $mail->IsHTML(true); 
        $mail->addAddress($toEmail, $toEmail);
        
        $url = "https://mylunch.lk/#/resetpassword?email=$toEmail&token=$resetToken";
        /*
        $mail->Host = "103.47.204.4"; // specify main and backup server

        $mail->setFrom('dilshadtheman@gmail.com', 'Mylunch.lk');
        $mail->IsHTML(true); 
        $mail->addAddress($toEmail, $toEmail);
        */
$body = <<<EOT
        <div style="width:500px;font-family:Georgia;overflow:auto;">
            <div style="background:#115FB2;">
                <img src="https://mylunch.lk/assets/mylunch/img/cart.png"/>
            </div>
            <div style="height:100px;clear: both;">
                <h2>You have requested to reset the password. Please use the following link to reset the password</h2>
                <a href="%%URL%%">Reset password</a>
            </div>

        </div>
EOT;
        
        $body = str_replace("%%URL%%",$url,$body);
        $mail->Body = $body;       
        $mail->Send();
    }

    public function getResetPassword($req){
        $outObject = Auth::ResetPassword($_GET["email"],$_GET["token"],$_GET["password"]);
        return $outObject;
    }
}

?>
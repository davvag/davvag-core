<?php

class LoginService {

    function __construct(){
        
    } 

    public function postRegisterUser($req){
        $bodyReguser= $req->Body(true);
        
        return $bodyReguser;
    }

    public function getGetSession($req){
        $url = "http://localhost:9000/getsession/$_GET[token]";
        $output = sendRestRequest($url, "GET");
        $outObject = json_decode($output);
        $_SESSION["authData"] = $output;
        return $outObject;
    }

    public function getLogin($req){
        $url = "http://localhost:9000/login/$_GET[email]/$_GET[password]/$_GET[domain]";
        $output = sendRestRequest($url, "GET");
        $outObject = json_decode($output);
        $_SESSION["authData"] = $output;
        setcookie("authData", $output, time() + (86400 * 1), "/"); // 86400 = 1 day
        
        require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");

        $result = SOSSData::Query ("profile", urlencode("email:".$outObject->email.""));

        if ($result->success == true){
            if (sizeof($result->result) > 0){
                $outObject->profile = $result->result[0];
            }
        }

        return $outObject;
    }

    public function getLogout($req){
        unset($_SESSION["authData"]);
        session_regenerate_id();
        $outObject = new stdClass();
        return $outObject;
    }

    public function getGetResetToken($req){
        $url = "http://localhost:9000/getresettoken/$_GET[email]/123";
        $output = sendRestRequest($url, "GET");
        $outObject = json_decode($output);
        if (isset($outObject)){
            if (isset($outObject->success)){
                if ($outObject->success == true){
                    // $this->sendEmail($_GET["email"],$outObject->message);
                    $outObject->message = "Successfully sent reset mail";
                }
            }else {
                $outObject = new stdClass();
                $outObject->success = false;
                $outObject->message = $output;                
            }
        }else {
            $outObject = new stdClass();
            $outObject->success = false;
            $outObject->message = $output;
        }
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
        $url = "http://localhost:9000/resetpassword/$_GET[email]/$_GET[token]/$_GET[password]";
        $output = sendRestRequest($url, "GET");
        $outObject = json_decode($output);
        return $outObject;
    }
}

?>
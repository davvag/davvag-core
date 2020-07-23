<?php
    require_once(PLUGIN_PATH . "/phpmailer/PHPMailerAutoload.php");
    class Notify{
      private static $emailconfig;
      private static $globals;
      public static function sendEmailMessage($name,$email,$className,$data){
          if(!isset(self::$emailconfig)){
              if(file_exists(TENANT_RESOURCE_LOCATION."/global/config/emailsmtp.conf")){
                //echo "im in";
                  $config=file_get_contents(TENANT_RESOURCE_LOCATION."/global/config/emailsmtp.conf");
                  self::$emailconfig=json_decode($config);
                  self::$globals=file_exists(TENANT_RESOURCE_LOCATION."/global/config/globals.conf")?json_decode(file_get_contents(TENANT_RESOURCE_LOCATION."/global/config/globals.conf")):new stdClass();
                  
              }else{
                  return false;
              }
          }
    
          if(file_exists(TENANT_RESOURCE_LOCATION."/global/templetes/email/".$className.".tmp")){
            //echo "im in";
            $config=file_get_contents(TENANT_RESOURCE_LOCATION."/global/templetes/email/".$className.".tmp");
            foreach (self::$globals as $propertyToSet => $value) {
              $config=str_replace("@".$propertyToSet,$value,$config);
            }
            foreach ($data as $propertyToSet => $value) {
              $config=str_replace("@".$propertyToSet,$value,$config);
            }
    
            $config=str_replace("@name",$name,$config);
            $config=str_replace("@email",$email,$config);
            $subject=self::getValue("subject",$config);
            $body=self::getValue("body",$config);
            $mail = new PHPMailer();
           //echo self::$emailconfig->username."-".self::$emailconfig->host."-".self::$emailconfig->port."-".self::$emailconfig->password."-";
            $mail->IsSMTP(); // set mailer to use SMTP
            $mail->SMTPDebug  = 0;
            $mail->CharSet = 'UTF-8';
            $mail->Host = self::$emailconfig->host; // specify main and backup server
            $mail->SMTPAuth = true; // turn on SMTP authentication
            $mail->Port =self::$emailconfig->port;
            $mail->Username = self::$emailconfig->username;
            $mail->Password = self::$emailconfig->password;
            $mail->SMTPSecure = 'tls';  
    
            $mail->From = self::$emailconfig->username;
            $mail->FromName = self::$emailconfig->companyname;
            $mail->Subject  = $subject;
            $mail->IsHTML(true); 
            $mail->addAddress($email, $name);
            $mail->Body = $body;
            return $mail->Send();
          }
          return false;
      }
    
      private static function getValue($prop,$text){
        $i =strpos($text,"#".$prop."{")+strlen("#".$prop."{");
        $x=strpos($text,"}#".$prop.";");
        if($i>0){
          return substr($text,$i,$x-$i);
        }else{
          return null;
        }
      }
    }
?>
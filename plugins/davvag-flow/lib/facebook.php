<?php
///require_once(PLUGIN_PATH."/davvag-flow/lib/vendor/autoload.php");
class facebook{
    //use A2Design\AIML;
    public function sendMessage($messaging_type,$inputData, $message) {
        //$messageTemlete=
        $message=facebook::replaceKeywords($inputData,$message);
        $m =array("messaging_type"=>$messaging_type,"recipient"=>array("id"=>$inputData->id),"message"=>$message);
        //$m =array("recipient"=>array("id"=>$inputData->id),"message"=>$message);

        $url = "https://graph.facebook.com/v6.0/me/messages?access_token=" . FB_MSG_APP_S;
        $result = facebook::callRest($url, $m, "POST");
        return json_decode($result);
    }

   public static function replaceKeywords($data,$obj){
        $arr =get_object_vars($data);
        $strobj=json_encode($obj);
        foreach ($arr as $k => $v) {
            if(!is_object($v)){
                $strobj=str_replace("@$k",$v,$strobj);
            }
        }
        return json_decode($strobj);
   }
   public static function callRest($url, $jsonObj = null, $method="GET", $headerArray=null){
        $ch = curl_init();
        //$url 
        curl_setopt ($ch, CURLOPT_URL, $url);
        if (!isset($headerArray))
            $headerArray = array("Content-Type: application/json");

        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headerArray);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (isset($jsonObj)){
            //curl_setopt ($ch, CURLOPT_POST, 1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode($jsonObj));
        }

        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $response  = curl_exec($ch);
        if(curl_errno($ch)){
            echo 'Curl error: ' . curl_error($ch);
        }
        //echo $response;
        curl_close($ch);
        return $response;
    }

    
}
?>
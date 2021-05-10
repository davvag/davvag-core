<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");

class FBMessangerPlatform {
    
    function __construct(){
        
    }

    public function postWebhook($req,$res){
        $raw_post_data = $req->Body();
        $header_signature = $req->Headers()->{"X-Hub-Signature"};
        $bodayhook=array("message"=>$raw_post_data);
        CacheData::setObjects(md5($header_signature),"fb_msg_row",$bodayhook);
        if($this->varify($raw_post_data,$header_signature)){
            $messageBody=json_decode($raw_post_data);
            CacheData::setObjects(md5($header_signature),"fb_msg",$messageBody);
            if($messageBody->object=="page"){
                foreach ($messageBody->entry as $value) {
                    $pageid= $value->id;
                    $flowid="1";
                    $node=null;
                    if(isset($value->messaging))
                    foreach ($value->messaging as $msg) {
                        $profile=$this->retriveUser($msg->sender->id,$pageid);
                        if(isset($msg->postback)){
                            list($flowid, $node,$workflowid)=explode(":",$msg->postback->payload);
                            /*if(count($data)==2){

                            }*/
                            //$r=$this->sendButtonMessage($msg->sender->id,"got it ".$node);
                            //return $r;
                        }
                        if(isset($msg->message->text)){
                            $flowid=$this->getFlow($msg->message->text);
                            $profile->message=$msg->message->text;
                        }
                        //return $profile;
                        if($profile){
                            require_once(PLUGIN_PATH . "/davvag-flow/flow.php");
                            try {
                                $x=DavvagFlow::Execute(null,$flowid,$profile,$node);
                                //var_dump($x);
                                //$r=$this->sendButtonMessage($msg->sender->id,$x->outData->replymessage);
                                //var_dump($r);
                                return $x;
                                if(isset($r->message_id)){
                                    $m=new stdClass();
                                    $m->msgid=isset($msg->message->mid)?$msg->message->mid:$r->message_id;
                                    $m->psid=$msg->sender->id;
                                    $m->pageid=$pageid;
                                    $m->message=isset($msg->message->text)?$msg->message->text:"Flow:".$flowid."Node".$node;
                                    $m->replymsgid=$r->message_id;
                                    $result=SOSSData::Insert ("fb_messages", $m,$tenantId = null);
                                }else{
                                    CacheData::setObjects($header_signature,"fb_messages_error",$r);
                                    return $r;
                                }
                                //return $result;
                                return $x;
                            } catch (Exception $e) {
                                //var_dump($profile);
                                return $e->getMessage();
                            }
                            
                           
                        }
                    }
                }
            }

        }else{
            return "Error varifying ";
        }
        return "not processed";
    }

    private function getFlow($keyword){
        
        //return $keys;
        $mainObj = new stdClass();
        $mainObj->parameters = new stdClass();
        $mainObj->parameters->keyword = $keyword;
        //$mainObj->parameters->search = isset($_GET["q"]) ?  $_GET["q"] : "";
        $resultObj = SOSSData::ExecuteRaw("fb_keywords_detail_pod_flow", $mainObj);
        //var_dump($resultObj);
        if($resultObj->success){
            if(count($resultObj->result)>0){
                return $resultObj->result[0]->davvagflow;
            }else{
                return null;
            }
        }
        

    }

    private function retriveUser($psid,$pageid){
       $profile=CacheData::getObjects($psid,"fb_profiles");
       if(!isset($profile)){
            $r = SOSSData::Query ("fb_profiles", urlencode("id:".$psid.""));
            if(count($r->result)!=0){
                $profile=$r->result[0];
                CacheData::setObjects($psid,"c",$profile);
                return $profile;
            }else{
                $url= "https://graph.facebook.com/".$psid."?fields=id,email,first_name,last_name,profile_pic,gender,timezone,name&access_token=".FB_MSG_APP_S."";
                $result = $this->callRest($url);
                //echo $result;
                $profile=json_decode($result);
                if(isset($profile->id)){
                    $profile->pageid=$pageid;
                    $profileDavvag=array("name"=>$profile->name,"gender"=>($profile->gender=="male"?"m":"f"),"organization"=>"facebook","email"=>isset($profile->email)?$profile->email:"notprovided@fb.com","contactno"=>"00000");
                    $result=SOSSData::Insert ("profile", $profileDavvag);
                    
                    //$profile->r=$result;
                    if($result->success){
                        $profile->davvagid=$result->result->generatedId;
                        $this->stripImage($profile->profile_pic,"profile",$profile->davvagid);
                        $result=SOSSData::Insert ("fb_profiles", $profile);
                        CacheData::setObjects($psid,"fb_profiles",$profile);
                    }
                    
                    
                    //$this->stripImage($profile->profile_pic,"fb_profile",$profile->id);
                    
                    return $profile;
                }else{
                    return null;
                }
            }
       }else{
           return $profile;
       }
    }

    private function stripImage($url,$ns,$name){
        $pic=file_get_contents($url);
        $folder = MEDIA_FOLDER . "/".  $_SERVER["HTTP_HOST"] . "/$ns";
        if (!file_exists($folder))
                mkdir($folder, 0777, true);
        file_put_contents("$folder/$name", $pic);
    }

    private function sendMessage($recipient, $textMessage) {
        
         $m =array("messaging_type"=>"RESPONSE","recipient"=>array("id"=>$recipient),"message"=>array("text"=>$textMessage));
         $url = "https://graph.facebook.com/v6.0/me/messages?access_token=" . FB_MSG_APP_S;
         $result = $this->callRest($url, $m, "POST");
         return json_decode($result);
    }

    private function sendButtonMessage($recipient, $textMessage,$buttons=null) {
        $messageButtonbody=array("attachment"=>array("type"=>"template","payload"=>array("template_type"=>"button",
        "text"=>$textMessage,
        "buttons"=>array(array("type"=>"web_url","url"=>"https://www.messenger.com","title"=>"Visit Messenger"),
        array("type"=>"postback","title"=>"POST BACK","payload"=>"1:node3")))));
        $m =array("messaging_type"=>"RESPONSE","recipient"=>array("id"=>$recipient),"message"=>$messageButtonbody);
        //echo json_encode($m);
        $url = "https://graph.facebook.com/v6.0/me/messages?access_token=" . FB_MSG_APP_S;
        $result = $this->callRest($url, $m, "POST");
        return json_decode($result);
   }

    private  function callRest($url, $jsonObj = null, $method="GET", $headerArray=null){
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

    private function varify($raw_post_data,$header_signature){
        $expected_signature = hash_hmac('sha1', $raw_post_data, FB_MSG_APP_S);
        //echo $expected_signature;
        $signature = '';
        if(
            strlen($header_signature) == 45 &&
            substr($header_signature, 0, 5) == 'sha1='
        ) {
        $signature = substr($header_signature, 5);
        }
        if (hash_equals($signature, $expected_signature)) {
            return true;
        }else{
            return true;
        }
    }

    public function getWebhook($req,$res){
         $appsecret = '123';
         
         //var_dump($_GET);
         $mode = $_GET['hub_mode'];
         $token = $_GET["hub_verify_token"];
         $challenge = $_GET["hub_challenge"];


         if (isset($mode) && isset($token)) {
            if ($mode === 'subscribe' && $token === FB_CHALLENGE) {
                echo  $challenge;
                exit();
            }else{
                $res->SetError("Error");
                exit();
            }
         }else{
             $res->SetError("Error");
             exit();
         }
        /*
        $appsecret = 'EAADLGyJrZBTcBALGRs2SLzP3WkhtZBsrLCpMSCYCpUrAjNZCellpch9UR0oxHygc4VCWjopOklNkfTOPE4h4vsKYBxYKAcVJM1ZBHeZAHmpZB32XPUkr8Aj0tWV3GaBdzYUpZCzrCZCyAGsvd16R5L9fYBauKYjWnfIQpR9RMfwg8gZDZD';
        $raw_post_data = file_get_contents('php://input');
        $header_signature = $headers['X-Hub-Signature'];

        // Signature matching
        $expected_signature = hash_hmac('sha1', $raw_post_data, $appsecret);

        $signature = '';
        if(
            strlen($header_signature) == 45 &&
            substr($header_signature, 0, 5) == 'sha1='
        ) {
        $signature = substr($header_signature, 5);
        }
        if (hash_equals($signature, $expected_signature)) {
            echo('SIGNATURE_VERIFIED');
        }*/
    }

}
?>
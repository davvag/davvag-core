<?php 
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");

class facebook{
    
    public function getautherize($req){
        //https://www.facebook.com/dialog/oauth?client_id=2111780905748745&redirect_uri=http://localhost/sossgrid/components/facebooktest/uom-handler/service/callback&auth_type=rerequest&scope=email,user_gender,id
    }
    
    public function getcallback($req){
        $app_id = "2111780905748745";
        $app_secret = "053b8095023ee262447e574e2d3a981f"; 
        $my_url = "http://localhost/sossgrid/components/facebooktest/uom-handler/service/callback";
        $code =$_GET["code"];
        $token_url="https://graph.facebook.com/oauth/access_token?client_id="
      . $app_id . "&redirect_uri=" . urlencode($my_url) 
      . "&client_secret=" . $app_secret 
      . "&code=" . $code . "&display=popup";
       
        $response = file_get_contents($token_url);
        $data = json_decode($response);
        //return $data;
        $graph_url = "https://graph.facebook.com/me?fields=id,email,name,picture"
        . "&access_token=" . $data->access_token;
        $response = file_get_contents($graph_url);
        $decoded_response = json_decode($response);
        return $decoded_response;
        //https://developers.facebook.com/tools/explorer/fields-parser/?query=id%2Cname&dpr=1.25
       
        //$params = null;
        //parse_str($response, $params);
        //$access_token = $params['access_token'];
        //return $access_token;
    }
}
?>
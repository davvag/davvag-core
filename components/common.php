<?php
    $appAccess=null;
    define("BYPASS",true);
    function writeResponse($res, $success, $result){
        $sObj =new stdClass();
        $sObj->success = $success;
        $sObj->result = $result;
        $res->Set($sObj);
    }

    function filterApp($val){

    }

    function checkAccess($res,$appcode,$type=null,$code=null,$operation=null){
        if(isset($type) && isset($code) && isset($operation)){
            if(BYPASS){
                return true;
            }
            $obj=Auth::GetAccess(GROUPID,$appcode,$type,$code,$operation);
            //var_dump($obj);
            if(!isset($obj->error)){
                //echo "sss";
                return true;
            }else{
                return false;
            }
        }else{

            getAuthApplications($appcode);
            //var_dump($GLOBALS["appAccess"]);
            if(!isset($GLOBALS["appAccess"]->{$appcode}->error)){
                if(isset($GLOBALS["appAccess"]->{$appcode})){
                    if(count($GLOBALS["appAccess"]->{$appcode})>0)
                        return true;
                    else
                        return false;
                }else{
                    return false;
                }
            }else{
                return false;
            }

        }
    }

    function getAuthApplications($appcode){
        if(!isset($GLOBALS["appAccess"])){
            $GLOBALS["appAccess"]=new stdClass();
            
        }
        if(!isset($GLOBALS["appAccess"]->{$appcode})){
            $GLOBALS["appAccess"]->{$appcode}=Auth::GetAccess(GROUPID,$appcode);
        }
        return $GLOBALS["appAccess"]->{$appcode};
    }

    function sendRestRequest($url, $method, $body = null, $forwardHeaders = null){
        $ch=curl_init();
        
        //$currentHeaders = apache_request_headers();
        if (isset($forwardHeaders)){
            array_push($forwardHeaders, "Host: $_SERVER[HTTP_HOST]");
            array_push($forwardHeaders, "Content-Type: application/json");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $forwardHeaders);
        }
        /*
        foreach ($currentHeaders as $key => $value)
            if (!(strcmp(strtolower($key), "host") ===0 || strcmp(strtolower($key),"content-type")===0))
                array_push($forwardHeaders, "$key : $value");
        */
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if(isset($body)){            
            curl_setopt($ch, CURLOPT_POST, count($body));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);    
        }

        $data = curl_exec($ch);

        return $data;
    }
?>
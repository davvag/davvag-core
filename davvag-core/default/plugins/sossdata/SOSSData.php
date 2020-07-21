<?php

//require_once (dirname(__FILE__) . "/../../configloader.php");

class SOSSData {

    public static function ExecuteRaw ($className, $saveObj, $lastVersionId = null, $tenantId = null){
        if ($tenantId == null)
            $tenantId = $_SERVER["HTTP_HOST"];

        $wrapper = new stdClass();
        $wrapper->object = $saveObj;
        
        $headerArray = array("Content-Type: application/json", "Host: $tenantId", "executeraw: true");
        $responseStr = SOSSData::callRest ($tenantId, $className, $wrapper, "POST", $headerArray);
        return json_decode($responseStr);
    }

    public static function Insert ($className, $saveObj, $tenantId = null){
        if ($tenantId == null)
            $tenantId = $_SERVER["HTTP_HOST"];

        $wrapper = new stdClass();
        $wrapper->object = $saveObj;

        $responseStr = SOSSData::callRest ($tenantId, $className, $wrapper, "POST");
        //var_dump ($responseStr);
        return json_decode($responseStr);
    }


    public static function Update ($className, $saveObj, $tenantId = null){
        if ($tenantId == null)
            $tenantId = $_SERVER["HTTP_HOST"];

        $wrapper = new stdClass();
        $wrapper->object = $saveObj;

        $responseStr = SOSSData::callRest ($tenantId, $className, $wrapper, "PUT");
        return json_decode($responseStr);
    }

    public static function Delete ($className, $saveObj, $tenantId = null){
        if ($tenantId == null)
            $tenantId = $_SERVER["HTTP_HOST"];
        
        
        if(is_array($saveObj)){
            $responceArray=array();
            foreach($saveObj as $value){
                $wrapper = new stdClass();
                $wrapper->object = $value;
                $responseStr = SOSSData::callRest ($tenantId, $className, $wrapper, "DELETE");
                array_push($responceArray,json_decode($responseStr));
            }
            return $responceArray;
        }else{
            $wrapper = new stdClass();
            $wrapper->object = $saveObj;
            $responseStr = SOSSData::callRest ($tenantId, $className, $wrapper, "DELETE");
            return json_decode($responseStr);
            //return $saveObj;
        }
    }

    public static function Query($className, $query, $lastVersionId = null, $tenantId = null){
        if ($tenantId == null)
            $tenantId = $_SERVER["HTTP_HOST"];

        $className = isset($query) ?  "$className?query=$query" : $className;
        $className = isset($query) ?  "$className&lastversionid=$lastVersionId" : "$className?lastversionid=$lastVersionId";
        $responseStr = SOSSData::callRest ($tenantId, $className);;
        return json_decode($responseStr);
    }

    public static function PostQuery($className, $query, $lastVersionId = null, $tenantId = null){
        if ($tenantId == null)
            $tenantId = $_SERVER["HTTP_HOST"];

        $wrapper = new stdClass();
        $wrapper->queryParams = $query;

        $className = isset($lastVersionId ) ?  "$className?lastversionid=$lastVersionId" : $className;

        $responseStr = SOSSData::callRest ($tenantId, $className, $wrapper, "POST");
        return json_decode($responseStr);
    }

    private static function callRest($host, $className, $jsonObj = null, $method="GET", $headerArray=null){
        $ch = curl_init();
        $url = OS_URL . "/$className";
        curl_setopt ($ch, CURLOPT_URL, $url);
        if (!isset($headerArray))
            $headerArray = array("Content-Type: application/json", "Host: $host");

        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headerArray);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (isset($jsonObj)){
            //curl_setopt ($ch, CURLOPT_POST, 1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode($jsonObj));
        }

        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $response  = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

}

?>
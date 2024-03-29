<?php
    
    $configFolder = getenv("DAVVAGCONFIG");

    if ($configFolder === FALSE)
        $configFolder = dirname(__FILE__);

    // Main Config Loading    
    define ("CONFIG_FILE", $configFolder . "/config.json");
    //echo CONFIG_FILE;
    if (file_exists(CONFIG_FILE)){
        $configData = json_decode(file_get_contents(CONFIG_FILE));
        if (isset($configData)){
            if (isset($configData->variables)){
                foreach ($configData->variables as $key => $value)
                    define($key,$value);
            }
        }
    }else {      
        $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
        header("Location: $protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]pages/install");
        exit();
    }
    $GLOBALS["ENGINE_CONFIG"] = $configData;
    //Domain Config loading
    define ("DOMAIN_CONFIG_FILE", $configFolder .'//davvag-core//'.$_SERVER["HTTP_HOST"]."//config.json");
    if (file_exists(DOMAIN_CONFIG_FILE)){
        $configData2 = json_decode(file_get_contents(DOMAIN_CONFIG_FILE));
        if (isset($configData2)){
            if (isset($configData2->variables)){
                foreach ($configData2->variables as $key => $value){
                    if(!defined($key))
                        define($key,$value);
                }
            }
        }
        $GLOBALS["ENGINE_CONFIG_LOCAL"] = $configData2;
    }else{
        //define ("LOCAL_DEV_HOST", "default");
        //exit;
    }
    
    
    if (!isset($configData))
        $configData = new stdClass();
    
    
    
    
    if (defined("LOCAL_DEV_HOST")){
        define ("HOST_NAME", LOCAL_DEV_HOST);
    }else {
        define ("HOST_NAME", $_SERVER["HTTP_HOST"]);
    }
    
    if (!defined("DATASTORE_DOMAIN")){
        define ("DATASTORE_DOMAIN", $_SERVER["HTTP_HOST"]);
    }

    if (!defined("AUTH_DOMAIN")){
        define ("AUTH_DOMAIN", $_SERVER["HTTP_HOST"]);
    }
    define ("TENANT_RESOURCE_LOCATION", RESOURCE_LOCATION . "/" . HOST_NAME);
    define ("TENANT_RESOURCE_LOCATION_APPS", RESOURCE_LOCATION . "/" . HOST_NAME."/apps");
    define ("BASE_PATH", dirname(__FILE__));
    define ("COMPONENT_PATH", dirname(__FILE__) . "/components");
    define ("PLUGIN_PATH", dirname(__FILE__) ."/plugins");
    define ("PLUGIN_PATH_LOCAL", TENANT_RESOURCE_LOCATION . "/plugins");
    define ("SCHEMA_PATH", TENANT_RESOURCE_LOCATION . "/schemas");
    if(isset($configData->DAVVAG_StartUp)){
        foreach($configData->DAVVAG_StartUp->plugins as $value){
            try{
                if($value->plugin_location=="global"){
                    if(file_exists(PLUGIN_PATH . $value->location)){
                        require_once(PLUGIN_PATH."/". $value->location);
                    }else{
                        echo "Error Loading Global Plugin ".PLUGIN_PATH . $value->location;
                    }
                }else{
                    if(file_exists(PLUGIN_PATH_LOCAL . $value->location)){
                        require_once(PLUGIN_PATH_LOCAL."/". $value->location);
                    }else{
                        echo "Error Loading Global Plugin ".PLUGIN_PATH . $value->location;
                    }
                }
            }catch(Exception $e){

            }
        }
    }
    //$GLOBALS["DBConfig"]=
    if($GLOBALS["ENGINE_CONFIG"]->DEBUG===true){
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
?>
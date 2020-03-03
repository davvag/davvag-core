<?php
    
    $configFolder = getenv("SOSSCONFIGFOLDER");

    if ($configFolder === FALSE)
        $configFolder = dirname(__FILE__);

    // Main Config Loading    
    define ("CONFIG_FILE", $configFolder . "/config.json");

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

    //Domain Config loading
    define ("DOMAIN_CONFIG_FILE", $configFolder .'//davvag-core//'.$_SERVER["HTTP_HOST"]."//config.json");
    if (file_exists(DOMAIN_CONFIG_FILE)){
        $configData = json_decode(file_get_contents(DOMAIN_CONFIG_FILE));
        if (isset($configData)){
            if (isset($configData->variables)){
                foreach ($configData->variables as $key => $value)
                    define($key,$value);
            }
        }
    }
    

    if (!isset($configData))
        $configData = new stdClass();
        
    $GLOBALS["ENGINE_CONFIG"] = $configData;

    
    if (defined("LOCAL_DEV_HOST")){
        define ("HOST_NAME", LOCAL_DEV_HOST);
    }else {
        define ("HOST_NAME", $_SERVER["HTTP_HOST"]);
    }
    
    define ("TENANT_RESOURCE_LOCATION", RESOURCE_LOCATION . "/" . HOST_NAME);
    
    define ("BASE_PATH", dirname(__FILE__));
    define ("COMPONENT_PATH", dirname(__FILE__) . "/components");
    define ("PLUGIN_PATH", dirname(__FILE__) . "/davvag-core/".HOST_NAME."/plugins");
    define ("SCHEMA_PATH", TENANT_RESOURCE_LOCATION . "/schemas");
?>
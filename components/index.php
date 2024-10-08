<?php
    //ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    require_once ("../configloader.php");
    require_once (dirname(__FILE__) . "/common.php");
    require_once (dirname(__FILE__) . "/resources.php");
    require_once (dirname(__FILE__) . "/carbite.php");
    require_once (dirname(__FILE__) . "/component_manager.php");
    require_once (dirname(__FILE__) . "/virtual_firewall.php");
    require_once (PLUGIN_PATH . "/auth/auth.php");
    require_once (PLUGIN_PATH . "/phpcache/cache.php");
    require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
    
    $componentManager = new ComponentManager();
    $virtualFirewall = new VirtualFirewall();
    $groupid="anonymous";
    $user=Auth::Autendicate();
    if(isset($user)){
        if(isset($user->group)){
            $groupid=$user->group;
        }
    }
    define("GROUPID",$groupid);
    
    Carbite::GET("/object/tenantdescriptor", [$componentManager,"GetTenantDescriptor"]);
    Carbite::GET("/object/apps", [$componentManager,"GetAllApps"]);
    Carbite::GET("/object/appicon/@appCode", [$componentManager,"GetAppIcon"]);
    Carbite::GET("/object/appdescriptor/@appCode", [$componentManager,"GetAppDescriptor"]);

    Carbite::GET("/@appCode/@componentName/file/*filePath", [$componentManager,"HandleFile"], [[$virtualFirewall,"CheckAuthentication"]]);
    
    Carbite::GET("/@appCode/@componentName/service/@handlerName", [$componentManager,"HandleService"], [[$virtualFirewall,"CheckAuthentication"]]);
    Carbite::GET("/@appCode/@componentName/service/@handlerName/*route", [$componentManager,"HandleService"], [[$virtualFirewall,"CheckAuthentication"]]);
    Carbite::POST("/@appCode/@componentName/service/@handlerName", [$componentManager,"HandleService"], [[$virtualFirewall,"CheckAuthentication"]]);
    Carbite::POST("/@appCode/@componentName/service/@handlerName/*route", [$componentManager,"HandleService"], [[$virtualFirewall,"CheckAuthentication"]]);

    Carbite::GET("/@appCode/@componentName/object", [$componentManager,"HandleComponent"], [[$virtualFirewall,"CheckAuthentication"]]);
    Carbite::GET("/@appCode/@componentName/transform/*route", [$componentManager,"HandleTransformer"], [[$virtualFirewall,"CheckAuthentication"]]);
    Carbite::POST("/@appCode/@componentName/transform/*route", [$componentManager,"HandleTransformer"], [[$virtualFirewall,"CheckAuthentication"]]);    
    Carbite::Start();
?>
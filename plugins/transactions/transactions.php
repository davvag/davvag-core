<?php

require_once (__DIR__. "/transaction_helpers.php");
require_once (__DIR__. "/transaction_builder.php");

class TransactionManager {

    private static $activities;

    public static function Create($initialObj = null, $settings = null, $dataBag = null){
        
        if (!isset($settings))
            $settings = new stdClass();

        if (!isset($dataBag))
            $dataBag = new stdClass();

        $builder = new TransactionBuilder(self::$activities, $dataBag, $initialObj, $settings);
        return $builder;
    }

    public static function RegisterActivity($cls, $name){
        self::$activities->$name = $cls;
    }

    public static function Initialize(){
        self::$activities = new stdClass();
        
        $baseDir = __DIR__ . "/activities";

        $files = scandir($baseDir);
        for ($i=0;$i< sizeof($files);$i++)
        {
            $file = $baseDir . "/" . $files[$i];
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (strtolower($ext) === "php"){
                require_once ($file);
            }
        }
    }
}

TransactionManager::Initialize();

?>
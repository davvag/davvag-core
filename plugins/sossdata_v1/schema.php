<?php
    class Schema{
        private static $Schema=array();

        public static function Get($name){
            if(isset(self::$Schema[$name])){
                return self::$Schema[$name];
            }else{
                if (file_exists(TENANT_RESOURCE_LOCATION."/schemas/".$name.".json")){
                    $s = json_decode(file_get_contents(TENANT_RESOURCE_LOCATION."/schemas/".$name.".json"));
                    self::$Schema[$name]=$s;
                }
            }
        }
    }
?>
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
                    return $s;
                }else{
                    throw new Exception("No Schema File Found.");
                }
            }
        }

        public static function GetSystemColums(){
            $fields=array();
            $key1=new stdClass();
            $key1->fieldName="sysversionid";
            $key1->dataType="long";
            $key1->annotations=new stdClass();
            $key1->annotations->isPrimary=false;
            array_push($fields,$key1);
            $key2=new stdClass();
            $key2->fieldName="syscreated";
            $key2->dataType="int";
            array_push($fields,$key2);
            $key2=new stdClass();
            $key2->fieldName="sysupdated";
            $key2->dataType="int";
            array_push($fields,$key2);
            return $fields;
        }
    }
?>
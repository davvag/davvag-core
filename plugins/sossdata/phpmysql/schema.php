<?php
    class Schema{
        private static $Schema=array();

        public static function Get($name){
            if(isset(self::$Schema[$name])){
                return clone(self::$Schema[$name]);
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
            $key3=new stdClass();
            $key3->fieldName="sysviewobject";
            $key3->dataType="int";
            $key3->annotations=new stdClass();
            $key3->annotations->default="0";
           
            array_push($fields,$key3);
            $key4=new stdClass();
            $key4->fieldName="syscreatedby";
            $key4->dataType="string";
            $key4->annotations=new stdClass();
            $key4->annotations->isPrimary=false;
            $key4->annotations->maxLen=100;
            array_push($fields,$key4);
            $key5=new stdClass();
            $key5->fieldName="syslastupdatedby";
            $key5->dataType="string";
            $key5->annotations=new stdClass();
            $key5->annotations->isPrimary=false;
            $key5->annotations->maxLen=100;
            array_push($fields,$key5);
            return $fields;
        }
    }
?>
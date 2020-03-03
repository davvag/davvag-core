<?php

class TransactionHelpers {
    
    private static function getBaseObject ($stuff, $str){
        switch (strtoupper($str)){
            case "@OBJ":
                return $stuff->object;
            case "@BAG":
                return $stuff->bag;
            case "@SETTINGS":
                return $stuff->settings;
            case "@ITEM":
                return $stuff->currentItem;
        }
    }

    private static function setBaseObject ($stuff, $str, $value){
        switch (strtoupper($str)){
            case "@OBJ":
                $stuff->object = $value;
                break;
            case "@BAG":
                $stuff->bag = $value;
                break;
            case "@SETTINGS":
                $stuff->settings = $value;
                break;
            case "@ITEM":
                $stuff->currentItem = $value;
                break;
        }
    }

    private static function manipulateObjectValue($stuff, $fullPath, $setValue = null, $canDelete = false){
        $pathParts = explode(".", $fullPath);
        $baseObj;
        $currentObj;
        $prevObj;
        $path;
        $isFound = true;

        for($i=0;$i<sizeof($pathParts);$i++){
            $path = trim($pathParts[$i]);          
            if (strlen($path) > 0){
                
                if ($path[0] === "@")
                {
                    $baseObj = self::getBaseObject($stuff, $path);
                    $currentObj = $baseObj;                
                }else {
                    if (isset ($baseObj)){
                        if (isset($currentObj->$path)){
                            $prevObj = $currentObj;
                            $currentObj = $currentObj->$path;
                        }
                        else {
                            $isFound = false;
                            break;
                        }
                    }
                }
            }
        }

        if (isset($setValue)){
            if ($isFound === true){
                if (sizeof($pathParts) == 1)
                    self::setBaseObject($stuff, $path, $setValue);
                else
                    $currentObj->$path = $setValue;
            }
            else {
                $lastPath = trim($pathParts[sizeof($pathParts) - 1]);
                if ($lastPath === $path)
                    $currentObj->$path = $setValue;
            }
        }
        else {
            if ($isFound === true){
                $outObj = $currentObj; 
                if ($canDelete === true && isset($prevObj))
                    unset($prevObj->$path);

                return $outObj;
            }
        }    
            
    }
    public static function GetObjectValue($stuff, $path){
        
        if (isset($path))
        if (strlen($path) > 0)
        {
            $canDelete = false;
            $sPath; 
            if ($path[0] === "-"){
                $canDelete = true;
                $sPath = substr($path,1);
            }else
                $sPath = $path;

            return self::manipulateObjectValue($stuff, $sPath, null, $canDelete);
        }

        return null;
    }

    public static function SetObjectValue($stuff, $path, $value){
        if (isset($path))
        if (strlen($path) > 0)
        {
            $sPath;
            switch ($path[0]){
                /*
                case "&":
                    break;
                case "+":
                    break;
                */
                default:
                    $sPath = $path;
                    break;
            }

            self::manipulateObjectValue($stuff, $sPath, $value);
        }
    }

    public static function ExtractExpression($stuff, $str){
        //"inventory.qty=#.qty/inventory.productid=#.id"
        $lhs = new stdClass();
        $rhs = new stdClass();

        $lhsRhs = explode("/", $str);

        $lhsParts = explode("=", $lhsRhs[0]);
        $rhsParts = explode("=", $lhsRhs[1]);

        $lhs_lhs_parts =  explode ("->", $lhsParts[0]);
        $lhs_rhs_parts =  explode ("->", $lhsParts[1]);

        $rhs_lhs_parts =  explode ("->", $rhsParts[0]);
        $rhs_rhs_parts =  explode ("->", $rhsParts[1]);

        $lhs->class = $lhs_lhs_parts[0];
        $lhs->classField = $lhs_lhs_parts[1];
        $lhs->objectField = $lhs_rhs_parts[1];
        
        $rhs->class = $rhs_lhs_parts[0];
        $rhs->classField = $rhs_lhs_parts[1];
        $rhs->objectField = $rhs_rhs_parts[1];

        $expression = new stdClass();
        $expression->lhs = $lhs;
        $expression->rhs = $rhs;

        return $expression;
    }

    public static function GetFirstObjectFromOs($obj){
        if (isset($obj))
        if (isset($obj->success))
        if ($obj->success === true)
        if (isset($obj->result))
        if (is_array($obj->result))
        if (sizeof($obj->result) > 0){
            return $obj->result[0];
        }
    }

    public static function GetResponseFromOs($obj){
        if (isset($obj))
        if (isset($obj->success))
        if ($obj->success === true)
        if (isset($obj->result)){
            return $obj->result;
        }
        
    }

    public static function ApplyValuesForTemplateObject($stuff, $obj, $templateObj){

        $templateObj = clone $templateObj;

        $stuff->currentItem = $obj;
        foreach($templateObj as $key => $value)
        if (isset($value))
        if (is_string($value))
        if (strlen($value) >0)
        if ($value[0] === "@"){
            $templateObj->$key = self::GetObjectValue($stuff, $value);
        }
        unset ($stuff->currentItem);
        return $templateObj;
    }

    public static function GetItemValue($stuff, $path, $item){
        $outObj;
        $stuff->currentItem = $item;
        $outObj = self::GetObjectValue($stuff, $path);
        unset ($stuff->currentItem);
        
        if (isset($outObj))
            return $outObj;
    }

    public static function SetItemValue($stuff, $path, $item, $value){
        $outObj;
        $stuff->currentItem = $item;
        $outObj = self::SetObjectValue($stuff, $path, $value);
        unset ($stuff->currentItem);
    }
}

?>
<?php
    class TRAN_OS_ITERATE_JOIN {
        
        function __construct(){
            require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
        }

        public function Process($stuff, $arrPath, $expression, $bindArray=false){
            
            $arr = TransactionHelpers::GetObjectValue($stuff, $arrPath);
            $expr = TransactionHelpers::ExtractExpression($stuff, $expression);
            
            $objField = $expr->lhs->objectField;

            $className = $expr->rhs->class;
            $whereObjField = $expr->rhs->objectField;
            $whereClassField = $expr->rhs->classField;

            for($i=0;$i<sizeof($arr);$i++){
                $item = $arr[$i];
                $oValue = TransactionHelpers::GetItemValue($stuff, "@ITEM.$whereObjField", $item);
                $response = SOSSData::Query($className, "$whereClassField:$oValue");
                $filteredValue = ($bindArray === true) ? TransactionHelpers::GetResponseFromOs($response) : TransactionHelpers::GetFirstObjectFromOs($response);
                TransactionHelpers::SetItemValue($stuff, "@ITEM.$objField", $item, $filteredValue );
            }

            return true;
        }

        public function Rollback(){

        }
    }

    TransactionManager::RegisterActivity ("TRAN_OS_ITERATE_JOIN", "IterateAndJoin");
?>
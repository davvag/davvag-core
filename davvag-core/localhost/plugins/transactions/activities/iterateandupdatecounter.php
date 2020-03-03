<?php
    class TRAN_ITERATE_UPDATE_COUNTER {
        public function Process($stuff, $path, $expression, $strategy, $templateObject, $minValue = 0){

            $items = TransactionHelpers::GetObjectValue($stuff, $path);

            if (is_array($items)){

                $expr = TransactionHelpers::ExtractExpression($stuff, $expression);
                
                $updClass = $expr->lhs->class;

                $objField = $expr->lhs->objectField;
                $updField = $expr->lhs->classField;
                
                $whereObjField = "@ITEM." . $expr->rhs->objectField;
                $whereClassField = $expr->rhs->classField;

                for ($i=0;$i<sizeof($items);$i++){
                    $isInsertMode = false;
                    
                    if (strlen($whereObjField) >0)
                    if ($whereObjField[0] === "@")
                        $whereObjField = TransactionHelpers::GetItemValue($stuff,$whereObjField, $items[$i]);

                    $result = SOSSData::Query ("$updClass", "$whereClassField:$whereObjField");
                    
                    $counterInDbObj = TransactionHelpers::GetFirstObjectFromOs($result);

                    if (!isset($counterInDbObj)){
                        $counterInDbObj = TransactionHelpers::ApplyValuesForTemplateObject($stuff,$items[$i], $templateObject);
                        $isInsertMode = true;
                    }

                    if (trim($strategy) === "+"){
                        $counterInDbObj->$updField += ($items[$i]->$objField);
                    }else {
                        if (isset($minValue)){
                            $counterInDbObj->$updField -= ($items[$i]->$objField);
                            if ($counterInDbObj->$updField < $minValue)
                                $counterInDbObj->$updField = 0;
                        }
                        else
                            $counterInDbObj->$updField -= ($items[$i]->$objField);
                    }


                    if ($isInsertMode)
                        SOSSData::Insert($updClass, $counterInDbObj);
                    else
                        SOSSData::Update($updClass, $counterInDbObj);
                }
            }

            return true;
        }

        public function Rollback(){

        }
    }

    TransactionManager::RegisterActivity ("TRAN_ITERATE_UPDATE_COUNTER", "IterateAndUpdateCounter");
?>
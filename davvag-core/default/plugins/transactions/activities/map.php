<?php
    class TRAN_MAP {
        
        public function Process($stuff, $itemPath, $mapArr){
            $items = TransactionHelpers::GetObjectValue($stuff, $itemPath);
            if (isset($items))
            {
                $itemArr = is_array($items) ? $items : array($items);
                
                for ($i=0;$i<sizeof($itemArr);$i++){
                    $item = $itemArr[$i];

                    foreach ($mapArr as $mKey => $mValue) {
                        $lValue = TransactionHelpers::GetItemValue($stuff, $mKey, $item);
                        if (isset($lValue))
                            TransactionHelpers::SetItemValue($stuff, $mValue, $item, $lValue);
                    }
                }
            }
        }

        public function Rollback(){

        }
    }

    TransactionManager::RegisterActivity ("TRAN_MAP", "Map");
?>
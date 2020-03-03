<?php
    class TRAN_COMPOSE {
        public function Process($stuff, $settings){
            
            foreach ($settings as $src => $dest) {

                $setVal = TransactionHelpers::GetObjectValue($stuff, $src);
                
                if (isset($setVal)){
                    TransactionHelpers::SetObjectValue($stuff, $dest, $setVal);
                }
            }

            return true;
        }

        public function Rollback(){

        }
    }

    TransactionManager::RegisterActivity ("TRAN_COMPOSE", "Compose");
?>
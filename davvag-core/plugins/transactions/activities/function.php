<?php
    class TRAN_FUNCTION {
        public function Process(){
            return true;
        }

        public function Rollback(){

        }
    }

    TransactionManager::RegisterActivity ("TRAN_FUNCTION", "Function");
?>
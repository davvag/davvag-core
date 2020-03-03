<?php
    class TRAN_OS_RETRIEVE {
        
        function __construct(){
            require_once (PLUGIN_PATH . "/sossdata/SOSSData.php");
        }

        public function Process($stuff, $className, $query, $bindPath, $single=false){
            
            $response = SOSSData::Query($className, $query);
            if ($single === true)
                $response = TransactionHelpers::GetFirstObjectFromOs($response);
            else
                $response = TransactionHelpers::GetResponseFromOs($response);
            
            TransactionHelpers::SetObjectValue($stuff, $bindPath, $response);

            return true;
        }

        public function Rollback(){

        }
    }

    TransactionManager::RegisterActivity ("TRAN_OS_RETRIEVE", "Get");
?>
<?php
    
    require_once("DAVVAG_Data.php");
    class davvagstore implements iDataStore{
      private $data=array();

     
      public function ExecuteRaw($className, $saveObj, $lastVersionId = null, $tenantId = null)
      {
        return DAVVAG_Data::ExecuteRaw($className, $saveObj, $lastVersionId , $tenantId );

      }
      public function Insert($className, $saveObj, $tenantId = null)
      {
        return DAVVAG_Data::Insert($className, $saveObj, $tenantId);
      }

      public function Update($className, $saveObj, $tenantId = null)
      {
        return DAVVAG_Data::Update($className, $saveObj, $tenantId);
      }

      public function Delete($className, $saveObj, $tenantId = null)
      {
        return DAVVAG_Data::Delete($className, $saveObj, $tenantId);
      }

      public function Query($className, $query, $lastVersionId = null, $sorting = "asc",$pageSize=20,$fromPage=0, $tenantId = null)
      {
        return DAVVAG_Data::Query($className, $query, $lastVersionId, $sorting,$pageSize,$fromPage, $tenantId);
        
      }

      public function Close($tenantId)
      {
        //$this->getCon($tenantId)->Close();
      }
    }
?>
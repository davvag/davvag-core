<?php
    //require_once("../DataStore.php");
    require_once("mysqlConnector.php");
    class phpmysql implements iDataStore{
      private $data=array();

      private function getCon($tenantId){
        if(!empty($this->data[$tenantId])){
            return $this->data[$tenantId];
            
        }else{
            $this->data[$tenantId]=new mysqlConnector();
            $this->data[$tenantId]->Open($tenantId);
            return $this->data[$tenantId];
        }
      }
      public function ExecuteRaw($className, $saveObj, $lastVersionId = null, $tenantId = null)
      {
        return $this->getCon($tenantId)->ExecuteRaw($className,$saveObj);

      }
      public function Insert($className, $saveObj, $tenantId = null)
      {
        return $this->getCon($tenantId)->Insert($className,$saveObj);
      }

      public function Update($className, $saveObj, $tenantId = null)
      {
        return $this->getCon($tenantId)->Update($className,$saveObj);
      }

      public function Delete($className, $saveObj, $tenantId = null)
      {
        return $this->getCon($tenantId)->Delete($className,$saveObj);
      }

      public function Query($className, $query, $lastVersionId = null, $sorting = "asc",$pageSize=20,$fromPage=0, $tenantId = null,$viewObject=true)
      {
        return $this->getCon($tenantId)->Query($className,$query,$lastVersionId,$sorting,$pageSize,$fromPage,$viewObject);
        
      }

      public function SetViewObject($objectID=0,$tenantId=null){
        return $this->getCon($tenantId)->SetViewObject($objectID);
      }

      public function Close($tenantId=null)
      {
        $this->getCon($tenantId)->Close();
      }
    }
?>
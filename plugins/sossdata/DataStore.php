<?php
interface iDataStore {
    public function ExecuteRaw ($className, $saveObj, $lastVersionId = null, $tenantId = null);
    public function Insert($className, $saveObj, $tenantId = null);
    public function Update ($className, $saveObj, $tenantId = null);
    public function Delete ($className, $saveObj, $tenantId = null);
    public function Query($className, $query, $lastVersionId = null,$sorting="asc",$pageSize=20,$fromPage=0, $tenantId = null);
    public function SetViewObject($objectID=0,$tenantId);
    public function Close($tenantId);
}
?>
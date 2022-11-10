<?php

require_once ("DataStore.php");

class SOSSData {

    private static  $DavvagData=array();
    
    private static function getDataSource($tenantId){
        if(!empty(self::$DavvagData[$tenantId])){
           return self::$DavvagData[$tenantId];
        }else{
            if(isset($GLOBALS["ENGINE_CONFIG"]->DAVVAG_DATA)){
                if(isset($GLOBALS["ENGINE_CONFIG"]->DAVVAG_DATA->{$tenantId})){
                    $lib=$GLOBALS["ENGINE_CONFIG"]->DAVVAG_DATA->{$tenantId}->connector;
                    require_once ($lib."/".$lib.".php");
                    self::$DavvagData[$tenantId]= new $lib();
                    return self::$DavvagData[$tenantId];
                }else{
                    require_once ("davvagstore/davvagstore.php");
                    self::$DavvagData[$tenantId]=new davvagstore(); 
                }
            }else{
                require_once ("davvagstore/davvagstore.php");
                self::$DavvagData[$tenantId]= new davvagstore();
            }
        }
    }

    public static function ExecuteRaw ($className, $saveObj, $lastVersionId = null, $tenantId = null){
        if ($tenantId == null)
            $tenantId = DATASTORE_DOMAIN;

        return self::getDataSource($tenantId)->ExecuteRaw($className, $saveObj, $lastVersionId, $tenantId);
    }

    public static function Insert ($className, $saveObj, $tenantId = null){
        if ($tenantId == null)
            $tenantId = DATASTORE_DOMAIN;

        return self::getDataSource($tenantId)->Insert($className, $saveObj, $tenantId);
    }


    public static function Update ($className, $saveObj, $tenantId = null){
        if ($tenantId == null)
            $tenantId = DATASTORE_DOMAIN;

        return self::getDataSource($tenantId)->Update($className, $saveObj, $tenantId);
    }

    public static function Delete ($className, $saveObj, $tenantId = null){
        if ($tenantId == null)
            $tenantId = DATASTORE_DOMAIN;
        
        
        return self::getDataSource($tenantId)->Delete($className, $saveObj, $tenantId);
    }

    public static function Query($className, $query, $lastVersionId = null,$sorting="asc",$pageSize=20,$fromPage=0, $tenantId = null,$viewObject=true){
        if ($tenantId == null)
            $tenantId = DATASTORE_DOMAIN;

        return self::getDataSource($tenantId)->Query($className, $query, $lastVersionId,$sorting,$pageSize,$fromPage, $tenantId,$viewObject);
    }

    public static function Close( $tenantId = null){
        if ($tenantId == null)
            $tenantId = DATASTORE_DOMAIN;

        return self::getDataSource($tenantId)->Close($tenantId);
    }

    public static function SetViewObject( $userIds=null,$userGroups=null,$viewObjects=null,$tenantId=null){
        if ($tenantId == null)
            $tenantId = DATASTORE_DOMAIN;
        
        $viewObject=0;
        return self::getDataSource($tenantId)->SetViewObject($viewObject,$tenantId);
    }

}

?>
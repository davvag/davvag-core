<?php

require_once ("DataStore.php");
require_once ("SOSSDataQueryFirewall.php");

class SOSSData {

    private static  $DavvagData=array();
    private static $serviceNamespaces = array();

    /** Server-side capability scope for schemas marked serviceOnly. Never expose through generic CRUD. */
    public static function WithServiceNamespaces($namespaces, $callback) {
        $previous = self::$serviceNamespaces;
        try {
            foreach ($namespaces as $namespace) {
                $namespace = SOSSDataQueryFirewall::validateNamespace($namespace);
                self::$serviceNamespaces[$namespace] = true;
            }
            return $callback();
        }
        finally { self::$serviceNamespaces = $previous; }
    }

    private static function assertNamespaceAccess($namespace) {
        if (!defined('SCHEMA_PATH')) return;
        $path = SCHEMA_PATH . '/' . $namespace . '.json';
        if (!is_file($path)) return;
        $schema = json_decode(file_get_contents($path));
        if ($schema && !empty($schema->serviceOnly) && empty(self::$serviceNamespaces[$namespace])) {
            throw new RuntimeException('This namespace is available only through its authorized application service.');
        }
    }
    
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

        try {
            $className = SOSSDataQueryFirewall::validateNamespace($className);
            self::assertNamespaceAccess($className);
            SOSSDataQueryFirewall::validateRawRequest($saveObj);
        } catch (Throwable $e) {
            return SOSSDataQueryFirewall::blockedResult($e);
        }

        return self::getDataSource($tenantId)->ExecuteRaw($className, $saveObj, $lastVersionId, $tenantId);
    }

    public static function Insert ($className, $saveObj, $tenantId = null){
        if ($tenantId == null)
            $tenantId = DATASTORE_DOMAIN;

        try {
            $className = SOSSDataQueryFirewall::validateNamespace($className);
            self::assertNamespaceAccess($className);
        } catch (Throwable $e) {
            return SOSSDataQueryFirewall::blockedResult($e);
        }

        return self::getDataSource($tenantId)->Insert($className, $saveObj, $tenantId);
    }


    public static function Update ($className, $saveObj, $tenantId = null){
        if ($tenantId == null)
            $tenantId = DATASTORE_DOMAIN;

        try {
            $className = SOSSDataQueryFirewall::validateNamespace($className);
            self::assertNamespaceAccess($className);
        } catch (Throwable $e) {
            return SOSSDataQueryFirewall::blockedResult($e);
        }

        return self::getDataSource($tenantId)->Update($className, $saveObj, $tenantId);
    }

    public static function Delete ($className, $saveObj, $tenantId = null){
        if ($tenantId == null)
            $tenantId = DATASTORE_DOMAIN;

        try {
            $className = SOSSDataQueryFirewall::validateNamespace($className);
            self::assertNamespaceAccess($className);
        } catch (Throwable $e) {
            return SOSSDataQueryFirewall::blockedResult($e);
        }
        
        return self::getDataSource($tenantId)->Delete($className, $saveObj, $tenantId);
    }

    public static function Query($className, $query, $lastVersionId = null,$sorting="DESC",$pageSize=1000,$fromPage=0, $tenantId = null,$viewObject=true){
        if ($tenantId == null)
            $tenantId = DATASTORE_DOMAIN;

        try {
            $className = SOSSDataQueryFirewall::validateNamespace($className);
            self::assertNamespaceAccess($className);
            $query = SOSSDataQueryFirewall::validateQuery($query);
            $lastVersionId = SOSSDataQueryFirewall::normalizeLastVersionId($lastVersionId);
            $sorting = SOSSDataQueryFirewall::normalizeDirection($sorting);
            $pageSize = SOSSDataQueryFirewall::normalizePageSize($pageSize);
            $fromPage = SOSSDataQueryFirewall::normalizeOffset($fromPage);
        } catch (Throwable $e) {
            return SOSSDataQueryFirewall::blockedResult($e);
        }

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

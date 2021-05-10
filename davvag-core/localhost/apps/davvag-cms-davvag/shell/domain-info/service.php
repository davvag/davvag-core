<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
require_once(PLUGIN_PATH . "/phpcache/cache.php");
require_once(PLUGIN_PATH . "/auth/auth.php");

class DomainInfoService {

    public function getDomainInfo(){
        //DomainInfoService
        return Auth::GetDomainAttributes();
    }
}

?>
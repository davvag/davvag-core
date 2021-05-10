<?php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");


class appService {

    function __construct(){
        
    } 

    public function postSave($req,$res){
        $data = $req->Body(true);
        return $data; 
    }

    


}

?>
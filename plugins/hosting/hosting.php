<?php

class Hosting {
    public static function BackupSystem(){
        $backup_location_app=TENANT_RESOURCE_LOCATION. "/apps/davvag-hosting-console/backups/".DATASTORE_DOMAIN."_APP.zip";
        $backup_location_data=TENANT_RESOURCE_LOCATION. "/apps/davvag-hosting-console/backups/".DATASTORE_DOMAIN."_DATA.zip";
        self::ZIP(TENANT_RESOURCE_LOCATION,$backup_location_app,false);
        self::ZIP(MEDIA_FOLDER . "/".  DATASTORE_DOMAIN."/",$backup_location_data,false);
    }
    
    public static function BackupDataBase(){
        if(defined("DB_CONFIG_FILE")){
                if(file_exists(DB_CONFIG_FILE)){
                    $database=str_replace(".","_",DATASTORE_DOMAIN);
                    $dbconfig = json_decode(file_get_contents(DB_CONFIG_FILE));
                    $database=$dbconfig->init_db.str_replace(".","_",DATASTORE_DOMAIN);
                    $backup_location=TENANT_RESOURCE_LOCATION. "/apps/davvag-hosting-console/backups/".$database.".sql";
                    
                    if($dbconfig->mysql_password!="")
                        $cmd ="mysqldump -h ".$dbconfig->mysql_server." -u ".$dbconfig->mysql_username." -p".$dbconfig->mysql_password." ". $database ." > ".escapeshellarg($backup_location);
                    else
                        $cmd ="mysqldump -h ".$dbconfig->mysql_server." -u ".$dbconfig->mysql_username." ". $database ." > ".escapeshellarg($backup_location);
                    
                    return shell_exec($cmd);
                    if(file_exists($backup_location)){
                        $filename =TENANT_RESOURCE_LOCATION. "/apps/davvag-hosting-console/backups/".$database.".zip";
                        if(file_exists($filename)){unlink($filename);};
                       
                        self::ZIP($backup_location,$filename,false);
                        return true;
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
        }else{
            return false;
        }
    }

    private static function ZIP($backup_location,$filename,$folder){
        $cmd="";
        switch(PHP_OS){
            case "WINNT":
                $cmd="powershell Compress-Archive -Path ".escapeshellarg($backup_location)." -DestinationPath ".escapeshellarg($filename)."";
                break;
            case "WIN32":
                $cmd="powershell Compress-Archive -Path ".escapeshellarg($backup_location)." -DestinationPath ".escapeshellarg($filename)."";
                break;
            case "Windows":
                $cmd="powershell Compress-Archive -Path ".escapeshellarg($backup_location)." -DestinationPath ".escapeshellarg($filename)."";
                break;
            case "Linux":
                $cmd="zip ".($folder?"-r ":"").escapeshellarg($filename)." ".escapeshellarg($backup_location)."";
                break;
            default:
                $cmd="zip ".($folder?"-r ":"").escapeshellarg($filename)." ".escapeshellarg($backup_location)."";
                break;
        }
        exec($cmd, $output);
        //var_dump($output);
    }
}
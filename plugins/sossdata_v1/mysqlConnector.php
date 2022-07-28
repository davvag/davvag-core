<?php 
require_once "schema.php";
class mysqlConnector{
    private $con=null;

    private function Open($db=null){
        if(!defined("DB_CONFIG_FILE")){
            throw new Exception("No database Configuration.");
        }
        if (file_exists(DB_CONFIG_FILE)){
            $configData = json_decode(file_get_contents(DB_CONFIG_FILE));
            if($db==null){
                $dbname=$configData->init_db.DATASTORE_DOMAIN;
            }else{
                $dbname=$configData->init_db.$db;
            }
            $this->con = new mysqli($configData->mysql_server, $configData->mysql_username, $configData->mysql_password, $dbname);
            if ($this->con->connect_error) {
                
                //die("Connection failed: " . $this->con->connect_error);
                throw new Exception($this->con->connect_error);
            }
        }else{
            throw new Exception("Configuration file missing.");
        }
    }

    private function ConOK(){
        if($this->con==null){
            $this->Open();
        }
        if ($this->con->connect_error) {
            throw new Exception($this->con->connect_error);
        }
        return true;
    }

    public function Insert($namespace,$data){
        if($this->ConOK()){
            try {
                $tableSchema=Schema::Get($namespace);
                $sql= $this->generateInsertSQL($namespace,$tableSchema,$data);
                if ($this->con->query($sql) === TRUE) {
                    return 0;
                } else {
                    echo "Error: " . $sql . "<br>" . $this->con->error;
                }
            } catch (Exception $e) {
                throw $e;
            }
        }
    
    }


    public function Update($namespace,$data){
        if($this->ConOK()){
            try {
                $tableSchema=Schema::Get($namespace);
                $sql= $this->generateUpdateSQL($namespace,$tableSchema,$data);
                if ($this->con->query($sql) === TRUE) {
                    return 0;
                } else {
                    echo "Error: " . $sql . "<br>" . $this->con->error;
                }
            }catch(Exception $e){
                throw $e;
            }
        }
    }

    public function Delete($namespace,$data){
        if($this->ConOK()){
            try {
                $tableSchema=Schema::Get($namespace);
                $sql= $this->generateDeleteSQL($namespace,$tableSchema,$data);
                if ($this->con->query($sql) === TRUE) {
                    return 0;
                } else {
                    echo "Error: " . $sql . "<br>" . $this->con->error;
                }
            }catch(Exception $e){
                throw $e;
            }
        }
    }

    private function generateDeleteSQL($namespace,$tableSchema,$data){
        $sql="";
        if(is_array($data)){
            foreach ($data as $key => $value) {
                # code...
                $sql.=$this->generateSingleDelete($namespace,$tableSchema,$value);
            }
        }else{
            $sql.=$this->generateSingleDelete($namespace,$tableSchema,$data);
        }
        return $sql;

    }

    private function generateSingleDelete($namespace,$tableSchema,$data){
        $sqlStart="Delete From ".$namespace." ";
        $sqlend=" where ";
        $primary=false;
        foreach($tableSchema->fields as $value){
            if(isset($data->{$value->fieldName->annotations->isPrimary})){
                if($value->fieldName->annotations->isPrimary){
                    $sqlend.=$value->fieldName."=".$this->getValue($value,$data->{$value->fieldName})." and ";
                    $primary=true;
                }
            }
            if(!$primary){
                if(isset($data->{$value->fieldName})){
                    //$sqlStart.=$value->fieldName."=".$this->getValue($value,$data->{$value->fieldName});
                    $sqlend.=$value->fieldName."=".$this->getValue($value,$data->{$value->fieldName})." and ";
                }
            }
            
        }
        if(strlen($sqlend)<8){
            throw new Exception("Delete cannot be performed no values will delete the who dataset.");
        }
        return rtrim($sqlStart,",")+rtrim($sqlend,"and ").";\n";
    }

    private function generateUpdateSQL($namespace,$tableSchema,$data){
        $sql="";
        if(is_array($data)){
            foreach ($data as $key => $value) {
                # code...
                $sql.=$this->generateSingleUpdate($namespace,$tableSchema,$value);
            }
        }else{
            $sql.=$this->generateSingleUpdate($namespace,$tableSchema,$data);
        }
        return $sql;

    }

    private function generateSingleUpdate($namespace,$tableSchema,$data){
        $sqlStart="Update ".$namespace." SET ";
        $sqlend=" where ";
        foreach($tableSchema->fields as $value){
            if(isset($data->{$value->fieldName->annotations->isPrimary})){
                if($value->fieldName->annotations->isPrimary){
                    $sqlend.=$value->fieldName."=".$this->getValue($value,$data->{$value->fieldName})." and ";
                }
            }
            if(isset($data->{$value->fieldName})){
                $sqlStart.=$value->fieldName."=".$this->getValue($value,$data->{$value->fieldName});
                //$sqlend.=$this->getValue($value,$data->{$value->fieldName}).",";
            }
            
        }
        if(strlen($sqlend)<8){
            throw new Exception("No Primary value was set to update.");
        }
        return rtrim($sqlStart,",")+rtrim($sqlend,"and ").";\n";
    }

    private function generateInsertSQL($namespace,$tableSchema,$data){
        $sql="";
        if(is_array($data)){
            foreach ($data as $key => $value) {
                # code...
                $sql.=$this->generateSingleInsert($namespace,$tableSchema,$value);
            }
        }else{
            $sql.=$this->generateSingleInsert($namespace,$tableSchema,$data);
        }
        return $sql;
    }

    private function generateSingleInsert($namespace,$tableSchema,$data){
        $sqlStart="insert into ".$namespace."(";
        $sqlend=" values(";
        foreach($tableSchema->fields as $value){
            if(isset($data->{$value->fieldName})){
                $sqlStart.=$value->fieldName.",";
                $sqlend.=$this->getValue($value,$data->{$value->fieldName}).",";
            }
        }
        return rtrim($sqlStart,",")+rtrim($sqlend,",").";\n";
    }

    private function getValue($field,$value){
        switch($field->dataType){
            case "java.lang.String":
                return "'".$value."'";
                break;
            case "int":
                return (int)$value;
                break;
            case "float":
                return (float)$value;
                break;
            case "java.util.Date":
                return "'".$value."'";
                break;
            default:
                return "'".$value."'";
              break;
        }
    }

    

}
?>
<?php 
require_once(dirname(__FILE__) . "/schema.php");
class mysqlConnector{
    private $con=null;

    public function Close(){
        mysqli_close($this->con);
    }

    public function Open($db=null){
        if(!defined("DB_CONFIG_FILE")){
            throw new Exception("No database Configuration.");
        }
        if (file_exists(DB_CONFIG_FILE)){
            $configData = json_decode(file_get_contents(DB_CONFIG_FILE));
            if($db==null){
                $dbname=$configData->init_db.str_replace(".","_",DATASTORE_DOMAIN);
            }else{
                $dbname=$configData->init_db.str_replace(".","_",$db);
            }
            $this->con = new mysqli($configData->mysql_server, $configData->mysql_username, $configData->mysql_password, $dbname);
            if ($this->con->connect_error) {
                
                //die("Connection failed: " . $this->con->connect_error);
                if(preg_match('/Unknown database/i',$this->con->connect_error)){
                    $this->createDatabase($configData->mysql_server,$configData->mysql_username, $configData->mysql_password, $dbname);
                    $this->Open();
                }else{
                    throw new Exception($this->con->connect_error);
                }
            }
        }else{
            throw new Exception("Configuration file missing.");
        }
    }

    private function ConOK(){
        if($this->con==null){
            //$this->Open();
            throw new Exception("connection is not Open");
        }
        if ($this->con->connect_error) {
            throw new Exception($this->con->connect_error);
        }
        return true;
    }

    private function createDatabase($servername,$username,$password,$dbname){
        $conn = new mysqli($servername, $username, $password);
        // Check connection
        if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
        }

        // Create database
        $sql = "CREATE DATABASE ".$dbname;
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            throw new Exception($conn->connect_error);
        }

        $conn->close();

    }

    public function ExecuteRaw($namespace,$params){
        $tableSchema=Schema::Get($namespace);
        if(isset($tableSchema->rawquery)){
            $sql = $tableSchema->rawquery->query;
            foreach ($params->parameters as $key => $value) {
                $sql=str_replace("$".$key,$value,$sql);
            }
            if($result=$this->con->query($sql)){
                //return $this->result(true,mysqli_fetch_all($result));
                $data =array();
                while($row = $result ->fetch_array(MYSQLI_ASSOC)){
                    $item =new stdClass();
                    foreach ($tableSchema->fields as $key => $value) {
                        # code...
                        $item->{$value->fieldName}=$row[$value->fieldName];
                        

                    }
                    //$item->{"@meta"}=new stdClass();
                    
                    array_push($data,$item);
                }
                return $this->result(true,$data);
            }else{
                return $this->result(false,null,$this->con->error);
                //throw new Exception($this->con->error); 
                
            }

        }else{
            throw new Exception("This not a valied Schema");
        }
    }

    public function Query($namespace,$param,$lastID=0,$sorting="asc",$pageSize=20,$fromPage=0){
        $tableSchema=Schema::Get($namespace);
        $systemFields=Schema::GetSystemColums();
        foreach ($systemFields as $key => $value) {
            # code...
            array_push($tableSchema->fields,$value);
        }
        $sql="Select * from ".$namespace;
        if(is_array($param)){
            return $this->ExecuteRaw($namespace,$param);
        }else{
            $dived=explode(",",$param);
            $sqlWhere="";
            foreach ($dived as $key => $value) {
                # code...
                $field=explode(":",$value);
                if(count($field)==2){
                    $sqlWhere.=" ".$field[0]."='".$field[1]."' and";
                }
            }
            if($lastID!=0){
                if($sorting =="ASC"){
                    $sqlWhere.=" sysversionid >".$lastID;
                }else{
                    $sqlWhere.=" sysversionid <".$lastID;
                }
            }

            $sql.=($sqlWhere!=""?" where".rtrim($sqlWhere,"and"):"")." Order by sysversionid $sorting limit $fromPage,$pageSize";
            if($result=$this->con->query($sql)){
                //return $this->result(true,mysqli_fetch_all($result));
                $data =array();
                while($row = $result ->fetch_array(MYSQLI_ASSOC)){
                    $item =new stdClass();
                    foreach ($tableSchema->fields as $key => $value) {
                        # code...
                        $item->{$value->fieldName}=$row[$value->fieldName];
                        

                    }
                    $item->{"@meta"}=new stdClass();
                    foreach ($systemFields as $key => $value) {
                        # code...
                        $item->{"@meta"}->{$value->fieldName}=$row[$value->fieldName];
                    }
                    array_push($data,$item);
                }
                return $this->result(true,$data);
            }else{
                if(mysqli_errno($this->con)==1146 || mysqli_errno($this->con)==1054){
                    $this->createTable($namespace);
                    return $this->Query($namespace,$param,$lastID,$sorting,$pageSize,$fromPage);
                }else{
                    return $this->result(false,null,$this->con->error);
                }
            }
        }
    }

    public function Insert($namespace,$data){
        if($this->ConOK()){
            try {
                $tableSchema=Schema::Get($namespace);
                $sql= $this->generateInsertSQL($namespace,$tableSchema,$data);
                if ($this->con->query($sql) === TRUE) {
                    $result=new stdClass();
                    $result->generatedId=mysqli_insert_id($this->con);
                    return $this->result(true,$result);
                } else {
                    if(mysqli_errno($this->con)==1146 || mysqli_errno($this->con)==1054){
                        $this->createTable($namespace);
                    }else{
                        throw new Exception($this->con->error); 
                    }
                    //echo "Error: " . $sql . "<br>" . $this->con->error;
                }
            } catch (Exception $e) {
                return $this->result(false,null,$e->getMessage());
            }
        }
    
    }


    public function Update($namespace,$data){
        if($this->ConOK()){
            try {
                $tableSchema=Schema::Get($namespace);
                $sql= $this->generateUpdateSQL($namespace,$tableSchema,$data);
                if ($this->con->query($sql) === TRUE) {
                    //if($this->con-> affected_rows>0)
                        return $this->result(true,$data);
                    //else{
                        //return $this->result(false,$data,"Not Updated");
                    //}
                } else {

                    if(mysqli_errno($this->con)==1146 || mysqli_errno($this->con)==1054){
                        $this->createTable($namespace);
                    }else{
                        throw new Exception($this->con->error); 
                    }                    //echo "Error: " . $sql . "<br>" . $this->con->error;
                }
            }catch(Exception $e){
                return $this->result(false,null,$e->getMessage());
            }
        }
    }

    public function Delete($namespace,$data){
        if($this->ConOK()){
            try {
                $tableSchema=Schema::Get($namespace);
                $sql= $this->generateDeleteSQL($namespace,$tableSchema,$data);
                if ($this->con->query($sql) === TRUE) {
                    if($this->con-> affected_rows>0)
                        return $this->result(true,$data);
                    else{
                        return $this->result(false,$data,"Not Deleted");
                    }
                } else {
                    if(mysqli_errno($this->con)==1146 || mysqli_errno($this->con)==1054){
                        $this->createTable($namespace);
                    }else{
                       throw new Exception($this->con->error); 
                    }
                    //echo "Error: " . $sql . "<br>" . $this->con->error;
                }
            }catch(Exception $e){
                return $this->result(false,null,$e->getMessage());
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
            if(!empty($value->annotations->isPrimary)){
                if($value->annotations->isPrimary){
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
        return rtrim($sqlStart,",").rtrim($sqlend,"and ").";\n";
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
            if(!empty($value->annotations->isPrimary)){
                if($value->annotations->isPrimary){
                    $sqlend.=$value->fieldName."=".$this->getValue($value,$data->{$value->fieldName})." and ";
                }
            }
            if(isset($data->{$value->fieldName})){
                $sqlStart.=$value->fieldName."=".$this->getValue($value,$data->{$value->fieldName}).",";
                //$sqlend.=$this->getValue($value,$data->{$value->fieldName}).",";
            }
            
        }
        if(strlen($sqlend)<8){
            throw new Exception("No Primary value was set to update.");
            return null;
        }
        $sqlStart.="sysversionid=".date("YmdHis").",sysupdated=".time();
        return rtrim($sqlStart,",").rtrim($sqlend,"and ").";\n";
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

        $sqlStart.="sysversionid,syscreated";
        $sqlend.=date("YmdHis").",".time();

        return rtrim($sqlStart,",").")".rtrim($sqlend,",").");\n";
    }

    private function createTable($namespace){
        $tableSchema=Schema::Get($namespace);
        $systemFields=Schema::GetSystemColums();
        foreach ($systemFields as $key => $value) {
            # code...
            $can=true;
            foreach ($tableSchema->fields as $key => $vt) {
                if($vt->fieldName==$value->fieldName){
                    $can=false;
                }
                # code...
            }
            if($can)
                array_push($tableSchema->fields,$value);
        }
        
        $update=false;
        if ($result = $this->con->query("SHOW TABLES LIKE '".$namespace."'")) {
            if($result->num_rows == 1) {
                $update=true;
            }
        }
        $sql="";
        if($update){
            if ($result = $this->con->query("DESCRIBE ".$namespace."")) {
                $colums=mysqli_fetch_all($result);

                    
                foreach($tableSchema->fields as $value){
                    $has=false;
                    $alter=false;
                    foreach ($colums as $key => $row) {
                        if(strtolower( $row[0])==strtolower($value->fieldName)){
                            $has=true;                            
                        }
                        
                    }
                    if(!$has){
                        $sql.= "ADD ".$value->fieldName. " ". $this->convertSQLtype($value->dataType,(!empty($value->annotations->maxLen)?$value->annotations->maxLen:0),
                            (!empty($value->annotations->isPrimary)?false:true),
                            (!empty($value->annotations->autoIncrement)?$value->annotations->autoIncrement:false),
                            (!empty($value->annotations->decimalPoints)?$value->annotations->decimalPoints:"10,2"),
                            (!empty($value->annotations->encoding)?$value->annotations->encoding:false))."";
                            $sql.=",\n";
                    }
                    if($alter){
                        $sql.= "Alter ".$value->fieldName. " ". $this->convertSQLtype($value->dataType,(!empty($value->annotations->maxLen)?$value->annotations->maxLen:0),
                            (!empty($value->annotations->isPrimary)?false:true),
                            (!empty($value->annotations->autoIncrement)?$value->annotations->autoIncrement:false),
                            (!empty($value->annotations->decimalPoints)?$value->annotations->decimalPoints:"10,2"),
                            (!empty($value->annotations->encoding)?$value->annotations->encoding:false))."";
                            $sql.=",\n";
                    }
                        
                }
                    
                
                if($sql!=""){
                    $sql=rtrim($sql,",\n");
                    $sql="Alter Table ".$namespace. " ".$sql.";";
                }
                
            }
        }
        else{
            $sql ="Create Table ".$namespace."(";
            $primary=" PRIMARY KEY(";
            foreach($tableSchema->fields as $value){
                $sql.= $value->fieldName. " ". $this->convertSQLtype($value->dataType,(!empty($value->annotations->maxLen)?$value->annotations->maxLen:0),
                (!empty($value->annotations->isPrimary)?false:true),
                (!empty($value->annotations->autoIncrement)?$value->annotations->autoIncrement:false),
                (!empty($value->annotations->decimalPoints)?$value->annotations->decimalPoints:"10,2"),
                (!empty($value->annotations->encoding)?$value->annotations->encoding:false)).",";
                if(empty($value->annotations->isPrimary)==false){
                    if($value->annotations->isPrimary){
                        $primary.=$value->fieldName.",";
                    }
                }
            }
            //$sql=rtrim($sql,",\n");
            $sql.=rtrim($primary,",")."));";
        }
        if($sql!=""){
            if ($this->con->query($sql) === TRUE) {
                return true;
            } else {
                echo "Error: " . $sql . "<br>" . $this->con->error;
                return false;
            }
        }

    }

    private function result($suessfull,$data=null,$message=""){
        $result=new stdClass();
        $result->success=$suessfull;
        if($suessfull){
            $result->result=$data;
        }else{
            $result->message=$message;
        }
        return $result;
    }

    private function convertSQLtype($datatype,$datalength,$isNull, $isAutoIncrement, $decimalPoints,$endCoding){
		$strValue="";
		
		
        switch($datatype){
			case "int":
				$strValue="INT ".(($isNull)?"":"NOT")." NULL ";
				if ($isAutoIncrement)
					$strValue .= "AUTO_INCREMENT ";
				break;
			case "float":
				$strValue="FLOAT ".(($isNull)?"":"NOT")." NULL";
				
				break;
			case "double":
				$strValue="DECIMAL ".(($isNull)?"":"NOT")." NULL";
				break;
			case "short":
				$strValue="LONG ".(($isNull)?"":"NOT")." NULL";
				break;
			case "long":
				$strValue="LONG ".(($isNull)?"":"NOT")." NULL";
				break;
			case "decimal":
				if ($decimalPoints == null)
					$strValue = "DECIMAL(10,1)";
				else
					$strValue = "DECIMAL(" . $decimalPoints . ") ";
				break;
			case "java.lang.String":
				if($datalength==0){
					$strValue="TEXT ".($endCoding==null || $endCoding==""?"":" CHARACTER SET '".$endCoding."' ")." ".(($isNull)?"":"NOT")." NULL";
				}
				else if($datalength<3072){
					if($endCoding==null || $endCoding==""){
						$strValue="VARCHAR(".($datalength).") ".(($isNull)?"":"NOT")." NULL";
					}else{
						$strValue="MEDIUMTEXT ".($endCoding==null || $endCoding==""?"":" CHARACTER SET '".$endCoding."' ")." ".(($isNull)?"":"NOT")." NULL";
					}
						
				}else{
					$strValue="TEXT ".($endCoding==null || $endCoding==""?"":" CHARACTER SET '".$endCoding."' ")." ".(($isNull)?"":"NOT")." NULL";
				}
				break;
			case "java.util.Date":
				$strValue= "DATETIME ".(($isNull)?"":"NOT")." NULL";
				break;
			case "boolean":
				$strValue="VARCHAR(5) ".(($isNull)?"":"NOT")." NULL";
				break;
			default:
				$strValue="TEXT ".(($isNull)?"":"NOT")." NULL";
				break;
        
        }
		return $strValue;
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
            case "double":
                return (float)$value;
                break;
            case "short":
                return (int)$value;
                break;
            case "long":
                return (int)$value;
                break;
                case "decimal":
            case "java.util.Date":
                return "'".$value."'";
                break;
            case "object":
                return "'".json_encode($value)."'";
                break;
            default:
                return "'".$value."'";
              break;
        }
    }

    

}
?>
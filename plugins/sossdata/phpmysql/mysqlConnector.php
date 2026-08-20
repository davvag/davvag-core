<?php
require_once(dirname(__FILE__) . "/schema.php");
require_once(dirname(__FILE__) . "/../SOSSDataQueryFirewall.php");
class mysqlConnector
{
    private $con = null;
    private $retry=0;
    
    public function Close()
    {
        mysqli_close($this->con);
    }

    public function Open($db = null)
    {
        try {
            if (!defined("DB_CONFIG_FILE")) {
                throw new Exception("No database Configuration.");
            }
            if (file_exists(DB_CONFIG_FILE)) {
                $configData = json_decode(file_get_contents(DB_CONFIG_FILE));
                if ($db == null) {
                    $dbname = $configData->init_db . str_replace(".", "_", DATASTORE_DOMAIN);
                } else {
                    $dbname = $configData->init_db . str_replace(".", "_", $db);
                }
                $this->con = new mysqli($configData->mysql_server, $configData->mysql_username, $configData->mysql_password, $dbname);
                if ($this->con->connect_error) {

                    //die("Connection failed: " . $this->con->connect_error);
                    if (preg_match('/Unknown database/i', $this->con->connect_error)) {
                        $this->createDatabase($configData->mysql_server, $configData->mysql_username, $configData->mysql_password, $dbname);
                        $this->Open($db);
                    } else {
                        throw new Exception($this->con->connect_error);
                    }
                }
                $charset = isset($configData->mysql_charset) ? $configData->mysql_charset : "utf8mb4";
                if (!$this->con->set_charset($charset)) {
                    throw new Exception("Unable to set the MySQL connection character set.");
                }
            } else {
                throw new Exception("Configuration file missing.");
            }
        }catch (Exception $e) {
            //if($e->getMessage())
            if (preg_match('/Unknown database/i', $e->getMessage())) {
                $this->createDatabase($configData->mysql_server, $configData->mysql_username, $configData->mysql_password, $dbname);
                $this->Open($db);
            } else {
                throw new Exception($e->getMessage());
            }
            //throw new Exception("Database connection failed: " . $e->getMessage());
        }
        
    }

    private function ConOK()
    {
        if ($this->con == null) {
            //$this->Open();
            throw new Exception("connection is not Open");
        }
        if ($this->con->connect_error) {
            throw new Exception($this->con->connect_error);
        }
        return true;
    }

    private function createDatabase($servername, $username, $password, $dbname)
    {
        $conn = new mysqli($servername, $username, $password);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Create database
        $sql = "CREATE DATABASE " . $this->quoteIdentifier($dbname);
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            throw new Exception($conn->connect_error);
        }

        $conn->close();
    }

    public function ExecuteRaw($namespace, $params)
    {
        try{
            $namespace = SOSSDataQueryFirewall::validateNamespace($namespace);
            SOSSDataQueryFirewall::validateRawRequest($params);
            $tableSchema = Schema::Get($namespace);
            if (isset($tableSchema->rawquery)) {
                $parameterValues = is_object($params) ? $params->parameters : $params["parameters"];
                $declaredParameters = isset($tableSchema->rawquery->parameters) ? $tableSchema->rawquery->parameters : array();
                $compiled = SOSSDataQueryFirewall::compileRawQuery(
                    $tableSchema->rawquery->query,
                    $parameterValues,
                    $declaredParameters
                );

                try {
                    $statement = $this->con->prepare($compiled->sql);
                } catch (mysqli_sql_exception $e) {
                    return $this->handleRawQueryError($namespace, $params, $e->getCode(), $e->getMessage());
                }
                if ($statement === false) {
                    return $this->handleRawQueryError($namespace, $params, mysqli_errno($this->con), $this->con->error);
                }

                $boundValues = $compiled->values;
                if ($compiled->types !== "" && !$statement->bind_param($compiled->types, ...$boundValues)) {
                    $message = $statement->error;
                    $statement->close();
                    return $this->result(false, null, $message);
                }
                try {
                    $executed = $statement->execute();
                } catch (mysqli_sql_exception $e) {
                    $statement->close();
                    return $this->handleRawQueryError($namespace, $params, $e->getCode(), $e->getMessage());
                }
                if (!$executed) {
                    $errorNumber = $statement->errno;
                    $message = $statement->error;
                    $statement->close();
                    return $this->handleRawQueryError($namespace, $params, $errorNumber, $message);
                }

                $result = $statement->get_result();
                if ($result === false) {
                    $message = $statement->error !== "" ? $statement->error : "Raw query did not return a result set.";
                    $statement->close();
                    return $this->result(false, null, $message);
                }

                $data = array();
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $item = new stdClass();
                    foreach ($tableSchema->fields as $key => $value) {
                        $item->{$value->fieldName} = isset($row[$value->fieldName]) ? $row[$value->fieldName] : $value->fieldName . " not found.";
                    }
                    array_push($data, $item);
                }
                $result->free();
                $statement->close();
                $this->drainPendingResults();
                return $this->result(true, $data);
            } else {
                throw new Exception("This is not a valid raw-query schema.");
            }
        }catch(Throwable $e){
            return $this->result(false, null, $e->getMessage());
        }
    }

    private function handleRawQueryError($namespace, $params, $errorNumber, $message)
    {
        if ($errorNumber == 1305) {
            if ($this->retry > 0) {
                return $this->result(false, null, $message);
            }
            $this->retry++;
            $this->ExcuteMySQLScript($namespace);
            $this->retry = 0;
            return $this->ExecuteRaw($namespace, $params);
        }
        return $this->result(false, null, $message);
    }

    private function drainPendingResults()
    {
        while ($this->con->more_results() && $this->con->next_result()) {
            $result = $this->con->store_result();
            if ($result) {
                $result->free();
            }
        }
    }

    private function AdvancedQuery($namespace, $param, $lastID = 0, $sorting = "DESC", $pageSize = 20, $fromPage = 0, $viewObject = true)
    {
        $tableSchema = clone(Schema::Get($namespace));
        $systemFields = Schema::GetSystemColumns();
        $fieldMap = array();

        foreach ($tableSchema->fields as $field) {
            $fieldMap[strtolower($field->fieldName)] = $field;
        }
        foreach ($systemFields as $field) {
            if (!isset($fieldMap[strtolower($field->fieldName)])) {
                array_push($tableSchema->fields, $field);
            }
            $fieldMap[strtolower($field->fieldName)] = $field;
        }

        $conditions = $this->getAdvancedQueryOption($param, array("conditions", "condition"), array());
        if ($conditions === null) {
            $conditions = array();
        }
        if (is_object($conditions)) {
            $conditions = (array)$conditions;
        }
        if (!is_array($conditions)) {
            throw new Exception("Condition must be an array.");
        }
        if ($this->hasAdvancedQueryOption($conditions, array("column", "coloumn"))) {
            $conditions = array($conditions);
        }

        $whereParts = array();
        $allowedOperators = array(
            "=" => "=",
            "==" => "=",
            "!=" => "!=",
            "<>" => "<>",
            ">" => ">",
            ">=" => ">=",
            "<" => "<",
            "<=" => "<=",
            "LIKE" => "LIKE",
            "NOT LIKE" => "NOT LIKE",
            "IN" => "IN",
            "NOT IN" => "NOT IN",
            "IS NULL" => "IS NULL",
            "IS NOT NULL" => "IS NOT NULL"
        );

        foreach ($conditions as $condition) {
            if (is_object($condition)) {
                $condition = (array)$condition;
            }
            if (!is_array($condition)) {
                throw new Exception("Each condition must be an object or array.");
            }

            // Use "column" in new payloads. The misspelled "coloumn" key remains a legacy alias.
            $column = $this->getAdvancedQueryOption($condition, array("column", "coloumn"), null);
            $operator = $this->getAdvancedQueryOption($condition, array("condition", "operator"), "=");
            $valueExists = $this->hasAdvancedQueryOption($condition, array("value"));
            $value = $this->getAdvancedQueryOption($condition, array("value"), null);

            if (!is_string($column)) {
                throw new Exception("Column [" . (is_scalar($column) ? $column : "") . "] not found.");
            }

            $column = trim($column);
            if (!isset($fieldMap[strtolower($column)])) {
                throw new Exception("Column [" . $column . "] not found.");
            }

            $operator = strtoupper(trim(preg_replace('/\s+/', ' ', (string)$operator)));
            if (!isset($allowedOperators[$operator])) {
                throw new Exception("Condition operator [" . $operator . "] is not supported.");
            }
            $operator = $allowedOperators[$operator];
            $field = $fieldMap[strtolower($column)];
            $quotedColumn = $this->quoteIdentifier($field->fieldName);

            if ($operator === "IS NULL" || $operator === "IS NOT NULL") {
                $whereParts[] = $quotedColumn . " " . $operator;
                continue;
            }

            if (!$valueExists) {
                throw new Exception("Condition value is required for column [" . $field->fieldName . "].");
            }

            if ($value === null && ($operator === "=" || $operator === "==")) {
                $whereParts[] = $quotedColumn . " IS NULL";
            } else if ($value === null && ($operator === "!=" || $operator === "<>")) {
                $whereParts[] = $quotedColumn . " IS NOT NULL";
            } else if ($value === null) {
                throw new Exception("Null can only be used with =, !=, <>, IS NULL, or IS NOT NULL.");
            } else if ($operator === "IN" || $operator === "NOT IN") {
                if (!is_array($value) || count($value) === 0) {
                    throw new Exception("Condition value for [" . $operator . "] must be a non-empty array.");
                }
                $sqlValues = array();
                foreach ($value as $singleValue) {
                    $sqlValues[] = $singleValue === null ? "NULL" : $this->getValue($field, $singleValue);
                }
                $whereParts[] = $quotedColumn . " " . $operator . " (" . implode(",", $sqlValues) . ")";
            } else {
                $whereParts[] = $quotedColumn . " " . $operator . " " . $this->getValue($field, $value);
            }
        }

        $defaultDirection = SOSSDataQueryFirewall::normalizeDirection($sorting);
        $queryDirection = $this->getAdvancedQueryOption($param, array("sortDirection", "sortingDirection", "direction"), null);
        if ($queryDirection !== null) {
            $defaultDirection = SOSSDataQueryFirewall::normalizeDirection($queryDirection);
        }

        $sortItems = $this->getAdvancedQueryOption($param, array("sorting", "sort"), array());
        if ($sortItems === null || $sortItems === array()) {
            $sortItems = array("sysversionid");
        } else if (!is_array($sortItems)) {
            $sortItems = array($sortItems);
        } else if ($this->hasAdvancedQueryOption($sortItems, array("column", "coloumn"))) {
            $sortItems = array($sortItems);
        }

        $orderParts = array();
        foreach ($sortItems as $sortKey => $sortItem) {
            $sortColumn = null;
            $sortDirection = $defaultDirection;

            if (is_object($sortItem)) {
                $sortItem = (array)$sortItem;
            }
            if (is_array($sortItem)) {
                $sortColumn = $this->getAdvancedQueryOption($sortItem, array("column", "coloumn"), null);
                $itemDirection = $this->getAdvancedQueryOption($sortItem, array("direction", "sorting"), null);
                if ($itemDirection !== null) {
                    $sortDirection = strtoupper(trim((string)$itemDirection));
                }
            } else if (!is_int($sortKey)) {
                $sortColumn = $sortKey;
                $sortDirection = strtoupper(trim((string)$sortItem));
            } else {
                $sortText = trim((string)$sortItem);
                if (preg_match('/^(.+?)\s+(ASC|DESC)$/i', $sortText, $matches)) {
                    $sortColumn = trim($matches[1]);
                    $sortDirection = strtoupper($matches[2]);
                } else if (substr($sortText, 0, 1) === "-") {
                    $sortColumn = substr($sortText, 1);
                    $sortDirection = "DESC";
                } else {
                    $sortColumn = $sortText;
                }
            }

            if (!is_string($sortColumn)) {
                throw new Exception("Sorting column [" . (is_scalar($sortColumn) ? $sortColumn : "") . "] not found.");
            }
            $sortColumn = trim($sortColumn);
            if (!isset($fieldMap[strtolower($sortColumn)])) {
                throw new Exception("Sorting column [" . $sortColumn . "] not found.");
            }
            if ($sortDirection !== "ASC" && $sortDirection !== "DESC") {
                throw new Exception("Sorting direction must be ASC or DESC.");
            }
            $orderParts[] = $this->quoteIdentifier($fieldMap[strtolower($sortColumn)]->fieldName) . " " . $sortDirection;
        }

        $pageSize = $this->getAdvancedQueryOption($param, array("pageSize"), $pageSize);
        $fromPage = $this->getAdvancedQueryOption($param, array("pageFrom"), $fromPage);
        $pageSize = SOSSDataQueryFirewall::normalizePageSize($pageSize);
        $fromPage = SOSSDataQueryFirewall::normalizeOffset($fromPage);

        if ($lastID != 0) {
            $whereParts[] = $this->quoteIdentifier("sysversionid") . ($defaultDirection === "ASC" ? " > " : " < ") . (int)$lastID;
        }

        if ($viewObject) {
            $objects = Auth::ViewObjects();
            if (is_array($objects) && count($objects) > 0) {
                $viewObjects = array();
                foreach ($objects as $object) {
                    $viewObjects[] = (int)$object;
                }
                $whereParts[] = $this->quoteIdentifier("sysviewobject") . " IN (" . implode(",", $viewObjects) . ")";
            }
        }

        $sql = "SELECT * FROM " . $this->quoteIdentifier($namespace);
        if (count($whereParts) > 0) {
            $sql .= " WHERE " . implode(" AND ", $whereParts);
        }
        $sqlCount = "SELECT COUNT(*) FROM (" . $sql . ") tmp1982";
        $sql .= " ORDER BY " . implode(", ", $orderParts) . " LIMIT " . $fromPage . "," . $pageSize;

        if ($result = $this->con->query($sql)) {
            $numberOfRecords = 0;
            if ($rCount = $this->con->query($sqlCount)) {
                $countRow = $rCount->fetch_row();
                $numberOfRecords = (int)$countRow[0];
            }

            $data = array();
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $item = new stdClass();
                foreach ($tableSchema->fields as $field) {
                    $item->{$field->fieldName} = $this->getValueToObject($field, isset($row[$field->fieldName]) ? $row[$field->fieldName] : null);
                }
                $item->{"@meta"} = new stdClass();
                foreach ($systemFields as $field) {
                    $item->{"@meta"}->{$field->fieldName} = isset($row[$field->fieldName]) ? $row[$field->fieldName] : null;
                }
                array_push($data, $item);
            }
            return $this->result(true, $data, "", $numberOfRecords, $fromPage, $pageSize);
        }

        if (mysqli_errno($this->con) == 1146 || mysqli_errno($this->con) == 1054) {
            if ($this->retry > 0) {
                return $this->result(false, array(), $this->con->error);
            }
            $this->retry++;
            $this->ensureTableReady($namespace);
            $this->retry = 0;
            return $this->AdvancedQuery($namespace, $param, $lastID, $sorting, $pageSize, $fromPage, $viewObject);
        }

        return $this->result(false, array(), $this->con->error);
    }

    private function getAdvancedQueryOption($options, $names, $default = null)
    {
        if (is_object($options)) {
            $options = (array)$options;
        }
        if (!is_array($options)) {
            return $default;
        }

        $lowerNames = array_map("strtolower", $names);
        foreach ($options as $key => $value) {
            if (in_array(strtolower((string)$key), $lowerNames, true)) {
                return $value;
            }
        }
        return $default;
    }

    private function hasAdvancedQueryOption($options, $names)
    {
        if (is_object($options)) {
            $options = (array)$options;
        }
        if (!is_array($options)) {
            return false;
        }

        $lowerNames = array_map("strtolower", $names);
        foreach ($options as $key => $value) {
            if (in_array(strtolower((string)$key), $lowerNames, true)) {
                return true;
            }
        }
        return false;
    }

    private function quoteIdentifier($identifier)
    {
        return "`" . str_replace("`", "``", (string)$identifier) . "`";
    }

    public function Query($namespace, $param, $lastID = 0, $sorting = "DESC", $pageSize = 20, $fromPage = 0, $viewObject = true)
    {
        try {
            $namespace = SOSSDataQueryFirewall::validateNamespace($namespace);
            $sorting = SOSSDataQueryFirewall::normalizeDirection($sorting);
            $pageSize = SOSSDataQueryFirewall::normalizePageSize($pageSize);
            $fromPage = SOSSDataQueryFirewall::normalizeOffset($fromPage);
            $lastID = SOSSDataQueryFirewall::normalizeLastVersionId($lastID);
            $this->ensureNamespaceReady($namespace);
            $tableSchema = clone(Schema::Get($namespace));
            $systemFields = Schema::GetSystemColumns();
            if (is_string($param)) {
                $param = urldecode($param);
                $decodedParam = json_decode($param, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedParam)) {
                    $param = $decodedParam;
                }
            } else if (is_object($param)) {
                $param = (array)$param;
            }
            $param = SOSSDataQueryFirewall::validateQuery($param);
            foreach ($systemFields as $key => $value) {
                # code...
                array_push($tableSchema->fields, $value);
            }
            $sql = "Select * from " . $namespace;
            if (is_array($param)) {
                return $this->AdvancedQuery($namespace, $param, $lastID, $sorting, $pageSize, $fromPage, $viewObject);
            } else {
                $divided = explode(",", $param);
                $whereParts = array();
                $fieldMap = array();
                foreach ($tableSchema->fields as $schemaField) {
                    $fieldMap[strtolower($schemaField->fieldName)] = $schemaField;
                }
                foreach ($divided as $key => $value) {
                    $field = explode(":", $value);
                    if (count($field) == 2) {
                        $fieldName = trim($field[0]);
                        $fieldKey = strtolower($fieldName);
                        if (!isset($fieldMap[$fieldKey])) {
                            throw new Exception("Column [" . $fieldName . "] not found.");
                        }
                        $schemaField = $fieldMap[$fieldKey];
                        $whereParts[] = $this->quoteIdentifier($schemaField->fieldName) . "=" . $this->getValue($schemaField, $field[1]);
                    }
                }
                if ($lastID !== null) {
                    if ($sorting == "ASC") {
                        $whereParts[] = $this->quoteIdentifier("sysversionid") . ">" . $lastID;
                    } else {
                        $whereParts[] = $this->quoteIdentifier("sysversionid") . "<" . $lastID;
                    }
                }
                if($viewObject){
                    $objs=Auth::ViewObjects();
                    if(is_array($objs) && count($objs)>0){
                        $viewObjects = array();
                        foreach ($objs as $object) {
                            $viewObjects[] = (int)$object;
                        }
                        $whereParts[] = $this->quoteIdentifier("sysviewobject") . " IN (" . implode(",", $viewObjects) . ")";
                    }
                }
                
                $sql = "SELECT * FROM " . $this->quoteIdentifier($namespace);
                if (count($whereParts) > 0) {
                    $sql .= " WHERE " . implode(" AND ", $whereParts);
                }
                $sqlCount ="Select count(*) from ($sql) tmp1982";
                $sql.= " ORDER BY " . $this->quoteIdentifier("sysversionid") . " " . $sorting . " LIMIT " . $fromPage . "," . $pageSize;
                if ($result = $this->con->query($sql)) {
                    //return $this->result(true,mysqli_fetch_all($result));
                    if ($rCount = $this->con->query($sqlCount)){
                        $r = $rCount -> fetch_row();
                        $data = array();
                        $numberOfRecords=(int)$r[0];
                    }else{
                        $numberOfRecords=0;
                    }
                    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                        $item = new stdClass();
                        
                        foreach ($tableSchema->fields as $key => $value) {
                            # code...
                            $item->{$value->fieldName} = $this->getValueToObject($value, isset($row[$value->fieldName])?$row[$value->fieldName]:null);
                        }
                        $item->{"@meta"} = new stdClass();
                        foreach ($systemFields as $key => $value) {
                            # code...
                            $item->{"@meta"}->{$value->fieldName} = isset($row[$value->fieldName])?$row[$value->fieldName]:null;
                        }
                        array_push($data, $item);
                    }
                    return $this->result(true, $data,"",$numberOfRecords,$fromPage,$pageSize);
                } else {
                    if (mysqli_errno($this->con) == 1146 || mysqli_errno($this->con) == 1054) {
                        if($this->retry>0){
                            return $this->result(false, [], $this->con->error);
                        }
                        $this->retry++;
                        $this->ensureTableReady($namespace);
                        $this->retry=0;
                        return $this->Query($namespace, $param, $lastID, $sorting, $pageSize, $fromPage, $viewObject);
                    } else {
                        return $this->result(false, [], $this->con->error);
                    }
                }
            }
        } catch (Throwable $e) {
            $errorNumber = $this->con instanceof mysqli ? mysqli_errno($this->con) : 0;
            return $this->result(false, [], $e->getMessage() . " ERROR NO" . $errorNumber);
        }
        
    }

    public function Insert($namespace, $data)
    {
        if ($this->ConOK()) {

            try {
                $namespace = SOSSDataQueryFirewall::validateNamespace($namespace);
                $this->ensureNamespaceReady($namespace);
                $tableSchema = Schema::Get($namespace);
                $sqls = $this->generateInsertSQL($namespace, $tableSchema, $data);
                $genis = array();
                $success = true;
                $errorMsg = "";
                
                foreach ($sqls as $key => $sql) {
                    # code...
                    $result = new stdClass();
                    if ($this->con->query($sql) === TRUE) {
                        $result->success = true;
                        $result->generatedId = mysqli_insert_id($this->con);
                        array_push($genis, $result);
                    } else {
                        if (mysqli_errno($this->con) == 1146 || mysqli_errno($this->con) == 1054) {
                            if($this->retry>0){
                                return $this->result(false, null, $this->con->error);
                            }
                            $this->retry++;
                            $this->ensureTableReady($namespace);
                            $this->retry=0;
                            return $this->Insert($namespace, $data);
                        } else {
                            $result->success = false;
                            $result->success = $this->con->error;
                            $success = false;
                            $errorMsg = $this->con->error;
                            array_push($genis, $result);
                            //throw new Exception($this->con->error); 
                        }
                        //echo "Error: " . $sql . "<br>" . $this->con->error;
                    }
                }
                return $this->result($success, count($genis) == 1 ? $genis[0] : $genis, $errorMsg);
            } catch (Throwable $e) {
                if(mysqli_errno($this->con) == 1146 || mysqli_errno($this->con) == 1054){
                    if($this->retry>0){
                        return $this->result(false, null, $this->con->error);
                    }
                    $this->retry++;
                    $this->ensureTableReady($namespace);
                    $this->retry=0;
                    return $this->Insert($namespace, $data);
                }else{
                    return $this->result(false, null, $e->getMessage());
                }
                
            }
        }
    }


    public function Update($namespace, $data)
    {
        if ($this->ConOK()) {
            try {
                $namespace = SOSSDataQueryFirewall::validateNamespace($namespace);
                $this->ensureNamespaceReady($namespace);
                $tableSchema = Schema::Get($namespace);
                $sqls = $this->generateUpdateSQL($namespace, $tableSchema, $data);
                $results = array();
                $success = true;
                $errorMsg = "";
                
                foreach ($sqls as $key => $dout) {
                    # code...

                    if ($this->con->query($dout->sql) === TRUE) {
                        array_push($results, $dout->data);
                    } else {

                        if (mysqli_errno($this->con) == 1146 || mysqli_errno($this->con) == 1054) {
                            if($this->retry>0){
                                return $this->result(false, null, $this->con->error);
                            }
                            $this->retry++;
                            $this->ensureTableReady($namespace);
                            $this->retry=0;
                            return $this->Update($namespace, $data);
                        } else {
                            $dout->data->{"_ErrorMessage"} = $this->con->error;
                            $success = false;
                            $errorMsg = $this->con->error;
                            array_push($results, $dout->data);
                        }
                    }
                }
                return $this->result($success, count($results) == 1 ? $results[0] : $results, $errorMsg);
            } catch (Throwable $e) {
                if(mysqli_errno($this->con) == 1146 || mysqli_errno($this->con) == 1054){
                    if($this->retry>0){
                        return $this->result(false, null, $this->con->error);
                    }
                    $this->retry++;
                    $this->ensureTableReady($namespace);
                    $this->retry=0;
                    return $this->Update($namespace, $data);
                }else{
                    return $this->result(false, null, $e->getMessage());
                }
            }
        }
    }

    public function Delete($namespace, $data)
    {
        if ($this->ConOK()) {
            try {
                $namespace = SOSSDataQueryFirewall::validateNamespace($namespace);
                $this->ensureNamespaceReady($namespace);
                
                $tableSchema = Schema::Get($namespace);
                $sqls = $this->generateDeleteSQL($namespace, $tableSchema, $data);
                $results = array();
                $success = true;
                $errorMsg = "";
                foreach ($sqls as $key => $dout) {
                    # code...
                    if ($this->con->query($dout->sql) === TRUE) {
                        if ($this->con->affected_rows > 0)
                            array_push($results, $dout->data);
                        //return $this->result(true,$data);
                        else {
                            $success = false;
                            $dout->data->{"_ErrorMessage"} = "Not Deleted";
                            $errorMsg = "Not Deleted";
                            array_push($results, $dout->data);
                            //return $this->result(false,$data,"Not Deleted");
                        }
                    } else {
                        if (mysqli_errno($this->con) == 1146 || mysqli_errno($this->con) == 1054) {
                            if($this->retry>0){
                                return $this->result(false, null, $this->con->error);
                            }
                            $this->retry++;
                            $this->ensureTableReady($namespace);
                            $this->retry=0;
                            return $this->Delete($namespace, $data);
                        } else {
                            $success = false;
                            $dout->data->{"_ErrorMessage"} = $this->con->error;
                            $errorMsg = $this->con->error;
                            array_push($results, $dout->data);
                        }
                        //echo "Error: " . $sql . "<br>" . $this->con->error;
                    }
                }
                return $this->result($success, count($results) == 1 ? $results[0] : $results, $errorMsg);
            } catch (Throwable $e) {
                return $this->result(false, null, $e->getMessage());
            }
        }
    }

    public function SetViewObject($objectID=0){
        
    }

    private function ExcuteMySQLScript($namespace){
        if (file_exists(TENANT_RESOURCE_LOCATION."/schemas/mysqlquery/".$namespace.".sql")){
            $s = file_get_contents(TENANT_RESOURCE_LOCATION."/schemas/mysqlquery/".$namespace.".sql");
            $sqls=explode("DELIMITER $$",$s);
            //self::$Schema[$name]=$s;
            foreach ($sqls as $sql) {
                if ($this->con->query($sql) === TRUE) {
                   
                } else {
                    throw new Exception("Error executing mysql script for ".$namespace.": ".$this->con->error);
                }
                # code...
            }
        }
    }

    private function ensureTableReady($namespace)
    {
        if (!$this->createTable($namespace)) {
            $message = $this->con ? $this->con->error : "Unknown schema recovery error";
            throw new Exception("Auto-create failed for table '".$namespace."': ".$message);
        }
    }

    private function ensureNamespaceReady($namespace)
    {
        if (!$this->tableExists($namespace)) {
            $this->ensureTableReady($namespace);
        }
    }

    private function tableExists($namespace)
    {
        $escaped = mysqli_real_escape_string($this->con, $namespace);
        $result = $this->con->query("SHOW TABLES LIKE '" . $escaped . "'");
        if ($result === false) {
            throw new Exception("Unable to inspect table '".$namespace."': ".$this->con->error);
        }
        return $result->num_rows === 1;
    }

    private function generateDeleteSQL($namespace, $tableSchema, $data)
    {
        $sql = array();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                # cod(e...
                $std = (object)clone($value);
                array_push($sql, $this->generateSingleDelete($namespace, $tableSchema, $std));
            }
        } else {
            array_push($sql, $this->generateSingleDelete($namespace, $tableSchema, clone($data)));
        }
        return $sql;
    }

    private function generateSingleDelete($namespace, $tableSchema, $data)
    {
        $sqlStart = "Delete From " . $namespace . " ";
        $sqlend = " where ";
        $primary = false;
        foreach ($tableSchema->fields as $value) {
            if (!empty($value->annotations->isPrimary)) {
                if ($value->annotations->isPrimary) {
                    $sqlend .= "`" . $value->fieldName . "`=" . $this->getValue($value, $data->{$value->fieldName}) . " and ";
                    $primary = true;
                }
            }
            if (!$primary) {
                if (isset($data->{$value->fieldName})) {
                    //$sqlStart.=$value->fieldName."=".$this->getValue($value,$data->{$value->fieldName});
                    $sqlend .= "`" . $value->fieldName . "`=" . $this->getValue($value, $data->{$value->fieldName}) . " and ";
                }
            }
        }
        if (strlen($sqlend) < 8) {
            throw new Exception("Delete cannot be performed no values will delete the who dataset.");
        }
        $dout = new stdClass();
        $dout->sql = rtrim($sqlStart, ",") . rtrim($sqlend, "and ") . ";\n";
        $dout->data = $data;
        return $dout;
    }

    private function generateUpdateSQL($namespace, $tableSchema, $data)
    {
        $sql = array();
        $user=Auth::Autendicate();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                # code...
                $std = (object)clone($value);
                if(empty($std->sysviewobject)){
                    $std->sysviewobject=0;
                }
                if(isset($user->userid)){
                    $std->syslastupdatedby=$user->userid;
                }else{
                    $std->syslastupdatedby="anonymous";
                }
                array_push($sql, $this->generateSingleUpdate($namespace, $tableSchema, $std));
            }
        } else {
            $std = (object)clone($data);
            if(empty($std->sysviewobject)){
                $std->sysviewobject=0;
            }
            if(isset($user->userid)){
                $std->syslastupdatedby=$user->userid;
            }else{
                $std->syslastupdatedby="anonymous";
            }
            array_push($sql, $this->generateSingleUpdate($namespace, $tableSchema, $std));
        }
        return $sql;
    }

    private function generateSingleUpdate($namespace, $tableSchema, $data)
    {
        $sqlStart = "Update " . $namespace . " SET ";
        $sqlend = " where ";
        $dout = new stdClass();
        foreach ($tableSchema->fields as $value) {
            if (!empty($value->annotations->isPrimary)) {
                if ($value->annotations->isPrimary) {
                    $sqlend .= "`" . $value->fieldName . "`=" . $this->getValue($value, empty($data->{$value->fieldName}) ? null : $data->{$value->fieldName}) . " and ";
                }
            }
            if (isset($data->{$value->fieldName})) {
                $sqlStart .= "`" . $value->fieldName . "`" . "=" . $this->getValue($value, empty($data->{$value->fieldName}) ? null : $data->{$value->fieldName}) . ",";
                //$sqlend.=$this->getValue($value,$data->{$value->fieldName}).",";
            }
        }
        if (strlen($sqlend) < 8) {
            throw new Exception("No Primary value was set to update.");
            return null;
        }
        $lastUpdatedBy = isset($data->syslastupdatedby) ? $data->syslastupdatedby : "anonymous";
        $sqlStart .= "sysversionid=" . date("YmdHis") . ",sysupdated=" . time() . ",sysviewobject=" . (int)$data->sysviewobject . ",syslastupdatedby='" . $this->escapeSqlValue($lastUpdatedBy) . "'";
        $dout->sql = rtrim($sqlStart, ",") . rtrim($sqlend, "and ") . ";\n";
        $dout->data = $data;
        return $dout;
    }

    private function generateInsertSQL($namespace, $tableSchema, $data)
    {
        $sql = array();
        $user=Auth::Autendicate();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                # code...
                $std = (object)clone($value);
                if(empty($std->sysviewobject)){
                    $std->sysviewobject=0;
                }
                if(isset($user->userid)){
                    $std->syscreatedby=$user->userid;
                }else{
                    $std->syscreatedby="anonymous";
                }
                array_push($sql, $this->generateSingleInsert($namespace, $tableSchema, $std));
            }
        } else {
            $std = (object)clone($data);
            if(empty($std->sysviewobject)){
                $std->sysviewobject=0;
            }
            if(isset($user->userid)){
                $std->syscreatedby=$user->userid;
            }else{
                $std->syscreatedby="anonymous";
            }
            array_push($sql, $this->generateSingleInsert($namespace, $tableSchema, $std));
        }
        return $sql;
    }

    private function generateSingleInsert($namespace, $tableSchema, $data)
    {
        $sqlStart = "insert into " . $namespace . "(";
        $sqlend = " values(";
        foreach ($tableSchema->fields as $value) {
            if (isset($data->{$value->fieldName})) {
                $sqlStart .= "`" . $value->fieldName . "`,";
                $sqlend .= $this->getValue($value, $data->{$value->fieldName}) . ",";
            }
        }

        $sqlStart .= "sysversionid,syscreated,syscreatedby,sysviewobject";
        $sqlend .= date("YmdHis") . "," . time() . ",'" . $this->escapeSqlValue($data->syscreatedby) . "'," . (int)$data->sysviewobject;

        return rtrim($sqlStart, ",") . ")" . rtrim($sqlend, ",") . ");\n";
    }

    private function createTable($namespace)
    {
        $tableSchema = clone(Schema::Get($namespace));
        $systemFields = Schema::GetSystemColumns();
        foreach ($systemFields as $key => $value) {
            # code...
            $can = true;
            foreach ($tableSchema->fields as $key => $vt) {
                if ($vt->fieldName == $value->fieldName) {
                    $can = false;
                }
                # code...
            }
            if ($can)
                array_push($tableSchema->fields, $value);
        }

        $update = false;
        if ($result = $this->con->query("SHOW TABLES LIKE '" . $namespace . "'")) {
            if ($result->num_rows == 1) {
                $update = true;
            }
        }
        $sql = "";
        if ($update) {
            if ($result = $this->con->query("DESCRIBE " . $namespace . "")) {
                $colums = mysqli_fetch_all($result);


                foreach ($tableSchema->fields as $value) {
                    $has = false;
                    $alter = false;
                    $existingType = "";
                    foreach ($colums as $key => $row) {
                        if (strtolower($row[0]) == strtolower($value->fieldName)) {
                            $has = true;
                            $existingType = isset($row[1]) ? strtolower(trim(strval($row[1]))) : "";
                        }
                    }
                    $decimalPoints = !empty($value->annotations->decimalPoints) ? $value->annotations->decimalPoints : "10,2";
                    if ($has && $this->decimalColumnNeedsAlter($value->dataType, $existingType, $decimalPoints)) {
                        $alter = true;
                    }
                    if (!$has) {
                        $sql .= "ADD `" . $value->fieldName . "` " . $this->convertSQLtype(
                            $value->dataType,
                            (!empty($value->annotations->maxLen) ? $value->annotations->maxLen : 0),
                            (!empty($value->annotations->isPrimary) ? false : true),
                            (!empty($value->annotations->autoIncrement) ? $value->annotations->autoIncrement : false),
                            (!empty($value->annotations->decimalPoints) ? $value->annotations->decimalPoints : "10,2"),
                            (!empty($value->annotations->encoding) ? $value->annotations->encoding : false),
                            (isset($value->annotations->default) ? $value->annotations->default : null)
                        ) . "";
                        $sql .= ",\n";
                    }
                    if ($alter) {
                        $sql .= "MODIFY `" . $value->fieldName . "` " . $this->convertSQLtype(
                            $value->dataType,
                            (!empty($value->annotations->maxLen) ? $value->annotations->maxLen : 0),
                            (!empty($value->annotations->isPrimary) ? false : true),
                            (!empty($value->annotations->autoIncrement) ? $value->annotations->autoIncrement : false),
                            (!empty($value->annotations->decimalPoints) ? $value->annotations->decimalPoints : "10,2"),
                            (!empty($value->annotations->encoding) ? $value->annotations->encoding : false),
                            (isset($value->annotations->default) ? $value->annotations->default : null)
                        ) . "";
                        $sql .= ",\n";
                    }
                }


                if ($sql != "") {
                    $sql = rtrim($sql, ",\n");
                    $sql = "Alter Table `" . $namespace . "` " . $sql . ";";
                }
            }
        } else {
            $sql = "Create Table `" . $namespace . "`(";
            $primary = " PRIMARY KEY(";
            foreach ($tableSchema->fields as $value) {
                        $sql .= "`" . $value->fieldName . "` " . $this->convertSQLtype(
                            $value->dataType,
                            (!empty($value->annotations->maxLen) ? $value->annotations->maxLen : 0),
                            (!empty($value->annotations->isPrimary) ? false : true),
                            (!empty($value->annotations->autoIncrement) ? $value->annotations->autoIncrement : false),
                            (!empty($value->annotations->decimalPoints) ? $value->annotations->decimalPoints : "10,2"),
                            (!empty($value->annotations->encoding) ? $value->annotations->encoding : false),
                            (isset($value->annotations->default) ? $value->annotations->default : null)
                        ) . ",";
                if (empty($value->annotations->isPrimary) == false) {
                    if ($value->annotations->isPrimary) {
                        $primary .= "`" . $value->fieldName . "`,";
                    }
                }
            }
            //$sql=rtrim($sql,",\n");
            $sql .= rtrim($primary, ",") . "));";
        }
        if ($sql != "") {
            if ($this->con->query($sql) === TRUE) {
                return true;
            } else {
                throw new Exception("Schema sync failed for '".$namespace."': ".$this->con->error.". SQL: ".$sql);
            }
        }
        return true;
    }

    private function result($successful, $data = null, $message = "", $numberOfRecords = null, $pageNumber = null, $pageSize = null)
    {
        $result = new stdClass();
        $result->success = $successful;
        if ($successful) {
            $result->result = isset($data)?$data:[];
            if(isset($pageNumber)){
                $result->pageNumber=$pageNumber;
            }
            if(isset($numberOfRecords)){
                $result->numberOfRecords=$numberOfRecords;
            }
            if(isset($pageSize)){
                $result->pageSize=$pageSize;
            }
            if(!empty($message)){
                $result->message = $message;
            }
        } else {
            $result->message = $message;
        }
        return $result;
    }

    private function convertSQLtype($datatype, $datalength, $isNull, $isAutoIncrement, $decimalPoints, $endCoding,$default=null)
    {
        $strValue = "";
        $decimalPoints = $this->normalizeDecimalPoints($decimalPoints);

        switch ($datatype) {
            case "int":
                $strValue = "INT " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");
                if ($isAutoIncrement)
                    $strValue .= "AUTO_INCREMENT ";
                break;
            case "float":
                $strValue = "FLOAT " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");

                break;
            case "double":
                $strValue = "DECIMAL(" . $decimalPoints . ") " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");
                break;
            case "short":
                $strValue = "BIGINT " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");
                break;
            case "long":
                $strValue = "BIGINT " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");
                break;
            case "decimal":
                $strValue = "DECIMAL(" . $decimalPoints . ") " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");
                break;
            case "java.lang.String":
                if ($datalength == 0) {
                    $strValue = "TEXT " . ($endCoding == null || $endCoding == "" ? "" : " CHARACTER SET '" . $endCoding . "' ") . " " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");
                } else if ($datalength < 3072) {
                    if ($endCoding == null || $endCoding == "") {
                        $strValue = "VARCHAR(" . ($datalength) . ") " . (($isNull) ? "" : "NOT") . " NULL";
                    } else {
                        $strValue = "MEDIUMTEXT " . ($endCoding == null || $endCoding == "" ? "" : " CHARACTER SET '" . $endCoding . "' ") . " " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");
                    }
                } else {
                    $strValue = "TEXT " . ($endCoding == null || $endCoding == "" ? "" : " CHARACTER SET '" . $endCoding . "' ") . " " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");
                }
                break;
            case "java.util.Date":
                $strValue = "DATETIME " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");
                break;
            case "boolean":
                $strValue = "VARCHAR(5) " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");
                break;
            default:
                $strValue = "TEXT " . (($isNull) ? "" : "NOT") . " NULL ".(isset($default)?"DEFAULT '$default' ":"");
                break;
        }
        return $strValue;
    }

    private function normalizeDecimalPoints($decimalPoints)
    {
        $value = trim(strval($decimalPoints));
        if (!preg_match('/^(\d{1,2})\s*,\s*(\d{1,2})$/', $value, $matches)) {
            return "10,2";
        }
        $precision = intval($matches[1]);
        $scale = intval($matches[2]);
        if ($precision < 1 || $precision > 65 || $scale < 0 || $scale > 30 || $scale > $precision) {
            return "10,2";
        }
        return $precision . "," . $scale;
    }

    private function decimalColumnNeedsAlter($dataType, $existingType, $decimalPoints)
    {
        if ($dataType !== "double" && $dataType !== "decimal") {
            return false;
        }
        return strtolower(trim(strval($existingType))) !== "decimal(" . $this->normalizeDecimalPoints($decimalPoints) . ")";
    }

    private function getValueToObject($field, $value)
    {
        switch ($field->dataType) {
            case "java.lang.String":
                return iconv(mb_detect_encoding($value, mb_detect_order(), true), "UTF-8", $value);;
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
                return $value === null ? null : (float)$value;
                break;
            case "java.util.Date":
                return date('m-d-Y H:i:s', strtotime($value));
                break;
            case "object":
                return json_decode($value);
                break;
            default:
                return $value;
                break;
        }
    }

    private function getValue($field, $value)
    {
        switch ($field->dataType) {
            case "java.lang.String":
                return "'" . $this->escapeSqlValue($value) . "'";
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
                return $value === null ? "NULL" : (float)$value;
                break;
            case "java.util.Date":
                return "'" . date('Y-m-d H:i:s', strtotime($value)) . "'";
                break;
            case "object":
                return "'" . $this->escapeSqlValue(json_encode($value)) . "'";
                break;
            default:
                return "'" . $this->escapeSqlValue($value) . "'";
                break;
        }
    }

    private function escapeSqlValue($value)
    {
        if ($value === null) {
            return "";
        }
        return mysqli_real_escape_string($this->con, (string)$value);
    }
}

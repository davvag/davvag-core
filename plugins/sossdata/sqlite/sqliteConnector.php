<?php

require_once dirname(__FILE__) . "/schema.php";
require_once dirname(__FILE__) . "/../SOSSDataQueryFirewall.php";

class sqliteConnector
{
    private $con = null;
    private $databasePath = null;

    public function Open($tenantName = null)
    {
        if (!extension_loaded("pdo_sqlite")) {
            throw new RuntimeException("The PDO SQLite extension is not enabled.");
        }

        if ($tenantName === null || trim((string)$tenantName) === "") {
            $tenantName = defined("DATASTORE_DOMAIN") ? DATASTORE_DOMAIN : "default";
        }

        $databaseDirectory = defined("SQLITE_DATABASE_DIRECTORY")
            ? SQLITE_DATABASE_DIRECTORY
            : dirname(__FILE__) . DIRECTORY_SEPARATOR . "databases";

        $databaseDirectory = rtrim((string)$databaseDirectory, "/\\");
        if ($databaseDirectory === "") {
            throw new RuntimeException("The SQLite database directory is not configured.");
        }

        if (!is_dir($databaseDirectory) && !mkdir($databaseDirectory, 0775, true) && !is_dir($databaseDirectory)) {
            throw new RuntimeException("Unable to create the SQLite database directory: " . $databaseDirectory);
        }
        if (!is_writable($databaseDirectory)) {
            throw new RuntimeException("The SQLite database directory is not writable: " . $databaseDirectory);
        }

        $databaseName = $this->safeTenantName($tenantName);
        $this->databasePath = $databaseDirectory . DIRECTORY_SEPARATOR . $databaseName . ".sqlite";
        if (file_exists($this->databasePath) && !is_file($this->databasePath)) {
            throw new RuntimeException("The SQLite database path is not a file: " . $this->databasePath);
        }

        // Opening a filesystem SQLite DSN creates the database file when it does not exist.
        try {
            $this->con = new PDO("sqlite:" . $this->databasePath);
        } catch (PDOException $e) {
            $this->con = null;
            throw new RuntimeException("Unable to open or create the SQLite database: " . $e->getMessage(), 0, $e);
        }

        clearstatcache(true, $this->databasePath);
        if (!is_file($this->databasePath)) {
            $this->con = null;
            throw new RuntimeException("SQLite did not create the database file: " . $this->databasePath);
        }

        $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->con->exec("PRAGMA foreign_keys = ON");
        $this->con->exec("PRAGMA busy_timeout = 5000");
    }

    public function Close()
    {
        $this->con = null;
    }

    public function ExecuteRaw($namespace, $params)
    {
        try {
            $this->ConOK();
            $namespace = SOSSDataQueryFirewall::validateNamespace($namespace);
            SOSSDataQueryFirewall::validateRawRequest($params);
            $tableSchema = SQLiteSchema::Get($namespace);
            if (!isset($tableSchema->rawquery)) {
                throw new Exception("This is not a valid raw-query schema.");
            }

            $parameterValues = is_object($params) ? $params->parameters : $params["parameters"];
            $declaredParameters = isset($tableSchema->rawquery->parameters)
                ? $tableSchema->rawquery->parameters
                : array();
            $compiled = SOSSDataQueryFirewall::compileRawQuery(
                $tableSchema->rawquery->query,
                $parameterValues,
                $declaredParameters
            );

            $statement = $this->con->prepare($compiled->sql);
            $statement->execute(array_values($compiled->values));
            if ($statement->columnCount() === 0) {
                return $this->result(false, null, "Raw query did not return a result set.");
            }

            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            $data = array();
            foreach ($rows as $row) {
                $item = new stdClass();
                foreach ($tableSchema->fields as $field) {
                    $item->{$field->fieldName} = array_key_exists($field->fieldName, $row)
                        ? $row[$field->fieldName]
                        : $field->fieldName . " not found.";
                }
                $data[] = $item;
            }
            return $this->result(true, $data);
        } catch (Throwable $e) {
            return $this->result(false, null, $e->getMessage());
        }
    }

    public function Query($namespace, $param, $lastID = 0, $sorting = "DESC", $pageSize = 20, $fromPage = 0, $viewObject = true)
    {
        try {
            $this->ConOK();
            $namespace = SOSSDataQueryFirewall::validateNamespace($namespace);
            $sorting = SOSSDataQueryFirewall::normalizeDirection($sorting);
            $pageSize = SOSSDataQueryFirewall::normalizePageSize($pageSize);
            $fromPage = SOSSDataQueryFirewall::normalizeOffset($fromPage);
            $lastID = SOSSDataQueryFirewall::normalizeLastVersionId($lastID);
            $this->ensureNamespaceReady($namespace);

            if (is_string($param)) {
                $param = urldecode($param);
                $decoded = json_decode($param, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $param = $decoded;
                }
            } elseif (is_object($param)) {
                $param = (array)$param;
            }
            $param = SOSSDataQueryFirewall::validateQuery($param);

            if (is_array($param)) {
                return $this->advancedQuery($namespace, $param, $lastID, $sorting, $pageSize, $fromPage, $viewObject);
            }
            return $this->simpleQuery($namespace, $param, $lastID, $sorting, $pageSize, $fromPage, $viewObject);
        } catch (Throwable $e) {
            return $this->result(false, array(), $e->getMessage());
        }
    }

    public function Insert($namespace, $data)
    {
        try {
            $this->ConOK();
            $namespace = SOSSDataQueryFirewall::validateNamespace($namespace);
            $this->ensureNamespaceReady($namespace);
            $schema = SQLiteSchema::Get($namespace);
            $items = is_array($data) ? $data : array($data);
            $results = array();

            foreach ($items as $value) {
                if (!is_object($value) && !is_array($value)) {
                    throw new InvalidArgumentException("Insert data must be an object or array of objects.");
                }
                $item = is_object($value) ? clone $value : (object)$value;
                $item->sysviewobject = empty($item->sysviewobject) ? 0 : (int)$item->sysviewobject;
                $item->syscreatedby = $this->currentUserId();

                $columns = array();
                $placeholders = array();
                $values = array();
                foreach ($schema->fields as $field) {
                    if (property_exists($item, $field->fieldName)) {
                        $columns[] = $this->quoteIdentifier($field->fieldName);
                        $placeholders[] = "?";
                        $values[] = $this->valueForDatabase($field, $item->{$field->fieldName});
                    }
                }

                $columns = array_merge($columns, array(
                    $this->quoteIdentifier("sysversionid"),
                    $this->quoteIdentifier("syscreated"),
                    $this->quoteIdentifier("syscreatedby"),
                    $this->quoteIdentifier("sysviewobject")
                ));
                $placeholders = array_merge($placeholders, array("?", "?", "?", "?"));
                $values = array_merge($values, array($this->versionId(), time(), $item->syscreatedby, $item->sysviewobject));

                $sql = "INSERT INTO " . $this->quoteIdentifier($namespace)
                    . " (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
                $statement = $this->con->prepare($sql);
                $statement->execute($values);

                $insertResult = new stdClass();
                $insertResult->success = true;
                $insertResult->generatedId = (int)$this->con->lastInsertId();
                $results[] = $insertResult;
            }

            return $this->result(true, count($results) === 1 ? $results[0] : $results);
        } catch (Throwable $e) {
            return $this->result(false, null, $e->getMessage());
        }
    }

    public function Update($namespace, $data)
    {
        try {
            $this->ConOK();
            $namespace = SOSSDataQueryFirewall::validateNamespace($namespace);
            $this->ensureNamespaceReady($namespace);
            $schema = SQLiteSchema::Get($namespace);
            $items = is_array($data) ? $data : array($data);
            $results = array();

            foreach ($items as $value) {
                if (!is_object($value) && !is_array($value)) {
                    throw new InvalidArgumentException("Update data must be an object or array of objects.");
                }
                $item = is_object($value) ? clone $value : (object)$value;
                $item->sysviewobject = empty($item->sysviewobject) ? 0 : (int)$item->sysviewobject;
                $item->syslastupdatedby = $this->currentUserId();

                $setParts = array();
                $setValues = array();
                $whereParts = array();
                $whereValues = array();
                foreach ($schema->fields as $field) {
                    if ($this->annotation($field, "isPrimary", false)) {
                        if (!property_exists($item, $field->fieldName)) {
                            throw new Exception("No Primary value was set to update.");
                        }
                        $whereParts[] = $this->quoteIdentifier($field->fieldName) . " = ?";
                        $whereValues[] = $this->valueForDatabase($field, $item->{$field->fieldName});
                    }
                    if (property_exists($item, $field->fieldName)) {
                        $setParts[] = $this->quoteIdentifier($field->fieldName) . " = ?";
                        $setValues[] = $this->valueForDatabase($field, $item->{$field->fieldName});
                    }
                }
                if (count($whereParts) === 0) {
                    throw new Exception("No Primary value was set to update.");
                }

                $setParts[] = $this->quoteIdentifier("sysversionid") . " = ?";
                $setValues[] = $this->versionId();
                $setParts[] = $this->quoteIdentifier("sysupdated") . " = ?";
                $setValues[] = time();
                $setParts[] = $this->quoteIdentifier("sysviewobject") . " = ?";
                $setValues[] = $item->sysviewobject;
                $setParts[] = $this->quoteIdentifier("syslastupdatedby") . " = ?";
                $setValues[] = $item->syslastupdatedby;

                $sql = "UPDATE " . $this->quoteIdentifier($namespace)
                    . " SET " . implode(", ", $setParts)
                    . " WHERE " . implode(" AND ", $whereParts);
                $statement = $this->con->prepare($sql);
                $statement->execute(array_merge($setValues, $whereValues));
                $results[] = $item;
            }

            return $this->result(true, count($results) === 1 ? $results[0] : $results);
        } catch (Throwable $e) {
            return $this->result(false, null, $e->getMessage());
        }
    }

    public function Delete($namespace, $data)
    {
        try {
            $this->ConOK();
            $namespace = SOSSDataQueryFirewall::validateNamespace($namespace);
            $this->ensureNamespaceReady($namespace);
            $schema = SQLiteSchema::Get($namespace);
            $items = is_array($data) ? $data : array($data);
            $results = array();
            $successful = true;
            $message = "";

            foreach ($items as $value) {
                if (!is_object($value) && !is_array($value)) {
                    throw new InvalidArgumentException("Delete data must be an object or array of objects.");
                }
                $item = is_object($value) ? clone $value : (object)$value;
                list($whereParts, $whereValues) = $this->deleteConditions($schema, $item);
                $sql = "DELETE FROM " . $this->quoteIdentifier($namespace)
                    . " WHERE " . implode(" AND ", $whereParts);
                $statement = $this->con->prepare($sql);
                $statement->execute($whereValues);

                if ($statement->rowCount() > 0) {
                    $results[] = $item;
                } else {
                    $successful = false;
                    $message = "Not Deleted";
                    $item->{"_ErrorMessage"} = $message;
                    $results[] = $item;
                }
            }

            return $this->result($successful, count($results) === 1 ? $results[0] : $results, $message);
        } catch (Throwable $e) {
            return $this->result(false, null, $e->getMessage());
        }
    }

    public function SetViewObject($objectID = 0)
    {
        return true;
    }

    private function simpleQuery($namespace, $param, $lastID, $sorting, $pageSize, $fromPage, $viewObject)
    {
        $schema = SQLiteSchema::Get($namespace);
        $fieldMap = $this->fieldMap($schema, true);
        $whereParts = array();
        $values = array();

        if ($param !== "") {
            foreach (explode(",", $param) as $condition) {
                $parts = explode(":", $condition, 2);
                if (count($parts) !== 2) {
                    continue;
                }
                $fieldName = trim($parts[0]);
                $fieldKey = strtolower($fieldName);
                if (!isset($fieldMap[$fieldKey])) {
                    throw new Exception("Column [" . $fieldName . "] not found.");
                }
                $whereParts[] = $this->quoteIdentifier($fieldMap[$fieldKey]->fieldName) . " = ?";
                $values[] = $this->valueForDatabase($fieldMap[$fieldKey], $parts[1]);
            }
        }

        if ($lastID !== null) {
            $whereParts[] = $this->quoteIdentifier("sysversionid") . ($sorting === "ASC" ? " > ?" : " < ?");
            $values[] = $lastID;
        }
        $this->appendViewObjectCondition($whereParts, $values, $viewObject);

        return $this->runQuery(
            $namespace,
            $schema,
            $whereParts,
            $values,
            array($this->quoteIdentifier("sysversionid") . " " . $sorting),
            $pageSize,
            $fromPage
        );
    }

    private function advancedQuery($namespace, $param, $lastID, $sorting, $pageSize, $fromPage, $viewObject)
    {
        $schema = SQLiteSchema::Get($namespace);
        $fieldMap = $this->fieldMap($schema, true);
        $conditions = $this->getOption($param, array("conditions", "condition"), array());
        if ($conditions === null) {
            $conditions = array();
        }
        if (is_object($conditions)) {
            $conditions = (array)$conditions;
        }
        if (!is_array($conditions)) {
            throw new Exception("Condition must be an array.");
        }
        if ($this->hasOption($conditions, array("column", "coloumn"))) {
            $conditions = array($conditions);
        }

        $allowedOperators = array(
            "=" => "=", "==" => "=", "!=" => "!=", "<>" => "<>",
            ">" => ">", ">=" => ">=", "<" => "<", "<=" => "<=",
            "LIKE" => "LIKE", "NOT LIKE" => "NOT LIKE", "IN" => "IN",
            "NOT IN" => "NOT IN", "IS NULL" => "IS NULL", "IS NOT NULL" => "IS NOT NULL"
        );
        $whereParts = array();
        $values = array();

        foreach ($conditions as $condition) {
            if (is_object($condition)) {
                $condition = (array)$condition;
            }
            if (!is_array($condition)) {
                throw new Exception("Each condition must be an object or array.");
            }

            $column = $this->getOption($condition, array("column", "coloumn"), null);
            $operator = strtoupper(trim(preg_replace('/\s+/', ' ', (string)$this->getOption($condition, array("condition", "operator"), "="))));
            $valueExists = $this->hasOption($condition, array("value"));
            $value = $this->getOption($condition, array("value"), null);
            $fieldKey = is_string($column) ? strtolower(trim($column)) : "";

            if (!isset($fieldMap[$fieldKey])) {
                throw new Exception("Column [" . (is_scalar($column) ? $column : "") . "] not found.");
            }
            if (!isset($allowedOperators[$operator])) {
                throw new Exception("Condition operator [" . $operator . "] is not supported.");
            }

            $operator = $allowedOperators[$operator];
            $field = $fieldMap[$fieldKey];
            $quotedColumn = $this->quoteIdentifier($field->fieldName);
            if ($operator === "IS NULL" || $operator === "IS NOT NULL") {
                $whereParts[] = $quotedColumn . " " . $operator;
                continue;
            }
            if (!$valueExists) {
                throw new Exception("Condition value is required for column [" . $field->fieldName . "].");
            }
            if ($value === null) {
                if ($operator === "=") {
                    $whereParts[] = $quotedColumn . " IS NULL";
                } elseif ($operator === "!=" || $operator === "<>") {
                    $whereParts[] = $quotedColumn . " IS NOT NULL";
                } else {
                    throw new Exception("Null can only be used with =, !=, <>, IS NULL, or IS NOT NULL.");
                }
                continue;
            }
            if ($operator === "IN" || $operator === "NOT IN") {
                if (!is_array($value) || count($value) === 0) {
                    throw new Exception("Condition value for [" . $operator . "] must be a non-empty array.");
                }
                $placeholders = array();
                foreach ($value as $singleValue) {
                    if ($singleValue === null) {
                        $placeholders[] = "NULL";
                    } else {
                        $placeholders[] = "?";
                        $values[] = $this->valueForDatabase($field, $singleValue);
                    }
                }
                $whereParts[] = $quotedColumn . " " . $operator . " (" . implode(", ", $placeholders) . ")";
            } else {
                $whereParts[] = $quotedColumn . " " . $operator . " ?";
                $values[] = $this->valueForDatabase($field, $value);
            }
        }

        $defaultDirection = SOSSDataQueryFirewall::normalizeDirection($sorting);
        $queryDirection = $this->getOption($param, array("sortDirection", "sortingDirection", "direction"), null);
        if ($queryDirection !== null) {
            $defaultDirection = SOSSDataQueryFirewall::normalizeDirection($queryDirection);
        }

        $sortItems = $this->getOption($param, array("sorting", "sort"), array());
        if ($sortItems === null || $sortItems === array()) {
            $sortItems = array("sysversionid");
        } elseif (!is_array($sortItems)) {
            $sortItems = array($sortItems);
        } elseif ($this->hasOption($sortItems, array("column", "coloumn"))) {
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
                $sortColumn = $this->getOption($sortItem, array("column", "coloumn"), null);
                $itemDirection = $this->getOption($sortItem, array("direction", "sorting"), null);
                if ($itemDirection !== null) {
                    $sortDirection = strtoupper(trim((string)$itemDirection));
                }
            } elseif (!is_int($sortKey)) {
                $sortColumn = $sortKey;
                $sortDirection = strtoupper(trim((string)$sortItem));
            } else {
                $sortText = trim((string)$sortItem);
                if (preg_match('/^(.+?)\s+(ASC|DESC)$/i', $sortText, $matches)) {
                    $sortColumn = trim($matches[1]);
                    $sortDirection = strtoupper($matches[2]);
                } elseif (substr($sortText, 0, 1) === "-") {
                    $sortColumn = substr($sortText, 1);
                    $sortDirection = "DESC";
                } else {
                    $sortColumn = $sortText;
                }
            }

            $fieldKey = is_string($sortColumn) ? strtolower(trim($sortColumn)) : "";
            if (!isset($fieldMap[$fieldKey])) {
                throw new Exception("Sorting column [" . (is_scalar($sortColumn) ? $sortColumn : "") . "] not found.");
            }
            if ($sortDirection !== "ASC" && $sortDirection !== "DESC") {
                throw new Exception("Sorting direction must be ASC or DESC.");
            }
            $orderParts[] = $this->quoteIdentifier($fieldMap[$fieldKey]->fieldName) . " " . $sortDirection;
        }

        $pageSize = SOSSDataQueryFirewall::normalizePageSize($this->getOption($param, array("pageSize"), $pageSize));
        $fromPage = SOSSDataQueryFirewall::normalizeOffset($this->getOption($param, array("pageFrom"), $fromPage));
        if ($lastID !== null) {
            $whereParts[] = $this->quoteIdentifier("sysversionid") . ($defaultDirection === "ASC" ? " > ?" : " < ?");
            $values[] = $lastID;
        }
        $this->appendViewObjectCondition($whereParts, $values, $viewObject);

        return $this->runQuery($namespace, $schema, $whereParts, $values, $orderParts, $pageSize, $fromPage);
    }

    private function runQuery($namespace, $schema, $whereParts, $values, $orderParts, $pageSize, $fromPage)
    {
        $baseSql = "SELECT * FROM " . $this->quoteIdentifier($namespace);
        if (count($whereParts) > 0) {
            $baseSql .= " WHERE " . implode(" AND ", $whereParts);
        }

        $countStatement = $this->con->prepare("SELECT COUNT(*) FROM (" . $baseSql . ") AS tmp1982");
        $countStatement->execute($values);
        $numberOfRecords = (int)$countStatement->fetchColumn();

        $sql = $baseSql . " ORDER BY " . implode(", ", $orderParts)
            . " LIMIT " . (int)$pageSize . " OFFSET " . (int)$fromPage;
        $statement = $this->con->prepare($sql);
        $statement->execute($values);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $data = array();
        $systemFields = SQLiteSchema::GetSystemColumns();

        foreach ($rows as $row) {
            $item = new stdClass();
            foreach ($schema->fields as $field) {
                $item->{$field->fieldName} = $this->valueForObject(
                    $field,
                    array_key_exists($field->fieldName, $row) ? $row[$field->fieldName] : null
                );
            }
            $item->{"@meta"} = new stdClass();
            foreach ($systemFields as $field) {
                $item->{"@meta"}->{$field->fieldName} = array_key_exists($field->fieldName, $row)
                    ? $row[$field->fieldName]
                    : null;
            }
            $data[] = $item;
        }

        return $this->result(true, $data, "", $numberOfRecords, $fromPage, $pageSize);
    }

    private function appendViewObjectCondition(&$whereParts, &$values, $viewObject)
    {
        if (!$viewObject || !class_exists("Auth") || !is_callable(array("Auth", "ViewObjects"))) {
            return;
        }
        $objects = Auth::ViewObjects();
        if (!is_array($objects) || count($objects) === 0) {
            return;
        }

        $whereParts[] = $this->quoteIdentifier("sysviewobject")
            . " IN (" . implode(", ", array_fill(0, count($objects), "?")) . ")";
        foreach ($objects as $object) {
            $values[] = (int)$object;
        }
    }

    private function fieldMap($schema, $includeSystemFields)
    {
        $fields = $schema->fields;
        if ($includeSystemFields) {
            $fields = array_merge($fields, SQLiteSchema::GetSystemColumns());
        }
        $map = array();
        foreach ($fields as $field) {
            $map[strtolower($field->fieldName)] = $field;
        }
        return $map;
    }

    private function deleteConditions($schema, $item)
    {
        $primaryFields = array();
        foreach ($schema->fields as $field) {
            if ($this->annotation($field, "isPrimary", false)) {
                $primaryFields[] = $field;
            }
        }

        $candidateFields = count($primaryFields) > 0 ? $primaryFields : $schema->fields;
        $whereParts = array();
        $values = array();
        foreach ($candidateFields as $field) {
            if (!property_exists($item, $field->fieldName)) {
                if (count($primaryFields) > 0) {
                    throw new Exception("Primary value [" . $field->fieldName . "] was not set to delete.");
                }
                continue;
            }
            $value = $this->valueForDatabase($field, $item->{$field->fieldName});
            if ($value === null) {
                $whereParts[] = $this->quoteIdentifier($field->fieldName) . " IS NULL";
            } else {
                $whereParts[] = $this->quoteIdentifier($field->fieldName) . " = ?";
                $values[] = $value;
            }
        }
        if (count($whereParts) === 0) {
            throw new Exception("Delete cannot be performed because no identifying values were supplied.");
        }
        return array($whereParts, $values);
    }

    private function ensureNamespaceReady($namespace)
    {
        $this->createOrUpdateTable($namespace);
    }

    private function createOrUpdateTable($namespace)
    {
        $schema = SQLiteSchema::Get($namespace);
        $fields = $this->schemaFieldsWithSystemFields($schema);

        if (!$this->tableExists($namespace)) {
            $this->createTable($namespace, $fields);
            return;
        }

        $existingColumns = $this->tableColumns($namespace);
        foreach ($fields as $field) {
            if (isset($existingColumns[strtolower($field->fieldName)])) {
                continue;
            }

            $sql = "ALTER TABLE " . $this->quoteIdentifier($namespace)
                . " ADD COLUMN " . $this->quoteIdentifier($field->fieldName)
                . " " . $this->columnDefinition($field, false, true);
            $this->con->exec($sql);
        }
    }

    private function createTable($namespace, $fields)
    {
        $primaryFields = array();
        foreach ($fields as $field) {
            if ($this->annotation($field, "isPrimary", false)) {
                $primaryFields[] = $field->fieldName;
            }
        }

        $inlineAutoPrimary = null;
        if (count($primaryFields) === 1) {
            foreach ($fields as $field) {
                if ($field->fieldName === $primaryFields[0]
                    && $this->annotation($field, "autoIncrement", false)
                    && $this->sqliteType($field) === "INTEGER") {
                    $inlineAutoPrimary = $field->fieldName;
                    break;
                }
            }
        }

        $definitions = array();
        foreach ($fields as $field) {
            $inlinePrimary = $inlineAutoPrimary !== null && $field->fieldName === $inlineAutoPrimary;
            $definitions[] = $this->quoteIdentifier($field->fieldName)
                . " " . $this->columnDefinition($field, $inlinePrimary, false);
        }
        if (count($primaryFields) > 0 && $inlineAutoPrimary === null) {
            $quotedPrimaryFields = array();
            foreach ($primaryFields as $fieldName) {
                $quotedPrimaryFields[] = $this->quoteIdentifier($fieldName);
            }
            $definitions[] = "PRIMARY KEY (" . implode(", ", $quotedPrimaryFields) . ")";
        }

        $sql = "CREATE TABLE " . $this->quoteIdentifier($namespace)
            . " (" . implode(", ", $definitions) . ")";
        $this->con->exec($sql);
    }

    private function schemaFieldsWithSystemFields($schema)
    {
        $fields = $schema->fields;
        $known = array();
        foreach ($fields as $field) {
            $this->normalizeField($field);
            $known[strtolower($field->fieldName)] = true;
        }
        foreach (SQLiteSchema::GetSystemColumns() as $field) {
            if (!isset($known[strtolower($field->fieldName)])) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    private function tableExists($namespace)
    {
        $statement = $this->con->prepare(
            "SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = ? LIMIT 1"
        );
        $statement->execute(array($namespace));
        return $statement->fetchColumn() !== false;
    }

    private function tableColumns($namespace)
    {
        $statement = $this->con->query("PRAGMA table_info(" . $this->quoteIdentifier($namespace) . ")");
        $columns = array();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $columns[strtolower($row["name"])] = $row;
        }
        return $columns;
    }

    private function columnDefinition($field, $inlineAutoPrimary, $addingColumn)
    {
        if ($inlineAutoPrimary) {
            return "INTEGER PRIMARY KEY AUTOINCREMENT";
        }

        $definition = $this->sqliteType($field);
        $isPrimary = $this->annotation($field, "isPrimary", false);
        $hasDefault = $this->hasAnnotation($field, "default");
        if ($isPrimary && !$addingColumn) {
            $definition .= " NOT NULL";
        }
        if ($hasDefault) {
            $definition .= " DEFAULT " . $this->defaultSql($field, $this->annotation($field, "default"));
        }
        return $definition;
    }

    private function sqliteType($field)
    {
        switch ($field->dataType) {
            case "int":
            case "short":
            case "long":
            case "boolean":
                return "INTEGER";
            case "float":
            case "double":
            case "decimal":
                return "REAL";
            case "java.util.Date":
            case "java.lang.String":
            case "object":
            default:
                return "TEXT";
        }
    }

    private function defaultSql($field, $value)
    {
        if ($value === null) {
            return "NULL";
        }
        switch ($field->dataType) {
            case "int":
            case "short":
            case "long":
                return (string)(int)$value;
            case "float":
            case "double":
            case "decimal":
                return (string)(float)$value;
            case "boolean":
                return $this->toBooleanInteger($value) ? "1" : "0";
            default:
                return $this->con->quote((string)$value);
        }
    }

    private function valueForDatabase($field, $value)
    {
        if ($value === null) {
            return null;
        }
        switch ($field->dataType) {
            case "int":
            case "short":
            case "long":
                return (int)$value;
            case "float":
            case "double":
            case "decimal":
                return (float)$value;
            case "boolean":
                return $this->toBooleanInteger($value);
            case "java.util.Date":
                $timestamp = strtotime((string)$value);
                return $timestamp === false ? (string)$value : date("Y-m-d H:i:s", $timestamp);
            case "object":
                return is_string($value) ? $value : json_encode($value);
            default:
                return (string)$value;
        }
    }

    private function valueForObject($field, $value)
    {
        if ($value === null) {
            return null;
        }
        switch ($field->dataType) {
            case "int":
            case "short":
            case "long":
                return (int)$value;
            case "float":
            case "double":
            case "decimal":
                return (float)$value;
            case "boolean":
                return (bool)$value;
            case "java.util.Date":
                $timestamp = strtotime((string)$value);
                return $timestamp === false ? (string)$value : date("m-d-Y H:i:s", $timestamp);
            case "object":
                return json_decode($value);
            case "java.lang.String":
                if (function_exists("mb_detect_encoding") && function_exists("iconv")) {
                    $encoding = mb_detect_encoding($value, mb_detect_order(), true);
                    if ($encoding !== false) {
                        $converted = iconv($encoding, "UTF-8//IGNORE", $value);
                        return $converted === false ? $value : $converted;
                    }
                }
                return $value;
            default:
                return $value;
        }
    }

    private function toBooleanInteger($value)
    {
        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, array("false", "0", "no", "off", ""), true)) {
                return 0;
            }
            if (in_array($normalized, array("true", "1", "yes", "on"), true)) {
                return 1;
            }
        }
        return $value ? 1 : 0;
    }

    private function currentUserId()
    {
        if (class_exists("Auth") && is_callable(array("Auth", "Autendicate"))) {
            $user = Auth::Autendicate();
            if (is_object($user) && isset($user->userid)) {
                return (string)$user->userid;
            }
        }
        return "anonymous";
    }

    private function versionId()
    {
        return (int)date("YmdHis");
    }

    private function normalizeField($field)
    {
        if (!isset($field->annotations) || !is_object($field->annotations)) {
            $field->annotations = new stdClass();
        }
    }

    private function hasAnnotation($field, $name)
    {
        return isset($field->annotations)
            && is_object($field->annotations)
            && property_exists($field->annotations, $name);
    }

    private function annotation($field, $name, $default = null)
    {
        return $this->hasAnnotation($field, $name) ? $field->annotations->{$name} : $default;
    }

    private function getOption($options, $names, $default = null)
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

    private function hasOption($options, $names)
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

    private function safeTenantName($tenantName)
    {
        $tenantName = trim((string)$tenantName);
        $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $tenantName);
        $safeName = trim($safeName, ". ");
        if ($safeName === "") {
            throw new InvalidArgumentException("The tenant name cannot be converted to a database name.");
        }

        $reserved = array("CON", "PRN", "AUX", "NUL", "COM1", "COM2", "COM3", "COM4", "COM5", "COM6", "COM7", "COM8", "COM9", "LPT1", "LPT2", "LPT3", "LPT4", "LPT5", "LPT6", "LPT7", "LPT8", "LPT9");
        if (in_array(strtoupper($safeName), $reserved, true)) {
            $safeName = "_" . $safeName;
        }
        if (strlen($safeName) > 180) {
            $safeName = substr($safeName, 0, 140) . "_" . substr(hash("sha256", $tenantName), 0, 16);
        }
        return $safeName;
    }

    private function quoteIdentifier($identifier)
    {
        return '"' . str_replace('"', '""', (string)$identifier) . '"';
    }

    private function ConOK()
    {
        if (!($this->con instanceof PDO)) {
            throw new Exception("connection is not Open");
        }
        return true;
    }

    private function result($successful, $data = null, $message = "", $numberOfRecords = null, $pageNumber = null, $pageSize = null)
    {
        $result = new stdClass();
        $result->success = $successful;
        if ($successful) {
            $result->result = isset($data) ? $data : array();
            if (isset($pageNumber)) {
                $result->pageNumber = $pageNumber;
            }
            if (isset($numberOfRecords)) {
                $result->numberOfRecords = $numberOfRecords;
            }
            if (isset($pageSize)) {
                $result->pageSize = $pageSize;
            }
            if ($message !== "") {
                $result->message = $message;
            }
        } else {
            $result->message = $message;
        }
        return $result;
    }
}

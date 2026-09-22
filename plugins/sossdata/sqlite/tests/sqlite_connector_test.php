<?php

$testRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "sossdata-sqlite-" . bin2hex(random_bytes(6));
$schemaDirectory = $testRoot . DIRECTORY_SEPARATOR . "tenant" . DIRECTORY_SEPARATOR . "schemas";
$databaseDirectory = $testRoot . DIRECTORY_SEPARATOR . "databases";
mkdir($schemaDirectory, 0775, true);

define("TENANT_RESOURCE_LOCATION", dirname($schemaDirectory));
define("SQLITE_DATABASE_DIRECTORY", $databaseDirectory);
define("DATASTORE_DOMAIN", "tenant.example");

class Auth
{
    public static function Autendicate()
    {
        return (object)array("userid" => "sqlite-test-user");
    }

    public static function ViewObjects()
    {
        return array(0);
    }
}

$itemSchema = array(
    "fields" => array(
        array("fieldName" => "itemId", "dataType" => "int", "annotations" => array("isPrimary" => true, "autoIncrement" => true)),
        array("fieldName" => "title", "dataType" => "java.lang.String", "annotations" => array("maxLen" => 100)),
        array("fieldName" => "price", "dataType" => "decimal", "annotations" => array("decimalPoints" => "10,2")),
        array("fieldName" => "enabled", "dataType" => "boolean", "annotations" => array("default" => true)),
        array("fieldName" => "payload", "dataType" => "object", "annotations" => new stdClass())
    )
);
$rawSchema = array(
    "fields" => array(
        array("fieldName" => "itemId", "dataType" => "int", "annotations" => new stdClass()),
        array("fieldName" => "title", "dataType" => "java.lang.String", "annotations" => new stdClass())
    ),
    "rawquery" => array(
        "query" => "SELECT itemId, title FROM items WHERE title = \$title",
        "parameters" => array("title")
    )
);
file_put_contents($schemaDirectory . DIRECTORY_SEPARATOR . "items.json", json_encode($itemSchema));
file_put_contents($schemaDirectory . DIRECTORY_SEPARATOR . "item_search.json", json_encode($rawSchema));

$failures = 0;
function checkSQLiteConnector($condition, $message)
{
    global $failures;
    if (!$condition) {
        $failures++;
        echo "FAIL: " . $message . PHP_EOL;
    }
}

$tenant = DATASTORE_DOMAIN;
$GLOBALS["ENGINE_CONFIG"] = (object)array(
    "DAVVAG_DATA" => (object)array(
        $tenant => (object)array("connector" => "sqlite")
    )
);
$originalDirectory = getcwd();
chdir(dirname(__FILE__) . "/../..");
require_once "SOSSData.php";

$insert = SOSSData::Insert("items", (object)array(
    "title" => "First item",
    "price" => 12.5,
    "enabled" => true,
    "payload" => (object)array("source" => "test")
), $tenant);
checkSQLiteConnector($insert->success === true, "insert succeeds");
checkSQLiteConnector($insert->result->generatedId === 1, "auto-increment ID is returned");
checkSQLiteConnector(file_exists($databaseDirectory . DIRECTORY_SEPARATOR . $tenant . ".sqlite"), "tenant name is used as database filename");

$query = SOSSData::Query("items", "", null, "ASC", 20, 0, $tenant, false);
checkSQLiteConnector($query->success === true && count($query->result) === 1, "simple query returns inserted row");
checkSQLiteConnector($query->result[0]->payload->source === "test", "object fields are decoded");
checkSQLiteConnector($query->result[0]->enabled === true, "boolean fields retain their type");

$advanced = SOSSData::Query("items", array(
    "conditions" => array(array("column" => "price", "operator" => ">=", "value" => 10)),
    "sorting" => array(array("column" => "itemId", "direction" => "DESC"))
), null, "ASC", 20, 0, $tenant, false);
checkSQLiteConnector($advanced->success === true && $advanced->numberOfRecords === 1, "advanced query works");

$update = SOSSData::Update("items", (object)array("itemId" => 1, "title" => "Updated item"), $tenant);
checkSQLiteConnector($update->success === true, "update succeeds");

$raw = SOSSData::ExecuteRaw("item_search", (object)array(
    "parameters" => (object)array("title" => "Updated item")
), null, $tenant);
checkSQLiteConnector($raw->success === true && count($raw->result) === 1, "parameterized raw query succeeds");

$delete = SOSSData::Delete("items", (object)array("itemId" => 1), $tenant);
checkSQLiteConnector($delete->success === true, "delete succeeds");
$afterDelete = SOSSData::Query("items", "", null, "ASC", 20, 0, $tenant, false);
checkSQLiteConnector($afterDelete->success === true && count($afterDelete->result) === 0, "deleted row is absent");
SOSSData::Close($tenant);
chdir($originalDirectory);

foreach (glob($databaseDirectory . DIRECTORY_SEPARATOR . "*") as $file) {
    unlink($file);
}
unlink($schemaDirectory . DIRECTORY_SEPARATOR . "items.json");
unlink($schemaDirectory . DIRECTORY_SEPARATOR . "item_search.json");
rmdir($schemaDirectory);
rmdir(dirname($schemaDirectory));
rmdir($databaseDirectory);
rmdir($testRoot);

if ($failures === 0) {
    echo "sqlite connector tests passed." . PHP_EOL;
}
exit($failures === 0 ? 0 : 1);

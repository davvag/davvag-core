<?php
require_once(__DIR__ . "/../mysqlConnector.php");

$failures = 0;
function checkSqlType($condition, $message)
{
    global $failures;
    if (!$condition) {
        $failures++;
        echo "FAIL: " . $message . PHP_EOL;
    }
}

$reflection = new ReflectionClass("mysqlConnector");
$convertSqlType = $reflection->getMethod("convertSQLtype");
$convertSqlType->setAccessible(true);
$connector = $reflection->newInstanceWithoutConstructor();

foreach (array("float", "double", "short", "long", "java.util.Date", "boolean") as $dataType) {
    $sqlType = $convertSqlType->invoke(
        $connector,
        $dataType,
        50,
        true,
        false,
        "10,2",
        "",
        "true"
    );
    checkSqlType(
        strpos($sqlType, "NULL DEFAULT 'true'") !== false,
        $dataType . " defaults have valid SQL whitespace"
    );
    checkSqlType(
        strpos($sqlType, "NULLDEFAULT") === false,
        $dataType . " defaults never concatenate NULL and DEFAULT"
    );
}

if ($failures === 0) {
    echo "mysqlConnector SQL type tests passed." . PHP_EOL;
}
exit($failures ? 1 : 0);
?>

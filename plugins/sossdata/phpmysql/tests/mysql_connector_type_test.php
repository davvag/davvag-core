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

$defaultDouble = $convertSqlType->invoke($connector, "double", 0, true, false, null, "", null);
$defaultDecimal = $convertSqlType->invoke($connector, "decimal", 0, true, false, "", "", null);
$configuredDouble = $convertSqlType->invoke($connector, "double", 0, true, false, "8,2", "", null);
$invalidPrecision = $convertSqlType->invoke($connector, "decimal", 0, true, false, "invalid", "", null);
checkSqlType(strpos($defaultDouble, "DECIMAL(10,2)") === 0, "double defaults to DECIMAL(10,2) when decimalPoints is absent");
checkSqlType(strpos($defaultDecimal, "DECIMAL(10,2)") === 0, "decimal defaults to DECIMAL(10,2) when decimalPoints is blank");
checkSqlType(strpos($configuredDouble, "DECIMAL(8,2)") === 0, "double honors the configured decimalPoints annotation");
checkSqlType(strpos($invalidPrecision, "DECIMAL(10,2)") === 0, "invalid decimalPoints safely falls back to DECIMAL(10,2)");

$decimalColumnNeedsAlter = $reflection->getMethod("decimalColumnNeedsAlter");
$decimalColumnNeedsAlter->setAccessible(true);
checkSqlType($decimalColumnNeedsAlter->invoke($connector, "double", "decimal(10,0)", "8,2") === true, "rounded legacy double columns are marked for precision alteration");
checkSqlType($decimalColumnNeedsAlter->invoke($connector, "double", "decimal(8,2)", "8,2") === false, "matching decimal columns are not altered repeatedly");

if ($failures === 0) {
    echo "mysqlConnector SQL type tests passed." . PHP_EOL;
}
exit($failures ? 1 : 0);
?>

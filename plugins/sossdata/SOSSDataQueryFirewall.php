<?php

/**
 * Validates all caller-controlled values that can affect SOSSData query shape.
 *
 * Data values are still bound or escaped by the active connector. This class
 * protects the adapter boundary: identifiers, directions, pagination, raw
 * placeholders, input size, and supported input shapes.
 */
class SOSSDataQueryFirewall
{
    const MAX_QUERY_LENGTH = 65535;
    const MAX_PAGE_SIZE = 10000;
    const MAX_CONDITIONS = 100;
    const MAX_SORT_COLUMNS = 20;
    const MAX_RAW_PARAMETERS = 100;
    const MAX_RAW_VALUE_LENGTH = 1048576;

    public static function validateNamespace($namespace)
    {
        if (!is_string($namespace) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $namespace)) {
            throw new InvalidArgumentException("Invalid SOSSData namespace.");
        }
        return $namespace;
    }

    public static function validateQuery($query)
    {
        if ($query === null) {
            return "";
        }
        if (is_object($query)) {
            $query = (array)$query;
        }
        if (is_string($query)) {
            if (strlen($query) > self::MAX_QUERY_LENGTH) {
                throw new InvalidArgumentException("Query exceeds the allowed length.");
            }
            if (strpos($query, "\0") !== false) {
                throw new InvalidArgumentException("Query contains a null byte.");
            }
            return $query;
        }
        if (!is_array($query)) {
            throw new InvalidArgumentException("Query must be a string, array, object, or null.");
        }

        self::validateAdvancedPayload($query);
        return $query;
    }

    public static function validateAdvancedPayload($payload)
    {
        if (is_object($payload)) {
            $payload = (array)$payload;
        }
        if (!is_array($payload)) {
            throw new InvalidArgumentException("Advanced query payload must be an object or array.");
        }

        $allowedKeys = array(
            "conditions", "condition", "sorting", "sort", "sortdirection",
            "sortingdirection", "direction", "pagesize", "pagefrom"
        );
        foreach ($payload as $key => $value) {
            if (!in_array(strtolower((string)$key), $allowedKeys, true)) {
                throw new InvalidArgumentException("Unsupported advanced query option [" . $key . "].");
            }
        }

        $conditions = self::getOption($payload, array("conditions", "condition"), array());
        if (is_object($conditions)) {
            $conditions = (array)$conditions;
        }
        if (is_array($conditions) && self::isDescriptor($conditions, array("column", "coloumn"))) {
            $conditions = array($conditions);
        }
        if (!is_array($conditions) || count($conditions) > self::MAX_CONDITIONS) {
            throw new InvalidArgumentException("Advanced query conditions are invalid or exceed the allowed count.");
        }

        foreach ($conditions as $condition) {
            if (is_object($condition)) {
                $condition = (array)$condition;
            }
            if (!is_array($condition)) {
                throw new InvalidArgumentException("Each advanced query condition must be an object or array.");
            }
            foreach ($condition as $key => $value) {
                if (!in_array(strtolower((string)$key), array("column", "coloumn", "operator", "condition", "value"), true)) {
                    throw new InvalidArgumentException("Unsupported condition option [" . $key . "].");
                }
            }
            $column = self::getOption($condition, array("column", "coloumn"), null);
            self::validateColumnName($column);
            $operator = strtoupper(trim(preg_replace('/\s+/', ' ', (string)self::getOption($condition, array("operator", "condition"), "="))));
            $allowedOperators = array("=", "==", "!=", "<>", ">", ">=", "<", "<=", "LIKE", "NOT LIKE", "IN", "NOT IN", "IS NULL", "IS NOT NULL");
            if (!in_array($operator, $allowedOperators, true)) {
                throw new InvalidArgumentException("Condition operator is not supported.");
            }
            $conditionValue = self::getOption($condition, array("value"), null);
            if (is_array($conditionValue) && count($conditionValue) > self::MAX_RAW_PARAMETERS) {
                throw new InvalidArgumentException("Condition value array exceeds the allowed count.");
            }
        }

        $sorting = self::getOption($payload, array("sorting", "sort"), array());
        if (is_object($sorting)) {
            $sorting = (array)$sorting;
        }
        if (is_array($sorting) && self::isDescriptor($sorting, array("column", "coloumn"))) {
            $sorting = array($sorting);
        } else if (!is_array($sorting)) {
            $sorting = array($sorting);
        }
        if (count($sorting) > self::MAX_SORT_COLUMNS) {
            throw new InvalidArgumentException("Sorting exceeds the allowed column count.");
        }
        foreach ($sorting as $key => $sortItem) {
            if (is_object($sortItem)) {
                $sortItem = (array)$sortItem;
            }
            if (is_array($sortItem)) {
                foreach ($sortItem as $sortKey => $sortValue) {
                    if (!in_array(strtolower((string)$sortKey), array("column", "coloumn", "direction", "sorting"), true)) {
                        throw new InvalidArgumentException("Unsupported sorting option [" . $sortKey . "].");
                    }
                }
                self::validateColumnName(self::getOption($sortItem, array("column", "coloumn"), null));
                $itemDirection = self::getOption($sortItem, array("direction", "sorting"), null);
                if ($itemDirection !== null) {
                    self::normalizeDirection($itemDirection);
                }
            } else if (!is_int($key)) {
                self::validateColumnName($key);
                self::normalizeDirection($sortItem);
            } else {
                $sortText = trim((string)$sortItem);
                if (!preg_match('/^-?[A-Za-z_][A-Za-z0-9_]*(?:\s+(?:ASC|DESC))?$/iD', $sortText)) {
                    throw new InvalidArgumentException("Sorting column or direction is invalid.");
                }
            }
        }

        $pageSize = self::getOption($payload, array("pageSize"), null);
        if ($pageSize !== null) {
            self::normalizePageSize($pageSize);
        }
        $pageFrom = self::getOption($payload, array("pageFrom"), null);
        if ($pageFrom !== null) {
            self::normalizeOffset($pageFrom);
        }
        $direction = self::getOption($payload, array("sortDirection", "sortingDirection", "direction"), null);
        if ($direction !== null) {
            self::normalizeDirection($direction);
        }
    }

    public static function normalizeDirection($direction)
    {
        if (!is_string($direction)) {
            throw new InvalidArgumentException("Sorting direction must be ASC or DESC.");
        }
        $direction = strtoupper(trim($direction));
        if ($direction !== "ASC" && $direction !== "DESC") {
            throw new InvalidArgumentException("Sorting direction must be ASC or DESC.");
        }
        return $direction;
    }

    public static function normalizePageSize($pageSize)
    {
        if (filter_var($pageSize, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException("Page size must be an integer.");
        }
        $pageSize = (int)$pageSize;
        if ($pageSize < 1 || $pageSize > self::MAX_PAGE_SIZE) {
            throw new InvalidArgumentException("Page size must be between 1 and " . self::MAX_PAGE_SIZE . ".");
        }
        return $pageSize;
    }

    public static function normalizeOffset($offset)
    {
        if (filter_var($offset, FILTER_VALIDATE_INT) === false || (int)$offset < 0) {
            throw new InvalidArgumentException("Page offset must be a non-negative integer.");
        }
        return (int)$offset;
    }

    public static function normalizeLastVersionId($lastVersionId)
    {
        if ($lastVersionId === null || $lastVersionId === "" || $lastVersionId === 0 || $lastVersionId === "0") {
            return null;
        }
        if (filter_var($lastVersionId, FILTER_VALIDATE_INT) === false || (int)$lastVersionId < 0) {
            throw new InvalidArgumentException("Last version ID must be a non-negative integer.");
        }
        return (int)$lastVersionId;
    }

    public static function validateRawRequest($params)
    {
        if (is_object($params)) {
            $params = (array)$params;
        }
        if (!is_array($params) || !array_key_exists("parameters", $params)) {
            throw new InvalidArgumentException("Raw query parameters are required.");
        }

        self::normalizeRawParameters($params["parameters"]);
        return $params;
    }

    public static function compileRawQuery($template, $parameters, $declaredParameters = array())
    {
        if (!is_string($template) || $template === "" || strlen($template) > self::MAX_QUERY_LENGTH) {
            throw new InvalidArgumentException("Raw query template is empty or exceeds the allowed length.");
        }
        if (strpos($template, "\0") !== false) {
            throw new InvalidArgumentException("Raw query template contains a null byte.");
        }

        $parameterMap = self::normalizeRawParameters($parameters);
        if (is_object($declaredParameters)) {
            $declaredParameters = (array)$declaredParameters;
        }
        if (!is_array($declaredParameters)) {
            throw new InvalidArgumentException("Raw query parameter declaration must be an array.");
        }

        $declaredMap = array();
        foreach ($declaredParameters as $parameterName) {
            if (!is_string($parameterName) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $parameterName)) {
                throw new InvalidArgumentException("Raw query contains an invalid parameter declaration.");
            }
            $declaredMap[$parameterName] = true;
        }
        if (count($declaredMap) > 0) {
            foreach ($parameterMap as $name => $value) {
                if (!isset($declaredMap[$name])) {
                    throw new InvalidArgumentException("Unexpected raw query parameter [" . $name . "].");
                }
            }
        }

        if (preg_match('/"[^"\r\n]*\$[A-Za-z_][A-Za-z0-9_]*[^"\r\n]*"/', $template)) {
            throw new InvalidArgumentException("Raw query placeholders inside double quotes are not supported; use single-quoted values or bare placeholders.");
        }

        $values = array();
        $types = "";
        $usedParameters = array();
        $pattern = "/'((?:[^'\\\\]|\\\\.)*\\\$[A-Za-z_][A-Za-z0-9_]*(?:[^'\\\\]|\\\\.)*)'|\\\$([A-Za-z_][A-Za-z0-9_]*)/";
        $sql = preg_replace_callback($pattern, function ($matches) use ($parameterMap, $declaredMap, &$values, &$types, &$usedParameters) {
            if (isset($matches[1]) && $matches[1] !== "") {
                $interpolated = preg_replace_callback('/\$([A-Za-z_][A-Za-z0-9_]*)/', function ($innerMatches) use ($parameterMap, $declaredMap, &$usedParameters) {
                    $name = $innerMatches[1];
                    self::assertRawParameterAvailable($name, $parameterMap, $declaredMap);
                    $usedParameters[$name] = true;
                    return (string)$parameterMap[$name];
                }, $matches[1]);
                $values[] = $interpolated;
                $types .= "s";
                return "?";
            }

            $name = $matches[2];
            self::assertRawParameterAvailable($name, $parameterMap, $declaredMap);
            $usedParameters[$name] = true;
            $value = $parameterMap[$name];
            $values[] = $value;
            $types .= self::inferBindType($value);
            return "?";
        }, $template);

        if ($sql === null || preg_match('/\$[A-Za-z_][A-Za-z0-9_]*/', $sql)) {
            throw new InvalidArgumentException("Raw query contains an unbound placeholder.");
        }
        foreach ($declaredMap as $name => $unused) {
            if (!isset($usedParameters[$name])) {
                throw new InvalidArgumentException("Declared raw query parameter [" . $name . "] is not used by the query.");
            }
        }

        $compiled = new stdClass();
        $compiled->sql = $sql;
        $compiled->types = $types;
        $compiled->values = $values;
        return $compiled;
    }

    public static function blockedResult($exception)
    {
        $result = new stdClass();
        $result->success = false;
        $result->code = "SOSS_QUERY_FIREWALL_BLOCKED";
        $result->message = "SOSSData query firewall blocked the request: " . $exception->getMessage();
        error_log($result->message);
        return $result;
    }

    private static function normalizeRawParameters($parameters)
    {
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        if (!is_array($parameters) || count($parameters) > self::MAX_RAW_PARAMETERS) {
            throw new InvalidArgumentException("Raw query parameters are invalid or exceed the allowed count.");
        }

        $normalized = array();
        foreach ($parameters as $name => $value) {
            if (!is_string($name) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name)) {
                throw new InvalidArgumentException("Raw query parameter name is invalid.");
            }
            if (!is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException("Raw query parameter [" . $name . "] must be a scalar value or null.");
            }
            if (is_string($value) && strlen($value) > self::MAX_RAW_VALUE_LENGTH) {
                throw new InvalidArgumentException("Raw query parameter [" . $name . "] exceeds the allowed length.");
            }
            $normalized[$name] = $value;
        }
        return $normalized;
    }

    private static function assertRawParameterAvailable($name, $parameterMap, $declaredMap)
    {
        if (count($declaredMap) > 0 && !isset($declaredMap[$name])) {
            throw new InvalidArgumentException("Raw query placeholder [" . $name . "] is not declared.");
        }
        if (!array_key_exists($name, $parameterMap)) {
            throw new InvalidArgumentException("Raw query parameter [" . $name . "] is missing.");
        }
    }

    private static function inferBindType($value)
    {
        if (is_int($value) || is_bool($value)) {
            return "i";
        }
        if (is_float($value)) {
            return "d";
        }
        return "s";
    }

    private static function validateColumnName($column)
    {
        if (!is_string($column) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', trim($column))) {
            throw new InvalidArgumentException("Advanced query column name is invalid.");
        }
    }

    private static function getOption($options, $names, $default = null)
    {
        $lowerNames = array_map("strtolower", $names);
        foreach ($options as $key => $value) {
            if (in_array(strtolower((string)$key), $lowerNames, true)) {
                return $value;
            }
        }
        return $default;
    }

    private static function isDescriptor($value, $keys)
    {
        $lowerKeys = array_map("strtolower", $keys);
        foreach ($value as $key => $unused) {
            if (in_array(strtolower((string)$key), $lowerKeys, true)) {
                return true;
            }
        }
        return false;
    }
}

<?php

class SQLiteSchema
{
    private static $Schema = array();

    public static function Get($name)
    {
        if (isset(self::$Schema[$name])) {
            return clone self::$Schema[$name];
        }

        $path = TENANT_RESOURCE_LOCATION . "/schemas/" . $name . ".json";
        if (!file_exists($path)) {
            throw new Exception("No Schema File Found.");
        }

        $schema = json_decode(file_get_contents($path));
        if (!$schema || !isset($schema->fields) || !is_array($schema->fields)) {
            throw new Exception("Invalid schema file.");
        }

        self::$Schema[$name] = $schema;
        return clone $schema;
    }

    public static function GetSystemColumns()
    {
        return array(
            self::field("sysversionid", "long", array("isPrimary" => false)),
            self::field("syscreated", "int"),
            self::field("sysupdated", "int"),
            self::field("sysviewobject", "int", array("default" => "0")),
            self::field("syscreatedby", "java.lang.String", array("isPrimary" => false, "maxLen" => 100)),
            self::field("syslastupdatedby", "java.lang.String", array("isPrimary" => false, "maxLen" => 100))
        );
    }

    public static function GetSystemColums()
    {
        return self::GetSystemColumns();
    }

    private static function field($name, $type, $annotations = array())
    {
        $field = new stdClass();
        $field->fieldName = $name;
        $field->dataType = $type;
        $field->annotations = (object)$annotations;
        return $field;
    }
}

# Database Schemas

DAVVAG uses schema JSON files to create and update database tables through `SOSSData` and the MySQL connector.

## Schema Location

Schemas live under the active tenant:

```text
{TENANT_RESOURCE_LOCATION}/schemas/{namespace}.json
```

Example:

```text
davvag-core/example.com/schemas/my_new_app_items.json
```

## Basic Schema

```json
{
  "fields": [
    {
      "fieldName": "id",
      "dataType": "int",
      "annotations": {
        "isPrimary": true,
        "autoIncrement": true
      }
    },
    {
      "fieldName": "title",
      "dataType": "java.lang.String",
      "annotations": {
        "maxLen": 255,
        "encoding": "utf8"
      }
    },
    {
      "fieldName": "status",
      "dataType": "java.lang.String",
      "annotations": {
        "maxLen": 50,
        "default": "Active"
      }
    },
    {
      "fieldName": "metadata",
      "dataType": "object",
      "annotations": {
        "maxLen": 2000
      }
    }
  ]
}
```

## Field Keys

| Key | Purpose |
| --- | --- |
| `fieldName` | Column/property name. |
| `dataType` | Logical field type. |
| `annotations.isPrimary` | Marks primary key fields. |
| `annotations.autoIncrement` | Enables auto increment for supported numeric fields. |
| `annotations.maxLen` | Controls string column sizing. |
| `annotations.encoding` | Optional character encoding. |
| `annotations.default` | Optional default value. |
| `annotations.decimalPoints` | Decimal precision string, for example `10,2`. |

## Common Data Types

```text
int
float
double
short
long
decimal
java.lang.String
java.util.Date
boolean
object
```

## System Columns

The framework adds these system columns:

```text
sysversionid
syscreated
sysupdated
sysviewobject
syscreatedby
syslastupdatedby
```

Do not define these manually unless you are intentionally overriding framework behavior.

## Using SOSSData

```php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");

SOSSData::Insert("my_new_app_items", $data);
SOSSData::Update("my_new_app_items", $data);
SOSSData::Delete("my_new_app_items", $data);
SOSSData::Query("my_new_app_items", "status:Active");
```

Query syntax:

```text
field:value,anotherField:anotherValue
```

Example:

```php
$result = SOSSData::Query("my_new_app_items", "status:Active,title:Test");
```

## Raw Queries

Raw query helpers live in:

```text
schemas/mysqlquery/
schemas/query/
```

Call:

```php
$params = new \stdClass();
$params->parameters = new \stdClass();
$params->parameters->status = "Active";

$result = SOSSData::ExecuteRaw("my_new_app_report", $params);
```

Use existing files in `schemas/mysqlquery/` as examples.

## Database Creation Behavior

The MySQL connector:

1. Reads DB config from `DB_CONFIG_FILE`.
2. Builds a DB name from `init_db` plus `DATASTORE_DOMAIN`.
3. Creates a missing database when possible.
4. Creates missing tables from schema JSON.
5. Adds missing columns from schema JSON.
6. Adds framework system columns.
7. Applies view-object filtering by default on queries.

## View Object Filtering

`SOSSData::Query()` filters records by `sysviewobject` unless the caller disables view filtering.

This depends on:

```php
Auth::ViewObjects()
```

For system/admin queries, inspect existing code before disabling view-object filtering.

## Security Notes

The current MySQL connector builds SQL strings directly. When generating new services:

1. Validate request body fields.
2. Avoid passing raw user input into query strings.
3. Use known field names from schemas.
4. Prefer exact filters over arbitrary user-provided query strings.
5. Avoid exposing write endpoints to `anonymous` unless intentionally public.


# Database Schemas

DAVVAG uses schema JSON files to describe logical data namespaces. `SOSSData` reads those schemas and forwards each request to the active datastore adapter for the tenant.

In the default MySQL path, the adapter can create databases, tables, and columns on demand from the tenant schema files.

## Schema Location

Schemas live under the active tenant:

```text
{TENANT_RESOURCE_LOCATION}/schemas/{namespace}.json
```

Example:

```text
davvag-core/example.com/schemas/my_new_app_items.json
```

The active tenant is resolved from `TENANT_RESOURCE_LOCATION`, so schema files are tenant-specific and must live inside the tenant folder that is currently being served.

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

## Relationships

Schemas can also keep relationship metadata in the same JSON file. This metadata documents how a field maps to another schema and should be maintained alongside `fields` whenever the relationship is stable and known.

Example:

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
      "fieldName": "course_id",
      "dataType": "int"
    },
    {
      "fieldName": "teacher_id",
      "dataType": "int"
    }
  ],
  "relations": [
    {
      "relationName": "course_manager_subject_course",
      "relationType": "many-to-one",
      "targetEntity": "course_manager_course",
      "joinColumns": [
        {
          "sourceColumn": "course_id",
          "targetColumn": "id"
        }
      ]
    },
    {
      "relationName": "course_manager_subject_teacher_profile",
      "relationType": "many-to-one",
      "targetEntity": "profile",
      "joinColumns": [
        {
          "sourceColumn": "teacher_id",
          "targetColumn": "id"
        }
      ]
    }
  ]
}
```

Relationship keys:

| Key | Purpose |
| --- | --- |
| `relationName` | Stable unique name for the relationship inside the schema. |
| `relationType` | Logical relationship type such as `many-to-one`. |
| `targetEntity` | Target namespace or logical target entity name. |
| `joinColumns[].sourceColumn` | Field in the current schema. |
| `joinColumns[].targetColumn` | Field in the target schema. |

Use `relations` for direct references only:

1. Add a relation when a field always points to one known target schema.
2. Skip polymorphic pairs such as `entity_type` plus `entity_id` unless the target is fixed.
3. Keep the `relations` block in the tenant schema JSON so the schema remains the source of truth.
4. For documentation formatting, reserve the exact heading `Schema Relationship` for detailed per-schema reference documents only. General framework docs should use a normal heading such as `Relationships`.

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

`SOSSData` is a facade. It does not talk directly to MySQL. It selects a datastore adapter based on the tenant configuration:

```text
$GLOBALS["ENGINE_CONFIG"]->DAVVAG_DATA->{$tenantId}->connector
```

If a connector is present, DAVVAG loads:

```text
plugins/sossdata/{connector}/{connector}.php
```

The class name must match the connector folder name. For example:

```text
plugins/sossdata/phpmysql/phpmysql.php
class phpmysql implements iDataStore
```

If no connector is configured, DAVVAG falls back to:

```text
plugins/sossdata/davvagstore/davvagstore.php
```

Query syntax:

```text
field:value,anotherField:anotherValue
```

Example:

```php
$result = SOSSData::Query("my_new_app_items", "status:Active,title:Test");
```

For multiple validated conditions, sorting, and pagination, pass an advanced query array:

```php
$result = SOSSData::Query("my_new_app_items", [
    "conditions" => [
        ["column" => "status", "operator" => "=", "value" => "Active"],
        ["column" => "priority", "operator" => ">=", "value" => 3]
    ],
    "sorting" => [
        ["column" => "priority", "direction" => "DESC"]
    ],
    "pageSize" => 100,
    "pageFrom" => 0
]);
```

Use the canonical `column` spelling. Advanced conditions are joined with `AND`, and all condition and sort columns must exist in the schema. See [13-advanced-queries.md](13-advanced-queries.md) for the complete operator, sorting, pagination, and compatibility contract.

## Raw Queries for Reports and Joins

Use a raw query schema when an app needs a read model that does not fit either simple or advanced `SOSSData::Query()` payloads: joins, reporting summaries, aggregates, subqueries, computed ordering expressions, or stored procedure calls.

The main raw query definition lives in the normal tenant schema folder:

```text
{TENANT_RESOURCE_LOCATION}/schemas/{namespace}.json
```

Example:

```json
{
  "rawquery": {
    "type": "sql",
    "parameters": ["startdate", "enddate", "page", "size"],
    "query": "SELECT DATE_FORMAT(oh.invoiceDate, '%Y-%m') AS reportMonth, p.id AS profileId, p.name AS profileName, SUM(od.qty) AS qty, SUM(od.total) AS total FROM orderheader oh INNER JOIN orderdetails od ON oh.invoiceNo = od.invoiceNo INNER JOIN profile p ON oh.profileId = p.id WHERE oh.invoiceDate BETWEEN $startdate AND $enddate GROUP BY DATE_FORMAT(oh.invoiceDate, '%Y-%m'), p.id, p.name ORDER BY reportMonth DESC LIMIT $page,$size"
  },
  "fields": [
    {"fieldName": "reportMonth", "dataType": "java.lang.String"},
    {"fieldName": "profileId", "dataType": "int"},
    {"fieldName": "profileName", "dataType": "java.lang.String"},
    {"fieldName": "qty", "dataType": "float"},
    {"fieldName": "total", "dataType": "float"}
  ]
}
```

Call it from a service by building a parameter object:

```php
$params = new \stdClass();
$params->parameters = new \stdClass();
$params->parameters->page = isset($data->page) ? max(0, (int)$data->page) : 0;
$params->parameters->size = isset($data->size) ? min(100, max(1, (int)$data->size)) : 25;
$params->parameters->startdate = $startdate;
$params->parameters->enddate = $enddate;

$result = SOSSData::ExecuteRaw("my_new_app_sales_report", $params);
```

The MySQL adapter handles `ExecuteRaw()` like this:

1. Loads `schemas/{namespace}.json` with `Schema::Get($namespace)`.
2. Reads `rawquery.query`.
3. Compiles each declared `$name` placeholder to a prepared-statement `?` marker.
4. Binds `$params->parameters->name` values separately and executes the SQL or procedure call.
5. Creates each result object from the schema `fields` list.

Because result objects are built from `fields`, joined reports should select explicit aliases:

```sql
SELECT
  p.id AS profileId,
  p.name AS profileName,
  s.outstanding AS outstanding
FROM profile p
INNER JOIN profilestatus s ON p.id = s.profileid
```

Then define matching fields:

```json
[
  {"fieldName": "profileId", "dataType": "int"},
  {"fieldName": "profileName", "dataType": "java.lang.String"},
  {"fieldName": "outstanding", "dataType": "float"}
]
```

Existing tenant examples:

| Namespace | Pattern |
| --- | --- |
| `profiles_search` | Joined profile/status query called from `apps/com_qti_students/services/productsvr/service.php`. |
| `davvag_launchers_query` | Join with launcher permissions called from `apps/davvag-cms/shell/auth-handler/service.php`. |
| `messages_inbox_query` | Join and aggregation for inbox views. |
| `orderdetails_purchase_sum_by_month` | Report aggregate with `SUM`, `COUNT`, `GROUP BY`, and date parameters. |

`schemas/mysqlquery/{namespace}.sql` is a companion setup script location for stored procedures, indexes, or helper SQL. If a raw query calls a missing procedure and MySQL returns error `1305`, the adapter executes the matching script and retries. The raw query itself should still be declared in `schemas/{namespace}.json`.

`schemas/query/` contains older helper examples. Prefer `schemas/{namespace}.json` with a `rawquery` block for new advanced queries.

Security rules for raw queries:

1. Declare every placeholder in `rawquery.parameters`; missing, unexpected, or unused declarations are blocked.
2. Pass scalar data values only. The MySQL adapter binds them through a prepared statement.
3. Continue validating business formats such as dates and whitelisting enum values.
4. Do not accept raw SQL fragments, field names, sort expressions, or `WHERE` clauses from the browser; identifiers cannot be parameter-bound.
5. Raw queries do not automatically add `sysviewobject` filtering, so add visibility conditions yourself or keep the endpoint admin-only.
6. See [14-sossdata-query-firewall.md](14-sossdata-query-firewall.md) for firewall limits and migration behavior for older quoted placeholders.

## Database Creation Behavior

The `phpmysql` adapter and its `mysqlConnector` helper use the tenant schema file to keep storage in sync:

1. Reads DB config from `DB_CONFIG_FILE`.
2. Builds a DB name from `init_db` plus `DATASTORE_DOMAIN`.
3. Creates a missing database when possible.
4. Creates missing tables from schema JSON.
5. Adds missing columns from schema JSON.
6. Adds framework system columns.
7. Applies view-object filtering by default on queries.

The system columns are:

```text
sysversionid
syscreated
sysupdated
sysviewobject
syscreatedby
syslastupdatedby
```

`syscreatedby` and `syslastupdatedby` are filled from the authenticated user when available, otherwise they default to `anonymous`.

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

## Adapter Rules

When you add a new datastore adapter, keep these rules aligned with `SOSSData`:

1. Implement the `iDataStore` interface.
2. Provide the same method names and parameter order used by `SOSSData`.
3. Place the adapter in `plugins/sossdata/{connector}/{connector}.php`.
4. Keep the class name identical to the connector folder name.
5. Make sure the adapter respects tenant-specific schemas and `DATASTORE_DOMAIN`.

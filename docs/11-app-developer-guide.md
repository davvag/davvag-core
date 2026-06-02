# App Developer Guide for DAVVAG

This guide is for application developers and AI agents that need to store and retrieve data in DAVVAG correctly.

The key idea is simple:

`SOSSData` is the framework facade. It chooses a datastore adapter for the active tenant, then the adapter uses the tenant schema files to read or write data.

## What `SOSSData` Does

`plugins/sossdata/SOSSData.php` exposes the public data API:

```php
SOSSData::Insert($namespace, $object, $tenantId = null);
SOSSData::Update($namespace, $object, $tenantId = null);
SOSSData::Delete($namespace, $object, $tenantId = null);
SOSSData::Query($namespace, $query, $lastVersionId = null, $sorting = "DESC", $pageSize = 1000, $fromPage = 0, $tenantId = null, $viewObject = true);
SOSSData::ExecuteRaw($namespace, $params, $lastVersionId = null, $tenantId = null);
SOSSData::Close($tenantId = null);
```

The facade caches adapter instances per tenant and keeps the calling code stable even if the backend datastore changes.

## Tenant-Specific Adapter Selection

When you call `SOSSData`, DAVVAG resolves the adapter in this order:

1. Use the supplied `$tenantId`, or fall back to `DATASTORE_DOMAIN`.
2. Check `$GLOBALS["ENGINE_CONFIG"]->DAVVAG_DATA->{$tenantId}->connector`.
3. If a connector exists, load `plugins/sossdata/{connector}/{connector}.php`.
4. Instantiate the class with the same name as the connector folder.
5. If no connector is configured, fall back to `plugins/sossdata/davvagstore/davvagstore.php`.

Example:

```text
plugins/sossdata/phpmysql/phpmysql.php
class phpmysql implements iDataStore
```

This means a tenant can use a different datastore adapter without changing app code.

## How Data Is Stored

App code should store data by namespace, not by table name. The namespace maps to a tenant schema file:

```text
{TENANT_RESOURCE_LOCATION}/schemas/{namespace}.json
```

Example namespace:

```text
my_new_app_items
```

Example schema file:

```text
davvag-core/example.com/schemas/my_new_app_items.json
```

The schema defines the logical fields. The adapter uses it to map values and create the backing table if needed.

## Save Flow

Typical save flow from a service:

```php
<?php
namespace my_new_app;

require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");

class ApiService {
    public function postSave($req, $res) {
        $data = $req->Body(true);
        return SOSSData::Insert("my_new_app_items", $data);
    }
}
?>
```

What happens behind the scenes:

1. The service receives the request body.
2. The service passes the object to `SOSSData::Insert()`.
3. `SOSSData` resolves the tenant adapter.
4. The adapter loads the tenant schema file.
5. The adapter creates or updates the storage structure if needed.
6. The adapter writes the row and returns a framework response object.

## Query Flow

Typical read flow from a service:

```php
<?php
namespace my_new_app;

require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");

class ApiService {
    public function getList($req, $res) {
        $query = isset($_GET["query"]) ? $_GET["query"] : "";
        return SOSSData::Query("my_new_app_items", $query);
    }
}
?>
```

Query filters use comma-separated `field:value` pairs:

```text
status:Active,title:Test
```

The MySQL adapter uses the schema to validate field names and map returned values into PHP objects.

## Update and Delete Flow

Update and delete calls use the same namespace and schema-driven mapping:

```php
SOSSData::Update("my_new_app_items", $data);
SOSSData::Delete("my_new_app_items", $data);
```

Important behavior in the MySQL adapter:

- updates require a primary key or a valid where condition generated from the schema fields;
- inserts and updates stamp system metadata;
- deletes should be targeted, because the connector builds SQL from the object fields supplied.

## System Columns

The framework adds these system columns when using the MySQL adapter:

```text
sysversionid
syscreated
sysupdated
sysviewobject
syscreatedby
syslastupdatedby
```

Meaning:

| Column | Purpose |
| --- | --- |
| `sysversionid` | Version stamp used for ordering and change tracking. |
| `syscreated` | Creation timestamp. |
| `sysupdated` | Last update timestamp. |
| `sysviewobject` | Visibility bucket used by access filtering. |
| `syscreatedby` | User ID of the creator, or `anonymous`. |
| `syslastupdatedby` | User ID of the last updater, or `anonymous`. |

## View Object Filtering

`SOSSData::Query()` can filter rows by view object automatically.

The MySQL adapter checks `Auth::ViewObjects()` and adds a `sysviewobject in (...)` condition when filtering is enabled.

Use the default filtering for normal app requests. Only disable it for trusted admin or maintenance code paths.

## Raw Queries

Some namespaces can define a raw query file under the tenant schema folder:

```text
{TENANT_RESOURCE_LOCATION}/schemas/mysqlquery/{namespace}.sql
```

When you call:

```php
$params = new \stdClass();
$params->parameters = new \stdClass();
$params->parameters->status = "Active";

$result = SOSSData::ExecuteRaw("my_new_app_report", $params);
```

the adapter loads the SQL template and replaces parameter placeholders before execution.

Use raw queries sparingly and only when schema-driven insert/query/update logic is not enough.

## Adapter Behavior You Should Expect

The default MySQL adapter is schema-aware and can:

- create a missing database for the tenant;
- create a missing table from the schema file;
- add missing columns when the schema evolves;
- convert values to and from PHP objects;
- apply system metadata and query pagination;
- return standard framework response objects.

The fallback `davvagstore` adapter is useful when the datastore is proxied through the DAVVAG REST layer instead of direct MySQL access.

## Service Guidelines

When writing an app service:

1. Require `plugins/sossdata/SOSSData.php`.
2. Use a stable namespace string for the data model.
3. Validate request input before building query strings.
4. Keep schema files in the active tenant.
5. Use the authenticated user context when you need ownership or audit data.
6. Avoid exposing write operations to public users unless that is intentional.

Recommended service pattern:

```php
<?php
namespace my_new_app;

require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");

class ApiService {
    public function postSave($req, $res) {
        $data = $req->Body(true);
        if (empty($data->title)) {
            return $res->SetError("title is required");
        }
        return SOSSData::Insert("my_new_app_items", $data);
    }

    public function getList($req, $res) {
        $query = isset($_GET["query"]) ? $_GET["query"] : "";
        return SOSSData::Query("my_new_app_items", $query);
    }
}
?>
```

## Practical Checklist

Before you ship a new data-backed app:

1. Create a tenant schema file for every namespace the app uses.
2. Make sure the tenant config points at the correct datastore connector.
3. Confirm the adapter exists under `plugins/sossdata/{connector}/{connector}.php`.
4. Match the class name to the connector folder name.
5. Test insert, query, update, and delete from the app service.
6. Verify that view-object filtering behaves as expected for the target group.

## Where To Read Next

- [05-database-schemas.md](05-database-schemas.md)
- [07-plugins.md](07-plugins.md)
- [10-ai-agent-playbook.md](10-ai-agent-playbook.md)


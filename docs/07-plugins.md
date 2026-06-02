# Plugins

DAVVAG supports global framework plugins and tenant-local plugins.

## Plugin Locations

Global plugins:

```text
plugins/
```

Tenant-local plugins:

```text
{TENANT_RESOURCE_LOCATION}/plugins/
```

Examples:

```text
plugins/sossdata/SOSSData.php
plugins/auth/auth.php
davvag-core/localhost/plugins/profile/profile.php
davvag-core/localhost/plugins/davvag-flow/flow.php
```

## Startup Plugins

Root `config.json` can load global startup plugins:

```json
{
  "DAVVAG_StartUp": {
    "plugins": [
      {
        "name": "sossdata",
        "plugin_location": "global",
        "location": "/sossdata/SOSSData.php"
      },
      {
        "name": "auth",
        "plugin_location": "global",
        "location": "/auth/auth.php"
      }
    ]
  }
}
```

`configloader.php` requires these files during framework startup.

## Component Plugin Dependencies

Service components can load dependencies before the handler class is loaded:

```json
{
  "dependency": {
    "plugins": [
      {
        "type": "php",
        "plugin_location": "local",
        "location": "/profile/profile.php"
      }
    ]
  }
}
```

| `plugin_location` | Base path |
| --- | --- |
| `global` | Root `PLUGIN_PATH`. |
| `local` | Tenant `PLUGIN_PATH_LOCAL`. |
| other/default | Explicit `location` path. |

## Creating a Tenant Plugin

Create:

```text
davvag-core/example.com/plugins/my-plugin/my-plugin.php
```

Example:

```php
<?php
class MyPlugin {
    public static function normalizeTitle($title) {
        return trim($title);
    }
}
?>
```

Use it from a service descriptor:

```json
{
  "dependency": {
    "plugins": [
      {
        "type": "php",
        "plugin_location": "local",
        "location": "/my-plugin/my-plugin.php"
      }
    ]
  }
}
```

Use it from service code:

```php
$title = \MyPlugin::normalizeTitle($data->title);
```

## Common Global Plugins

| Plugin | Purpose |
| --- | --- |
| `auth` | Authentication and authorization facade. |
| `sossdata` | Data access facade that routes to tenant-specific datastore adapters. |
| `phpcache` | JSON file cache. |
| `notify` | Email notification wrapper. |
| `davvag-flow` | Workflow execution. |
| `transactions` | Fluent transaction/activity pipeline. |
| `hosting` | Backup helpers. |
| `mpdf`, `fpdf`, `fpdm` | PDF tools. |
| `phpspreadsheet` | Spreadsheet tools. |

## SOSSData Plugin

`plugins/sossdata/SOSSData.php` is the framework entry point for data storage and retrieval.

It does three jobs:

1. Resolves the correct adapter for the current tenant.
2. Caches the adapter instance in memory for reuse.
3. Exposes a stable API for insert, update, delete, query, raw query, close, and view-object setup.

The adapter selection flow is:

```text
SOSSData -> tenant connector config -> adapter class -> datastore operations
```

Default behavior:

- If the tenant has a configured connector, DAVVAG loads `plugins/sossdata/{connector}/{connector}.php`.
- If not, DAVVAG falls back to `plugins/sossdata/davvagstore/davvagstore.php`.
- Every adapter must implement `iDataStore`.

Example adapter:

```text
plugins/sossdata/phpmysql/phpmysql.php
class phpmysql implements iDataStore
```

The `phpmysql` adapter uses tenant schema files under `schemas/` to auto-create and update storage structures.

## Schema-Aware Storage

When `SOSSData::Insert()`, `Update()`, `Delete()`, or `Query()` is called, the selected adapter typically reads:

```text
{TENANT_RESOURCE_LOCATION}/schemas/{namespace}.json
```

That schema drives:

- column creation;
- type conversion;
- system column injection;
- default values;
- query-time object mapping.

For app-level examples, use `docs/05-database-schemas.md` and `docs/11-app-developer-guide.md`.

## Security

Tenant plugin folders should deny direct access:

```apache
<Files ~ "^.*">
  Deny from all
</Files>
```

Do not put secrets in plugin source. Put secrets in protected config files or environment-specific config.

## Plugin Best Practices

1. Keep plugin files side-effect free when loaded.
2. Do not echo output during plugin load.
3. Use explicit class names.
4. Prefer static helper methods for simple utility plugins.
5. Use declared component dependencies for tenant-local plugins.
6. Use root plugins for framework-wide behavior.
7. Use tenant plugins for domain-specific reusable behavior.

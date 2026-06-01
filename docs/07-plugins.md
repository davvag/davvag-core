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
| `sossdata` | Data access facade. |
| `phpcache` | JSON file cache. |
| `notify` | Email notification wrapper. |
| `davvag-flow` | Workflow execution. |
| `transactions` | Fluent transaction/activity pipeline. |
| `hosting` | Backup helpers. |
| `mpdf`, `fpdf`, `fpdm` | PDF tools. |
| `phpspreadsheet` | Spreadsheet tools. |

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


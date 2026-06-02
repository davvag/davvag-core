# Framework Overview

DAVVAG is a PHP-based, tenant-aware application framework. The framework root contains shared runtime code. Each tenant/domain folder contains applications and configuration for that tenant.

## Runtime Flow

Normal request flow:

```text
Browser request
  -> index.php
  -> configloader.php
  -> init.php
  -> tenant.json
  -> apps/{startupApp}/app.php
  -> lib/webdock.js
  -> /components/... API
```

Admin request flow:

```text
/admin
  -> admin.php
  -> index.php
  -> configloader.php
  -> init.php
  -> tenant.json > webdock.events.onStartup.admin
```

Important source spelling:

```php
SOSSPlatform::intialize()
```

The method name is spelled `intialize` in source code. Preserve that spelling when referencing or calling it.

## Root Framework Folders

| Path | Purpose |
| --- | --- |
| `assets/` | Apache rewrite entry for tenant app asset files. |
| `components/` | Core component API router, service dispatcher, transformer dispatcher, and helper classes. |
| `davvag-core/` | Tenant/domain container. Example tenant: `davvag-core/localhost`. |
| `files/` | Apache rewrite entry for media files. |
| `lib/` | Shared browser libraries such as `webdock.js`, `jquery.js`, and Bootstrap. |
| `pages/` | Framework pages, including the install/module check page. |
| `plugins/` | Global framework plugins such as `auth`, `sossdata`, `phpcache`, `notify`, and `davvag-flow`. |

## Important Root Files

| File | Purpose |
| --- | --- |
| `index.php` | Main entry point. Loads config and initializes DAVVAG. |
| `admin.php` | Admin entry point. Defines `IS_ADMIN_MODE=true`, then loads `index.php`. |
| `configloader.php` | Loads config, defines constants, loads startup plugins, and resolves tenant paths. |
| `init.php` | Reads tenant `tenant.json` and loads the selected app. |
| `config.json` | Main framework config. Defines paths, DB config file, startup plugins, auth connector, and data connector. |
| `.htaccess` | Root Apache rewrite rules. |

## Important Constants

`configloader.php` defines constants that control path resolution and runtime behavior:

```text
RESOURCE_LOCATION
HOST_NAME
TENANT_RESOURCE_LOCATION
TENANT_RESOURCE_LOCATION_APPS
BASE_PATH
COMPONENT_PATH
PLUGIN_PATH
PLUGIN_PATH_LOCAL
SCHEMA_PATH
DATASTORE_DOMAIN
AUTH_DOMAIN
MEDIA_FOLDER
DB_CONFIG_FILE
```

`TENANT_RESOURCE_LOCATION` is the most important constant for tenant work:

```text
TENANT_RESOURCE_LOCATION = RESOURCE_LOCATION / HOST_NAME
```

If `LOCAL_DEV_HOST` exists, `HOST_NAME` uses it. Otherwise `HOST_NAME` uses `$_SERVER["HTTP_HOST"]`.

## Core Modules

| Module | Path | Purpose |
| --- | --- | --- |
| Component API | `components/index.php` | Starts session, loads framework helpers, authenticates user, registers routes, dispatches request. |
| Router | `components/carbite.php` | Lightweight route matcher and response output wrapper. |
| Component Manager | `components/component_manager.php` | Resolves app/component descriptors and handles files, services, objects, and transformers. |
| Common Helpers | `components/common.php` | CORS response wrapping, access checks, REST helper. |
| Virtual Firewall | `components/virtual_firewall.php` | Hook point for component authorization. Currently permissive in source. |
| Auth Facade | `plugins/auth/auth.php` | Login, session restore, logout, group access, permissions, domain attributes. |
| Data Facade | `plugins/sossdata/SOSSData.php` | Tenant-aware data facade that routes insert, update, delete, query, raw query, and close calls to the configured adapter. |
| Cache | `plugins/phpcache/cache.php` | JSON cache files under `MEDIA_FOLDER/cache`. |
| Frontend Loader | `lib/webdock.js` | Downloads app/component descriptors, injects assets, exposes service/transform calls. |

## Tenant Container

Tenant/domain folders live under:

```text
davvag-core/
```

Examples:

```text
davvag-core/localhost
davvag-core/example.com
davvag-core/apps.davvag.com
```

The framework should not expose tenant folders directly. `davvag-core/.htaccess` denies direct file access.

## Frontend Loading Model

`webdock.js` reads app metadata, loads components, injects component resources, and exposes backend services.

Component file loading:

```text
/components/{appCode}/{componentName}/file/{filePath}
```

Component descriptor loading:

```text
/components/{appCode}/{componentName}/object?object=desc
```

Service call:

```text
/components/{appCode}/{componentName}/service/{handlerName}
```

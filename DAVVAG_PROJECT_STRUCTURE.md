# DAVVAG Project Structure

Generated from a local scan of `c:\xampp\htdocs\git\davvag-core` on 2026-06-01.

This document is written for AI agents and developers that need to understand, modify, or generate DAVVAG applications inside this repository.

## 1. Framework Overview

DAVVAG is a PHP-based, tenant-aware application framework. The root framework boots configuration, loads global plugins, resolves the active tenant, and serves a configured startup app. Application UI and service behavior are described by JSON descriptors under each tenant app folder.

The main runtime path is:

1. Apache serves `index.php` or `admin.php`.
2. `index.php` loads `configloader.php`, then `init.php`, then calls `SOSSPlatform::intialize()`.
3. `configloader.php` defines constants such as `RESOURCE_LOCATION`, `TENANT_RESOURCE_LOCATION`, `PLUGIN_PATH`, `SCHEMA_PATH`, `DATASTORE_DOMAIN`, and `AUTH_DOMAIN`.
4. `init.php` reads `{TENANT_RESOURCE_LOCATION}/tenant.json`.
5. The tenant startup app is selected from `tenant.json > webdock > events > onStartup`.
6. The startup app PHP file is required from `{TENANT_RESOURCE_LOCATION}/apps/{appCode}/app.php`.
7. Frontend code loads components through the `/components/...` API, handled by `components/index.php`.

Important code spelling note: the platform method is named `SOSSPlatform::intialize()` in source code.

## 2. Root Folder Map

| Path | Purpose |
| --- | --- |
| `.vscode/` | Local IDE settings. Not part of runtime behavior. |
| `assets/` | Apache rewrite entry for tenant app assets. `assets/.htaccess` maps `/assets/{appCode}/{path}` to tenant app asset files. |
| `components/` | Core component API router and service dispatch layer. All dynamic component/object/service/transform requests flow through `components/index.php`. |
| `davvag-core/` | Tenant resource root in this checkout. Direct HTTP access is denied by `davvag-core/.htaccess`. Contains tenant folders such as `davvag-core/localhost/`. |
| `files/` | Apache rewrite entry for media files. `files/.htaccess` maps requests to `/var/media/{HTTP_HOST}/...` on Linux-style deployments. |
| `lib/` | Shared frontend libraries, especially `webdock.js`, `jquery.js`, and `bootstrap.min.js`. |
| `pages/` | Framework-level pages. `pages/install/index.php` checks required Apache and PHP modules. |
| `plugins/` | Global DAVVAG plugins and bundled third-party libraries. Startup plugins are loaded from `config.json > DAVVAG_StartUp`. |

## 3. Important Root Files

| File | Purpose |
| --- | --- |
| `index.php` | Main public entry point. Loads config and initializes the selected tenant startup app. |
| `admin.php` | Admin entry point. Defines `IS_ADMIN_MODE=true` and then loads `index.php`. This makes `init.php` choose the admin startup app. |
| `configloader.php` | Loads global and domain-specific config, defines framework constants, and requires startup plugins. |
| `init.php` | Defines `SOSSPlatform` and selects the tenant startup app from `tenant.json`. |
| `config.json` | Main local runtime config. Defines paths, service URLs, DB config path, startup plugins, auth connector, data connector, and debug mode. |
| `config-temp.json` | Alternate/template-style config. Useful when creating new deployments. |
| `.htaccess` | Root Apache rewrite rules for admin and admin assets. |
| `.gitignore` | Ignore rules. Very large in this checkout. |
| `.hintrc` | Web hint/lint configuration. |
| `.cpanel.yml` | Not present in this checkout. Add one only if cPanel Git deployment automation is needed. |

## 4. Configuration Loading

`configloader.php` supports an optional environment override:

```php
$configFolder = getenv("DAVVAGCONFIG");
if ($configFolder === FALSE) {
    $configFolder = dirname(__FILE__);
}
```

The effective config file is `{DAVVAGCONFIG or repo root}/config.json`.

`configloader.php` performs these steps:

1. Loads `config.json`.
2. Defines every key under `variables` as a PHP constant.
3. If missing, redirects to `pages/install`.
4. Stores the config object in `$GLOBALS["ENGINE_CONFIG"]`.
5. Attempts to load domain config from `{configFolder}/davvag-core/{HTTP_HOST}/config.json`.
6. Defines `HOST_NAME` from `LOCAL_DEV_HOST` if present, otherwise `$_SERVER["HTTP_HOST"]`.
7. Defaults `DATASTORE_DOMAIN` and `AUTH_DOMAIN` to `$_SERVER["HTTP_HOST"]` if not explicitly configured.
8. Defines resource and plugin paths:

```php
TENANT_RESOURCE_LOCATION      = RESOURCE_LOCATION . "/" . HOST_NAME
TENANT_RESOURCE_LOCATION_APPS = RESOURCE_LOCATION . "/" . HOST_NAME . "/apps"
BASE_PATH                     = repo root
COMPONENT_PATH                = repo root . "/components"
PLUGIN_PATH                   = repo root . "/plugins"
PLUGIN_PATH_LOCAL             = TENANT_RESOURCE_LOCATION . "/plugins"
SCHEMA_PATH                   = TENANT_RESOURCE_LOCATION . "/schemas"
```

Startup plugins are configured in `config.json > DAVVAG_StartUp > plugins`. The current config loads:

| Plugin | Location |
| --- | --- |
| `notify` | `plugins/notify/notify.php` |
| `sossdata` | `plugins/sossdata/SOSSData.php` |
| `auth` | `plugins/auth/auth.php` |

Deployment caution: the checked-in `config.json` sets `RESOURCE_LOCATION` to `C:\xampp\htdocs` and `LOCAL_DEV_HOST` to `apps.davvag.com`. This means runtime tenant resources are expected at `C:\xampp\htdocs\apps.davvag.com` unless config is adjusted. The repository also contains a tenant under `davvag-core/localhost`; to use that tenant directly, set `RESOURCE_LOCATION` to the repository `davvag-core` folder and set `LOCAL_DEV_HOST` or the vhost name to `localhost`.

## 5. Tenant Resource Structure

The scanned tenant folder is `davvag-core/localhost/`.

Common tenant files and folders:

| Path | Purpose |
| --- | --- |
| `tenant.json` | Tenant app registry and startup app selection. |
| `config.json` | Tenant/domain-specific config override if loaded for the current `HTTP_HOST`. |
| `{group}.json` | Group/application access descriptors, for example `anonymous.json`, `web_user.json`, `sysadmin.json`. |
| `apps/` | Tenant applications. Each app has `app.json`, optional `app.php`, components, services, assets, and pages. |
| `schemas/` | JSON schema descriptors for `SOSSData` and MySQL table generation. |
| `schemas/mysqlquery/` | Raw SQL/query definitions used by `SOSSData::ExecuteRaw()`. |
| `plugins/` | Tenant-local plugins loaded through component dependencies with `plugin_location: "local"`. |
| `global/` | Shared tenant config/templates such as email SMTP config and email templates. |
| `davvag-flow/` | Tenant workflow JSON files for `DavvagFlow`. |
| `sossgrid.conf` | MySQL connection config referenced by `DB_CONFIG_FILE` in local config. |

`tenant.json` contains an `apps` object and a startup mapping:

```json
{
  "webdock": {
    "events": {
      "onStartup": {
        "admin": "dock",
        "default": "davvag-cms"
      }
    }
  }
}
```

In normal mode, the default startup app is `davvag-cms`. In admin mode, the startup app is `dock`.

## 6. Apache Rewrite and Access Rules

Root `.htaccess`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(admin)?$ ./admin.php [QSA,L]
RewriteRule ^(admin)/(assets)/([A-Za-z0-9-]+)/(.*)/?$ ./davvag-core/%{HTTP_HOST}/apps/$3/assets/$4 [QSA,L]
```

`components/.htaccess` routes all non-file/non-directory component requests to `components/index.php`:

```apache
RewriteRule ^(.*)$ index.php [QSA,L]
```

`assets/.htaccess` maps public asset URLs to tenant app assets:

```apache
RewriteRule ^([A-Za-z0-9-]+)/(.*)/?$ ../davvag-core/%{HTTP_HOST}/apps/$1/assets/$2 [QSA,L]
```

`davvag-core/.htaccess` denies direct HTTP access to tenant resources:

```apache
<Files ~ "^.*">
  Deny from all
</Files>
```

`files/.htaccess` maps app/file requests to a media folder:

```apache
RewriteRule ^([A-Za-z0-9-]+)/(.*)/?$ /var/media/%{HTTP_HOST}/$1/$2 [QSA,L]
```

On Windows/XAMPP, validate or adjust the `/var/media/...` rewrite because it is Linux-style.

## 7. Core DAVVAG Modules

### 7.1 `components/index.php`

This is the central HTTP API entry point for component requests.

It:

1. Starts a PHP session if none exists.
2. Loads framework config and core component classes.
3. Loads `auth`, `phpcache`, and `sossdata` plugins.
4. Authenticates the current user through `Auth::Autendicate()`.
5. Defines `GROUPID`, defaulting to `anonymous`.
6. Registers Carbite routes.
7. Calls `Carbite::Start()`.

### 7.2 `components/carbite.php`

Carbite is a small PHP router and response wrapper.

Main classes:

| Class | Purpose |
| --- | --- |
| `CReq` | Request wrapper with `Params()`, `Query()`, `Headers()`, and `Body($json=false)`. |
| `CRes` | Response wrapper with `Set()`, `SetJSON()`, `SetError()`, and content type helpers. |
| `Carbite` | Static router supporting `GET`, `POST`, `PUT`, `DELETE`, `HANDLE`, wildcard paths, filters, and output serialization. |

Route syntax:

| Syntax | Meaning |
| --- | --- |
| `/fixed/path` | Exact path segment match. |
| `/@appCode` | Captures a segment into `$req->Params()->appCode`. |
| `/*filePath` | Captures the rest of the path into one parameter. |

### 7.3 `components/component_manager.php`

`ComponentManager` resolves app/component descriptors and dispatches component operations.

Primary responsibilities:

| Method | Route usage | Purpose |
| --- | --- | --- |
| `GetAllApps` | `/components/object/apps` | Reads `{GROUPID}.json` and returns app descriptions from installed app descriptors. |
| `GetAppDescriptor` | `/components/object/appdescriptor/{appCode}` | Returns `{tenant}/apps/{appCode}/app.json` and stores it in `$_SESSION["appDescriptor"]`. |
| `GetAppIcon` | `/components/object/appicon/{appCode}` | Serves app icon PNG. |
| `HandleFile` | `/components/{app}/{component}/file/{path}` | Serves files declared or located under a component folder. |
| `HandleComponent` | `/components/{app}/{component}/object?...` | Returns component descriptors and resources. |
| `HandleService` | `/components/{app}/{component}/service/{handler}` | Loads and invokes a PHP service handler. |
| `HandleTransformer` | `/components/{app}/{component}/transform/{route}` | Routes transform calls to `SOSSData` or external REST destinations. |

Access checks happen through `checkAccess()` before descriptors and service handlers are returned.

### 7.4 `components/common.php`

Shared helper functions:

| Function | Purpose |
| --- | --- |
| `writeResponse($res, $success, $result)` | Adds CORS headers and wraps responses as `{success, result}`. |
| `checkAccess(...)` | Uses `Auth::GetAccess()` and `GROUPID` to enforce app/service permissions. |
| `getAuthApplications($appcode)` | Caches app access in `$GLOBALS["appAccess"]`. |
| `sendRestRequest(...)` | cURL helper for forwarding REST requests. |

`BYPASS` is defined as `false` in this file.

### 7.5 `components/virtual_firewall.php`

`VirtualFirewall::CheckAuthentication()` is registered as a Carbite filter for most component routes. In the current source, `checkPermission()` immediately returns `true`, so the virtual firewall is effectively permissive. Real enforcement currently happens mainly through `ComponentManager` and `checkAccess()`.

### 7.6 `components/carbitetransform.php`

Maps component `transformers` descriptors to external REST calls. It:

1. Reads the raw request body.
2. Replaces route variables such as `@id`.
3. Builds destination headers and body.
4. Sends the request with cURL.
5. Mirrors destination content type and HTTP status.
6. Clears cache objects for POST and DELETE calls.

### 7.7 `plugins/sossdata`

`SOSSData` is the data access facade.

Important files:

| File | Purpose |
| --- | --- |
| `plugins/sossdata/SOSSData.php` | Static facade for `Insert`, `Update`, `Delete`, `Query`, `ExecuteRaw`, `Close`, and `SetViewObject`. |
| `plugins/sossdata/DataStore.php` | `iDataStore` interface. |
| `plugins/sossdata/phpmysql/phpmysql.php` | MySQL connector adapter implementing `iDataStore`. |
| `plugins/sossdata/phpmysql/mysqlConnector.php` | Actual MySQL SQL generation, table creation, query, insert, update, delete. |
| `plugins/sossdata/phpmysql/schema.php` | Loads JSON schema files from `SCHEMA_PATH` and defines system columns. |
| `plugins/sossdata/davvagstore/` | Fallback datastore implementation. |

Data source selection:

1. `SOSSData` checks `$GLOBALS["ENGINE_CONFIG"]->DAVVAG_DATA`.
2. If a connector is configured for the tenant id, that connector is loaded.
3. Otherwise it falls back to `davvagstore`.

MySQL behavior:

1. Reads DB credentials from `DB_CONFIG_FILE`.
2. Builds database name from `init_db` plus tenant domain with dots converted to underscores.
3. Creates missing databases.
4. Creates or alters missing tables using schema JSON.
5. Adds system fields including `sysversionid`, `syscreated`, `sysupdated`, `sysviewobject`, `syscreatedby`, and `syslastupdatedby`.
6. Applies view-object filtering through `Auth::ViewObjects()` unless `$viewObject=false`.

Query syntax is comma-separated `field:value` pairs, for example:

```text
email:user@example.com,status:Active
```

### 7.8 `plugins/auth`

`Auth` is the authentication facade.

Important files:

| File | Purpose |
| --- | --- |
| `plugins/auth/auth.php` | Static auth facade used by framework code. |
| `plugins/auth/iDavvagAuth.php` | Auth connector interface. |
| `plugins/auth/phpauth/phpauth.php` | Local PHP/MySQL auth connector using `SOSSData`. |
| `plugins/auth/davvagauth/` | Alternate connector wrapping `AuthSvr`. |
| `plugins/auth/docs/api.md` | Existing auth API notes. |

The configured connector is `DAVVAG_AUTH.connector`, currently `phpauth`.

`Auth` handles login, session lookup, logout, user groups, permissions, domain attributes, cross-domain calls, and view-object lists.

### 7.9 `plugins/phpcache`

`CacheData` stores JSON cache files under:

```text
{MEDIA_FOLDER}/cache/{DATASTORE_DOMAIN}/{userId or global}/{className}/{key}.chr
```

Default cache TTL is one hour in `getObjects()`. `getObjects_fullcache()` does not expire by time. Writes use `json_encode()`.

### 7.10 Other Global Plugins

| Plugin | Purpose |
| --- | --- |
| `plugins/notify` | Email notification wrapper around PHPMailer. Reads SMTP config and templates from tenant `global/` resources. |
| `plugins/transactions` | Fluent transaction/activity pipeline. Auto-loads PHP activities from `plugins/transactions/activities`. |
| `plugins/davvag-flow` | JSON-defined workflow executor that loads activity classes from `plugins/davvag-flow/lib`. |
| `plugins/hosting` | Backup utilities for tenant app/media folders and MySQL dumps. |
| `plugins/fpdf`, `plugins/fpdm`, `plugins/mpdf` | PDF generation and PDF template libraries. |
| `plugins/phpmailer`, `plugins/phpmailer_old` | Mail libraries. |
| `plugins/phpspreadsheet` | Spreadsheet generation/reading library. |
| `plugins/Facebook`, `plugins/Google` | Bundled SDK libraries. |

## 8. Component API Endpoints

All routes below are relative to `/components/` and are implemented by `components/index.php`.

| Method | Path | Handler | Purpose |
| --- | --- | --- | --- |
| `GET` | `/object/tenantdescriptor` | `ComponentManager::GetTenantDescriptor` | Placeholder in current code. |
| `GET` | `/object/apps` | `ComponentManager::GetAllApps` | Returns apps allowed for `GROUPID`. |
| `GET` | `/object/appicon/@appCode` | `ComponentManager::GetAppIcon` | Returns app icon PNG. |
| `GET` | `/object/appdescriptor/@appCode` | `ComponentManager::GetAppDescriptor` | Returns app `app.json`. |
| `GET` | `/@appCode/@componentName/file/*filePath` | `ComponentManager::HandleFile` | Serves component files such as JS, HTML, and CSS. |
| `GET` | `/@appCode/@componentName/service/@handlerName` | `ComponentManager::HandleService` | Invokes a GET service handler. |
| `GET` | `/@appCode/@componentName/service/@handlerName/*route` | `ComponentManager::HandleService` | Invokes a GET service handler with extra path. |
| `POST` | `/@appCode/@componentName/service/@handlerName` | `ComponentManager::HandleService` | Invokes a POST service handler. |
| `POST` | `/@appCode/@componentName/service/@handlerName/*route` | `ComponentManager::HandleService` | Invokes a POST service handler with extra path. |
| `GET` | `/@appCode/@componentName/object` | `ComponentManager::HandleComponent` | Handles descriptor/resource object requests. |
| `GET` | `/@appCode/@componentName/transform/*route` | `ComponentManager::HandleTransformer` | Invokes a GET transformer. |
| `POST` | `/@appCode/@componentName/transform/*route` | `ComponentManager::HandleTransformer` | Invokes a POST transformer. |

Frontend `webdock.js` calls these endpoints automatically.

## 9. Service Handler Contract

A service component is declared in an app `app.json`:

```json
{
  "components": {
    "app-handler": {
      "type": "service",
      "location": "service"
    }
  }
}
```

The service component descriptor then declares a PHP service handler:

```json
{
  "serviceHandler": {
    "file": "service.php",
    "class": "davvag_sample_app_1\\appService",
    "methods": {
      "Save": {
        "method": "POST"
      }
    }
  }
}
```

`ComponentManager::HandleService()` loads `service.php`, instantiates the configured class, and invokes:

```text
strtolower($_SERVER["REQUEST_METHOD"]) . ucwords(handlerName)
```

Examples:

| Request | PHP method |
| --- | --- |
| `GET /components/my-app/app-handler/service/login` | `getLogin($req, $res)` |
| `POST /components/my-app/app-handler/service/save` | `postSave($req, $res)` |
| Any method without a specific method | `__handle($req, $res)` fallback |

Service handlers should return plain values, arrays, or objects. `ComponentManager` wraps the return value with:

```json
{
  "success": true,
  "result": {}
}
```

If a handler calls `$res->SetError($error)`, the framework returns `success=false` and sets HTTP status 500 if no other status was set.

## 10. Transformer Contract

Component descriptors may declare `transformers`.

External REST transformer example:

```json
{
  "transformers": {
    "createUser": {
      "method": "POST",
      "route": "/createUser",
      "destMethod": "POST",
      "destUrl": "http://localhost:9000/createuser/"
    }
  }
}
```

Direct `SOSSData` transformer example:

```json
{
  "transformers": {
    "saveItem": {
      "method": "POST",
      "route": "/items",
      "destUrl": "SOSSData",
      "destMethod": "insert",
      "namespace": "items"
    }
  }
}
```

Supported direct `SOSSData` destination methods in `ComponentManager::HandleTransformer()`:

| `destMethod` | Behavior |
| --- | --- |
| `insert` | Reads JSON body and calls `SOSSData::Insert(namespace, data)`. |
| `delete` | Reads JSON body and calls `SOSSData::Delete(namespace, data)`. |
| `update` | Reads JSON body and calls `SOSSData::Update(namespace, data)`. |
| `query` | Reads query parameters and calls `SOSSData::Query(namespace, query, lastID, sorting, pageSize, fromPage)`. |

For REST destinations, `CarbiteTransform` forwards the request body and selected headers, then echoes the remote content type and status.

## 11. Frontend Architecture

The frontend is HTML/CSS/JavaScript based. No Flutter or Dart files were found in the scan.

Important frontend assets:

| Path | Purpose |
| --- | --- |
| `lib/webdock.js` | Main client-side component loader and backend API wrapper. |
| `lib/jquery.js` | jQuery dependency used by `webdock.js`. |
| `lib/bootstrap.min.js` | Bootstrap JS. |
| `{tenant}/apps/{app}/app.php` | App entry file. Often includes pages or handles login redirects. |
| `{tenant}/apps/{app}/app.json` | App descriptor defining components, services, tags, and Webdock config. |
| `{tenant}/apps/{app}/{location}/{component}/component.json` | Component descriptor defining frontend resources, service handlers, and transformers. |
| `{component}/script.js` | Main component script, often registering with `WEBDOCK.component()`. |
| `{component}/partial.html` | Component view markup loaded into `[webdock-component]` placeholders. |
| `{component}/*.css` | Component-specific CSS. |

`webdock.js`:

1. Reads the current app code from the `webdockapp` attribute on the script tag.
2. Downloads the app descriptor via `/components/object/appdescriptor/{appCode}`.
3. Downloads components listed in `configuration.webdock.onLoad` plus components present in the HTML as `[webdock-component]`.
4. Downloads each component descriptor via `/components/{app}/{component}/object?object=desc`.
5. Downloads component files via `/components/{app}/{component}/file/{file}`.
6. Injects CSS and scripts.
7. Creates component instances.
8. Exposes service and transformer calls as `component.services.{method}` and `component.transformers.{method}`.

Example frontend service call shape generated by `webdock.js`:

```javascript
component.services.Save(payload).then(function (response) {
  // response is the framework JSON response
}).error(function (error) {
  // handle failed request
});
```

## 12. Session, Cookie, and Auth Flow

Core session startup:

```php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
```

This is done in `components/index.php` before component requests are handled.

Login flow:

1. A login service or app form calls `Auth::Login($username, $password)`.
2. `Auth` delegates to the configured connector, currently `phpauth`.
3. On success, the returned object includes `token`, `userid`, `email`, `group`, and related session data.
4. `Auth::Login()` stores the object in `$_SESSION["authData"]`.
5. `Auth::Login()` sets `securityToken` cookie for 1 day.
6. Some app entry files also set `securityToken` and `authData` cookies for 30 days.

Request authentication:

1. `components/index.php` calls `Auth::Autendicate()`.
2. If `$_SESSION["authData"]` exists, it is returned.
3. Otherwise, if `$_COOKIE["securityToken"]` exists, `Auth::GetSession(token)` loads the session from the datastore.
4. The loaded session is written back to `$_SESSION["authData"]`.
5. `GROUPID` is defined from `$user->group`; otherwise it is `anonymous`.

Logout flow:

1. `Auth::GetLogout($token)` deletes the server-side session through the auth connector.
2. `session_destroy()` is called.
3. Session keys `authData`, `viewObjects`, `viewObjects_e`, and `viewObjects_f` are unset.
4. `securityToken` cookie is expired.

Other cookies observed:

| Cookie | Purpose |
| --- | --- |
| `securityToken` | Primary auth token used to restore PHP session. |
| `authData` | JSON auth data written by some app entry files and older handlers. |
| `sosskey` | Forwarding token used by cross-domain calls and some auth handlers. |
| `Location` | Lat/lng location cookie used by shop/cart handlers. |

Security note: several cookies are set without explicit `HttpOnly`, `Secure`, or `SameSite` attributes. For production HTTPS deployments, add those flags where compatible.

## 13. CORS and HTTP Headers

`writeResponse()` in `components/common.php` emits:

```http
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Allow-Credentials: true
```

Important browser compatibility note: browsers reject credentialed CORS responses when `Access-Control-Allow-Origin` is `*`. If frontend requests use `xhrFields: {withCredentials: true}`, production CORS should echo an allowed origin instead of `*`, or credentials should be disabled.

The code sets CORS headers on normal framework JSON responses, but there is no central `OPTIONS` preflight route in `components/index.php`. If deploying cross-origin APIs, add explicit `OPTIONS` handling before route dispatch.

Component files and descriptors use private cache headers:

```http
Cache-Control: private, max-age=10800, pre-check=10800
Pragma: private
Expires: +2 days
```

## 14. Data and Permission Model

DAVVAG uses both group-level permissions and row-level view-object filtering.

Group access:

1. `GROUPID` comes from the authenticated session group or `anonymous`.
2. `ComponentManager` calls `checkAccess()` for app and service access.
3. `checkAccess()` calls `Auth::GetAccess(GROUPID, appCode, type, code, operation)`.
4. Access data is cached through `CacheData`.

View objects:

1. `Auth::ViewObjects()` builds lists of visible object IDs.
2. Lists are cached in session as:
   - `$_SESSION["viewObjects"]`
   - `$_SESSION["viewObjects_e"]`
   - `$_SESSION["viewObjects_f"]`
3. MySQL `Query()` appends `sysviewobject in (...)` unless `viewObject=false`.
4. `sysadmin` receives broad access in the auth connector.

## 15. Application Descriptor Model

Each app has an `app.json`. Example shape:

```json
{
  "components": {
    "sample-input-form": {
      "type": "component",
      "location": "apps"
    },
    "app-handler": {
      "type": "service",
      "location": "service"
    }
  },
  "description": {
    "title": "Sample App",
    "author": "Davvag",
    "version": "0.1",
    "icon": "appicon.png"
  },
  "tags": ["showincms", "showindock"],
  "configuration": {
    "webdock": {
      "startupComponent": "sample-input-form",
      "onLoad": ["app-handler"],
      "routes": {
        "partials": {
          "/test": "test-form",
          "/app": "sample-popup"
        }
      }
    }
  }
}
```

Component descriptors define resources:

```json
{
  "resources": {
    "files": [
      {
        "type": "mainScript",
        "location": "script.js"
      },
      {
        "type": "mainView",
        "location": "partial.html"
      }
    ],
    "css": [
      {
        "type": "css",
        "location": "component.css"
      }
    ]
  }
}
```

`webdock.js` expects:

| Resource type | Behavior |
| --- | --- |
| `mainScript` | Injected as a script and used to register the component. |
| `mainView` | Downloaded as HTML and inserted into matching placeholders. |
| `css` | Injected as a stylesheet. |
| `script` | Treated as an additional script resource. |
| `tag` | Used as a library marker to prevent duplicate loading. |

## 16. Deployment Notes

### Apache

Required Apache module:

```text
mod_rewrite
```

`pages/install/index.php` checks for `mod_rewrite`.

Ensure `.htaccess` files are honored:

```apache
AllowOverride All
```

### PHP

Required/used PHP extensions observed:

| Extension | Used for |
| --- | --- |
| `curl` | Auth, transformers, REST forwarding. |
| `mysqli` or `mysql` check | MySQL datastore. The install page checks `mysql`, while code uses `mysqli`. |
| `mbstring`/`iconv` | Character conversion in MySQL result mapping. |
| `zip` or shell `zip`/PowerShell | Hosting backups and generated archives. |
| `openssl` | Usually required by mail/TLS and SDKs. |

Enable PHP sessions and ensure the PHP session save path is writable.

### cPanel

No `.cpanel.yml` exists in this checkout. If using cPanel Git Version Control deployment, add a root `.cpanel.yml` that copies framework files, preserves tenant resource/media folders, and runs any needed Composer installs for plugin vendors.

Recommended cPanel considerations:

1. Set document root to the repository root or public deployment copy that contains `index.php`.
2. Preserve `.htaccess` files.
3. Configure `DAVVAGCONFIG` if production config should live outside the repo.
4. Keep `config.json`, DB credentials, SMTP configs, tenant `global/`, and media folders out of public direct access.
5. Make `MEDIA_FOLDER` writable by the PHP user.
6. Validate `files/.htaccess`, because it rewrites to `/var/media/...`.

### Database

`DB_CONFIG_FILE` must point to JSON with at least:

```json
{
  "mysql_server": "localhost",
  "mysql_username": "user",
  "mysql_password": "password",
  "init_db": "prefix_"
}
```

The final DB name is:

```text
{init_db}{DATASTORE_DOMAIN with dots replaced by underscores}
```

### Media and Cache

`MEDIA_FOLDER` is used for:

| Path | Purpose |
| --- | --- |
| `{MEDIA_FOLDER}/cache/...` | Framework object cache. |
| `{MEDIA_FOLDER}/{DATASTORE_DOMAIN}/...` | App media and uploads. |
| `{MEDIA_FOLDER}/backup/{DATASTORE_DOMAIN}/...` | Hosting backup output. |

## 17. Guidelines for Extending DAVVAG

When adding a new app:

1. Create a new folder under `{TENANT_RESOURCE_LOCATION}/apps/{appCode}`.
2. Add `app.json`.
3. Add `app.php` if this app can be a startup app or needs its own page shell.
4. Add component folders under a clear location such as `components/`, `apps/`, `shell/`, `partials/`, or `services/`.
5. Add each component to `app.json > components`.
6. For frontend components, include `component.json`, `script.js`, `partial.html`, and optional CSS.
7. For service components, include `component.json` with `serviceHandler`, plus `service.php`.
8. For direct data operations, either implement PHP service methods using `SOSSData` or declare `transformers` with `destUrl: "SOSSData"`.
9. Add any new schemas under `{TENANT_RESOURCE_LOCATION}/schemas/{namespace}.json`.
10. Register the app in `{TENANT_RESOURCE_LOCATION}/tenant.json > apps`.
11. Add app permissions to tenant group JSON files such as `anonymous.json`, `web_user.json`, or `sysadmin.json`.
12. If the app should appear in dock/CMS, add tags such as `showindock` or `showincms`.
13. If the app should be the startup app, update `tenant.json > webdock > events > onStartup`.

Naming conventions:

| Item | Convention |
| --- | --- |
| `appCode` | Lowercase hyphenated, for example `my-sales-app`. |
| Component name | Lowercase hyphenated, for example `order-list`. |
| Service method key | Descriptive action name, for example `Save`, `login`, `q`. |
| PHP handler method | `get{Name}` or `post{Name}`, matching the URL handler segment through `ucwords()`. |
| Data namespace | Usually matches schema/table name, for example `orders`, `profile`, `user_view_objects`. |

Avoid editing framework core for app behavior unless a capability must be shared by all apps. Prefer app-level service handlers and descriptors.

## 18. Example Workflow: Create a New DAVVAG App

Goal: create `hello-davvag` with one UI component and one POST service.

### Step 1: Create app folder

```text
{TENANT_RESOURCE_LOCATION}/apps/hello-davvag/
```

Suggested structure:

```text
hello-davvag/
  app.json
  app.php
  components/
    hello-card/
      component.json
      script.js
      partial.html
      hello-card.css
  services/
    hello-api/
      component.json
      script.js
      service.php
```

### Step 2: Add `app.json`

```json
{
  "components": {
    "hello-card": {
      "type": "component",
      "location": "components"
    },
    "hello-api": {
      "type": "service",
      "location": "services"
    }
  },
  "description": {
    "title": "Hello DAVVAG",
    "author": "DAVVAG",
    "version": "0.1",
    "icon": "appicon.png"
  },
  "tags": ["showindock"],
  "configuration": {
    "webdock": {
      "startupComponent": "hello-card",
      "onLoad": ["hello-api"],
      "routes": {
        "partials": {
          "/": "hello-card"
        }
      }
    }
  }
}
```

### Step 3: Add `app.php`

Use an existing app entry file as a template. At minimum it should load a page that includes `lib/jquery.js`, `lib/webdock.js`, and a `webdockapp="hello-davvag"` script attribute.

### Step 4: Add UI component descriptor

```json
{
  "name": "hello-card",
  "description": "Hello DAVVAG UI component",
  "author": "DAVVAG",
  "version": "0.1",
  "resources": {
    "files": [
      {
        "type": "mainScript",
        "location": "script.js"
      },
      {
        "type": "mainView",
        "location": "partial.html"
      }
    ],
    "css": [
      {
        "type": "css",
        "location": "hello-card.css"
      }
    ]
  }
}
```

### Step 5: Add service descriptor

```json
{
  "name": "hello-api",
  "description": "Hello DAVVAG service",
  "author": "DAVVAG",
  "version": "0.1",
  "resources": {
    "files": [
      {
        "type": "mainScript",
        "location": "script.js"
      }
    ]
  },
  "serviceHandler": {
    "file": "service.php",
    "class": "hello_davvag\\HelloApi",
    "methods": {
      "Save": {
        "method": "POST"
      }
    }
  },
  "transformers": {}
}
```

### Step 6: Add `service.php`

```php
<?php
namespace hello_davvag;

require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");

class HelloApi {
    public function postSave($req, $res) {
        $data = $req->Body(true);
        return [
            "message" => "Saved by DAVVAG",
            "input" => $data
        ];
    }
}
?>
```

### Step 7: Register app in `tenant.json`

```json
{
  "apps": {
    "hello-davvag": {
      "version": "latest"
    }
  }
}
```

Preserve existing apps while adding the new entry.

### Step 8: Grant access

Update the appropriate group descriptor, for example `{TENANT_RESOURCE_LOCATION}/anonymous.json` or `{TENANT_RESOURCE_LOCATION}/web_user.json`, so `ComponentManager::GetAllApps()` and `checkAccess()` allow the app and service calls.

### Step 9: Test endpoints

Expected descriptor endpoints:

```text
GET /components/object/appdescriptor/hello-davvag
GET /components/hello-davvag/hello-card/object?object=desc
GET /components/hello-davvag/hello-card/file/script.js
POST /components/hello-davvag/hello-api/service/Save
```

If the POST URL does not hit `postSave()`, verify that the handler segment and method name match the framework convention.

## 19. AI Agent Implementation Checklist

Before modifying or generating code:

1. Read `configloader.php` and confirm the effective `RESOURCE_LOCATION` and `HOST_NAME`.
2. Locate the active tenant folder from `TENANT_RESOURCE_LOCATION`.
3. Read tenant `tenant.json`.
4. Read the target app `app.json`.
5. Read the target component `component.json`.
6. For services, inspect the configured `serviceHandler.file` and `serviceHandler.class`.
7. For data changes, inspect the schema in `schemas/{namespace}.json`.
8. For frontend changes, preserve the Webdock resource contract: `mainScript`, `mainView`, and optional `css`.
9. For auth-sensitive behavior, verify `GROUPID`, `Auth::GetAccess()`, and view-object filtering.
10. Avoid writing directly under `davvag-core/` if it is deployed as protected tenant resources unless the change is intentionally tenant data/app code.

## 20. Known Caveats and Risks

| Area | Observation |
| --- | --- |
| CORS | `Access-Control-Allow-Origin: *` is combined with `Access-Control-Allow-Credentials: true`, which browsers reject for credentialed cross-origin requests. |
| Security cookies | Cookies are set without explicit `HttpOnly`, `Secure`, or `SameSite`. |
| Virtual firewall | `VirtualFirewall::checkPermission()` returns `true` immediately, so its detailed admin check is inactive. |
| SQL escaping | `mysqlConnector` builds SQL strings directly from values. Treat input validation and escaping as high priority before exposing public write endpoints. |
| Install check | `pages/install/index.php` checks PHP extension `mysql`, while runtime code uses `mysqli`. |
| Config paths | The checked-in `config.json` may point outside the repository. Confirm `RESOURCE_LOCATION` before assuming `davvag-core/localhost` is active. |
| cPanel | No `.cpanel.yml` exists. Deployment automation must be added explicitly if needed. |

## 21. Quick Reference

Common paths:

```text
Root entry:              index.php
Admin entry:             admin.php
Config loader:           configloader.php
Tenant selector:         init.php
Component API:           components/index.php
Router:                  components/carbite.php
Component dispatcher:    components/component_manager.php
Auth facade:             plugins/auth/auth.php
Data facade:             plugins/sossdata/SOSSData.php
Frontend loader:         lib/webdock.js
Tenant registry:         {TENANT_RESOURCE_LOCATION}/tenant.json
App descriptor:          {TENANT_RESOURCE_LOCATION}/apps/{appCode}/app.json
Component descriptor:    {TENANT_RESOURCE_LOCATION}/apps/{appCode}/{location}/{component}/component.json
Schema descriptor:       {TENANT_RESOURCE_LOCATION}/schemas/{namespace}.json
```

Common URLs:

```text
/                                  Startup app
/admin                             Admin startup app
/components/object/apps             App list for current group
/components/object/appdescriptor/{appCode}
/components/{appCode}/{component}/object?object=desc
/components/{appCode}/{component}/file/{filePath}
/components/{appCode}/{component}/service/{handlerName}
/components/{appCode}/{component}/transform/{route}
/assets/{appCode}/{assetPath}
```

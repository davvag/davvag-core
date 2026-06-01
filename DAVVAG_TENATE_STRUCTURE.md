# DAVVAG Tenant Structure

Generated from a local scan of `C:\xampp\htdocs\git\davvag-core\davvag-core\localhost` on 2026-06-01.

This document describes the DAVVAG tenant/domain resource structure and how it interacts with the PHP framework in `C:\xampp\htdocs\git\davvag-core`. It is written for AI agents and developers that need to generate, modify, or reason about DAVVAG applications.

Note: the requested output filename is `DAVVAG_TENATE_STRUCTURE.md`. The word `TENATE` is preserved exactly as requested.

## 1. High-Level Model

DAVVAG is a PHP framework that separates framework code from tenant/domain resources.

The framework root contains runtime entry points and shared engine code:

```text
C:\xampp\htdocs\git\davvag-core
  index.php
  admin.php
  configloader.php
  init.php
  components/
  plugins/
  lib/
  davvag-core/
```

The tenant resource root contains one folder per tenant/domain:

```text
C:\xampp\htdocs\git\davvag-core\davvag-core
  localhost/
```

The scanned tenant is:

```text
C:\xampp\htdocs\git\davvag-core\davvag-core\localhost
```

At runtime:

1. `index.php` loads `configloader.php` and `init.php`.
2. `configloader.php` defines `TENANT_RESOURCE_LOCATION`.
3. `init.php` reads `{TENANT_RESOURCE_LOCATION}/tenant.json`.
4. The configured startup app is loaded from `{TENANT_RESOURCE_LOCATION}/apps/{appCode}/app.php`.
5. Frontend components and backend services are loaded through `/components/...`.

Important source spelling: the framework calls `SOSSPlatform::intialize()`.

## 2. Tenant Root Overview

The scanned tenant folder contains:

```text
localhost/
  apps/
  davvag-flow/
  global/
  plugins/
  schemas/
  anonymous.json
  facebook_user.json
  seller.json
  sysadmin.json
  sysuser.json
  web_user.json
  tenant.json
  config.json
  sossgrid.conf
  auth-0.0.1-SNAPSHOT.jar
```

Folder and file roles:

| Path | Role |
| --- | --- |
| `apps/` | Tenant applications. The checkout contains 63 app folders. Each app is described by `app.json` and may include `app.php`, frontend components, backend services, assets, shell components, and pages. |
| `davvag-flow/` | Tenant workflow definitions. The checkout contains 11 workflow JSON files. Workflows are executed dynamically by the `DavvagFlow` plugin. |
| `global/` | Shared tenant configuration and templates, including SMTP config, global values, app notification templates, and email templates. |
| `plugins/` | Tenant-local plugins. Components can load these by declaring dependencies with `plugin_location: "local"`. |
| `schemas/` | Schema definitions used by `SOSSData` to create or update database tables on demand. The checkout contains 168 root schema JSON files. |
| `tenant.json` | Tenant application registry and startup app selection. |
| `config.json` | Tenant/domain config override. Defines tenant-level constants such as auth/data domains, app URLs, currency, and provider keys. |
| `{group}.json` | User-group app access descriptors, for example `anonymous.json`, `web_user.json`, and `sysadmin.json`. |
| `sossgrid.conf` | MySQL connection config used when the root config points `DB_CONFIG_FILE` here. |

## 3. `davvag-core` Tenant Container

In this repository, `davvag-core/` is the tenant/domain container:

```text
davvag-core/
  localhost/
```

In a production deployment this folder can contain tenant folders named after domains, for example:

```text
davvag-core/
  davvag.com/
  example.com/
  localhost/
```

Each tenant folder owns:

1. Its own applications under `apps/`.
2. Its own `tenant.json` registry.
3. Its own `config.json` overrides.
4. Its own user-group JSON access files.
5. Its own schemas, workflows, templates, and local plugins.

The framework protects direct access to the tenant container with `davvag-core/.htaccess`:

```apache
<Files ~ "^.*">
  Deny from all
</Files>
```

The tenant files are not intended to be served directly. They are resolved through framework entry points, component routing, and Apache rewrite rules.

## 4. Tenant Configuration

Tenant config file:

```text
davvag-core/localhost/config.json
```

This file overrides or adds tenant-specific constants after the main framework config is loaded. The scanned tenant config includes variables such as:

```text
MAIN_STORE_DOMAIN
AUTH_DOMAIN
DATASTORE_DOMAIN
MAPS_APIKEY
FBLAPP_ID
FBLAPP_S
FBCAPP_DOMAIN
APPURL
GOOGLE_ID
GOOGLE_S
FB_CHALLENGE
FB_MSG_APP_S
CURRENCY_CODE
```

Do not hard-code secret values into generated documentation, code comments, or client-side files. Provider secrets and SMTP credentials should be kept in server-side config and protected by `.htaccess` or an external config folder.

Framework config loading order:

1. Root `configloader.php` loads root `config.json`.
2. It attempts domain config from `{configFolder}/davvag-core/{HTTP_HOST}/config.json`.
3. It defines default `HOST_NAME`, `DATASTORE_DOMAIN`, and `AUTH_DOMAIN` when missing.
4. It defines:

```text
TENANT_RESOURCE_LOCATION      = RESOURCE_LOCATION / HOST_NAME
TENANT_RESOURCE_LOCATION_APPS = TENANT_RESOURCE_LOCATION / apps
PLUGIN_PATH                   = framework root / plugins
PLUGIN_PATH_LOCAL             = TENANT_RESOURCE_LOCATION / plugins
SCHEMA_PATH                   = TENANT_RESOURCE_LOCATION / schemas
```

## 5. Application Registry: `tenant.json`

Tenant application registry:

```text
davvag-core/localhost/tenant.json
```

Primary structure:

```json
{
  "apps": {
    "davvag-cms": {
      "version": "latest"
    },
    "dock": {
      "version": "latest"
    }
  },
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

Runtime behavior:

1. `init.php` reads `tenant.json`.
2. `tenant.json > webdock > events > onStartup` selects the startup app.
3. `admin.php` defines `IS_ADMIN_MODE=true`, so `onStartup.admin` is used.
4. Normal `index.php` mode uses `onStartup.default`.
5. The selected app is loaded from:

```text
{TENANT_RESOURCE_LOCATION}/apps/{startupApp}/app.php
```

In this tenant:

| Mode | Startup app |
| --- | --- |
| Default | `davvag-cms` |
| Admin | `dock` |

## 6. User Group Permission Files

The tenant root contains JSON files that represent app access for user groups:

```text
anonymous.json
facebook_user.json
seller.json
sysadmin.json
sysuser.json
web_user.json
```

These files use the same broad shape as `tenant.json`:

```json
{
  "apps": {
    "userapp": {
      "version": "latest"
    }
  },
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

Framework usage:

1. `components/index.php` authenticates the user through `Auth::Autendicate()`.
2. It defines `GROUPID` from the authenticated user's group.
3. If no user is authenticated, `GROUPID` is `anonymous`.
4. `ComponentManager::GetAllApps()` reads `{TENANT_RESOURCE_LOCATION}/{GROUPID}.json`.
5. It returns installed apps from that group descriptor.
6. `checkAccess()` and `Auth::GetAccess()` enforce app/service access for component and service requests.

Observed examples:

| Group file | Role |
| --- | --- |
| `anonymous.json` | Very limited public app list. |
| `web_user.json` | Authenticated web user app list. |
| `sysadmin.json` | Broad administrative app list, including `davvag-sample-app-1`. |

When adding a new app, update both `tenant.json` and the intended group JSON files.

## 7. Application Folder Lifecycle

Applications live under:

```text
davvag-core/localhost/apps/{appCode}/
```

The scanned tenant has 63 application folders, including:

```text
davvag-cms
dock
davvag-sample-app-1
davvag-app-manager
davvag-attributes
davvag-tools
davvag-useradmin
profileapp
productapp
romashop
stelup_shop
```

Typical application structure:

```text
apps/{appCode}/
  app.json
  app.php
  assets/
  components/
  apps/
  services/
  service/
  shell/
  partials/
  dependencies/
```

The exact subfolder names are app-specific. The framework does not require every app to use the same component location names. Instead, each component's folder location is declared in `app.json`.

Example from `davvag-sample-app-1/app.json`:

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

Component resolution:

```text
{TENANT_RESOURCE_LOCATION}/apps/{appCode}/{location}/{componentName}/component.json
```

For the sample app:

```text
apps/davvag-sample-app-1/apps/sample-input-form/component.json
apps/davvag-sample-app-1/service/app-handler/component.json
```

## 8. Component Descriptor Model

Frontend component descriptor example:

```json
{
  "name": "Sample Input Form",
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
        "location": "sample-input-form.css"
      }
    ]
  }
}
```

Resource roles:

| Resource type | Role |
| --- | --- |
| `mainScript` | JavaScript loaded by Webdock. Usually registers and configures a component. |
| `mainView` | HTML fragment injected into matching `[webdock-component]` elements. |
| `css` | Stylesheet loaded for the component. |
| `script` | Extra JavaScript dependency. |
| `tag` | Library marker used by the frontend loader to avoid duplicate library loads. |

Frontend loading flow:

1. App page includes `lib/webdock.js` with a `webdockapp` attribute.
2. `webdock.js` requests `/components/object/appdescriptor/{appCode}`.
3. It reads `configuration.webdock.onLoad` and HTML `[webdock-component]` markers.
4. It requests `/components/{appCode}/{component}/object?object=desc`.
5. It downloads files via `/components/{appCode}/{component}/file/{path}`.
6. It injects CSS, script, and view HTML.
7. It exposes service and transformer wrappers on the component instance.

## 9. Service Handler Model

Service component descriptor example:

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
  },
  "transformers": {}
}
```

Service request path:

```text
/components/{appCode}/{componentName}/service/{handlerName}
```

`ComponentManager::HandleService()` resolves and executes service handlers:

1. Checks access with `checkAccess($res, $appCode, "service", $componentName, $handlerName)`.
2. Reads app descriptor.
3. Reads component descriptor.
4. Loads declared PHP dependencies.
5. Requires the configured `serviceHandler.file`.
6. Instantiates the configured `serviceHandler.class`.
7. Calls the method:

```text
strtolower(REQUEST_METHOD) + ucwords(handlerName)
```

Examples:

| Request | Handler method |
| --- | --- |
| `GET /components/my-app/api/service/login` | `getLogin($req, $res)` |
| `POST /components/my-app/api/service/Save` | `postSave($req, $res)` |
| No method-specific handler | `__handle($req, $res)` fallback |

Handler request and response objects:

| Object | Important methods |
| --- | --- |
| `$req` | `Params()`, `Query()`, `Headers()`, `Body($json=false)` |
| `$res` | `Set()`, `SetJSON()`, `SetError()`, `SetContentType()` |

Return value behavior:

1. Normal return values are wrapped as `{ "success": true, "result": value }`.
2. `$res->SetError(...)` marks the response as an error.
3. `writeResponse()` adds CORS headers to JSON responses.

## 10. Transformer Model

Transformers are declared in `component.json` and called through:

```text
/components/{appCode}/{componentName}/transform/{route}
```

Two patterns are supported.

External REST transformer:

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

Direct `SOSSData` transformer:

```json
{
  "transformers": {
    "save": {
      "method": "POST",
      "route": "/save",
      "destUrl": "SOSSData",
      "destMethod": "insert",
      "namespace": "my_schema"
    }
  }
}
```

Supported direct data operations:

| `destMethod` | Result |
| --- | --- |
| `insert` | Calls `SOSSData::Insert(namespace, body)`. |
| `update` | Calls `SOSSData::Update(namespace, body)`. |
| `delete` | Calls `SOSSData::Delete(namespace, body)`. |
| `query` | Calls `SOSSData::Query(namespace, query, lastID, sorting, pageSize, fromPage)`. |

External REST transformers are handled by `CarbiteTransform`. It forwards body data and route parameters, mirrors the destination content type and HTTP status, and clears relevant cache entries for write operations.

## 11. `davvag-flow` Workflow Execution

Tenant workflow files live in:

```text
davvag-core/localhost/davvag-flow/
```

Scanned workflow files include:

```text
davvag-flow/1.json
davvag-flow/english.json
davvag-flow/idle.json
davvag-flow/lasitha.json
davvag-flow/player.json
davvag-flow/sinhala.json
davvag-flow/supun.json
davvag-flow/tamil.json
davvag-flow/testflow.json
davvag-flow/davvag-attributes/testflow.json
davvag-flow/davvag-attributes/stelup_dispatch_order.json
```

Workflow executor:

```text
davvag-core/localhost/plugins/davvag-flow/flow.php
```

Global framework also has:

```text
plugins/davvag-flow/flow.php
```

The tenant-local flow implementation supports node types including:

| `urntype` | Behavior |
| --- | --- |
| `class` | Loads an activity file from `PLUGIN_PATH/davvag-flow/lib` and calls a static class method. |
| `service` | Loads an app service component and calls its service method through synthetic `FReq` and `FRes` objects. |
| `create_object` | Builds a new object from workflow variables. |

Execution call shape:

```php
DavvagFlow::Execute($namespace, $flowid, $inputData);
```

Path resolution:

| Arguments | Workflow file |
| --- | --- |
| `$namespace` is null | `{TENANT_RESOURCE_LOCATION}/davvag-flow/{flowid}.json` |
| `$namespace` is set | `{TENANT_RESOURCE_LOCATION}/davvag-flow/{namespace}/{flowid}.json` |

Execution model:

1. Load workflow JSON.
2. Initialize `excutionStack`, `outData`, and a generated workflow id.
3. Start at `start_up_node` unless a specific step is supplied.
4. Execute the node method.
5. Store returned values in `outData` when `method.return` is true.
6. Continue to `success` step on success.
7. Continue to `fail` step on exception if configured.
8. Append debug and error logs to `excutionStack`.
9. Return the execution data object.

When generating workflows, include explicit `success` and `fail` paths where possible so failures are observable and recoverable.

## 12. `global` Folder

Tenant global folder:

```text
davvag-core/localhost/global/
```

Observed structure:

```text
global/
  config/
    emailsmtp.conf
    globals.conf
  templetes/
    app/
      *.jnx
    email/
      *.tmp
```

Roles:

| Path | Role |
| --- | --- |
| `global/config/emailsmtp.conf` | SMTP configuration used by `Notify::sendEmailMessage()`. Treat as secret. |
| `global/config/globals.conf` | Global replacement values used in templates. |
| `global/templetes/email/*.tmp` | Email templates used by auth and notification flows. |
| `global/templetes/app/*.jnx` | App or notification templates used by tenant app logic. |

Note: folder name is spelled `templetes` in source. Preserve that spelling when using existing paths.

## 13. Tenant Plugins

Tenant plugins live in:

```text
davvag-core/localhost/plugins/
```

Observed plugin folders include:

```text
auth
davvag-attributes
davvag-flow
davvag-ipg
davvag-order
davvag-summary
Facebook
Google
notify
phpcache
phpmailer
profile
profile-stelup
sossdata
stripe
transactions
```

The tenant plugin folder contains `.htaccess` that denies direct HTTP access:

```apache
<Files ~ "^.*">
  Deny from all
</Files>
```

Plugin loading patterns:

1. Global framework plugins are loaded from root `plugins/` through root `config.json > DAVVAG_StartUp`.
2. Tenant-local plugins can be loaded by component dependency declarations.
3. `ComponentManager::HandleService()` supports dependencies with `plugin_location` values:
   - `global`: load from `PLUGIN_PATH`.
   - `local`: load from `PLUGIN_PATH_LOCAL`.
   - other/default: load from explicit path.

Example dependency shape:

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

Plugin extension guidelines:

1. Put tenant-specific reusable code under `{TENANT_RESOURCE_LOCATION}/plugins/{pluginName}`.
2. Keep plugin entry files small and explicit, for example `{pluginName}.php`.
3. Avoid emitting output during plugin load.
4. Avoid relying on global mutable state unless the existing plugin pattern already requires it.
5. Declare plugin dependencies in component descriptors instead of requiring local plugin files from unrelated app code.
6. Use `PLUGIN_PATH_LOCAL` for tenant plugins and `PLUGIN_PATH` for framework plugins.
7. Keep secrets in `global/config` or external config, not inside plugin source.

## 14. Schema-Driven Database Setup

Tenant schemas live in:

```text
davvag-core/localhost/schemas/
```

Observed schema structure:

```text
schemas/
  *.json
  *.sql
  attributes/
  backup/
  mysqlquery/
  query/
```

Roles:

| Path | Role |
| --- | --- |
| `schemas/*.json` | Data schema definitions used by `SOSSData` and the MySQL connector. |
| `schemas/attributes/` | Attribute schema versions and generated attribute definitions. |
| `schemas/mysqlquery/` | SQL/raw query helper files used by `SOSSData::ExecuteRaw()`. |
| `schemas/query/` | Query helper files. |
| `schemas/backup/` | Schema backups or historical generated schema files. |

Schema loader:

```text
plugins/sossdata/phpmysql/schema.php
```

Schema lookup:

```php
Schema::Get($name)
```

File resolved:

```text
{TENANT_RESOURCE_LOCATION}/schemas/{name}.json
```

Schema fields example:

```json
{
  "fields": [
    {
      "fieldName": "domain",
      "dataType": "java.lang.String",
      "annotations": {
        "isPrimary": true,
        "maxLen": 100
      }
    }
  ]
}
```

Supported field concepts:

| Field key | Meaning |
| --- | --- |
| `fieldName` | Column/property name. |
| `dataType` | Logical type mapped by the MySQL connector. |
| `annotations.isPrimary` | Marks primary key fields. |
| `annotations.autoIncrement` | Enables MySQL auto increment for supported numeric fields. |
| `annotations.maxLen` | Controls VARCHAR/TEXT sizing behavior. |
| `annotations.encoding` | Optional text encoding/charset behavior. |
| `annotations.default` | Optional default value. |

System columns added by framework:

```text
sysversionid
syscreated
sysupdated
sysviewobject
syscreatedby
syslastupdatedby
```

Database behavior:

1. `SOSSData::Query`, `Insert`, `Update`, and `Delete` select a connector from config.
2. The current root config uses `phpmysql`.
3. `mysqlConnector` reads DB credentials from `DB_CONFIG_FILE`.
4. Missing databases can be created automatically.
5. Missing tables or columns can be created from schema definitions.
6. Queries are filtered by `sysviewobject` unless view-object filtering is disabled by the caller.

Use schema files as installation helpers: adding a schema can allow the framework to create the backing table when first used.

## 15. Runtime Component Endpoints

All routes are handled by:

```text
components/index.php
```

Base URL:

```text
/components/
```

Core routes:

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/object/apps` | Return apps allowed for current `GROUPID`. |
| `GET` | `/object/appdescriptor/{appCode}` | Return `{appCode}/app.json`. |
| `GET` | `/object/appicon/{appCode}` | Return app icon. |
| `GET` | `/{appCode}/{componentName}/object?object=desc` | Return component descriptor. |
| `GET` | `/{appCode}/{componentName}/object?object=resource&resource=attributes&file={name}` | Return schema/attribute resources. |
| `GET` | `/{appCode}/{componentName}/file/{filePath}` | Serve component file. |
| `GET` | `/{appCode}/{componentName}/service/{handlerName}` | Invoke service handler. |
| `POST` | `/{appCode}/{componentName}/service/{handlerName}` | Invoke service handler. |
| `GET` | `/{appCode}/{componentName}/transform/{route}` | Invoke transformer. |
| `POST` | `/{appCode}/{componentName}/transform/{route}` | Invoke transformer. |

## 16. Sessions, Cookies, and Access

Core component requests start sessions in `components/index.php`:

```php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
```

Authentication flow:

1. Login calls `Auth::Login()`.
2. On success, auth data is stored in `$_SESSION["authData"]`.
3. `securityToken` cookie is set.
4. Later requests call `Auth::Autendicate()`.
5. If session data exists, it is used.
6. Otherwise `securityToken` is used to restore a session through `Auth::GetSession()`.
7. `GROUPID` is set from the user group or defaults to `anonymous`.

Important cookies observed:

| Cookie | Role |
| --- | --- |
| `securityToken` | Primary session restore token. |
| `authData` | Auth data stored by some app flows. |
| `sosskey` | Forwarded token for some service/auth calls. |
| `Location` | Location data used by shop/cart flows. |

Production hardening:

1. Add `HttpOnly`, `Secure`, and `SameSite` flags where compatible.
2. Avoid exposing `authData` cookie to client-side JavaScript unless required.
3. Use HTTPS when cookies represent authentication state.
4. Review old app-level auth handlers before exposing them publicly.

## 17. CORS and Response Handling

`components/common.php` wraps JSON responses and emits CORS headers:

```http
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Allow-Credentials: true
```

Important deployment note: browsers reject credentialed CORS responses when `Access-Control-Allow-Origin` is `*`. Because `webdock.js` uses credentialed AJAX requests, production cross-origin deployments should echo a trusted origin instead of `*`.

There is no central `OPTIONS` preflight route in the current component router. If deploying APIs cross-origin, add explicit `OPTIONS` handling before Carbite dispatch.

## 18. Apache and cPanel Deployment Notes

Required Apache behavior:

1. `mod_rewrite` must be enabled.
2. `.htaccess` files must be honored with `AllowOverride All`.
3. Direct access to tenant resource folders should remain denied.
4. Public requests should go through `index.php`, `admin.php`, `components/index.php`, or configured asset/file rewrite rules.

Relevant rewrite files:

| File | Purpose |
| --- | --- |
| Root `.htaccess` | Routes `/admin` and admin assets. |
| `components/.htaccess` | Routes component API requests to `components/index.php`. |
| `assets/.htaccess` | Maps asset URLs to tenant app asset folders. |
| `davvag-core/.htaccess` | Denies direct access to tenant resources. |
| `tenant/plugins/.htaccess` | Denies direct access to tenant plugins. |

cPanel notes:

1. No `.cpanel.yml` was found in the project root during the earlier root scan.
2. If using cPanel Git deployment, create `.cpanel.yml` explicitly.
3. Preserve `.htaccess` files during deployment.
4. Keep production config and secrets outside public web access when possible.
5. Ensure `MEDIA_FOLDER`, cache folders, and backup folders are writable by the PHP user.
6. Confirm the tenant folder name matches `HOST_NAME` or `LOCAL_DEV_HOST`.

PHP requirements observed:

| Extension/tool | Used for |
| --- | --- |
| `curl` | REST calls, auth forwarding, transformers. |
| `mysqli` | MySQL datastore connector. |
| `mbstring`/`iconv` | String encoding handling. |
| PHP sessions | Auth state and per-user cache/access state. |
| `zip` or shell zip/PowerShell | Backup utilities. |

## 19. Extending the Framework With a New App

Use this process for a new tenant app.

### Step 1: Create App Folder

```text
davvag-core/localhost/apps/my-new-app/
```

Suggested structure:

```text
my-new-app/
  app.json
  app.php
  components/
    main-view/
      component.json
      script.js
      partial.html
      main-view.css
  services/
    api/
      component.json
      script.js
      service.php
```

### Step 2: Define App Descriptor

Create:

```text
apps/my-new-app/app.json
```

Example:

```json
{
  "components": {
    "main-view": {
      "type": "component",
      "location": "components"
    },
    "api": {
      "type": "service",
      "location": "services"
    }
  },
  "description": {
    "title": "My New App",
    "author": "DAVVAG",
    "version": "0.1",
    "icon": "appicon.png"
  },
  "tags": ["showindock"],
  "configuration": {
    "webdock": {
      "startupComponent": "main-view",
      "onLoad": ["api"],
      "routes": {
        "partials": {
          "/": "main-view"
        }
      }
    }
  }
}
```

### Step 3: Define UI Component

Create:

```text
apps/my-new-app/components/main-view/component.json
```

Example:

```json
{
  "name": "main-view",
  "description": "Main UI component",
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
        "location": "main-view.css"
      }
    ]
  }
}
```

### Step 4: Define Service Component

Create:

```text
apps/my-new-app/services/api/component.json
```

Example:

```json
{
  "name": "api",
  "description": "Application service API",
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
    "class": "my_new_app\\ApiService",
    "methods": {
      "Save": {
        "method": "POST"
      },
      "List": {
        "method": "GET"
      }
    }
  },
  "transformers": {}
}
```

### Step 5: Implement Service

Create:

```text
apps/my-new-app/services/api/service.php
```

Example:

```php
<?php
namespace my_new_app;

require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");

class ApiService {
    public function postSave($req, $res) {
        $data = $req->Body(true);
        return SOSSData::Insert("my_new_app_items", $data);
    }

    public function getList($req, $res) {
        $query = isset($_GET["query"]) ? $_GET["query"] : "";
        return SOSSData::Query("my_new_app_items", $query);
    }
}
?>
```

### Step 6: Add Schema

Create:

```text
schemas/my_new_app_items.json
```

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
      "fieldName": "title",
      "dataType": "java.lang.String",
      "annotations": {
        "maxLen": 255
      }
    }
  ]
}
```

The table can be created automatically on first query/write by the MySQL connector.

### Step 7: Register App in `tenant.json`

Add the app under `apps`:

```json
{
  "my-new-app": {
    "version": "latest"
  }
}
```

Preserve all existing app entries.

### Step 8: Grant Group Access

Add the app to the appropriate group file, for example:

```text
web_user.json
sysadmin.json
anonymous.json
```

For service-level permission, ensure the auth permission data allows:

```text
appCode = my-new-app
type = service
code = api
operation = Save or List
```

### Step 9: Test Component Endpoints

Expected URLs:

```text
GET  /components/object/appdescriptor/my-new-app
GET  /components/my-new-app/main-view/object?object=desc
GET  /components/my-new-app/main-view/file/script.js
GET  /components/my-new-app/main-view/file/partial.html
POST /components/my-new-app/api/service/Save
GET  /components/my-new-app/api/service/List
```

## 20. AI Agent Checklist

Before generating code:

1. Confirm the active tenant folder from `TENANT_RESOURCE_LOCATION`.
2. Read root `configloader.php` to understand path constants.
3. Read tenant `tenant.json`.
4. Read target group JSON files for app visibility.
5. Read target app `app.json`.
6. Read target component `component.json`.
7. For services, read `serviceHandler.file` and `serviceHandler.class`.
8. For data changes, read or create `schemas/{namespace}.json`.
9. For workflow changes, read `davvag-flow/{flowid}.json` and the executor in `plugins/davvag-flow/flow.php`.
10. For plugin usage, prefer declared dependencies over direct ad hoc includes.
11. Keep tenant-local code under the tenant folder and framework-wide code under root `plugins/` or `components/`.
12. Do not embed secrets from `config.json`, SMTP config, or provider config into generated frontend code.

## 21. Quick Reference

Tenant root:

```text
C:\xampp\htdocs\git\davvag-core\davvag-core\localhost
```

Core tenant paths:

```text
apps/                         Tenant apps
davvag-flow/                  Workflow JSON files
global/config/                Tenant global config
global/templetes/             Tenant templates
plugins/                      Tenant-local plugins
schemas/                      Database schema definitions
tenant.json                   App registry and startup app mapping
config.json                   Tenant config overrides
{group}.json                  Group app visibility/access descriptors
```

Core framework paths:

```text
index.php                     Default entry
admin.php                     Admin entry
configloader.php              Config and path constants
init.php                      Tenant startup app loader
components/index.php          Component API entry
components/component_manager.php
components/carbite.php        PHP router
components/common.php         Shared response/access helpers
plugins/auth/auth.php         Auth facade
plugins/sossdata/SOSSData.php Data facade
lib/webdock.js                Frontend component loader
```

Common routes:

```text
/                                  Default startup app
/admin                             Admin startup app
/components/object/apps
/components/object/appdescriptor/{appCode}
/components/{appCode}/{component}/object?object=desc
/components/{appCode}/{component}/file/{filePath}
/components/{appCode}/{component}/service/{handlerName}
/components/{appCode}/{component}/transform/{route}
```

Known caveats:

| Area | Note |
| --- | --- |
| Naming | User request says `app` folder, but this tenant uses `apps/`. Use the existing `apps/` folder name. |
| CORS | `Access-Control-Allow-Origin: *` plus credentials is not valid for browser credentialed cross-origin requests. |
| Cookies | Auth cookies lack explicit hardening flags in several code paths. |
| SQL | MySQL connector builds SQL strings directly; validate and escape inputs before exposing public write APIs. |
| Direct access | Tenant resource folders should remain protected by `.htaccess`. |
| Secrets | Tenant config contains provider keys/secrets. Keep them server-side and avoid generated leaks. |

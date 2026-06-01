# DAVVAG Core

DAVVAG is a PHP-based, tenant-aware application framework. It separates shared framework code from tenant/domain resources, then loads applications from tenant folders using JSON descriptors.

This README is written as the first-stop guide for AI agents doing development in this repository. For deeper reference, read:

- `DAVVAG_PROJECT_STRUCTURE.md` for the full framework-level map.
- `DAVVAG_TENATE_STRUCTURE.md` for the scanned tenant/domain structure under `davvag-core/localhost`.

## Quick Mental Model

The framework root contains the engine:

```text
index.php
admin.php
configloader.php
init.php
components/
plugins/
lib/
davvag-core/
```

The tenant root contains domain-specific resources:

```text
davvag-core/localhost/
  apps/
  davvag-flow/
  global/
  plugins/
  schemas/
  tenant.json
  config.json
  anonymous.json
  web_user.json
  sysadmin.json
```

At runtime:

1. `index.php` loads `configloader.php`.
2. `configloader.php` defines framework constants and loads startup plugins.
3. `init.php` reads `{TENANT_RESOURCE_LOCATION}/tenant.json`.
4. The startup app is selected from `tenant.json > webdock > events > onStartup`.
5. The selected app loads from `{TENANT_RESOURCE_LOCATION}/apps/{appCode}/app.php`.
6. Frontend components and backend services are loaded through `/components/...`.

Note: the source method is spelled `SOSSPlatform::intialize()`.

## Core Runtime Files

| Path | Purpose |
| --- | --- |
| `index.php` | Default public entry point. |
| `admin.php` | Admin entry point; defines `IS_ADMIN_MODE=true` before loading `index.php`. |
| `configloader.php` | Loads root and tenant config, defines constants, and loads startup plugins. |
| `init.php` | Reads `tenant.json` and loads the selected startup app. |
| `components/index.php` | Main API router for app descriptors, component files, services, and transformers. |
| `components/carbite.php` | Lightweight PHP router used by the component API. |
| `components/component_manager.php` | Resolves app/component descriptors and dispatches services or transformers. |
| `components/common.php` | Shared response, CORS, access, and REST helper functions. |
| `plugins/auth/auth.php` | Authentication facade. |
| `plugins/sossdata/SOSSData.php` | Data access facade. |
| `lib/webdock.js` | Frontend component loader and service caller. |

## Important Constants

`configloader.php` defines the constants most agents need:

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

Before making tenant changes, confirm the effective `TENANT_RESOURCE_LOCATION`. The checked-in root config may point outside the repository depending on `RESOURCE_LOCATION` and `LOCAL_DEV_HOST`.

## Tenant Structure

Tenant folders live under `davvag-core/`. In this checkout, the main scanned tenant is:

```text
davvag-core/localhost
```

Tenant folder roles:

| Folder/File | Purpose |
| --- | --- |
| `apps/` | Tenant applications. Each app has an `app.json` descriptor and may have `app.php`, components, services, assets, and dependencies. |
| `tenant.json` | Registers installed apps and defines default/admin startup apps. |
| `config.json` | Tenant-level config override. |
| `{group}.json` | Group app visibility/access files such as `anonymous.json`, `web_user.json`, and `sysadmin.json`. |
| `davvag-flow/` | Tenant workflow JSON files. |
| `global/` | Tenant global config and templates, including SMTP and email templates. |
| `plugins/` | Tenant-local PHP plugins. |
| `schemas/` | JSON database schema definitions and SQL query helpers. |

## Application Model

Applications live at:

```text
{TENANT_RESOURCE_LOCATION}/apps/{appCode}
```

Every app should have:

```text
app.json
```

Apps that can be launched directly should also have:

```text
app.php
```

Example app descriptor shape:

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
      "onLoad": ["app-handler"]
    }
  }
}
```

Component paths are descriptor-driven:

```text
{TENANT_RESOURCE_LOCATION}/apps/{appCode}/{location}/{componentName}/component.json
```

## Component Types

Frontend components usually include:

```text
component.json
script.js
partial.html
*.css
```

Service components usually include:

```text
component.json
script.js
service.php
```

Frontend resource descriptor:

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

Service descriptor:

```json
{
  "serviceHandler": {
    "file": "service.php",
    "class": "my_app\\ApiService",
    "methods": {
      "Save": {
        "method": "POST"
      }
    }
  },
  "transformers": {}
}
```

Service method mapping:

```text
GET  /components/{app}/{component}/service/login -> getLogin($req, $res)
POST /components/{app}/{component}/service/Save  -> postSave($req, $res)
```

If the method-specific handler does not exist, `ComponentManager` tries `__handle($req, $res)`.

## Component API Routes

Common routes:

```text
GET  /components/object/apps
GET  /components/object/appdescriptor/{appCode}
GET  /components/object/appicon/{appCode}
GET  /components/{appCode}/{componentName}/object?object=desc
GET  /components/{appCode}/{componentName}/file/{filePath}
GET  /components/{appCode}/{componentName}/service/{handlerName}
POST /components/{appCode}/{componentName}/service/{handlerName}
GET  /components/{appCode}/{componentName}/transform/{route}
POST /components/{appCode}/{componentName}/transform/{route}
```

Responses are usually wrapped as:

```json
{
  "success": true,
  "result": {}
}
```

## Data Model

Use `SOSSData` for framework-compatible data access:

```php
SOSSData::Insert($namespace, $object);
SOSSData::Update($namespace, $object);
SOSSData::Delete($namespace, $object);
SOSSData::Query($namespace, $query);
SOSSData::ExecuteRaw($namespace, $params);
```

Schema files live at:

```text
{TENANT_RESOURCE_LOCATION}/schemas/{namespace}.json
```

The MySQL connector can create missing databases, tables, and columns from schema files. It also adds system columns:

```text
sysversionid
syscreated
sysupdated
sysviewobject
syscreatedby
syslastupdatedby
```

Query strings use comma-separated `field:value` filters:

```text
email:user@example.com,status:Active
```

Security note: the current MySQL connector builds SQL strings directly. Validate and escape user-controlled input before adding public write or query endpoints.

## Auth and Permissions

Core auth flow:

1. Component API starts a PHP session.
2. `Auth::Autendicate()` checks `$_SESSION["authData"]`.
3. If missing, it tries `$_COOKIE["securityToken"]`.
4. Authenticated users get `GROUPID` from `$user->group`.
5. Unauthenticated users default to `GROUPID=anonymous`.
6. App visibility is read from `{TENANT_RESOURCE_LOCATION}/{GROUPID}.json`.
7. Service access is checked through `Auth::GetAccess()`.

Important cookies:

```text
securityToken
authData
sosskey
Location
```

Production deployments should use secure cookie flags where possible: `HttpOnly`, `Secure`, and `SameSite`.

## Workflow Model

Tenant workflows live in:

```text
{TENANT_RESOURCE_LOCATION}/davvag-flow
```

Execution is handled by the `DavvagFlow` plugin. The tenant-local implementation can execute:

| Node type | Purpose |
| --- | --- |
| `class` | Load an activity class from `davvag-flow/lib` and call a method. |
| `service` | Load an app service component and call a service method. |
| `create_object` | Build an object from workflow variables. |

Call shape:

```php
DavvagFlow::Execute($namespace, $flowid, $inputData);
```

Workflow files resolve to:

```text
{TENANT_RESOURCE_LOCATION}/davvag-flow/{flowid}.json
{TENANT_RESOURCE_LOCATION}/davvag-flow/{namespace}/{flowid}.json
```

## Plugin Model

Global framework plugins live in:

```text
plugins/
```

Tenant-local plugins live in:

```text
{TENANT_RESOURCE_LOCATION}/plugins/
```

Service component descriptors can declare plugin dependencies:

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

Use `plugin_location: "global"` for root plugins and `plugin_location: "local"` for tenant plugins.

## Development Checklist for AI Agents

Before editing:

1. Confirm the active tenant path from `configloader.php` and config constants.
2. Read `tenant.json`.
3. Read the target group JSON file if app visibility or access matters.
4. Read the app's `app.json`.
5. Read the target component's `component.json`.
6. For services, read `service.php` and verify namespace/class/method mapping.
7. For data work, read or create the matching schema JSON.
8. For workflow work, read the workflow JSON and the relevant `DavvagFlow` implementation.
9. Prefer app-level services/components over framework core edits.
10. Preserve existing folder names and source spellings, including `apps`, `templetes`, and `intialize`.

When adding a new app:

1. Create `{TENANT_RESOURCE_LOCATION}/apps/{appCode}`.
2. Add `app.json`.
3. Add `app.php` if launchable.
4. Add component and service folders.
5. Register components in `app.json`.
6. Register the app in `tenant.json`.
7. Add the app to relevant group JSON files.
8. Add schemas under `schemas/` for new datastore namespaces.
9. Test descriptor, file, service, and transform endpoints.

## Deployment Notes

Apache:

```text
mod_rewrite must be enabled.
AllowOverride All is required for .htaccess routing.
```

Important `.htaccess` files:

```text
.htaccess
components/.htaccess
assets/.htaccess
files/.htaccess
davvag-core/.htaccess
davvag-core/localhost/plugins/.htaccess
```

CORS:

The framework emits:

```text
Access-Control-Allow-Origin: *
Access-Control-Allow-Credentials: true
```

Browsers reject credentialed cross-origin requests with wildcard origins. For production cross-origin deployments, echo a trusted origin instead of `*` and add explicit `OPTIONS` handling if needed.

cPanel:

No `.cpanel.yml` is currently present. If using cPanel Git deployment, add one explicitly and make sure tenant config, media folders, cache folders, and secrets are handled safely.

## Safe Editing Rules

- Do not commit secrets from tenant `config.json`, `global/config`, or provider credentials into generated frontend code.
- Do not bypass `Auth::GetAccess()` or group JSON access without a deliberate security reason.
- Do not directly expose `davvag-core/` tenant folders over HTTP.
- Keep reusable framework features in root `plugins/` or `components/`.
- Keep tenant-specific features under `davvag-core/{tenant}/apps` or `davvag-core/{tenant}/plugins`.
- Use `SOSSData` for data access unless a raw SQL helper is already part of the app pattern.
- Keep generated docs self-contained and path-specific so future AI agents can reason without guessing.

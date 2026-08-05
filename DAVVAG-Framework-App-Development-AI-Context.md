# DAVVAG FRAMEWORK — APPLICATION DEVELOPMENT ARCHITECTURE
("C:\xampp\htdocs\davvag-core\DAVVAG-Framework-App-Development-AI-Context.md")
## AI CONTEXT DOCUMENT

**Document role:** Permanent architecture and implementation context  
**System:** DAVVAG Framework Application Development  
**Primary stack:** PHP 8+, DAVVAG tenant-aware framework, Webdock, Vue.js, JavaScript, MySQL through SOSSData, JSON schemas and JSON workflows  
**Status:** Architecture Authority  
**Last repository verification:** 2026-08-05
**Scope:** tenants, applications, components, services, schemas, workflows, plugins, authentication, permissions, AI agents, cross-app reuse, testing, deployment and maintenance

---

# 1. PURPOSE OF THIS DOCUMENT

This document defines the authoritative architecture for developing applications on the DAVVAG Framework.

Any AI coding agent, developer or automation system working on DAVVAG must read and follow this document before:

* creating a tenant
* creating a new application
* modifying an existing application
* adding a component or service
* changing a schema
* writing database logic
* creating a workflow
* adding a plugin dependency
* integrating authentication or permissions
* integrating AI agents
* adding file uploads or media
* creating cross-app integrations
* changing deployment-sensitive behavior

This document is not a tutorial for one application.

It is the permanent architectural context for all DAVVAG application development.

Do not redesign the framework architecture inside an individual feature.

Architectural changes must be intentional, framework-level decisions and documented separately.

---

# 2. FRAMEWORK DEFINITION

DAVVAG is a PHP-based, tenant-aware application framework for building modular business applications.

The framework separates:

```text
SHARED FRAMEWORK ENGINE
        ↓
TENANT / DOMAIN RESOURCES
        ↓
APPLICATIONS
        ↓
COMPONENTS + SERVICES
        ↓
SCHEMAS + WORKFLOWS + PLUGINS
```

A DAVVAG application can provide:

* user interfaces
* business services
* APIs
* workflows
* dashboards
* data management
* commerce
* education
* messaging
* automation
* AI-assisted behavior
* custom domain solutions

The framework must support reuse across applications without copying the same capability into every app.

The core development model is:

```text
DISCOVER
   ↓
REUSE
   ↓
DECLARE
   ↓
BUILD
   ↓
VALIDATE
   ↓
REGISTER
   ↓
TEST
   ↓
DEPLOY
```

---

# 3. FRAMEWORK POSITIONING

DAVVAG is not:

* a single monolithic PHP application
* a conventional Laravel or Yii project
* a collection of unrelated pages
* a direct-to-MySQL coding pattern
* a framework where every app creates its own auth system
* a framework where every app creates its own AI runtime
* a framework where frontend code contains business rules

DAVVAG is:

> A tenant-aware modular application platform where apps are assembled from descriptors, components, services, schemas, workflows, plugins and shared system capabilities.

Applications must conform to the framework instead of creating parallel architectures.

---

# 4. ARCHITECTURAL SOURCE OF TRUTH

DAVVAG has multiple sources of truth, each with a distinct responsibility.

## Framework Runtime Truth

The framework root is authoritative for:

* request bootstrap
* tenant resolution
* component routing
* service dispatch
* authentication integration
* plugin loading
* datastore adapter selection
* frontend component loading

Application code must not duplicate these responsibilities.

## Tenant Truth

The active tenant folder is authoritative for:

* installed applications
* tenant configuration
* app visibility by user group
* schemas
* workflows
* tenant-local plugins
* templates

## Application Truth

Each app's `app.json` is authoritative for:

* registered components
* registered services
* startup component
* on-load components
* routes
* subapps
* app metadata
* install/runtime dependencies

## Component Truth

Each `component.json` is authoritative for:

* component resources
* frontend scripts and views
* styles
* service handlers
* service methods
* plugin dependencies
* transformer definitions

## Data Truth

Tenant schema JSON files are authoritative for:

* logical namespaces
* fields
* data types
* primary keys
* defaults
* relationships
* raw query result shapes

## Workflow Truth

Workflow JSON files are authoritative for orchestration.

Do not hide workflow behavior inside unrelated frontend code.

---

# 5. REQUIRED READING ORDER

Before changing DAVVAG code, read the documentation relevant to the task.

Minimum order:

```text
README.md
01-framework-overview.md
02-tenant-setup.md
03-application-development.md
04-components-and-services.md
```

For data work:

```text
05-database-schemas.md
11-app-developer-guide.md
```

For workflow work:

```text
06-workflows.md
```

For plugin work:

```text
07-plugins.md
```

For authentication or permission-sensitive work:

```text
08-auth-sessions-permissions.md
```

For deployment-sensitive work:

```text
09-deployment.md
```

For AI coding agents:

```text
10-ai-agent-playbook.md
```

For reusable app patterns:

```text
12-reusable-app-patterns.md
```

Do not begin by generating code from assumptions when the framework already documents the pattern.

---

# 6. SHARED TERMINOLOGY

Every developer and AI agent must use these terms consistently.

## Framework Root

The shared engine and runtime code.

Typical contents:

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

## Tenant

A domain-specific resource folder under `davvag-core/`.

Examples:

```text
davvag-core/localhost
davvag-core/example.com
davvag-core/customer-a.com
```

A tenant owns apps, schemas, workflows, local plugins, configuration and group access files.

## Active Tenant

The tenant resolved by runtime configuration.

Never assume `davvag-core/localhost` is active.

## App

A business capability package under:

```text
{TENANT_RESOURCE_LOCATION}/apps/{app-code}
```

## Component

A reusable frontend or shell unit loaded by Webdock.

## Service Component

A component that exposes backend PHP handlers through the DAVVAG component API.

## Shell Component

A shared runtime component supplied by the active dock or CMS shell.

Examples:

```text
soss-routes
app_popup
soss-validator
soss-uploader
soss-data
auth-handler
```

## Schema Namespace

A logical data object name mapped to:

```text
{TENANT_RESOURCE_LOCATION}/schemas/{namespace}.json
```

Application code uses namespaces, not physical table names.

## Workflow

A JSON-defined orchestration executed by `DavvagFlow`.

## Plugin

A reusable PHP capability loaded globally or from the active tenant.

## Group File

A tenant JSON file that controls visible apps for a user group.

Examples:

```text
anonymous.json
web_user.json
sysadmin.json
```

## View Object

A DAVVAG record-level visibility mechanism used by the data layer.

## Saved AI Agent

An AI agent configured through `ai-agent-creator` and reused by apps through the shared agent runtime.

---

# 7. CORE RUNTIME ARCHITECTURE

Normal browser flow:

```text
Browser Request
      ↓
index.php
      ↓
configloader.php
      ↓
Tenant Resolution
      ↓
init.php
      ↓
tenant.json
      ↓
Startup App
      ↓
Webdock
      ↓
App Descriptor
      ↓
Components + Services
```

Admin flow:

```text
/admin
   ↓
admin.php
   ↓
IS_ADMIN_MODE = true
   ↓
Normal Framework Bootstrap
   ↓
tenant.json > webdock.events.onStartup.admin
```

Component API flow:

```text
/components/...
      ↓
components/index.php
      ↓
Session + Authentication
      ↓
Route Registration
      ↓
ComponentManager
      ↓
File / Object / Service / Transformer Dispatch
```

Application code must use this runtime instead of inventing alternate entry points unless the architecture explicitly requires one.

---

# 8. ACTIVE TENANT RESOLUTION

Before creating or modifying an app, resolve the active tenant.

The important relationship is:

```text
TENANT_RESOURCE_LOCATION = RESOURCE_LOCATION / HOST_NAME
```

`HOST_NAME` comes from:

```text
LOCAL_DEV_HOST
```

when configured, otherwise:

```text
$_SERVER["HTTP_HOST"]
```

Required agent procedure:

```text
READ root configloader.php
        ↓
READ root config.json
        ↓
RESOLVE RESOURCE_LOCATION
        ↓
RESOLVE LOCAL_DEV_HOST or HTTP_HOST
        ↓
CONFIRM TENANT_RESOURCE_LOCATION
        ↓
ONLY THEN EDIT TENANT APPS / SCHEMAS / WORKFLOWS
```

Never write files into `davvag-core/localhost` only because it exists.

The active tenant is determined by runtime configuration, not by convenience.

---

# 9. TENANT ARCHITECTURE

Minimum tenant structure:

```text
davvag-core/{tenant}/
├── apps/
├── davvag-flow/
├── global/
│   ├── config/
│   └── templetes/
│       ├── app/
│       └── email/
├── plugins/
├── schemas/
├── tenant.json
├── config.json
├── anonymous.json
├── web_user.json
└── sysadmin.json
```

The source spelling `templetes` is intentional and must be preserved.

Tenant responsibilities:

```text
TENANT
  │
  ├── APPLICATION REGISTRY
  ├── GROUP VISIBILITY
  ├── CONFIGURATION
  ├── SCHEMAS
  ├── WORKFLOWS
  ├── LOCAL PLUGINS
  └── TEMPLATES
```

Do not put tenant-specific business logic into framework root files.

Do not expose tenant folders directly over HTTP.

---

# 10. APPLICATION CAPABILITY BOUNDARIES

Apps should represent meaningful business capabilities.

Good app boundaries:

```text
course-manager
inventory
davvag-shop
ai-agent-creator
davvag-flow-designer
task-tracker
```

Avoid one giant app containing unrelated capabilities.

Avoid one app per screen.

Before creating a new app:

```text
SEARCH EXISTING APPS
      ↓
SEARCH EXISTING COMPONENTS
      ↓
SEARCH EXISTING SERVICES
      ↓
SEARCH EXISTING SCHEMAS
      ↓
SEARCH EXISTING WORKFLOWS
      ↓
SEARCH EXISTING PLUGINS
      ↓
REUSE WHEN POSSIBLE
```

The current DAVVAG installation already contains reusable capabilities for:

* AI agents
* agent flows
* workflow design
* profiles
* users
* CMS
* file upload
* image cropping
* products
* commerce
* orders
* payments
* courses
* tasks
* scheduling
* messaging

Do not create a second implementation of an existing platform capability without a documented reason.

---

# 11. REQUIRED APPLICATION STRUCTURE

Recommended app structure:

```text
apps/{app-code}/
├── app.json
├── app.php
├── components/
│   └── {component-name}/
│       ├── component.json
│       ├── script.js
│       ├── partial.html
│       └── component.css
├── services/
│   └── {service-name}/
│       ├── component.json
│       ├── script.js
│       └── service.php
└── assets/
```

Folder names are flexible when `app.json` declares the correct `location`.

New apps normally require:

```text
[ ] app.json
[ ] app.php
[ ] at least one UI component
[ ] service component when backend logic is needed
[ ] schemas for persisted namespaces
[ ] workflow files when orchestration is needed
[ ] tenant registration
[ ] group visibility registration
[ ] dependency declarations
[ ] tests of framework routes
```

---

# 12. APP DESCRIPTOR CONTRACT

Every app must have a valid `app.json`.

Minimum conceptual shape:

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
    "title": "My App",
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
  },
  "dependencies": {
    "apps": [],
    "schemas": [],
    "workflows": [],
    "plugins": [],
    "php-extensions": []
  }
}
```

The descriptor is part of the application contract.

Do not treat it as optional metadata.

## App Icon Descriptor Rule

`description.icon` is a logical app icon filename, not a nested asset path.

Use:

```json
"icon": "appicon.svg"
```

or:

```json
"icon": "appicon.png"
```

Do not write:

```json
"icon": "assets/appicon.svg"
```

The framework/dock expects the app icon value as the app-level icon filename. If the physical icon asset is stored under the app assets folder, keep the descriptor value as the filename expected by the dock.

---

# 13. APPLICATION DEPENDENCY ARCHITECTURE

Every app descriptor must declare:

```json
"dependencies": {
  "apps": [],
  "schemas": [],
  "workflows": [],
  "plugins": [],
  "php-extensions": []
}
```

Rules:

```text
NO blank placeholder values
NO undeclared runtime dependencies
NO accidental dependence on another app already being loaded
```

Declare an app dependency when the app:

* calls another app service
* opens another app component
* opens another app popup
* uses another app through a bundled workflow

Declare a schema dependency when the app reads or writes a namespace.

Declare a workflow dependency when the app executes or triggers that workflow.

Declare a plugin dependency when service code imports or requires the plugin.

Declare a PHP extension dependency when app-specific behavior directly requires it.

Examples:

```text
curl
imap
mysqli
zip
```

Dependencies are install/runtime contracts, not documentation suggestions.

If service PHP imports a plugin with `require_once(PLUGIN_PATH . ...)` or `require_once(PLUGIN_PATH_LOCAL . ...)`, the app must declare that plugin in `dependencies.plugins`.

Example:

```php
require_once(PLUGIN_PATH_LOCAL . "/profile/profile.php");
```

requires:

```json
"plugins": ["profile"]
```

If the same service also imports auth, cache or data plugins, declare those too:

```json
"plugins": ["auth", "sossdata", "phpcache", "profile"]
```

After dependency or descriptor changes, bump the affected app/component versions so stale descriptors are not reused by the browser or Webdock runtime.

---

# 14. COMPONENT ARCHITECTURE

A frontend component normally contains:

```text
component.json
script.js
partial.html
optional CSS
```

Descriptor example:

```json
{
  "name": "main-view",
  "resources": {
    "files": [
      {"type": "mainScript", "location": "script.js"},
      {"type": "mainView", "location": "partial.html"}
    ],
    "css": [
      {"type": "css", "location": "main-view.css"}
    ]
  }
}
```

Frontend component responsibility:

```text
RENDER UI
MANAGE VIEW STATE
COLLECT USER INPUT
CALL SERVICES
NAVIGATE
OPEN REUSABLE COMPONENTS
DISPLAY RESULTS AND ERRORS
```

Frontend components must not:

```text
implement authoritative business rules
connect directly to MySQL
store provider secrets
reimplement authentication
reimplement the AI runtime
make tenant isolation decisions
```

---

# 15. WEBDOCK COMPONENT LIFECYCLE

DAVVAG frontend components use:

```javascript
WEBDOCK.component().register(function (exports) {
});
```

Typical component pattern:

```javascript
WEBDOCK.component().register(function (exports) {
    var api;
    var scope;

    var state = {
        form: {},
        items: [],
        errors: []
    };

    exports.vue = {
        data: state,
        methods: {
            save: save
        },
        onReady: function (s) {
            scope = s;
            init();
        }
    };

    function init() {
        api = exports.getComponent("api");
    }

    function save() {
        api.services.Save(state.form).then(function (response) {
            if (response.success) {
                state.form = {};
            }
        });
    }
});
```

For Vue-backed components, initialize through `exports.vue.onReady`, not `exports.onReady`.

The active dock creates Vue first and then calls `instance.vue.onReady(...)`. Service lookups, route-context reads, DOM-dependent setup and initial data loading should therefore start from the Vue `onReady` hook:

```javascript
onReady: function (s) {
    scope = s;
    init();
}
```

Use `exports.onReady` only for non-Vue components or components that intentionally manage their own mount lifecycle.

If a Vue component receives `undefined` for a service dependency or `api.services` is missing, check these items before rewriting the component:

```text
service component is listed in app.json components
service component type is "service"
service component is included in configuration.webdock.onLoad when needed at startup
service component.json contains serviceHandler.methods
frontend initialization runs inside exports.vue.onReady
app/component versions were bumped after descriptor changes
browser cache was refreshed after descriptor changes
```

Do not work around missing `services` by hard-coding service URLs in the component. Fix the descriptor loading contract.

Use the framework's component APIs:

```text
exports.getComponent(...)
exports.getAppComponent(...)
exports.getShellComponent(...)
exports.Complete(...)
```

Do not bypass Webdock resource loading with ad hoc global scripts unless required by an existing framework pattern.

---

# 16. SERVICE COMPONENT ARCHITECTURE

Backend business logic belongs in service components.

Service descriptor:

```json
{
  "name": "api",
  "serviceHandler": {
    "file": "service.php",
    "class": "my_app\\ApiService",
    "methods": {
      "Save": {"method": "POST"},
      "List": {"method": "GET"}
    }
  }
}
```

PHP implementation:

```php
<?php
namespace my_app;

require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");

class ApiService {
    public function postSave($req, $res) {
        $data = $req->Body(true);
        return \SOSSData::Insert("my_app_items", $data);
    }

    public function getList($req, $res) {
        $query = isset($_GET["query"]) ? $_GET["query"] : "";
        return \SOSSData::Query("my_app_items", $query);
    }
}
?>
```

Active profile lookup pattern:

```php
require_once(PLUGIN_PATH_LOCAL . "/profile/profile.php");

$storeProfile = \Profile::getUserProfile();
$profile = isset($storeProfile->profile) ? $storeProfile->profile : $storeProfile;

if ($profile === null || !isset($profile->id) || intval($profile->id) < 1) {
    $res->SetError("An active profile is required.");
    return null;
}

$profileId = intval($profile->id);
```

Use this pattern when an app needs the active user's app-facing profile id.

Do not replace it with a direct `linkeduserid` datastore lookup inside each app. The profile plugin is the shared identity facade and may return either the profile directly or a wrapper containing `profile`.

Service responsibility:

```text
VALIDATE INPUT
AUTHORIZE ACTION
COORDINATE USE CASE
CALL DATA FACADE
CALL WORKFLOWS
CALL SHARED APP SERVICES
CALL APPROVED EXTERNAL APIs
RETURN STABLE RESULT
```

---

# 17. SERVICE HANDLER NAMING RULE

DAVVAG maps the HTTP method and handler name to a PHP method.

Conceptually:

```text
strtolower(REQUEST_METHOD) + ucwords(handlerName)
```

Examples:

```text
GET  List       → getList($req, $res)
POST Save       → postSave($req, $res)
POST DeleteItem → postDeleteItem($req, $res)
```

When no method-specific handler exists, the framework may try:

```php
__handle($req, $res)
```

Do not invent alternate naming conventions inside one app.

The descriptor class name and PHP namespace/class must match exactly.

---

# 18. REQUEST AND RESPONSE CONTRACT

Service methods receive:

```text
$req
$res
```

Request helpers:

```text
$req->Params()
$req->Query()
$req->Headers()
$req->Body(true)
$req->Body(false)
```

Response helpers:

```text
$res->Set(...)
$res->SetJSON(...)
$res->SetError(...)
$res->SetContentType(...)
```

A normal direct return is wrapped by the framework:

```json
{
  "success": true,
  "result": {}
}
```

App services may also return an internal result shape such as:

```json
{
  "success": false,
  "message": "Validation failed."
}
```

Keep response contracts stable for existing callers.

Before changing a service result shape, inspect all frontend and cross-app consumers.

Frontend service invocation must not retry real service failures.

Retry only transport-level failures such as:

```text
jqXHR.status === 0
timeout
```

Do not retry application/service responses such as:

```text
HTTP 400
HTTP 401 / 403
HTTP 500
{"success": false, ...}
```

This is especially important for POST service calls. Retrying a real service error can duplicate writes or duplicate validation failures in the browser network log.

---

# 18A. SERVICE REQUEST BUTTON LOCKING

Every button-triggered service request must have an in-flight lock.

This applies to service-backed actions including:

```text
CREATE / SAVE
EDIT / UPDATE
DELETE / CANCEL
STATUS CHANGES
SEARCH / LOAD MORE
UPLOAD / PROCESS / SEND
```

Required behavior:

```text
USER FIRES BUTTON ACTION
      ↓
LOCK THE INITIATING BUTTON
      ↓
START ONE SERVICE REQUEST
      ↓
KEEP THE BUTTON LOCKED WHILE THE REQUEST IS IN FLIGHT
      ↓
UNLOCK AFTER SUCCESS OR FAILURE COMPLETION
```

Rules:

```text
IGNORE REPEATED CLICKS WHILE LOCKED
RELEASE THE LOCK ON BOTH SUCCESS AND ERROR PATHS
DO NOT RELEASE THE LOCK BEFORE THE SERVICE PROMISE / REQUEST SETTLES
DO NOT USE A FIXED TIMER AS THE REQUEST COMPLETION SIGNAL
PRESERVE ANY BUSINESS-RULE DISABLED STATE AFTER THE TEMPORARY LOCK ENDS
KEEP A LOCK COUNT WHEN ONE BUTTON STARTS MULTIPLE RELATED REQUESTS
```

Prefer an explicit reactive `submitting`, `saving`, `deleting` or `loading` state when a component owns the action. A centralized app-level request-lock helper is acceptable for legacy apps with many components, provided it only associates real DAVVAG service requests with their initiating buttons and releases them on the request's actual completion event.

Client-side locking prevents accidental duplicate submissions but is not a server-side idempotency guarantee. Financial, destructive or otherwise sensitive services should also enforce duplicate protection or idempotency in the backend where required.

Do not implement only a success-path unlock. Network errors, authorization failures, validation failures and service errors must all release the temporary UI lock.

---

# 19. CONTROLLERS AND BUSINESS LOGIC RULE

DAVVAG does not require every app to imitate an external MVC framework.

The authoritative DAVVAG separation is:

```text
UI COMPONENT
      ↓
SERVICE COMPONENT
      ↓
FRAMEWORK / PLUGIN FACADE
      ↓
DATASTORE / WORKFLOW / EXTERNAL SYSTEM
```

Rules:

```text
UI components manage presentation.
Service components coordinate use cases.
SOSSData handles persistence access.
Schemas define data contracts.
Workflows coordinate multi-step automation.
Plugins provide reusable PHP capabilities.
```

Do not introduce unnecessary controller/repository/domain layers into every app only because another framework uses them.

Use additional internal classes when business complexity justifies them, but keep them behind the DAVVAG service component contract.

---

# 20. TRANSFORMERS

Transformers may be used for simple forwarding or simple datastore operations.

Examples:

```text
Direct SOSSData transform
External REST transform
```

Use a PHP service handler when the operation needs:

* validation
* authorization decisions
* business rules
* multiple writes
* multiple services
* workflow execution
* complex error handling

Do not hide complex business logic inside a transformer declaration.

---

# 21. FRONTEND ROUTING

Use the active shell route component:

```javascript
var handler = exports.getShellComponent("soss-routes");
handler.appNavigate("../tasks?projectId=" + projectId);
```

Important behavior:

```text
soss-routes.appNavigate() appends plain paths to the current hash route.
```

From:

```text
#/app/task-tracker/projects
```

this may be wrong:

```javascript
handler.appNavigate("/tasks?projectId=" + projectId);
```

because it can append to the current app route incorrectly.

Prefer sibling navigation when appropriate:

```javascript
handler.appNavigate("../tasks?projectId=" + projectId);
```

The dock route controller must preserve the active app root while resolving component navigation. For example, both of these starting routes:

```text
#/app/profileapp.v1
#/app/profileapp.v1/list
```

must resolve:

```javascript
handler.appNavigate("../edit?id=1");
```

to:

```text
#/app/profileapp.v1/edit?id=1
```

The resolver must never climb above `#/app/{app-code}` for app-relative navigation. A leading component path such as `/edit?id=1` is also app-root-relative while an app is active. Use a full hash such as `#/app/{other-app-code}` only when intentionally switching applications.

Always test real hash navigation inside the target dock.

For app descriptor dock subapps, use full app hash paths instead of relative paths.

Correct:

```json
{
  "name": "Networks",
  "path": "#/app/davvag-mesh-networks"
}
```

Incorrect:

```json
{
  "name": "Sync",
  "path": "../davvag-mesh-sync"
}
```

Descriptor-level relative app paths can produce broken hashes such as:

```text
#/app/davvag-mesh/../davvag-mesh-sync
```

Use relative navigation only where the active route component intentionally resolves sibling routes at runtime. Use full `#/app/{app-code}` paths when switching apps from dock/subapp metadata.

---

# 22. MULTI-DOCK COMPATIBILITY

DAVVAG apps may run inside different shells:

```text
/#/app/{app-code}/...
/admin#/app/{app-code}/...
```

Different docks may not provide identical CSS or component behavior.

Rules:

```text
TEST IN EVERY SUPPORTED DOCK
SCOPE APP STYLES
DO NOT ASSUME ONE BOOTSTRAP VERSION
LOAD SHARED APP STYLES EXPLICITLY
```

When an app depends on compatibility styles, create a reusable style component and load it through `onLoad`.

Do not depend on styles leaking from an unrelated app.

---

# 23. CROSS-APP COMPONENT REUSE

Applications should reuse existing cross-app components.

Common pattern:

```text
CALLER APP
    ↓
app_popup
    ↓
SOURCE APP COMPONENT
    ↓
exports.Complete(selectedObject)
    ↓
CALLBACK TO CALLER
```

Example use cases:

* profile selection
* product selection
* reusable lookup screens
* common upload tools

Rules:

```text
Declare source app in dependencies.apps.
Confirm current user group can access the source app.
Return a small predictable object.
Normalize old and new return shapes in the caller when needed.
Close popup instances after selection.
```

Do not copy a complete selector into every consuming app.

---

# 24. REUSABLE PLATFORM COMPONENTS

Prefer existing shared capabilities.

Examples:

## File and Image Tools

Use:

```text
davvag-tools
```

for:

```text
davvag-img-cropper
davvag-file-uploader
davvag-app-downloader
viewObjectAPI
```

## Profiles

Use existing profile data and profile lookup components.

## Products

Use reusable product lookup components when available.

## AI

Use `ai-agent-creator`.

## Workflow

Use `DavvagFlow` and existing workflow tooling.

## Routing

Use the shell route component.

The default question is:

> Which existing DAVVAG capability should this feature reuse?

not:

> What new subsystem should this app create?

---

# 25. DATA ACCESS ARCHITECTURE

Application code must use `SOSSData` as the persistence facade.

Public API:

```php
SOSSData::Insert(...);
SOSSData::Update(...);
SOSSData::Delete(...);
SOSSData::Query(...);
SOSSData::ExecuteRaw(...);
SOSSData::Close(...);
```

The architecture is:

```text
APP SERVICE
    ↓
SOSSData
    ↓
ACTIVE TENANT CONNECTOR CONFIG
    ↓
DATASTORE ADAPTER
    ↓
SCHEMA-AWARE STORAGE
```

Apps must not hard-code one database connector when SOSSData already provides tenant-aware adapter selection.

Do not open direct `mysqli` connections from ordinary app services unless a documented exceptional integration requires it.

---

# 26. DATASTORE ADAPTER RESOLUTION

SOSSData resolves the adapter by tenant.

Conceptually:

```text
SUPPLIED TENANT ID
      OR
DATASTORE_DOMAIN
      ↓
ENGINE CONFIG CONNECTOR
      ↓
plugins/sossdata/{connector}/{connector}.php
      ↓
connector class implementing iDataStore
```

If no configured connector exists, DAVVAG falls back to the default datastore implementation.

This abstraction allows a tenant to change storage implementation without rewriting app services.

Application code must remain namespace-oriented.

---

# 27. SCHEMA-DRIVEN DATA MODEL

Schemas live at:

```text
{TENANT_RESOURCE_LOCATION}/schemas/{namespace}.json
```

Basic schema shape:

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

The schema is the source of truth for the logical namespace.

Rules:

```text
CREATE THE SCHEMA BEFORE WRITING THE DATA PATH
KEEP FIELD NAMES STABLE
USE LOGICAL NAMESPACES IN APP CODE
DO NOT DEFINE FRAMEWORK SYSTEM COLUMNS MANUALLY
```

---

# 28. SCHEMA RELATIONSHIPS

Stable direct relationships belong in the schema `relations` block.

Example:

```json
{
  "relations": [
    {
      "relationName": "subject_course",
      "relationType": "many-to-one",
      "targetEntity": "course_manager_course",
      "joinColumns": [
        {
          "sourceColumn": "course_id",
          "targetColumn": "id"
        }
      ]
    }
  ]
}
```

Use schema relations when:

```text
one source field always points to one known target namespace
```

Do not create false fixed relationships for polymorphic pairs such as:

```text
entity_type + entity_id
```

unless the target is genuinely fixed.

Keep relationship metadata with the schema, not scattered across service comments.

---

# 29. FRAMEWORK SYSTEM COLUMNS

The MySQL adapter adds framework system columns such as:

```text
sysversionid
syscreated
sysupdated
sysviewobject
syscreatedby
syslastupdatedby
```

Do not define these manually unless intentionally overriding framework behavior.

Application logic should understand their roles:

```text
sysversionid       change/version ordering
syscreated         creation timestamp
sysupdated         update timestamp
sysviewobject      record visibility bucket
syscreatedby       creator identity
syslastupdatedby   last updater identity
```

Do not build a duplicate audit metadata system inside each app without a clear requirement.

---

# 30. VIEW OBJECT FILTERING

Normal `SOSSData::Query()` calls may apply view-object filtering.

The architecture is:

```text
AUTHENTICATED USER
      ↓
Auth::ViewObjects()
      ↓
ALLOWED VIEW OBJECTS
      ↓
SOSSData Query Filter
```

Use normal filtering for ordinary app requests.

Disable it only for trusted admin, maintenance or explicitly authorized paths.

Do not disable view-object filtering merely to make a query return more data during development.

---

# 31. QUERY ARCHITECTURE

Normal query syntax:

```text
field:value,anotherField:anotherValue
```

Example:

```php
SOSSData::Query("my_app_items", "status:Active,title:Test");
```

Rules:

```text
VALIDATE INPUT
ENCODE QUERY VALUES WHEN NEEDED
USE SCHEMA FIELD NAMES
LIMIT RESULT SIZE
PAGINATE LARGE DATASETS
```

Do not pass arbitrary browser-provided query fragments into lower-level SQL behavior.

---

# 32. RAW QUERY ARCHITECTURE

Use `SOSSData::ExecuteRaw()` only when normal queries cannot express the required read model.

Valid uses:

* joins
* aggregates
* reporting
* grouped summaries
* subqueries
* controlled custom ordering
* stored procedure calls

The raw query definition belongs in a schema file.

Result fields must match explicit SQL aliases.

Rules:

```text
NO SELECT * FOR NEW JOINED REPORTS
VALIDATE ALL PARAMETERS
CAST NUMERIC INPUTS
VALIDATE DATES
WHITELIST STATUS / TYPE / SORT OPTIONS
DO NOT ACCEPT RAW SQL FROM THE BROWSER
```

Raw queries do not automatically inherit every normal data-layer restriction.

Treat them as security-sensitive code.

---

# 33. WORKFLOW ARCHITECTURE

DAVVAG workflows live under:

```text
{TENANT_RESOURCE_LOCATION}/davvag-flow/
```

Root workflow:

```text
davvag-flow/{flowid}.json
```

Namespaced workflow:

```text
davvag-flow/{namespace}/{flowid}.json
```

Execution:

```php
DavvagFlow::Execute($namespace, $flowid, $inputData);
```

Use workflows for:

* multi-step automation
* reusable orchestration
* service-to-service processes
* integration sequences
* scheduled or triggered business flows

Do not use workflows to replace simple local service logic.

---

# 34. WORKFLOW NODE TYPES

Current workflow node types include:

```text
service
class
create_object
```

## Service Node

Calls a DAVVAG app service.

Prefer this for business operations.

## Class Node

Calls a reusable PHP activity class.

Use this for generic workflow activities.

## Create Object Node

Builds an output object from constants and workflow state.

Use it for shaping results.

Workflow flow:

```text
START NODE
    ↓
EXECUTE
    ↓
STORE RETURN IN outData
    ↓
FOLLOW success
       OR
FOLLOW fail
```

Important source spellings must be preserved:

```text
scopData
excutionStack
```

---

# 34A. SAVED AI AGENT WORKFLOW ACTIVITY

The shared workflow activity:

```text
plugins/davvag-flow/lib/saved_agent.php
```

provides this callable contract:

```php
SavedAgentWorkflow::run($input);
```

The activity:

```text
CONFIRMS TENANT_RESOURCE_LOCATION
    ↓
LOADS {TENANT_RESOURCE_LOCATION}/apps/ai-agent-creator/services/creator-api/service.php
    ↓
CREATES ai_agent_creator\CreatorService
    ↓
CALLS CreatorService::runAgent($input)
    ↓
RETURNS THE COMPLETE SUCCESSFUL SAVED-AGENT RESULT
```

It throws an exception when the active tenant is unresolved, `ai-agent-creator` is not installed, the saved-agent runtime cannot be loaded, or the agent result is unsuccessful. A workflow can therefore route the exception through its normal `fail` node handling.

Minimum input:

```json
{
  "agentCode": "saved-agent-code",
  "message": "Prompt for the saved agent"
}
```

Supported `runAgent` context may also include:

```text
profile
sessionId
flow
connector
payload
```

Global flow class-node example:

```json
{
  "urntype": "class",
  "file": "saved_agent.php",
  "class": "SavedAgentWorkflow",
  "method": {
    "name": "run",
    "params": [
      {
        "inputData": ""
      }
    ],
    "return": true,
    "returnobj": "agentResult"
  }
}
```

The empty `inputData` value is intentional in the global engine: it forwards the complete workflow input object to `SavedAgentWorkflow::run()`.

## Flow Runtime Dialect Boundary

The repository contains two `davvag-flow/flow.php` implementations:

```text
plugins/davvag-flow/flow.php                            global engine
{TENANT_RESOURCE_LOCATION}/plugins/davvag-flow/flow.php tenant-local engine
```

They do not currently expose identical node and parameter behavior.

The global engine used by Lesson Manager supports `class` nodes and its legacy class parameter form uses `inputData` or `scopData` properties. The active localhost tenant-local engine also supports `service` and `create_object` nodes and resolves modern named parameters through `type` and `value`.

Do not assume a workflow validated against one engine is compatible with the other. Before adding or changing a workflow:

```text
READ THE CALLING SERVICE'S flow.php IMPORT
CONFIRM WHETHER PLUGIN_PATH OR PLUGIN_PATH_LOCAL IS USED
AUTHOR NODE PARAMETERS FOR THAT ENGINE
EXECUTE THE REAL WORKFLOW PATH
```

When a class workflow must remain compatible with both current parameter dialects, a whole-input parameter can carry both representations:

```json
{
  "name": "input",
  "inputData": "",
  "type": "object",
  "value": "inputData"
}
```

The saved-agent activity is a low-level workflow bridge to `runAgent`. Apps that need the normalized app-interaction contract and stable `response` field should continue using `CreatorService::interactWithAgent()` or the `InteractWithAgent` service endpoint.

Apps using this activity must declare:

```text
dependencies.apps: ai-agent-creator
dependencies.workflows: the namespaced workflow id
dependencies.plugins: davvag-flow
dependencies.php-extensions: curl when the saved-agent provider requires it
```

Never place provider credentials or saved-agent secrets in workflow JSON or in the activity input.

---

# 35. WORKFLOW DESIGN RULES

Every important workflow node should define failure behavior.

Rules:

```text
KEEP NODE IDS STABLE
KEEP FLOW IDS STABLE
USE SERVICE NODES FOR APP BUSINESS LOGIC
USE CLASS NODES FOR REUSABLE ACTIVITIES
USE CREATE_OBJECT FOR RESULT SHAPING
DO NOT STORE SECRETS IN WORKFLOW JSON
PRESERVE UNKNOWN FIELDS WHEN EDITING EXISTING FLOWS
```

A workflow designer or AI agent must not delete fields it does not understand.

---

# 36. PLUGIN ARCHITECTURE

DAVVAG supports:

```text
GLOBAL PLUGINS
TENANT-LOCAL PLUGINS
```

Locations:

```text
plugins/
{TENANT_RESOURCE_LOCATION}/plugins/
```

Use global plugins for framework-wide capabilities.

Use tenant plugins for reusable tenant/domain behavior.

Common global capabilities include:

```text
auth
sossdata
phpcache
notify
davvag-flow
transactions
hosting
PDF tools
spreadsheet tools
```

Do not create a plugin for logic used by only one service unless reuse or isolation justifies it.

---

# 37. PLUGIN LOADING RULES

Plugins may be loaded:

* at framework startup
* as component dependencies
* explicitly by service code when required by an established pattern

Component dependency example:

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

Rules:

```text
NO OUTPUT DURING PLUGIN LOAD
NO SECRETS IN PLUGIN SOURCE
USE EXPLICIT CLASS NAMES
KEEP STARTUP SIDE EFFECTS MINIMAL
DECLARE THE DEPENDENCY
PROTECT TENANT PLUGIN FOLDERS FROM DIRECT ACCESS
```

---

# 38. AUTHENTICATION ARCHITECTURE

The normal request authentication flow is:

```text
SESSION START
      ↓
Auth::Autendicate()
      ↓
SESSION authData
      OR
securityToken COOKIE
      ↓
RESTORE USER SESSION
      ↓
DEFINE GROUPID
```

Important source spelling:

```text
Autendicate
```

Do not silently correct source API spellings in generated code.

Application code must use the existing auth system rather than creating a parallel login/session model.

---

# 39. APP VISIBILITY ARCHITECTURE

App visibility is controlled by tenant group files.

Examples:

```text
anonymous.json
web_user.json
sysadmin.json
```

Rules:

```text
PUBLIC APPS ONLY IN anonymous.json
USER APPS IN APPROPRIATE GROUP FILES
ADMIN APPS MUST NOT LEAK INTO PUBLIC GROUPS
DO NOT REMOVE EXISTING APP ENTRIES WHEN REGISTERING A NEW APP
```

An app being present in `tenant.json` does not mean every group should see it.

Visibility is an explicit permission decision.

---

# 40. SERVICE ACCESS ARCHITECTURE

Service access is checked through the framework access system.

Conceptually:

```text
REQUEST
   ↓
checkAccess(...)
   ↓
Auth::GetAccess(...)
   ↓
ALLOW OR DENY
```

Do not bypass service access checks casually.

The current framework also contains known security caveats, including permissive behavior in some virtual firewall paths.

Therefore:

```text
DO NOT TREAT CURRENT PERMISSIVE CODE AS SECURITY DESIGN
VALIDATE AUTHORIZATION INSIDE SENSITIVE SERVICES
PLAN FRAMEWORK HARDENING SEPARATELY
```

---

# 41. PROFILE AND USER IDENTITY ARCHITECTURE

Use existing system identity objects.

General rule:

```text
APP-FACING PERSON OR AGENT IDENTITY
        ↓
profile
```

Use `profile` for:

* customers
* students
* staff
* contacts
* AI agents
* app-facing identities

Use auth users when login or system-level action is required.

Do not invent a new person table for every app.

Do not overwrite a normal user profile with AI agent identity data.

---

# 42. AI AGENT ARCHITECTURE

DAVVAG already has a shared AI agent runtime.

Use:

```text
ai-agent-creator
```

Apps must call the shared interaction service:

```text
POST /components/ai-agent-creator/creator-api/service/InteractWithAgent
```

Do not duplicate:

* provider API calls
* provider configuration
* model selection logic
* session storage
* skill execution
* agent configuration storage

inside every application.

The architecture is:

```text
DAVVAG APP
    ↓
InteractWithAgent
    ↓
SAVED AGENT CONFIG
    ↓
SHARED AGENT RUNTIME
    ↓
PROVIDER + SESSION + SKILLS
    ↓
STABLE RESPONSE
```

When an app depends on the agent runtime, declare:

```text
ai-agent-creator
```

in `dependencies.apps`.

---

# 43. AI INTERACTION CONTEXT

An app may send:

```text
agentCode
message
appCode
appName
profile
conversationKey
context
payload
```

Use context to provide structured app state.

Use payload for domain data needed by the agent.

Use a stable conversation key when continuity is required.

Do not expose provider secrets to the frontend.

Do not make AI the authoritative validator for deterministic business rules.

Correct order:

```text
VALIDATE
AUTHORIZE
LOAD TRUSTED DATA
EXECUTE DETERMINISTIC RULES
THEN CALL AI WHEN NEEDED
```

AI should assist application behavior, not replace required security and validation.

---

# 44. AI AGENT IDENTITY

An AI agent that participates in the DAVVAG identity system should use existing profile patterns.

Typical pattern:

```text
AI AGENT
   ↓
profile with catogory = "AI Agent"
   ↓
optional auth user
   ↓
optional sysuser membership when system access is required
```

Preserve the source spelling:

```text
catogory
```

Do not create an auth user for every simple AI configuration.

Create one only when the agent must act as a system user.

Mask sensitive fields before sending agent configuration to the frontend.

Sensitive fields include:

```text
password
apiKey
token
secret
authorization
authHeader
```

---

# 45. FILE AND MEDIA ARCHITECTURE

Use shared upload capabilities.

Preferred app-level flow:

```text
USER SELECTS FILES
      ↓
LOCAL PREVIEW
      ↓
SAVE ENTITY / METADATA
      ↓
UPLOAD WITH davvag-file-uploader
      ↓
RELOAD ENTITY DETAILS
```

Use:

```text
davvag-tools / davvag-file-uploader
```

Do not call lower-level upload infrastructure directly from every app screen when the shared wrapper already provides the expected modal/progress behavior.

Keep storage naming consistent with existing uploader conventions.

Do not expose filesystem paths or secrets to the browser.

---

# 46. REUSABLE LOOKUP ARCHITECTURE

Reusable lookup components should:

```text
LOAD THEIR REQUIRED STYLES
ACCEPT INITIAL FILTERS
RETURN A SMALL STABLE OBJECT
CALL exports.Complete(selectedObject)
```

Callers should:

```text
OPEN THROUGH app_popup
NORMALIZE RETURN SHAPE
USE SELECTED IDENTITY
CLOSE POPUP INSTANCE
DECLARE SOURCE APP DEPENDENCY
```

Do not force every app to understand the entire source application's internal object shape.

Cross-app contracts should be intentionally small.

---

# 47. CONFIGURATION AND SECRET MANAGEMENT

Configuration belongs in protected server-side configuration.

Possible locations include:

```text
root config.json
tenant config.json
protected global config
external DAVVAGCONFIG location
server-owned agent configuration
```

Rules:

```text
NO PROVIDER SECRETS IN FRONTEND JS
NO SMTP PASSWORDS IN APP DESCRIPTORS
NO TOKENS IN WORKFLOW JSON
NO RAW PASSWORDS IN API RESPONSES
```

Use environment-specific config when deployment requires it.

A tenant app must not hard-code development credentials.

---

# 48. CACHE AND VERSION ARCHITECTURE

DAVVAG uses cached app/component resources and object caches.

When changing:

```text
app descriptors
component descriptors
JavaScript
CSS
```

bump relevant versions.

At minimum, update app `description.version` when needed to avoid stale Webdock resource URLs.

When writes affect cached shared objects, clear the relevant cache using the established cache pattern.

Do not diagnose stale UI behavior as a code failure until versioned resource caching has been considered.

---

# 49. ERROR HANDLING

Errors must be actionable and stable.

Frontend responsibilities:

```text
SHOW USER-SAFE MESSAGE
PRESERVE FORM STATE WHEN POSSIBLE
HANDLE NETWORK FAILURE
HANDLE SERVICE FAILURE
```

Backend responsibilities:

```text
VALIDATE INPUT
RETURN CLEAR FAILURE
DO NOT LEAK SECRETS
DO NOT LEAK STACK DETAILS TO PUBLIC USERS
LOG ENOUGH SERVER CONTEXT FOR DEBUGGING
```

Cross-app and workflow calls must propagate meaningful failures.

Do not convert every failure into a successful empty result.

---

# 50. VALIDATION ARCHITECTURE

Validation must occur at the boundary where the system accepts untrusted input.

Required checks may include:

```text
required fields
allowed values
numeric casting
ID format
email format
date format
page limits
file type
file size
permission
ownership
state transition
```

Frontend validation improves user experience.

Backend validation is authoritative.

Do not rely on the browser to enforce business or security rules.

---

# 51. BUSINESS RULE ARCHITECTURE

Deterministic business rules belong in backend services or explicitly reusable workflow/plugin logic.

Examples:

```text
Can this user edit the record?
Can this state transition occur?
Can this payment be processed?
Can this workflow continue?
Is this input valid?
```

AI may explain, summarize, classify or recommend.

AI must not be the sole authority for:

```text
authentication
authorization
money movement
permission changes
destructive actions
required validation
```

---

# 52. APP REGISTRATION ARCHITECTURE

A new app normally must be registered in:

```text
tenant.json
```

and in appropriate group files.

Registration checklist:

```text
[ ] tenant.json includes the app
[ ] sysadmin.json includes the app when admins require access
[ ] web_user.json includes the app only when normal users require access
[ ] anonymous.json includes the app only when intentional public access is required
[ ] startup app references remain valid
[ ] existing app entries are preserved
```

Never replace whole tenant/group files with a minimal generated file unless creating a brand-new tenant.

When modifying an existing tenant, merge changes.

---

# 53. MODIFYING EXISTING APPLICATIONS

Before modifying an existing app:

```text
READ app.json
      ↓
READ ALL RELEVANT component.json FILES
      ↓
READ CALLING FRONTEND COMPONENTS
      ↓
READ SERVICE IMPLEMENTATION
      ↓
READ USED SCHEMAS
      ↓
READ USED WORKFLOWS
      ↓
READ DECLARED DEPENDENCIES
      ↓
SEARCH CROSS-APP CALLERS
```

Rules:

```text
PRESERVE BACKWARD COMPATIBILITY WHEN PRACTICAL
DO NOT DELETE UNKNOWN DESCRIPTOR FIELDS
DO NOT REMOVE ROUTES ACCIDENTALLY
DO NOT REMOVE EXISTING TENANT REGISTRATIONS
DO NOT CHANGE SERVICE OUTPUT SHAPES BLINDLY
BUMP VERSIONS AFTER RESOURCE CHANGES
```

Small edits still require architectural awareness.

---

# 54. CREATING A NEW APPLICATION

Recommended build sequence:

```text
1. Resolve active tenant
2. Define app capability boundary
3. Search for reusable existing capabilities
4. Choose app code and namespace names
5. Define required dependencies
6. Create app.json
7. Create app.php
8. Create startup UI component
9. Create service component
10. Create schemas
11. Add workflows if needed
12. Register app in tenant.json
13. Register intended group visibility
14. Bump versions as development changes resources
15. Validate JSON and PHP syntax
16. Test descriptor routes
17. Test service routes
18. Test app in supported docks
19. Test permissions
20. Document final files and dependencies
```

Do not start with database tables.

Start with the capability, contracts and framework integration.

---

# 55. TESTING ARCHITECTURE

Every generated or modified app should test the framework integration points.

Descriptor tests:

```text
GET /components/object/appdescriptor/{appCode}
GET /components/{appCode}/{component}/object?object=desc
```

Resource tests:

```text
GET /components/{appCode}/{component}/file/script.js
GET /components/{appCode}/{component}/file/partial.html
```

Service tests:

```text
POST /components/{appCode}/{service}/service/{Handler}
GET  /components/{appCode}/{service}/service/{Handler}
```

Visibility test:

```text
GET /components/object/apps
```

App-level tests must cover:

```text
happy path
validation failure
unauthorized access
missing dependency
empty data
large data or pagination
cross-app integration
workflow failure
AI failure when AI is optional
supported docks
```

---

# 56. STATIC VALIDATION REQUIREMENTS

Before browser testing:

```text
VALIDATE JSON
VALIDATE PHP SYNTAX
CONFIRM FILE PATHS
CONFIRM NAMESPACE / CLASS MATCH
CONFIRM SERVICE METHOD NAMES
CONFIRM DECLARED DEPENDENCIES
CONFIRM SCHEMA NAMES
CONFIRM ROUTE COMPONENT NAMES
```

For changed app descriptors, confirm:

```text
startupComponent exists
onLoad components exist
route targets exist
dependency arrays contain no blank placeholders
```

Do not use browser trial-and-error as the first validator for malformed JSON.

---

# 57. OBSERVABILITY AND DEBUGGING

Debugging should follow the runtime layers.

Recommended order:

```text
1. Confirm active tenant
2. Confirm app registration
3. Confirm group visibility
4. Confirm app descriptor loads
5. Confirm component descriptor loads
6. Confirm required onLoad dependency is registered
7. Confirm service route exists
8. Confirm PHP class and method mapping
9. Confirm schema exists
10. Confirm datastore result
11. Confirm workflow or external API result
12. Confirm frontend rendering
```

Common failure categories:

```text
wrong tenant
stale version/cache
missing app dependency
missing group access
component not registered
wrong service class namespace
wrong handler method name
missing schema
invalid query input
workflow path mismatch
missing PHP extension
```

Debug architecture before rewriting working code.

---

# 57A. MESH / WEBDOCK IMPLEMENTATION FINDINGS

These findings were captured while making the `davvag-mesh` app family work correctly.

Apply them to future DAVVAG apps when the same symptoms appear.

## Vue component startup

For Vue-backed components, use:

```javascript
exports.vue = {
    data: state,
    methods: {},
    onReady: function (s) {
        scope = s;
        init();
    }
};
```

Do not initialize Vue-backed components with only:

```javascript
exports.onReady = init;
```

Symptom when wrong:

```text
exports.getComponent("service-component").services is undefined
```

## Service descriptors and cache

When a service is unavailable in the frontend:

```text
confirm app.json onLoad includes the service when needed
confirm the service component has type "service"
confirm component.json contains serviceHandler.methods
confirm the service PHP class namespace matches component.json
bump app/component versions after descriptor changes
hard refresh or clear cached descriptors during development
```

## Active profile id

Use the profile plugin:

```php
require_once(PLUGIN_PATH_LOCAL . "/profile/profile.php");

$storeProfile = \Profile::getUserProfile();
$profile = isset($storeProfile->profile) ? $storeProfile->profile : $storeProfile;
$profileId = ($profile && isset($profile->id)) ? intval($profile->id) : 0;
```

Do not query `profile` manually by `linkeduserid` in each service.

## Service invoke retry behavior

Retry service invokes only once and only for transport/network failures.

Do not retry HTTP/service errors. A `500` response containing JSON such as:

```json
{"success": false, "result": "An active profile is required."}
```

is a service failure, not a network failure.

## App switching routes

Dock/subapp app-switching paths must use full app hashes:

```text
#/app/davvag-mesh-sync
```

Do not use descriptor paths that produce:

```text
#/app/davvag-mesh/../davvag-mesh-sync
```

## App icon values

Use:

```json
"icon": "appicon.svg"
```

not:

```json
"icon": "assets/appicon.svg"
```

---

# 57B. ACTIVE LESSON MANAGER ROUTING DEFECT

The following issue is currently reported in the `lesson-manager` app and must be treated as unresolved until it is reproduced, corrected and browser-tested:

```text
OPEN #/app/lesson-manager/learn
      ↓
THE APPLICATION SHELL / ROUTING SYSTEM BREAKS
      ↓
SUBSEQUENT ROUTES DO NOT WORK
```

Known scope:

```text
affected app: lesson-manager
trigger route: #/app/lesson-manager/learn
visible impact: navigation stops working across routes after the Learn screen is opened
status: active defect; root cause not yet verified
```

Required investigation order:

```text
1. Reproduce from a clean page load in the supported dock
2. Capture the first browser console error and failed network request
3. Confirm the dock hash router still owns window.onhashchange
4. Confirm partial-app receives appName=lesson-manager and appRoute=/learn
5. Confirm the learn component mounts without replacing or corrupting the shell render boundary
6. Confirm StudentCourses and LearningCourse service failures remain isolated to the component
7. Navigate from Learn to another lesson-manager route
8. Navigate from Learn to a different app route
9. Refresh directly on #/app/lesson-manager/learn and repeat the navigation tests
```

Do not mark this defect fixed based only on valid JSON, JavaScript syntax or a successful direct resource request. The fix is complete only when entering the Learn route no longer prevents both same-app and cross-app navigation in the actual browser dock.

Static remediation applied on 2026-07-27:

```text
all Lesson Manager component navigation now calls appNavigate with app-root paths such as /dashboard and /learn
component fallbacks use full #/app/lesson-manager/<route> hashes
no Lesson Manager component assigns window.onhashchange or mutates shell route settings
app and affected component versions were bumped
```

Additional route evidence and remediation on 2026-07-27:

```text
Apache access logs confirmed that /learn resolved the learn descriptor, script and view with HTTP 200
StudentCourses, LearningCourse and StartLesson all returned HTTP 200 during the affected navigation
the Dock router resolves appNavigate('/learn') to #/app/lesson-manager/learn as designed
the remaining failure boundary is client-side rendering or stale versioned resources, not route matching
Lesson Manager was bumped to 1.4 and the Learn component to 1.3 to invalidate stale v=1.3 resources
Learn startup now renders an isolated service-availability error instead of throwing into the shell
```

Verified client-rendering root cause and correction on 2026-07-27:

```text
the affected Dock uses Vue 2.0.3 and compiles downloaded component HTML in the browser
three Learn interpolation expressions contained literal &&current sequences in HTML text
the HTML parser decoded the second ampersand through the legacy &curren; entity, producing invalid Vue expressions
Vue 2.0.3 compiled the complete Learn template to an empty no-op render function without a production console error
the Learn, Submissions and Reports views also contained UTF-8 BOM prefixes and were normalized to UTF-8 without BOM
the three Learn text expressions now use &amp;&amp; so the DOM preserves the intended JavaScript && operator
Lesson Manager was bumped to 1.5 and the Learn component to 1.4
a disposable Chrome test using the Dock's exact Vue 2.0.3 build and partial-app mount pattern compiled the served Learn view with zero failing elements and rendered one .lm-page root
```

Additional Learn blank-screen remediation on 2026-07-29:

```text
the CMS route, Learn partial, Learn script, stylesheet and StudentCourses request all returned HTTP 200
the browser fetched the new Learn partial but reused script.js?v=1.7 from its disk cache
the resulting new-template / old-script mismatch removed the Vue render after course data arrived
the mobile completion checklist also reintroduced literal &&current expressions in HTML interpolation text
those interpolation operators are now encoded as &amp;&amp; so browser entity parsing preserves JavaScript &&
Lesson Manager is now 1.8, Learn is 1.6 and lesson-style is 1.2
CMS partial-app is now 0.8 and dock-shell is 0.3
both CMS app renderers prefer the freshly downloaded descriptor version over stale app-list metadata when loading component resources
the served v=1.8 Learn script contains the new subject/lesson methods and the served partial contains no literal &&current interpolation
```

This correction still requires an authenticated hard-refresh and the complete browser navigation matrix before the defect can be marked browser-certified.

This reduces the known route-risk but does not close the defect. The required browser dock navigation matrix was not executable in the implementation environment because the browser-control process could not start. Keep the defect open until the checks above pass in an authenticated supported dock.

When correcting this issue:

```text
PRESERVE THE GLOBAL DOCK ROUTER
ISOLATE LEARN COMPONENT STARTUP / SERVICE ERRORS
DO NOT REPLACE window.onhashchange FROM AN APP COMPONENT
DO NOT MUTATE THE SHELL ROUTE SETTINGS FROM lesson-manager
BUMP THE AFFECTED APP / COMPONENT VERSION
TEST NAVIGATION BEFORE AND AFTER ENTERING THE LEARN ROUTE
```

---

# 57D. LESSON MANAGER 1.3 IMPLEMENTATION BASELINE

The Lesson Manager application at:

```text
davvag-core/localhost/apps/lesson-manager
```

was completed against its `instruction.md` contract on 2026-07-27. The code-level feature scope is approximately 94% complete and production readiness is approximately 78%. The difference is external/runtime verification, not an intentionally omitted core workflow.

Implemented application contracts:

```text
teacher/admin authorization and subject/course ownership filtering
student enrollment checks and subject-scoped progression locks
subject-required lesson creation with server-derived course_id
safe lesson reordering, publishing, archiving and dependency handling
rich-text authoring with backend allowlist sanitization
Davvag uploader reuse for content resources, editor images, local videos,
assignment supporting files and learner submissions
YouTube/Facebook settings with tenant-local encrypted credentials,
OAuth state ownership checks, YouTube token refresh and Facebook long-lived-token exchange
provider metadata retrieval with editable manual fallbacks
saved ai-agent-creator selection and davvag-flow quiz-generation workflow
best-effort extraction of uploaded text, HTML, DOCX and PDF material for quiz prompts
editable draft quizzes, question types, randomization, limits and publishing
server-created timed quiz attempts, attempt-limit enforcement and automatic marking
manual quiz review with teacher feedback and Course Manager mark persistence
assignment rules, due/late/resubmission limits, file type/size validation,
verified uploaded media references, history, feedback and pass/fail state
Course Manager assessment, assignment, mark, grading-scale and notification reuse
student dashboard totals, teachers, pending work, current lesson and course status
learner views for resources, video, quiz/assignment history, marks and feedback
teacher submission review, quiz review, lock override and filtered reporting
course totals, grades, completion, inactivity and quiz pass-rate reporting
progress timestamps and queued lifecycle notifications
```

Declared Lesson Manager 1.3 dependencies:

```text
apps: course-manager, ai-agent-creator, davvag-tools
workflow: lesson-manager/generate-quiz
plugins: auth, sossdata, profile, davvag-flow
PHP extensions: curl, openssl
```

Verification completed on 2026-07-27:

```text
PHP lint: lesson-manager service and workflow adapter passed
business/security rules: passed
service descriptor to PHP handler reflection check: passed
JSON parse: 21 relevant app/component/schema/workflow files passed
JavaScript parse: dashboard, learn, lesson-style, quiz-studio, reports,
settings, studio and submissions passed
dependency and app-root navigation source checks: passed
```

Release gates that remain external to static implementation:

```text
run the complete #/app/lesson-manager/learn dock navigation matrix from section 57B
install/update the changed tenant schemas in the target datastore
exercise teacher and student roles against representative enrollment data
run a real saved AI agent and verify its provider/billing configuration
test upload storage and download URLs against the deployed MEDIA_FOLDER layout
test YouTube and Facebook OAuth with real approved provider applications
verify caption/transcript availability for the connected provider account
```

Provider transcript retrieval and PDF/DOCX text extraction are best-effort. A missing transcript or an unextractable document must not prevent manual authoring or quiz review. `DAVVAG_PROVIDER_SECRET` remains mandatory for storing provider secrets. `DAVVAG_FACEBOOK_GRAPH_VERSION` may override the validated Graph API version used by the deployment.

Do not describe Lesson Manager 1.3 as browser-certified or provider-certified until the external release gates above pass.

---

# 57E. LESSON FREE / CREDIT-POINT AUTHORING CONTRACT

Lesson Studio at:

```text
#/app/lesson-manager/studio
```

supports lesson-level access metadata through:

```text
lesson_manager_lesson.is_free
lesson_manager_lesson.required_credit_points
```

Contract:

```text
existing lessons with no is_free value are treated as free
new lessons default to free
free lessons persist required_credit_points as 0
non-free lessons require a whole credit-point value from 1 through 1,000,000,000
the service validates and normalizes both fields before persistence
the Studio sequence displays Free or the required credit-point count
```

Version baseline:

```text
Lesson Manager app: 1.7
Studio component: 1.4
Learn component: 1.5
API service component: 1.5
```

The `davvag-credit-points` ledger defined in section 68 now enforces this contract. Learners see the charge before confirming; `StartLesson` performs the authoritative one-time debit and permanent unlock. The deduction and unlock record share one database transaction and deterministic idempotency key. `profile_attributes` remains user-editable and must never be used as an authoritative wallet.

After changing these fields, install or update the `lesson_manager_lesson` schema in the target datastore. Do not describe the complete learner experience as browser-certified until the authenticated dock navigation and confirmation flow have been exercised in a supported browser session.

---

# 57C. VIRAL CONTENT MANAGER OPTIMIZER RESPONSE ARCHIVE

The `davvag-viral-content-manager` Existing Content Optimizer persists service responses in the tenant schema:

```text
optimizer_responses
```

The archive owns two response types:

```text
details_fetch          platform metadata/transcript returned by FetchUrlDetails
optimization_analysis  completed AnalyzeUrl optimization package
```

Implementation contract:

```text
FetchUrlDetails always calls the configured platform services and saves the returned payload
AnalyzeUrl saves its completed client response payload after the existing content/analysis records
ListOptimizerResponses returns the newest saved responses for the Responses grid sub-app
requestPayload contains only the optimizer input fields approved by the service
responsePayload contains the complete client-safe service result
```

The dock route is:

```text
#/app/davvag-viral-content-manager/responses
```

The Responses grid and payload viewer are implemented by the existing `viral-dashboard` component. When extending this archive, preserve the `optimizer_responses` schema contract, do not store provider credentials, keep fetched raw provider data masked, and bump the app/component versions after descriptor or UI changes.

For explicit Fetch Details behavior:

```text
LOCK THE BUTTON DURING THE REQUEST
CALL FetchUrlDetails WITH forceRefresh=true
REPLACE THE PREVIOUS FETCHED FORM FIELDS WITH THE NEW RESPONSE, INCLUDING EMPTY VALUES
SAVE THE NEW RESPONSE AS A SEPARATE ARCHIVE ROW
RELOAD THE RESPONSES GRID AFTER SUCCESS
```

# 58. DEPLOYMENT ARCHITECTURE

DAVVAG deployment depends on:

```text
Apache rewrite support
AllowOverride All
PHP extensions
writable media folder
protected tenant folders
correct tenant host mapping
correct database configuration
secure config storage
```

Important rewrite/protection files include:

```text
.htaccess
components/.htaccess
assets/.htaccess
files/.htaccess
davvag-core/.htaccess
tenant plugins/.htaccess
```

Deployment must preserve these files.

Do not deploy by copying only app PHP/JS files and omitting framework routing or protection files.

---

# 59. PHP AND SERVER REQUIREMENTS

Common requirements include:

```text
curl
mysqli
mbstring
iconv
PHP sessions
openssl
zip when required
```

Apps may require additional extensions such as:

```text
imap
```

App-specific extension requirements must be declared in `app.json`.

Do not assume development-server extensions exist in production.

---

# 60. CORS AND SESSION SECURITY

Credentialed cross-origin behavior must not use an invalid wildcard origin configuration.

Rules:

```text
USE TRUSTED ORIGINS FOR CREDENTIALS
HANDLE OPTIONS PREFLIGHT WHEN REQUIRED
HARDEN AUTH COOKIES
USE HTTPS IN PRODUCTION
```

Recommended cookie properties where compatible:

```text
HttpOnly
Secure
SameSite=Lax or SameSite=Strict
```

Do not make app-specific CORS exceptions without understanding framework-wide session behavior.

---

# 61. PRODUCTION SECURITY REQUIREMENTS

Minimum production requirements:

```text
HTTPS
PROTECTED TENANT RESOURCES
PROTECTED TENANT PLUGINS
SERVER-SIDE SECRETS
INPUT VALIDATION
AUTHORIZATION CHECKS
SAFE RAW QUERIES
HARDENED COOKIES
CONTROLLED CORS
LIMITED PUBLIC WRITE ENDPOINTS
```

Known permissive or legacy framework behavior must not be treated as a reason to create new insecure code.

New code should improve security without silently breaking framework compatibility.

---

# 62. EXISTING SOURCE SPELLINGS

The current framework contains spellings that are part of the source contract.

Preserve them when calling or referencing existing code:

```text
SOSSPlatform::intialize()
Autendicate()
global/templetes/
catogory
scopData
excutionStack
PermisionValues
UserVieObjects
```

Do not automatically “correct” them during unrelated feature work.

Renaming them is a breaking refactor and must be handled separately.

---

# 63. VERSIONING RULES

When modifying app resources or descriptors:

```text
BUMP APP VERSION
BUMP COMPONENT VERSION WHEN THE COMPONENT CONTRACT USES IT
BUMP SERVICE COMPONENT VERSION WHEN REQUIRED BY THE EXISTING PATTERN
```

Version bumps are operational, not cosmetic.

They help prevent stale descriptors, scripts and styles from remaining in browser caches.

When a bug appears only after a code update, verify cached versioned resources before redesigning the implementation.

---

# 64. NON-NEGOTIABLE ARCHITECTURE RULES

1. Resolve the active tenant before editing tenant resources.

2. Keep app-specific behavior inside the tenant app, not framework core.

3. Search existing apps and reusable capabilities before creating a new subsystem.

4. Every app must have a complete dependency block.

5. Do not use blank dependency placeholders.

6. Frontend components must not contain authoritative business logic.

7. Backend business logic belongs behind DAVVAG service components.

8. Use `SOSSData` for normal application persistence.

9. Create and maintain schema JSON files as data contracts.

10. Keep stable schema relationships in the schema source.

11. Validate all untrusted input on the backend.

12. Do not send raw SQL fragments from the browser.

13. Use workflows for reusable multi-step orchestration, not for every simple method.

14. Use plugins for reusable PHP capabilities, not random app code.

15. Use existing auth, profile and permission systems.

16. Do not create parallel identity tables without a documented reason.

17. Use `ai-agent-creator` as the shared AI runtime.

18. AI must not replace authorization or deterministic validation.

19. Keep secrets out of frontend files, workflows and public responses.

20. Register apps explicitly in the tenant and intended group files.

21. Do not remove existing app registrations while adding a new app.

22. Declare cross-app dependencies.

23. Confirm user-group access for cross-app popup/component reuse.

24. Test navigation inside the actual dock.

25. Test apps in every supported dock.

26. Preserve framework source spellings.

27. Bump versions after resource changes.

28. Validate JSON and PHP before browser testing.

29. Preserve backward compatibility when modifying existing app contracts.

30. Do not redesign DAVVAG as Laravel, Yii or another framework inside one feature.

31. Lock every service-triggering button for the full lifetime of its in-flight request and reject repeated activation until completion.

---

# 65. AI CODING AGENT EXECUTION PROTOCOL

Every AI coding agent working on DAVVAG must follow this sequence.

## Phase 1 — Understand

```text
READ THIS DOCUMENT
READ RELEVANT DAVVAG DOCS
RESOLVE ACTIVE TENANT
READ TARGET APP
READ RELATED SCHEMAS
READ RELATED WORKFLOWS
SEARCH EXISTING REUSABLE CAPABILITIES
```

## Phase 2 — Design

Define:

```text
app capability boundary
files to create or modify
service contracts
schema namespaces
workflow needs
cross-app dependencies
plugin dependencies
PHP extension dependencies
group access
security checks
service request button-lock behavior
test routes
```

## Phase 3 — Implement

```text
CREATE OR MODIFY APP FILES
CREATE OR MODIFY SCHEMAS
CREATE OR MODIFY WORKFLOWS
DECLARE DEPENDENCIES
REGISTER APP / GROUP ACCESS
PRESERVE EXISTING FILE CONTENT
BUMP VERSIONS
```

## Phase 4 — Validate

```text
PARSE ALL JSON
CHECK PHP SYNTAX
CHECK FILE PATHS
CHECK CLASS / NAMESPACE MATCH
CHECK HANDLER METHOD NAMES
CHECK DEPENDENCY CONTRACT
CHECK SCHEMA REFERENCES
CHECK ROUTE TARGETS
```

## Phase 5 — Test

```text
TEST APP DESCRIPTOR
TEST COMPONENT DESCRIPTORS
TEST RESOURCE FILES
TEST SERVICES
TEST DATA WRITES / READS
TEST WORKFLOWS
TEST PERMISSIONS
TEST CROSS-APP INTEGRATIONS
TEST SUPPORTED DOCKS
```

## Phase 6 — Report

The agent must report:

```text
files created
files modified
schemas added or changed
workflows added or changed
dependencies added
routes added
services added
security decisions
tests completed
known limitations
```

Do not claim a test passed unless it was actually executed.

---

# 66. AI AGENT DO-NOT-DO LIST

An AI coding agent must not:

```text
assume localhost is the active tenant
edit framework core for app-specific behavior
remove existing tenant registrations
invent a second auth system
invent a second profile system
invent a second AI agent runtime
connect app code directly to MySQL by default
store secrets in frontend files
accept raw SQL from the browser
hide complex business logic in frontend components
silently change public service response shapes
fix source misspellings during unrelated work
skip dependency declarations
skip version bumps after resource changes
claim successful testing without running tests
```

---

# 67. FINAL ARCHITECTURE PRINCIPLE

The central DAVVAG application development principle is:

```text
BUILD APPS AS DECLARED, TENANT-AWARE, REUSABLE CAPABILITIES
```

The framework should remain understandable through its source contracts:

```text
TENANT
   ↓
APP DESCRIPTOR
   ↓
COMPONENTS + SERVICES
   ↓
SCHEMAS + WORKFLOWS + PLUGINS
   ↓
AUTH + DATA + SHARED PLATFORM CAPABILITIES
```

When adding a feature, the correct question is not:

> How can this feature bypass the framework?

The correct question is:

> Which DAVVAG contract owns this responsibility, and how should the feature integrate with it cleanly?

That principle is the authority for future DAVVAG application development.

---

# 68. CREDIT POINTS APPLICATION BASELINE

The active tenant includes `davvag-credit-points` as the platform wallet for whole-number virtual credits. It is registered for tenant, `web_user`, and `sysadmin` contexts, but not anonymous users.

Core rules:

```text
DAVVAG CREDIT TRANSACTIONS ARE IMMUTABLE AND DOUBLE-ENTRY
USER WALLET BALANCES MAY NOT BECOME NEGATIVE
EVERY MUTATION REQUIRES AN IDEMPOTENCY KEY
PURCHASED CREDITS DO NOT EXPIRE
PROMOTIONAL LOTS MAY EXPIRE
COUPON PLAINTEXT IS NEVER STORED
PAYMENT CREDITING REQUIRES A VERIFIED SERVER CALLBACK
```

The app owns programs, wallets, transactions, entries, lots, reservations, packages, purchase orders, payment events, reward claims, coupon campaigns/codes/redemptions, idempotency records, and permanent lesson unlocks. Its PHP ledger library is the authoritative cross-app integration contract. The implementation uses a direct `mysqli` transaction boundary only because the current SOSSData public connector does not expose begin/commit/rollback; schemas are still declared as DAVVAG tenant schemas and initialized through SOSSData.

`credit-admin-api.ProcessExpirations` is the protected paginated scheduler target for due promotional lots. Each processed amount creates a balanced `EXPIRATION` transaction; purchased lots are excluded. Configure the declared `davvag-scheduler` dependency to call this endpoint on the deployment's required cadence.

Lesson Manager integration:

- Missing `is_free` remains backward-compatible and is treated as free.
- A paid lesson requires a positive whole `required_credit_points` value.
- The learner confirms the charge before opening the lesson.
- Unlock uses the deterministic key `lesson-access:{profile_id}:{lesson_id}` and atomically records the debit plus a unique permanent unlock.
- Teachers and administrators retain the existing management/access bypass.

Operational secrets are server environment values only:

```text
DAVVAG_CREDIT_COUPON_PEPPER
DAVVAG_CREDIT_PAYMENT_WEBHOOK_SECRET
```

The payment service is a signed provider-neutral completion boundary. A provider-specific checkout connector must create the external checkout and send the verified completion fields; the Credit Points frontend must never collect raw card data or mark an order paid from browser state.

## Shared currency contract

`currency-configuration` is the tenant's common currency application and owns the `currency_configuration` schema plus the `List`, `Active`, and `Default` service contract. Business apps must consume its active three-letter currency codes instead of maintaining local currency lists or accepting unchecked free text.

Credit Points 1.3 follows this contract:

- Package administration receives active currencies and the default currency from `currency-configuration`.
- The currency field is a configured-currency selector.
- The server rejects unknown or inactive codes both when saving a package and when creating a purchase order.
- Package prices use the shared symbol and configured decimal-place count.
- Purchase orders retain the selected code as an immutable currency snapshot.

## Administration routes and product mapping

Credit Points 1.3 separates configuration management into routed administration subapps:

```text
#/app/davvag-credit-points/admin/packages
#/app/davvag-credit-points/admin/rewards
#/app/davvag-credit-points/admin/coupons
```

The main `#/app/davvag-credit-points/admin` route remains responsible for wallet reconciliation and audited manual credit grants. It links to the three configuration subapps.

The subapps provide:

```text
PACKAGES    list, search, create, edit, delete, and DAVVAG product mapping
REWARDS     list, search, create, edit, and delete reward rules
COUPONS     list, search, create, edit, and delete campaigns;
            generate codes; view masked codes; delete or disable codes
```

Package product mapping uses:

```text
davvag_credit_package.product_id -> products.itemid
```

`product_id` is the optional DAVVAG catalog relationship. It is not the same field as `provider_product_id`, which remains the external payment provider's product identifier. The server validates a non-zero `product_id` against the tenant product catalog before saving. The app therefore declares the `productapp` application dependency and the `products` schema dependency.

The package schema declares the many-to-one `product_id -> products.itemid` relationship. Because the current datastore schema bootstrap does not add this column to an already-created package table, `CreditDatabase` also performs an idempotent compatibility migration that adds `product_id` and `idx_credit_package_product` when missing. Fresh installations remain schema-driven; existing installations gain the same contract without dropping or recreating package data.

Configuration deletion must preserve audit and transaction history:

```text
unused package / reward / campaign / coupon     hard delete is allowed
referenced package / reward / campaign          mark DELETED and hide from admin lists
campaign archived with active coupon codes      disable its active codes
redeemed coupon code                            disable and retain it
```

Do not cascade-delete purchase orders, reward claims, coupon redemptions, ledger transactions, or ledger entries to satisfy an administration delete request.

---

# 69. COMMON CURRENCY APPLICATION

`currency-configuration` is the tenant-wide authority for real monetary currencies. Applications that create, edit, charge, invoice, pay, report, or display monetary values must declare both the `currency-configuration` app dependency and the `currency_configuration` schema dependency. Applications with no currency behavior must not declare these dependencies.

The authoritative implementation is:

```text
{TENANT_RESOURCE_LOCATION}/apps/currency-configuration/services/currency-configuration-handler/service.php
```

Its shared PHP contract is `currency_configuration\CurrencyConfigurationService`:

```text
listCurrencies()          all configured records
activeCurrencies()        active records for selectors
defaultCurrency()         active base currency, or first active currency
requireActiveCurrency()   validate a three-letter configured code
resolveCurrencyCode()     validate a supplied code or use the configured default
formatAmount()            format with configured symbol and decimal places
```

Backend callers load the tenant app implementation and instantiate the namespaced service:

```php
require_once(
    TENANT_RESOURCE_LOCATION .
    "/apps/currency-configuration/services/currency-configuration-handler/service.php"
);

$currency = new \currency_configuration\CurrencyConfigurationService();
$code = $currency->resolveCurrencyCode($submittedCode);
```

Do not copy currency records, default codes or provider-specific currency fallbacks into each application. A missing submitted code resolves to the configured active base currency. A supplied code must be a configured, active three-letter code. Server-side write and payment boundaries must perform this validation even when the UI uses a controlled selector.

The component service exposes these framework endpoints:

```text
List       all configured records
Active     active records for selectors
Default    configured active base currency, or first active currency
Save       create or update a normalized currency record
```

The shared frontend service `currency-configuration-handler` exposes `loadActive`, `loadDefault`, and `format`. Currency input controls must use active configured records and submit the three-letter code, never the display symbol. The symbol and decimal-place count are presentation metadata; the three-letter code is the persisted integration value.

As of the 2026-07-28 verified tenant baseline, the shared contract is declared by the product, shop, course, lesson, order, banking, PayPal, Stripe, DirectPay, profile and related commerce applications that handle monetary values. Adding currency behavior to another app requires both dependency declarations and server-boundary validation; copying a dependency into apps with no monetary behavior is not required.

Historical transactions, paid orders, and ledger entries retain their stored currency code as an immutable snapshot. Deactivating a currency prevents new writes but does not rewrite history.

When deploying a currency-contract change:

```text
INSTALL OR UPDATE currency_configuration IN THE TARGET DATASTORE
CONFIRM AT LEAST ONE ACTIVE CURRENCY EXISTS
CONFIRM THE INTENDED BASE CURRENCY
TEST PRODUCT / ORDER WRITES WITH ACTIVE AND INACTIVE CODES
TEST EACH ENABLED PAYMENT PROVIDER WITH A SUPPORTED CURRENCY
VERIFY HISTORICAL RECORDS STILL RENDER FROM THEIR STORED CODE
```

---

# 70. DECIMAL DATA AND TRAVEL DESTINATION COORDINATES

The `phpmysql` SOSSData adapter must keep `decimal` and `java.util.Date` as separate serialization contracts. Decimal values are numeric on both reads and writes; they must never pass through `strtotime()` or date formatting. A decimal routed through the date branch becomes the Unix epoch display value (`01-01-1970 00:00:00`) and can also be corrupted before persistence.

The authoritative adapter behavior is:

```text
decimal read       database decimal string -> PHP float (null remains null)
decimal write      numeric input -> unquoted numeric SQL value (null -> SQL NULL)
java.util.Date     database/application date -> DAVVAG date formatting
```

The Travel Destinations coordinate schema uses:

```text
travel_destination.latitude     DECIMAL(9,7)    valid range -90 through 90
travel_destination.longitude    DECIMAL(10,7)   valid range -180 through 180
```

Do not use `DECIMAL(8,7)` for these fields: it leaves only one digit before the decimal point and cannot represent normal two-digit Sri Lankan longitudes or the documented coordinate boundaries. Backend range validation remains mandatory even with sufficient database precision.

For an existing deployment, changing schema JSON alone does not resize an already-created column because the current `phpmysql` schema synchronizer adds missing columns but does not modify existing column definitions. Deployment must therefore inspect and, when necessary, migrate the live table before relying on the expanded ranges:

```sql
ALTER TABLE `travel_destination`
    MODIFY `latitude` DECIMAL(9,7) NULL,
    MODIFY `longitude` DECIMAL(10,7) NULL;
```

After deployment, verify a known destination through `GetDestination` and confirm that latitude/longitude are JSON numbers, fall within their valid ranges, retain the intended precision, and no longer contain epoch-formatted strings. If earlier writes were attempted while decimal serialization was broken or while longitude precision was undersized, compare the stored coordinates with the authoritative source and repair affected rows; a serializer correction cannot reconstruct already-corrupted values.

---

# 71. LESSON MANAGER PYTHON PUBLISHER

The Lesson Manager textbook publisher is located at:

```text
{TENANT_RESOURCE_LOCATION}/apps/lesson-manager/python/lesson_publisher.py
```

It reads only `DAVVAG_BASE_URL`, `DAVVAG_EMAIL`, `DAVVAG_PASSWORD`, and optional `DAVVAG_SECURITY_TOKEN` from the configured environment file. Existing process environment variables take priority. Credentials and tokens must never be written to import state or printed in logs.

Password authentication must use the public browser-login contract:

```text
GET /components/userapp/login-handler/service/login
parameters: email, password, domain
domain: hostname parsed from DAVVAG_BASE_URL
```

Do not use `/components/dock/auth-handler/service/login` to establish a session. That service is permission-protected from anonymous callers and returns an unauthorized framework response before credentials can be authenticated. A successful public login must return a result containing a security token; the session cookie is then used for Lesson Manager API requests.

The publisher is dry-run by default. A subject code is mandatory, must resolve exactly and case-insensitively through `Bootstrap`, and must be among the courses and subjects manageable by the authenticated profile. `--apply` authorizes uploads and draft database writes; `--apply --publish` additionally publishes only after verification. On the verified 2026-07-29 tenant account, the Grade 11 Catholic subject code is `CATH_11S`.

Windows console output must remain valid when lesson titles contain Sinhala or other characters outside the active legacy code page. Serialize CLI JSON with ASCII escapes (or an equivalently guaranteed UTF-8 output channel); do not allow a successful import or preflight to end with `UnicodeEncodeError` while printing its summary.

The verified dry-run baseline for `CATH_11S` is:

```text
lessons       25
content       30
media files   130
API writes    0
```

The shared `phpmysql` SQL type generator must preserve whitespace between the
nullability and default clauses. Every applicable type must produce `NULL
DEFAULT ...` or `NOT NULL DEFAULT ...`; `NULLDEFAULT ...` is invalid SQL and
causes first-write schema synchronization to fail. Regression coverage is kept
with the adapter under `plugins/sossdata/phpmysql/tests`.

Apply mode creates or validates lesson records before beginning media uploads,
so schema and lesson-write authorization failures stop early. Upload and record
state is resumable: after correcting a server-side failure, rerun the identical
subject-code command and retain `.lesson-import-state-{subject-code}.json` so
already verified uploads are reused.

---

# 72. LESSON STUDIO MATERIAL MANAGEMENT UI

As of the 2026-07-29 tenant baseline, the Lesson Manager Studio Materials tab uses a list-first management interface. The material editor must not remain permanently beside the material list because the two-column layout makes saved titles, metadata, and actions too narrow at normal administration viewport sizes.

The authoritative interaction contract is:

```text
MATERIALS TAB         full-width ordered material list
NEW MATERIAL          opens an empty modal form
EDIT                  opens the selected record in the same modal form
SAVE                  persists, closes the modal, and refreshes the ordered list
CANCEL / CLOSE / ESC  closes the modal without saving
DELETE                requires confirmation and refreshes the list after success
```

Each list row displays the material order, title, content type, available file/resource metadata, and explicit Edit and Delete actions. The empty state provides its own New material action. Records are sorted numerically by `sort_order` when loaded, and a new record defaults to the next available order value.

The reusable New/Edit modal owns the complete material form:

```text
content type
sort order
title
rich-text body editor and formatting toolbar
rich-text image upload
lesson resource upload
resource URL / uploaded file reference
```

The rich-text editor continues to synchronize HTML with `contentForm.body` and uses the existing Lesson Manager backend sanitization contract. Uploaded images and resources continue through the declared Davvag uploader; saving is disabled while an upload is in progress. Video and assignment upload controls remain in their existing tab-specific Uploaded media area.

The modal is viewport constrained, scrolls its body independently, supports backdrop and Escape dismissal, and becomes a full-height sheet on small screens. Future Studio changes must preserve readable full-width material listing and must not reintroduce the former side-by-side editor/list layout.

---

# 73. LESSON MANAGER MOBILE LEARN FLOW

The learner route is:

```text
#/app/lesson-manager/learn
```

Its responsive interaction order is deliberately progressive:

```text
1. CHOOSE ASSIGNED COURSE
2. CHOOSE SUBJECT WITHIN THAT COURSE
3. CHOOSE A PUBLISHED LESSON
4. VIEW CONTENT, RESOURCES, VIDEO AND COMPLETION REQUIREMENTS
```

Do not automatically open the first course or lesson. The learner must make each selection so the same flow remains understandable on a narrow phone viewport. On mobile, the course/subject picker, lesson list and lesson content are separate visual stages with explicit Subjects and Lessons back controls. On wider screens the lesson list and selected content may share the available width.

Lessons must remain grouped by `subject_id`. Previous and Next navigation operate only inside the selected subject. The lesson screen must show the configured completion checklist for reading, video, quiz, assignment and teacher approval before the detailed material. Images, video, embedded frames, tables, resources and action controls must fit narrow viewports without horizontal page overflow.

The CMS compiles downloaded views with Vue 2 after browser HTML parsing. The historically affected Dock used Vue 2.0.3, while the current localhost CMS v7 bundle reports Vue 2.6.12; the entity-decoding hazard occurs before either compiler version runs. In interpolation text, never write a logical-AND sequence whose right side starts with `current` as a literal `&&current`; the legacy HTML entity parser can consume `&curren;` and corrupt the Vue expression. Use:

```html
{{current.progress&amp;&amp;current.progress.lesson_completed}}
```

After changing a downloaded app view, script or stylesheet, bump the app and affected component versions together. The CMS app renderers must use the freshly downloaded descriptor version for component resource URLs; stale app-list version metadata must not force an old script to run against a new partial.

The lesson selector's `disabled` binding must always evaluate to a JavaScript boolean. Vue preserves numeric `0` as the value of a bound HTML boolean attribute, and the browser treats any present `disabled` attribute as disabled. The broken expression ended with `||busyLesson`; with the idle value `busyLesson = 0`, every row displayed `Available` but accepted no click and generated no `StartLesson` request. The corrected form is:

```html
v-bind:disabled="(!l.unlocked&&!l.credit_locked)||!!busyLesson"
```

The same rule applies to quiz attempt-limit controls and every other bound HTML boolean attribute: coerce numeric counters with `!!` or return an explicit `true`/`false`. Lesson Manager 1.9 and Learn 1.7 contain this correction. Verification must confirm that an idle lesson button renders `disabled: false`, has a click handler, calls `StartLesson` with the lesson ID, assigns `current`, and advances `mobileStage` to `content`.

---

# 74. TASK TRACKER WORK LOG REPORTING

As of the 2026-08-05 tenant baseline, Task Manager 2.7 at:

```text
davvag-core/localhost/apps/task-tracker
```

provides two read-only reporting subapps for the work recorded against tasks:

```text
task-work-log-summery
task-work-log-detailed
```

The identifier `task-work-log-summery` intentionally preserves the requested spelling and is part of the route/component contract. Its user-facing title should be `Work Log Summary`. Do not silently rename the identifier to `task-work-log-summary` after links or descriptors depend on it; a correction requires an explicit route migration or alias.

The routes and dock entries are:

```text
#/app/task-tracker/task-work-log-summery   Work Log Summary
#/app/task-tracker/task-work-log-detailed  Work Log Detailed
```

Both subapps report existing data. They must not create a second time-entry namespace or copy work-log rows into reporting tables. Their authoritative data path is:

```text
task_manager_projects.projectId
        -> task_manager_tasks.projectId
        -> task_manager_work_logs.taskId
```

The read model is defined by the non-persisted raw-query schema:

```text
schemas/task_manager_work_log_report.json
```

Both endpoints execute it through `SOSSData::ExecuteRaw()`. The SQL must apply the inclusive `logDate` range, optional numeric project filter, and project-profile permission condition before returning joined rows to PHP. Do not retrieve every task or work-log record and then date-filter the dataset in application memory.

The stored integer `task_manager_work_logs.durationInMinutes` is the authoritative duration. Reports calculate totals from minutes and only then present hours. Never total independently rounded per-row hour values.

```text
task minutes    = SUM(work log durationInMinutes for one task)
project minutes = SUM(task minutes for one project)
date minutes    = SUM(work log durationInMinutes for one calendar date)
report minutes  = SUM(all included work log durationInMinutes)
decimal hours   = total minutes / 60
HH:MM           = FLOOR(total minutes / 60) + ':' + zero-padded remainder
```

Negative or missing durations must not reduce a total. Treat them as zero in report output and flag or omit malformed rows according to the normal error-handling contract. A valid zero-minute historical row may be displayed but contributes zero.

## Reporting Period Contract

Both reports use the same filters:

```text
period preset  Weekly | Monthly | Specific Date Range
startDate      local calendar date, inclusive
endDate        local calendar date, inclusive
projectId      optional; All Projects means all accessible projects
```

Preset behavior is deterministic:

```text
Weekly              current Monday through current Sunday
Monthly             first through last calendar day of the selected/current month
Specific Date Range user-selected inclusive start and end dates
```

The report includes a row when its `logDate` calendar date is within the inclusive range. `logDate`, rather than task creation/update/due date, controls date membership. Normalize boundaries server-side using the tenant/server local timezone, validate real `YYYY-MM-DD` values, reject `startDate > endDate`, and return the effective normalized range with the result. The frontend must not implement an independent inclusion rule.

Changing the period, either boundary, or the project filter reloads the report. Both screens must show the active period, an explicit empty state, loading/error state, and the total duration in both `HH:MM` and decimal hours. Decimal-hour display may be rounded to two places, but returned/stored total minutes remain exact.

## Work Log Summary Subapp

`task-work-log-summery` answers how much time was worked by project, task, and date for the selected period.

Its Project-wise view groups results in this order:

```text
PROJECT
  TASK
    total minutes
    HH:MM
    decimal hours
```

Each project row displays the project total, and each nested task row displays `taskId`, task subject/title, task status, and the total time calculated for that task. The sum of visible task totals must equal the project total. The sum of visible project totals must equal the overall report total.

Its Date-wise view groups the same filtered work logs by `logDate` calendar day. Each day displays its daily total and a project/task breakdown so users can identify where that day's hours were spent. Dates are ordered newest first by default; project and task labels use stable, human-readable ordering within a day.

The screen must allow switching between Project-wise and Date-wise presentation without changing the selected filters or producing different overall totals.

## Work Log Detailed Subapp

`task-work-log-detailed` displays one row per included `task_manager_work_logs` record. Each row contains at least:

```text
work date
project name
task id
task subject/title
profile/person name
comments/work description
start time
end time
duration in minutes
duration as HH:MM
duration as decimal hours
recorded progress
recorded status
```

The default ordering is `logDate` descending, followed by `startDate` descending and `logId` descending for stable results. The detailed report footer displays exact total minutes plus `HH:MM` and decimal-hour totals for the currently filtered rows. Its total must equal the Summary report total for the same effective filters and permissions.

## Service, Permission, and Response Contract

Reporting aggregation belongs in the Task Tracker backend service, not in authoritative frontend business logic. The `taskapi` service exposes POST report methods with request and handler names following the DAVVAG convention:

```text
POST WorkLogSummary  -> postWorkLogSummary($req, $res)
POST WorkLogDetailed -> postWorkLogDetailed($req, $res)
```

The service request accepts `period`, `startDate`, `endDate`, and optional `projectId`. Responses return the effective filters, exact `totalMinutes`, formatted/presentation-ready totals, and the appropriate grouped or detailed rows. The frontend may format labels but must not recalculate which records are authorized or included.

Apply the existing Task Manager access rules before returning or aggregating any work log:

```text
sysadmin             may report all projects
other authenticated  may report only projects allowed by task_manager_project_access
requested projectId  must be rejected or return no data when inaccessible
```

Do not leak project, task, profile, comment, or duration data through aggregates. A total is permission-sensitive data and must be calculated only after inaccessible projects and tasks are excluded. Raw queries do not automatically apply `sysviewobject`, so `task_manager_work_log_report` must keep its explicit `task_manager_project_access` condition for non-sysadmin profiles. All dates must be strictly validated, and project/profile/admin parameters must be server-derived or cast numeric values. Never accept raw SQL fragments from the browser. Use SOSSData and the existing Task Manager namespaces; do not connect directly to MySQL from the component.

## Descriptor and Verification Requirements

Both components are registered in `task-tracker/app.json`, both routes are present in `configuration.webdock.routes.partials`, both entries are exposed in `configuration.dock.subapps`, and the report methods are declared in `services/taskapi/component.json`. The existing `taskapi` service remains in `configuration.webdock.onLoad`, and both screens reuse the shared `task-style` component for dock compatibility.

The raw-query implementation uses Task Manager app version 2.7, `taskapi` version 0.4, and report component version 0.1. Future report changes must bump the affected versions. Verification must cover:

```text
weekly boundary and inclusive Sunday
calendar-month boundary, including leap-year February
single-day specific range
custom inclusive multi-day range
invalid and reversed date ranges
All Projects and one accessible project
rejection/exclusion of an inaccessible project
multiple logs for one task
multiple tasks under one project
multiple projects on one date
task, project, date, and overall totals derived from exact minutes
Summary and Detailed totals matching for identical filters
empty result behavior
stable detailed ordering
both /admin#/app/... and /#/app/... dock rendering
SQL date filtering before PHP row shaping or aggregation
raw-query permission exclusion for non-sysadmin profiles
```

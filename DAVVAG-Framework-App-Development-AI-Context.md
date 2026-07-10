# DAVVAG FRAMEWORK — APPLICATION DEVELOPMENT ARCHITECTURE

## AI CONTEXT DOCUMENT

**Document role:** Permanent architecture and implementation context  
**System:** DAVVAG Framework Application Development  
**Primary stack:** PHP 8+, DAVVAG tenant-aware framework, Webdock, Vue.js, JavaScript, MySQL through SOSSData, JSON schemas and JSON workflows  
**Status:** Architecture Authority  
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

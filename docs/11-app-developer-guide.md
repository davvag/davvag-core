# App Developer Guide for DAVVAG

This guide is for application developers and AI agents that need to build DAVVAG apps and store data correctly.

The key idea is simple:

`SOSSData` is the framework facade. It chooses a datastore adapter for the active tenant, then the adapter uses the tenant schema files to read or write data.

## App Folder Convention

A tenant app normally lives under:

```text
{TENANT_RESOURCE_LOCATION}/apps/{app-code}
```

For local development this may look like:

```text
davvag-core/localhost/apps/task-tracker
```

Use this app shape:

```text
apps/{app-code}/
  app.json
  app.php
  components/
    {component-name}/
      component.json
      partial.html
      script.js
      component.css
  services/
    {service-name}/
      component.json
      script.js
      service.php
```

Register the app in the tenant files that should see it:

```text
tenant.json
sysadmin.json
web_user.json
anonymous.json, only if public access is intentional
```

Do not assume the repo-local tenant is the active runtime tenant. The root `config.json` can point `TENANT_RESOURCE_LOCATION` somewhere else through `RESOURCE_LOCATION` and `LOCAL_DEV_HOST`. Before browser testing, confirm which tenant folder is active.

## App Descriptor Pattern

`app.json` declares components, services, route mappings, startup components, and on-load dependencies.

Example:

```json
{
  "components": {
    "projects": {"type": "component", "location": "components"},
    "taskapi": {"type": "service", "location": "services"}
  },
  "description": {
    "title": "Task Manager",
    "author": "DAVVAG",
    "version": "0.3",
    "icon": "appicon.png"
  },
  "tags": ["showincms", "showindock"],
  "configuration": {
    "webdock": {
      "startupComponent": "projects",
      "onLoad": ["taskapi"],
      "routes": {
        "partials": {
          "/": "projects",
          "/projects": "projects"
        }
      }
    },
    "dock": {
      "subapps": [
        {"name": "Projects", "path": "projects"}
      ]
    }
  }
}
```

When changing CSS or descriptors, bump the app `description.version`. Webdock appends this version to resource URLs, so a version bump helps avoid stale cached component descriptors and styles.

## Component Descriptor Pattern

Component descriptors live at:

```text
components/{component-name}/component.json
```

Typical UI component:

```json
{
  "name": "projects",
  "resources": {
    "files": [
      {"type": "mainScript", "location": "script.js"},
      {"type": "mainView", "location": "partial.html"}
    ],
    "css": [
      {"type": "css", "location": "projects.css"}
    ]
  }
}
```

Typical service component:

```json
{
  "name": "taskapi",
  "serviceHandler": {
    "file": "service.php",
    "class": "TaskManagerService",
    "methods": {
      "SaveTask": {"method": "POST"}
    }
  }
}
```

Service method names must follow DAVVAG's PHP naming convention:

```text
POST SaveTask -> postSaveTask($req, $res)
GET ListItems -> getListItems($req, $res)
```

## Route Navigation

Use the shell route component for app navigation:

```javascript
handler = exports.getShellComponent("soss-routes");
handler.appNavigate("../tasks?projectId=" + projectId);
handler.appNavigate("../task?projectId=" + projectId + "&taskId=" + taskId);
```

Important behavior:

```text
soss-routes.appNavigate() appends plain paths to the current hash route.
```

From:

```text
#/app/task-tracker/projects
```

this is usually wrong:

```javascript
handler.appNavigate("/tasks?projectId=" + projectId);
```

because it can become:

```text
#/app/task-tracker/projects/tasks?projectId=...
```

Use sibling navigation instead:

```javascript
handler.appNavigate("../tasks?projectId=" + projectId);
```

Keep a direct hash fallback in split components if you need simple testing outside the shell, but shell code should use `exports.getShellComponent("soss-routes")`.

## Running In Both Docks

DAVVAG apps may run inside different docks:

```text
/#/app/{app-code}/...
/admin#/app/{app-code}/...
```

The default dock and admin dock may not provide the same CSS framework version. For example, a default `davvag-cms` dock can behave like Bootstrap 4, while the admin `dock` can behave more like Bootstrap 3. If your app uses older classes such as:

```text
btn-default
btn-xs
label label-default
progress-bar-success
glyphicon ...
col-md-5
```

add a scoped compatibility stylesheet instead of relying on the dock theme.

Recommended pattern:

1. Create a lightweight shared style component, for example `components/task-style`.
2. Register it in `app.json`.
3. Add it to `configuration.webdock.onLoad`.
4. In every main screen component, call a small style loader as a fallback.

Example fallback loader:

```javascript
function ensureTaskCommonStyles() {
    if (document.getElementById("task-tracker-common-css")) {
        return;
    }
    var link = document.createElement("link");
    link.id = "task-tracker-common-css";
    link.rel = "stylesheet";
    link.type = "text/css";
    link.href = "components/task-tracker/task-style/file/task-common.css?v=0.3";
    document.getElementsByTagName("head")[0].appendChild(link);
}
```

The URL should use the DAVVAG component file endpoint:

```text
components/{app-code}/{component-name}/file/{file-name}
```

Avoid `../` CSS paths in component descriptors. Browser URL normalization can change the request before PHP receives it.

## File Uploads

For app attachments, use the DAVVAG file uploader app component. This is the same pattern used by Album-form and Article-form apps.

Do this:

```javascript
exports.getAppComponent("davvag-tools", "davvag-file-uploader", function (uploader) {
    uploader.initialize();
    uploader.upload(newfiles, "task_manager_attachments", taskId, function () {
        newfiles = [];
        cb();
    });
});
```

Do not call `soss-uploader` directly from app screens unless you are intentionally bypassing the standard modal/progress wrapper.

Important naming rule:

```text
davvag-file-uploader creates filenames as {id}-{file.name}
```

Pass only the entity ID as the third argument:

```javascript
uploader.upload(files, "task_manager_attachments", taskId, cb);
uploader.upload(files, "task_manager_comment_attachments", commentId, cb);
```

Do not pass:

```javascript
taskId + "-" + file.name
```

Read uploaded files through:

```text
components/dock/soss-uploader/service/get/{store-name}/{id}-{fileName}
```

Typical UI flow:

1. User selects files.
2. `FileReader` creates local previews.
3. Save metadata with your app service.
4. Upload the actual files with `davvag-file-uploader`.
5. Reload the entity details so metadata and file URLs are in sync.

Server-side importers can write compatible files directly when there is no browser `File` object. Use the same storage convention used by `soss-uploader`:

```text
MEDIA_FOLDER/DATASTORE_DOMAIN/{store-name}/{id}-{fileName}
```

Then insert matching metadata rows into the app attachment namespace.

Task Manager uses this for `TaskEmailClient`:

```text
GET components/task-tracker/TaskEmailClient/service/getMail
```

The service reads project IMAP settings, requires PHP `ext-imap`, imports new emails from project-authorized profile email addresses, stores the original `emailMessageId` on tasks, and saves replies as discussion comments matched by `Message-ID`, `In-Reply-To`, or `References`.

## Reusable Lookup Popups

DAVVAG apps can expose reusable selector components for other apps by calling `exports.Complete(selectedObject)` from the selector. A caller opens the selector through the shell `app_popup` component and receives the selected object in the callback.

Profile selection uses this pattern:

```javascript
function openProfilePopup(onSelect) {
    var popup = exports.getShellComponent("app_popup");
    if (!popup || !popup.open) {
        setError("Profile popup is not loaded.");
        return;
    }
    popup.open("profileapp", "frmprofile-list-popup", {}, function (profile, instance) {
        var selected = normalizeProfile(profile);
        if (selected && selected.id) {
            onSelect(selected);
        }
        if (instance && instance.close) {
            instance.close();
        }
    }, "Select Profile", true, true);
}
```

Reusable selector components should keep their return object small and predictable:

```javascript
function select(profile) {
    exports.Complete(profile);
}
```

Product selection is available from:

```text
productapp-v2 / frmproduct-list-popup
```

The current user must have access to `productapp-v2` through the tenant group files, because `app_popup` downloads the source app component under the active user's permissions.

Use it from another app like this:

```javascript
function openProductPopup(onSelect) {
    var popup = exports.getShellComponent("app_popup");
    if (!popup || !popup.open) {
        setError("Product popup is not loaded.");
        return;
    }
    popup.open("productapp-v2", "frmproduct-list-popup", {}, function (product, instance) {
        var selected = normalizeProduct(product);
        if (selected && selected.product_id) {
            onSelect(selected);
        }
        if (instance && instance.close) {
            instance.close();
        }
    }, "Select Product", true, true);
}

function normalizeProduct(product) {
    if (product && product.product_id) {
        return product;
    }
    if (product && product.itemid) {
        return {
            product_id: product.itemid,
            product_code: String(product.itemid),
            product_title: product.name || "",
            product_price: product.price || 0,
            product_currency_code: product.currencycode || "",
            category: product.catogory || "",
            uom: product.uom || "",
            invType: product.invType || ""
        };
    }
    return null;
}
```

You can pass initial lookup filters as the third argument:

```javascript
popup.open(
    "productapp-v2",
    "frmproduct-list-popup",
    {search: "math", invType: "Service"},
    function (product, instance) {
        var selected = normalizeProduct(product);
    },
    "Select Product",
    true,
    true
);
```

Course Manager uses this lookup from the subject list. Keep the mapping action in the feature component and delegate product selection to `productapp-v2`:

```javascript
function mapProductToSubject(subject) {
    if (!subject || !subject.id) {
        return;
    }
    openProductPopup(function (product) {
        var payload = clone(subject);
        applyProductToSubject(payload, product);
        clearMessages();
        api.services.SaveSubject(payload).then(function (response) {
            if (response.success) {
                setInfo("Product mapped to subject.");
                loadSubjects();
                bindData.subjectForm = clone(response.result || payload);
            } else {
                setError(response.result && response.result.message ? response.result.message : "Product mapping failed.");
            }
        }).error(function () {
            setError("Product mapping failed.");
        });
    });
}

function applyProductToSubject(subject, product) {
    subject.product_id = product.product_id || "";
    subject.product_code = product.product_code || product.product_id || "";
    subject.product_title = product.product_title || "";
    subject.product_price = product.product_price || "";
    subject.product_currency_code = product.product_currency_code || "";
}
```

When one app depends on another app's lookup component, record that dependency in the caller app descriptor:

```json
{
  "dependencies": {
    "apps": [
      "productapp-v2"
    ]
  }
}
```

Implementation details learned from `productapp-v2/frmproduct-list-popup`:

1. Load shared styles inside the popup component as a fallback, because a popup can be opened outside the source app's normal startup flow.
2. Return both native fields and stable cross-app aliases. For products, include `itemid`, `name`, `product_id`, `product_code`, `product_title`, `product_price`, and `product_currency_code`.
3. Keep the old data shape in caller-side `normalizeProduct()` so existing local product popups and the reusable product popup can both work.
4. If a descriptor or CSS changes, bump `description.version` on the source app and the caller app to avoid cached descriptors and styles.
5. The tenant `davvag-core/localhost` folder may be ignored by Git in this repo, so verify generated tenant app files on disk even when `git status` does not show them.

For new reusable lookup components:

1. Register the component in the source app's `app.json`.
2. Include `inputData` and `outputData` in `component.json`.
3. Use `exports.Complete(selectedObject)` when the user selects a row.
4. In the caller, close the popup instance after handling the selected object.
5. Normalize the returned object in the caller when older apps may return a different shape.

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

When a schema has stable foreign-key style references, maintain a `relations` block in the same JSON file. Keep relationship metadata in the schema source instead of scattering it across app docs or service comments. Use it for direct references like `course_id -> course_manager_course.id` or `student_id -> profile.id`. For polymorphic references like `entity_type` plus `entity_id`, document them in prose unless the target is fixed.

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

For a schema-backed list that needs comparison operators, multiple conditions, sorting, or pagination, pass an advanced query array:

```php
$query = [
    "conditions" => [
        ["column" => "status", "operator" => "=", "value" => "Active"],
        ["column" => "priority", "operator" => ">=", "value" => 3]
    ],
    "sorting" => [
        ["column" => "priority", "direction" => "DESC"],
        ["column" => "title", "direction" => "ASC"]
    ],
    "pageSize" => 100,
    "pageFrom" => 0
];

return SOSSData::Query("my_new_app_items", $query);
```

Use `column`, not the legacy misspelling `coloumn`. The MySQL adapter joins conditions with `AND`, validates condition and sort columns against the schema, restricts operators and directions, applies normal view-object filtering, and reports the total match count before pagination. `pageFrom` is a row offset.

See [13-advanced-queries.md](13-advanced-queries.md) for the full payload and supported operators. This payload is currently specific to the `phpmysql` adapter; confirm support before using it with another datastore adapter.

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

## Raw Queries and Reporting Joins

Use `SOSSData::ExecuteRaw()` for read-only advanced data shapes that do not fit simple `SOSSData::Query()` filters:

- joined report views;
- totals and grouped summaries;
- custom search ranking;
- subqueries;
- stored procedure calls.

Define the raw query in a normal schema file:

```text
{TENANT_RESOURCE_LOCATION}/schemas/{namespace}.json
```

Example:

```json
{
  "rawquery": {
    "type": "sql",
    "parameters": ["startdate", "enddate", "page", "size"],
    "query": "SELECT DATE_FORMAT(oh.invoiceDate, '%Y-%m') AS reportMonth, p.id AS profileId, p.name AS profileName, SUM(od.qty) AS qty, SUM(od.total) AS total FROM orderheader oh INNER JOIN orderdetails od ON oh.invoiceNo = od.invoiceNo INNER JOIN profile p ON oh.profileId = p.id WHERE oh.invoiceDate BETWEEN '$startdate' AND '$enddate' GROUP BY DATE_FORMAT(oh.invoiceDate, '%Y-%m'), p.id, p.name ORDER BY reportMonth DESC LIMIT $page,$size"
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

Expose it through a service method:

```php
public function postSalesReport($req, $res) {
    $data = $req->Body(true);

    $params = new \stdClass();
    $params->parameters = new \stdClass();
    $params->parameters->page = isset($data->page) ? max(0, (int)$data->page) : 0;
    $params->parameters->size = isset($data->size) ? min(100, max(1, (int)$data->size)) : 25;
    $params->parameters->startdate = isset($data->startdate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data->startdate) ? $data->startdate : date("Y-m-01");
    $params->parameters->enddate = isset($data->enddate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data->enddate) ? $data->enddate : date("Y-m-d");

    return SOSSData::ExecuteRaw("my_new_app_sales_report", $params);
}
```

Then register the service handler method:

```json
{
  "SalesReport": {
    "method": "POST"
  }
}
```

The current codebase uses the same parameter object shape in:

| File | Raw namespace |
| --- | --- |
| `apps/com_qti_students/services/productsvr/service.php` | `profiles_search` |
| `apps/davvag-cms/shell/auth-handler/service.php` | `davvag_launchers_query` |
| `apps/davvag-cms/shell/auth-handler/service.php` | `davvag_launchers_subquery` |

Runtime behavior:

1. `SOSSData::ExecuteRaw($namespace, $params)` resolves the tenant adapter.
2. The MySQL adapter loads `schemas/{namespace}.json`.
3. It reads `rawquery.query`.
4. It replaces `$placeholder` values from `$params->parameters`.
5. It executes the SQL and maps returned columns through `fields`.

For joined results, avoid `SELECT *` in new report schemas. Use explicit aliases so the result object is predictable:

```sql
SELECT
  p.id AS profileId,
  p.name AS profileName,
  s.outstanding AS outstanding
FROM profile p
INNER JOIN profilestatus s ON p.id = s.profileid
```

Every selected alias must have a matching `fields[].fieldName`. If a field is listed but not returned by the SQL, the adapter returns a placeholder message for that field.

Stored procedure pattern:

```json
{
  "rawquery": {
    "type": "procedure",
    "parameters": ["page", "size"],
    "query": "call my_report_proc($page,$size);"
  },
  "fields": []
}
```

Place procedure setup SQL at:

```text
{TENANT_RESOURCE_LOCATION}/schemas/mysqlquery/{namespace}.sql
```

The adapter executes that setup script only when the procedure call fails with MySQL missing procedure error `1305`, then retries the raw query.

Raw query safety:

- Cast page, size, IDs, and other numeric values before passing them to `ExecuteRaw()`.
- Validate dates with a strict format such as `YYYY-MM-DD`.
- Whitelist statuses, types, and sort modes.
- Do not pass SQL snippets, column names, order clauses, or raw where clauses from the browser.
- Raw queries do not automatically apply `sysviewobject` filtering, so include permission filters in the SQL or keep the endpoint restricted to trusted groups.

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

## Calling Saved AI Agents

Use `ai-agent-creator` as the shared agent runtime for app-specific AI behavior. Do not copy provider calls, session storage, or skill execution into each app service.

Preferred server-side pattern:

```php
<?php
namespace my_new_app;

require_once(TENANT_RESOURCE_LOCATION . "/apps/ai-agent-creator/services/creator-api/service.php");

class ApiService {
    private function askSavedAgent($agentCode, $message, $profileId, $conversationKey, $context = array(), $payload = array()) {
        $creator = new \ai_agent_creator\CreatorService();
        return $creator->interactWithAgent(array(
            "agentCode" => $agentCode,
            "message" => $message,
            "appCode" => "my-new-app",
            "appName" => "My New App",
            "profile" => array(
                "profileId" => $profileId
            ),
            "conversationKey" => $conversationKey,
            "context" => $context,
            "payload" => $payload
        ));
    }
}
?>
```

The returned object uses this stable shape:

```php
$agentRun = $this->askSavedAgent("support-agent", $message, $profileId, $ticketId, array(
    "ticketId" => $ticketId
), $ticket);

if (!$agentRun->success) {
    return $agentRun;
}

$reply = $agentRun->response; // Same text as $agentRun->reply.
$session = $agentRun->session;
$skillResults = $agentRun->skillResults;
```

The same method is exposed as a DAVVAG service endpoint:

```text
POST /components/ai-agent-creator/creator-api/service/InteractWithAgent
```

Payload fields:

| Field | Required | Purpose |
| --- | --- | --- |
| `agentCode` | Yes | Saved agent code from `ai-agent-creator`. |
| `message` | Yes | User/app prompt sent to the agent. `prompt` and `question` are accepted aliases. |
| `appCode` | Recommended | Calling app code; used for runtime trace and generated session IDs. |
| `appName` | Optional | Human-readable calling app name. |
| `profile.profileId` or `profileId` | Recommended | Stable user/customer/profile key for session continuity. |
| `conversationKey` or `conversationId` | Recommended | Stable business object key, such as task ID, ticket ID, order ID, or chat thread ID. |
| `context` | Optional | App context shown to the agent runtime, such as selected entity IDs or status. |
| `payload` | Optional | Additional app data the agent can inspect through runtime context and configured skills. |
| `sessionId` | Optional | Explicit session ID. If omitted, the runtime derives one from app, agent, profile, and conversation key. |

Caller app descriptors should include:

```json
"dependencies": {
  "apps": ["ai-agent-creator"],
  "schemas": [],
  "workflows": [],
  "plugins": [],
  "php-extensions": ["curl"]
}
```

Use one stable `conversationKey` per real conversation. For example, use a task ID for a task assistant, an order ID for an order assistant, or a customer channel thread ID for support chat. That keeps agent memory scoped to the correct app workflow instead of mixing unrelated messages.

## Current Profile and Audit Stamping

When an app records user activity, stamp the current profile on the server. Do not trust the browser to send profile identity for work logs, comments, approvals, or audit records.

Recommended helper pattern:

```php
private function currentProfile() {
    $out = new stdClass();
    $out->id = 0;
    $out->name = "Unknown";

    if (class_exists("Profile")) {
        $profile = Profile::getUserProfile();
        if (isset($profile->profile) && isset($profile->profile->id)) {
            $out->id = $profile->profile->id;
            $out->name = isset($profile->profile->name) ? $profile->profile->name : "Unknown";
            return $out;
        }
    }

    $user = Auth::Autendicate();
    if (isset($user->userid)) {
        $profileResult = SOSSData::Query("profile", "linkeduserid:" . $user->userid);
        if ($profileResult->success && count($profileResult->result) > 0) {
            $out->id = $profileResult->result[0]->id;
            $out->name = isset($profileResult->result[0]->name) ? $profileResult->result[0]->name : "Unknown";
            return $out;
        }
        $out->name = isset($user->email) ? $user->email : "Unknown";
    }
    return $out;
}
```

Then apply it in service handlers:

```php
$profile = $this->currentProfile();
$log->profileId = $profile->id;
$log->profileName = $profile->name;
```

This is useful for work logs, task comments, progress timelines, discussions, and notifications.

## Related Data and Cleanup

Use separate namespaces for related data when a feature grows beyond one table. Example:

```text
task_manager_tasks
task_manager_task_assignees
task_manager_task_attachments
task_manager_work_logs
task_manager_task_comments
task_manager_comment_attachments
task_manager_notifications
```

When deleting a parent entity, clean up child namespaces explicitly:

```php
$this->deleteByQuery($this->assigneeNamespace, "taskId:" . $task->taskId);
$this->deleteByQuery($this->attachmentNamespace, "taskId:" . $task->taskId);
$this->deleteByQuery($this->workLogNamespace, "taskId:" . $task->taskId);
$this->deleteByQuery($this->commentNamespace, "taskId:" . $task->taskId);
$this->deleteByQuery($this->commentAttachmentNamespace, "taskId:" . $task->taskId);
```

For thread-like data, keep the model simple unless the product needs deep nesting. A practical comment model is:

```text
commentId
taskId
parentCommentId
profileId
profileName
body
commentDate
status
```

Return root comments with one-level `replies` arrays and an `Attachments` array on every comment or reply. This keeps Vue templates simple and avoids expensive client-side joins.

## Permission Layers

Most non-trivial DAVVAG apps need more than one permission layer.

View-object permissions:

```javascript
openViewObject(target.sysviewobject, function (data, shellpopup) {
    target.sysviewobject = data;
    api.services.SaveProject(target);
    shellpopup.close();
});
```

Do not rename `sysviewobject`; it is a framework system column used by view-object filtering.

Domain-specific access:

```text
task_manager_project_access
```

For example, a project app can store allowed profile IDs in a project-access namespace. Then services can filter project lists and task access by the current profile. Use the broad framework view-object permission for DAVVAG visibility and the app-specific access table for product rules.

Only add the app to `anonymous.json` when public access is intentional.

## Time and Progress Forms

For work logs, a good UI pattern is:

```text
work date: date input
start time: time input
end time: time input
minutes: calculated number
```

In the browser, keep UI-only fields separate:

```javascript
logForm: {
    logDate: "2026-06-10",
    startTime: "09:00",
    endTime: "10:30",
    startDate: "",
    endDate: "",
    durationInMinutes: 0
}
```

Before saving, combine date and time:

```javascript
log.startDate = log.logDate + "T" + log.startTime;
log.endDate = log.logDate + "T" + log.endTime;
delete log.startTime;
delete log.endTime;
```

Also calculate duration server-side if both full dates exist and duration is zero. That gives the UI convenience without making the database depend on browser-only fields.

## UI Layout Lessons

Keep app screens focused:

- Split large apps into route-level components instead of putting every workflow on one page.
- Use list/edit screens for parent entities, such as Projects.
- Use child list screens scoped by parent ID, such as Tasks under one Project.
- Use a dedicated detail screen for activity, comments, progress, and logs.
- Put secondary forms inside expandable sections when the primary job is reading existing activity.

For discussion UIs:

- Show existing comments before the new-comment form.
- Keep reply forms hidden until the user clicks `Reply to`.
- Hide reply forms again after save or cancel.
- Store profile name on the server so timelines remain readable even if profile data later changes.

## Notifications

For task-style workflows, queue notification rows in a namespace first:

```text
task_manager_notifications
```

Example:

```php
$notification->taskId = $body->taskId;
$notification->profileId = $assignee->profileId;
$notification->profileName = $assignee->profileName;
$notification->eventType = "Discussion";
$notification->message = "Task discussion updated";
$notification->status = "Queued";
$notification->createdate = date("Y-m-d H:i:s");
```

Actual email or IMAP/SMTP delivery can remain a later plugin integration. This keeps the app behavior stable even before mail delivery is implemented.

## Practical Checklist

Before you ship a new data-backed app:

1. Create a tenant schema file for every namespace the app uses.
2. Make sure the tenant config points at the correct datastore connector.
3. Confirm the adapter exists under `plugins/sossdata/{connector}/{connector}.php`.
4. Match the class name to the connector folder name.
5. Test insert, query, update, and delete from the app service.
6. Verify that view-object filtering behaves as expected for the target group.
7. Register the app in the right tenant group files.
8. Verify every component descriptor has the correct `mainScript`, `mainView`, and `css` resources.
9. Test route navigation with `soss-routes.appNavigate("../sibling?...")`.
10. If the app opens reusable lookup popups from another app, add that source app to `dependencies.apps` and confirm the current user group can access it.
11. Test in both docks if the app is tagged for both:

```text
http://localhost/git/davvag-core/#/app/{app-code}/{route}
http://localhost/git/davvag-core/admin#/app/{app-code}/{route}
```

12. Check component CSS URLs directly:

```text
components/{app-code}/{component-name}/file/{file-name}
```

13. Validate scripts and JSON before browser testing:

```powershell
node --check davvag-core\localhost\apps\{app-code}\components\{component}\script.js
Get-Content davvag-core\localhost\apps\{app-code}\app.json -Raw | ConvertFrom-Json | Out-Null
Get-ChildItem davvag-core\localhost\schemas\{app-prefix}_*.json | ForEach-Object { Get-Content $_.FullName -Raw | ConvertFrom-Json | Out-Null }
C:\xampp\php\php.exe -l davvag-core\localhost\apps\{app-code}\services\{service}\service.php
```

## Where To Read Next

- [05-database-schemas.md](05-database-schemas.md)
- [07-plugins.md](07-plugins.md)
- [10-ai-agent-playbook.md](10-ai-agent-playbook.md)

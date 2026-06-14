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
10. Test in both docks if the app is tagged for both:

```text
http://localhost/git/davvag-core/#/app/{app-code}/{route}
http://localhost/git/davvag-core/admin#/app/{app-code}/{route}
```

11. Check component CSS URLs directly:

```text
components/{app-code}/{component-name}/file/{file-name}
```

12. Validate scripts and JSON before browser testing:

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

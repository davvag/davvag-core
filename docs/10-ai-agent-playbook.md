# AI Agent Playbook

Use this file as the operational checklist for AI agents modifying or generating DAVVAG code.

## First Read

Before editing code:

```text
README.md
docs/01-framework-overview.md
docs/02-tenant-setup.md
docs/03-application-development.md
docs/04-components-and-services.md
```

For data work:

```text
docs/05-database-schemas.md
```

For workflow work:

```text
docs/06-workflows.md
```

For auth/deployment-sensitive work:

```text
docs/08-auth-sessions-permissions.md
docs/09-deployment.md
```

For profile, user, chat, CMS, profile-photo, and reusable app patterns:

```text
docs/12-reusable-app-patterns.md
```

## Resolve Active Tenant

1. Read root `configloader.php`.
2. Read root `config.json`.
3. Identify `RESOURCE_LOCATION`.
4. Identify `LOCAL_DEV_HOST` or expected `HTTP_HOST`.
5. Resolve:

```text
TENANT_RESOURCE_LOCATION = RESOURCE_LOCATION / HOST_NAME
```

Do not assume `davvag-core/localhost` is active unless config resolves to it.

## Create a New Tenant

Use:

```text
davvag-core/example.com/
```

Create:

```text
apps/
davvag-flow/
global/config/
global/templetes/app/
global/templetes/email/
plugins/
schemas/
tenant.json
config.json
anonymous.json
web_user.json
sysadmin.json
```

Then make root config resolve `HOST_NAME` to `example.com`.

## Create a New App

Checklist:

```text
[ ] apps/{appCode}/app.json
[ ] apps/{appCode}/app.php
[ ] apps/{appCode}/components/{component}/component.json
[ ] apps/{appCode}/components/{component}/script.js
[ ] apps/{appCode}/components/{component}/partial.html
[ ] apps/{appCode}/services/{service}/component.json
[ ] apps/{appCode}/services/{service}/script.js
[ ] apps/{appCode}/services/{service}/service.php
[ ] schemas/{namespace}.json
[ ] tenant.json includes appCode
[ ] sysadmin.json includes appCode
[ ] web_user.json or anonymous.json includes appCode if needed
```

## Service Handler Rules

Class in `component.json`:

```json
{
  "serviceHandler": {
    "file": "service.php",
    "class": "my_app\\ApiService"
  }
}
```

PHP must match:

```php
namespace my_app;

class ApiService {
}
```

Method naming:

```text
GET  List -> getList($req, $res)
POST Save -> postSave($req, $res)
```

Use:

```php
$req->Body(true)
$req->Query()
$res->SetError("message")
```

## Data Rules

Use `SOSSData`:

```php
SOSSData::Insert("namespace", $data);
SOSSData::Query("namespace", "field:value");
```

Create schemas first:

```text
schemas/namespace.json
```

Validate user input before building query strings.

## Workflow Rules

Put workflows under:

```text
davvag-flow/{namespace}/{flowid}.json
```

Call:

```php
DavvagFlow::Execute($namespace, $flowid, $inputData);
```

Prefer `service` nodes for app business logic.

## Test Routes

After generating an app:

```text
GET  /components/object/appdescriptor/{appCode}
GET  /components/{appCode}/{component}/object?object=desc
GET  /components/{appCode}/{component}/file/script.js
GET  /components/{appCode}/{component}/file/partial.html
POST /components/{appCode}/{service}/service/Save
GET  /components/{appCode}/{service}/service/List
```

For visibility:

```text
GET /components/object/apps
```

## Preserve Source Spellings

Existing source includes spellings that must be preserved:

```text
SOSSPlatform::intialize()
global/templetes/
excutionStack
scopData
Autendicate()
```

Do not "fix" these names unless intentionally doing a breaking refactor.

## Do Not Do

1. Do not expose `davvag-core/` directly over HTTP.
2. Do not put secrets in frontend JS or HTML.
3. Do not remove existing app entries from tenant/group JSON files.
4. Do not bypass auth checks casually.
5. Do not edit framework core for app-specific behavior.
6. Do not assume a tenant is active without checking config.
7. Do not add public write services without input validation.

## Preferred Pattern

When unsure, follow:

```text
davvag-core/localhost/apps/davvag-sample-app-1
```

Then adapt:

```text
app.json
apps/sample-input-form/component.json
service/app-handler/component.json
service/app-handler/service.php
schemas/*.json
davvag-flow/*.json
```

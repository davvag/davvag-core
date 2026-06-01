# Components and Services

Components are the core application units in DAVVAG. A component can provide UI resources, backend service handlers, transformers, or shell behavior.

## Component Resolution

Given:

```json
{
  "components": {
    "api": {
      "type": "service",
      "location": "services"
    }
  }
}
```

The framework resolves:

```text
{TENANT_RESOURCE_LOCATION}/apps/{appCode}/services/api/component.json
```

## Frontend Component Descriptor

```json
{
  "name": "main-view",
  "description": "Main UI component",
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

Resource types:

| Type | Purpose |
| --- | --- |
| `mainScript` | Main component JavaScript. |
| `mainView` | HTML fragment injected into the DOM. |
| `css` | Stylesheet. |
| `script` | Additional JavaScript dependency. |
| `tag` | Library marker to avoid duplicate load. |

## Service Component Descriptor

```json
{
  "name": "api",
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

`script.js` can be minimal:

```javascript
WEBDOCK.component().register(function (exports) {
});
```

## Service Handler PHP

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

## Handler Naming Rule

`ComponentManager::HandleService()` builds the PHP method name like this:

```text
strtolower(REQUEST_METHOD) + ucwords(handlerName)
```

Examples:

| Request | Method |
| --- | --- |
| `GET /components/my-new-app/api/service/List` | `getList($req, $res)` |
| `POST /components/my-new-app/api/service/Save` | `postSave($req, $res)` |
| `POST /components/my-new-app/api/service/deleteItem` | `postDeleteItem($req, $res)` |

If no method-specific handler exists, the framework tries:

```php
__handle($req, $res)
```

## Request Object

Service methods receive `$req`:

| Method | Purpose |
| --- | --- |
| `$req->Params()` | Route parameters such as `appCode`, `componentName`, `handlerName`. |
| `$req->Query()` | Query string as object. |
| `$req->Headers()` | HTTP headers as object. |
| `$req->Body(true)` | JSON-decoded request body. |
| `$req->Body(false)` | Raw request body. |

## Response Object

Service methods receive `$res`:

| Method | Purpose |
| --- | --- |
| `$res->Set($value)` | Set response output. |
| `$res->SetJSON($value, $success=true)` | Set framework JSON response shape. |
| `$res->SetError($error)` | Mark service as failed. |
| `$res->SetContentType($type)` | Set content type metadata. |

Usually, return a value directly:

```php
return ["message" => "Saved"];
```

The framework wraps it:

```json
{
  "success": true,
  "result": {
    "message": "Saved"
  }
}
```

## Declaring Dependencies

Service components can load plugins before the service class is loaded:

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

`plugin_location` values:

| Value | Base path |
| --- | --- |
| `global` | Root `PLUGIN_PATH`. |
| `local` | Tenant `PLUGIN_PATH_LOCAL`. |
| other/default | Explicit path. |

## Transformers

Transformers are declared in `component.json` and called through:

```text
/components/{appCode}/{componentName}/transform/{route}
```

Direct datastore transformer:

```json
{
  "transformers": {
    "Save": {
      "method": "POST",
      "route": "/Save",
      "destUrl": "SOSSData",
      "destMethod": "insert",
      "namespace": "my_new_app_items"
    }
  }
}
```

External REST transformer:

```json
{
  "transformers": {
    "CreateUser": {
      "method": "POST",
      "route": "/CreateUser",
      "destMethod": "POST",
      "destUrl": "http://localhost:9000/createuser/"
    }
  }
}
```

Use PHP service handlers for business logic. Use transformers for simple CRUD or REST forwarding.

## Common Component API Routes

```text
GET  /components/object/apps
GET  /components/object/appdescriptor/{appCode}
GET  /components/{appCode}/{componentName}/object?object=desc
GET  /components/{appCode}/{componentName}/file/{filePath}
GET  /components/{appCode}/{componentName}/service/{handlerName}
POST /components/{appCode}/{componentName}/service/{handlerName}
GET  /components/{appCode}/{componentName}/transform/{route}
POST /components/{appCode}/{componentName}/transform/{route}
```


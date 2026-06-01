# Workflows

DAVVAG workflow definitions live in tenant `davvag-flow/` folders and execute through the `DavvagFlow` plugin.

## Workflow Location

Root-level workflow:

```text
{TENANT_RESOURCE_LOCATION}/davvag-flow/{flowid}.json
```

Namespaced workflow:

```text
{TENANT_RESOURCE_LOCATION}/davvag-flow/{namespace}/{flowid}.json
```

Example:

```text
davvag-core/example.com/davvag-flow/my-new-app/create-item.json
```

## Execution

```php
require_once(PLUGIN_PATH_LOCAL . "/davvag-flow/flow.php");

$input = new \stdClass();
$input->title = "Test";
$input->status = "Active";

$result = \DavvagFlow::Execute("my-new-app", "create-item", $input);
```

Path resolution:

| Call | File |
| --- | --- |
| `DavvagFlow::Execute(null, "testflow", $input)` | `davvag-flow/testflow.json` |
| `DavvagFlow::Execute("my-new-app", "create-item", $input)` | `davvag-flow/my-new-app/create-item.json` |

## Basic Workflow Shape

```json
{
  "name": "Create Item Workflow",
  "start_up_node": "save-item",
  "inputData": [
    {
      "name": "title",
      "datatype": "string"
    }
  ],
  "save-item": {
    "urntype": "service",
    "appCode": "my-new-app",
    "componentCode": "api",
    "method": {
      "type": "post",
      "name": "Save",
      "params": [
        {
          "name": "postData",
          "type": "object",
          "value": "inputData"
        }
      ],
      "return": true,
      "returnobj": "savedItem"
    },
    "success": "build-result",
    "fail": "nodefail"
  }
}
```

## Node Types

Tenant-local `plugins/davvag-flow/flow.php` supports:

| `urntype` | Purpose |
| --- | --- |
| `service` | Load and call an app service component. |
| `class` | Load an activity class from `plugins/davvag-flow/lib`. |
| `create_object` | Build an object from constants or workflow data. |

## Service Node

```json
{
  "urntype": "service",
  "appCode": "my-new-app",
  "componentCode": "api",
  "method": {
    "type": "post",
    "name": "Save",
    "params": [
      {
        "name": "postData",
        "type": "object",
        "value": "inputData"
      }
    ],
    "return": true,
    "returnobj": "savedItem"
  },
  "success": "next-step",
  "fail": "nodefail"
}
```

This calls:

```text
apps/my-new-app/{api location}/api/service.php
postSave($req, $res)
```

## Class Node

```json
{
  "urntype": "class",
  "file": "test.php",
  "class": "test",
  "method": {
    "name": "fail",
    "params": [
      {
        "inputData": "title"
      }
    ],
    "return": true,
    "returnobj": "failed"
  }
}
```

Activity file path:

```text
PLUGIN_PATH/davvag-flow/lib/test.php
```

## Create Object Node

```json
{
  "urntype": "create_object",
  "method": {
    "type": "create_object",
    "name": "Result",
    "return": true,
    "returnobj": "result"
  },
  "variables": [
    {
      "name": "message",
      "value": "Item saved"
    },
    {
      "name": "item",
      "type": "object",
      "value": "scopData.outData.savedItem"
    }
  ],
  "fail": "nodefail"
}
```

Note: source code uses the spelling `scopData` in workflow parameter handling.

## Output Model

Workflow execution returns an object containing:

```text
excutionStack
outData
inputData
```

Note: source code uses the spelling `excutionStack`.

Returned node values are stored in:

```text
outData.{returnobj}
```

## Workflow Best Practices

1. Use `service` nodes for app business logic.
2. Use `class` nodes for reusable workflow activities.
3. Use `create_object` nodes for shaping output.
4. Always define `fail` for important nodes.
5. Keep workflow IDs and namespace folder names stable.
6. Avoid embedding secrets inside workflow JSON.


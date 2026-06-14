# Davvag Flow

Davvag Flow is the tenant workflow engine in `davvag-core/localhost/plugins/davvag-flow`. Workflow files are JSON documents stored in `davvag-core/localhost/davvag-flow`.

The plugin executes one node at a time, starting from `start_up_node`. Each node can pass control to another node through `success` or `fail`.

## Runtime Files

```text
davvag-core/localhost/plugins/davvag-flow/
  davvag-flow.php
  flow.php
  lib/
    facebook.php
    tags.php
    test.php
```

| File | Purpose |
| --- | --- |
| `davvag-flow.php` | Lists workflow JSON files for a namespace. |
| `flow.php` | Loads, executes, and routes workflow nodes. |
| `lib/*.php` | Custom workflow activity classes available to `class` nodes. |

## Workflow Location

Root workflows live directly under:

```text
davvag-core/localhost/davvag-flow/{flowid}.json
```

Namespaced workflows live under:

```text
davvag-core/localhost/davvag-flow/{namespace}/{flowid}.json
```

The current repository includes examples such as `english.json`, `idle.json`, `tamil.json`, and `davvag-attributes/testflow.json`.

## JSON Contract

```json
{
  "name": "Workflow -1",
  "start_up_node": "node1",
  "inputData": {
    "profileId": {
      "datatype": "int"
    }
  },
  "node1": {
    "urntype": "class",
    "file": "test.php",
    "class": "test",
    "method": {
      "name": "gohome",
      "params": [
        {
          "name": "id",
          "type": "object",
          "value": "inputData.id"
        }
      ],
      "return": true,
      "returnobj": "gohome"
    },
    "success": "node2",
    "fail": "nodefail"
  }
}
```

Top-level workflow fields:

| Field | Purpose |
| --- | --- |
| `name` | Display name for the workflow. |
| `start_up_node` | First node key executed when no explicit step is passed. |
| `inputData` | Optional description of expected workflow inputs. |
| `{nodeId}` | Node object keyed by the step id used in `success`, `fail`, and direct execution. |
| `__designer` | Optional canvas metadata used by the visual designer. The runtime ignores it unless referenced as a step. |

## Node Types

`flow.php` supports three node types.

### Class Node

Loads a PHP file from `plugins/davvag-flow/lib` and calls a class method.

```json
{
  "urntype": "class",
  "file": "tags.php",
  "class": "tags",
  "method": {
    "name": "addTag",
    "params": [
      {
        "name": "id",
        "type": "object",
        "value": "inputData.davvagid"
      },
      {
        "name": "tag",
        "value": "english"
      }
    ],
    "return": true,
    "returnobj": "tag"
  },
  "success": "next-step",
  "fail": "nodefail"
}
```

### Service Node

Loads a Davvag app service component and calls its handler method.

```json
{
  "urntype": "service",
  "appCode": "task-tracker",
  "componentCode": "taskapi",
  "method": {
    "type": "post",
    "name": "SaveTask",
    "params": [
      {
        "name": "postData",
        "type": "object",
        "value": "inputData"
      }
    ],
    "return": true,
    "returnobj": "savedTask"
  },
  "success": "next-step",
  "fail": "nodefail"
}
```

### Create Object Node

Builds and returns an object from constant values or values resolved from workflow data.

```json
{
  "urntype": "create_object",
  "method": {
    "type": "create_object",
    "name": "BuildResult",
    "return": true,
    "returnobj": "result"
  },
  "variables": [
    {
      "name": "message",
      "value": "Done"
    },
    {
      "name": "item",
      "type": "object",
      "value": "scopData.outData.savedTask"
    }
  ]
}
```

## Parameter Resolution

`method.params` can contain literal values or objects that point to runtime data.

| Shape | Meaning |
| --- | --- |
| `{ "name": "tag", "value": "english" }` | Pass the literal value `english`. |
| `{ "name": "id", "type": "object", "value": "inputData.id" }` | Resolve from workflow input. |
| `{ "name": "saved", "type": "object", "value": "scopData.outData.saved" }` | Resolve from workflow output data. |

The runtime source uses the spellings `scopData` and `excutionStack`; keep those names when referencing the current implementation.

## Execution Flow

1. `DavvagFlow::Execute($ns, $flowid, $inputData)` loads the workflow JSON.
2. If no step is supplied, the runtime uses `start_up_node`.
3. The node method is executed.
4. If `method.return` is true, the return value is stored in `outData.{returnobj}`.
5. On success, the runtime follows `success`.
6. On exception, the runtime follows `fail` when present, otherwise the exception is thrown.

## Designer Application

The `davvag-flow-designer` app is a Webdock application for creating and editing workflow JSON files.

Expected app structure:

```text
davvag-core/localhost/apps/davvag-flow-designer/
  app.json
  app.php
  components/workflow-designer/
  services/flow-designer-api/
```

The designer should:

1. List root and namespaced workflow JSON files.
2. Load a workflow without changing unknown node fields.
3. Drag toolbox templates onto a canvas.
4. Move nodes and save positions in `__designer.nodes`.
5. Edit node ids, methods, params, variables, and `success` or `fail` links.
6. Delete nodes and remove stale links.
7. Save valid JSON back to `davvag-flow`.

## Best Practices

1. Keep node ids stable after workflows are used by postbacks or scheduled jobs.
2. Use service nodes for app business logic.
3. Use class nodes for reusable workflow activity plugins.
4. Use `create_object` for output shaping.
5. Add `fail` links for user-facing or externally dependent actions.
6. Avoid storing secrets in workflow JSON.
7. Preserve old workflow fields when editing visually.

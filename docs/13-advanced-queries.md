# Advanced SOSSData Queries

Use an advanced query payload when a schema-backed list needs multiple conditions, explicit sorting, and pagination without defining raw SQL.

Call the public `SOSSData::Query()` facade. Do not call the MySQL connector's private `AdvancedQuery()` method directly.

```php
$query = [
    "conditions" => [
        [
            "column" => "status",
            "operator" => "=",
            "value" => "Active"
        ],
        [
            "column" => "priority",
            "operator" => ">=",
            "value" => 3
        ]
    ],
    "sorting" => [
        ["column" => "priority", "direction" => "DESC"],
        ["column" => "title", "direction" => "ASC"]
    ],
    "pageSize" => 100,
    "pageFrom" => 0
];

$result = SOSSData::Query("my_app_items", $query);
```

The MySQL adapter recognizes an advanced query when the second argument is:

- a PHP array;
- an object;
- a JSON object string; or
- a URL-encoded JSON object string.

This advanced payload is currently a `phpmysql` adapter feature. Verify equivalent behavior before relying on it with a different datastore adapter.

## Canonical Payload

Use these spellings in all new code:

```json
{
  "conditions": [
    {
      "column": "columnname",
      "operator": "=",
      "value": "the value"
    }
  ],
  "sorting": [
    {
      "column": "columnname",
      "direction": "ASC"
    }
  ],
  "pageSize": 100,
  "pageFrom": 0
}
```

Payload keys are matched case-insensitively, but the camel-case form above is the project convention.

## Conditions

Every condition must identify a field declared by the namespace schema or a framework system column. Conditions in the array are joined with `AND`.

| Property | Required | Meaning |
| --- | --- | --- |
| `column` | Yes | Schema or framework system column name. |
| `operator` | No | Comparison operator. Defaults to `=`. |
| `value` | Usually | Comparison value. Omit it for `IS NULL` and `IS NOT NULL`. |

Supported operators:

```text
=
==
!=
<>
>
>=
<
<=
LIKE
NOT LIKE
IN
NOT IN
IS NULL
IS NOT NULL
```

`IN` and `NOT IN` require a non-empty value array:

```php
[
    "column" => "status",
    "operator" => "IN",
    "value" => ["Active", "Pending"]
]
```

A `null` value with `=` becomes `IS NULL`. A `null` value with `!=` or `<>` becomes `IS NOT NULL`.

## Sorting

The recommended format is a list of sorting descriptors:

```php
"sorting" => [
    ["column" => "priority", "direction" => "DESC"],
    ["column" => "title", "direction" => "ASC"]
]
```

A simple column list is also supported:

```php
"sorting" => ["priority", "title"],
"direction" => "ASC"
```

When a simple list has no payload-level `direction`, it uses the `$sorting` argument passed to `SOSSData::Query()`. If `sorting` is omitted, the adapter orders by `sysversionid`.

Only `ASC` and `DESC` are accepted. Sort columns are checked against the schema before SQL is executed.

## Pagination

| Property | Rule |
| --- | --- |
| `pageSize` | Positive integer; maximum number of rows returned. |
| `pageFrom` | Non-negative integer row offset, not a page index. |

For example, `"pageSize": 100` and `"pageFrom": 200` return up to 100 rows beginning at offset 200.

The successful response includes:

```json
{
  "success": true,
  "result": [],
  "pageNumber": 200,
  "numberOfRecords": 450,
  "pageSize": 100
}
```

`numberOfRecords` is the total number of matching rows before pagination. For compatibility, `pageNumber` contains the `pageFrom` row offset.

## Version and View-Object Filtering

The existing `SOSSData::Query()` arguments continue to apply:

```php
SOSSData::Query(
    $namespace,
    $query,
    $lastVersionId,
    $sorting,
    $pageSize,
    $fromPage,
    $tenantId,
    $viewObject
);
```

- A non-zero `$lastVersionId` adds a `sysversionid` cursor condition based on the active sort direction.
- Normal `Auth::ViewObjects()` filtering remains enabled when `$viewObject` is `true`.
- Disable view-object filtering only in trusted and explicitly authorized code paths.

## Validation and Security

The adapter:

- rejects columns that are absent from the schema;
- accepts only the documented operators and sorting directions;
- converts values according to their schema data type;
- escapes string values before constructing SQL; and
- converts pagination values to validated integers.

Do not place SQL fragments in `column`, `operator`, `sorting`, or `value`. Pass data values only.

## Compatibility Aliases

Older payloads remain accepted, but new code should use the canonical contract.

| Legacy form | Canonical form |
| --- | --- |
| `Condition` or `condition` | `conditions` |
| condition property `condition` | `operator` |
| misspelled condition property `coloumn` | `column` |
| `pagesize` | `pageSize` |
| `pagefrom` | `pageFrom` |

## When to Use ExecuteRaw

Use `SOSSData::ExecuteRaw()` instead when the read model requires joins, aggregates, grouping, subqueries, stored procedure calls, or another controlled SQL shape that the advanced query payload cannot express.

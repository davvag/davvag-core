# SOSSData Query Firewall

`SOSSDataQueryFirewall` is the validation boundary for caller-controlled query data. It blocks unsafe query shapes before an adapter opens or executes SQL, while the MySQL connector applies schema allowlists and safe value handling.

The firewall is loaded automatically by:

```php
require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
```

Application code does not call the firewall directly. Continue using `SOSSData::Query()`, `ExecuteRaw()`, `Insert()`, `Update()`, and `Delete()`.

## Protection Layers

The SOSSData MySQL path now applies these layers:

1. Namespace identifiers must match `^[A-Za-z_][A-Za-z0-9_]*$`.
2. Query input must use a supported string, array, object, or `null` shape.
3. Sort direction is restricted to `ASC` or `DESC`.
4. Pagination and version values must be valid integers within their documented ranges.
5. Advanced-query columns must exist in the namespace schema or framework system columns.
6. Advanced operators and sort directions use explicit allowlists.
7. Legacy `field:value` values are converted through their schema data type and safely escaped using the active connection character set.
8. `ExecuteRaw()` placeholders are compiled to MySQLi prepared-statement markers and bound separately from SQL.
9. Write metadata such as `sysviewobject`, `syscreatedby`, and `syslastupdatedby` is cast or escaped before SQL construction.
10. Requests proxied through `davvagstore` use RFC 3986 query-string encoding.

The firewall does not use a blacklist of SQL words. Values such as names containing apostrophes remain valid data because values are separated from query structure or safely encoded.

## Firewall Limits

| Input | Limit |
| --- | --- |
| Query or raw-query template | 65,535 bytes |
| `pageSize` | 1 through 10,000 |
| Advanced conditions | 100 |
| Sort columns | 20 |
| Raw-query parameters | 100 |
| Individual raw string parameter | 1 MiB |

`pageFrom` and `lastVersionId` must be non-negative integers.

## Blocked Response

The public SOSSData facade fails closed and returns a standard object:

```json
{
  "success": false,
  "code": "SOSS_QUERY_FIREWALL_BLOCKED",
  "message": "SOSSData query firewall blocked the request: Invalid SOSSData namespace."
}
```

The block is also written to the PHP error log. Do not replace this response with raw database errors in a public service.

## Safe Standard Query

Legacy query syntax remains supported:

```php
$result = SOSSData::Query("users", "email:" . urlencode($email));
```

The namespace and query controls are validated at the SOSSData boundary. The MySQL connector then checks `email` against the schema and encodes the value according to the schema field type.

Prefer an advanced query array for new code because it keeps query structure separate from values:

```php
$result = SOSSData::Query("users", [
    "conditions" => [
        ["column" => "email", "operator" => "=", "value" => $email]
    ],
    "sorting" => [
        ["column" => "sysversionid", "direction" => "DESC"]
    ],
    "pageSize" => 20,
    "pageFrom" => 0
]);
```

## Safe ExecuteRaw Placeholders

Raw SQL structure must remain in a protected tenant schema file. Parameters are data values only.

Canonical schema form:

```json
{
  "rawquery": {
    "type": "sql",
    "parameters": ["startdate", "enddate", "page", "size"],
    "query": "SELECT id, title FROM orders WHERE created_at BETWEEN $startdate AND $enddate ORDER BY created_at DESC LIMIT $page,$size"
  },
  "fields": [
    {"fieldName": "id", "dataType": "int"},
    {"fieldName": "title", "dataType": "java.lang.String"}
  ]
}
```

The connector compiles it to the equivalent prepared statement:

```sql
SELECT id, title
FROM orders
WHERE created_at BETWEEN ? AND ?
ORDER BY created_at DESC
LIMIT ?,?
```

It then binds the four values separately. Legacy quoted placeholders such as `'$startdate'` are still recognized and migrated to a bound marker at runtime.

For `LIKE`, put wildcard characters in the parameter value:

```php
$params->parameters->search = "%" . $search . "%";
```

```sql
WHERE title LIKE $search
```

Legacy templates such as `LIKE '%$search%'` are supported; the compiler binds the combined wildcard value rather than inserting search text into SQL.

## Raw-Query Rules

- Declare every placeholder in `rawquery.parameters`.
- Supply every declared parameter exactly as a scalar value or `null`.
- Never accept SQL, column names, table names, `WHERE` fragments, or order expressions from a request.
- Keep dynamic identifiers out of placeholders; parameter binding is for data only.
- Keep raw-query schema files and companion MySQL scripts protected as server-owned configuration.
- Apply permission and `sysviewobject` rules explicitly because `ExecuteRaw()` does not add them automatically.

PHP recommends binding dynamic data through prepared statements and allowlisting query parts that cannot be bound. See the [PHP SQL injection guidance](https://www.php.net/manual/en/security.database.sql-injection.php) and [MySQLi prepared statement documentation](https://www.php.net/manual/en/mysqli.prepare.php).

## Scope

The firewall stops caller-controlled values from changing SOSSData SQL structure. It does not make a malicious tenant schema or companion `.sql` script safe. Those files are trusted server configuration and must remain writable only by authorized deployment processes.

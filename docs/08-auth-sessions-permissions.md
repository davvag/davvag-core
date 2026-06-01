# Auth, Sessions, and Permissions

DAVVAG uses PHP sessions, cookies, user groups, and access records to control app visibility and service access.

## Session Startup

Component API requests start a PHP session in:

```text
components/index.php
```

Pattern:

```php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
```

## Login Flow

1. App code calls `Auth::Login($username, $password)`.
2. `Auth` delegates to configured connector, currently often `phpauth`.
3. Connector validates credentials through `SOSSData`.
4. On success, auth data includes token, user id, email, group, and related session data.
5. `Auth::Login()` stores auth data in `$_SESSION["authData"]`.
6. `Auth::Login()` sets the `securityToken` cookie.

## Request Authentication

`components/index.php` calls:

```php
$user = Auth::Autendicate();
```

Behavior:

1. If `$_SESSION["authData"]` exists, return it.
2. Otherwise, if `$_COOKIE["securityToken"]` exists, call `Auth::GetSession(token)`.
3. Store restored session in `$_SESSION["authData"]`.
4. Define `GROUPID` from `$user->group`.
5. If no user exists, define `GROUPID` as `anonymous`.

## Cookies

Important cookies:

| Cookie | Purpose |
| --- | --- |
| `securityToken` | Main auth token used to restore a session. |
| `authData` | Auth data written by some app flows. |
| `sosskey` | Token forwarding cookie used in cross-domain/service flows. |
| `Location` | Location cookie used by some shop/cart flows. |

Production recommendation:

```text
HttpOnly
Secure
SameSite=Lax or SameSite=Strict
```

Apply these where compatible with existing frontend behavior.

## App Visibility

App visibility is group based.

Group files live at:

```text
{TENANT_RESOURCE_LOCATION}/{GROUPID}.json
```

Examples:

```text
anonymous.json
web_user.json
sysadmin.json
```

`ComponentManager::GetAllApps()` reads the current group file and returns app descriptions for installed apps.

## Service Access

Service access is checked in:

```text
components/common.php
components/component_manager.php
```

Pattern:

```php
checkAccess($res, $appCode, "service", $componentName, $handlerName)
```

This calls:

```php
Auth::GetAccess(GROUPID, $appCode, $type, $code, $operation)
```

## View Object Access

Data queries can be filtered by view objects.

`Auth::ViewObjects()` builds and caches:

```text
$_SESSION["viewObjects"]
$_SESSION["viewObjects_e"]
$_SESSION["viewObjects_f"]
```

The MySQL connector adds a `sysviewobject in (...)` filter unless disabled by caller.

## Logout Flow

1. App calls `Auth::GetLogout($token)`.
2. Auth connector deletes the server-side session.
3. `session_destroy()` is called.
4. Auth/session cache keys are unset.
5. `securityToken` cookie is expired.

## Security Caveats

| Area | Caveat |
| --- | --- |
| Cookie flags | Several code paths set cookies without explicit hardening flags. |
| CORS | Credentialed CORS with wildcard origin is invalid in browsers. |
| Virtual firewall | `VirtualFirewall::checkPermission()` returns `true` immediately in current source. |
| SQL | Some datastore SQL is string-built; validate input. |

## Development Rules

1. Add new apps to the correct group JSON files.
2. Do not add private/admin apps to `anonymous.json`.
3. Do not bypass `Auth::GetAccess()` without a deliberate framework-level decision.
4. Keep auth tokens server-side where possible.
5. Avoid exposing raw auth data to frontend code.


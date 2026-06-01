# Deployment

This guide covers Apache, cPanel, PHP, database, sessions, media, and CORS notes for DAVVAG.

## Apache Requirements

DAVVAG expects Apache rewrite support:

```text
mod_rewrite
AllowOverride All
```

Important `.htaccess` files:

```text
.htaccess
components/.htaccess
assets/.htaccess
files/.htaccess
davvag-core/.htaccess
davvag-core/{tenant}/plugins/.htaccess
```

## Root Rewrite Behavior

Root `.htaccess` handles admin routing and admin assets.

`components/.htaccess` routes component API requests to:

```text
components/index.php
```

`assets/.htaccess` maps public asset URLs to tenant app assets.

`davvag-core/.htaccess` denies direct access to tenant resources.

## PHP Requirements

Required or commonly used extensions/tools:

| Extension/tool | Purpose |
| --- | --- |
| `curl` | REST calls, auth forwarding, transformers. |
| `mysqli` | MySQL datastore connector. |
| `mbstring` | String handling. |
| `iconv` | Encoding conversion. |
| PHP sessions | Auth state and per-user cache/access state. |
| `zip` or shell zip/PowerShell | Backup utilities. |
| `openssl` | TLS mail/API clients. |

The install page checks Apache/PHP module availability:

```text
pages/install/index.php
```

## Config

Root config:

```text
config.json
```

Tenant config:

```text
davvag-core/{tenant}/config.json
```

Use `DAVVAGCONFIG` to point config loading outside the repo when needed:

```text
DAVVAGCONFIG=C:\path\to\private\config
```

## Database

`DB_CONFIG_FILE` should point to JSON shaped like:

```json
{
  "mysql_server": "localhost",
  "mysql_username": "user",
  "mysql_password": "password",
  "init_db": "prefix_"
}
```

The MySQL connector builds a database name using:

```text
{init_db}{DATASTORE_DOMAIN with dots replaced by underscores}
```

## Media and Cache

`MEDIA_FOLDER` is used for:

```text
cache/
backup/
tenant media/uploads
```

Ensure the PHP user can write to `MEDIA_FOLDER`.

## CORS

Framework JSON responses currently emit:

```text
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Allow-Credentials: true
```

Browser caveat:

```text
Access-Control-Allow-Origin: * is invalid with credentials.
```

If deploying cross-origin with cookies, echo a trusted origin instead of `*`.

There is no central `OPTIONS` preflight route in the current component router. Add one before Carbite dispatch if cross-origin APIs require preflight.

## cPanel Notes

No `.cpanel.yml` exists in this checkout by default.

If using cPanel Git deployment:

1. Add `.cpanel.yml`.
2. Preserve `.htaccess` files.
3. Keep config and secrets outside public direct access.
4. Make `MEDIA_FOLDER` writable.
5. Verify tenant folder names match production host names.
6. Run any required Composer install/build steps for plugin vendors if needed.

## Production Hardening

1. Use HTTPS.
2. Harden auth cookies with `HttpOnly`, `Secure`, and `SameSite`.
3. Avoid wildcard CORS for credentialed requests.
4. Protect `davvag-core/` from direct access.
5. Protect tenant `plugins/` from direct access.
6. Do not expose SMTP/provider secrets.
7. Review public service endpoints for SQL input validation.


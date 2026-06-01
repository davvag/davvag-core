# Tenant Setup

A DAVVAG tenant is a domain-specific resource folder under the framework's `davvag-core/` container.

Example:

```text
C:\xampp\htdocs\git\davvag-core\davvag-core\example.com
```

## When to Create a Tenant

Create a tenant when you need a separate domain, app registry, configuration, schemas, workflows, plugins, and user-group access model.

Examples:

```text
davvag-core/localhost
davvag-core/example.com
davvag-core/customer-a.com
```

## Minimum Tenant Structure

Create:

```text
davvag-core/example.com/
  apps/
  davvag-flow/
  global/
    config/
    templetes/
      app/
      email/
  plugins/
  schemas/
  tenant.json
  config.json
  anonymous.json
  web_user.json
  sysadmin.json
```

Folder roles:

| Path | Purpose |
| --- | --- |
| `apps/` | Applications for this tenant. |
| `davvag-flow/` | Workflow JSON files. |
| `global/config/` | Tenant-global config such as SMTP settings. |
| `global/templetes/` | Existing source spelling for app/email templates. |
| `plugins/` | Tenant-local PHP plugins. |
| `schemas/` | Database schema JSON and raw query files. |

## Configure Root Path Resolution

The root config controls tenant lookup.

Example root `config.json` variables:

```json
{
  "variables": {
    "RESOURCE_LOCATION": "C:\\xampp\\htdocs\\git\\davvag-core\\davvag-core",
    "LOCAL_DEV_HOST": "example.com"
  }
}
```

With that config:

```text
TENANT_RESOURCE_LOCATION = C:\xampp\htdocs\git\davvag-core\davvag-core\example.com
```

If `LOCAL_DEV_HOST` is not set, the framework uses `$_SERVER["HTTP_HOST"]`. In production, the tenant folder name should match the host name.

## Tenant Config

Create:

```text
davvag-core/example.com/config.json
```

Example:

```json
{
  "variables": {
    "AUTH_DOMAIN": "example.com",
    "DATASTORE_DOMAIN": "example.com",
    "APPURL": "https://example.com",
    "CURRENCY_CODE": "LKR"
  }
}
```

Do not place secrets in client-side app files. Keep provider secrets and SMTP credentials in server-side config.

## Tenant App Registry

Create:

```text
davvag-core/example.com/tenant.json
```

Minimum:

```json
{
  "apps": {
    "my-new-app": {
      "version": "latest"
    }
  },
  "webdock": {
    "events": {
      "onStartup": {
        "admin": "my-new-app",
        "default": "my-new-app"
      }
    }
  }
}
```

`onStartup.default` is used by `index.php`.

`onStartup.admin` is used by `admin.php`.

## Group Access Files

Group files control visible apps for each user group.

Common files:

```text
anonymous.json
web_user.json
sysadmin.json
```

Example `sysadmin.json`:

```json
{
  "apps": {
    "my-new-app": {
      "version": "latest"
    }
  },
  "webdock": {
    "events": {
      "onStartup": {
        "admin": "my-new-app",
        "default": "my-new-app"
      }
    }
  }
}
```

Only put public apps in `anonymous.json`.

## Tenant Plugins

Create:

```text
davvag-core/example.com/plugins/.htaccess
```

Recommended content:

```apache
<Files ~ "^.*">
  Deny from all
</Files>
```

This protects tenant-local plugin source from direct browser access.

## Tenant Startup Checklist

```text
[ ] Folder exists at davvag-core/{tenantDomain}.
[ ] Root config resolves HOST_NAME to the tenant folder.
[ ] tenant.json exists and has webdock.events.onStartup.
[ ] apps/{startupApp}/app.php exists.
[ ] apps/{startupApp}/app.json exists.
[ ] sysadmin.json includes the startup app.
[ ] anonymous.json and web_user.json include only intended apps.
[ ] schemas/ exists.
[ ] plugins/.htaccess denies direct access.
```


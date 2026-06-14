# Application Development

DAVVAG applications live inside a tenant's `apps/` folder.

Example:

```text
davvag-core/example.com/apps/my-new-app
```

## Recommended App Structure

```text
apps/my-new-app/
  app.json
  app.php
  components/
    main-view/
      component.json
      script.js
      partial.html
      main-view.css
  services/
    api/
      component.json
      script.js
      service.php
  assets/
```

The folder names under the app are flexible. The framework resolves components using `location` from `app.json`.

## Create `app.json`

```json
{
  "components": {
    "main-view": {
      "type": "component",
      "location": "components"
    },
    "api": {
      "type": "service",
      "location": "services"
    }
  },
  "description": {
    "title": "My New App",
    "author": "DAVVAG",
    "version": "0.1",
    "icon": "appicon.png"
  },
  "tags": ["showindock"],
  "configuration": {
    "webdock": {
      "startupComponent": "main-view",
      "onLoad": ["api"],
      "routes": {
        "partials": {
          "/": "main-view"
        }
      }
    }
  },
  "dependencies": {
    "apps": [],
    "schemas": [],
    "workflows": [],
    "plugins": [],
    "php-extensions": []
  }
}
```

Important fields:

| Field | Purpose |
| --- | --- |
| `components` | Registers all app components and services. |
| `type` | Usually `component` or `service`. |
| `location` | App subfolder where the component folder exists. |
| `description` | Metadata displayed by launchers and app lists. |
| `tags` | Discovery tags such as `showindock` or `showincms`. |
| `configuration.webdock.startupComponent` | Main UI component. |
| `configuration.webdock.onLoad` | Components loaded at startup, usually service/shell components. |
| `configuration.webdock.routes.partials` | Optional route-to-component map. |
| `dependencies` | Install/runtime dependencies required by the app. Keep arrays empty when none are required; do not use placeholder empty strings. |

Every app descriptor must include the dependency block before deployment:

```json
"dependencies": {
  "apps": [],
  "schemas": [],
  "workflows": [],
  "plugins": [],
  "php-extensions": []
}
```

Dependency fields:

| Field | Use |
| --- | --- |
| `apps` | Other DAVVAG apps or app services that this app calls directly, including apps referenced by bundled workflows. |
| `schemas` | Tenant schema namespaces read or written by this app, including dynamic attribute schemas and datasource schemas. |
| `workflows` | Workflow files used directly or triggered by the app's declared attribute schemas. Use namespaced paths when applicable, such as `davvag-attributes/testflow.json`. |
| `plugins` | Tenant/global plugins imported by service code or required by runtime helpers. |
| `php-extensions` | PHP extensions that must be installed on the server for app-specific code paths. |

## Register the App

Add the app to tenant `tenant.json`:

```json
{
  "my-new-app": {
    "version": "latest"
  }
}
```

Add the same entry to group files that should see the app:

```text
sysadmin.json
web_user.json
anonymous.json
```

Do not remove existing app entries.

## Create `app.php`

`app.php` is the launchable page for the app. Use existing apps as templates.

Minimum conceptual shape:

```html
<!doctype html>
<html>
<head>
  <title>My New App</title>
</head>
<body>
  <div webdock-component="main-view"></div>

  <script src="/lib/jquery.js"></script>
  <script src="/lib/webdock.js" webdockapp="my-new-app"></script>
</body>
</html>
```

If the app is loaded inside an existing shell such as `dock` or `davvag-cms`, follow the shell's existing `app.php` pattern.

## Create a UI Component

Create:

```text
apps/my-new-app/components/main-view/component.json
apps/my-new-app/components/main-view/script.js
apps/my-new-app/components/main-view/partial.html
apps/my-new-app/components/main-view/main-view.css
```

`component.json`:

```json
{
  "name": "main-view",
  "description": "Main UI component",
  "author": "DAVVAG",
  "version": "0.1",
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

`script.js`:

```javascript
WEBDOCK.component().register(function (exports) {
    var api;

    var state = {
        form: {},
        items: [],
        errors: []
    };

    exports.vue = {
        data: state,
        methods: {
            save: save
        }
    };

    exports.onReady = function () {
        api = exports.getComponent("api");
    };

    function save() {
        if (!api) {
            state.errors.push("API service is not loaded.");
            return;
        }

        api.services.Save(state.form).then(function (response) {
            if (response.success) {
                state.items.push(response.result);
                state.form = {};
            } else {
                state.errors.push("Save failed.");
            }
        }).error(function () {
            state.errors.push("Save request failed.");
        });
    }
});
```

`partial.html`:

```html
<section>
  <form v-on:submit.prevent="save">
    <input type="text" v-model="form.title" placeholder="Title">
    <button type="submit">Save</button>
  </form>

  <p v-for="error in errors">{{ error }}</p>

  <ul>
    <li v-for="item in items">{{ item.title }}</li>
  </ul>
</section>
```

## Test App Descriptor

```text
GET /components/object/appdescriptor/my-new-app
GET /components/my-new-app/main-view/object?object=desc
GET /components/my-new-app/main-view/file/script.js
GET /components/my-new-app/main-view/file/partial.html
```

## Common App Errors

| Error | Likely cause |
| --- | --- |
| App not visible | Missing from group JSON file. |
| Startup app not installed | Missing from `tenant.json > apps`. |
| Component descriptor not found | Wrong `location` or missing `component.json`. |
| Script not loading | Wrong resource `location` or invalid route. |

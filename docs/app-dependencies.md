# App Dependency Updates

DAVVAG app descriptors must declare install/runtime dependencies in `app.json`:

```json
"dependencies": {
  "apps": [],
  "schemas": [],
  "workflows": [],
  "plugins": [],
  "php-extensions": []
}
```

Keep arrays empty when no dependency exists. Do not use placeholder values such as `""`.

## Audit Notes

### Tenant-wide pass

Updated all 70 `davvag-core/localhost/apps/*/app.json` descriptors so each one has the top-level dependency block with the required keys.

Audit method:

- Preserved any existing top-level dependency values.
- Removed placeholder empty-string values.
- Detected app dependencies from `getAppComponent(...)`, app popup calls, and workflow `appCode` references.
- Detected schema dependencies from `SOSSData::*(...)` namespace literals and dynamic attribute schema usage.
- Detected workflow dependencies from dynamic attribute `postworkflow` declarations.
- Detected plugin dependencies from `PLUGIN_PATH` / `PLUGIN_PATH_LOCAL` imports and dynamic attribute shell usage.
- Detected PHP extension dependencies for common direct calls such as `curl_*`, `imap_*`, `mysqli_*`, and `ZipArchive`.

Validation after update:

- 70 app descriptors parsed as valid JSON.
- 70 app descriptors include `dependencies`.
- 0 dependency arrays contain blank placeholder values.
- 63 app descriptors have at least one detected dependency; the remaining 7 keep empty arrays.

### davvag-sample-app-1

Updated: `davvag-core/localhost/apps/davvag-sample-app-1/app.json`

Dependencies declared:

| Type | Values | Evidence |
| --- | --- | --- |
| `apps` | `stelup_shop` | `davvag-flow/davvag-attributes/testflow.json` calls `stelup_shop/seller_svr/Orders`. |
| `schemas` | `attr_lasitha_form`, `profile` | `apps/test-form/script.js` renders and saves `attr_lasitha_form`; that attribute schema uses `profile` as a datasource. |
| `workflows` | `davvag-attributes/testflow.json` | `schemas/attributes/attr_lasitha_form.json` declares postworkflow `testflow`. |
| `plugins` | `sossdata`, `davvag-attributes`, `davvag-flow` | `service/app-handler/service.php` imports `sossdata`; dynamic attribute save/render depends on `davvag-attributes`; the postworkflow depends on `davvag-flow`. |
| `php-extensions` | none | No app-specific PHP extension calls were found. |

Notes:

- `davvag-flow-designer` was removed from the app dependency list because it is a design-time tool, not a runtime dependency for this sample app.
- Shell components such as `soss-validator`, `attribute_shell`, `attribute_shell_popup`, and `app_popup` are provided by the active dock shell and are not listed as installable app dependencies for this app.

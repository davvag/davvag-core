# Reusable App Patterns for DAVVAG AI Agents

Use this guide when building or modifying DAVVAG tenant apps, especially apps that involve AI agents, profiles, users, chat, CMS listings, or profile photos.

These patterns were learned from working on:

```text
davvag-core/localhost/apps/ai-chatgpt-agent
davvag-core/localhost/apps/ai-agent-creator
davvag-core/localhost/apps/chat-agent
davvag-core/localhost/apps/davvag-cms-v7
davvag-core/localhost/apps/davvag-useradmin
davvag-core/localhost/apps/userapp
davvag-core/localhost/apps/davvag-tools
```

## High-Value Rules

1. Preserve framework spellings and conventions:

```text
catogory
templetes
scopData
```

2. App code should live under the active tenant:

```text
{TENANT_RESOURCE_LOCATION}/apps/{app-code}
```

3. App service classes must match the namespace and service handler descriptor.

4. When changing app descriptors, component descriptors, JS, or CSS, bump versions in:

```text
apps/{app}/app.json
apps/{app}/components/{component}/component.json
apps/{app}/services/{service}/component.json
```

5. Add dependencies to `app.json` whenever a service or component uses them. Do not rely on another app having loaded them by accident.

6. Use existing system data objects instead of inventing parallel identity models. In this framework, app-facing people and agents should usually be attached to `profile`.

## App Manifest Dependencies

If an app uses profiles, users, auth, cache, data, file uploads, or cURL, declare it.

Example:

```json
{
  "dependencies": {
    "apps": ["davvag-tools"],
    "schemas": ["profile", "profile_attributes", "users", "usergroups"],
    "workflows": [],
    "plugins": ["auth", "phpcache", "sossdata"],
    "php-extensions": ["curl"]
  }
}
```

Common dependencies:

```text
profile              profile records
profile_attributes   profile notes/details
users                auth user records
usergroups           user group records
domain_permision     user/group permissions
auth                 Auth::SaveUser, Auth::Join, session lookups
sossdata             SOSSData::Query/Insert/Update/Delete
phpcache             CacheData::clearObjects
davvag-tools         image cropper and file uploader components
curl                 outbound HTTP calls
```

## Service Component Pattern

Descriptor:

```json
{
  "serviceHandler": {
    "file": "service.php",
    "class": "my_app\\ApiService",
    "methods": {
      "Save": {"method": "POST"},
      "List": {"method": "GET"}
    }
  }
}
```

PHP:

```php
<?php
namespace my_app;

if (defined("PLUGIN_PATH")) {
    if (file_exists(PLUGIN_PATH . "/sossdata/SOSSData.php")) {
        require_once(PLUGIN_PATH . "/sossdata/SOSSData.php");
    }
    if (file_exists(PLUGIN_PATH . "/phpcache/cache.php")) {
        require_once(PLUGIN_PATH . "/phpcache/cache.php");
    }
    if (file_exists(PLUGIN_PATH . "/auth/auth.php")) {
        require_once(PLUGIN_PATH . "/auth/auth.php");
    }
}

class ApiService {
    public function postSave($req, $res) {
        $body = $req->Body(true);
        return $this->ok();
    }

    private function ok() {
        $out = new \stdClass();
        $out->success = true;
        return $out;
    }

    private function fail($message) {
        $out = new \stdClass();
        $out->success = false;
        $out->message = $message;
        return $out;
    }
}
?>
```

Prefer returning `{success:true}` / `{success:false,message:"..."}` from app services when the frontend already expects `response.result`. Use `$res->SetError(...)` when existing app patterns require framework-level errors.

## Profile Pattern

Use `profile` for people, customers, agents, and app identities that need to participate in the broader DAVVAG system.

Important fields from `schemas/profile.json`:

```text
id
name
contactno
email
catogory
createdate
userid
linkeduserid
Status
mainprofileid
```

Create or update a profile with `SOSSData`:

```php
$profile = new \stdClass();
$profile->name = $name;
$profile->email = strtolower($email);
$profile->contactno = $phone;
$profile->catogory = "Customer";
$profile->createdate = date_format(new \DateTime(), "m-d-Y H:i:s");
$profile->Status = "Active";

$result = \SOSSData::Insert("profile", $profile, null);
if ($result->success && isset($result->result->generatedId)) {
    $profile->id = $result->result->generatedId;
}
```

Look up profiles by ID or email:

```php
\SOSSData::Query("profile", urlencode("id:" . intval($profileId)), null, "desc", 1, 0, null, false);
\SOSSData::Query("profile", urlencode("email:" . strtolower($email)), null, "desc", 1, 0, null, false);
```

When an app creates an AI agent profile, tag it:

```php
$profile->catogory = "AI Agent";
```

Do not overwrite a normal user/customer profile with agent data. If an email already belongs to a non-agent profile, fail and ask for a dedicated agent email.

## Auth User Pattern

Use `davvag-useradmin` as the reference for creating users:

```text
davvag-core/localhost/apps/davvag-useradmin/services/user-handler/service.php
```

Create a user:

```php
$user = new \stdClass();
$user->username = $email;
$user->email = $email;
$user->name = $name;
$user->password = $plainTextPassword;

$created = \Auth::SaveUser($user);
```

Join a group:

```php
\Auth::Join(HOST_NAME, $created->userid, "sysuser");
```

For AI agents that need system access:

```text
Every agent should have:
- one profile tagged catogory = "AI Agent"
- one mapped auth user
- the auth user joined to group sysuser
- the profile linked with userid and linkeduserid
- the generated password stored only in the server-side agent config file
```

Store the raw generated password only in server-owned files such as `agents.json`. Mask it before returning config to the frontend.

## Safe Auth Helper Pattern

There are multiple `Auth` implementations. Some allow `Auth::Autendicate()` with no parameters; some require parameters. Avoid fatal errors by reading session/cookie data first and using reflection or `GetSession` as a fallback.

Pattern:

```php
private function currentUser() {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    if (isset($_SESSION["authData"]) && is_object($_SESSION["authData"])) {
        return $_SESSION["authData"];
    }

    if (isset($_COOKIE["authData"])) {
        $user = json_decode($_COOKIE["authData"]);
        if (is_object($user)) {
            return $user;
        }
    }

    if (!class_exists("\\Auth")) {
        return null;
    }

    try {
        $method = new \ReflectionMethod("\\Auth", "Autendicate");
        if ($method->getNumberOfRequiredParameters() === 0) {
            $user = \Auth::Autendicate();
            if (is_object($user)) {
                return $user;
            }
        }
    } catch (\Throwable $th) {
    }

    if (isset($_COOKIE["securityToken"]) && method_exists("\\Auth", "GetSession")) {
        try {
            $user = \Auth::GetSession($_COOKIE["securityToken"]);
            if (is_object($user)) {
                return $user;
            }
        } catch (\Throwable $th) {
        }
    }

    return null;
}
```

## Profile Photo Upload Pattern

The existing user profile photo upload flow lives in:

```text
davvag-core/localhost/apps/userapp/components/frmprofile-view/script.js
davvag-core/localhost/apps/davvag-tools/services/davvag-img-cropper/script.js
davvag-core/localhost/apps/davvag-tools/services/davvag-file-uploader/script.js
```

Frontend crop and upload:

```js
exports.getAppComponent("davvag-tools", "davvag-img-cropper", function(cropper) {
    cropper.initialize(300, 300);
    cropper.crop(1, 1, function(result) {
        if (!result || !result.fileData) {
            return;
        }
        var file = result.fileData;
        file.name = profileId;

        exports.getAppComponent("davvag-tools", "davvag-file-uploader", function(uploader) {
            uploader.initialize();
            uploader.upload([file], "profile", null, function() {
                // uploaded
            });
        });
    });
});
```

Profile image URL:

```text
components/dock/soss-uploader/service/get/profile/{profileId}
```

Use this URL in profile cards, chat windows, agent consoles, and profile summaries. Add an image-error fallback to initials because not every profile has a photo.

## DAVVAG Tools Reusable Components

The shared tools app lives at:

```text
davvag-core/localhost/apps/davvag-tools
```

Use it as an app dependency when another app needs shared upload, crop, capture, embedded-launcher, or record-permission behavior:

```json
{
  "dependencies": {
    "apps": ["davvag-tools"]
  }
}
```

Stable component contracts:

```text
davvag-img-cropper
  initialize(width, height)
  crop(width, height, callback, optionalDataUrl)
  crope(width, height, callback, optionalDataUrl)  legacy alias
  callback result: { name, data, fileData }

davvag-file-uploader
  initialize()
  upload(filesOrFile, className, idPrefix, callback)
  upload_uncompressed(filesOrFile, className, idPrefix, callback)
  marks each file with status/result/error before callback

davvag-app-downloader
  launchApp(launcherInfo, onLoaded, onError, onComplete, data)
  RenderHTML(jqueryElement, onLoaded, onError, onComplete, data)
  downloadAPP(appId, componentId, elementId, onLoaded, onError, onComplete, data)

viewObjectAPI
  Save [POST]
  FindObject [GET]
  PermisionValues / PermissionValues [GET]
  UserVieObjects / UserViewObjects [GET]
```

Keep the misspelled method names in old callers, but prefer the correctly spelled aliases in new code.

## AI Agent Creator Pattern

Saved AI agents in `ai-agent-creator` should include identity data in the server-side config.

Recommended config shape:

```json
{
  "agent": {
    "code": "support-agent",
    "name": "Support Agent",
    "profileId": 4,
    "profileImage": "components/dock/soss-uploader/service/get/profile/4",
    "profile": {
      "profileId": 4,
      "name": "Support Agent",
      "email": "support-agent@example.com",
      "phone": "+94710000000",
      "catogory": "AI Agent"
    },
    "userId": "abc123",
    "userGroup": "sysuser",
    "user": {
      "userid": "abc123",
      "username": "support-agent@example.com",
      "email": "support-agent@example.com",
      "groupid": "sysuser",
      "password": "server-generated-password"
    }
  },
  "systemUser": {
    "userid": "abc123",
    "groupid": "sysuser",
    "password": "server-generated-password"
  }
}
```

Frontend responses must mask:

```text
password
apiKey
token
secret
authorization
authHeader
```

Block old saved agents from running if they do not have a mapped profile and `sysuser`.

## Simple AI Agent Pattern

For smaller apps such as a single ChatGPT tenant agent:

```text
- Store provider/API config in a tenant-local JSON file.
- Require profile name, email, and phone on save.
- Create or update a profile tagged "AI Agent".
- Save profileId next to the API config.
- Block chat until profileId resolves to a real profile.
- Upload profile photo after save, once profileId exists.
```

Do not create an auth user unless the app needs the agent to act as a system user. `ai-agent-creator` needs a `sysuser`; a simple tenant chat config may only need a profile.

## Chat Agent Identity Pattern

For visitor chat:

```text
Logged-in user:
- use the logged-in user's registered profile
- do not show guest registration form
- ignore stale guest profileId from localStorage/request payload

Guest:
- show registration form
- require name, email, and phone
- create/resolve a Customer profile
- store guest form fields in localStorage for convenience
```

When rendering chat identity:

```text
- show profile photo from components/dock/soss-uploader/service/get/profile/{profileId}
- show initials if the image is missing
- show profile name near the chat composer/thread
```

For message threads, scroll after both local sends and service/poll responses:

```js
function scrollThread() {
    if (vueInstance && typeof vueInstance.$nextTick === "function") {
        vueInstance.$nextTick(focusLatestMessage);
    }
    window.setTimeout(focusLatestMessage, 0);
    window.setTimeout(focusLatestMessage, 80);
    window.setTimeout(focusLatestMessage, 240);
}
```

## CMS App Listing Pattern

When CMS needs to show only allowed apps, use the tagged object endpoint:

```text
components/object/apps?tags=showincms
```

If the app loader throws errors such as:

```text
Componet Not Registered [chat-agent/api]
```

check whether startup/onLoad dependencies are being preloaded before route components try to use them.

For apps that require service components on page load, include them in:

```json
{
  "configuration": {
    "webdock": {
      "onLoad": ["api", "other-service"]
    }
  }
}
```

## Frontend Service Result Pattern

Many DAVVAG component service calls return:

```js
{
    success: true,
    result: {}
}
```

Normalize this in the frontend:

```js
function serviceResult(response) {
    if (!response || response.success !== true) {
        return {
            success: false,
            message: response && response.result && response.result.message
                ? response.result.message
                : "DAVVAG service call failed."
        };
    }

    return response.result || {
        success: false,
        message: "DAVVAG service returned an empty response."
    };
}
```

## Cache and Version Notes

The framework and browser can cache descriptors, JS, CSS, and datastore objects.

After code or descriptor changes:

```text
- bump app.json description.version
- bump changed component.json version
- clear relevant CacheData objects after SOSSData writes
```

Common cache clears:

```php
\CacheData::clearObjects("profile");
\CacheData::clearObjects("users");
\CacheData::clearObjects("usergroups");
\CacheData::clearObjects("sys_access");
\CacheData::clearObjects("domain_permision_e");
```

## Validation Checklist

Before handing off changes:

```text
[ ] PHP lint changed service files
[ ] node --check changed component JS files
[ ] ConvertFrom-Json changed app/component descriptors
[ ] confirm dependencies in app.json
[ ] confirm version bumps
[ ] confirm secrets are masked in frontend responses
[ ] confirm profile/user writes clear relevant caches
[ ] confirm guest and authenticated flows do not mix identities
```

Useful local commands:

```powershell
& "C:\xampp\php\php.exe" -l "davvag-core\localhost\apps\APP\services\SERVICE\service.php"
node --check "davvag-core\localhost\apps\APP\components\COMPONENT\script.js"
Get-Content -Raw "davvag-core\localhost\apps\APP\app.json" | ConvertFrom-Json | Out-Null
```

## Git Note

In this repository, tenant-local paths under:

```text
davvag-core/localhost/
```

may be ignored by git. `git status` can show only:

```text
!! davvag-core/localhost/
```

Do not assume no files changed just because ignored tenant files do not appear as normal tracked modifications.

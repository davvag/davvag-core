# Flutter DAVVAG Development Instructions

**Document role:** First Flutter hybrid launcher and integration guide  
**Framework:** DAVVAG PHP tenant-aware framework  
**Target tenant studied:** `davvag-core/localhost`  
**Created:** 2026-09-10  
**Requested filename spelling:** `flutter_davvag_developemnet_instructions.md`

---

# 1. Goal

Build a Flutter hybrid application framework launcher for DAVVAG that can:

1. Login through the existing `userapp` login service.
2. Preserve the DAVVAG PHP/auth session across native API calls and WebView pages.
3. Launch existing DAVVAG web apps inside a WebView.
4. Allow new screens to be written as native Flutter modules.
5. Use the DAVVAG app descriptor and group visibility model instead of hard-coded app lists.

The first Flutter deliverable should be a reusable package/module named something like:

```text
davvag_flutter_core
```

Then build one host app around it:

```text
davvag_launcher
```

---

# 2. DAVVAG Runtime Facts Confirmed From This Repository

The DAVVAG web entry points are:

```text
index.php      normal runtime
admin.php      admin runtime; defines IS_ADMIN_MODE and then loads index.php
components/    component, service, object and file API router
```

The component API starts PHP session handling in:

```text
components/index.php
```

The service URL pattern is:

```text
/components/{appCode}/{componentName}/service/{handlerName}
```

For `userapp` login, the service descriptor is:

```text
davvag-core/localhost/apps/userapp/services/login-handler/component.json
```

The PHP handler is:

```text
davvag-core/localhost/apps/userapp/services/login-handler/service.php
```

The handler class is:

```text
LoginService
```

DAVVAG maps service calls by HTTP method plus handler name:

```text
GET login        -> LoginService::getLogin($req)
GET LoginState   -> LoginService::getLoginState($req, $res)
GET Logout       -> LoginService::getLogout($req)
POST registerUser -> LoginService::postRegisterUser($req, $res)
```

The framework response envelope is normally:

```json
{
  "success": true,
  "result": {}
}
```

Important: for login, `success: true` can mean the PHP handler ran. The Flutter client must still check `result.token`. Failed login can return a `result` object without `token`, often with an error/message from the auth connector.

---

# 3. Authentication And Session Contract

The existing login flow is:

```text
Flutter/DAVVAG web code
        -> GET /components/userapp/login-handler/service/login
        -> LoginService::getLogin()
        -> Auth::Login(email, password)
        -> auth connector validates user
        -> PHP session receives $_SESSION["authData"]
        -> response sets securityToken cookie
        -> response body returns auth object with token/user/profile data
```

The important cookie is:

```text
securityToken
```

The browser/PHP session cookie should also be preserved when returned:

```text
PHPSESSID
```

Some DAVVAG frontend code may also use:

```text
authData
sosskey
Location
```

For the hybrid launcher, do not depend on JavaScript `localStorage.loginData` as the source of truth. The source of truth is the DAVVAG server session restored by the `securityToken` cookie.

When the WebView opens a DAVVAG route with the same cookie store, `components/index.php` calls:

```php
Auth::Autendicate()
```

That method restores `$_SESSION["authData"]` from `$_COOKIE["securityToken"]` when the PHP session is new or missing.

---

# 4. Required Flutter Libraries

Package snapshot checked on 2026-09-10 from pub.dev. Run `flutter pub add` again when starting a real project so you get current compatible versions.

Recommended first dependencies:

```yaml
dependencies:
  flutter:
    sdk: flutter
  dio: ^5.11.1
  dio_cookie_manager: ^3.5.0
  cookie_jar: ^4.0.9
  flutter_inappwebview: ^6.1.5
  flutter_secure_storage: ^11.0.0
  go_router: ^18.0.1
  flutter_riverpod: ^3.4.3
  path_provider: any
  path: any
```

Use these roles:

```text
dio                    HTTP client for DAVVAG service calls
dio_cookie_manager     Dio interceptor that stores Set-Cookie and sends Cookie
cookie_jar             Persistent cookie jar shared by the DAVVAG API client
flutter_inappwebview   WebView with explicit CookieManager support
flutter_secure_storage Secure storage for token/user snapshot and app config
go_router              Flutter-native navigation and deep-link handling
flutter_riverpod       App/session state and async data loading
path_provider/path     Cookie file storage path
```

Use `flutter_inappwebview` instead of the simpler WebView package for the first version because this launcher needs explicit cookie injection before opening DAVVAG web apps.

References:

```text
dio                    https://pub.dev/packages/dio
dio_cookie_manager     https://pub.dev/packages/dio_cookie_manager
cookie_jar             https://pub.dev/packages/cookie_jar
flutter_inappwebview   https://pub.dev/packages/flutter_inappwebview
flutter_secure_storage https://pub.dev/packages/flutter_secure_storage
go_router              https://pub.dev/packages/go_router
flutter_riverpod       https://pub.dev/packages/flutter_riverpod
```

---

# 5. Local Development URL Rules

Do not hard-code `localhost` blindly in a mobile app.

Use one configured DAVVAG base URL everywhere:

```dart
const davvagBaseUrl = 'http://10.0.2.2/davvag-core/';
```

Common local values:

```text
Android emulator -> http://10.0.2.2/davvag-core/
iOS simulator    -> http://localhost/davvag-core/
Windows desktop  -> http://localhost/davvag-core/
Real phone       -> http://{LAN-IP-or-dev-domain}/davvag-core/
Production       -> https://{domain}/
```

Important tenant-resolution rule:

DAVVAG resolves tenant resources from `HTTP_HOST` unless `LOCAL_DEV_HOST` is defined. If Android uses `10.0.2.2`, PHP may try to load:

```text
davvag-core/10.0.2.2
```

For emulator testing against the checked-in `localhost` tenant, configure the root `config.json` or environment so:

```text
LOCAL_DEV_HOST = localhost
```

Use HTTPS in production. For local HTTP only, Android may need cleartext traffic enabled and iOS may need a local App Transport Security exception.

---

# 6. DAVVAG API Endpoints Needed By The Launcher

Base helper:

```text
{baseUrl}/components/{appCode}/{componentName}/service/{handlerName}
```

Login:

```http
GET /components/userapp/login-handler/service/login?email={email}&password={password}&domain={host}
```

Session state:

```http
GET /components/userapp/login-handler/service/LoginState
```

Logout:

```http
GET /components/userapp/login-handler/service/Logout
```

Profile data:

```http
GET /components/userapp/login-handler/service/ProfileData
```

Register:

```http
POST /components/userapp/login-handler/service/registerUser
Content-Type: application/json
```

Visible app catalog for the current authenticated group:

```http
GET /components/object/apps
GET /components/object/apps?tags=showindock
GET /components/object/apps?tags=showincms
```

App descriptor:

```http
GET /components/object/appdescriptor/{appCode}
```

App icon:

```http
GET /components/object/appicon/{appCode}
```

The launcher should normally use:

```text
/components/object/apps?tags=showindock
```

or:

```text
/components/object/apps?tags=showincms
```

because `ComponentManager::GetAllApps()` reads the current `GROUPID` file such as `anonymous.json`, `web_user.json`, or `sysadmin.json`.

---

# 7. Web App Launch URLs

DAVVAG web apps are hash-routed:

```text
{baseUrl}#/app/{appCode}
{baseUrl}#/app/{appCode}/{subRoute}
```

Examples:

```text
http://10.0.2.2/davvag-core/#/app/userapp/profile
http://10.0.2.2/davvag-core/#/app/lesson-manager/learn
http://10.0.2.2/davvag-core/#/app/task-tracker/time-tracker
http://10.0.2.2/davvag-core/#/app/travel-destinations/map
```

Admin dock route:

```text
{baseUrl}admin.php#/app/{appCode}/{subRoute}
```

Only expose admin launching if the authenticated user/group is allowed by DAVVAG group files and service permissions.

---

# 8. First Flutter Architecture

Create these modules/classes first:

```text
lib/
  main.dart
  davvag/
    davvag_config.dart
    davvag_api_client.dart
    davvag_auth_repository.dart
    davvag_session_bridge.dart
    davvag_app_catalog.dart
    davvag_web_launcher.dart
    models/
      davvag_response.dart
      davvag_user_session.dart
      davvag_app_info.dart
  features/
    login/
    launcher/
    web_app/
```

Responsibilities:

```text
DavvagConfig          owns baseUrl, host/domain value, timeout and environment
DavvagApiClient       owns Dio, CookieJar and generic service/object calls
DavvagAuthRepository  owns login, login state, logout and secure token snapshot
DavvagSessionBridge   copies cookies between CookieJar and WebView CookieManager
DavvagAppCatalog      loads /components/object/apps and maps app descriptors
DavvagWebLauncher     builds #/app URLs and opens them in a WebView route
Native features       call DAVVAG services through DavvagApiClient
```

Keep native Flutter modules and DAVVAG WebView modules behind the same authenticated session.

---

# 9. Core Dart Models

```dart
class DavvagResponse<T> {
  DavvagResponse({
    required this.success,
    this.result,
    this.message,
    this.raw,
  });

  final bool success;
  final T? result;
  final String? message;
  final Map<String, dynamic>? raw;

  factory DavvagResponse.fromJson(
    Map<String, dynamic> json,
    T Function(Object? value) parseResult,
  ) {
    return DavvagResponse<T>(
      success: json['success'] == true,
      result: parseResult(json['result']),
      message: json['message']?.toString(),
      raw: json,
    );
  }
}
```

```dart
class DavvagUserSession {
  DavvagUserSession({
    required this.token,
    this.userId,
    this.email,
    this.group,
    this.profile,
    this.raw,
  });

  final String token;
  final String? userId;
  final String? email;
  final String? group;
  final Map<String, dynamic>? profile;
  final Map<String, dynamic>? raw;

  factory DavvagUserSession.fromJson(Map<String, dynamic> json) {
    return DavvagUserSession(
      token: json['token']?.toString() ?? '',
      userId: json['userid']?.toString(),
      email: json['email']?.toString(),
      group: json['group']?.toString(),
      profile: json['profile'] is Map<String, dynamic>
          ? json['profile'] as Map<String, dynamic>
          : null,
      raw: json,
    );
  }
}
```

---

# 10. DavvagConfig

```dart
class DavvagConfig {
  const DavvagConfig({
    required this.baseUrl,
    this.domain,
  });

  final Uri baseUrl;
  final String? domain;

  String get loginDomain => domain ?? baseUrl.host;

  Uri resolve(String path, [Map<String, dynamic>? query]) {
    final cleanBase = baseUrl.toString().endsWith('/')
        ? baseUrl.toString()
        : '${baseUrl.toString()}/';
    final cleanPath = path.startsWith('/') ? path.substring(1) : path;
    final uri = Uri.parse('$cleanBase$cleanPath');
    return uri.replace(
      queryParameters: query?.map((key, value) => MapEntry(key, value.toString())),
    );
  }

  Uri webAppUrl(String appCode, {String? subRoute, bool admin = false}) {
    final base = baseUrl.toString().endsWith('/')
        ? baseUrl.toString()
        : '${baseUrl.toString()}/';
    final cleanSubRoute = (subRoute ?? '').replaceFirst(RegExp(r'^/+'), '');
    final route = cleanSubRoute.isEmpty
        ? '#/app/$appCode'
        : '#/app/$appCode/$cleanSubRoute';
    return Uri.parse(admin ? '${base}admin.php$route' : '$base$route');
  }
}
```

---

# 11. DavvagApiClient

```dart
import 'package:cookie_jar/cookie_jar.dart';
import 'package:dio/dio.dart';
import 'package:dio_cookie_manager/dio_cookie_manager.dart' as dio_cookie;
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

class DavvagApiClient {
  DavvagApiClient._({
    required this.config,
    required this.dio,
    required this.cookieJar,
  });

  final DavvagConfig config;
  final Dio dio;
  final PersistCookieJar cookieJar;

  static Future<DavvagApiClient> create(DavvagConfig config) async {
    final dir = await getApplicationDocumentsDirectory();
    final jar = PersistCookieJar(
      ignoreExpires: false,
      storage: FileStorage(p.join(dir.path, 'davvag_cookies')),
    );

    final dio = Dio(BaseOptions(
      baseUrl: config.baseUrl.toString(),
      connectTimeout: const Duration(seconds: 20),
      receiveTimeout: const Duration(seconds: 30),
      headers: const {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));

    dio.interceptors.add(dio_cookie.CookieManager(jar));

    return DavvagApiClient._(
      config: config,
      dio: dio,
      cookieJar: jar,
    );
  }

  Future<Response<dynamic>> getService(
    String appCode,
    String componentName,
    String handlerName, {
    Map<String, dynamic>? query,
  }) {
    return dio.get<dynamic>(
      'components/$appCode/$componentName/service/$handlerName',
      queryParameters: query,
    );
  }

  Future<Response<dynamic>> postService(
    String appCode,
    String componentName,
    String handlerName, {
    Object? body,
  }) {
    return dio.post<dynamic>(
      'components/$appCode/$componentName/service/$handlerName',
      data: body ?? const <String, dynamic>{},
    );
  }

  Future<Response<dynamic>> getObject(
    String path, {
    Map<String, dynamic>? query,
  }) {
    return dio.get<dynamic>('components/object/$path', queryParameters: query);
  }
}
```

---

# 12. Auth Repository

```dart
import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class DavvagAuthRepository {
  DavvagAuthRepository({
    required this.api,
    FlutterSecureStorage? secureStorage,
  }) : secureStorage = secureStorage ?? const FlutterSecureStorage();

  final DavvagApiClient api;
  final FlutterSecureStorage secureStorage;

  static const _sessionKey = 'davvag.session';

  Future<DavvagUserSession> login({
    required String email,
    required String password,
  }) async {
    final response = await api.getService(
      'userapp',
      'login-handler',
      'login',
      query: {
        'email': email,
        'password': password,
        'domain': api.config.loginDomain,
      },
    );

    final data = Map<String, dynamic>.from(response.data as Map);
    final result = data['result'];
    if (result is! Map || result['token'] == null) {
      throw Exception(result is Map && result['message'] != null
          ? result['message'].toString()
          : 'DAVVAG login failed');
    }

    final session = DavvagUserSession.fromJson(
      Map<String, dynamic>.from(result),
    );
    await secureStorage.write(
      key: _sessionKey,
      value: jsonEncode(session.raw),
    );
    return session;
  }

  Future<DavvagUserSession?> loginState() async {
    final response = await api.getService(
      'userapp',
      'login-handler',
      'LoginState',
    );

    final data = Map<String, dynamic>.from(response.data as Map);
    final result = data['result'];
    if (result is Map && result['token'] != null) {
      return DavvagUserSession.fromJson(Map<String, dynamic>.from(result));
    }
    return null;
  }

  Future<void> logout() async {
    await api.getService('userapp', 'login-handler', 'Logout');
    await api.cookieJar.deleteAll();
    await secureStorage.delete(key: _sessionKey);
  }
}
```

Security note: the current DAVVAG login service uses `GET` query parameters for email and password because that is the existing `userapp` contract. For production mobile apps, add a compatible `POST Login` endpoint later so credentials are not placed in URLs, proxy logs, browser history, or server logs.

---

# 13. Cookie Bridge Between Dio And WebView

This is the most important part of the hybrid launcher.

After Flutter logs in through `Dio`, the response cookies are stored in `PersistCookieJar`. Before opening a DAVVAG WebView, copy those cookies into `flutter_inappwebview`'s `CookieManager`.

```dart
import 'dart:io';
import 'package:flutter_inappwebview/flutter_inappwebview.dart' as inapp;

class DavvagSessionBridge {
  DavvagSessionBridge(this.api);

  final DavvagApiClient api;

  Future<void> syncApiCookiesToWebView() async {
    final url = api.config.baseUrl;
    final cookies = await api.cookieJar.loadForRequest(url);
    final manager = inapp.CookieManager.instance();

    for (final Cookie cookie in cookies) {
      await manager.setCookie(
        url: inapp.WebUri(url.toString()),
        name: cookie.name,
        value: cookie.value,
        domain: cookie.domain,
        path: cookie.path ?? '/',
        expiresDate: cookie.expires?.millisecondsSinceEpoch,
        isSecure: cookie.secure,
        isHttpOnly: cookie.httpOnly,
      );
    }
  }

  Future<void> clearWebViewCookies() async {
    await inapp.CookieManager.instance().deleteAllCookies();
  }
}
```

Minimum cookie verification after login:

```dart
final cookies = await api.cookieJar.loadForRequest(api.config.baseUrl);
final hasSecurityToken = cookies.any((c) => c.name == 'securityToken');
```

Do not open a DAVVAG WebView route until `securityToken` is present or `LoginState` confirms the session.

---

# 14. Web App Screen

```dart
import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';

class DavvagWebAppScreen extends StatefulWidget {
  const DavvagWebAppScreen({
    super.key,
    required this.url,
    required this.sessionBridge,
  });

  final Uri url;
  final DavvagSessionBridge sessionBridge;

  @override
  State<DavvagWebAppScreen> createState() => _DavvagWebAppScreenState();
}

class _DavvagWebAppScreenState extends State<DavvagWebAppScreen> {
  bool ready = false;

  @override
  void initState() {
    super.initState();
    _prepare();
  }

  Future<void> _prepare() async {
    await widget.sessionBridge.syncApiCookiesToWebView();
    if (mounted) {
      setState(() => ready = true);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (!ready) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      body: SafeArea(
        child: InAppWebView(
          initialUrlRequest: URLRequest(url: WebUri(widget.url.toString())),
          initialSettings: InAppWebViewSettings(
            javaScriptEnabled: true,
            thirdPartyCookiesEnabled: true,
          ),
        ),
      ),
    );
  }
}
```

If a launched DAVVAG app redirects to `#/app/userapp/login`, cookie sync failed or the `securityToken` is expired.

---

# 15. App Catalog Loader

```dart
class DavvagAppInfo {
  DavvagAppInfo({
    required this.code,
    required this.title,
    this.version,
    this.icon,
    this.config,
  });

  final String code;
  final String title;
  final String? version;
  final String? icon;
  final Map<String, dynamic>? config;

  factory DavvagAppInfo.fromEntry(String code, Object? value) {
    final json = Map<String, dynamic>.from(value as Map);
    return DavvagAppInfo(
      code: code,
      title: json['title']?.toString() ?? code,
      version: json['version']?.toString(),
      icon: json['icon']?.toString(),
      config: json['config'] is Map<String, dynamic>
          ? json['config'] as Map<String, dynamic>
          : null,
    );
  }
}
```

```dart
class DavvagAppCatalog {
  DavvagAppCatalog(this.api);

  final DavvagApiClient api;

  Future<List<DavvagAppInfo>> loadDockApps({String tag = 'showindock'}) async {
    final response = await api.getObject(
      'apps',
      query: {'tags': tag},
    );

    final data = Map<String, dynamic>.from(response.data as Map);
    final result = data['result'];
    if (data['success'] != true || result is! Map) {
      throw Exception('Unable to load DAVVAG apps');
    }

    return result.entries
        .map((entry) => DavvagAppInfo.fromEntry(entry.key.toString(), entry.value))
        .toList();
  }
}
```

Use each app's descriptor `configuration.dock.subapps` to build sub-routes when available. If no subapps exist, launch:

```text
#/app/{appCode}
```

---

# 16. Native Flutter App Development Pattern

For each DAVVAG-backed native Flutter feature:

1. Reuse the existing DAVVAG app/service when possible.
2. Read the app's `app.json` and service `component.json`.
3. Call the service through `DavvagApiClient`.
4. Keep validation and authorization server-side in PHP services.
5. Let Flutter manage view state, input collection, offline drafts, and device UX.
6. Do not connect Flutter directly to MySQL.
7. Do not duplicate DAVVAG auth or permission rules in Flutter as the authority.
8. Do not store provider secrets or database credentials in Flutter.

Native Flutter screens should call services like:

```dart
await api.postService(
  'task-tracker',
  'taskapi',
  'WorkLogSummary',
  body: {
    'period': 'Weekly',
    'startDate': '2026-09-07',
    'endDate': '2026-09-13',
  },
);
```

The exact handler names come from the target service `component.json`.

---

# 17. Login And Launch Flow

First app startup:

```text
1. Create DavvagApiClient.
2. Load cookies from PersistCookieJar.
3. Call LoginState.
4. If LoginState returns token, show launcher.
5. Otherwise show native Flutter login screen.
```

Login:

```text
1. User enters email/password in Flutter.
2. Flutter calls userapp login service.
3. Dio receives Set-Cookie and stores PHPSESSID/securityToken.
4. Auth repository validates result.token.
5. Store non-authoritative session snapshot in secure storage.
6. Load /components/object/apps?tags=showindock.
7. Show native launcher grid/list.
```

Launch DAVVAG web app:

```text
1. Build URL: {baseUrl}#/app/{appCode}/{subRoute}
2. Sync Dio cookies to WebView CookieManager.
3. Open InAppWebView.
4. DAVVAG Webdock loads as if the user logged in on the web.
```

Logout:

```text
1. Call userapp Logout service.
2. Clear CookieJar.
3. Clear WebView cookies.
4. Clear secure storage session snapshot.
5. Return to Flutter login screen.
```

---

# 18. Security Requirements

For production:

1. Use HTTPS only.
2. Replace or supplement GET login with POST login.
3. Set cookie flags server-side where compatible: `HttpOnly`, `Secure`, `SameSite=Lax`.
4. Do not expose auth tokens in logs, crash reports, analytics, screenshots, URLs, or debug overlays.
5. Store only a minimal session snapshot in `flutter_secure_storage`.
6. Keep real credentials, provider secrets, OAuth keys, and DB config on the DAVVAG server.
7. Treat WebView content as authenticated DAVVAG web content, not as untrusted random browsing.
8. Restrict WebView navigation to the configured DAVVAG host unless explicitly opening an external browser.
9. Disable file access and dangerous WebView permissions unless an app specifically requires them.
10. Re-check server session with `LoginState` when the app resumes from background.

The current `components/common.php` sends wildcard CORS with credentials. Native Flutter is not blocked by browser CORS, and same-origin WebView DAVVAG pages work normally. Do not build a browser-based Flutter Web client around credentialed cross-origin calls until the CORS policy is corrected.

---

# 19. Platform Setup Notes

Android local HTTP:

```xml
<application
    android:usesCleartextTraffic="true">
</application>
```

Use cleartext only for local development. Production must use HTTPS.

Android emulator to XAMPP:

```text
Use http://10.0.2.2/davvag-core/
Set DAVVAG LOCAL_DEV_HOST to localhost if the tenant folder is davvag-core/localhost
```

iOS local HTTP:

```text
Prefer HTTPS. If using local HTTP during development, add an ATS exception only for the dev host.
```

WebView OAuth/social login:

The existing `FacebookLogin` and `GoogleLogin` handlers return provider URLs and callback to DAVVAG web endpoints. For the first Flutter version, prefer email/password login. Add social login later by opening the provider flow in WebView or an in-app browser and then calling `LoginState` after the DAVVAG callback completes.

---

# 20. Testing Checklist

API/session tests:

```text
[ ] Wrong email/password returns no token.
[ ] Correct login returns result.token.
[ ] CookieJar contains securityToken after login.
[ ] LoginState succeeds after app restart using persisted cookies.
[ ] Logout removes server session and local cookies.
[ ] Expired securityToken returns no LoginState result.
```

WebView session tests:

```text
[ ] After native login, opening #/app/userapp/profile does not show login.
[ ] Web app service calls still authenticate inside WebView.
[ ] Closing and reopening WebView preserves session.
[ ] Clearing cookies forces WebView back to login.
[ ] Android emulator works with LOCAL_DEV_HOST = localhost.
[ ] Real device works against a LAN/dev domain.
```

Launcher tests:

```text
[ ] Anonymous app list before login is limited.
[ ] Authenticated app list reflects the user's group file.
[ ] showindock and showincms filters behave correctly.
[ ] Apps with dock subapps open correct hash routes.
[ ] Admin routes are hidden unless group access allows them.
```

Security tests:

```text
[ ] No password appears in Flutter logs.
[ ] No token appears in Flutter logs.
[ ] WebView cannot navigate away from allowed DAVVAG host silently.
[ ] HTTPS production build has no cleartext exception.
```

---

# 21. Recommended First Implementation Milestone

Build milestone 1 in this exact order:

1. Create Flutter app `davvag_launcher`.
2. Add the dependencies listed in section 4.
3. Create `DavvagConfig`.
4. Create `DavvagApiClient` with persistent cookies.
5. Create `DavvagAuthRepository`.
6. Build a native login screen that calls `userapp/login-handler/login`.
7. Verify `securityToken` is stored in `CookieJar`.
8. Create `DavvagSessionBridge`.
9. Create `DavvagWebAppScreen`.
10. Open `#/app/userapp/profile` after native login.
11. Create `DavvagAppCatalog`.
12. Replace the fixed test launch button with the authenticated DAVVAG app list.
13. Add logout and cookie clearing.
14. Add one native Flutter demo feature that calls an existing DAVVAG service.

Do not start by rewriting DAVVAG Webdock in Flutter. The first value is a stable session bridge and launcher.

---

# 22. Files Studied For This Guide

```text
components/index.php
components/common.php
components/component_manager.php
components/carbite.php
components/virtual_firewall.php
lib/webdock.js
plugins/auth/auth.php
docs/08-auth-sessions-permissions.md
davvag-core/localhost/anonymous.json
davvag-core/localhost/web_user.json
davvag-core/localhost/sysadmin.json
davvag-core/localhost/apps/userapp/app.json
davvag-core/localhost/apps/userapp/services/login-handler/component.json
davvag-core/localhost/apps/userapp/services/login-handler/service.php
davvag-core/localhost/apps/userapp/components/login-form/script.js
davvag-core/localhost/apps/userapp/components/login-switcher/script.js
```


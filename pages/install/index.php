<?php
$frameworkRoot = realpath(dirname(__FILE__) . "/../..");
$frameworkRoot = $frameworkRoot ? $frameworkRoot : dirname(__FILE__) . "/../..";
$rootConfigPath = $frameworkRoot . DIRECTORY_SEPARATOR . "config.json";
$tenantContainerPath = $frameworkRoot . DIRECTORY_SEPARATOR . "davvag-core";

$rootConfig = null;
$configError = "";
if (file_exists($rootConfigPath)) {
    $rootConfig = json_decode(file_get_contents($rootConfigPath));
    if (!is_object($rootConfig)) {
        $configError = "config.json exists, but it is not valid JSON.";
    }
}

$variables = is_object($rootConfig) && isset($rootConfig->variables) ? $rootConfig->variables : new stdClass();
$detectedHost = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "localhost";
$hostName = isset($variables->LOCAL_DEV_HOST) ? $variables->LOCAL_DEV_HOST : $detectedHost;
$resourceLocation = isset($variables->RESOURCE_LOCATION) ? $variables->RESOURCE_LOCATION : $tenantContainerPath;
$tenantPath = rtrim($resourceLocation, "\\/") . DIRECTORY_SEPARATOR . $hostName;
$tenantJsonPath = $tenantPath . DIRECTORY_SEPARATOR . "tenant.json";
$defaultAppPath = $tenantPath . DIRECTORY_SEPARATOR . "apps" . DIRECTORY_SEPARATOR . "default-app";
$dbConfigPath = isset($variables->DB_CONFIG_FILE) ? $variables->DB_CONFIG_FILE : $tenantPath . DIRECTORY_SEPARATOR . "sossgrid.conf";
$mediaFolder = isset($variables->MEDIA_FOLDER) ? $variables->MEDIA_FOLDER : dirname($frameworkRoot) . DIRECTORY_SEPARATOR . "davvag-media";
$dataStoreKey = isset($variables->DATASTORE_DOMAIN) ? $variables->DATASTORE_DOMAIN : $detectedHost;

$requiredApacheModules = array("mod_rewrite");
$requiredPhpModules = array("curl", "mysqli", "json", "session", "mbstring", "iconv");
$apacheModules = function_exists("apache_get_modules") ? apache_get_modules() : array();

function install_status_label($ok, $unknown = false) {
    if ($unknown) {
        return '<span class="pill pill-warn">unknown</span>';
    }
    return $ok ? '<span class="pill pill-ok">ok</span>' : '<span class="pill pill-bad">missing</span>';
}

function install_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function install_json($value) {
    return htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_QUOTES, "UTF-8");
}

$exampleConfig = array(
    "variables" => array(
        "RESOURCE_LOCATION" => $resourceLocation,
        "MEDIA_FOLDER" => $mediaFolder,
        "LOCAL_DEV_HOST" => $hostName,
        "DB_CONFIG_FILE" => $dbConfigPath
    ),
    "alias" => array(
        "localhost" => array("127.0.0.1")
    ),
    "DAVVAG_StartUp" => array(
        "plugins" => array(
            array("name" => "notify", "plugin_location" => "global", "file" => "notify.php", "location" => "/notify/notify.php"),
            array("name" => "sossdata", "plugin_location" => "global", "file" => "SOSSData.php", "location" => "/sossdata/SOSSData.php"),
            array("name" => "auth", "plugin_location" => "global", "file" => "auth.php", "location" => "/auth/auth.php")
        )
    ),
    "DAVVAG_DATA" => array(
        $dataStoreKey => array(
            "connector" => "phpmysql",
            "phpmysql" => new stdClass()
        )
    ),
    "DAVVAG_AUTH" => array(
        "connector" => "phpauth",
        "phpauth" => new stdClass()
    ),
    "DEBUG" => false
);

$exampleDbConfig = array(
    "mysql_server" => "localhost",
    "mysql_username" => "davvag_user",
    "mysql_password" => "change-this-password",
    "init_db" => "davvag_"
);

$exampleTenant = array(
    "apps" => array(
        "default-app" => array(
            "version" => "latest"
        )
    ),
    "webdock" => array(
        "events" => array(
            "onStartup" => array(
                "admin" => "default-app",
                "default" => "default-app"
            )
        )
    )
);

$exampleGroup = $exampleTenant;

$exampleAppJson = array(
    "components" => new stdClass(),
    "description" => array(
        "title" => "DAVVAG Default App",
        "author" => "DAVVAG",
        "version" => "0.1",
        "icon" => ""
    ),
    "tags" => array("showindock"),
    "configuration" => array(
        "webdock" => array(
            "startupComponent" => "",
            "onLoad" => array(),
            "routes" => array(
                "partials" => new stdClass()
            )
        )
    )
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DAVVAG Installation Guide</title>
    <style>
        :root {
            --bg: #020604;
            --panel: rgba(0, 22, 12, 0.88);
            --panel-strong: rgba(1, 35, 20, 0.94);
            --ink: #d7ffe8;
            --muted: #7cbf98;
            --line: rgba(0, 255, 136, 0.22);
            --accent: #00ff88;
            --accent-2: #51ffbd;
            --bad: #ff6b6b;
            --bad-soft: rgba(255, 107, 107, 0.12);
            --warn: #ffe082;
            --warn-soft: rgba(255, 224, 130, 0.13);
            --shadow: rgba(0, 255, 136, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(0, 255, 136, 0.16), transparent 30rem),
                radial-gradient(circle at bottom right, rgba(0, 110, 66, 0.18), transparent 34rem),
                repeating-linear-gradient(0deg, rgba(0, 255, 136, 0.035), rgba(0, 255, 136, 0.035) 1px, transparent 1px, transparent 4px),
                linear-gradient(135deg, #020604 0%, #03130b 52%, #000 100%);
            color: var(--ink);
            font: 16px/1.55 Consolas, "Courier New", monospace;
        }

        main {
            width: min(1180px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 2rem 0 4rem;
        }

        h1,
        h2,
        h3 {
            line-height: 1.12;
            margin: 0 0 0.75rem;
        }

        h1 {
            color: var(--accent);
            font-family: Consolas, "Courier New", monospace;
            font-size: clamp(2.2rem, 5vw, 4.4rem);
            letter-spacing: -0.06em;
            text-shadow: 0 0 1.4rem rgba(0, 255, 136, 0.35);
        }

        h2 {
            color: var(--accent-2);
            font-family: Consolas, "Courier New", monospace;
            font-size: clamp(1.5rem, 3vw, 2.3rem);
        }

        h3 {
            color: var(--accent);
            font-size: 1.05rem;
        }

        p {
            margin: 0 0 1rem;
        }

        a {
            color: var(--accent-2);
            font-weight: 700;
        }

        code,
        pre {
            font-family: Consolas, "Courier New", monospace;
        }

        code {
            border-radius: 0.35rem;
            background: rgba(0, 255, 136, 0.11);
            color: var(--accent-2);
            padding: 0.08rem 0.3rem;
        }

        pre {
            overflow: auto;
            border: 1px solid var(--line);
            border-radius: 0.9rem;
            background:
                linear-gradient(180deg, rgba(0, 255, 136, 0.07), rgba(0, 0, 0, 0.18)),
                #020604;
            box-shadow: inset 0 0 2rem rgba(0, 255, 136, 0.06);
            color: #baffd6;
            padding: 1rem;
            white-space: pre;
        }

        .hero,
        .card {
            border: 1px solid var(--line);
            border-radius: 1.25rem;
            background: var(--panel);
            box-shadow: 0 0 2.5rem rgba(0, 0, 0, 0.4), 0 0 2rem var(--shadow);
            backdrop-filter: blur(12px);
        }

        .hero {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.25rem;
            padding: clamp(1.25rem, 4vw, 2rem);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            color: rgba(0, 255, 136, 0.3);
            content: "davvag@installer:~$ ./bootstrap --tenant --mysql";
            font-size: 0.85rem;
            letter-spacing: 0.03em;
            position: absolute;
            right: 1.5rem;
            top: 1rem;
        }

        .hero p {
            max-width: 72ch;
            color: var(--muted);
            font-size: 1.05rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .card {
            margin: 1rem 0;
            padding: 1.25rem;
        }

        .card h2::before {
            color: var(--accent);
            content: "> ";
        }

        .card > :last-child {
            margin-bottom: 0;
        }

        .check-list,
        .steps,
        .file-list {
            margin: 0;
            padding-left: 1.25rem;
        }

        .check-list li,
        .steps li,
        .file-list li {
            margin: 0.45rem 0;
        }

        .status-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 0.8rem;
        }

        .status-table th,
        .status-table td {
            border-bottom: 1px solid var(--line);
            padding: 0.7rem 0.5rem;
            text-align: left;
            vertical-align: top;
        }

        .status-table th {
            color: var(--muted);
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .pill {
            display: inline-block;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.22rem 0.55rem;
            text-transform: uppercase;
        }

        .pill-ok {
            background: rgba(0, 255, 136, 0.12);
            color: var(--accent);
        }

        .pill-bad {
            background: var(--bad-soft);
            color: var(--bad);
        }

        .pill-warn {
            background: var(--warn-soft);
            color: var(--warn);
        }

        .note {
            border: 1px solid var(--line);
            border-left: 0.35rem solid var(--accent);
            border-radius: 0.75rem;
            background: rgba(0, 255, 136, 0.09);
            color: #c8ffdc;
            padding: 0.9rem 1rem;
        }

        .warning {
            border-left-color: var(--warn);
            background: var(--warn-soft);
        }

        .path {
            overflow-wrap: anywhere;
        }

        @media (max-width: 820px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<main>
    <section class="hero">
        <div>
            <p><strong>DAVVAG Framework Console</strong></p>
            <h1>Boot the tenant runtime</h1>
        </div>
        <p>
            DAVVAG is a tenant-first PHP application framework: configure the engine once,
            create a domain folder, register apps with JSON, and let Webdock load UI components
            and PHP services on demand. This console guides developers through the clean install
            path before deploying the default tenant application.
        </p>
    </section>

    <section class="grid">
        <article class="card">
            <h2>Detected Paths</h2>
            <table class="status-table">
                <tr>
                    <th>Item</th>
                    <th>Value</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>Framework root</td>
                    <td class="path"><code><?php echo install_h($frameworkRoot); ?></code></td>
                    <td><?php echo install_status_label(is_dir($frameworkRoot)); ?></td>
                </tr>
                <tr>
                    <td>Root config</td>
                    <td class="path"><code><?php echo install_h($rootConfigPath); ?></code></td>
                    <td><?php echo install_status_label(file_exists($rootConfigPath)); ?></td>
                </tr>
                <tr>
                    <td>Effective host</td>
                    <td class="path"><code><?php echo install_h($hostName); ?></code></td>
                    <td><?php echo isset($variables->LOCAL_DEV_HOST) ? '<span class="pill pill-warn">forced by LOCAL_DEV_HOST</span>' : '<span class="pill pill-ok">from HTTP_HOST</span>'; ?></td>
                </tr>
                <tr>
                    <td>Tenant resource path</td>
                    <td class="path"><code><?php echo install_h($tenantPath); ?></code></td>
                    <td><?php echo install_status_label(is_dir($tenantPath)); ?></td>
                </tr>
                <tr>
                    <td>Tenant descriptor</td>
                    <td class="path"><code><?php echo install_h($tenantJsonPath); ?></code></td>
                    <td><?php echo install_status_label(file_exists($tenantJsonPath)); ?></td>
                </tr>
                <tr>
                    <td>Default app path</td>
                    <td class="path"><code><?php echo install_h($defaultAppPath); ?></code></td>
                    <td><?php echo install_status_label(is_dir($defaultAppPath)); ?></td>
                </tr>
                <tr>
                    <td>MySQL config</td>
                    <td class="path"><code><?php echo install_h($dbConfigPath); ?></code></td>
                    <td><?php echo install_status_label(file_exists($dbConfigPath)); ?></td>
                </tr>
                <tr>
                    <td>Media folder</td>
                    <td class="path"><code><?php echo install_h($mediaFolder); ?></code></td>
                    <td><?php echo install_status_label(is_dir($mediaFolder)); ?></td>
                </tr>
            </table>
            <?php if ($configError !== "") { ?>
                <p class="note warning"><?php echo install_h($configError); ?></p>
            <?php } ?>
        </article>

        <article class="card">
            <h2>Requirement Check</h2>
            <h3>Apache</h3>
            <ul class="check-list">
                <?php foreach ($requiredApacheModules as $module) { ?>
                    <li>
                        <code><?php echo install_h($module); ?></code>
                        <?php echo install_status_label(in_array($module, $apacheModules), !function_exists("apache_get_modules")); ?>
                    </li>
                <?php } ?>
            </ul>
            <h3>PHP</h3>
            <ul class="check-list">
                <?php foreach ($requiredPhpModules as $module) { ?>
                    <li>
                        <code><?php echo install_h($module); ?></code>
                        <?php echo install_status_label(extension_loaded($module)); ?>
                    </li>
                <?php } ?>
            </ul>
            <p class="note">
                The datastore connector uses <code>mysqli</code>. The older <code>mysql</code>
                extension is not required and is removed from modern PHP.
            </p>
        </article>
    </section>

    <section class="card">
        <h2>Install Order</h2>
        <ol class="steps">
            <li>Create or edit root <code>config.json</code> at <code><?php echo install_h($rootConfigPath); ?></code>.</li>
            <li>Create the tenant folder under <code>RESOURCE_LOCATION</code>, for example <code><?php echo install_h($tenantPath); ?></code>.</li>
            <li>Create tenant files: <code>tenant.json</code>, optional tenant <code>config.json</code>, <code>anonymous.json</code>, <code>web_user.json</code>, and <code>sysadmin.json</code>.</li>
            <li>Create tenant database config <code>sossgrid.conf</code> if using the bundled MySQL connector.</li>
            <li>Create the default application under <code>apps/default-app</code>.</li>
            <li>Open the site root again. <code>init.php</code> will load <code>TENANT_RESOURCE_LOCATION/tenant.json</code>, select the startup app, and include <code>apps/default-app/app.php</code>.</li>
        </ol>
    </section>

    <section class="card">
        <h2>1. Root config.json Example</h2>
        <p>
            Save this at the framework root as <code>config.json</code>. The lean install keeps
            tenant resolution, media storage, and MySQL config in <code>variables</code>.
            <code>DB_CONFIG_FILE</code> points to the tenant's <code>sossgrid.conf</code>.
        </p>
        <pre><?php echo install_json($exampleConfig); ?></pre>
        <p class="note warning">
            Use <code>LOCAL_DEV_HOST</code> only when local development needs to force a tenant
            folder, for example <code>apps.davvag.com</code> while browsing through localhost.
            In production, remove <code>LOCAL_DEV_HOST</code> so DAVVAG uses the real HTTP host
            as the tenant name.
        </p>
        <p class="note">
            The key under <code>DAVVAG_DATA</code> must match the runtime datastore domain.
            If no datastore domain is configured, DAVVAG uses the current HTTP host. In a
            local forced-tenant setup, that is often <code>localhost</code>.
        </p>
    </section>

    <section class="card">
        <h2>2. Tenant Folder Structure</h2>
        <p>Create this folder before installing the default app:</p>
        <pre><?php echo install_h($tenantPath); ?>/
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
  sossgrid.conf</pre>
        <p>PowerShell example for this machine:</p>
        <pre>New-Item -ItemType Directory -Force "<?php echo install_h($tenantPath); ?>\apps"
New-Item -ItemType Directory -Force "<?php echo install_h($tenantPath); ?>\davvag-flow"
New-Item -ItemType Directory -Force "<?php echo install_h($tenantPath); ?>\global\config"
New-Item -ItemType Directory -Force "<?php echo install_h($tenantPath); ?>\global\templetes\app"
New-Item -ItemType Directory -Force "<?php echo install_h($tenantPath); ?>\global\templetes\email"
New-Item -ItemType Directory -Force "<?php echo install_h($tenantPath); ?>\plugins"
New-Item -ItemType Directory -Force "<?php echo install_h($tenantPath); ?>\schemas"</pre>
    </section>

    <section class="grid">
        <article class="card">
            <h2>3. tenant.json</h2>
            <p>
                <code>tenant.json</code> registers installed apps and declares the default startup app.
            </p>
            <pre><?php echo install_json($exampleTenant); ?></pre>
        </article>

        <article class="card">
            <h2>4. Group Access Files</h2>
            <p>
                Save the same starter JSON as <code>anonymous.json</code>, <code>web_user.json</code>,
                and <code>sysadmin.json</code>. Later, remove apps from groups that should not see them.
            </p>
            <pre><?php echo install_json($exampleGroup); ?></pre>
        </article>
    </section>

    <section class="card">
        <h2>5. sossgrid.conf Example</h2>
        <p>
            Save this as <code><?php echo install_h($dbConfigPath); ?></code>
            and update the database credentials. The connector creates databases with
            <code>init_db</code> plus the tenant/domain name with dots converted to underscores.
        </p>
        <pre><?php echo install_json($exampleDbConfig); ?></pre>
        <p class="note">
            Example database name for tenant <code>localhost</code> with <code>init_db=davvag_</code>:
            <code>davvag_localhost</code>.
        </p>
    </section>

    <section class="card">
        <h2>6. Default Tenant Application</h2>
        <p>Create the default app only after the tenant folder and JSON files exist.</p>
        <ul class="file-list">
            <li><code><?php echo install_h($defaultAppPath); ?>/app.json</code></li>
            <li><code><?php echo install_h($defaultAppPath); ?>/app.php</code></li>
        </ul>
        <h3>app.json</h3>
        <pre><?php echo install_json($exampleAppJson); ?></pre>
        <h3>app.php</h3>
        <pre>&lt;!doctype html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="utf-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1"&gt;
    &lt;title&gt;DAVVAG Default App&lt;/title&gt;
    &lt;style&gt;
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #020604;
            color: #00ff88;
            font-family: Consolas, monospace;
        }
        main {
            max-width: 720px;
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid rgba(0, 255, 136, 0.32);
            background: rgba(0, 22, 12, 0.88);
            box-shadow: 0 0 2rem rgba(0, 255, 136, 0.16);
        }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;main&gt;
        &lt;h1&gt;DAVVAG tenant is installed&lt;/h1&gt;
        &lt;p&gt;The default tenant application loaded successfully.&lt;/p&gt;
    &lt;/main&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>
    </section>

    <section class="card">
        <h2>7. Production and cPanel Notes</h2>
        <ul class="file-list">
            <li>Enable Apache <code>mod_rewrite</code> and allow <code>.htaccess</code>.</li>
            <li>Keep <code>davvag-core/.htaccess</code> in place so tenant files are not browsable directly.</li>
            <li>Set production <code>RESOURCE_LOCATION</code> to <code>/home/USER/public_html/davvag-core</code>.</li>
            <li>Set production <code>MEDIA_FOLDER</code> outside public web root, for example <code>/home/USER/davvag-media</code>.</li>
            <li>Set production <code>DB_CONFIG_FILE</code> to the tenant MySQL config, for example <code>/home/USER/public_html/davvag-core/example.com/sossgrid.conf</code>.</li>
            <li>Do not commit real database passwords, SMTP credentials, API keys, or tenant secrets.</li>
            <li>After the smoke-test app works, replace <code>default-app</code> with a DAVVAG Webdock app such as <code>dock</code> or a custom tenant app.</li>
        </ul>
    </section>
</main>
</body>
</html>

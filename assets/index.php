<?php
require_once(dirname(__DIR__) . "/configloader.php");

$app = isset($_GET["app"]) ? $_GET["app"] : "";
$file = isset($_GET["file"]) ? $_GET["file"] : "";
$app = preg_replace("/[^A-Za-z0-9_\\.\\-]+/", "", $app);
$file = str_replace("\\", "/", $file);
$file = ltrim($file, "/");

if($app === "" || $file === "" || strpos($file, "..") !== false){
    http_response_code(400);
    echo "Invalid asset path.";
    exit();
}

$host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "localhost";
$host = preg_replace("/:.*/", "", $host);
$repoRoot = dirname(__DIR__);
$roots = array();

if(defined("TENANT_RESOURCE_LOCATION")){
    $roots[] = TENANT_RESOURCE_LOCATION;
}

$roots[] = $repoRoot . "/davvag-core/" . $host;
$roots[] = $repoRoot . "/davvag-core/localhost";

$target = null;
foreach($roots as $root){
    $base = realpath($root . "/apps/" . $app . "/assets");
    if($base === false){
        continue;
    }
    $candidate = realpath($base . "/" . $file);
    if($candidate === false || is_dir($candidate)){
        continue;
    }
    if(strpos($candidate, $base . DIRECTORY_SEPARATOR) !== 0 && $candidate !== $base){
        continue;
    }
    $target = $candidate;
    break;
}

if($target === null){
    http_response_code(404);
    echo "Asset not found.";
    exit();
}

$extension = strtolower(pathinfo($target, PATHINFO_EXTENSION));
if(in_array($extension, array("php", "phtml", "phar", "cgi", "pl"))){
    http_response_code(403);
    echo "Asset type not allowed.";
    exit();
}

$types = array(
    "css" => "text/css; charset=UTF-8",
    "js" => "application/javascript; charset=UTF-8",
    "json" => "application/json; charset=UTF-8",
    "svg" => "image/svg+xml",
    "png" => "image/png",
    "jpg" => "image/jpeg",
    "jpeg" => "image/jpeg",
    "gif" => "image/gif",
    "webp" => "image/webp",
    "ico" => "image/x-icon",
    "pdf" => "application/pdf",
    "txt" => "text/plain; charset=UTF-8",
    "woff" => "font/woff",
    "woff2" => "font/woff2",
    "ttf" => "font/ttf",
    "otf" => "font/otf",
    "eot" => "application/vnd.ms-fontobject"
);

$contentType = isset($types[$extension]) ? $types[$extension] : "application/octet-stream";
header("Content-Type: " . $contentType);
header("Content-Length: " . filesize($target));
header("Cache-Control: public, max-age=300");
readfile($target);
exit();
?>

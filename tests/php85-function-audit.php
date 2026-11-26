<?php
/** Token-based scan; input is a JSON array of PHP file paths on stdin. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$removed = array_fill_keys([
    'each', 'create_function', 'ereg', 'eregi', 'ereg_replace', 'eregi_replace',
    'split', 'spliti', 'money_format', 'get_magic_quotes_gpc', 'get_magic_quotes_runtime',
    'set_magic_quotes_runtime', 'mcrypt_create_iv', 'mcrypt_encrypt', 'mcrypt_decrypt',
    'mcrypt_get_iv_size', '__autoload',
], true);
$deprecated = array_fill_keys([
    'curl_close', 'curl_share_close', 'finfo_close', 'imagedestroy', 'mysqli_execute',
    'xml_parser_free', 'socket_set_timeout', 'utf8_encode', 'utf8_decode', 'strftime',
    'gmstrftime', 'mhash',
], true);
$findings = [];
$guarded = [];
foreach (json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR) as $file) {
    $source = file_get_contents($file);
    $sourceLines = explode("\n", $source);
    $tokens = array_values(array_filter(token_get_all($source), function ($token) {
        return !is_array($token) || !in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true);
    }));
    foreach ($tokens as $i => $token) {
        if (!is_array($token) || !in_array($token[0], [T_STRING, T_NAME_FULLY_QUALIFIED], true)) {
            continue;
        }
        $name = strtolower(ltrim($token[1], '\\'));
        if (!isset($removed[$name]) && !isset($deprecated[$name]) && strpos($name, 'mysql_') !== 0) {
            continue;
        }
        $previous = $tokens[$i - 1] ?? null;
        if (($tokens[$i + 1] ?? null) !== '(' || (is_array($previous) && in_array($previous[0], [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW], true))) {
            continue;
        }
        $finding = ['file' => $file, 'line' => $token[2], 'function' => $name];
        // Bundled HTMLPurifier explicitly limits this branch to PHP < 7.4
        // and checks function_exists(), so PHP 8.5 cannot execute the call.
        $normalized = str_replace('\\', '/', $file);
        $legacyGuard = "version_compare(PHP_VERSION, '7.4.0', '<') && function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()";
        if ($name === 'get_magic_quotes_gpc' && str_ends_with($normalized, '/library/HTMLPurifier/Config.php')
            && str_contains($sourceLines[$token[2] - 1], $legacyGuard)) {
            $guarded[] = $finding;
        } else {
            $findings[] = $finding;
        }
    }
}
echo json_encode(['findings' => $findings, 'guarded' => $guarded], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit($findings ? 1 : 0);

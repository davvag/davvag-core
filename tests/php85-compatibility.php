<?php
/** Run with PHP 8.5: php tests/php85-compatibility.php [plugin-directory] */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    return false;
});
$plugins = $argv[1] ?? dirname(__DIR__) . '/plugins';
$checks = 0;
function checkPhp85($condition, $message) {
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    ++$checks;
}

require $plugins . '/Facebook/autoload.php';
$collection = new Facebook\GraphNodes\Collection(['first' => 1]);
$collection[] = 2;
checkPhp85(count($collection) === 2 && $collection['first'] === 1, 'Facebook collection access');
checkPhp85(iterator_to_array($collection) === ['first' => 1, 0 => 2], 'Facebook iteration');
unset($collection['first']);
checkPhp85(!isset($collection['first']), 'Facebook collection unset');
$generator = Facebook\PseudoRandomString\PseudoRandomStringGeneratorFactory::createPseudoRandomStringGenerator('mcrypt');
$random = $generator->getPseudoRandomString(32);
checkPhp85(strlen($random) === 32 && ctype_xdigit($random), 'Legacy random generator configuration');
checkPhp85($generator->getPseudoRandomString(32) !== $random, 'Independent random values');
$curl = new Facebook\HttpClients\FacebookCurl();
$curl->init();
$curl->close();
checkPhp85(true, 'cURL handle release');
$stream = new Facebook\HttpClients\FacebookStream();
$stream->streamContextCreate([]);
checkPhp85($stream->fileGetContents('data://text/plain,php85') === 'php85', 'Stream response body');
checkPhp85($stream->getResponseHeaders() === [], 'Non-HTTP streams have no stale response headers');

require $plugins . '/Google/Model.php';
require $plugins . '/Google/Client.php';
require $plugins . '/Google/Collection.php';
$model = new Google_Model(['name' => 'test']);
checkPhp85($model['name'] === 'test', 'Google model array access');
$model['name'] = 'changed';
checkPhp85($model['name'] === 'changed', 'Google model write');
$googleCollection = new class(['items' => ['one', 'two']]) extends Google_Collection {
    public $items;
};
checkPhp85(count($googleCollection) === 2, 'Google collection count');
checkPhp85(iterator_to_array($googleCollection) === ['one', 'two'], 'Google collection iteration');

$mailRoot = is_file($plugins . '/phpmailer_old/PHPMailerAutoload.php') ? $plugins . '/phpmailer_old' : $plugins . '/phpmailer';
require $mailRoot . '/PHPMailerAutoload.php';
$mailer = new PHPMailer(true);
$mailer->setFrom('sender@example.com');
$mailer->addAddress('recipient@example.com');
$mailer->Subject = 'PHP 8.5 attachment check';
$mailer->Body = 'Attachment contents must be preserved.';
$attachment = tempnam(sys_get_temp_dir(), 'php85-mail-');
try {
    file_put_contents($attachment, "quotes \" dollar $ backslash \\ and UTF-8 \xc3\xa9");
    $mailer->addAttachment($attachment, 'sample.txt');
    checkPhp85($mailer->preSend(), 'Mail MIME generation');
    checkPhp85(strpos($mailer->getSentMIMEMessage(), base64_encode(file_get_contents($attachment))) !== false, 'Mail attachment encoding');
} finally {
    unlink($attachment);
}
require $mailRoot . '/extras/ntlm_sasl_client.php';
$ntlm = new ntlm_sasl_client_class();
$client = new stdClass();
checkPhp85($ntlm->initialize($client) === 1, 'NTLM OpenSSL/hash support');
// RFC 2759 section 9.2: https://www.rfc-editor.org/rfc/rfc2759.html#section-9.2
checkPhp85(bin2hex($ntlm->NTLMResponse(hex2bin('d02e4386bce91226'), 'clientPass')) === '82309ecd8d708b5ea08faa3981cd83544233114a3d85d6df', 'NTLM standard challenge-response vector');

if (is_file($plugins . '/fpdm/fpdm.php')) {
    require $plugins . '/fpdm/fpdm.php';
    $form = new FPDM('[_STATIC_]');
    $form->pdf_entries = ['/V (old)'];
    $form->shift = 0;
    $form->verbose = false;
    $value = 'Quotes " dollar $ and backslash \\';
    $form->_set_field_value(0, $value);
    checkPhp85($form->pdf_entries[0] === '/V (' . $value . ')', 'PDF closure preserves literal values');
    $form2 = new FPDM('[_STATIC_]');
    checkPhp85(count($form2->FPDM_FILTERS) === 5, 'PDF filters survive repeated construction');
    $filter = new FilterASCII85();
    checkPhp85($filter->decode('87cURD]j7BEbo80~>') === 'Hello world!', 'PDF ASCII85 bytes');
    require $plugins . '/fpdm/export/fdf/forge_fdf.php';
    checkPhp85(escape_pdf_string('(test)') === '\\(test\\)', 'FDF string offsets');
}
if (is_file($plugins . '/phpspreadsheet/vendor/autoload.php')) {
    require $plugins . '/phpspreadsheet/vendor/autoload.php';
    $book = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $book->getActiveSheet();
    $sheet->setCellValue('A1', 2);
    $sheet->setCellValue('A2', '=A1*3');
    checkPhp85($sheet->getCell('A2')->getCalculatedValue() === 6, 'Spreadsheet initialization and calculation');
    $sheet->setTitle('Renamed');
    checkPhp85($sheet->getCell('A2')->getCalculatedValue() === 6, 'Spreadsheet cache rename');
    $book->disconnectWorksheets();
}
if (is_file($plugins . '/mpdf/vendor/autoload.php')) {
    require $plugins . '/mpdf/vendor/autoload.php';
    checkPhp85(class_exists(Mpdf\Mpdf::class), 'mPDF and FPDI autoload');
    $filter = new setasign\Fpdi\PdfParser\Filter\Ascii85();
    checkPhp85($filter->decode('87cURD]j7BEbo80~>') === 'Hello world!', 'FPDI ASCII85 bytes');
}
echo "PASS: $checks PHP 8.5 compatibility checks ($plugins)\n";

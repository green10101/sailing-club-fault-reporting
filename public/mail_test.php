<?php
/**
 * Temporary SMTP diagnostic script.
 * Upload to the server, visit it once in a browser, then DELETE it.
 * It is protected by a simple token so it cannot be triggered by anyone else.
 */

// ── Simple access token – change this to anything you like ───────────────────
define('ACCESS_TOKEN', 'cyc-mail-test-2026');

if (($_GET['token'] ?? '') !== ACCESS_TOKEN) {
    http_response_code(403);
    exit('Access denied. Append ?token=' . ACCESS_TOKEN . ' to the URL.');
}

// ── Bootstrap ────────────────────────────────────────────────────────────────
require_once '../src/config/vendor_bootstrap.php';
loadVendorAutoload();

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname($envFile));
    $dotenv->load();
}

// ── Collect settings ─────────────────────────────────────────────────────────
$host       = $_ENV['MAIL_HOST']        ?? getenv('MAIL_HOST')        ?: '(not set)';
$port       = $_ENV['MAIL_PORT']        ?? getenv('MAIL_PORT')        ?: '(not set)';
$username   = $_ENV['MAIL_USERNAME']    ?? getenv('MAIL_USERNAME')    ?: '(not set)';
$password   = $_ENV['MAIL_PASSWORD']    ?? getenv('MAIL_PASSWORD')    ?: '(not set)';
$encryption = $_ENV['MAIL_ENCRYPTION']  ?? getenv('MAIL_ENCRYPTION')  ?: '(not set)';
$from       = $_ENV['MAIL_FROM_ADDRESS']?? getenv('MAIL_FROM_ADDRESS')?? '(not set)';
$to         = $_ENV['MAIL_TO']          ?? getenv('MAIL_TO')          ?: '(not set)';

echo "<h2>SMTP Diagnostic</h2>\n";
echo "<pre>\n";
echo "PHPMailer available : " . (class_exists(\PHPMailer\PHPMailer\PHPMailer::class) ? "YES" : "NO – vendor not updated on server") . "\n";
echo "MAIL_HOST           : $host\n";
echo "MAIL_PORT           : $port\n";
echo "MAIL_USERNAME       : $username\n";
echo "MAIL_PASSWORD       : " . (strlen($password) > 3 ? str_repeat('*', strlen($password) - 3) . substr($password, -3) : '(short/empty)') . "\n";
echo "MAIL_ENCRYPTION     : $encryption\n";
echo "MAIL_FROM_ADDRESS   : $from\n";
echo "MAIL_TO             : $to\n";
echo "</pre>\n";

if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
    echo "<p style='color:red'><strong>STOP: PHPMailer is not available. Upload the updated vendor folder (or vendor.tar.gz) to the server first.</strong></p>\n";
    exit;
}

// ── Attempt to send ──────────────────────────────────────────────────────────
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
$mail->SMTPDebug  = SMTP::DEBUG_SERVER;       // Full SMTP conversation output
$mail->Debugoutput = function($str, $level) {
    echo htmlspecialchars($str) . "<br>\n";
};

try {
    $mail->isSMTP();
    $mail->Host       = $host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $username;
    $mail->Password   = $password;
    $mail->Port       = (int) $port;
    $mail->SMTPSecure = (strtolower($encryption) === 'ssl')
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;

    $mail->setFrom($from, 'Sailing Club');
    $mail->addAddress($to);
    $mail->Subject = 'SMTP Test – Sailing Club Fault Reporter';
    $mail->Body    = 'This is a test email sent from the SMTP diagnostic script.';
    $mail->send();
    echo "<p style='color:green'><strong>SUCCESS – email sent to $to</strong></p>\n";
} catch (Exception $e) {
    echo "<p style='color:red'><strong>FAILED: " . htmlspecialchars($e->getMessage()) . "</strong></p>\n";
}

echo "<hr><p style='color:orange'><strong>Delete this file (public/mail_test.php) from the server when done.</strong></p>\n";

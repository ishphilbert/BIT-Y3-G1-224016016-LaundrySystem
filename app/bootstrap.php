<?php
/**
 * app/bootstrap.php - loaded first by every public page.
 * Starts the session, loads configuration, helpers, models and controllers.
 */
define('APP_PATH', __DIR__);

$appConfig = require APP_PATH . '/config/app.php';
// Work out the URL path of the public/ folder (so CSS, JS and links work under XAMPP too).
$baseUrl = $appConfig['base_url'];
if ($baseUrl === 'auto') {
    $baseUrl = '';
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $pubDir  = realpath(dirname(APP_PATH) . '/public');
    if ($docRoot && $pubDir) {
        $doc = str_replace('\\', '/', $docRoot);
        $pub = str_replace('\\', '/', $pubDir);
        if (stripos($pub, $doc) === 0) {
            $baseUrl = substr($pub, strlen($doc));   // '' when public/ is the document root
        }
    }
}
define('BASE_URL', rtrim($baseUrl, '/'));
define('APP_NAME', $appConfig['app_name']);
define('CURRENCY', $appConfig['currency']);
define('RESET_EMAIL_FROM', $appConfig['reset_email_from'] ?? 'no-reply@example.com');
date_default_timezone_set($appConfig['timezone']);

// Never show PHP errors to visitors; they are written to the server error log instead.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

set_exception_handler(function ($e) {
    error_log('Unhandled exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'An unexpected error occurred. Please try again later.';
});

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Session cookie hardening
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
ini_set('session.use_strict_mode', '1');
session_start();

require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/helpers/view.php';
require_once APP_PATH . '/helpers/validation.php';
require_once APP_PATH . '/helpers/csrf.php';
require_once APP_PATH . '/helpers/order_status.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Customer.php';
require_once APP_PATH . '/models/OrderItem.php';
require_once APP_PATH . '/models/Order.php';
require_once APP_PATH . '/helpers/auth.php';
require_once APP_PATH . '/controllers/AuthController.php';
require_once APP_PATH . '/controllers/DashboardController.php';
require_once APP_PATH . '/controllers/CustomerController.php';
require_once APP_PATH . '/controllers/OrderController.php';
require_once APP_PATH . '/controllers/UserController.php';

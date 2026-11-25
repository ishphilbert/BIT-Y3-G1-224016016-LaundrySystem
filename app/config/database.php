<?php
/**
 * app/config/database.php
 * PDO connection for the Online Laundry Service System.
 *
 * Default settings suit a local XAMPP/WAMP/Laragon install (user "root", empty password).
 * To use different credentials WITHOUT editing this file (and without committing them to
 * GitHub), create app/config/database.local.php that returns an array like:
 *
 *   <?php
 *   return ['host' => '127.0.0.1', 'name' => 'online_laundry_db',
 *           'user' => 'laundry_user', 'pass' => 'your-password'];
 *
 * That file is listed in .gitignore.
 */

function getDbConfig(): array
{
    $config = [
        'host'    => '127.0.0.1',
        'port'    => '3306',
        'name'    => 'online_laundry_db',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ];

    $localFile = __DIR__ . '/database.local.php';
    if (is_file($localFile)) {
        $config = array_merge($config, require $localFile);
    }
    return $config;
}

/**
 * Returns one shared PDO connection (created on first call).
 * On failure it logs the real error on the server and shows only a generic message,
 * so credentials and SQL details are never exposed to visitors.
 */
function getConnection(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $c   = getDbConfig();
    $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}";

    try {
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw exceptions on SQL errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // rows as associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                    // use real prepared statements
        ]);
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        http_response_code(500);
        exit('Service temporarily unavailable. Please try again later.');
    }
    return $pdo;
}

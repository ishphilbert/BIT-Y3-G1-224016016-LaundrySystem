<?php
/**
 * tools/make-hash.php - command-line helper that prints a password_hash() value.
 * Usage:  php tools/make-hash.php "MyPassword123"
 * Refuses to run from a browser.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
if ($argc < 2) {
    exit("Usage: php tools/make-hash.php \"password\"\n");
}
echo password_hash($argv[1], PASSWORD_DEFAULT) . "\n";

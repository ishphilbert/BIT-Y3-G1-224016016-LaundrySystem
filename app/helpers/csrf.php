<?php
/** app/helpers/csrf.php - CSRF token generation and verification. */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input to place inside every POST form. */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/** Call at the top of every POST handler. Stops the request when the token is missing/invalid. */
function verifyCsrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    $real = $_SESSION['csrf_token'] ?? '';
    if (!is_string($sent) || $real === '' || !hash_equals($real, $sent)) {
        abortWith(419, 'Invalid or expired security token. Please go back, refresh the page and try again.');
    }
}

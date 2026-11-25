<?php
/** app/helpers/view.php - output escaping, URLs, redirects, flash messages, view rendering. */

/** Escape a value for safe HTML output (XSS protection). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Build a URL relative to the site root. */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Returns HTML for queued flash messages and clears them. */
function renderFlash(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $html = '';
    foreach ($_SESSION['flash'] as $f) {
        $class = $f['type'] === 'success' ? 'alert-success' : 'alert-error';
        $html .= '<div class="alert ' . $class . '" role="alert">' . e($f['message']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

/**
 * Render a view inside a layout.
 * $layout 'public' or 'dashboard' selects app/views/layouts/<layout>_header.php / _footer.php.
 */
function render(string $view, array $data = [], string $layout = 'dashboard'): void
{
    extract($data, EXTR_SKIP);
    $pageTitle = $data['title'] ?? APP_NAME;
    require APP_PATH . '/views/layouts/' . $layout . '_header.php';
    require APP_PATH . '/views/' . $view . '.php';
    require APP_PATH . '/views/layouts/' . $layout . '_footer.php';
}

/** Stop the request with an error page (403, 404, 405, 419 ...). */
function abortWith(int $code, string $message): void
{
    http_response_code($code);
    render('errors/error', ['title' => 'Error ' . $code, 'code' => $code, 'message' => $message], 'public');
    exit;
}

function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        abortWith(405, 'This action must be submitted from a form.');
    }
}

function money($amount): string
{
    return CURRENCY . ' ' . number_format((float) $amount, 2);
}

function formatDate($date): string
{
    if (empty($date)) {
        return '-';
    }
    $ts = strtotime((string) $date);
    return $ts ? date('d M Y', $ts) : '-';
}

/** Escaped value from an array, for re-filling forms. */
function val(array $source, string $key, string $default = ''): string
{
    return e($source[$key] ?? $default);
}

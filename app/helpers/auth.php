<?php
/** app/helpers/auth.php - session-based authentication and role-based access control. */

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'user_id'  => (int) $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email'    => $_SESSION['email'] ?? '',
        'role'     => $_SESSION['role'] ?? '',
    ];
}

/** Log a user in. The session ID is regenerated to prevent session fixation. */
function loginUser(array $user): void
{
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $_SESSION['user_id']  = (int) $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['role']     = $user['role'];
}

/** Destroy the session completely (call session_start() again afterwards to show a message). */
function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function dashboardPathForRole(string $role): string
{
    if ($role === 'admin') {
        return 'admin/index.php';
    }
    if ($role === 'staff') {
        return 'staff/index.php';
    }
    return 'customer/index.php';
}

/** Require a logged-in user that still exists in the database. Returns the user row. */
function requireLogin(): array
{
    header('Cache-Control: no-store'); // stop the back button showing protected pages after logout
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in to continue.');
        redirect('login.php');
    }
    $user = User::findById((int) $_SESSION['user_id']);
    if (!$user) {
        logoutUser();
        session_start();
        setFlash('error', 'Your session has expired. Please log in again.');
        redirect('login.php');
    }
    $_SESSION['role']     = $user['role'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email']    = $user['email'];
    return $user;
}

/** Require one of the given roles, otherwise show "Unauthorized access". */
function requireRole(array $roles): array
{
    $user = requireLogin();
    if (!in_array($user['role'], $roles, true)) {
        abortWith(403, 'Unauthorized access.');
    }
    return $user;
}

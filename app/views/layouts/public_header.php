<?php
$navUser = currentUser();
$script  = $_SERVER['SCRIPT_NAME'];
$links = [
    'index.php' => 'Home', 'about.php' => 'About Us', 'services.php' => 'Services', 'contact.php' => 'Contact',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= e(url('assets/images/logo.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="public">
<header class="site-header">
    <div class="container nav">
        <a class="brand" href="<?= e(url('index.php')) ?>">
            <img src="<?= e(url('assets/images/logo.svg')) ?>" alt="" width="34" height="34">
            <span><?= e(APP_NAME) ?></span>
        </a>
        <button class="nav-toggle" id="navToggle" type="button" aria-label="Toggle menu" aria-expanded="false">&#9776;</button>
        <nav class="nav-links" id="navLinks">
            <?php foreach ($links as $file => $label): ?>
                <a href="<?= e(url($file)) ?>" class="<?= substr($script, -strlen($file)) === $file ? 'active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
            <?php if ($navUser): ?>
                <a class="btn btn-primary btn-small" href="<?= e(url(dashboardPathForRole($navUser['role']))) ?>">My Dashboard</a>
            <?php else: ?>
                <a href="<?= e(url('login.php')) ?>" class="<?= substr($script, -9) === 'login.php' ? 'active' : '' ?>">Login</a>
                <a class="btn btn-primary btn-small" href="<?= e(url('register.php')) ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
<div class="container"><?= renderFlash() ?></div>

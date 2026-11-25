<?php
$user   = currentUser();
$role   = $user['role'];
$script = $_SERVER['SCRIPT_NAME'];
$menus = [
    'admin' => [
        ['admin/index.php', 'Dashboard'], ['admin/orders.php', 'Orders'], ['admin/order-form.php', 'New Order'],
        ['admin/customers.php', 'Customers'], ['admin/users.php', 'User Accounts'], ['admin/reports.php', 'Reports'],
    ],
    'staff' => [
        ['staff/index.php', 'Dashboard'], ['staff/orders.php', 'Assigned Orders'], ['staff/history.php', 'Order History'],
    ],
    'customer' => [
        ['customer/index.php', 'Dashboard'], ['customer/new-order.php', 'Schedule Pickup'], ['customer/orders.php', 'My Orders'],
        ['customer/items.php', 'Items & Prices'], ['customer/profile.php', 'My Profile'],
    ],
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
<body class="dashboard">
<aside class="sidebar" id="sidebar">
    <a class="brand" href="<?= e(url('index.php')) ?>">
        <img src="<?= e(url('assets/images/logo.svg')) ?>" alt="" width="32" height="32">
        <span><?= e(APP_NAME) ?></span>
    </a>
    <p class="role-tag"><?= e(ucfirst($role)) ?> panel</p>
    <nav>
        <?php foreach ($menus[$role] as $item): ?>
            <a href="<?= e(url($item[0])) ?>" class="<?= substr($script, -strlen($item[0])) === $item[0] ? 'active' : '' ?>"><?= e($item[1]) ?></a>
        <?php endforeach; ?>
    </nav>
</aside>
<div class="dash-main">
    <header class="topbar">
        <button class="nav-toggle" id="sidebarToggle" type="button" aria-label="Toggle navigation">&#9776;</button>
        <h1><?= e($pageTitle) ?></h1>
        <div class="topbar-user">
            <span><?= e($user['username']) ?> <small>(<?= e($role) ?>)</small></span>
            <form method="post" action="<?= e(url('logout.php')) ?>">
                <?= csrfField() ?>
                <button type="submit" class="btn btn-outline btn-small">Logout</button>
            </form>
        </div>
    </header>
    <main class="content">
        <?= renderFlash() ?>

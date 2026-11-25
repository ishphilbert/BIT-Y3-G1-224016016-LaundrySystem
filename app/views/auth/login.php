<section class="container narrow">
    <div class="card auth-card">
        <h1><?= $audience === 'customer' ? 'Customer Login' : ($audience === 'staff' ? 'Admin / Staff Login' : 'Choose Login') ?></h1>
        <p class="muted"><?= $audience === 'customer' ? 'Sign in to schedule and track your laundry.' : ($audience === 'staff' ? 'Sign in to manage laundry orders and service.' : 'Choose the sign-in page for your account.') ?></p>
        <?php include APP_PATH . '/views/partials/errors.php'; ?>
        <?php if ($audience === ''): ?>
            <div class="form-actions">
                <a class="btn btn-primary btn-block" href="<?= e(url('login.php?type=customer')) ?>">Customer Login</a>
                <a class="btn btn-outline btn-block" href="<?= e(url('login.php?type=staff')) ?>">Admin / Staff Login</a>
            </div>
        <?php else: ?>
        <form method="post" data-validate>
            <?= csrfField() ?>
            <input type="hidden" name="audience" value="<?= e($audience) ?>">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= val($old, 'email') ?>" required maxlength="150" autocomplete="email">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <p class="muted center"><a href="<?= e(url('forgot-password.php')) ?>">Forgot your password?</a></p>
        <?php if ($audience === 'customer'): ?>
            <p class="muted center">New customer? <a href="<?= e(url('register.php')) ?>">Create an account</a></p>
        <?php endif; ?>
        <p class="muted center"><a href="<?= e(url('login.php')) ?>">Choose a different login</a></p>
        <?php endif; ?>
    </div>
</section>

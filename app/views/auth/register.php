<section class="container narrow">
    <div class="card auth-card">
        <h1>Create your account</h1>
        <p class="muted">Register as a customer to schedule laundry pickups.</p>
        <?php include APP_PATH . '/views/partials/errors.php'; ?>
        <form method="post" data-validate>
            <?= csrfField() ?>
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name" value="<?= val($old, 'full_name') ?>" required minlength="2" maxlength="150">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= val($old, 'username') ?>" required minlength="3" maxlength="100" pattern="[A-Za-z0-9 _.\-]{3,100}" title="Letters, numbers, spaces and . _ -">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= val($old, 'email') ?>" required maxlength="150" autocomplete="email">
            <label for="phone">Phone number</label>
            <input type="tel" id="phone" name="phone" value="<?= val($old, 'phone') ?>" required pattern="\+?[0-9 \-]{9,20}" title="9-15 digits, optionally starting with +" placeholder="0788123456">
            <label for="address">Pickup address</label>
            <textarea id="address" name="address" rows="2" required minlength="5" maxlength="500"><?= val($old, 'address') ?></textarea>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
            <small class="muted">At least 8 characters, with a letter and a number.</small>
            <label for="confirm_password">Confirm password</label>
            <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" data-match="password">
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        <p class="muted center">Already registered? <a href="<?= e(url('login.php')) ?>">Login</a></p>
    </div>
</section>

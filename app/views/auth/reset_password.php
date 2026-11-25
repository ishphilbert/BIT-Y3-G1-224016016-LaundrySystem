<section class="container narrow">
    <div class="card auth-card">
        <h1>Reset Password</h1>
        <?php include APP_PATH . '/views/partials/errors.php'; ?>
        <form method="post" data-validate>
            <?= csrfField() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <label for="password">New password</label>
            <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
            <label for="confirm_password">Confirm new password</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
            <button type="submit" class="btn btn-primary btn-block">Save new password</button>
        </form>
    </div>
</section>

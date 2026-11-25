<section class="container narrow">
    <div class="card auth-card">
        <h1>Forgot Password</h1>
        <p class="muted">Enter your account email and we’ll send a password reset link if an account matches.</p>
        <?php include APP_PATH . '/views/partials/errors.php'; ?>
        <form method="post" data-validate>
            <?= csrfField() ?>
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" required maxlength="150" autocomplete="email">
            <button type="submit" class="btn btn-primary btn-block">Send reset link</button>
        </form>
        <p class="muted center"><a href="<?= e(url('login.php')) ?>">Back to login</a></p>
    </div>
</section>

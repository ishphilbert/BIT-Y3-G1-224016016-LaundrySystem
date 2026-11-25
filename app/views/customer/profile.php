<section class="card narrow-card">
    <h2>My profile</h2>
    <?php include APP_PATH . '/views/partials/errors.php'; ?>
    <form method="post" data-validate>
        <?= csrfField() ?>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= val($old, 'username') ?>" readonly>
        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" value="<?= val($old, 'full_name') ?>" required minlength="2" maxlength="150">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?= val($old, 'email') ?>" required maxlength="150">
        <label for="phone">Phone number</label>
        <input type="tel" id="phone" name="phone" value="<?= val($old, 'phone') ?>" required pattern="\+?[0-9 \-]{9,20}" title="9-15 digits, optionally starting with +">
        <label for="address">Pickup address</label>
        <textarea id="address" name="address" rows="2" required minlength="5" maxlength="500"><?= val($old, 'address') ?></textarea>
        <h3>Change password (optional)</h3>
        <label for="current_password">Current password</label>
        <input type="password" id="current_password" name="current_password" autocomplete="current-password">
        <label for="new_password">New password</label>
        <input type="password" id="new_password" name="new_password" minlength="8" autocomplete="new-password">
        <label for="confirm_password">Confirm new password</label>
        <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" data-match="new_password">
        <button type="submit" class="btn btn-primary">Save profile</button>
    </form>
</section>

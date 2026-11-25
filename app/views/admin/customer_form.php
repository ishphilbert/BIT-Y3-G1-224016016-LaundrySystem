<section class="card narrow-card">
    <h2><?= $customer ? 'Edit customer' : 'Add customer' ?></h2>
    <?php include APP_PATH . '/views/partials/errors.php'; ?>
    <form method="post" data-validate>
        <?= csrfField() ?>
        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" value="<?= val($old, 'full_name') ?>" required minlength="2" maxlength="150">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= val($old, 'username') ?>" required minlength="3" maxlength="100" pattern="[A-Za-z0-9 _.\-]{3,100}" title="Letters, numbers, spaces and . _ -">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?= val($old, 'email') ?>" required maxlength="150">
        <label for="phone">Phone number</label>
        <input type="tel" id="phone" name="phone" value="<?= val($old, 'phone') ?>" required pattern="\+?[0-9 \-]{9,20}" title="9-15 digits, optionally starting with +">
        <label for="address">Address</label>
        <textarea id="address" name="address" rows="2" required minlength="5" maxlength="500"><?= val($old, 'address') ?></textarea>
        <label for="password"><?= $customer ? 'New password (leave blank to keep current)' : 'Password' ?></label>
        <input type="password" id="password" name="password" minlength="8" <?= $customer ? '' : 'required' ?> autocomplete="new-password">
        <div class="btn-row">
            <button type="submit" class="btn btn-primary"><?= $customer ? 'Save changes' : 'Create customer' ?></button>
            <a class="btn btn-outline" href="<?= e(url('admin/customers.php')) ?>">Cancel</a>
        </div>
    </form>
</section>

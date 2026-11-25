<section class="card narrow-card">
    <h2><?= $edit ? 'Edit user (' . e(ucfirst($edit['role'])) . ')' : 'Register a staff account' ?></h2>
    <?php include APP_PATH . '/views/partials/errors.php'; ?>
    <form method="post" data-validate>
        <?= csrfField() ?>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= val($old, 'username') ?>" required minlength="3" maxlength="100" pattern="[A-Za-z0-9 _.\-]{3,100}" title="Letters, numbers, spaces and . _ -">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?= val($old, 'email') ?>" required maxlength="150">
        <label for="password"><?= $edit ? 'New password (leave blank to keep current)' : 'Password' ?></label>
        <input type="password" id="password" name="password" minlength="8" <?= $edit ? '' : 'required' ?> autocomplete="new-password">
        <div class="btn-row">
            <button type="submit" class="btn btn-primary"><?= $edit ? 'Save changes' : 'Create staff account' ?></button>
            <a class="btn btn-outline" href="<?= e(url('admin/users.php')) ?>">Cancel</a>
        </div>
    </form>
</section>

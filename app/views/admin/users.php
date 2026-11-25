<section class="card">
    <div class="card-head">
        <h2>User accounts</h2>
        <a class="btn btn-primary btn-small" href="<?= e(url('admin/user-form.php')) ?>">+ Register staff</a>
    </div>
    <form method="get" class="filter-bar">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Username or email" maxlength="50" aria-label="Search users">
        <select name="role" aria-label="Filter by role">
            <option value="">All roles</option>
            <?php foreach (['admin', 'staff', 'customer'] as $r): ?>
                <option value="<?= e($r) ?>"<?= $role === $r ? ' selected' : '' ?>><?= e(ucfirst($r)) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-small" type="submit">Filter</button>
        <a class="btn btn-outline btn-small" href="<?= e(url('admin/users.php')) ?>">Reset</a>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int) $u['user_id'] ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="badge badge-role-<?= e($u['role']) ?>"><?= e(ucfirst($u['role'])) ?></span></td>
                    <td><?= e(formatDate($u['created_at'])) ?></td>
                    <td class="actions">
                        <a class="btn btn-outline btn-small" href="<?= e(url('admin/user-form.php?id=' . (int) $u['user_id'])) ?>">Edit</a>
                        <?php if ((int) $u['user_id'] !== (int) currentUser()['user_id']): ?>
                        <form method="post" action="<?= e(url('admin/user-delete.php')) ?>" data-confirm="Delete user <?= e($u['username']) ?>? <?= $u['role'] === 'customer' ? 'Their customer profile and orders will also be deleted.' : 'This cannot be undone.' ?>">
                            <?= csrfField() ?>
                            <input type="hidden" name="user_id" value="<?= (int) $u['user_id'] ?>">
                            <button class="btn btn-danger btn-small" type="submit">Delete</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$users): ?><tr><td colspan="6" class="muted">No users found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

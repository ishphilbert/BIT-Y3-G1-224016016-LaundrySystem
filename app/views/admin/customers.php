<section class="card">
    <div class="card-head">
        <h2>Customers</h2>
        <a class="btn btn-primary btn-small" href="<?= e(url('admin/customer-form.php')) ?>">+ Add customer</a>
    </div>
    <form method="get" class="filter-bar">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Name, email or phone" maxlength="50" aria-label="Search customers">
        <button class="btn btn-primary btn-small" type="submit">Search</button>
        <a class="btn btn-outline btn-small" href="<?= e(url('admin/customers.php')) ?>">Reset</a>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th class="num">Orders</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($customers as $c): ?>
                <tr>
                    <td><?= (int) $c['customer_id'] ?></td>
                    <td><?= e($c['full_name']) ?></td>
                    <td><?= e($c['email']) ?></td>
                    <td><?= e($c['phone']) ?></td>
                    <td><?= e($c['address']) ?></td>
                    <td class="num"><?= (int) $c['order_count'] ?></td>
                    <td class="actions">
                        <a class="btn btn-outline btn-small" href="<?= e(url('admin/customer-form.php?id=' . (int) $c['customer_id'])) ?>">Edit</a>
                        <form method="post" action="<?= e(url('admin/customer-delete.php')) ?>" data-confirm="Delete <?= e($c['full_name']) ?>? Their account and all <?= (int) $c['order_count'] ?> order(s) will be permanently deleted.">
                            <?= csrfField() ?>
                            <input type="hidden" name="customer_id" value="<?= (int) $c['customer_id'] ?>">
                            <button class="btn btn-danger btn-small" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$customers): ?><tr><td colspan="7" class="muted">No customers found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

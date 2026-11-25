<section class="card">
    <div class="card-head">
        <h2>All orders</h2>
        <a class="btn btn-primary btn-small" href="<?= e(url('admin/order-form.php')) ?>">+ New order</a>
    </div>
    <form method="get" class="filter-bar">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Order ID, customer or status" maxlength="50" aria-label="Search">
        <select name="status" aria-label="Filter by status">
            <option value="">All statuses</option>
            <?php foreach (orderStatuses() as $key => $label): ?>
                <option value="<?= e($key) ?>"<?= $status === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-small" type="submit">Filter</button>
        <a class="btn btn-outline btn-small" href="<?= e(url('admin/orders.php')) ?>">Reset</a>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>ID</th><th>Customer</th><th>Staff</th><th>Pickup</th><th>Delivery</th><th class="num">Total</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= (int) $o['order_id'] ?></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td><?= $o['staff_name'] ? e($o['staff_name']) : '<span class="muted">Unassigned</span>' ?></td>
                    <td><?= e(formatDate($o['pickup_date'])) ?></td>
                    <td><?= e(formatDate($o['delivery_date'])) ?></td>
                    <td class="num"><?= e(money($o['total'])) ?></td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td class="actions">
                        <a class="btn btn-outline btn-small" href="<?= e(url('admin/order.php?id=' . (int) $o['order_id'])) ?>">View</a>
                        <?php if (!in_array($o['status'], ['delivered', 'cancelled'], true)): ?>
                            <a class="btn btn-outline btn-small" href="<?= e(url('admin/order-form.php?id=' . (int) $o['order_id'])) ?>">Edit</a>
                        <?php endif; ?>
                        <form method="post" action="<?= e(url('admin/order-delete.php')) ?>" data-confirm="Delete order #<?= (int) $o['order_id'] ?> and its items? This cannot be undone.">
                            <?= csrfField() ?>
                            <input type="hidden" name="order_id" value="<?= (int) $o['order_id'] ?>">
                            <button class="btn btn-danger btn-small" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$orders): ?><tr><td colspan="8" class="muted">No orders found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

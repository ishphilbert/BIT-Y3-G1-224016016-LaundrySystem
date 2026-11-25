<section class="card">
    <h2><?= e($heading) ?></h2>
    <form method="get" class="filter-bar">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Order ID, customer or status" maxlength="50" aria-label="Search">
        <select name="status" aria-label="Filter by status">
            <option value="">All</option>
            <?php foreach ($choices as $key): ?>
                <option value="<?= e($key) ?>"<?= $status === $key ? ' selected' : '' ?>><?= e(statusLabel($key)) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-small" type="submit">Filter</button>
        <a class="btn btn-outline btn-small" href="<?= e(url('staff/' . $page)) ?>">Reset</a>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>ID</th><th>Customer</th><th>Phone</th><th>Pickup</th><th>Delivery</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= (int) $o['order_id'] ?></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td><?= e($o['customer_phone']) ?></td>
                    <td><?= e(formatDate($o['pickup_date'])) ?></td>
                    <td><?= e(formatDate($o['delivery_date'])) ?></td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td><a class="btn btn-outline btn-small" href="<?= e(url('staff/order.php?id=' . (int) $o['order_id'])) ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$orders): ?><tr><td colspan="7" class="muted">No orders found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

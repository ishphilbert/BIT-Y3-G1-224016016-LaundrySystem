<section class="card">
    <h2>My orders</h2>
    <div class="tabs">
        <a href="<?= e(url('customer/orders.php')) ?>" class="<?= $filter === '' ? 'active' : '' ?>">All</a>
        <a href="<?= e(url('customer/orders.php?filter=active')) ?>" class="<?= $filter === 'active' ? 'active' : '' ?>">Active</a>
        <a href="<?= e(url('customer/orders.php?filter=completed')) ?>" class="<?= $filter === 'completed' ? 'active' : '' ?>">Completed</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>ID</th><th>Placed</th><th>Pickup</th><th>Delivery</th><th class="num">Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= (int) $o['order_id'] ?></td>
                    <td><?= e(formatDate($o['created_at'])) ?></td>
                    <td><?= e(formatDate($o['pickup_date'])) ?></td>
                    <td><?= e(formatDate($o['delivery_date'])) ?></td>
                    <td class="num"><?= e(money($o['total'])) ?></td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td><a class="btn btn-outline btn-small" href="<?= e(url('customer/order.php?id=' . (int) $o['order_id'])) ?>">Details</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$orders): ?><tr><td colspan="7" class="muted">No orders found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<p class="lead">Hello, <?= e($customer['full_name']) ?>!</p>
<div class="stats">
    <div class="stat"><span class="stat-label">Total orders</span><span class="stat-value"><?= (int) $total ?></span></div>
    <div class="stat"><span class="stat-label">Active orders</span><span class="stat-value"><?= (int) $active ?></span></div>
    <div class="stat"><span class="stat-label">Completed (delivered)</span><span class="stat-value"><?= (int) $delivered ?></span></div>
    <div class="stat"><span class="stat-label">Total spent</span><span class="stat-value small"><?= e(money($spent)) ?></span></div>
</div>
<p><a class="btn btn-primary" href="<?= e(url('customer/new-order.php')) ?>">Schedule a pickup</a></p>
<section class="card">
    <div class="card-head"><h2>Recent orders</h2><a href="<?= e(url('customer/orders.php')) ?>">View all</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>ID</th><th>Pickup</th><th>Delivery</th><th class="num">Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recent as $o): ?>
                <tr>
                    <td>#<?= (int) $o['order_id'] ?></td>
                    <td><?= e(formatDate($o['pickup_date'])) ?></td>
                    <td><?= e(formatDate($o['delivery_date'])) ?></td>
                    <td class="num"><?= e(money($o['total'])) ?></td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td><a href="<?= e(url('customer/order.php?id=' . (int) $o['order_id'])) ?>">Track</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recent): ?><tr><td colspan="6" class="muted">You have no orders yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

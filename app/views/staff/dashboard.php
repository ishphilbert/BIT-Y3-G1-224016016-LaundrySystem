<div class="stats">
    <div class="stat"><span class="stat-label">Assigned orders</span><span class="stat-value"><?= (int) $total ?></span></div>
    <div class="stat"><span class="stat-label">To confirm (pending)</span><span class="stat-value"><?= (int) $groups['pending'] ?></span></div>
    <div class="stat"><span class="stat-label">In progress</span><span class="stat-value"><?= (int) $groups['processing'] ?></span></div>
    <div class="stat"><span class="stat-label">Ready / out for delivery</span><span class="stat-value"><?= (int) $groups['completed'] ?></span></div>
    <div class="stat"><span class="stat-label">Delivered</span><span class="stat-value"><?= (int) $groups['delivered'] ?></span></div>
</div>
<section class="card">
    <div class="card-head"><h2>Orders needing attention</h2><a href="<?= e(url('staff/orders.php')) ?>">View all</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>ID</th><th>Customer</th><th>Pickup</th><th>Delivery</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($active as $o): ?>
                <tr>
                    <td>#<?= (int) $o['order_id'] ?></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td><?= e(formatDate($o['pickup_date'])) ?></td>
                    <td><?= e(formatDate($o['delivery_date'])) ?></td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td><a class="btn btn-outline btn-small" href="<?= e(url('staff/order.php?id=' . (int) $o['order_id'])) ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$active): ?><tr><td colspan="6" class="muted">No active orders assigned to you.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

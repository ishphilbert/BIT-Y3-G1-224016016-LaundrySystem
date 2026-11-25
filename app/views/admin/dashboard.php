<div class="stats">
    <div class="stat"><span class="stat-label">Customers</span><span class="stat-value"><?= (int) $customerCount ?></span></div>
    <div class="stat"><span class="stat-label">Staff members</span><span class="stat-value"><?= (int) $staffCount ?></span></div>
    <div class="stat"><span class="stat-label">Total orders</span><span class="stat-value"><?= (int) $totalOrders ?></span></div>
    <div class="stat"><span class="stat-label">Pending</span><span class="stat-value"><?= (int) $groups['pending'] ?></span></div>
    <div class="stat"><span class="stat-label">Processing</span><span class="stat-value"><?= (int) $groups['processing'] ?></span></div>
    <div class="stat"><span class="stat-label">Completed (ready / out)</span><span class="stat-value"><?= (int) $groups['completed'] ?></span></div>
    <div class="stat"><span class="stat-label">Delivered</span><span class="stat-value"><?= (int) $groups['delivered'] ?></span></div>
    <div class="stat"><span class="stat-label">Revenue (excl. cancelled)</span><span class="stat-value small"><?= e(money($revenue)) ?></span></div>
    <div class="stat"><span class="stat-label">Delivered revenue</span><span class="stat-value small"><?= e(money($deliveredRevenue)) ?></span></div>
</div>

<section class="card">
    <h2>Live order search</h2>
    <p class="muted">Type an order ID, customer name or status. Results update without reloading the page.</p>
    <input type="search" id="liveSearch" placeholder="e.g. 3, Jane, processing" maxlength="50" autocomplete="off"
           data-endpoint="<?= e(url('api/search-orders.php')) ?>" data-order-url="<?= e(url('admin/order.php')) ?>" aria-label="Search orders">
    <p id="liveSearchMsg" class="muted" aria-live="polite"></p>
    <div class="table-wrap">
        <table class="table" id="liveResults" hidden>
            <thead><tr><th>ID</th><th>Customer</th><th>Status</th><th>Pickup</th><th>Delivery</th><th class="num">Total</th><th>Staff</th><th></th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</section>

<section class="card">
    <div class="card-head"><h2>Recent orders</h2><a href="<?= e(url('admin/orders.php')) ?>">View all</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>ID</th><th>Customer</th><th>Status</th><th>Pickup</th><th class="num">Total</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recent as $o): ?>
                <tr>
                    <td>#<?= (int) $o['order_id'] ?></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td><?= e(formatDate($o['pickup_date'])) ?></td>
                    <td class="num"><?= e(money($o['total'])) ?></td>
                    <td><a href="<?= e(url('admin/order.php?id=' . (int) $o['order_id'])) ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recent): ?><tr><td colspan="6" class="muted">No orders yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="two-col">
    <section class="card">
        <h2>Order #<?= (int) $order['order_id'] ?> <?= statusBadge($order['status']) ?></h2>
        <dl class="details">
            <dt>Customer</dt><dd><?= e($order['customer_name']) ?></dd>
            <dt>Phone</dt><dd><?= e($order['customer_phone']) ?></dd>
            <dt>Pickup / delivery address</dt><dd><?= nl2br(e($order['customer_address'])) ?></dd>
            <dt>Pickup date</dt><dd><?= e(formatDate($order['pickup_date'])) ?></dd>
            <dt>Delivery date</dt><dd><?= e(formatDate($order['delivery_date'])) ?></dd>
        </dl>
    </section>
    <section class="card">
        <h2>Update progress</h2>
        <?php if (!$next): ?>
            <p class="muted">No further actions: this order is <?= e(strtolower(statusLabel($order['status']))) ?>.</p>
        <?php else: ?>
            <p class="muted">Current status: <?= statusBadge($order['status']) ?></p>
            <?php foreach ($next as $n): ?>
                <form method="post" action="<?= e(url('staff/order-status.php')) ?>" class="inline-form">
                    <?= csrfField() ?>
                    <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                    <input type="hidden" name="new_status" value="<?= e($n) ?>">
                    <button type="submit" class="btn btn-primary btn-block"><?= e(statusActionLabel($n)) ?></button>
                </form>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>
<section class="card">
    <h2>Laundry items</h2>
    <?php include APP_PATH . '/views/partials/items_table.php'; ?>
</section>
<p><a href="<?= e(url('staff/orders.php')) ?>">&larr; Back to assigned orders</a></p>

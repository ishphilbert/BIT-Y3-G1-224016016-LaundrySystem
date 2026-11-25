<?php
$steps   = trackerSteps();
$current = array_search($order['status'], $steps, true);
?>
<section class="card">
    <div class="card-head">
        <h2>Order #<?= (int) $order['order_id'] ?> <?= statusBadge($order['status']) ?></h2>
        <?php if ($order['status'] === 'pending'): ?>
            <form method="post" action="<?= e(url('customer/cancel-order.php')) ?>" data-confirm="Cancel order #<?= (int) $order['order_id'] ?>?">
                <?= csrfField() ?>
                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                <button type="submit" class="btn btn-danger btn-small">Cancel order</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($order['status'] === 'cancelled'): ?>
        <p class="alert alert-error">This order was cancelled.</p>
    <?php else: ?>
        <ol class="tracker">
            <?php foreach ($steps as $i => $step): ?>
                <li class="<?= $i < $current ? 'done' : ($i === $current ? 'current' : '') ?>"><span><?= e(statusLabel($step)) ?></span></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <dl class="details">
        <dt>Pickup date</dt><dd><?= e(formatDate($order['pickup_date'])) ?></dd>
        <dt>Delivery date</dt><dd><?= e(formatDate($order['delivery_date'])) ?></dd>
        <dt>Pickup address</dt><dd><?= nl2br(e($order['customer_address'])) ?></dd>
        <dt>Handled by</dt><dd><?= $order['staff_name'] ? e($order['staff_name']) : 'Not yet assigned' ?></dd>
    </dl>
</section>
<section class="card">
    <h2>Items &amp; prices</h2>
    <?php include APP_PATH . '/views/partials/items_table.php'; ?>
</section>
<p><a href="<?= e(url('customer/orders.php')) ?>">&larr; Back to my orders</a></p>

<?php $final = in_array($order['status'], ['delivered', 'cancelled'], true); ?>
<div class="two-col">
    <section class="card">
        <div class="card-head">
            <h2>Order #<?= (int) $order['order_id'] ?> <?= statusBadge($order['status']) ?></h2>
            <?php if (!$final): ?>
                <a class="btn btn-outline btn-small" href="<?= e(url('admin/order-form.php?id=' . (int) $order['order_id'])) ?>">Edit order</a>
            <?php endif; ?>
        </div>
        <dl class="details">
            <dt>Customer</dt><dd><?= e($order['customer_name']) ?></dd>
            <dt>Phone</dt><dd><?= e($order['customer_phone']) ?></dd>
            <dt>Address</dt><dd><?= nl2br(e($order['customer_address'])) ?></dd>
            <dt>Pickup date</dt><dd><?= e(formatDate($order['pickup_date'])) ?></dd>
            <dt>Delivery date</dt><dd><?= e(formatDate($order['delivery_date'])) ?></dd>
            <dt>Assigned staff</dt><dd><?= $order['staff_name'] ? e($order['staff_name']) : 'Unassigned' ?></dd>
            <dt>Created</dt><dd><?= e($order['created_at']) ?></dd>
            <dt>Last updated</dt><dd><?= e($order['updated_at'] ?? '-') ?></dd>
        </dl>
    </section>

    <section class="card">
        <h2>Manage</h2>
        <?php if ($final): ?>
            <p class="muted">This order is <?= e(strtolower(statusLabel($order['status']))) ?> and can no longer be changed.</p>
        <?php else: ?>
            <form method="post" action="<?= e(url('admin/order-assign.php')) ?>">
                <?= csrfField() ?>
                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                <label for="staff_id">Assign staff</label>
                <select id="staff_id" name="staff_id">
                    <option value="">-- Unassigned --</option>
                    <?php foreach ($staff as $s): ?>
                        <option value="<?= (int) $s['user_id'] ?>"<?= (int) $order['assigned_staff_id'] === (int) $s['user_id'] ? ' selected' : '' ?>><?= e($s['username']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary btn-small" type="submit">Save assignment</button>
            </form>
            <hr>
            <form method="post" action="<?= e(url('admin/order-status.php')) ?>">
                <?= csrfField() ?>
                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                <label for="new_status">Update status</label>
                <select id="new_status" name="new_status" required>
                    <?php foreach (allowedNextStatuses($order['status']) as $next): ?>
                        <option value="<?= e($next) ?>"><?= e(statusLabel($next)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary btn-small" type="submit">Update status</button>
            </form>
        <?php endif; ?>
    </section>
</div>

<section class="card">
    <h2>Laundry items</h2>
    <?php include APP_PATH . '/views/partials/items_table.php'; ?>
</section>

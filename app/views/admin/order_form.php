<?php $priceEditable = true; ?>
<section class="card">
    <h2><?= $order ? 'Edit order #' . (int) $order['order_id'] : 'Create a laundry order' ?></h2>
    <?php include APP_PATH . '/views/partials/errors.php'; ?>
    <form method="post" data-validate data-order-form>
        <?= csrfField() ?>
        <div class="form-grid">
            <div>
                <label for="customer_id">Customer</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">-- Select customer --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= (int) $c['customer_id'] ?>"<?= (string) $old['customer_id'] === (string) $c['customer_id'] ? ' selected' : '' ?>><?= e($c['full_name']) ?> (<?= e($c['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="assigned_staff_id">Assigned staff</label>
                <select id="assigned_staff_id" name="assigned_staff_id">
                    <option value="">-- Unassigned --</option>
                    <?php foreach ($staff as $s): ?>
                        <option value="<?= (int) $s['user_id'] ?>"<?= (string) $old['assigned_staff_id'] === (string) $s['user_id'] ? ' selected' : '' ?>><?= e($s['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="pickup_date">Pickup date</label>
                <input type="date" id="pickup_date" name="pickup_date" value="<?= val($old, 'pickup_date') ?>" required <?= $order ? '' : 'min="' . e(date('Y-m-d')) . '"' ?>>
            </div>
            <div>
                <label for="delivery_date">Delivery date</label>
                <input type="date" id="delivery_date" name="delivery_date" value="<?= val($old, 'delivery_date') ?>" required>
            </div>
            <?php if ($order): ?>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="<?= e($order['status']) ?>" selected><?= e(statusLabel($order['status'])) ?> (current)</option>
                    <?php foreach ($allowed as $next): ?>
                        <option value="<?= e($next) ?>"<?= $old['status'] === $next ? ' selected' : '' ?>><?= e(statusLabel($next)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <h3>Laundry items</h3>
        <?php include APP_PATH . '/views/partials/item_rows.php'; ?>

        <div class="btn-row">
            <button type="submit" class="btn btn-primary"><?= $order ? 'Save changes' : 'Create order' ?></button>
            <a class="btn btn-outline" href="<?= e(url('admin/orders.php')) ?>">Cancel</a>
        </div>
    </form>
</section>

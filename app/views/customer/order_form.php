<?php $priceEditable = false; ?>
<section class="card">
    <h2>Schedule a laundry pickup</h2>
    <?php include APP_PATH . '/views/partials/errors.php'; ?>
    <form method="post" data-validate data-order-form>
        <?= csrfField() ?>
        <div class="form-grid">
            <div>
                <label for="pickup_date">Pickup date</label>
                <input type="date" id="pickup_date" name="pickup_date" value="<?= val($old, 'pickup_date') ?>" required min="<?= e(date('Y-m-d')) ?>">
            </div>
            <div>
                <label for="delivery_date">Preferred delivery date</label>
                <input type="date" id="delivery_date" name="delivery_date" value="<?= val($old, 'delivery_date') ?>" required>
            </div>
        </div>
        <label for="address">Pickup address</label>
        <textarea id="address" name="address" rows="2" required minlength="5" maxlength="500"><?= val($old, 'address') ?></textarea>

        <h3>Laundry items</h3>
        <?php include APP_PATH . '/views/partials/item_rows.php'; ?>

        <div class="btn-row">
            <button type="submit" class="btn btn-primary">Submit order</button>
            <a class="btn btn-outline" href="<?= e(url('customer/index.php')) ?>">Cancel</a>
        </div>
    </form>
</section>

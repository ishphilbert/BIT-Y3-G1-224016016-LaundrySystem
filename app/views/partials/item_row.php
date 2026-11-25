<?php /* One laundry item row. Needs $row, $catalog and $priceEditable. */ ?>
<div class="item-row">
    <select name="item_type[]" class="item-type" required aria-label="Item type">
        <option value="">-- Select item --</option>
        <?php foreach ($catalog as $name => $price): ?>
            <option value="<?= e($name) ?>" data-price="<?= e($price) ?>"<?= ($row['item_type'] === $name) ? ' selected' : '' ?>>
                <?= e($name) ?> (<?= e(number_format($price, 2)) ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <input type="number" name="quantity[]" class="item-qty" min="1" max="1000" step="1" value="<?= e($row['quantity']) ?>" required aria-label="Quantity">
    <input type="number" name="unit_price[]" class="item-price" min="0" step="0.01" value="<?= e($row['unit_price']) ?>" <?= $priceEditable ? '' : 'readonly' ?> aria-label="Unit price">
    <span class="item-subtotal">0.00</span>
    <button type="button" class="btn btn-small btn-danger remove-item" aria-label="Remove item">&times;</button>
</div>

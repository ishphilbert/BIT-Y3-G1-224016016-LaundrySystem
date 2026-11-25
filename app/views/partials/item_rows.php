<?php /* Dynamic list of laundry item rows. Needs $rows, $catalog, $priceEditable. */ ?>
<div class="item-head">
    <span>Item type</span><span>Quantity</span><span>Unit price</span><span>Subtotal</span><span></span>
</div>
<div id="itemRows" data-price-editable="<?= $priceEditable ? '1' : '0' ?>">
    <?php foreach ($rows as $row): ?>
        <?php include APP_PATH . '/views/partials/item_row.php'; ?>
    <?php endforeach; ?>
</div>
<template id="itemRowTemplate">
    <?php $row = ['item_type' => '', 'quantity' => '1', 'unit_price' => '']; include APP_PATH . '/views/partials/item_row.php'; ?>
</template>
<p><button type="button" id="addItemRow" class="btn btn-outline btn-small">+ Add another item</button></p>
<p class="order-total">Order total: <strong><?= e(CURRENCY) ?> <span id="orderTotal">0.00</span></strong></p>

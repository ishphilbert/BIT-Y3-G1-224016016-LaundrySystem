<?php /* Read-only items table with subtotals and order total. Needs $items and $order. */ ?>
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Item</th><th class="num">Quantity</th><th class="num">Unit price</th><th class="num">Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <td><?= e($it['item_type']) ?></td>
                <td class="num"><?= (int) $it['quantity'] ?></td>
                <td class="num"><?= e(money($it['unit_price'])) ?></td>
                <td class="num"><?= e(money($it['subtotal'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><th colspan="3" class="num">Total</th><th class="num"><?= e(money($order['total'])) ?></th></tr></tfoot>
    </table>
</div>

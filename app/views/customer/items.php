<section class="card">
    <h2>Laundry items &amp; prices</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Item</th><th class="num">Price per item (<?= e(CURRENCY) ?>)</th></tr></thead>
            <tbody>
            <?php foreach ($catalog as $name => $price): ?>
                <tr><td><?= e($name) ?></td><td class="num"><?= e(number_format($price, 2)) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p><a class="btn btn-primary" href="<?= e(url('customer/new-order.php')) ?>">Schedule a pickup</a></p>
</section>

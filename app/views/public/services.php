<?php $catalog = require APP_PATH . '/config/items.php'; ?>
<section class="container section">
    <h1>Services &amp; prices</h1>
    <p class="lead">Choose any combination of items when you schedule a pickup.</p>
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
    <p><a class="btn btn-primary" href="<?= e(url('register.php')) ?>">Schedule a pickup</a></p>
</section>

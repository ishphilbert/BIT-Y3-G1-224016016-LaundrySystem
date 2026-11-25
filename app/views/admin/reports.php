<?php $totalOrders = 0; $totalRevenue = 0.0; foreach ($counts as $s => $r) { $totalOrders += $r['n']; if ($s !== 'cancelled') { $totalRevenue += $r['revenue']; } } ?>
<section class="card">
    <h2>Orders by status</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Status</th><th class="num">Orders</th><th class="num">Share</th><th class="num">Order value</th></tr></thead>
            <tbody>
            <?php foreach (orderStatuses() as $key => $label): ?>
                <?php $n = $counts[$key]['n'] ?? 0; $v = $counts[$key]['revenue'] ?? 0; ?>
                <tr>
                    <td><?= statusBadge($key) ?></td>
                    <td class="num"><?= (int) $n ?></td>
                    <td class="num"><?= $totalOrders > 0 ? e(number_format($n * 100 / $totalOrders, 1)) . '%' : '-' ?></td>
                    <td class="num"><?= e(money($v)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot><tr><th>Total</th><th class="num"><?= (int) $totalOrders ?></th><th></th><th class="num"><?= e(money($totalRevenue)) ?> <small>(excl. cancelled)</small></th></tr></tfoot>
        </table>
    </div>
</section>

<section class="card">
    <h2>Staff workload</h2>
    <p class="muted">Unassigned open orders: <strong><?= (int) $unassigned ?></strong></p>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Staff member</th><th class="num">Orders assigned</th><th class="num">Value</th></tr></thead>
            <tbody>
            <?php foreach ($workload as $w): ?>
                <tr><td><?= e($w['username']) ?></td><td class="num"><?= (int) $w['orders_count'] ?></td><td class="num"><?= e(money($w['orders_value'])) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$workload): ?><tr><td colspan="3" class="muted">No staff accounts.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

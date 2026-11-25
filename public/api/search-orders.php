<?php
/**
 * public/api/search-orders.php - JSON endpoint for the admin live order search.
 * GET ?q=<order id | customer name | status>
 * 200 JSON list | 400 bad input | 401 not logged in | 403 not admin | 405 wrong method | 500 server error
 */
require __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function jsonOut(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonOut(405, ['error' => 'Method not allowed.']);
}
if (!isLoggedIn()) {
    jsonOut(401, ['error' => 'Please log in.']);
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    jsonOut(403, ['error' => 'Unauthorized access.']);
}

$q = getStr('q');
if (strlen($q) > 50 || preg_match('/[\x00-\x1F]/', $q)) {
    jsonOut(400, ['error' => 'Search text must be 50 characters or fewer.']);
}
if ($q === '') {
    jsonOut(200, ['orders' => []]);
}

try {
    $rows   = Order::search(['q' => $q], 20);
    $orders = [];
    foreach ($rows as $o) {       // only non-sensitive fields are returned
        $orders[] = [
            'order_id'      => (int) $o['order_id'],
            'customer_name' => $o['customer_name'],
            'status'        => $o['status'],
            'status_label'  => statusLabel($o['status']),
            'pickup_date'   => formatDate($o['pickup_date']),
            'delivery_date' => formatDate($o['delivery_date']),
            'total'         => money($o['total']),
            'staff_name'    => $o['staff_name'] ?: 'Unassigned',
        ];
    }
    jsonOut(200, ['orders' => $orders]);
} catch (Throwable $e) {
    error_log('search-orders failed: ' . $e->getMessage());
    jsonOut(500, ['error' => 'Search is temporarily unavailable.']);
}

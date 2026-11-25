<?php
/** app/models/Order.php - database operations for the orders table. */
class Order
{
    private const SELECT = 'SELECT o.*, c.full_name AS customer_name, c.phone AS customer_phone,
                                   c.address AS customer_address, s.username AS staff_name
                            FROM orders o
                            JOIN customers c ON c.customer_id = o.customer_id
                            LEFT JOIN users s ON s.user_id = o.assigned_staff_id';

    public static function find(int $orderId)
    {
        $stmt = getConnection()->prepare(self::SELECT . ' WHERE o.order_id = :id LIMIT 1');
        $stmt->execute([':id' => $orderId]);
        return $stmt->fetch();
    }

    /**
     * Search/filter orders. Supported filters: q (order ID, customer name or status),
     * status, statuses (array), customer_id, staff_id.
     */
    public static function search(array $f = [], int $limit = 200): array
    {
        $where  = [];
        $params = [];

        if (!empty($f['customer_id'])) {
            $where[] = 'o.customer_id = :customer_id';
            $params[':customer_id'] = (int) $f['customer_id'];
        }
        if (!empty($f['staff_id'])) {
            $where[] = 'o.assigned_staff_id = :staff_id';
            $params[':staff_id'] = (int) $f['staff_id'];
        }
        if (!empty($f['status'])) {
            $where[] = 'o.status = :status';
            $params[':status'] = $f['status'];
        }
        if (!empty($f['statuses']) && is_array($f['statuses'])) {
            $holders = [];
            foreach (array_values($f['statuses']) as $i => $s) {
                $holders[] = ':st' . $i;
                $params[':st' . $i] = $s;
            }
            $where[] = 'o.status IN (' . implode(',', $holders) . ')';
        }
        if (isset($f['q']) && $f['q'] !== '') {
            $q = $f['q'];
            $where[] = '(o.order_id = :q_id OR c.full_name LIKE :q_name OR o.status LIKE :q_status)';
            $params[':q_id']     = ctype_digit($q) ? (int) $q : 0;
            $params[':q_name']   = likeTerm($q);
            $params[':q_status'] = likeTerm(str_replace(' ', '_', strtolower($q)));
        }

        $sql = self::SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . ' ORDER BY o.created_at DESC, o.order_id DESC LIMIT ' . (int) $limit;
        $stmt = getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Create an order and its items in ONE transaction. The total is calculated here
     * from the items. $address (optional) updates the customer's pickup address.
     */
    public static function create(int $customerId, ?int $staffId, string $pickup, string $delivery,
                                  array $items, string $status = 'pending', string $address = ''): int
    {
        $pdo = getConnection();
        $pdo->beginTransaction();
        try {
            $total = OrderItem::calculateTotal($items);
            $stmt = $pdo->prepare(
                'INSERT INTO orders (customer_id, assigned_staff_id, pickup_date, delivery_date, total, status)
                 VALUES (:cid, :sid, :pickup, :delivery, :total, :status)'
            );
            $stmt->execute([
                ':cid' => $customerId, ':sid' => $staffId, ':pickup' => $pickup,
                ':delivery' => $delivery, ':total' => $total, ':status' => $status,
            ]);
            $orderId = (int) $pdo->lastInsertId();
            OrderItem::insertMany($orderId, $items);

            if ($address !== '') {
                $a = $pdo->prepare('UPDATE customers SET address = :address WHERE customer_id = :id');
                $a->execute([':address' => $address, ':id' => $customerId]);
            }
            $pdo->commit();
            return $orderId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Update an order and replace its items in ONE transaction; total is recalculated. */
    public static function update(int $orderId, int $customerId, ?int $staffId, string $pickup,
                                  string $delivery, array $items, string $status): void
    {
        $pdo = getConnection();
        $pdo->beginTransaction();
        try {
            $total = OrderItem::calculateTotal($items);
            $stmt = $pdo->prepare(
                'UPDATE orders SET customer_id = :cid, assigned_staff_id = :sid, pickup_date = :pickup,
                        delivery_date = :delivery, total = :total, status = :status, updated_at = NOW()
                 WHERE order_id = :id'
            );
            $stmt->execute([
                ':cid' => $customerId, ':sid' => $staffId, ':pickup' => $pickup, ':delivery' => $delivery,
                ':total' => $total, ':status' => $status, ':id' => $orderId,
            ]);
            OrderItem::deleteForOrder($orderId);
            OrderItem::insertMany($orderId, $items);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateStatus(int $orderId, string $status): void
    {
        $stmt = getConnection()->prepare('UPDATE orders SET status = :status, updated_at = NOW() WHERE order_id = :id');
        $stmt->execute([':status' => $status, ':id' => $orderId]);
    }

    public static function assignStaff(int $orderId, ?int $staffId): void
    {
        $stmt = getConnection()->prepare('UPDATE orders SET assigned_staff_id = :sid, updated_at = NOW() WHERE order_id = :id');
        $stmt->execute([':sid' => $staffId, ':id' => $orderId]);
    }

    public static function delete(int $orderId): void
    {
        $stmt = getConnection()->prepare('DELETE FROM orders WHERE order_id = :id');
        $stmt->execute([':id' => $orderId]);
    }

    /**
     * Number of orders and revenue per status, optionally limited to one customer or staff member.
     * Returns [status => ['n' => int, 'revenue' => float]].
     */
    public static function statusCounts(?int $customerId = null, ?int $staffId = null): array
    {
        $sql    = 'SELECT status, COUNT(*) AS n, COALESCE(SUM(total), 0) AS revenue FROM orders WHERE 1 = 1';
        $params = [];
        if ($customerId !== null) {
            $sql .= ' AND customer_id = :cid';
            $params[':cid'] = $customerId;
        }
        if ($staffId !== null) {
            $sql .= ' AND assigned_staff_id = :sid';
            $params[':sid'] = $staffId;
        }
        $sql .= ' GROUP BY status';
        $stmt = getConnection()->prepare($sql);
        $stmt->execute($params);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[$row['status']] = ['n' => (int) $row['n'], 'revenue' => (float) $row['revenue']];
        }
        return $out;
    }

    /** Orders and value handled per staff member (for reports). */
    public static function staffWorkload(): array
    {
        $stmt = getConnection()->prepare(
            "SELECT u.username, COUNT(o.order_id) AS orders_count, COALESCE(SUM(o.total), 0) AS orders_value
             FROM users u
             LEFT JOIN orders o ON o.assigned_staff_id = u.user_id
             WHERE u.role = 'staff'
             GROUP BY u.user_id, u.username
             ORDER BY u.username"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function unassignedCount(): int
    {
        $stmt = getConnection()->prepare(
            "SELECT COUNT(*) FROM orders WHERE assigned_staff_id IS NULL AND status NOT IN ('cancelled','delivered')"
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}

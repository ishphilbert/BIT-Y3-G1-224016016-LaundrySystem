<?php
/** app/models/OrderItem.php - database operations for the order_items table. */
class OrderItem
{
    public static function forOrder(int $orderId): array
    {
        $stmt = getConnection()->prepare(
            'SELECT item_id, order_id, item_type, quantity, unit_price, (quantity * unit_price) AS subtotal
             FROM order_items WHERE order_id = :id ORDER BY item_id'
        );
        $stmt->execute([':id' => $orderId]);
        return $stmt->fetchAll();
    }

    /** Insert several items for an order (call inside a transaction). */
    public static function insertMany(int $orderId, array $items): void
    {
        $stmt = getConnection()->prepare(
            'INSERT INTO order_items (order_id, item_type, quantity, unit_price) VALUES (:oid, :type, :qty, :price)'
        );
        foreach ($items as $item) {
            $stmt->execute([
                ':oid'   => $orderId,
                ':type'  => $item['item_type'],
                ':qty'   => $item['quantity'],
                ':price' => $item['unit_price'],
            ]);
        }
    }

    public static function deleteForOrder(int $orderId): void
    {
        $stmt = getConnection()->prepare('DELETE FROM order_items WHERE order_id = :id');
        $stmt->execute([':id' => $orderId]);
    }

    /** Order total = sum of quantity x unit price. */
    public static function calculateTotal(array $items): float
    {
        $total = 0.0;
        foreach ($items as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }
        return round($total, 2);
    }
}

<?php
/** app/models/Customer.php - database operations for the customers table. */
class Customer
{
    public static function all(string $search = ''): array
    {
        $sql = 'SELECT c.customer_id, c.user_id, c.full_name, c.phone, c.address, u.username, u.email,
                       (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.customer_id) AS order_count
                FROM customers c
                JOIN users u ON u.user_id = c.user_id';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE c.full_name LIKE :q1 OR u.email LIKE :q2 OR c.phone LIKE :q3';
            $params[':q1'] = likeTerm($search);
            $params[':q2'] = likeTerm($search);
            $params[':q3'] = likeTerm($search);
        }
        $sql .= ' ORDER BY c.full_name';
        $stmt = getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $customerId)
    {
        $stmt = getConnection()->prepare(
            'SELECT c.customer_id, c.user_id, c.full_name, c.phone, c.address, u.username, u.email
             FROM customers c JOIN users u ON u.user_id = c.user_id
             WHERE c.customer_id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $customerId]);
        return $stmt->fetch();
    }

    public static function findByUserId(int $userId)
    {
        $stmt = getConnection()->prepare(
            'SELECT c.customer_id, c.user_id, c.full_name, c.phone, c.address, u.username, u.email
             FROM customers c JOIN users u ON u.user_id = c.user_id
             WHERE c.user_id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch();
    }

    public static function count(): int
    {
        return (int) getConnection()->query('SELECT COUNT(*) FROM customers')->fetchColumn();
    }

    /** Creates the user account and the customer profile together (one transaction). */
    public static function createWithUser(array $d, string $password): int
    {
        $pdo = getConnection();
        $pdo->beginTransaction();
        try {
            $userId = User::create($d['username'], $d['email'], $password, 'customer');
            $stmt = $pdo->prepare(
                'INSERT INTO customers (user_id, full_name, phone, address) VALUES (:uid, :name, :phone, :address)'
            );
            $stmt->execute([':uid' => $userId, ':name' => $d['full_name'], ':phone' => $d['phone'], ':address' => $d['address']]);
            $customerId = (int) $pdo->lastInsertId();
            $pdo->commit();
            return $customerId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Admin update: profile + account (password only when given). */
    public static function updateWithUser(int $customerId, array $d, string $newPassword = ''): void
    {
        $pdo = getConnection();
        $pdo->beginTransaction();
        try {
            $customer = self::find($customerId);
            if (!$customer) {
                throw new RuntimeException('Customer not found');
            }
            User::update((int) $customer['user_id'], $d['username'], $d['email'], $newPassword);
            $stmt = $pdo->prepare(
                'UPDATE customers SET full_name = :name, phone = :phone, address = :address WHERE customer_id = :id'
            );
            $stmt->execute([':name' => $d['full_name'], ':phone' => $d['phone'], ':address' => $d['address'], ':id' => $customerId]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Deleting the user also deletes the profile and (via foreign keys) the customer's orders and items. */
    public static function delete(int $customerId): void
    {
        $customer = self::find($customerId);
        if ($customer) {
            User::delete((int) $customer['user_id']);
        }
    }
}

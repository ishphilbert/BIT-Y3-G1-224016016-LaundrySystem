<?php
/** app/models/User.php - database operations for the users table (PDO prepared statements). */
class User
{
    /** Includes password_hash: only used by the login check. */
    public static function findByEmail(string $email)
    {
        $stmt = getConnection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /** Never returns the password hash. */
    public static function findById(int $id)
    {
        $stmt = getConnection()->prepare(
            'SELECT user_id, username, email, role, created_at FROM users WHERE user_id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function emailExists(string $email, int $excludeUserId = 0): bool
    {
        $stmt = getConnection()->prepare('SELECT COUNT(*) FROM users WHERE email = :email AND user_id <> :id');
        $stmt->execute([':email' => $email, ':id' => $excludeUserId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(string $username, string $email, string $password, string $role): int
    {
        $pdo  = getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :hash, :role)'
        );
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':hash'     => password_hash($password, PASSWORD_DEFAULT),
            ':role'     => $role,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** Update username/email, and the password only when $newPassword is not empty. */
    public static function update(int $id, string $username, string $email, string $newPassword = ''): void
    {
        $pdo = getConnection();
        if ($newPassword !== '') {
            $stmt = $pdo->prepare(
                'UPDATE users SET username = :username, email = :email, password_hash = :hash WHERE user_id = :id'
            );
            $stmt->execute([
                ':username' => $username, ':email' => $email,
                ':hash' => password_hash($newPassword, PASSWORD_DEFAULT), ':id' => $id,
            ]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET username = :username, email = :email WHERE user_id = :id');
            $stmt->execute([':username' => $username, ':email' => $email, ':id' => $id]);
        }
    }

    /** Returns the stored hash for a user (for "current password" checks). */
    public static function getHash(int $id): string
    {
        $stmt = getConnection()->prepare('SELECT password_hash FROM users WHERE user_id = :id');
        $stmt->execute([':id' => $id]);
        return (string) $stmt->fetchColumn();
    }

    public static function createPasswordReset(int $userId, string $tokenHash, string $expiresAt): void
    {
        $pdo = getConnection();
        $pdo->prepare('DELETE FROM password_resets WHERE user_id = :id OR expires_at < NOW()')->execute([':id' => $userId]);
        $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:id, :hash, :expires)');
        $stmt->execute([':id' => $userId, ':hash' => $tokenHash, ':expires' => $expiresAt]);
    }

    public static function findPasswordReset(string $tokenHash)
    {
        $stmt = getConnection()->prepare('SELECT user_id FROM password_resets WHERE token_hash = :hash AND expires_at > NOW() LIMIT 1');
        $stmt->execute([':hash' => $tokenHash]);
        return $stmt->fetch();
    }

    public static function resetPassword(int $userId, string $password, string $tokenHash): void
    {
        $pdo = getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE user_id = :id');
            $stmt->execute([':hash' => password_hash($password, PASSWORD_DEFAULT), ':id' => $userId]);
            $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token_hash = :hash');
            $stmt->execute([':hash' => $tokenHash]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function delete(int $id): void
    {
        $stmt = getConnection()->prepare('DELETE FROM users WHERE user_id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function all(string $role = '', string $search = ''): array
    {
        $sql    = 'SELECT user_id, username, email, role, created_at FROM users WHERE 1 = 1';
        $params = [];
        if ($role !== '') {
            $sql .= ' AND role = :role';
            $params[':role'] = $role;
        }
        if ($search !== '') {
            $sql .= ' AND (username LIKE :q1 OR email LIKE :q2)';
            $params[':q1'] = likeTerm($search);
            $params[':q2'] = likeTerm($search);
        }
        $sql .= ' ORDER BY role, username';
        $stmt = getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countByRole(string $role): int
    {
        $stmt = getConnection()->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $stmt->execute([':role' => $role]);
        return (int) $stmt->fetchColumn();
    }

    public static function staffList(): array
    {
        $stmt = getConnection()->prepare("SELECT user_id, username FROM users WHERE role = 'staff' ORDER BY username");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

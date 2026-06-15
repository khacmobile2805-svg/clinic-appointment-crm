<?php

/**
 * UserRepository — Người gác cổng dữ liệu bảng users (Week 8-1).
 * CHỈ truy vấn dữ liệu bằng prepared statement; không validate, không in HTML, không đọc $_POST.
 */
class UserRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function findActiveByEmail(string $email): ?array
    {
        // prepare() + named placeholder (:email): tách câu lệnh khỏi dữ liệu, chống SQL Injection.
        $stmt = $this->db->prepare(
            "SELECT id, name, email, password_hash, role, status
               FROM users
              WHERE email = :email
                AND status = 'active'
              LIMIT 1"
        );
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch();
        return $user ?: null;
    }
}

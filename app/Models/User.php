<?php
// app/Models/User.php

class User {
    private $conn;
    private $table = "users";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // جيب يوزر بالـ username
    public function findByUsername(string $username): ?array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table} WHERE username = ? AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    // اعمل login وسجّل الوقت
    public function updateLastLogin(int $userId): void {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET last_login = NOW() WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
    }
}
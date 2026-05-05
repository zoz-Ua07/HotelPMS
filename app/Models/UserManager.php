<?php
class UserManager {
    private $conn;
    private $table = "users";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // جيب كل الـ users
    public function getAll(): array {
        $stmt = $this->conn->query(
            "SELECT user_id, username, full_name, email, role, is_active, last_login 
             FROM {$this->table} ORDER BY created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // جيب user بالـ ID
    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    // ضيف user جديد
    public function create(array $data): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} 
             (username, password_hash, full_name, email, role) 
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['full_name'],
            $data['email'],
            $data['role']
        ]);
    }

    // عدّل user
    public function update(array $data): bool {
        // لو بعتولنا password جديد هنغيره، لو لأ هنسيبه
        if (!empty($data['password'])) {
            $stmt = $this->conn->prepare(
                "UPDATE {$this->table} 
                 SET full_name=?, email=?, role=?, is_active=?, password_hash=?
                 WHERE user_id=?"
            );
            return $stmt->execute([
                $data['full_name'],
                $data['email'],
                $data['role'],
                $data['is_active'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['user_id']
            ]);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE {$this->table} 
                 SET full_name=?, email=?, role=?, is_active=?
                 WHERE user_id=?"
            );
            return $stmt->execute([
                $data['full_name'],
                $data['email'],
                $data['role'],
                $data['is_active'],
                $data['user_id']
            ]);
        }
    }

    // امسح user
    public function delete(int $id): bool {
        $stmt = $this->conn->prepare(
            "DELETE FROM {$this->table} WHERE user_id = ?"
        );
        return $stmt->execute([$id]);
    }
}
<?php
class AuditLog {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public static function log(
        int    $actorId,
        string $action,
        string $entityType,
        int    $entityId,
        mixed  $oldValue = null,
        mixed  $newValue = null
    ): void {
        $conn = Database::getInstance()->getConnection();
        $stmt = $conn->prepare(
            "INSERT INTO audit_log 
             (actor_id, action, entity_type, entity_id, old_value, new_value, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $actorId,
            $action,
            $entityType,
            $entityId,
            $oldValue ? json_encode($oldValue) : null,
            $newValue ? json_encode($newValue) : null,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
        ]);
    }
}
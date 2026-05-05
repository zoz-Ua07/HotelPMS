<?php
// config/Database.php

class Database {
    private static $instance = null;
    private $conn;

    private $host = "localhost";
    private $db   = "hotel_pms_complete";
    private $user = "root";
    private $pass = "";

    // private constructor — مينفعش حد يعمل new Database()
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",
                $this->user,
                $this->pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die(json_encode(["error" => "DB Connection failed: " . $e->getMessage()]));
        }
    }

    // الطريقة الوحيدة للوصول للـ connection
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->conn;
    }
}
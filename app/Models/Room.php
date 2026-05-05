<?php
require_once __DIR__ . '/../../config/Database.php';

class Room {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── CREATE ──────────────────────────────────────────────────────────────
    public function create(array $data): bool {
        $sql = "INSERT INTO rooms (room_number, room_type, floor_number, capacity, base_rate, status, features)
                VALUES (:room_number, :room_type, :floor_number, :capacity, :base_rate, :status, :features)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':room_number'  => $data['room_number'],
            ':room_type'    => $data['room_type'],
            ':floor_number' => $data['floor_number'],
            ':capacity'     => $data['capacity'],
            ':base_rate'    => $data['base_rate'],
            ':status'       => $data['status'] ?? 'Clean',
            ':features'     => isset($data['features']) ? json_encode($data['features']) : null,
        ]);
    }

    // ── READ ALL (with optional search) ─────────────────────────────────────
    public function getAll(array $filters = []): array {
        $sql    = "SELECT * FROM rooms WHERE 1=1";
        $params = [];

        if (!empty($filters['room_type'])) {
            $sql .= " AND room_type = :room_type";
            $params[':room_type'] = $filters['room_type'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['floor_number'])) {
            $sql .= " AND floor_number = :floor_number";
            $params[':floor_number'] = $filters['floor_number'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND room_number LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY room_number ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── READ ONE ─────────────────────────────────────────────────────────────
    public function getById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE room_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ── UPDATE ───────────────────────────────────────────────────────────────
    public function update(int $id, array $data): bool {
        $sql = "UPDATE rooms
                SET room_number  = :room_number,
                    room_type    = :room_type,
                    floor_number = :floor_number,
                    capacity     = :capacity,
                    base_rate    = :base_rate,
                    status       = :status,
                    features     = :features
                WHERE room_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':room_number'  => $data['room_number'],
            ':room_type'    => $data['room_type'],
            ':floor_number' => $data['floor_number'],
            ':capacity'     => $data['capacity'],
            ':base_rate'    => $data['base_rate'],
            ':status'       => $data['status'],
            ':features'     => isset($data['features']) ? json_encode($data['features']) : null,
            ':id'           => $id,
        ]);
    }

    // ── DELETE ───────────────────────────────────────────────────────────────
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM rooms WHERE room_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ── UPDATE STATUS ────────────────────────────────────────────────────────
    public function updateStatus(int $id, string $status): bool {
        $allowed = ['Clean','Occupied','Dirty','InCleaning','Inspecting','Ready','OutOfOrder'];
        if (!in_array($status, $allowed)) return false;

        $stmt = $this->db->prepare("UPDATE rooms SET status = :status WHERE room_id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    // ── AVAILABILITY CHECK (used by AJAX + Reservation) ─────────────────────
    public function getAvailable(string $arrival, string $departure, string $roomType = ''): array {
        $sql = "SELECT r.*
                FROM rooms r
                WHERE r.status NOT IN ('OutOfOrder','Occupied')
                  AND r.room_id NOT IN (
                      SELECT res.room_id
                      FROM reservations res
                      WHERE res.state NOT IN ('Cancelled','NoShow','CheckedOut','FolioClosed')
                        AND res.arrival_date   < :departure
                        AND res.departure_date > :arrival
                  )";
        $params = [':arrival' => $arrival, ':departure' => $departure];

        if ($roomType !== '') {
            $sql .= " AND r.room_type = :room_type";
            $params[':room_type'] = $roomType;
        }

        $sql .= " ORDER BY r.room_number ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

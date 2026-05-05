<?php
require_once __DIR__ . '/../../config/Database.php';

class Reservation {
    private PDO $db;

    // ── Valid state transitions ───────────────────────────────────────────────
    private const TRANSITIONS = [
        'Inquiry'     => ['Confirmed', 'Cancelled'],
        'Confirmed'   => ['CheckedIn', 'NoShow', 'Cancelled'],
        'CheckedIn'   => ['CheckedOut'],
        'CheckedOut'  => ['FolioClosed'],
        'FolioClosed' => [],
        'NoShow'      => [],
        'Cancelled'   => [],
    ];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── CREATE ───────────────────────────────────────────────────────────────
    public function create(array $data): int|false {
        // Overbooking guard
        if ($this->isOverbooked($data['room_id'], $data['arrival_date'], $data['departure_date'])) {
            return false;   // caller should handle this
        }

        $sql = "INSERT INTO reservations
                    (guest_id, room_id, master_booking_id, state,
                     arrival_date, departure_date, arrival_time,
                     adults, children, special_requests,
                     credit_limit, vip_flag, created_by)
                VALUES
                    (:guest_id, :room_id, :master_booking_id, :state,
                     :arrival_date, :departure_date, :arrival_time,
                     :adults, :children, :special_requests,
                     :credit_limit, :vip_flag, :created_by)";

        $stmt = $this->db->prepare($sql);
        $ok   = $stmt->execute([
            ':guest_id'          => $data['guest_id'],
            ':room_id'           => $data['room_id'],
            ':master_booking_id' => $data['master_booking_id'] ?? null,
            ':state'             => $data['state']             ?? 'Confirmed',
            ':arrival_date'      => $data['arrival_date'],
            ':departure_date'    => $data['departure_date'],
            ':arrival_time'      => $data['arrival_time']      ?? null,
            ':adults'            => $data['adults']            ?? 1,
            ':children'          => $data['children']          ?? 0,
            ':special_requests'  => $data['special_requests']  ?? null,
            ':credit_limit'      => $data['credit_limit']      ?? 500.00,
            ':vip_flag'          => $data['vip_flag']          ?? 0,
            ':created_by'        => $data['created_by'],
        ]);
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    // ── WALK-IN: check-in immediately without prior reservation ──────────────
    public function createWalkIn(array $data): int|false {
        // Walk-ins skip Inquiry/Confirmed and go directly to CheckedIn
        $data['state'] = 'CheckedIn';
        $id = $this->create($data);
        if ($id) {
            // Immediately mark room Occupied
            $this->db->prepare("UPDATE rooms SET status='Occupied' WHERE room_id=:rid")
                     ->execute([':rid' => $data['room_id']]);
        }
        return $id;
    }

    // ── READ ALL (filters) ───────────────────────────────────────────────────
    public function getAll(array $filters = []): array {
        $sql = "SELECT res.*, 
                       CONCAT(g.first_name,' ',g.last_name) AS guest_name,
                       r.room_number, r.room_type
                FROM reservations res
                JOIN guests g ON g.guest_id = res.guest_id
                JOIN rooms  r ON r.room_id  = res.room_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['state'])) {
            $sql .= " AND res.state = :state";
            $params[':state'] = $filters['state'];
        }
        if (!empty($filters['arrival_date'])) {
            $sql .= " AND res.arrival_date = :arrival_date";
            $params[':arrival_date'] = $filters['arrival_date'];
        }
        if (!empty($filters['guest_name'])) {
            $sql .= " AND CONCAT(g.first_name,' ',g.last_name) LIKE :gname";
            $params[':gname'] = '%' . $filters['guest_name'] . '%';
        }
        if (!empty($filters['room_number'])) {
            $sql .= " AND r.room_number LIKE :rnum";
            $params[':rnum'] = '%' . $filters['room_number'] . '%';
        }

        $sql .= " ORDER BY res.arrival_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── READ ONE ─────────────────────────────────────────────────────────────
    public function getById(int $id): array|false {
        $sql = "SELECT res.*,
                       CONCAT(g.first_name,' ',g.last_name) AS guest_name,
                       g.email AS guest_email, g.phone AS guest_phone,
                       r.room_number, r.room_type, r.base_rate
                FROM reservations res
                JOIN guests g ON g.guest_id = res.guest_id
                JOIN rooms  r ON r.room_id  = res.room_id
                WHERE res.reservation_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ── UPDATE (reschedule) ──────────────────────────────────────────────────
    public function update(int $id, array $data): bool {
        // If dates change, re-check overbooking for other reservations
        if (!empty($data['arrival_date']) && !empty($data['departure_date'])) {
            if ($this->isOverbooked($data['room_id'], $data['arrival_date'], $data['departure_date'], $id)) {
                return false;
            }
        }

        $sql = "UPDATE reservations
                SET arrival_date     = :arrival_date,
                    departure_date   = :departure_date,
                    arrival_time     = :arrival_time,
                    adults           = :adults,
                    children         = :children,
                    special_requests = :special_requests,
                    room_id          = :room_id
                WHERE reservation_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':arrival_date'     => $data['arrival_date'],
            ':departure_date'   => $data['departure_date'],
            ':arrival_time'     => $data['arrival_time']     ?? null,
            ':adults'           => $data['adults']           ?? 1,
            ':children'         => $data['children']         ?? 0,
            ':special_requests' => $data['special_requests'] ?? null,
            ':room_id'          => $data['room_id'],
            ':id'               => $id,
        ]);
    }

    // ── STATE MACHINE: transition ─────────────────────────────────────────────
    /**
     * Attempt to move a reservation from its current state to $newState.
     * Returns true on success, throws InvalidArgumentException on invalid transition.
     */
    public function transition(int $id, string $newState, int $actorId): bool {
        $res = $this->getById($id);
        if (!$res) throw new InvalidArgumentException("Reservation $id not found.");

        $currentState = $res['state'];
        $allowed      = self::TRANSITIONS[$currentState] ?? [];

        if (!in_array($newState, $allowed)) {
            throw new InvalidArgumentException(
                "Cannot transition from '{$currentState}' to '{$newState}'."
            );
        }

        // Execute transition
        $stmt = $this->db->prepare(
            "UPDATE reservations SET state = :state WHERE reservation_id = :id"
        );
        $stmt->execute([':state' => $newState, ':id' => $id]);

        // Log the state change
        $log = $this->db->prepare(
            "INSERT INTO reservation_state_log
                (reservation_id, previous_state, new_state, triggered_by)
             VALUES (:res, :prev, :new, :actor)"
        );
        $log->execute([
            ':res'   => $id,
            ':prev'  => $currentState,
            ':new'   => $newState,
            ':actor' => $actorId,
        ]);

        // Side-effects per transition
        $this->handleSideEffects($res, $newState);
        return true;
    }

    // ── CHECK-IN ─────────────────────────────────────────────────────────────
    public function checkIn(int $id, int $actorId): bool {
        $res = $this->getById($id);
        $this->transition($id, 'CheckedIn', $actorId);

        // Mark room Occupied + log room status
        $this->db->prepare("UPDATE rooms SET status='Occupied' WHERE room_id=:rid")
                 ->execute([':rid' => $res['room_id']]);
        $this->logRoomStatus($res['room_id'], $res['status'] ?? 'Clean', 'Occupied', $actorId);

        // Create folio if none exists
        $folioCheck = $this->db->prepare(
            "SELECT folio_id FROM folios WHERE reservation_id=:rid LIMIT 1"
        );
        $folioCheck->execute([':rid' => $id]);
        if (!$folioCheck->fetch()) {
            $this->db->prepare(
                "INSERT INTO folios (reservation_id, guest_id) VALUES (:res, :g)"
            )->execute([':res' => $id, ':g' => $res['guest_id']]);
        }
        return true;
    }

    // ── CHECK-OUT ─────────────────────────────────────────────────────────────
    public function checkOut(int $id, int $actorId): bool {
        $res = $this->getById($id);
        $this->transition($id, 'CheckedOut', $actorId);

        // Mark room Dirty (needs housekeeping)
        $this->db->prepare("UPDATE rooms SET status='Dirty' WHERE room_id=:rid")
                 ->execute([':rid' => $res['room_id']]);
        $this->logRoomStatus($res['room_id'], 'Occupied', 'Dirty', $actorId);

        // Record stay_history
        $nights = (int)((strtotime($res['departure_date']) - strtotime($res['arrival_date'])) / 86400);
        $this->db->prepare(
            "INSERT INTO stay_history
                 (guest_id, reservation_id, nights, check_in_date, check_out_date)
             VALUES (:g, :r, :n, :ci, :co)"
        )->execute([
            ':g'  => $res['guest_id'],
            ':r'  => $id,
            ':n'  => max($nights, 1),
            ':ci' => $res['arrival_date'],
            ':co' => $res['departure_date'],
        ]);
        return true;
    }

    // ── CANCEL ───────────────────────────────────────────────────────────────
    public function cancel(int $id, int $actorId): bool {
        return $this->transition($id, 'Cancelled', $actorId);
    }

    // ── NO-SHOW DETECTION ────────────────────────────────────────────────────
    /**
     * Mark overdue Confirmed reservations as NoShow.
     * Called by a cron / daily job.
     */
    public function markNoShows(int $windowHours = 2): int {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$windowHours} hours"));
        $stmt   = $this->db->prepare(
            "SELECT reservation_id FROM reservations
             WHERE state = 'Confirmed'
               AND CONCAT(arrival_date, ' ', IFNULL(arrival_time,'23:59:59')) < :cutoff"
        );
        $stmt->execute([':cutoff' => $cutoff]);
        $ids   = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $count = 0;
        foreach ($ids as $rid) {
            try {
                $this->transition((int)$rid, 'NoShow', 1); // system user_id = 1
                $count++;
            } catch (Exception) {}
        }
        return $count;
    }

    // ── OVERBOOKING CHECK ─────────────────────────────────────────────────────
    /**
     * Returns true if the room is already booked for the given date range.
     * Pass $excludeId to ignore the current reservation (for reschedule).
     */
    public function isOverbooked(int $roomId, string $arrival, string $departure, int $excludeId = 0): bool {
        $sql = "SELECT COUNT(*) FROM reservations
                WHERE room_id = :room_id
                  AND reservation_id <> :exclude
                  AND state NOT IN ('Cancelled','NoShow','CheckedOut','FolioClosed')
                  AND arrival_date   < :departure
                  AND departure_date > :arrival";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':room_id'    => $roomId,
            ':exclude'    => $excludeId,
            ':arrival'    => $arrival,
            ':departure'  => $departure,
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────────
    private function handleSideEffects(array $res, string $newState): void {
        // Future: send email on Confirmed, create HK task on CheckedOut, etc.
    }

    private function logRoomStatus(int $roomId, string $prev, string $new, int $actorId): void {
        $this->db->prepare(
            "INSERT INTO room_status_log (room_id, previous_state, new_state, changed_by)
             VALUES (:r, :p, :n, :a)"
        )->execute([':r' => $roomId, ':p' => $prev, ':n' => $new, ':a' => $actorId]);
    }
}

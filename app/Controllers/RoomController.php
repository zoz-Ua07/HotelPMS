<?php
require_once __DIR__ . '/../Models/Room.php';
require_once __DIR__ . '/../../config/Database.php';

class RoomController {
    private Room $roomModel;
    private PDO  $db;

    public function __construct() {
        $this->roomModel = new Room();
        $this->db        = Database::getInstance()->getConnection();
    }

    // ── MAIN DISPATCHER ───────────────────────────────────────────────────────
    public function handleRequest(): void {
        $action = $_GET['action'] ?? 'index';
         require_once __DIR__ . '/../../middleware/auth.php';
    requireAuth(['Manager','FrontDesk','SalesManager']);

        // AJAX availability endpoint — returns JSON, no view needed
        if ($action === 'available') {
            $this->ajaxAvailability();
            return;
        }

        match ($action) {
            'index'  => $this->index(),
            'create' => $this->showCreateForm(),
            'store'  => $this->store(),
            'edit'   => $this->showEditForm((int)($_GET['id'] ?? 0)),
            'update' => $this->update((int)($_POST['room_id'] ?? 0)),
            'delete' => $this->delete((int)($_GET['id'] ?? 0)),
            default  => $this->index(),
        };
    }

    // ── LIST ──────────────────────────────────────────────────────────────────
    private function index(): void {
        $filters = [
            'room_type'    => $_GET['room_type']    ?? '',
            'status'       => $_GET['status']       ?? '',
            'floor_number' => $_GET['floor_number'] ?? '',
            'search'       => $_GET['search']       ?? '',
        ];
        $rooms = $this->roomModel->getAll($filters);
        require __DIR__ . '/../Views/rooms/index.php';
    }

    // ── CREATE FORM ───────────────────────────────────────────────────────────
    private function showCreateForm(): void {
        require __DIR__ . '/../Views/rooms/form.php';
    }

    // ── STORE (POST) ──────────────────────────────────────────────────────────
    private function store(): void {
        $errors = $this->validateRoomInput($_POST);
        if (!empty($errors)) {
            $room = $_POST;               // repopulate form
            require __DIR__ . '/../Views/rooms/form.php';
            return;
        }

        $this->roomModel->create($_POST);
        $this->logAction('CREATE_ROOM', 0, null, json_encode($_POST));
        header('Location: ?module=rooms&action=index&msg=created');
        exit;
    }

    // ── EDIT FORM ─────────────────────────────────────────────────────────────
    private function showEditForm(int $id): void {
        $room = $this->roomModel->getById($id);
        if (!$room) { header('Location: ?module=rooms&action=index&err=notfound'); exit; }
        require __DIR__ . '/../Views/rooms/form.php';
    }

    // ── UPDATE (POST) ─────────────────────────────────────────────────────────
    private function update(int $id): void {
        $errors = $this->validateRoomInput($_POST);
        if (!empty($errors)) {
            $room = array_merge($_POST, ['room_id' => $id]);
            require __DIR__ . '/../Views/rooms/form.php';
            return;
        }

        $old = $this->roomModel->getById($id);
        $this->roomModel->update($id, $_POST);
        $this->logAction('UPDATE_ROOM', $id, json_encode($old), json_encode($_POST));
        header('Location: ?module=rooms&action=index&msg=updated');
        exit;
    }

    // ── DELETE ────────────────────────────────────────────────────────────────
    private function delete(int $id): void {
        $old = $this->roomModel->getById($id);
        $this->roomModel->delete($id);
        $this->logAction('DELETE_ROOM', $id, json_encode($old), null);
        header('Location: ?module=rooms&action=index&msg=deleted');
        exit;
    }

    // ── AJAX: Available Rooms ─────────────────────────────────────────────────
    private function ajaxAvailability(): void {
        header('Content-Type: application/json');

        $arrival    = $_GET['arrival']    ?? '';
        $departure  = $_GET['departure']  ?? '';
        $room_type  = $_GET['room_type']  ?? '';

        if (!$arrival || !$departure || $arrival >= $departure) {
            echo json_encode(['success' => false, 'message' => 'Invalid dates']);
            exit;
        }

        $rooms = $this->roomModel->getAvailable($arrival, $departure, $room_type);
        echo json_encode(['success' => true, 'rooms' => $rooms]);
        exit;
    }

    // ── VALIDATION ────────────────────────────────────────────────────────────
    private function validateRoomInput(array $data): array {
        $errors = [];
        if (empty($data['room_number']))             $errors[] = 'Room number is required.';
        if (empty($data['room_type']))               $errors[] = 'Room type is required.';
        if (!isset($data['floor_number']) || $data['floor_number'] < 1)
                                                     $errors[] = 'Valid floor number is required.';
        if (!isset($data['base_rate']) || $data['base_rate'] <= 0)
                                                     $errors[] = 'Base rate must be positive.';
        return $errors;
    }

    // ── AUDIT LOG ─────────────────────────────────────────────────────────────
    private function logAction(string $action, int $entityId, ?string $old, ?string $new): void {
        $actorId = $_SESSION['user_id'] ?? 1;
        $ip      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt    = $this->db->prepare(
            "INSERT INTO audit_log (actor_id, action, entity_type, entity_id, old_value, new_value, ip_address)
             VALUES (:actor, :action, 'room', :entity, :old, :new, :ip)"
        );
        $stmt->execute([
            ':actor'  => $actorId,
            ':action' => $action,
            ':entity' => $entityId,
            ':old'    => $old,
            ':new'    => $new,
            ':ip'     => $ip,
        ]);
    }
}

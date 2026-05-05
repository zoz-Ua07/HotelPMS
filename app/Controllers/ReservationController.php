<?php
require_once __DIR__ . '/../Models/Reservation.php';
require_once __DIR__ . '/../Models/Room.php';
require_once __DIR__ . '/../../config/Database.php';

class ReservationController {
    private Reservation $resModel;
    private Room        $roomModel;
    private PDO         $db;

    public function __construct() {
        $this->resModel  = new Reservation();
        $this->roomModel = new Room();
        $this->db        = Database::getInstance()->getConnection();
    }

    // ── DISPATCHER ────────────────────────────────────────────────────────────
    public function handleRequest(): void {
        $action = $_GET['action'] ?? 'index';
         require_once __DIR__ . '/../../config/auth.php';
    requireAuth(['Manager','FrontDesk','SalesManager','RevenueManager']);

        match ($action) {
            'index'    => $this->index(),
            'create'   => $this->showCreateForm(),
            'store'    => $this->store(),
            'edit'     => $this->showEditForm((int)($_GET['id'] ?? 0)),
            'update'   => $this->updateReservation((int)($_POST['reservation_id'] ?? 0)),
            'checkin'  => $this->checkIn((int)($_GET['id'] ?? 0)),
            'checkout' => $this->checkOut((int)($_GET['id'] ?? 0)),
            'cancel'   => $this->cancel((int)($_GET['id'] ?? 0)),
            'walkin'   => $this->showWalkInForm(),
            'walkin_store' => $this->storeWalkIn(),
            default    => $this->index(),
        };
    }

    // ── LIST ──────────────────────────────────────────────────────────────────
    private function index(): void {
        $filters = [
            'state'        => $_GET['state']        ?? '',
            'arrival_date' => $_GET['arrival_date'] ?? '',
            'guest_name'   => $_GET['guest_name']   ?? '',
            'room_number'  => $_GET['room_number']  ?? '',
        ];
        $reservations = $this->resModel->getAll($filters);
        require __DIR__ . '/../Views/reservations/index.php';
    }

    // ── CREATE FORM ───────────────────────────────────────────────────────────
    private function showCreateForm(): void {
        $guests = $this->getGuestsList();
        $rooms  = $this->roomModel->getAll(['status' => 'Clean']);
        require __DIR__ . '/../Views/reservations/form.php';
    }

    // ── STORE ─────────────────────────────────────────────────────────────────
    private function store(): void {
        $errors = $this->validateInput($_POST);
        if (!empty($errors)) {
            $guests = $this->getGuestsList();
            $rooms  = $this->roomModel->getAll();
            $data   = $_POST;
            require __DIR__ . '/../Views/reservations/form.php';
            return;
        }

        $_POST['created_by'] = $_SESSION['user_id'] ?? 1;
        $id = $this->resModel->create($_POST);

        if ($id === false) {
            $errors[] = 'Room is already booked for the selected dates (overbooking prevented).';
            $guests   = $this->getGuestsList();
            $rooms    = $this->roomModel->getAll();
            $data     = $_POST;
            require __DIR__ . '/../Views/reservations/form.php';
            return;
        }

        header('Location: ?module=reservations&action=index&msg=created');
        exit;
    }

    // ── EDIT FORM ─────────────────────────────────────────────────────────────
    private function showEditForm(int $id): void {
        $reservation = $this->resModel->getById($id);
        if (!$reservation) { header('Location: ?module=reservations&action=index&err=notfound'); exit; }
        $guests = $this->getGuestsList();
        $rooms  = $this->roomModel->getAll();
        require __DIR__ . '/../Views/reservations/form.php';
    }

    // ── UPDATE (Reschedule) ───────────────────────────────────────────────────
    private function updateReservation(int $id): void {
        $errors = $this->validateInput($_POST);
        if (!empty($errors)) {
            $reservation = $this->resModel->getById($id);
            $guests      = $this->getGuestsList();
            $rooms       = $this->roomModel->getAll();
            $data        = $_POST;
            require __DIR__ . '/../Views/reservations/form.php';
            return;
        }

        $ok = $this->resModel->update($id, $_POST);
        if (!$ok) {
            $errors[] = 'Room is already booked for the selected dates.';
            $reservation = $this->resModel->getById($id);
            $guests = $this->getGuestsList();
            $rooms  = $this->roomModel->getAll();
            require __DIR__ . '/../Views/reservations/form.php';
            return;
        }

        header('Location: ?module=reservations&action=index&msg=updated');
        exit;
    }

    // ── CHECK-IN ─────────────────────────────────────────────────────────────
    private function checkIn(int $id): void {
        try {
            $this->resModel->checkIn($id, $_SESSION['user_id'] ?? 1);
            header('Location: ?module=reservations&action=index&msg=checkedin');
        } catch (InvalidArgumentException $e) {
            header('Location: ?module=reservations&action=index&err=' . urlencode($e->getMessage()));
        }
        exit;
    }

    // ── CHECK-OUT ─────────────────────────────────────────────────────────────
    private function checkOut(int $id): void {
        try {
            $this->resModel->checkOut($id, $_SESSION['user_id'] ?? 1);
            header('Location: ?module=reservations&action=index&msg=checkedout');
        } catch (InvalidArgumentException $e) {
            header('Location: ?module=reservations&action=index&err=' . urlencode($e->getMessage()));
        }
        exit;
    }

    // ── CANCEL ───────────────────────────────────────────────────────────────
    private function cancel(int $id): void {
        try {
            $this->resModel->cancel($id, $_SESSION['user_id'] ?? 1);
            header('Location: ?module=reservations&action=index&msg=cancelled');
        } catch (InvalidArgumentException $e) {
            header('Location: ?module=reservations&action=index&err=' . urlencode($e->getMessage()));
        }
        exit;
    }

    // ── WALK-IN FORM ─────────────────────────────────────────────────────────
    private function showWalkInForm(): void {
        $guests     = $this->getGuestsList();
        $rooms      = $this->roomModel->getAvailable(date('Y-m-d'), date('Y-m-d', strtotime('+1 day')));
        $is_walkin  = true;
        require __DIR__ . '/../Views/reservations/form.php';
    }

    // ── WALK-IN STORE ─────────────────────────────────────────────────────────
    private function storeWalkIn(): void {
        $_POST['arrival_date']   = date('Y-m-d');
        $_POST['departure_date'] = $_POST['departure_date'] ?? date('Y-m-d', strtotime('+1 day'));
        $_POST['created_by']     = $_SESSION['user_id'] ?? 1;

        $id = $this->resModel->createWalkIn($_POST);
        if ($id === false) {
            $errors    = ['Room unavailable for walk-in.'];
            $guests    = $this->getGuestsList();
            $rooms     = $this->roomModel->getAvailable(date('Y-m-d'), $_POST['departure_date']);
            $is_walkin = true;
            require __DIR__ . '/../Views/reservations/form.php';
            return;
        }
        header('Location: ?module=reservations&action=index&msg=walkin');
        exit;
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────
    private function validateInput(array $data): array {
        $errors = [];
        if (empty($data['guest_id']))       $errors[] = 'Guest is required.';
        if (empty($data['room_id']))        $errors[] = 'Room is required.';
        if (empty($data['arrival_date']))   $errors[] = 'Arrival date is required.';
        if (empty($data['departure_date'])) $errors[] = 'Departure date is required.';
        if (!empty($data['arrival_date']) && !empty($data['departure_date'])) {
            if ($data['arrival_date'] >= $data['departure_date'])
                $errors[] = 'Departure must be after arrival.';
        }
        return $errors;
    }

    private function getGuestsList(): array {
        return $this->db
            ->query("SELECT guest_id, CONCAT(first_name,' ',last_name) AS name FROM guests ORDER BY first_name")
            ->fetchAll();
    }
}

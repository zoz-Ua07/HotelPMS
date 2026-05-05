<?php
class UserController
{
    private $model;

    public function __construct()
    {
        require_once __DIR__ . '/../Models/UserManager.php';
        require_once __DIR__ . '/../Models/AuditLog.php';
        $this->model = new UserManager();
    }

    public function handleRequest(): void
    {
        $action = $_GET['action'] ?? 'index';

        match ($action) {
            'index'  => $this->index(),
            'create' => $this->create(),
            'edit'   => $this->edit(),
            'delete' => $this->delete(),
            default  => $this->index()
        };
    }

    private function index(): void
    {
        $users = $this->model->getAll();
        require __DIR__ . '/../Views/users/index.php';
    }

    private function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);

            // سجّل في الـ Audit Log
            AuditLog::log(
                actorId: $_SESSION['user_id'],
                action: 'CREATE_USER',
                entityType: 'users',
                entityId: 0,
                newValue: ['username' => $_POST['username'], 'role' => $_POST['role']]
            );

            header("Location: /hotelpms/public/index.php?url=/users");
            exit;
        }
        $users = $this->model->getAll();
        require __DIR__ . '/../Views/users/index.php';
    }

    private function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldUser = $this->model->getById($id);
            $this->model->update($_POST);

            // سجّل في الـ Audit Log
            AuditLog::log(
                actorId: $_SESSION['user_id'],
                action: 'UPDATE_USER',
                entityType: 'users',
                entityId: $id,
                oldValue: ['role' => $oldUser['role'], 'is_active' => $oldUser['is_active']],
                newValue: ['role' => $_POST['role'],   'is_active' => $_POST['is_active']]
            );

            header("Location: /hotelpms/public/index.php?url=/users");
            exit;
        }
        $editUser = $this->model->getById($id);
        $users    = $this->model->getAll();
        require __DIR__ . '/../Views/users/index.php';
    }

    private function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id !== (int)$_SESSION['user_id']) {
            $oldUser = $this->model->getById($id);
            $this->model->delete($id);

            // سجّل في الـ Audit Log
            AuditLog::log(
                actorId: $_SESSION['user_id'],
                action: 'DELETE_USER',
                entityType: 'users',
                entityId: $id,
                oldValue: ['username' => $oldUser['username'], 'role' => $oldUser['role']]
            );
        }
        header("Location: /hotelpms/public/index.php?url=/users");
        exit;
    }
}

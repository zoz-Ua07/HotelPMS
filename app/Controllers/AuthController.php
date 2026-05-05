<?php
// app/Controllers/AuthController.php
require_once __DIR__ . '/../Models/User.php';
class AuthController {

    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // اعرض صفحة الـ login
            require_once __DIR__ . '/../Views/auth/login.php';
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // validation بسيطة
        if (empty($username) || empty($password)) {
            $error = "Please fill in all fields.";
            require_once __DIR__ . '/../Views/auth/login.php';
            return;
        }

        $user = $this->userModel->findByUsername($username);

        // password_verify بيتحقق من الـ hash في الـ DB
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = "Invalid credentials.";
            require_once __DIR__ . '/../Views/auth/login.php';
            return;
        }

        // عمل الـ session
        session_start();
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];

        // سجّل آخر login
        $this->userModel->updateLastLogin($user['user_id']);

        // redirect حسب الـ role
        $this->redirectByRole($user['role']);
    }

    public function logout(): void {
        session_start();
        session_destroy();
        header("Location: /hotelpms/public/index.php");
        exit;
    }

private function redirectByRole(string $role): void {
    $routes = [
        'FrontDesk'      => '/hotel/public/index.php?module=dashboard&role=frontdesk',
        'Housekeeper'    => '/hotel/public/index.php?module=dashboard&role=housekeeping',
        'HKSupervisor'   => '/hotel/public/index.php?module=dashboard&role=housekeeping',
        'Accountant'     => '/hotel/public/index.php?module=dashboard&role=billing',
        'SalesManager'   => '/hotel/public/index.php?module=dashboard&role=reservations',
        'RevenueManager' => '/hotel/public/index.php?module=dashboard&role=revenue',
        'Manager'        => '/hotel/public/index.php?module=dashboard&role=manager',
    ];
    $path = $routes[$role] ?? '/hotel/public/index.php?module=dashboard';
    header("Location: /hotelpms/public/index.php?url=/dashboard");
    exit;
}
}
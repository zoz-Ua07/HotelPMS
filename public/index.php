<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/RoomController.php';
require_once __DIR__ . '/../app/Controllers/ReservationController.php';
$url = $_GET['url'] ?? '/';
$url = '/' . trim($url, '/');
$module = $_GET['module'] ?? null;
if ($module) {
    match ($module) {
        'rooms'        => (new RoomController())->handleRequest(),
        'reservations' => (new ReservationController())->handleRequest(),
        default        => http_response_code(404)
    };
    exit;
}
switch ($url) {
    case '/':
    case '/login':
        (new AuthController())->login();
        break;
    case '/logout':
        (new AuthController())->logout();
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
}

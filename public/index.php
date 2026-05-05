<?php
// public/index.php

session_start();

require_once '../config/Database.php';
require_once '../app/Models/User.php';
require_once '../app/Controllers/AuthController.php';

$url = $_GET['url'] ?? '/';
$url = '/' . trim($url, '/');

// Routing بسيط
switch ($url) {
    case '/':
    case '/login':
        (new AuthController())->login();
        break;

    case '/logout':
        (new AuthController())->logout();
        break;

    case '/dashboard':
        require_once '../app/Views/dashboard/index.php';
        break;

    case '/reservations':
        require_once '../app/Views/reservations/index.php';
        break;

    case '/rooms':
        require_once '../app/Views/rooms/index.php';
        break;

    case '/users':
        require_once '../app/Models/UserManager.php';
        require_once '../app/Controllers/UserController.php';
        (new UserController())->handleRequest();
        break;

    default:
        //default:
        $module = $_GET['module'] ?? null;
        if ($module) {
            require_once '../app/Controllers/RoomController.php';
            require_once '../app/Controllers/ReservationController.php';
            match ($module) {
                'rooms'        => (new RoomController())->handleRequest(),
                'reservations' => (new ReservationController())->handleRequest(),
                default        => http_response_code(404)
            };
            exit;
        }
        http_response_code(404);
        echo "404 Not Found";
}

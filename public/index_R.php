<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../app/Controllers/RoomController.php';
require_once __DIR__ . '/../app/Controllers/ReservationController.php';

$module = $_GET['module'] ?? 'reservations';

match ($module) {
    'rooms'        => (new RoomController())->handleRequest(),
    'reservations' => (new ReservationController())->handleRequest(),
    default        => (new ReservationController())->handleRequest(),
};

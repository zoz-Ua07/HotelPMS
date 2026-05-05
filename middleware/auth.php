<?php
function requireAuth(array $allowedRoles = []): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        header("Location: /");
        exit;
    }

    if (!empty($allowedRoles) && !in_array($_SESSION['role'], $allowedRoles)) {
        http_response_code(403);
        echo "<h1>403 — Access Denied</h1>";
        exit;
    }
}
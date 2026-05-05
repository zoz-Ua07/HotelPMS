<?php
require_once __DIR__ . '/../../../middleware/auth.php';
requireAuth();
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

$role = $_SESSION['role'];
$name = $_SESSION['full_name'];
?>

<!-- Topbar -->
<div class="topbar">
    <div style="font-family:'Cormorant Garamond',serif; font-size:20px">
        Dashboard
    </div>
    <div>
        <span class="badge-gold"><?= $role ?></span>
    </div>
</div>

<!-- Content -->
<div class="main-content">
    <h5 style="color:var(--muted); font-weight:300; margin-bottom:30px">
        Welcome back, <?= htmlspecialchars($name) ?>
    </h5>

    <div class="row g-4">

        <?php if (in_array($role, ['FrontDesk', 'Manager', 'SalesManager'])): ?>
        <div class="col-md-3">
            <div class="card p-4 text-center">
                <i class="bi bi-calendar-check" style="font-size:28px; color:var(--gold)"></i>
                <div class="card-title mt-2">Reservations</div>
                <div style="color:var(--muted); font-size:13px">Manage bookings</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 text-center">
                <i class="bi bi-people" style="font-size:28px; color:var(--gold)"></i>
                <div class="card-title mt-2">Guests</div>
                <div style="color:var(--muted); font-size:13px">Guest profiles</div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (in_array($role, ['Housekeeper', 'HKSupervisor', 'Manager'])): ?>
        <div class="col-md-3">
            <div class="card p-4 text-center">
                <i class="bi bi-stars" style="font-size:28px; color:var(--gold)"></i>
                <div class="card-title mt-2">HK Tasks</div>
                <div style="color:var(--muted); font-size:13px">Housekeeping</div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (in_array($role, ['Accountant', 'Manager'])): ?>
        <div class="col-md-3">
            <div class="card p-4 text-center">
                <i class="bi bi-receipt" style="font-size:28px; color:var(--gold)"></i>
                <div class="card-title mt-2">Billing</div>
                <div style="color:var(--muted); font-size:13px">Folios & payments</div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
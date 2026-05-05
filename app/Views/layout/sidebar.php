<?php
$role = $_SESSION['role'] ?? '';
$name = $_SESSION['full_name'] ?? 'User';
?>

<div class="sidebar d-flex flex-column">
    <div class="brand">LUMIÈRE</div>

    <nav class="flex-grow-1 py-3">

        <!-- كل الـ roles بيشوفوا Dashboard -->
        <div class="nav-section">Main</div>
        <a href="/dashboard" class="nav-link d-block">
            <i class="bi bi-grid"></i> Dashboard
        </a>

        <!-- Front Desk -->
        <?php if (in_array($role, ['FrontDesk', 'Manager', 'SalesManager'])): ?>
            <div class="nav-section">Front Desk</div>
            <a href="/hotelpms/public/index.php?url=/reservations" class="nav-link d-block">
                <i class="bi bi-calendar-check"></i> Reservations
            </a>
            <a href="/guests" class="nav-link d-block">
                <i class="bi bi-people"></i> Guests
            </a>
            <a href="/hotelpms/public/index.php?url=/rooms" class="nav-link d-block">
                <i class="bi bi-door-open"></i> Rooms
            </a>
        <?php endif; ?>

        <!-- Housekeeping -->
        <?php if (in_array($role, ['Housekeeper', 'HKSupervisor', 'Manager'])): ?>
            <div class="nav-section">Housekeeping</div>
            <a href="/housekeeping" class="nav-link d-block">
                <i class="bi bi-stars"></i> HK Tasks
            </a>
        <?php endif; ?>

        <!-- Billing -->
        <?php if (in_array($role, ['Accountant', 'Manager'])): ?>
            <div class="nav-section">Billing</div>
            <a href="/billing" class="nav-link d-block">
                <i class="bi bi-receipt"></i> Folios
            </a>
        <?php endif; ?>

        <!-- Manager Only -->
        <?php if ($role === 'Manager'): ?>
            <div class="nav-section">Management</div>
            <a href="/hotelpms/public/index.php?url=/users" class="nav-link d-block">
                <i class="bi bi-person-gear"></i> Staff Users
            </a>
            <a href="/reports" class="nav-link d-block">
                <i class="bi bi-bar-chart"></i> Reports
            </a>
        <?php endif; ?>

    </nav>

    <!-- اسم اليوزر وـ logout في الأسفل -->
    <div style="padding:16px 20px; border-top:1px solid var(--border)">
        <div style="font-size:12px; color:var(--muted)"><?= htmlspecialchars($name) ?></div>
        <div style="font-size:11px; color:var(--border); margin-bottom:8px"><?= $role ?></div>
        <a href="/hotelpms/public/index.php?url=/logout" style="font-size:12px; color:#E87070; text-decoration:none">
            <i class="bi bi-box-arrow-left"></i> Sign Out
        </a>
    </div>
</div>
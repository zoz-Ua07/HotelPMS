<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reservations — Hotel HMS</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/hotelpms/assets/css/theme.css">
<style>
  body { background:#f4f6f9; }
  .state-badge { font-size:.72rem; padding:.3em .6em; border-radius:.4rem; font-weight:700; }
  .state-Inquiry     { background:#e0f2fe; color:#0369a1; }
  .state-Confirmed   { background:#d1fae5; color:#065f46; }
  .state-CheckedIn   { background:#fef3c7; color:#92400e; }
  .state-CheckedOut  { background:#ede9fe; color:#5b21b6; }
  .state-FolioClosed { background:#e5e7eb; color:#374151; }
  .state-NoShow      { background:#fee2e2; color:#991b1b; }
  .state-Cancelled   { background:#fce7f3; color:#9d174d; }
</style>
</head>
<body>
<div class="container-fluid py-4">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2 text-primary"></i>Reservations</h4>
    <div class="d-flex gap-2">
      <a href="?module=reservations&action=walkin" class="btn btn-warning">
        <i class="bi bi-person-walking me-1"></i> Walk-In
      </a>
      <a href="?module=reservations&action=create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Reservation
      </a>
    </div>
  </div>

  <!-- Flash -->
  <?php
    $msgs = [
      'created'   => ['success','Reservation created.'],
      'updated'   => ['success','Reservation rescheduled.'],
      'checkedin' => ['success','Guest checked in ✓'],
      'checkedout'=> ['success','Guest checked out ✓'],
      'cancelled' => ['warning','Reservation cancelled.'],
      'walkin'    => ['success','Walk-in checked in ✓'],
    ];
    if (!empty($_GET['msg']) && isset($msgs[$_GET['msg']])): [$type, $text] = $msgs[$_GET['msg']]; ?>
    <div class="alert alert-<?= $type ?> alert-dismissible fade show">
      <?= $text ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if (!empty($_GET['err'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?= htmlspecialchars($_GET['err']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Filters -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="module" value="reservations">
        <input type="hidden" name="action" value="index">
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Guest Name</label>
          <input type="text" name="guest_name" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($_GET['guest_name'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Room #</label>
          <input type="text" name="room_number" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($_GET['room_number'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Arrival Date</label>
          <input type="date" name="arrival_date" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($_GET['arrival_date'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">State</label>
          <select name="state" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach (['Inquiry','Confirmed','CheckedIn','CheckedOut','FolioClosed','NoShow','Cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= (($_GET['state'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-sm btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
          <a href="?module=reservations&action=index" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover mb-0 align-middle small">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Guest</th>
            <th>Room</th>
            <th>Arrival</th>
            <th>Departure</th>
            <th>Guests</th>
            <th>State</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($reservations)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No reservations found.</td></tr>
          <?php else: foreach ($reservations as $r): ?>
            <tr>
              <td class="text-muted"><?= $r['reservation_id'] ?></td>
              <td class="fw-semibold"><?= htmlspecialchars($r['guest_name']) ?></td>
              <td><?= htmlspecialchars($r['room_number']) ?> <span class="text-muted">(<?= $r['room_type'] ?>)</span></td>
              <td><?= $r['arrival_date'] ?></td>
              <td><?= $r['departure_date'] ?></td>
              <td><?= $r['adults'] ?>A / <?= $r['children'] ?>C</td>
              <td><span class="state-badge state-<?= $r['state'] ?>"><?= $r['state'] ?></span></td>
              <td class="text-end">
                <?php if ($r['state'] === 'Confirmed'): ?>
                  <a href="?module=reservations&action=checkin&id=<?= $r['reservation_id'] ?>"
                     class="btn btn-sm btn-success me-1" title="Check In">
                    <i class="bi bi-box-arrow-in-right"></i>
                  </a>
                  <a href="?module=reservations&action=cancel&id=<?= $r['reservation_id'] ?>"
                     class="btn btn-sm btn-outline-warning me-1" title="Cancel"
                     onclick="return confirm('Cancel this reservation?')">
                    <i class="bi bi-x-circle"></i>
                  </a>
                <?php endif; ?>
                <?php if ($r['state'] === 'CheckedIn'): ?>
                  <a href="?module=reservations&action=checkout&id=<?= $r['reservation_id'] ?>"
                     class="btn btn-sm btn-danger me-1" title="Check Out"
                     onclick="return confirm('Confirm check-out?')">
                    <i class="bi bi-box-arrow-right"></i>
                  </a>
                <?php endif; ?>
                <?php if (in_array($r['state'], ['Inquiry','Confirmed'])): ?>
                  <a href="?module=reservations&action=edit&id=<?= $r['reservation_id'] ?>"
                     class="btn btn-sm btn-outline-primary" title="Edit / Reschedule">
                    <i class="bi bi-pencil"></i>
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

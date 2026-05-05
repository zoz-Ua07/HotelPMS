<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<base href="/hotelpms/public/index.php">
<title>Rooms — Hotel HMS</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/hotelpms/assets/css/theme.css">
<style>
  .status-badge { font-size:.75rem; padding:.35em .65em; border-radius:.4rem; font-weight:600; }
  .badge-Clean      { background:#d1fae5; color:#065f46; }
  .badge-Occupied   { background:#fef3c7; color:#92400e; }
  .badge-Dirty      { background:#fee2e2; color:#991b1b; }
  .badge-Ready      { background:#dbeafe; color:#1e40af; }
  .badge-OutOfOrder { background:#e5e7eb; color:#374151; }
  .badge-InCleaning { background:#ede9fe; color:#5b21b6; }
  .badge-Inspecting { background:#fce7f3; color:#9d174d; }
</style>
</head>
<body>
<div class="container-fluid py-4">
 
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-building me-2 text-primary"></i>Room Management</h4>
    <a href="?module=rooms&action=create" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> Add Room
    </a>
  </div>
 
  <!-- Flash messages -->
  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= match($_GET['msg']) {
            'created' => 'Room added successfully.',
            'updated' => 'Room updated successfully.',
            'deleted' => 'Room deleted.',
            default   => 'Done.',
         } ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
 
  <!-- Search / Filter -->
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
      <form method="GET" action="/hotelpms/public/index.php" class="row g-2 align-items-end">
        <input type="hidden" name="module" value="rooms">
        <input type="hidden" name="action" value="index">
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Room Number</label>
          <input type="text" name="search" class="form-control form-control-sm"
                 placeholder="e.g. 101" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Type</label>
          <select name="room_type" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach (['Single','Double','Suite'] as $t): ?>
              <option value="<?= $t ?>" <?= (($_GET['room_type'] ?? '') === $t) ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach (['Clean','Occupied','Dirty','InCleaning','Inspecting','Ready','OutOfOrder'] as $s): ?>
              <option value="<?= $s ?>" <?= (($_GET['status'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Floor</label>
          <input type="number" name="floor_number" class="form-control form-control-sm"
                 placeholder="e.g. 2" value="<?= htmlspecialchars($_GET['floor_number'] ?? '') ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-sm btn-primary w-100"><i class="bi bi-search me-1"></i>Search</button>
          <a href="?module=rooms&action=index" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
        </div>
      </form>
    </div>
  </div>
 
  <!-- Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Room #</th>
            <th>Type</th>
            <th>Floor</th>
            <th>Capacity</th>
            <th>Base Rate (EGP)</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rooms)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No rooms found.</td></tr>
          <?php else: foreach ($rooms as $room): ?>
            <tr>
              <td class="price-highlight"><?= htmlspecialchars($room['room_number']) ?></td>
              <td><?= $room['room_type'] ?></td>
              <td><?= $room['floor_number'] ?></td>
              <td><?= $room['capacity'] ?></td>
              <td><?= number_format($room['base_rate'], 2) ?></td>
              <td>
                <span class="status-badge badge-<?= $room['status'] ?>">
                  <?= $room['status'] ?>
                </span>
              </td>
              <td class="text-end">
                <a href="?module=rooms&action=edit&id=<?= $room['room_id'] ?>"
                   class="btn btn-sm btn-outline-primary me-1">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="?module=rooms&action=delete&id=<?= $room['room_id'] ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete room <?= htmlspecialchars($room['room_number']) ?>?')">
                  <i class="bi bi-trash"></i>
                </a>
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
</div>

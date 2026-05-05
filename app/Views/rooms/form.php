<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= isset($room['room_id']) ? 'Edit' : 'Add' ?> Room — Hotel HMS</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:600px">

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
      <h5 class="fw-bold">
        <i class="bi bi-door-open me-2 text-primary"></i>
        <?= isset($room['room_id']) ? 'Edit Room' : 'Add New Room' ?>
      </h5>
      <a href="?module=rooms&action=index" class="text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Back to Rooms
      </a>
    </div>
    <div class="card-body px-4 py-3">

      <!-- Errors -->
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php
        $isEdit = isset($room['room_id']);
        $action = $isEdit
          ? "?module=rooms&action=update"
          : "?module=rooms&action=store";
        $val = fn($k) => htmlspecialchars($room[$k] ?? $_POST[$k] ?? '');
      ?>

      <form method="POST" action="<?= $action ?>">
        <?php if ($isEdit): ?>
          <input type="hidden" name="room_id" value="<?= $room['room_id'] ?? '' ?>">
        <?php endif; ?>

        <div class="row g-3">
          <div class="col-6">
            <label class="form-label fw-semibold small">Room Number *</label>
            <input type="text" name="room_number" class="form-control"
                   value="<?= $val('room_number') ?>" required>
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold small">Floor *</label>
            <input type="number" name="floor_number" class="form-control" min="1"
                   value="<?= $val('floor_number') ?>" required>
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold small">Room Type *</label>
            <select name="room_type" class="form-select" required>
              <?php foreach (['Single','Double','Suite'] as $t): ?>
                <option value="<?= $t ?>" <?= ($val('room_type') === $t) ? 'selected' : '' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold small">Capacity *</label>
            <input type="number" name="capacity" class="form-control" min="1"
                   value="<?= $val('capacity') ?: 2 ?>" required>
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold small">Base Rate (EGP) *</label>
            <input type="number" name="base_rate" class="form-control" step="0.01" min="0"
                   value="<?= $val('base_rate') ?>" required>
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold small">Status</label>
            <select name="status" class="form-select">
              <?php foreach (['Clean','Occupied','Dirty','InCleaning','Inspecting','Ready','OutOfOrder'] as $s): ?>
                <option value="<?= $s ?>" <?= ($val('status') === $s) ? 'selected' : '' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Room' : 'Create Room' ?>
          </button>
          <a href="?module=rooms&action=index" class="btn btn-outline-secondary w-100">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

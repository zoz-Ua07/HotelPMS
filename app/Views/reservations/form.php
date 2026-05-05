<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>
  <?= isset($is_walkin) ? 'Walk-In' : (isset($reservation['reservation_id']) ? 'Reschedule' : 'New Reservation') ?>
  — Hotel HMS
</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
  body { background:#f4f6f9; }
  #availability-results .room-card { cursor:pointer; transition:border .15s; }
  #availability-results .room-card:hover,
  #availability-results .room-card.selected { border-color:#0d6efd !important; background:#eff6ff; }
  #ajax-spinner { display:none; }
</style>
</head>
<body>
<div class="container py-4" style="max-width:800px">

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4">
      <h5 class="fw-bold">
        <?php if (isset($is_walkin)): ?>
          <i class="bi bi-person-walking me-2 text-warning"></i>Walk-In Check-In
        <?php elseif (isset($reservation['reservation_id'])): ?>
          <i class="bi bi-calendar-event me-2 text-primary"></i>Reschedule Reservation
        <?php else: ?>
          <i class="bi bi-calendar-plus me-2 text-primary"></i>New Reservation
        <?php endif; ?>
      </h5>
      <a href="?module=reservations&action=index" class="text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Back
      </a>
    </div>
    <div class="card-body px-4">

      <!-- Errors -->
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <?php
        $isEdit = isset($reservation['reservation_id']);
        $isWalkIn = isset($is_walkin);
        $formAction = $isWalkIn   ? '?module=reservations&action=walkin_store'
                    : ($isEdit    ? '?module=reservations&action=update'
                                  : '?module=reservations&action=store');
        $val = fn($k) => htmlspecialchars($reservation[$k] ?? $data[$k] ?? '');
      ?>

      <form method="POST" action="<?= $formAction ?>">
        <?php if ($isEdit): ?>
          <input type="hidden" name="reservation_id" value="<?= $reservation['reservation_id'] ?? '' ?>">
        <?php endif; ?>

        <div class="row g-3">

          <!-- Guest -->
          <div class="col-md-6">
            <label class="form-label fw-semibold small">Guest *</label>
            <select name="guest_id" class="form-select" required>
              <option value="">— Select Guest —</option>
             <?php foreach ($guests ?? [] as $g): ?>
                <option value="<?= $g['guest_id'] ?>"
                  <?= ($val('guest_id') == $g['guest_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($g['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Adults / Children -->
          <div class="col-md-3">
            <label class="form-label fw-semibold small">Adults *</label>
            <input type="number" name="adults" class="form-control" min="1" value="<?= $val('adults') ?: 1 ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold small">Children</label>
            <input type="number" name="children" class="form-control" min="0" value="<?= $val('children') ?: 0 ?>">
          </div>

          <!-- Dates -->
          <?php if (!$isWalkIn): ?>
          <div class="col-md-4">
            <label class="form-label fw-semibold small">Arrival Date *</label>
            <input type="date" id="arrival_date" name="arrival_date" class="form-control"
                   value="<?= $val('arrival_date') ?>" min="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold small">Departure Date *</label>
            <input type="date" id="departure_date" name="departure_date" class="form-control"
                   value="<?= $val('departure_date') ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold small">Room Type Filter</label>
            <select id="filter_room_type" class="form-select">
              <option value="">Any</option>
              <option value="Single">Single</option>
              <option value="Double">Double</option>
              <option value="Suite">Suite</option>
            </select>
          </div>
          <?php else: ?>
            <input type="hidden" name="arrival_date" value="<?= date('Y-m-d') ?>">
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Departure Date *</label>
              <input type="date" id="departure_date" name="departure_date" class="form-control"
                     value="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                     min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
            </div>
          <?php endif; ?>

          <!-- ⚡ AJAX Room Availability Section -->
          <div class="col-12">
            <div class="d-flex align-items-center gap-2 mb-2">
              <button type="button" id="btn-check-avail" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-search me-1"></i>Check Available Rooms
              </button>
              <span id="ajax-spinner" class="text-muted small">
                <span class="spinner-border spinner-border-sm me-1"></span>Searching...
              </span>
            </div>

            <div id="availability-results" class="row g-2 mb-3"></div>

            <!-- Hidden field populated by JS on room selection -->
            <input type="hidden" name="room_id" id="selected_room_id"
                   value="<?= $val('room_id') ?>" required>
            <div id="selected-room-label" class="text-muted small"></div>
          </div>

          <!-- Special Requests -->
          <div class="col-12">
            <label class="form-label fw-semibold small">Special Requests</label>
            <textarea name="special_requests" class="form-control" rows="2"><?= $val('special_requests') ?></textarea>
          </div>

        </div><!-- /row -->

        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-lg me-1"></i>
            <?= $isWalkIn ? 'Check In Now' : ($isEdit ? 'Save Changes' : 'Create Reservation') ?>
          </button>
          <a href="?module=reservations&action=index" class="btn btn-outline-secondary w-100">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ⚡ AJAX Script -->
<script>
(function () {
  const btnCheck    = document.getElementById('btn-check-avail');
  const spinner     = document.getElementById('ajax-spinner');
  const resultsDiv  = document.getElementById('availability-results');
  const hiddenInput = document.getElementById('selected_room_id');
  const label       = document.getElementById('selected-room-label');

  // Pre-select room if editing
  <?php if (!empty($val('room_id'))): ?>
  label.textContent = 'Currently assigned room ID: <?= $val('room_id') ?>';
  <?php endif; ?>

  btnCheck.addEventListener('click', function () {
    const arrival   = document.getElementById('arrival_date')?.value
                    || '<?= date('Y-m-d') ?>';
    const departure = document.getElementById('departure_date')?.value;
    const roomType  = document.getElementById('filter_room_type')?.value || '';

    if (!departure || arrival >= departure) {
      alert('Please select valid arrival and departure dates first.');
      return;
    }

    // Show spinner
    spinner.style.display = 'inline-flex';
    resultsDiv.innerHTML  = '';
    btnCheck.disabled     = true;

    const url = `?module=rooms&action=available&arrival=${arrival}&departure=${departure}&room_type=${roomType}`;

    fetch(url)
      .then(r => r.json())
      .then(data => {
        spinner.style.display = 'none';
        btnCheck.disabled     = false;

        if (!data.success) {
          resultsDiv.innerHTML = `<div class="col-12"><div class="alert alert-warning mb-0">${data.message}</div></div>`;
          return;
        }

        if (data.rooms.length === 0) {
          resultsDiv.innerHTML = '<div class="col-12"><div class="alert alert-info mb-0">No rooms available for these dates.</div></div>';
          return;
        }

        data.rooms.forEach(room => {
          const card = document.createElement('div');
          card.className = 'col-md-4';
          card.innerHTML = `
            <div class="card room-card border h-100" data-id="${room.room_id}" data-num="${room.room_number}">
              <div class="card-body py-2 px-3">
                <div class="fw-bold text-primary">Room ${room.room_number}</div>
                <div class="small text-muted">${room.room_type} · Floor ${room.floor_number}</div>
                <div class="small">Capacity: ${room.capacity} · 
                  <span class="fw-semibold">EGP ${parseFloat(room.base_rate).toLocaleString()}/night</span>
                </div>
                <div class="small mt-1">
                  <span class="badge bg-success-subtle text-success">${room.status}</span>
                </div>
              </div>
            </div>`;

          card.querySelector('.room-card').addEventListener('click', function () {
            // Deselect all
            document.querySelectorAll('#availability-results .room-card').forEach(c => {
              c.classList.remove('selected');
            });
            this.classList.add('selected');
            hiddenInput.value = room.room_id;
            label.textContent = `Selected: Room ${room.room_number} (${room.room_type}) — EGP ${parseFloat(room.base_rate).toLocaleString()}/night`;
          });

          resultsDiv.appendChild(card);
        });
      })
      .catch(() => {
        spinner.style.display = 'none';
        btnCheck.disabled     = false;
        resultsDiv.innerHTML  = '<div class="col-12"><div class="alert alert-danger mb-0">Server error. Please try again.</div></div>';
      });
  });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

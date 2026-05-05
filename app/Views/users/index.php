<?php
require_once __DIR__ . '/../../../middleware/auth.php';
requireAuth(['Manager']);
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';
?>

<div class="topbar">
    <div style="font-family:'Cormorant Garamond',serif; font-size:20px">Staff Users</div>
    <button onclick="showAddModal()" class="btn btn-sm" 
        style="background:var(--gold);color:#000;font-size:12px;letter-spacing:1px">
        + Add Staff
    </button>
</div>

<div class="main-content">

    <?php if(isset($_GET['success'])): ?>
    <div class="alert" style="background:rgba(80,200,80,.1);border:1px solid rgba(80,200,80,.3);color:#80e080;padding:12px;margin-bottom:20px;border-radius:4px">
        ✅ Done successfully
    </div>
    <?php endif; ?>

    <!-- جدول الـ Users -->
    <div class="card p-0 overflow-hidden">
        <table class="table mb-0" style="color:var(--text)">
            <thead style="background:rgba(255,255,255,.03);font-size:11px;letter-spacing:1px;text-transform:uppercase;color:var(--muted)">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="py-3">Username</th>
                    <th class="py-3">Email</th>
                    <th class="py-3">Role</th>
                    <th class="py-3">Status</th>
                    <th class="py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach(($users ?? []) as $u): ?>
                <tr style="border-top:1px solid var(--border)">
                    <td class="px-4 py-3"><?= htmlspecialchars($u['full_name']) ?></td>
                    <td class="py-3"><?= htmlspecialchars($u['username']) ?></td>
                    <td class="py-3"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="py-3">
                        <span class="badge-gold"><?= $u['role'] ?></span>
                    </td>
                    <td class="py-3">
                        <?php if($u['is_active']): ?>
                            <span style="color:#80e080;font-size:12px">● Active</span>
                        <?php else: ?>
                            <span style="color:#e08080;font-size:12px">● Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3">
                        <button onclick="showEditModal(<?= htmlspecialchars(json_encode($u)) ?>)" 
                            class="btn btn-sm btn-outline-secondary btn-sm me-1" style="font-size:11px">
                            Edit
                        </button>
                        <?php if($u['user_id'] != $_SESSION['user_id']): ?>
                        <a href="/hotelpms/public/index.php?url=/users&action=delete&id=<?= $u['user_id'] ?>"
                           onclick="return confirm('Delete this user?')"
                           class="btn btn-sm" style="font-size:11px;background:rgba(220,60,60,.15);color:#e08080;border:1px solid rgba(220,60,60,.3)">
                            Delete
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center">
    <div style="background:var(--panel);border:1px solid var(--border);padding:32px;width:440px;border-radius:4px">
        <h5 style="font-family:'Cormorant Garamond',serif;color:var(--gold);margin-bottom:24px">Add Staff Member</h5>
        <form method="POST" action="/hotelpms/public/index.php?url=/users&action=create">
            <div class="mb-3">
                <label style="font-size:11px;letter-spacing:1px;color:var(--muted)">FULL NAME</label>
                <input type="text" name="full_name" required class="form-control mt-1"
                    style="background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text)">
            </div>
            <div class="mb-3">
                <label style="font-size:11px;letter-spacing:1px;color:var(--muted)">USERNAME</label>
                <input type="text" name="username" required class="form-control mt-1"
                    style="background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text)">
            </div>
            <div class="mb-3">
                <label style="font-size:11px;letter-spacing:1px;color:var(--muted)">EMAIL</label>
                <input type="email" name="email" required class="form-control mt-1"
                    style="background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text)">
            </div>
            <div class="mb-3">
                <label style="font-size:11px;letter-spacing:1px;color:var(--muted)">PASSWORD</label>
                <input type="password" name="password" required class="form-control mt-1"
                    style="background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text)">
            </div>
            <div class="mb-4">
                <label style="font-size:11px;letter-spacing:1px;color:var(--muted)">ROLE</label>
                <select name="role" required class="form-select mt-1"
                    style="background:var(--panel);border:1px solid var(--border);color:var(--text)">
                    <option value="FrontDesk">Front Desk</option>
                    <option value="Housekeeper">Housekeeper</option>
                    <option value="HKSupervisor">HK Supervisor</option>
                    <option value="Accountant">Accountant</option>
                    <option value="SalesManager">Sales Manager</option>
                    <option value="RevenueManager">Revenue Manager</option>
                    <option value="Manager">Manager</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn flex-grow-1"
                    style="background:var(--gold);color:#000;font-size:12px;letter-spacing:1px">
                    Add Member
                </button>
                <button type="button" onclick="hideAddModal()" class="btn btn-outline-secondary">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center">
    <div style="background:var(--panel);border:1px solid var(--border);padding:32px;width:440px;border-radius:4px">
        <h5 style="font-family:'Cormorant Garamond',serif;color:var(--gold);margin-bottom:24px">Edit Staff Member</h5>
        <form method="POST" id="editForm" action="/hotelpms/public/index.php?url=/users&action=edit">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="mb-3">
                <label style="font-size:11px;letter-spacing:1px;color:var(--muted)">FULL NAME</label>
                <input type="text" name="full_name" id="edit_full_name" required class="form-control mt-1"
                    style="background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text)">
            </div>
            <div class="mb-3">
                <label style="font-size:11px;letter-spacing:1px;color:var(--muted)">EMAIL</label>
                <input type="email" name="email" id="edit_email" required class="form-control mt-1"
                    style="background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text)">
            </div>
            <div class="mb-3">
                <label style="font-size:11px;letter-spacing:1px;color:var(--muted)">NEW PASSWORD <span style="color:var(--muted);font-size:10px">(Optional)</span></label>
                <input type="password" name="password" class="form-control mt-1"
                    style="background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text)">
            </div>
            <div class="mb-3">
                <label style="font-size:11px;letter-spacing:1px;color:var(--muted)">ROLE</label>
                <select name="role" id="edit_role" class="form-select mt-1"
                    style="background:var(--panel);border:1px solid var(--border);color:var(--text)">
                    <option value="FrontDesk">Front Desk</option>
                    <option value="Housekeeper">Housekeeper</option>
                    <option value="HKSupervisor">HK Supervisor</option>
                    <option value="Accountant">Accountant</option>
                    <option value="SalesManager">Sales Manager</option>
                    <option value="RevenueManager">Revenue Manager</option>
                    <option value="Manager">Manager</option>
                </select>
            </div>
            <div class="mb-4">
                <label style="font-size:11px;letter-spacing:1px;color:var(--muted)">STATUS</label>
                <select name="is_active" id="edit_is_active" class="form-select mt-1"
                    style="background:var(--panel);border:1px solid var(--border);color:var(--text)">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn flex-grow-1"
                    style="background:var(--gold);color:#000;font-size:12px;letter-spacing:1px">
                    Save Changes
                </button>
                <button type="button" onclick="hideEditModal()" class="btn btn-outline-secondary">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}
function hideAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
function showEditModal(user) {
    document.getElementById('edit_user_id').value  = user.user_id;
    document.getElementById('edit_full_name').value = user.full_name;
    document.getElementById('edit_email').value     = user.email;
    document.getElementById('edit_role').value      = user.role;
    document.getElementById('edit_is_active').value = user.is_active;
    document.getElementById('editForm').action = 
        '/hotelpms/public/index.php?url=/users&action=edit&id=' + user.user_id;
    document.getElementById('editModal').style.display = 'flex';
}
function hideEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
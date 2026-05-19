<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('admin');

$pdo = db();
$roleFilter = trim((string) ($_GET['role'] ?? 'all'));
$sql = 'SELECT id, email, name, role, recruiter_status, created_at FROM users WHERE 1=1';
$params = [];
if (in_array($roleFilter, ['job_seeker', 'recruiter', 'admin'], true)) {
    $sql .= ' AND role = ?';
    $params[] = $roleFilter;
}
$sql .= ' ORDER BY created_at DESC';
$st = $pdo->prepare($sql);
$st->execute($params);
$users = $st->fetchAll();

$pageTitle = 'Manage Users';
$activeNav = 'users';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-up">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
          <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">User Directory</h2>
          <p class="text-muted small mb-0">Manage all registered accounts across all roles.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <div class="input-group input-group-sm" style="width: 280px;">
            <span class="input-group-text bg-white border-end-0"><i class="ri-search-line text-muted"></i></span>
            <input type="search" class="form-control border-start-0" id="userSearchInput" placeholder="Search name, email or role...">
          </div>
          <form method="get" class="d-flex gap-1">
            <select name="role" class="form-select form-select-sm" style="width: auto; border-radius: 8px;" onchange="this.form.submit()">
              <option value="all" <?= $roleFilter === 'all' ? 'selected' : '' ?>>All Roles</option>
              <option value="job_seeker" <?= $roleFilter === 'job_seeker' ? 'selected' : '' ?>>Job Seekers</option>
              <option value="recruiter" <?= $roleFilter === 'recruiter' ? 'selected' : '' ?>>Recruiters</option>
              <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admins</option>
            </select>
          </form>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table-premium">
          <thead>
            <tr>
              <th>User</th>
              <th>Role</th>
              <th>Recruiter Status</th>
              <th>Joined Date</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="userTableBody">
            <?php foreach ($users as $u): ?>
              <?php
                $search = strtolower($u['name'] . ' ' . $u['email'] . ' ' . $u['role']);
                $roleClass = match($u['role']) {
                    'admin' => 'bg-indigo text-white',
                    'recruiter' => 'bg-primary-subtle text-primary',
                    default => 'bg-light text-dark'
                };
              ?>
              <tr data-search="<?= e($search) ?>">
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-light text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                      <?= e(strtoupper(substr($u['name'], 0, 1))) ?>
                    </div>
                    <div>
                      <div class="fw-bold small"><?= e($u['name']) ?></div>
                      <div class="text-muted" style="font-size: 0.75rem;"><?= e($u['email']) ?></div>
                    </div>
                  </div>
                </td>
                <td><span class="badge border <?= $roleClass ?> small px-2 py-1" style="border-radius: 6px;"><?= e(str_replace('_', ' ', $u['role'])) ?></span></td>
                <td>
                  <?php if ($u['role'] === 'recruiter'): ?>
                    <span class="badge-admin badge-<?= e($u['recruiter_status'] ?? 'pending') ?>"><?= e($u['recruiter_status']) ?></span>
                  <?php else: ?>
                    <span class="text-muted small">—</span>
                  <?php endif; ?>
                </td>
                <td class="small text-muted"><?= date('M d, Y', strtotime((string)$u['created_at'])) ?></td>
                <td class="text-end">
                   <button class="btn btn-sm btn-light border" title="Edit User"><i class="ri-edit-line"></i></button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('userSearchInput').addEventListener('input', function(e) {
  const term = e.target.value.toLowerCase();
  document.querySelectorAll('#userTableBody tr').forEach(row => {
    row.style.display = row.getAttribute('data-search').includes(term) ? '' : 'none';
  });
});
</script>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

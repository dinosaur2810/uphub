<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('admin');

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
        redirect('admin/social-services.php');
    }
    $act = (string) ($_POST['ss_action'] ?? '');
    if ($act === 'add') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $desc = trim((string) ($_POST['description'] ?? ''));
        $addr = trim((string) ($_POST['exact_address'] ?? ''));
        $cat = trim((string) ($_POST['category'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $lat = ($_POST['latitude'] ?? '') !== '' ? (float) $_POST['latitude'] : null;
        $lng = ($_POST['longitude'] ?? '') !== '' ? (float) $_POST['longitude'] : null;
        if ($name === '') {
            flash_set('warning', 'Name is required.');
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO social_services (name, description, exact_address, category, phone, latitude, longitude, posted_by_user_id, moderation_status) VALUES (?,?,?,?,?,?,?,NULL,?)'
            );
            $ins->execute([$name, $desc, $addr, $cat, $phone, $lat, $lng, 'published']);
            flash_set('success', 'Service added.');
        }
    } elseif ($act === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $desc = trim((string) ($_POST['description'] ?? ''));
        $addr = trim((string) ($_POST['exact_address'] ?? ''));
        $cat = trim((string) ($_POST['category'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $lat = ($_POST['latitude'] ?? '') !== '' ? (float) $_POST['latitude'] : null;
        $lng = ($_POST['longitude'] ?? '') !== '' ? (float) $_POST['longitude'] : null;
        $mod = (string) ($_POST['moderation_status'] ?? 'published');
        if (!in_array($mod, ['pending_approval', 'published', 'rejected'], true)) {
            $mod = 'published';
        }
        if ($name === '') {
            flash_set('warning', 'Name is required.');
        } else {
            $pdo->prepare(
                'UPDATE social_services SET name=?, description=?, exact_address=?, category=?, phone=?, latitude=?, longitude=?, moderation_status=? WHERE id=?'
            )->execute([$name, $desc, $addr, $cat, $phone, $lat, $lng, $mod, $id]);
            flash_set('success', 'Service updated.');
        }
    } elseif ($act === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM social_services WHERE id = ?')->execute([$id]);
        flash_set('success', 'Service removed.');
    }
    redirect('admin/social-services.php');
}

$services = $pdo->query(
    'SELECT s.*, rp.company_name AS poster_org FROM social_services s
     LEFT JOIN recruiter_profiles rp ON rp.user_id = s.posted_by_user_id
     ORDER BY s.name'
)->fetchAll();

$pageTitle = 'Social Services Management';
$activeNav = 'services';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<p class="text-muted small">Latitude/longitude enable Google Maps embeds for job seekers when <code>GOOGLE_MAPS_API_KEY</code> is set.</p>
<div class="row g-4 mt-2">
  <div class="col-lg-4">
    <div class="card-premium-admin p-4 reveal reveal-up">
      <div class="mb-4">
        <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Add New Service</h2>
        <p class="text-muted small mb-0">Create a new social service entry for the public directory.</p>
      </div>
      <form method="post">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="ss_action" value="add">
        
        <div class="mb-3">
          <label class="form-label small fw-bold">Service Name</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Hope Food Bank" required>
        </div>
        
        <div class="mb-3">
          <label class="form-label small fw-bold">Description</label>
          <textarea name="description" class="form-control" rows="3" placeholder="What does this service provide?"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-bold">Address / Street</label>
          <input type="text" name="exact_address" class="form-control" placeholder="Tondo, Manila">
        </div>

        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label small fw-bold">Category</label>
            <input type="text" name="category" class="form-control" placeholder="Health, Food...">
          </div>
          <div class="col-6">
            <label class="form-label small fw-bold">Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="Contact number">
          </div>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-6">
            <label class="form-label small fw-bold text-muted">Latitude</label>
            <input type="text" name="latitude" class="form-control form-control-sm">
          </div>
          <div class="col-6">
            <label class="form-label small fw-bold text-muted">Longitude</label>
            <input type="text" name="longitude" class="form-control form-control-sm">
          </div>
        </div>

        <button type="submit" class="btn btn-indigo w-100 py-2">Add to Directory</button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card-premium-admin p-4 reveal reveal-up">
      <div class="mb-4">
        <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Directory Management</h2>
        <p class="text-muted small mb-0">Manage existing social services and review submissions.</p>
      </div>

      <div class="table-responsive">
        <table class="table-premium">
          <thead>
            <tr>
              <th>Service Details</th>
              <th>Context</th>
              <th>Visibility</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($services as $s): ?>
              <?php
                $statusBadge = match($s['moderation_status'] ?? 'published') {
                    'published' => 'approved',
                    'pending_approval' => 'pending',
                    'rejected' => 'rejected',
                    default => 'approved'
                };
              ?>
              <tr>
                <td>
                  <div class="fw-bold small"><?= e($s['name']) ?></div>
                  <div class="text-muted small"><?= e((string)$s['category']) ?></div>
                </td>
                <td>
                  <div class="small fw-semibold"><?= !empty($s['posted_by_user_id']) ? 'Recruiter' : 'Internal' ?></div>
                  <?php if (!empty($s['poster_org'])): ?>
                    <div class="text-muted" style="font-size: 0.7rem;"><?= e($s['poster_org']) ?></div>
                  <?php endif; ?>
                </td>
                <td><span class="badge-admin badge-<?= $statusBadge ?>"><?= e(str_replace('_', ' ', $s['moderation_status'] ?? 'published')) ?></span></td>
                <td class="text-end">
                  <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="collapse" data-bs-target="#editSS<?= $s['id'] ?>">
                      <i class="ri-edit-line"></i>
                    </button>
                    <form method="post" onsubmit="return confirm('Delete this service?');" class="d-inline">
                      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="ss_action" value="delete">
                      <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-light border text-danger">
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <tr class="collapse" id="editSS<?= $s['id'] ?>">
                <td colspan="4" class="p-0 border-0">
                  <div class="px-4 py-3 bg-light/50 border-bottom">
                    <form method="post" class="row g-3">
                      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="ss_action" value="edit">
                      <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                      
                      <div class="col-md-6">
                        <label class="form-label small fw-bold">Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" value="<?= e($s['name']) ?>" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label small fw-bold">Moderation</label>
                        <select name="moderation_status" class="form-select form-select-sm">
                          <option value="pending_approval" <?= ($s['moderation_status'] ?? '') === 'pending_approval' ? 'selected' : '' ?>>Pending approval</option>
                          <option value="published" <?= ($s['moderation_status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published</option>
                          <option value="rejected" <?= ($s['moderation_status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                      </div>
                      <div class="col-12">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2"><?= e((string) $s['description']) ?></textarea>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label small fw-bold">Address</label>
                        <input type="text" name="exact_address" class="form-control form-control-sm" value="<?= e((string) ($s['exact_address'] ?? '')) ?>">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label small fw-bold">Category</label>
                        <input type="text" name="category" class="form-control form-control-sm" value="<?= e((string) $s['category']) ?>">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label small fw-bold">Phone</label>
                        <input type="text" name="phone" class="form-control form-control-sm" value="<?= e((string) $s['phone']) ?>">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label small fw-bold">Latitude</label>
                        <input type="text" name="latitude" class="form-control form-control-sm" value="<?= $s['latitude'] !== null ? e((string) $s['latitude']) : '' ?>">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label small fw-bold">Longitude</label>
                        <input type="text" name="longitude" class="form-control form-control-sm" value="<?= $s['longitude'] !== null ? e((string) $s['longitude']) : '' ?>">
                      </div>
                      <div class="col-12 text-end">
                        <button type="submit" class="btn btn-sm btn-indigo px-4">Save Changes</button>
                      </div>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

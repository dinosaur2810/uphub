<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('admin');

$pdo = db();
$aid = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
        redirect('admin/financial-aid.php');
    }
    $act = (string) ($_POST['fa_action'] ?? '');
    if ($act === 'add') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $desc = trim((string) ($_POST['description'] ?? ''));
        $elig = trim((string) ($_POST['eligibility'] ?? ''));
        $contact = trim((string) ($_POST['contact_info'] ?? ''));
        $exactAddr = trim((string) ($_POST['exact_address'] ?? ''));
        $status = (string) ($_POST['status'] ?? 'active');
        if ($title === '') {
            flash_set('warning', 'Title is required.');
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO financial_aid_programs (title, description, eligibility, contact_info, exact_address, status, created_by, posted_by_user_id, moderation_status) VALUES (?,?,?,?,?,?,?,NULL,?)'
            );
            $ins->execute([$title, $desc, $elig, $contact, $exactAddr, $status === 'inactive' ? 'inactive' : 'active', $aid, 'published']);
            flash_set('success', 'Program added.');
        }
    } elseif ($act === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $desc = trim((string) ($_POST['description'] ?? ''));
        $elig = trim((string) ($_POST['eligibility'] ?? ''));
        $contact = trim((string) ($_POST['contact_info'] ?? ''));
        $exactAddr = trim((string) ($_POST['exact_address'] ?? ''));
        $status = (string) ($_POST['status'] ?? 'active');
        $mod = (string) ($_POST['moderation_status'] ?? 'published');
        if (!in_array($mod, ['pending_approval', 'published', 'rejected'], true)) {
            $mod = 'published';
        }
        if ($title === '') {
            flash_set('warning', 'Title is required.');
        } else {
            $pdo->prepare(
                'UPDATE financial_aid_programs SET title=?, description=?, eligibility=?, contact_info=?, exact_address=?, status=?, moderation_status=? WHERE id=?'
            )->execute([$title, $desc, $elig, $contact, $exactAddr, $status === 'inactive' ? 'inactive' : 'active', $mod, $id]);
            flash_set('success', 'Program updated.');
        }
    } elseif ($act === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM financial_aid_programs WHERE id = ?')->execute([$id]);
        flash_set('success', 'Program removed.');
    }
    redirect('admin/financial-aid.php');
}

$programs = $pdo->query(
    'SELECT f.*, rp.company_name AS poster_org FROM financial_aid_programs f
     LEFT JOIN recruiter_profiles rp ON rp.user_id = f.posted_by_user_id
     ORDER BY f.title'
)->fetchAll();

$pageTitle = 'Financial Aid Management';
$activeNav = 'aid';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mt-2">
  <div class="col-lg-5">
    <div class="card-premium-admin p-4 reveal reveal-up">
      <div class="mb-4">
        <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Add New Program</h2>
        <p class="text-muted small mb-0">List a new financial assistance resource.</p>
      </div>
      <form method="post">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="fa_action" value="add">
        <div class="mb-3">
          <label class="form-label small fw-bold">Program Title</label>
          <input type="text" name="title" class="form-control" placeholder="e.g. Emergency Rent Assistance" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-bold">Description</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Provide details about the help offered..."></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-bold">Eligibility Criteria</label>
          <textarea name="eligibility" class="form-control" rows="2" placeholder="Who can apply?"></textarea>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="form-label small fw-bold">Contact Info</label>
            <input type="text" name="contact_info" class="form-control" placeholder="Email or Phone">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Status</label>
            <select name="status" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-bold">Exact Address (Optional)</label>
          <input type="text" name="exact_address" class="form-control" placeholder="Physical location if applicable">
        </div>
        <button type="submit" class="btn btn-indigo w-100 py-2 mt-2">
          <i class="ri-add-circle-line me-1"></i> Create Program
        </button>
      </form>
    </div>
  </div>
  
  <div class="col-lg-7">
    <div class="card-premium-admin p-4 reveal reveal-up">
      <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
          <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Active Programs</h2>
          <p class="text-muted small mb-0">Total: <?= count($programs) ?> listings</p>
        </div>
      </div>
      
      <div class="table-responsive">
        <table class="table-premium">
          <thead>
            <tr>
              <th>Program Details</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($programs as $p): ?>
              <tr>
                <td>
                  <details class="premium-details">
                    <summary class="fw-bold small py-1 cursor-pointer">
                      <i class="ri-hand-coin-line me-1 text-indigo"></i> <?= e($p['title']) ?>
                      <?php if (!empty($p['posted_by_user_id'])): ?>
                        <span class="badge bg-light text-muted border ms-2" style="font-size: 0.65rem;">Partner Posted</span>
                      <?php endif; ?>
                    </summary>
                    <div class="p-3 bg-light rounded-3 mt-2 border">
                      <form method="post">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="fa_action" value="edit">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <!-- Inline Edit Form -->
                        <div class="mb-2">
                          <label class="form-label small fw-bold">Title</label>
                          <input type="text" name="title" class="form-control form-control-sm" value="<?= e($p['title']) ?>" required>
                        </div>
                        <div class="mb-2">
                          <label class="form-label small fw-bold">Description</label>
                          <textarea name="description" class="form-control form-control-sm" rows="2"><?= e((string) $p['description']) ?></textarea>
                        </div>
                        <div class="mb-2">
                          <label class="form-label small fw-bold">Eligibility</label>
                          <textarea name="eligibility" class="form-control form-control-sm" rows="2"><?= e((string) $p['eligibility']) ?></textarea>
                        </div>
                        <div class="row g-2 mb-2">
                           <div class="col-6">
                             <label class="form-label small fw-bold">Status</label>
                             <select name="status" class="form-select form-select-sm">
                               <option value="active" <?= $p['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                               <option value="inactive" <?= $p['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                             </select>
                           </div>
                           <div class="col-6">
                             <label class="form-label small fw-bold">Moderation</label>
                             <select name="moderation_status" class="form-select form-select-sm">
                               <option value="pending_approval" <?= ($p['moderation_status'] ?? '') === 'pending_approval' ? 'selected' : '' ?>>Pending</option>
                               <option value="published" <?= ($p['moderation_status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published</option>
                               <option value="rejected" <?= ($p['moderation_status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                             </select>
                           </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                          <button type="submit" class="btn btn-sm btn-indigo px-3">Update</button>
                          <button type="button" class="btn btn-sm btn-light border px-3" onclick="this.closest('details').open = false">Cancel</button>
                        </div>
                      </form>
                    </div>
                  </details>
                </td>
                <td>
                  <span class="badge-admin badge-<?= ($p['status'] === 'active' && ($p['moderation_status'] ?? 'published') === 'published') ? 'approved' : 'rejected' ?>">
                    <?= e($p['status']) ?>
                  </span>
                </td>
                <td class="text-end">
                  <div class="d-flex justify-content-end gap-1">
                    <form method="post" onsubmit="return confirm('Delete this program?');" class="d-inline">
                      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="fa_action" value="delete">
                      <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
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

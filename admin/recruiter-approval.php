<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('admin');

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
        redirect('admin/recruiter-approval.php');
    }
    $action = (string) ($_POST['approval_action'] ?? '');
    $uid = (int) ($_POST['user_id'] ?? 0);
    $st = $pdo->prepare("SELECT id, email, name FROM users WHERE id = ? AND role = 'recruiter' AND recruiter_status = 'pending' LIMIT 1");
    $st->execute([$uid]);
    $u = $st->fetch();
    if ($u) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE users SET recruiter_status = 'approved' WHERE id = ?")->execute([$uid]);
            notify_user($pdo, $uid, 'Your recruiter account has been approved. You can post jobs.', 'success');
            send_notification_email($u['email'], 'Recruiter approved', 'Your UpLiftHub recruiter account is approved.');
            flash_set('success', 'Recruiter approved.');
        } elseif ($action === 'reject') {
            $pdo->prepare("UPDATE users SET recruiter_status = 'rejected' WHERE id = ?")->execute([$uid]);
            notify_user($pdo, $uid, 'Your recruiter application was not approved.', 'warning');
            flash_set('success', 'Recruiter rejected.');
        }
    }
    redirect('admin/recruiter-approval.php');
}

$pending = $pdo->query(
    "SELECT u.id, u.name, u.email, u.created_at, rp.company_name, rp.industry, rp.location
     FROM users u
     INNER JOIN recruiter_profiles rp ON rp.user_id = u.id
     WHERE u.role = 'recruiter' AND u.recruiter_status = 'pending'
     ORDER BY u.created_at ASC"
)->fetchAll();

$pageTitle = 'Recruiter Approval';
$activeNav = 'recruiter-approval';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="card-premium-admin p-4 mt-2 reveal reveal-up">
  <div class="mb-4">
    <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Pending Approvals</h2>
    <p class="text-muted small mb-0">Verify and authorize new community partners.</p>
  </div>

  <div class="table-responsive">
    <table class="table-premium">
      <thead>
        <tr>
          <th>Company / Contact</th>
          <th>Industry & Location</th>
          <th>Registration Date</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$pending): ?>
          <tr><td colspan="4" class="text-center text-muted p-5">No pending recruiter applications found.</td></tr>
        <?php endif; ?>
        <?php foreach ($pending as $p): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-3">
                <div class="bg-indigo-soft text-indigo rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 1rem;">
                  <?= e(strtoupper(substr($p['company_name'], 0, 1))) ?>
                </div>
                <div>
                  <div class="fw-bold small"><?= e($p['company_name']) ?></div>
                  <div class="text-muted" style="font-size: 0.75rem;"><i class="ri-user-follow-line me-1"></i><?= e($p['name']) ?> (<?= e($p['email']) ?>)</div>
                </div>
              </div>
            </td>
            <td>
              <div class="small fw-semibold"><?= e($p['industry']) ?></div>
              <div class="text-muted small"><i class="ri-map-pin-line me-1"></i><?= e((string) $p['location']) ?></div>
            </td>
            <td class="small text-muted"><?= date('M d, Y', strtotime((string)$p['created_at'])) ?></td>
            <td class="text-end">
              <form method="post" class="d-flex justify-content-end gap-2">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="user_id" value="<?= (int) $p['id'] ?>">
                <button type="submit" name="approval_action" value="approve" class="btn btn-sm btn-indigo px-3" style="border-radius: 8px;">
                  <i class="ri-check-line me-1"></i> Approve
                </button>
                <button type="submit" name="approval_action" value="reject" class="btn btn-sm btn-light border px-3" style="border-radius: 8px;">
                  <i class="ri-close-line me-1"></i> Reject
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

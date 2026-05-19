<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('admin');

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
        redirect('admin/job-approval.php');
    }
    $jid = (int) ($_POST['job_id'] ?? 0);
    $action = (string) ($_POST['job_action'] ?? '');
    $st = $pdo->prepare(
        "SELECT j.id, j.title, j.recruiter_id, u.email FROM jobs j INNER JOIN users u ON u.id = j.recruiter_id
         WHERE j.id = ? AND j.status = 'pending_approval' LIMIT 1"
    );
    $st->execute([$jid]);
    $job = $st->fetch();
    if ($job) {
        if ($action === 'publish') {
            $pdo->prepare("UPDATE jobs SET status = 'published' WHERE id = ?")->execute([$jid]);
            notify_user($pdo, (int) $job['recruiter_id'], 'Your job "' . $job['title'] . '" is now published.', 'success');
            send_notification_email($job['email'], 'Job published', 'Your listing "' . $job['title'] . '" is live on UpLiftHub.');
            flash_set('success', 'Job published.');
        } elseif ($action === 'reject') {
            $pdo->prepare("UPDATE jobs SET status = 'closed' WHERE id = ?")->execute([$jid]);
            notify_user($pdo, (int) $job['recruiter_id'], 'Your job "' . $job['title'] . '" was not approved.', 'warning');
            flash_set('success', 'Job listing closed (not approved).');
        }
    }
    redirect('admin/job-approval.php');
}

$jobs = $pdo->query(
    "SELECT j.id, j.title, j.location, j.salary_range, j.created_at, rp.company_name, u.email AS recruiter_email
     FROM jobs j
     INNER JOIN users u ON u.id = j.recruiter_id
     LEFT JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
     WHERE j.status = 'pending_approval'
     ORDER BY j.created_at ASC"
)->fetchAll();

$pageTitle = 'Job Post Approval';
$activeNav = 'job-approval';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="card-premium-admin p-4 mt-2 reveal reveal-up">
  <div class="mb-4">
    <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Job Post Verification</h2>
    <p class="text-muted small mb-0">Review and publish new career opportunities.</p>
  </div>

  <div class="table-responsive">
    <table class="table-premium">
      <thead>
        <tr>
          <th>Role & Company</th>
          <th>Location & Pay</th>
          <th>Submitted On</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$jobs): ?>
          <tr><td colspan="4" class="text-center text-muted p-5">No job listings awaiting approval.</td></tr>
        <?php endif; ?>
        <?php foreach ($jobs as $j): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-3">
                <div class="bg-indigo-soft text-indigo rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 1rem;">
                  <i class="ri-briefcase-line"></i>
                </div>
                <div>
                  <div class="fw-bold small"><?= e($j['title']) ?></div>
                  <div class="text-muted" style="font-size: 0.75rem;"><i class="ri-building-line me-1"></i><?= e((string) $j['company_name']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <div class="small fw-semibold"><?= e((string) $j['location']) ?></div>
              <div class="text-muted small"><i class="ri-money-dollar-circle-line me-1"></i><?= e((string) $j['salary_range']) ?></div>
            </td>
            <td class="small text-muted"><?= date('M d, Y', strtotime((string)$j['created_at'])) ?></td>
            <td class="text-end">
              <form method="post" class="d-flex justify-content-end gap-2">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="job_id" value="<?= (int) $j['id'] ?>">
                <button type="submit" name="job_action" value="publish" class="btn btn-sm btn-indigo px-3" style="border-radius: 8px;">
                  <i class="ri-broadcast-line me-1"></i> Publish
                </button>
                <button type="submit" name="job_action" value="reject" class="btn btn-sm btn-light border px-3" style="border-radius: 8px;">
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

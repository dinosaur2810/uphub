<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('recruiter');

$pdo = db();
$uid = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
        redirect('recruiter/manage-postings.php');
    }
    $act = (string) ($_POST['manage_action'] ?? '');
    if ($act === 'close_job') {
        $jid = (int) ($_POST['job_id'] ?? 0);
        $chk = $pdo->prepare('SELECT id FROM jobs WHERE id = ? AND recruiter_id = ? LIMIT 1');
        $chk->execute([$jid, $uid]);
        if ($chk->fetch()) {
            $pdo->prepare("UPDATE jobs SET status = 'closed' WHERE id = ?")->execute([$jid]);
            flash_set('success', 'Job closed.');
        }
    } elseif ($act === 'withdraw_aid') {
        $id = (int) ($_POST['listing_id'] ?? 0);
        $st = $pdo->prepare(
            'SELECT id FROM financial_aid_programs WHERE id = ? AND posted_by_user_id = ? AND moderation_status = ? LIMIT 1'
        );
        $st->execute([$id, $uid, 'pending_approval']);
        if ($st->fetch()) {
            $pdo->prepare('DELETE FROM financial_aid_programs WHERE id = ?')->execute([$id]);
            flash_set('success', 'Draft financial aid listing withdrawn.');
        }
    } elseif ($act === 'deactivate_aid') {
        $id = (int) ($_POST['listing_id'] ?? 0);
        $st = $pdo->prepare(
            'SELECT id FROM financial_aid_programs WHERE id = ? AND posted_by_user_id = ? AND moderation_status = ? LIMIT 1'
        );
        $st->execute([$id, $uid, 'published']);
        if ($st->fetch()) {
            $pdo->prepare("UPDATE financial_aid_programs SET status = 'inactive' WHERE id = ?")->execute([$id]);
            flash_set('success', 'Program marked inactive.');
        }
    } elseif ($act === 'withdraw_service') {
        $id = (int) ($_POST['listing_id'] ?? 0);
        $st = $pdo->prepare(
            'SELECT id FROM social_services WHERE id = ? AND posted_by_user_id = ? AND moderation_status = ? LIMIT 1'
        );
        $st->execute([$id, $uid, 'pending_approval']);
        if ($st->fetch()) {
            $pdo->prepare('DELETE FROM social_services WHERE id = ?')->execute([$id]);
            flash_set('success', 'Draft social service withdrawn.');
        }
    } elseif ($act === 'unpublish_service') {
        $id = (int) ($_POST['listing_id'] ?? 0);
        $st = $pdo->prepare(
            'SELECT id FROM social_services WHERE id = ? AND posted_by_user_id = ? AND moderation_status = ? LIMIT 1'
        );
        $st->execute([$id, $uid, 'published']);
        if ($st->fetch()) {
            $pdo->prepare("UPDATE social_services SET moderation_status = 'rejected' WHERE id = ?")->execute([$id]);
            flash_set('success', 'Service removed from public directory.');
        }
    }
    redirect('recruiter/manage-postings.php');
}

$jobs = $pdo->prepare('SELECT id, title, location, salary_range, status, created_at FROM jobs WHERE recruiter_id = ? ORDER BY created_at DESC');
$jobs->execute([$uid]);
$jobs = $jobs->fetchAll();

$aid = $pdo->prepare(
    'SELECT id, title, status, moderation_status, created_at FROM financial_aid_programs WHERE posted_by_user_id = ? ORDER BY created_at DESC'
);
$aid->execute([$uid]);
$aid = $aid->fetchAll();

$services = $pdo->prepare(
    'SELECT id, name, moderation_status, created_at FROM social_services WHERE posted_by_user_id = ? ORDER BY created_at DESC'
);
$services->execute([$uid]);
$services = $services->fetchAll();

$rows = [];
foreach ($jobs as $j) {
    $rows[] = [
        'kind' => 'job',
        'id' => (int) $j['id'],
        'name' => $j['title'],
        'detail' => (string) $j['location'],
        'status' => $j['status'],
        'created_at' => $j['created_at'],
    ];
}
foreach ($aid as $a) {
    $rows[] = [
        'kind' => 'financial_aid',
        'id' => (int) $a['id'],
        'name' => $a['title'],
        'detail' => 'Status: ' . $a['status'],
        'status' => $a['moderation_status'],
        'created_at' => $a['created_at'],
    ];
}
foreach ($services as $s) {
    $rows[] = [
        'kind' => 'social_service',
        'id' => (int) $s['id'],
        'name' => $s['name'],
        'detail' => '',
        'status' => $s['moderation_status'],
        'created_at' => $s['created_at'],
    ];
}
usort($rows, static function ($a, $b) {
    return strcmp((string) $b['created_at'], (string) $a['created_at']);
});

$pageTitle = 'Manage listings';
$activeNav = 'jobs';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
          <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Submission Directory</h2>
          <p class="text-muted small mb-0">Track all your submitted jobs, aid programs, and services.</p>
        </div>
        <?php if (recruiter_is_approved()): ?>
          <a href="<?= e(app_url('recruiter/post-job.php')) ?>" class="btn btn-indigo px-4 py-2" style="border-radius: 10px;">
            <i class="ri-add-circle-line me-1"></i> Post New Listing
          </a>
        <?php endif; ?>
      </div>

      <div class="table-responsive">
        <table class="table-premium">
          <thead>
            <tr>
              <th>Resource / Type</th>
              <th>Details</th>
              <th>Workflow Status</th>
              <th>Submitted Date</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="5" class="text-center text-muted p-5">You haven't submitted any listings yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
              <?php
                $iconClass = match($r['kind']) {
                    'job' => 'ri-briefcase-line bg-indigo-soft text-indigo',
                    'financial_aid' => 'ri-hand-coin-line bg-green-soft text-green',
                    default => 'ri-heart-pulse-line bg-blue-soft text-blue'
                };
                $statusClass = match($r['status']) {
                    'published' => 'approved',
                    'pending_approval' => 'pending',
                    'closed', 'rejected' => 'rejected',
                    default => 'pending'
                };
              ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center <?= explode(' ', $iconClass)[1] ?> <?= explode(' ', $iconClass)[2] ?>" style="width: 36px; height: 36px; font-size: 1rem;">
                      <i class="<?= explode(' ', $iconClass)[0] ?>"></i>
                    </div>
                    <div>
                      <div class="fw-bold small"><?= e($r['name']) ?></div>
                      <div class="text-muted" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;"><?= str_replace('_', ' ', $r['kind']) ?></div>
                    </div>
                  </div>
                </td>
                <td class="small text-muted"><?= e($r['detail']) ?></td>
                <td><span class="badge-admin badge-<?= $statusClass ?>"><?= e(str_replace('_', ' ', $r['status'])) ?></span></td>
                <td class="small text-muted"><?= date('M d, Y', strtotime((string)$r['created_at'])) ?></td>
                <td class="text-end">
                  <?php if ($r['kind'] === 'job' && $r['status'] !== 'closed'): ?>
                    <form method="post" class="d-inline" onsubmit="return confirm('Close this job listing?');">
                      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="manage_action" value="close_job">
                      <input type="hidden" name="job_id" value="<?= (int) $r['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-light border text-danger" title="Close Listing"><i class="ri-close-circle-line"></i></button>
                    </form>
                  <?php elseif ($r['kind'] === 'financial_aid' && $r['status'] === 'pending_approval'): ?>
                    <form method="post" class="d-inline" onsubmit="return confirm('Withdraw this submission?');">
                      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="manage_action" value="withdraw_aid">
                      <input type="hidden" name="listing_id" value="<?= (int) $r['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-light border text-danger" title="Withdraw Submission"><i class="ri-delete-bin-line"></i></button>
                    </form>
                  <?php elseif ($r['kind'] === 'financial_aid' && $r['status'] === 'published'): ?>
                    <form method="post" class="d-inline" onsubmit="return confirm('Mark this program inactive?');">
                      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="manage_action" value="deactivate_aid">
                      <input type="hidden" name="listing_id" value="<?= (int) $r['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-light border" title="Deactivate"><i class="ri-pause-circle-line"></i></button>
                    </form>
                  <?php elseif ($r['kind'] === 'social_service' && $r['status'] === 'pending_approval'): ?>
                    <form method="post" class="d-inline" onsubmit="return confirm('Withdraw this submission?');">
                      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="manage_action" value="withdraw_service">
                      <input type="hidden" name="listing_id" value="<?= (int) $r['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-light border text-danger" title="Withdraw"><i class="ri-delete-bin-line"></i></button>
                    </form>
                  <?php elseif ($r['kind'] === 'social_service' && $r['status'] === 'published'): ?>
                    <form method="post" class="d-inline" onsubmit="return confirm('Remove from public directory?');">
                      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="manage_action" value="unpublish_service">
                      <input type="hidden" name="listing_id" value="<?= (int) $r['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-light border" title="Unpublish"><i class="ri-eye-off-line"></i></button>
                    </form>
                  <?php endif; ?>
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

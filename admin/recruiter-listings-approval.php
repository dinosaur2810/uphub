<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('admin');

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
        redirect('admin/recruiter-listings-approval.php');
    }
    $act = (string) ($_POST['rl_action'] ?? '');
    if ($act === 'publish_aid') {
        $id = (int) ($_POST['id'] ?? 0);
        $st = $pdo->prepare(
            "SELECT f.id, f.title, f.posted_by_user_id, u.email FROM financial_aid_programs f INNER JOIN users u ON u.id = f.posted_by_user_id
             WHERE f.id = ? AND f.posted_by_user_id IS NOT NULL AND f.moderation_status = 'pending_approval' LIMIT 1"
        );
        $st->execute([$id]);
        $row = $st->fetch();
        if ($row) {
            $pdo->prepare("UPDATE financial_aid_programs SET moderation_status = 'published' WHERE id = ?")->execute([$id]);
            notify_user($pdo, (int) $row['posted_by_user_id'], 'Your financial aid listing "' . $row['title'] . '" is now published.', 'success');
            send_notification_email($row['email'], 'Listing published', 'Your financial aid program is live on UpLiftHub.');
            flash_set('success', 'Financial aid listing published.');
        }
    } elseif ($act === 'reject_aid') {
        $id = (int) ($_POST['id'] ?? 0);
        $st = $pdo->prepare(
            "SELECT posted_by_user_id, title FROM financial_aid_programs WHERE id = ? AND posted_by_user_id IS NOT NULL AND moderation_status = 'pending_approval' LIMIT 1"
        );
        $st->execute([$id]);
        $row = $st->fetch();
        if ($row) {
            $pdo->prepare("UPDATE financial_aid_programs SET moderation_status = 'rejected' WHERE id = ?")->execute([$id]);
            notify_user($pdo, (int) $row['posted_by_user_id'], 'Your financial aid listing "' . $row['title'] . '" was not approved.', 'warning');
            flash_set('success', 'Financial aid listing rejected.');
        }
    } elseif ($act === 'publish_service') {
        $id = (int) ($_POST['id'] ?? 0);
        $st = $pdo->prepare(
            "SELECT s.id, s.name, s.posted_by_user_id, u.email FROM social_services s INNER JOIN users u ON u.id = s.posted_by_user_id
             WHERE s.id = ? AND s.posted_by_user_id IS NOT NULL AND s.moderation_status = 'pending_approval' LIMIT 1"
        );
        $st->execute([$id]);
        $row = $st->fetch();
        if ($row) {
            $pdo->prepare("UPDATE social_services SET moderation_status = 'published' WHERE id = ?")->execute([$id]);
            notify_user($pdo, (int) $row['posted_by_user_id'], 'Your social service "' . $row['name'] . '" is now published.', 'success');
            send_notification_email($row['email'], 'Listing published', 'Your social service is live on UpLiftHub.');
            flash_set('success', 'Social service published.');
        }
    } elseif ($act === 'reject_service') {
        $id = (int) ($_POST['id'] ?? 0);
        $st = $pdo->prepare(
            "SELECT posted_by_user_id, name FROM social_services WHERE id = ? AND posted_by_user_id IS NOT NULL AND moderation_status = 'pending_approval' LIMIT 1"
        );
        $st->execute([$id]);
        $row = $st->fetch();
        if ($row) {
            $pdo->prepare("UPDATE social_services SET moderation_status = 'rejected' WHERE id = ?")->execute([$id]);
            notify_user($pdo, (int) $row['posted_by_user_id'], 'Your social service "' . $row['name'] . '" was not approved.', 'warning');
            flash_set('success', 'Social service rejected.');
        }
    }
    redirect('admin/recruiter-listings-approval.php');
}

$pendingAid = $pdo->query(
    "SELECT f.*, rp.company_name, u.name AS contact_name, u.email
     FROM financial_aid_programs f
     INNER JOIN users u ON u.id = f.posted_by_user_id
     LEFT JOIN recruiter_profiles rp ON rp.user_id = f.posted_by_user_id
     WHERE f.moderation_status = 'pending_approval' AND f.posted_by_user_id IS NOT NULL
     ORDER BY f.created_at ASC"
)->fetchAll();

$pendingServices = $pdo->query(
    "SELECT s.*, rp.company_name, u.name AS contact_name, u.email
     FROM social_services s
     INNER JOIN users u ON u.id = s.posted_by_user_id
     LEFT JOIN recruiter_profiles rp ON rp.user_id = s.posted_by_user_id
     WHERE s.moderation_status = 'pending_approval' AND s.posted_by_user_id IS NOT NULL
     ORDER BY s.created_at ASC"
)->fetchAll();

$pageTitle = 'Recruiter listings approval';
$activeNav = 'recruiter-listings';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<h1 class="h4 fw-bold mb-2">Recruiter listings approval</h1>
<p class="text-muted small mb-4">Financial aid and social service posts from recruiters appear on job seeker pages after you publish them here. Job openings are approved under <a href="<?= e(app_url('admin/job-approval.php')) ?>">Job post approval</a>.</p>

<h2 class="h6 fw-bold mb-3 reveal reveal-fade">Financial aid (pending)</h2>
<div class="table-card mb-5 reveal reveal-up">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Program</th>
          <th>Organization</th>
          <th>Contact</th>
          <th>Submitted</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$pendingAid): ?>
          <tr><td colspan="5" class="text-muted p-3">No pending financial aid listings.</td></tr>
        <?php endif; ?>
        <?php foreach ($pendingAid as $p): ?>
          <tr>
            <td class="fw-semibold"><?= e($p['title']) ?></td>
            <td><?= e((string) $p['company_name']) ?></td>
            <td class="small"><?= e($p['email']) ?></td>
            <td class="small text-muted"><?= e((string) $p['created_at']) ?></td>
            <td class="text-end">
              <form method="post" class="d-inline">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button type="submit" name="rl_action" value="publish_aid" class="btn btn-sm btn-success">Publish</button>
                <button type="submit" name="rl_action" value="reject_aid" class="btn btn-sm btn-outline-danger">Reject</button>
              </form>
            </td>
          </tr>
          <tr class="table-light">
            <td colspan="5" class="small">
              <strong>Description:</strong> <?= nl2br(e((string) $p['description'])) ?>
              <?php if (trim((string) $p['eligibility']) !== ''): ?>
                <br><strong>Eligibility:</strong> <?= nl2br(e((string) $p['eligibility'])) ?>
              <?php endif; ?>
              <?php if (trim((string) $p['contact_info']) !== ''): ?>
                <br><strong>Listing contact:</strong> <?= e((string) $p['contact_info']) ?>
              <?php endif; ?>
              <?php if (trim((string) ($p['exact_address'] ?? '')) !== ''): ?>
                <br><strong>Address:</strong> <?= e((string) $p['exact_address']) ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<h2 class="h6 fw-bold mb-3 reveal reveal-fade">Social services (pending)</h2>
<div class="table-card reveal reveal-up">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Service</th>
          <th>Organization</th>
          <th>Contact</th>
          <th>Submitted</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$pendingServices): ?>
          <tr><td colspan="5" class="text-muted p-3">No pending social services.</td></tr>
        <?php endif; ?>
        <?php foreach ($pendingServices as $s): ?>
          <tr>
            <td class="fw-semibold"><?= e($s['name']) ?></td>
            <td><?= e((string) $s['company_name']) ?></td>
            <td class="small"><?= e($s['email']) ?></td>
            <td class="small text-muted"><?= e((string) $s['created_at']) ?></td>
            <td class="text-end">
              <form method="post" class="d-inline">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <button type="submit" name="rl_action" value="publish_service" class="btn btn-sm btn-success">Publish</button>
                <button type="submit" name="rl_action" value="reject_service" class="btn btn-sm btn-outline-danger">Reject</button>
              </form>
            </td>
          </tr>
          <tr class="table-light">
            <td colspan="5" class="small">
              <?= nl2br(e((string) $s['description'])) ?>
              <br><strong>Address:</strong> <?= e((string) ($s['exact_address'] ?? '')) ?> · <strong>Phone:</strong> <?= e((string) $s['phone']) ?>
              <?php if ($s['category']): ?>
                · <strong>Category:</strong> <?= e((string) $s['category']) ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

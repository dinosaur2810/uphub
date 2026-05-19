<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('job_seeker');

$pdo = db();
$uid = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_read') {
    if (csrf_verify($_POST['_csrf'] ?? null)) {
        $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$uid]);
        flash_set('success', 'Notifications marked as read.');
    }
    redirect('jobseeker/notifications.php');
}

$list = $pdo->prepare('SELECT id, message, type, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC');
$list->execute([$uid]);
$items = $list->fetchAll();

$pageTitle = 'Notifications';
$activeNav = 'notifications';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Notifications</h1>
          <p class="text-muted mb-0">Stay updated with application updates and program alerts.</p>
        </div>
        <div>
          <form method="post" class="m-0">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="mark_read">
            <button type="submit" class="btn btn-sm btn-light border px-3 fw-bold">
              <i class="ri-check-double-line me-1"></i>Mark all as read
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card-premium-admin p-0 overflow-hidden reveal reveal-up">
  <div class="list-group list-group-flush">
    <?php if (!$items): ?>
      <div class="p-5 text-center">
        <i class="ri-notification-off-line display-4 text-muted mb-3 d-block"></i>
        <p class="text-muted mb-0">Your inbox is clear! No new notifications.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($items as $n):
        $isUnread = (int) $n['is_read'] === 0;
        $icon = 'ri-information-line';
        $iconClass = 'indigo';
        
        if ($n['type'] === 'success') {
            $icon = 'ri-checkbox-circle-line';
            $iconClass = 'green';
        } elseif ($n['type'] === 'warning') {
            $icon = 'ri-error-warning-line';
            $iconClass = 'red';
        }
    ?>
      <div class="list-group-item p-4 border-0 border-bottom <?= $isUnread ? 'bg-light' : '' ?>" style="transition: background 0.2s;">
        <div class="d-flex gap-3">
          <div class="metric-icon <?= $iconClass ?> flex-shrink-0 mb-0" style="width: 40px; height: 40px; font-size: 1.1rem;">
            <i class="<?= $icon ?>"></i>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div class="fw-semibold <?= $isUnread ? 'text-dark' : 'text-muted' ?>" style="font-family: 'Outfit', sans-serif;">
                <?= e($n['message']) ?>
              </div>
              <small class="text-muted text-nowrap mt-1" style="font-size: 0.75rem;">
                <i class="ri-time-line me-1"></i><?= date('M d, H:i', strtotime((string)$n['created_at'])) ?>
              </small>
            </div>
            <div class="mt-2">
              <?php if ($isUnread): ?>
                <span class="badge-admin badge-pending" style="font-size: 0.65rem;">NEW</span>
              <?php endif; ?>
              <?php if ($n['type'] === 'success'): ?>
                 <span class="badge-admin badge-approved" style="font-size: 0.65rem;">UPDATE</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

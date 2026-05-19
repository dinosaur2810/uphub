<?php
declare(strict_types=1);
$u = current_user();
if ($u === null) {
    return;
}
$pdo = db();
$st = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
$st->execute([$u['id']]);
$notifCount = (int) $st->fetchColumn();
$role = $u['role'];
$notifHref = match ($role) {
    'job_seeker' => app_url('jobseeker/notifications.php'),
    'recruiter' => app_url('recruiter/notifications.php'),
    default => app_url('admin/dashboard.php'),
};
?>
<header class="dashboard-topbar">
  <div class="d-flex align-items-center gap-3">
    <button type="button" class="btn btn-light border d-lg-none" onclick="toggleMobileSidebar()" aria-label="Toggle menu">
      <i class="ri-menu-2-line"></i>
    </button>
    <h1 class="h5 fw-bold mb-0 d-none d-md-block" style="font-family: 'Outfit', sans-serif;"><?= e($pageTitle ?? 'Dashboard') ?></h1>
  </div>

  <div class="d-flex align-items-center gap-4">
    <a href="<?= e($notifHref) ?>" class="position-relative text-dark text-decoration-none" title="Notifications">
      <i class="ri-notification-3-line" style="font-size: 1.4rem;"></i>
      <?php if ($notifCount > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-sm" style="font-size: 0.6rem; padding: 0.25em 0.5em;"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
      <?php endif; ?>
    </a>

    <div class="d-flex align-items-center gap-3 ps-3 border-start">
      <div class="text-end lh-1 d-none d-sm-block">
        <div class="fw-bold small" style="font-family: 'Outfit', sans-serif;"><?= e($u['name']) ?></div>
        <div class="text-muted" style="font-size: 0.75rem;"><?= e($role === 'admin' ? 'Administrator' : $role) ?></div>
      </div>
      <div class="avatar bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:40px; height:40px; font-weight: 600; font-family: 'Outfit', sans-serif;">
        <?= e(strtoupper(substr($u['name'], 0, 1))) ?>
      </div>
    </div>
  </div>
</header>

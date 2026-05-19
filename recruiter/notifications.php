<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('recruiter');

$pdo = db();
$uid = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_read') {
    if (csrf_verify($_POST['_csrf'] ?? null)) {
        $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$uid]);
        flash_set('success', 'Notifications marked as read.');
    }
    redirect('recruiter/notifications.php');
}

$list = $pdo->prepare('SELECT id, message, type, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC');
$list->execute([$uid]);
$items = $list->fetchAll();

$pageTitle = 'Notifications';
$activeNav = 'notifications';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 fw-bold mb-0 reveal reveal-fade">Notifications</h1>
  <form method="post" class="m-0">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="mark_read">
    <button type="submit" class="btn btn-sm btn-outline-uplift">Mark all read</button>
  </form>
</div>
<ul class="list-group shadow-sm reveal reveal-up">
  <?php if (!$items): ?>
    <li class="list-group-item text-muted">No notifications.</li>
  <?php endif; ?>
  <?php foreach ($items as $n): ?>
    <li class="list-group-item <?= (int) $n['is_read'] === 0 ? 'list-group-item-light fw-semibold' : '' ?>">
      <div class="d-flex justify-content-between gap-2">
        <span><?= e($n['message']) ?></span>
        <small class="text-muted text-nowrap"><?= e((string) $n['created_at']) ?></small>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

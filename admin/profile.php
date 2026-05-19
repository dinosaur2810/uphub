<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('admin');

$pdo = db();
$uid = current_user()['id'];

$st = $pdo->prepare('SELECT name, email FROM users WHERE id = ? LIMIT 1');
$st->execute([$uid]);
$row = $st->fetch() ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        if ($name === '' || $email === '' || !validate_email($email)) {
            flash_set('warning', 'Valid name and email are required.');
        } else {
            $dup = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
            $dup->execute([$email, $uid]);
            if ($dup->fetch()) {
                flash_set('warning', 'That email is already in use.');
            } else {
                $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?')->execute([$name, $email, $uid]);
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                flash_set('success', 'Profile updated.');
                redirect('admin/profile.php');
            }
        }
    }
    $st->execute([$uid]);
    $row = $st->fetch() ?: [];
}

$pageTitle = 'Admin Profile';
$activeNav = 'dashboard';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="card border-0 shadow-sm reveal reveal-up" style="max-width: 520px;">
  <div class="card-body p-4">
    <h1 class="h4 fw-bold mb-4">Admin profile</h1>
    <form method="post" data-live-validate="true" novalidate>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" required value="<?= e((string) ($row['name'] ?? '')) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email" required value="<?= e((string) ($row['email'] ?? '')) ?>">
        <div class="invalid-feedback d-none" data-for="email"></div>
      </div>
      <button type="submit" class="btn btn-primary-uplift">Save</button>
    </form>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

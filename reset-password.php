<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$token = trim((string) ($_GET['token'] ?? ''));
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        $errors[] = 'Invalid session.';
    }
    $token = trim((string) ($_POST['token'] ?? ''));
    $pw = (string) ($_POST['password'] ?? '');
    $pw2 = (string) ($_POST['password_confirm'] ?? '');
    if ($token === '') {
        $errors[] = 'Missing reset token.';
    }
    if (!validate_password_strength($pw)) {
        $errors[] = 'Password must be at least 8 characters and include letters and numbers.';
    }
    if ($pw !== $pw2) {
        $errors[] = 'Passwords do not match.';
    }
    if (!$errors) {
        $pdo = db();
        $st = $pdo->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1');
        $st->execute([$token]);
        $row = $st->fetch();
        if (!$row) {
            $errors[] = 'This reset link is invalid or has expired.';
        } else {
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $up = $pdo->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
            $up->execute([$hash, $row['id']]);
            flash_set('success', 'Your password has been updated. You can sign in now.');
            redirect('login.php');
        }
    }
} else {
    if ($token === '') {
        flash_set('warning', 'Missing reset token.');
        redirect('login.php');
    }
    $pdo = db();
    $st = $pdo->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1');
    $st->execute([$token]);
    if (!$st->fetch()) {
        flash_set('danger', 'This reset link is invalid or has expired.');
        redirect('login.php');
    }
}

$pageTitle = 'Set new password';
require __DIR__ . '/includes/partials/head.php';
?>
<div class="auth-page">
  <!-- Visual Panel (Desktop) -->
  <div class="auth-visual-panel" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, var(--uplift-indigo) 100%);">
    <div class="auth-visual-content">
      <div class="badge-premium badge-blue mb-3">SECURITY FIRST</div>
      <h2 class="display-5 fw-bold mb-3">Secure your account.</h2>
      <p class="premium-lead mb-0 text-white opacity-90">"Recovery is just the first step. We ensure your new credentials meet the highest security standards."</p>
      
      <div class="mt-5">
        <div class="d-flex align-items-center gap-3 mb-4">
          <div class="auth-icon-badge m-0" style="background: rgba(255,255,255,0.1); color: #fff;">
            <i class="ri-shield-check-line"></i>
          </div>
          <div>
            <div class="fw-bold text-white">End-to-End Encryption</div>
            <div class="small text-white opacity-75">Your password is hashed before it hits our database.</div>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="auth-icon-badge m-0" style="background: rgba(255,255,255,0.1); color: #fff;">
            <i class="ri-lock-password-line"></i>
          </div>
          <div>
            <div class="fw-bold text-white">Instant Sync</div>
            <div class="small text-white opacity-75">Credentials are updated instantly across all your devices.</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Form Panel -->
  <div class="auth-form-panel">
    <div class="auth-card-premium">
      <div class="text-center mb-5">
         <a href="<?= e(app_url('index.php')) ?>" class="navbar-brand-premium mb-4 d-inline-flex">
            <div class="logo-icon"><i class="ri-rocket-fill text-white"></i></div>
            <span>UpLiftHub</span>
         </a>
         <h1 class="h3 fw-bold mt-3">Reset Password</h1>
         <p class="text-muted small">Enter your new security credentials below.</p>
      </div>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger py-2 border-0 shadow-sm mb-4" style="border-radius: 1rem;">
          <div class="d-flex align-items-center gap-2">
            <i class="ri-error-warning-line h5 m-0"></i> <?= e($err) ?>
          </div>
        </div>
      <?php endforeach; ?>

      <form method="post" id="resetForm" novalidate>
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        
        <div class="mb-4">
          <label class="form-label small fw-bold text-muted" for="password">New Password</label>
          <div class="auth-input-group">
            <i class="ri-lock-2-line"></i>
            <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••" autocomplete="new-password">
          </div>
          <!-- Strength Meter -->
          <div class="mt-2" id="strengthMeter">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="small text-muted">Password Strength</span>
              <span class="small fw-bold" id="strengthText">Weak</span>
            </div>
            <div class="progress" style="height: 4px; background: rgba(0,0,0,0.05);">
              <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 25%; background: var(--bs-danger);"></div>
            </div>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label small fw-bold text-muted" for="password_confirm">Confirm New Password</label>
          <div class="auth-input-group">
            <i class="ri-shield-keyhole-line"></i>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required placeholder="••••••••" autocomplete="new-password">
          </div>
        </div>

        <button type="submit" class="btn btn-brand w-100 py-3 shadow-lg">Update Securely</button>
      </form>

      <div class="text-center mt-4">
        <a href="<?= e(app_url('login.php')) ?>" class="small text-indigo fw-bold d-inline-flex align-items-center gap-1">
          <i class="ri-arrow-left-line"></i> Back to sign in
        </a>
      </div>

      <div class="mt-5 p-3 bg-light border-0 text-center" style="border-radius: 1rem;">
        <div class="small text-muted d-flex align-items-center justify-content-center gap-2">
          <i class="ri-shield-user-fill text-indigo"></i> 
          <span>Secure 256-bit encrypted session</span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const password = document.getElementById('password');
  const strengthBar = document.getElementById('strengthBar');
  const strengthText = document.getElementById('strengthText');

  password.addEventListener('input', function() {
    const val = password.value;
    let strength = 0;
    
    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    let width = '25%';
    let color = 'var(--bs-danger)';
    let label = 'Weak';

    if (val.length === 0) {
      width = '0%';
    } else if (strength === 1) {
      width = '25%';
      color = 'var(--bs-danger)';
      label = 'Weak';
    } else if (strength === 2) {
      width = '50%';
      color = 'var(--uplift-orange)';
      label = 'Fair';
    } else if (strength === 3) {
      width = '75%';
      color = 'var(--uplift-blue)';
      label = 'Good';
    } else if (strength >= 4) {
      width = '100%';
      color = 'var(--uplift-green)';
      label = 'Strong';
    }

    strengthBar.style.width = width;
    strengthBar.style.backgroundColor = color;
    strengthText.innerText = label;
    strengthText.style.color = color;
  });
});
</script>
<?php require __DIR__ . '/includes/partials/footer-public.php'; ?>

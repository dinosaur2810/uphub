<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$u = current_user();
if ($u) {
  match ($u['role']) {
    'job_seeker' => redirect('jobseeker/dashboard.php'),
    'recruiter' => redirect('recruiter/dashboard.php'),
    'admin' => redirect('admin/dashboard.php'),
    default => null,
  };
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
  if (!csrf_verify($_POST['_csrf'] ?? null)) {
    $errors[] = 'Invalid session. Please try again.';
  } else {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
      $errors[] = 'Email and password are required.';
    } elseif (!validate_email($email)) {
      $errors[] = 'Invalid email format.';
    } else {
      $pdo = db();
      $st = $pdo->prepare('SELECT id, email, password_hash, name, role, recruiter_status FROM users WHERE email = ? LIMIT 1');
      $st->execute([$email]);
      $row = $st->fetch();
      if (!$row || !password_verify($password, $row['password_hash'])) {
        $errors[] = 'Invalid email or password.';
      } elseif (($row['role'] ?? '') === 'recruiter' && ($row['recruiter_status'] ?? '') === 'rejected') {
        $errors[] = 'This recruiter account was not approved. Contact support.';
      } else {
        login_user($row);
        flash_set('success', 'Welcome back, ' . $row['name'] . '.');
        match ($row['role']) {
          'job_seeker' => redirect('jobseeker/dashboard.php'),
          'recruiter' => redirect('recruiter/dashboard.php'),
          'admin' => redirect('admin/dashboard.php'),
          default => redirect('index.php'),
        };
      }
    }
  }
}

$pageTitle = 'Sign in';
require __DIR__ . '/includes/partials/head.php';
?>
<div class="auth-page">
  <!-- Visual Panel (Desktop) -->
  <div class="auth-visual-panel" style="background-image: url('<?= e(app_url('assets/img/auth/login.png')) ?>');">
    <div class="auth-visual-content">
      <div class="badge-premium badge-blue mb-3">UPHUB CORE</div>
      <h2 class="display-5 fw-bold mb-3">Your gateway to growth.</h2>
      <p class="premium-lead mb-0 text-white opacity-90">"Since joining UpLiftHub, I've managed to secure three
        interviews in just two weeks. Truly a life-changing platform."</p>
      <div class="mt-4 d-flex align-items-center gap-3">
        <div class="avatar-circle bg-white text-indigo shadow-sm">JS</div>
        <div>
          <div class="fw-bold">Jane Smith</div>
          <div class="small opacity-75">Verified Job Seeker</div>
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
        <h1 class="h3 fw-bold mt-3">Welcome Back</h1>
        <p class="text-muted small">Sign in to manage your career journey or recruitment leads.</p>
      </div>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger py-2 border-0 shadow-sm mb-4" style="border-radius: 1rem;">
          <div class="d-flex align-items-center gap-2"><i class="ri-error-warning-line h5 m-0"></i> <?= e($err) ?></div>
        </div>
      <?php endforeach; ?>
      <?php require __DIR__ . '/includes/partials/alerts.php'; ?>

      <form method="post" action="" novalidate>
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

        <div class="mb-4">
          <label class="form-label small fw-bold text-muted" for="email">Email Address</label>
          <div class="auth-input-group">
            <i class="ri-mail-line"></i>
            <input type="email" class="form-control" id="email" name="email" required placeholder="name@example.com"
              value="<?= e(trim((string) ($_POST['email'] ?? ''))) ?>">
          </div>
        </div>

        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label small fw-bold text-muted mb-0" for="password">Password</label>
            <a href="#" class="small text-indigo fw-bold" data-bs-toggle="modal" data-bs-target="#forgotModal">Forgot
              Password?</a>
          </div>
          <div class="auth-input-group">
            <i class="ri-lock-2-line"></i>
            <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••"
              autocomplete="current-password">
          </div>
        </div>

        <button type="submit" class="btn btn-brand w-100 py-3 shadow-lg">Sign into Dashboard</button>
      </form>

      <div class="auth-divider">OR</div>

      <div class="text-center">
        <p class="small text-muted mb-4">Don't have an account yet?</p>
        <div class="row g-2">
          <div class="col-6">
            <a href="<?= e(app_url('register-jobseeker.php')) ?>" class="btn btn-outline-uplift w-100 small py-2">Job
              Seeker</a>
          </div>
          <div class="col-6">
            <a href="<?= e(app_url('register-recruiter.php')) ?>"
              class="btn btn-outline-uplift w-100 small py-2">Recruiter</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="forgotModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-premium reveal reveal-up">
      <form method="post" action="<?= e(app_url('forgot-password.php')) ?>">
        <div class="modal-header">
          <button type="button" class="btn-close position-absolute top-0 end-0 m-4" data-bs-dismiss="modal"
            aria-label="Close"></button>
          <div class="modal-icon-badge">
            <i class="ri-mail-lock-line"></i>
          </div>
          <h2 class="h4 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Reset Password</h2>
          <p class="text-muted small px-lg-4">Enter your email address and we'll send you a secure link to reset your
            password.</p>
        </div>
        <div class="modal-body">
          <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
          <div class="mb-2">
            <label class="form-label small fw-bold text-muted" for="reset_email">Email Address</label>
            <div class="auth-input-group">
              <i class="ri-mail-line"></i>
              <input type="email" class="form-control" id="reset_email" name="email" required
                placeholder="name@example.com">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-brand w-100 py-3">Send Reset Link</button>
          <button type="button" class="btn btn-link text-muted small text-decoration-none w-100"
            data-bs-dismiss="modal">Back to Login</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/partials/footer-public.php'; ?>
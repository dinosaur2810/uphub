<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

if (current_user()) {
    redirect('index.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        $errors[] = 'Invalid session. Please try again.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $password2 = (string) ($_POST['password_confirm'] ?? '');
        $contact = trim((string) ($_POST['contact'] ?? ''));
        $education = trim((string) ($_POST['education'] ?? ''));
        $skills = trim((string) ($_POST['skills'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $income = trim((string) ($_POST['income_bracket'] ?? ''));

        if ($name === '') {
            $errors[] = 'Name is required.';
        }
        if ($email === '' || !validate_email($email)) {
            $errors[] = 'Valid email is required.';
        }
        if (!validate_password_strength($password)) {
            $errors[] = 'Password must be at least 8 characters and include letters and numbers.';
        }
        if ($password !== $password2) {
            $errors[] = 'Passwords do not match.';
        }
        if ($contact === '') {
            $errors[] = 'Contact number is required.';
        }

        if (!$errors) {
            $pdo = db();
            $chk = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $errors[] = 'An account with this email already exists.';
            } else {
                $pdo->beginTransaction();
                try {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $ins = $pdo->prepare(
                        'INSERT INTO users (email, password_hash, role, name, recruiter_status) VALUES (?,?,?,?,?)'
                    );
                    $ins->execute([$email, $hash, 'job_seeker', $name, 'n/a']);
                    $uid = (int) $pdo->lastInsertId();
                    $prof = $pdo->prepare(
                        'INSERT INTO job_seeker_profiles (user_id, contact, education, skills, location, income_bracket) VALUES (?,?,?,?,?,?)'
                    );
                    $prof->execute([$uid, $contact, $education, $skills, $location, $income]);

                    $picPath = null;
                    if (!empty($_FILES['profile_picture']['name'])) {
                        $picPath = save_uploaded_image($_FILES['profile_picture'], 'profiles', 'js_' . $uid);
                        if ($picPath) {
                            $pdo->prepare('UPDATE job_seeker_profiles SET profile_picture = ? WHERE user_id = ?')->execute([$picPath, $uid]);
                        }
                    }

                    $pdo->commit();
                    notify_user($pdo, $uid, 'Welcome to UpLiftHub! Complete your profile and browse open roles.', 'success');
                    flash_set('success', 'Registration successful. Please sign in.');
                    redirect('login.php');
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
        }
    }
    if ($errors) {
        set_old($_POST);
    }
}

$pageTitle = 'Register — Job Seeker';
require __DIR__ . '/includes/partials/head.php';
$oldIncome = (string) ($_SESSION['_old']['income_bracket'] ?? '');
$incomeOptions = [
    '' => 'Select…',
    'Under $15,000' => 'Under $15,000',
    '$15,000 - $25,000' => '$15,000 - $25,000',
    '$25,000 - $40,000' => '$25,000 - $40,000',
    '$40,000+' => '$40,000+',
    'Prefer not to say' => 'Prefer not to say',
];
?>
<div class="auth-page">
  <!-- Visual Panel -->
  <div class="auth-visual-panel" style="background-image: url('<?= e(app_url('assets/img/auth/jobseeker.png')) ?>');">
    <div class="auth-visual-content">
      <div class="badge-premium badge-green mb-3">JOB SEEKER ADVANTAGE</div>
      <h2 class="display-5 fw-bold mb-3">Join our growing community.</h2>
      <p class="premium-lead mb-0 text-white opacity-90">Matched over 1,200+ candidates with their dream roles this month alone. Your next opportunity is just a few clicks away.</p>
      <div class="mt-4 d-flex align-items-center gap-3">
         <div class="avatar-circle bg-white text-indigo shadow-sm">MB</div>
         <div>
            <div class="fw-bold">Mark Benson</div>
            <div class="small opacity-75">Hired via UpLiftHub</div>
         </div>
      </div>
    </div>
  </div>

  <!-- Form Panel -->
  <div class="auth-form-panel">
    <div class="auth-card-premium" style="max-width: 600px;">
      <div class="mb-5">
         <a href="<?= e(app_url('index.php')) ?>" class="navbar-brand-premium mb-4 d-inline-flex">
            <div class="logo-icon"><i class="ri-rocket-fill text-white"></i></div>
            <span>UpLiftHub</span>
         </a>
         <h1 class="h3 fw-bold mt-3">Create Talent Account</h1>
         <p class="text-muted small">Start your journey towards a better career today.</p>
      </div>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger py-2 border-0 shadow-sm mb-4" style="border-radius: 1rem;"><div class="d-flex align-items-center gap-2"><i class="ri-error-warning-line h5 m-0"></i> <?= e($err) ?></div></div>
      <?php endforeach; ?>

      <form method="post" enctype="multipart/form-data" data-live-validate="true" novalidate>
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted" for="name">Full Name <span class="text-danger">*</span></label>
            <div class="auth-input-group">
              <i class="ri-user-line"></i>
              <input type="text" class="form-control" id="name" name="name" required placeholder="John Doe" value="<?= old('name') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted" for="email">Email Address <span class="text-danger">*</span></label>
            <div class="auth-input-group">
              <i class="ri-mail-line"></i>
              <input type="email" class="form-control" id="email" name="email" required placeholder="john@example.com" value="<?= old('email') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted" for="password">Password <span class="text-danger">*</span></label>
            <div class="auth-input-group">
              <i class="ri-lock-2-line"></i>
              <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••" autocomplete="new-password">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted" for="password_confirm">Confirm Password <span class="text-danger">*</span></label>
            <div class="auth-input-group">
              <i class="ri-refresh-line"></i>
              <input type="password" class="form-control" id="password_confirm" name="password_confirm" required placeholder="••••••••" autocomplete="new-password">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted" for="contact">Contact Number <span class="text-danger">*</span></label>
            <div class="auth-input-group">
              <i class="ri-phone-line"></i>
              <input type="text" class="form-control" id="contact" name="contact" required placeholder="+1 234 567 890" value="<?= old('contact') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted" for="location">Location</label>
            <div class="auth-input-group">
              <i class="ri-map-pin-line"></i>
              <input type="text" class="form-control" id="location" name="location" placeholder="City, State" value="<?= old('location') ?>">
            </div>
          </div>
          <div class="col-12">
            <label class="form-label small fw-bold text-muted" for="income_bracket">Income Bracket</label>
            <select class="form-select auth-input-group py-2 px-3" id="income_bracket" name="income_bracket" style="border-radius: 0.85rem; height: 3.5rem;">
              <?php foreach ($incomeOptions as $val => $label): ?>
                <option value="<?= e($val) ?>" <?= $oldIncome === $val ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label small fw-bold text-muted" for="profile_picture">Profile Picture (optional)</label>
            <div class="auth-input-group">
              <input type="file" class="form-control pt-3" id="profile_picture" name="profile_picture" accept="image/*" data-preview-target="jsPreview">
            </div>
            <img src="" alt="" id="jsPreview" class="profile-preview-img mt-3 d-none shadow-sm">
          </div>
        </div>

        <button type="submit" class="btn btn-brand w-100 py-3 mt-5 shadow-lg">Create My Account</button>
      </form>

      <div class="text-center mt-4">
        <p class="small text-muted mb-0">Already have an account? <a href="<?= e(app_url('login.php')) ?>" class="text-indigo fw-bold">Sign in</a></p>
      </div>
    </div>
  </div>
</div>
<?php
if (!empty($_SESSION['_old'])) {
    old_clear();
}
require __DIR__ . '/includes/partials/footer-public.php';
?>

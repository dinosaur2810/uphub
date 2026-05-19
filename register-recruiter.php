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
        $company = trim((string) ($_POST['company_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $password2 = (string) ($_POST['password_confirm'] ?? '');
        $contact = trim((string) ($_POST['contact'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $industry = trim((string) ($_POST['industry'] ?? ''));

        if ($name === '') {
            $errors[] = 'Your name is required.';
        }
        if ($company === '') {
            $errors[] = 'Company or organization name is required.';
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
            $errors[] = 'Contact is required.';
        }
        if ($industry === '') {
            $errors[] = 'Industry is required.';
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
                    $ins->execute([$email, $hash, 'recruiter', $name, 'pending']);
                    $uid = (int) $pdo->lastInsertId();
                    $prof = $pdo->prepare(
                        'INSERT INTO recruiter_profiles (user_id, company_name, contact, location, industry) VALUES (?,?,?,?,?)'
                    );
                    $prof->execute([$uid, $company, $contact, $location, $industry]);

                    if (!empty($_FILES['company_logo']['name'])) {
                        $logo = save_uploaded_image($_FILES['company_logo'], 'logos', 'rc_' . $uid);
                        if ($logo) {
                            $pdo->prepare('UPDATE recruiter_profiles SET company_logo = ? WHERE user_id = ?')->execute([$logo, $uid]);
                        }
                    }

                    $pdo->commit();
                    $admins = $pdo->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll();
                    foreach ($admins as $a) {
                        notify_user($pdo, (int) $a['id'], 'New recruiter pending approval: ' . $company, 'info');
                    }
                    flash_set('success', 'Registration submitted. An administrator must approve your account before you can post jobs.');
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

$pageTitle = 'Register — Recruiter';
require __DIR__ . '/includes/partials/head.php';
?>
<div class="auth-page">
  <!-- Visual Panel -->
  <div class="auth-visual-panel" style="background-image: url('<?= e(app_url('assets/img/auth/recruiter.png')) ?>');">
    <div class="auth-visual-content">
      <div class="badge-premium badge-blue mb-3">PARTNER WITH UPHUB</div>
      <h2 class="display-5 fw-bold mb-3">Scale your impact.</h2>
      <p class="premium-lead mb-0 text-white opacity-90">Join 500+ organizations using UpLiftHub to find, verify, and hire social-impact talent. Streamline your recruitment and support local communities.</p>
      <div class="mt-4 d-flex align-items-center gap-3">
         <div class="avatar-circle bg-white text-indigo shadow-sm">OC</div>
         <div>
            <div class="fw-bold">Our Commitment</div>
            <div class="small opacity-75">Verified Recruiter Support</div>
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
         <h1 class="h3 fw-bold mt-3">Recruiter Registration</h1>
         <p class="text-muted small">Post jobs, aid, or services once approved by our team.</p>
      </div>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger py-2 border-0 shadow-sm mb-4" style="border-radius: 1rem;"><div class="d-flex align-items-center gap-2"><i class="ri-error-warning-line h5 m-0"></i> <?= e($err) ?></div></div>
      <?php endforeach; ?>

      <form method="post" enctype="multipart/form-data" data-live-validate="true" novalidate>
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted" for="name">Your Name <span class="text-danger">*</span></label>
            <div class="auth-input-group">
              <i class="ri-user-smile-line"></i>
              <input type="text" class="form-control" id="name" name="name" required placeholder="Jane Doe" value="<?= old('name') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted" for="company_name">Organization <span class="text-danger">*</span></label>
            <div class="auth-input-group">
              <i class="ri-building-line"></i>
              <input type="text" class="form-control" id="company_name" name="company_name" required placeholder="Company Name" value="<?= old('company_name') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted" for="email">Work Email <span class="text-danger">*</span></label>
            <div class="auth-input-group">
              <i class="ri-mail-line"></i>
              <input type="email" class="form-control" id="email" name="email" required placeholder="recruiter@comp.com" value="<?= old('email') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted" for="industry">Industry <span class="text-danger">*</span></label>
            <div class="auth-input-group">
              <i class="ri-factory-line"></i>
              <input type="text" class="form-control" id="industry" name="industry" required placeholder="Tech, Health, etc." value="<?= old('industry') ?>">
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
            <label class="form-label small fw-bold text-muted" for="company_logo">Company Logo (optional)</label>
            <div class="auth-input-group">
              <input type="file" class="form-control pt-3" id="company_logo" name="company_logo" accept="image/*" data-preview-target="logoPreview">
            </div>
            <img src="" alt="" id="logoPreview" class="profile-preview-img mt-3 d-none shadow-sm">
          </div>
        </div>

        <button type="submit" class="btn btn-brand w-100 py-3 mt-5 shadow-lg">Submit for Approval</button>
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

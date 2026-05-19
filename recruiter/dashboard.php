<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('recruiter');

$pdo = db();
$uid = current_user()['id'];
$status = current_user()['recruiter_status'];

$jobCount = $pdo->prepare('SELECT COUNT(*) FROM jobs WHERE recruiter_id = ?');
$jobCount->execute([$uid]);
$jobsTotal = (int) $jobCount->fetchColumn();

$pub = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE recruiter_id = ? AND status = 'published'");
$pub->execute([$uid]);
$published = (int) $pub->fetchColumn();

$appCount = $pdo->prepare(
    'SELECT COUNT(*) FROM applications a INNER JOIN jobs j ON j.id = a.job_id WHERE j.recruiter_id = ?'
);
$appCount->execute([$uid]);
$apps = (int) $appCount->fetchColumn();

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 shadow-sm reveal reveal-fade" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.05) 0%, rgba(255, 255, 255, 1) 100%);">
      <div class="d-flex align-items-center gap-3">
        <div class="logo-icon bg-indigo text-white">
          <i class="ri-user-smile-line"></i>
        </div>
        <div>
          <h2 class="h4 fw-bold mb-1 text-dark" style="font-family: 'Outfit', sans-serif;">Welcome back, <?= e(current_user()['name']) ?>!</h2>
          <p class="text-muted small mb-0">Track and manage your recruitment pipeline with ease.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($status === 'pending'): ?>
  <div class="alert alert-warning mb-4 shadow-sm">
    <i class="ri-error-warning-line h4 mb-0"></i>
    <div>
      <div class="fw-bold">Account Pending Approval</div>
      <p class="small mb-0">Your account is currently being reviewed. Job posting features will be enabled once approved.</p>
    </div>
  </div>
<?php elseif ($status === 'rejected'): ?>
  <div class="alert alert-danger mb-4 shadow-sm">
    <i class="ri-close-circle-line h4 mb-0"></i>
    <div>
      <div class="fw-bold">Account Not Approved</div>
      <p class="small mb-0">Your application was not approved. Please contact our support team for details.</p>
    </div>
  </div>
<?php endif; ?>

<div class="row g-4 mb-5 reveal-stagger">
  <div class="col-md-4 reveal reveal-up">
    <div class="card-premium-admin metric-card h-100 hover-lift d-flex flex-column align-items-center text-center p-4">
      <div class="metric-icon indigo mb-3">
        <i class="ri-briefcase-line"></i>
      </div>
      <div>
        <h3 class="h6 text-muted fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">My Job Posts</h3>
        <div class="h2 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;"><?= $jobsTotal ?></div>
      </div>
      <a class="stretched-link" href="<?= e(app_url('recruiter/manage-postings.php')) ?>"></a>
    </div>
  </div>
  <div class="col-md-4 reveal reveal-up">
    <div class="card-premium-admin metric-card h-100 hover-lift d-flex flex-column align-items-center text-center p-4">
      <div class="metric-icon green mb-3">
        <i class="ri-checkbox-circle-line"></i>
      </div>
      <div>
        <h3 class="h6 text-muted fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">Published</h3>
        <div class="h2 fw-bold mb-0 text-success" style="font-family: 'Outfit', sans-serif;"><?= $published ?></div>
      </div>
      <a class="stretched-link" href="<?= e(app_url('recruiter/manage-postings.php')) ?>"></a>
    </div>
  </div>
  <div class="col-md-4 reveal reveal-up">
    <div class="card-premium-admin metric-card h-100 hover-lift d-flex flex-column align-items-center text-center p-4">
      <div class="metric-icon blue mb-3">
        <i class="ri-group-line"></i>
      </div>
      <div>
        <h3 class="h6 text-muted fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">Total Applicants</h3>
        <div class="h2 fw-bold mb-0 text-primary" style="font-family: 'Outfit', sans-serif;"><?= $apps ?></div>
      </div>
      <a class="stretched-link" href="<?= e(app_url('recruiter/applicants.php')) ?>"></a>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <h3 class="h5 fw-bold mb-3" style="font-family: 'Outfit', sans-serif;">Recruitment Tips</h3>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="p-3 bg-light rounded-3 d-flex gap-3 h-100">
            <i class="ri-profile-line h4 text-indigo mb-0"></i>
            <div>
              <div class="fw-bold small">Company Profile</div>
              <p class="text-muted small mb-0">High-quality company descriptions attract 40% more job seekers.</p>
              <a href="<?= e(app_url('recruiter/profile.php')) ?>" class="stretched-link"></a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="p-3 bg-light rounded-3 d-flex gap-3 h-100">
            <i class="ri-send-plane-fill h4 text-blue mb-0"></i>
            <div>
              <div class="fw-bold small">Post Listings</div>
              <p class="text-muted small mb-0">Ensure clear salary expectations and eligibility requirements for faster approvals.</p>
              <a href="<?= e(app_url('recruiter/post-job.php')) ?>" class="stretched-link"></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

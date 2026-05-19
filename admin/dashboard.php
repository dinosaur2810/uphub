<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('admin');

$pdo = db();
$users = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$pendingRec = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'recruiter' AND recruiter_status = 'pending'")->fetchColumn();
$pendingJobs = (int) $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'pending_approval'")->fetchColumn();
$pendingRecruiterListings =
    (int) $pdo->query(
        "SELECT COUNT(*) FROM financial_aid_programs WHERE posted_by_user_id IS NOT NULL AND moderation_status = 'pending_approval'"
    )->fetchColumn()
    + (int) $pdo->query(
        "SELECT COUNT(*) FROM social_services WHERE posted_by_user_id IS NOT NULL AND moderation_status = 'pending_approval'"
    )->fetchColumn();
$applications = (int) $pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn();

$pageTitle = 'Admin Dashboard';
$activeNav = 'dashboard';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mb-5 reveal-stagger">
  <div class="col-sm-6 col-xl-3 reveal reveal-up">
    <div class="card-premium-admin metric-card h-100 hover-lift d-flex flex-column align-items-center text-center p-4">
      <div class="metric-icon indigo mb-3">
        <i class="ri-group-line"></i>
      </div>
      <div>
        <h3 class="h6 text-muted fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">Total Users</h3>
        <div class="h2 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;"><?= $users ?></div>
      </div>
      <a class="stretched-link" href="<?= e(app_url('admin/users.php')) ?>"></a>
    </div>
  </div>
  
  <div class="col-sm-6 col-xl-3 reveal reveal-up">
    <div class="card-premium-admin metric-card h-100 hover-lift d-flex flex-column align-items-center text-center p-4">
      <div class="metric-icon orange mb-3">
        <i class="ri-shield-user-line"></i>
      </div>
      <div>
        <h3 class="h6 text-muted fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">Pending Recruiters</h3>
        <div class="h2 fw-bold mb-0 text-orange" style="font-family: 'Outfit', sans-serif;"><?= $pendingRec ?></div>
      </div>
      <a class="stretched-link" href="<?= e(app_url('admin/recruiter-approval.php')) ?>"></a>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3 reveal reveal-up">
    <div class="card-premium-admin metric-card h-100 hover-lift d-flex flex-column align-items-center text-center p-4">
      <div class="metric-icon blue mb-3">
        <i class="ri-briefcase-line"></i>
      </div>
      <div>
        <h3 class="h6 text-muted fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">Jobs Pending</h3>
        <div class="h2 fw-bold mb-0 text-blue" style="font-family: 'Outfit', sans-serif;"><?= $pendingJobs ?></div>
      </div>
      <a class="stretched-link" href="<?= e(app_url('admin/job-approval.php')) ?>"></a>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3 reveal reveal-up">
    <div class="card-premium-admin metric-card h-100 hover-lift d-flex flex-column align-items-center text-center p-4">
      <div class="metric-icon green mb-3">
        <i class="ri-list-check-3"></i>
      </div>
      <div>
        <h3 class="h6 text-muted fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">Pending Listings</h3>
        <div class="h2 fw-bold mb-0 text-green" style="font-family: 'Outfit', sans-serif;"><?= $pendingRecruiterListings ?></div>
      </div>
      <a class="stretched-link" href="<?= e(app_url('admin/recruiter-listings-approval.php')) ?>"></a>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8 reveal reveal-left">
    <div class="card-premium-admin p-4 h-100">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h5 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">System Status</h3>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small fw-bold">Active</span>
      </div>
      <div class="alert alert-warning border-0 bg-warning-subtle text-dark small mb-3">
        <i class="ri-information-line me-2"></i>
        Email and Maps integrations use placeholders in <code>config/config.php</code>.
      </div>
      <div class="p-3 bg-light rounded-3 small">
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Total Applications</span>
          <span class="fw-bold"><?= $applications ?></span>
        </div>
        <div class="progress" style="height: 6px;">
          <div class="progress-bar bg-indigo" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 reveal reveal-right">
    <div class="card-premium-admin p-4 h-100">
      <h3 class="h5 fw-bold mb-3" style="font-family: 'Outfit', sans-serif;">Quick Actions</h3>
      <div class="d-grid gap-2">
        <a href="<?= e(app_url('admin/profile.php')) ?>" class="btn btn-light border text-start py-3 px-3 d-flex align-items-center justify-content-between hover-lift">
          <div>
            <div class="fw-bold small">Admin Profile</div>
            <div class="text-muted" style="font-size: 0.7rem;">Update your credentials</div>
          </div>
          <i class="ri-arrow-right-s-line"></i>
        </a>
        <a href="<?= e(app_url('admin/reports.php')) ?>" class="btn btn-light border text-start py-3 px-3 d-flex align-items-center justify-content-between hover-lift">
          <div>
            <div class="fw-bold small">Audit Reports</div>
            <div class="text-muted" style="font-size: 0.7rem;">View application analytics</div>
          </div>
          <i class="ri-arrow-right-s-line"></i>
        </a>
      </div>
    </div>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

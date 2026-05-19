<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('admin');

$pdo = db();
$stats = [
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'job_seekers' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'job_seeker'")->fetchColumn(),
    'recruiters' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'recruiter'")->fetchColumn(),
    'jobs_published' => (int) $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'published'")->fetchColumn(),
    'jobs_pending' => (int) $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'pending_approval'")->fetchColumn(),
    'applications' => (int) $pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn(),
    'aid_programs' => (int) $pdo->query("SELECT COUNT(*) FROM financial_aid_programs WHERE status = 'active' AND moderation_status = 'published'")->fetchColumn(),
    'services' => (int) $pdo->query("SELECT COUNT(*) FROM social_services WHERE moderation_status = 'published'")->fetchColumn(),
];

$pageTitle = 'Reports';
$activeNav = 'reports';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-up">
      <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Platform Analytics</h2>
      <p class="text-muted small mb-0">Live snapshot of the UpLiftHub ecosystem activity.</p>
    </div>
  </div>
</div>

<div class="row g-4">
  <?php
  $cards = [
    ['Total Ecosystem Users', $stats['users'], 'ri-team-line', 'indigo'],
    ['Job Seeker Accounts', $stats['job_seekers'], 'ri-user-search-line', 'blue'],
    ['Registered Recruiters', $stats['recruiters'], 'ri-user-star-line', 'green'],
    ['Total Applications', $stats['applications'], 'ri-send-plane-fill', 'indigo'],
    ['Published Jobs', $stats['jobs_published'], 'ri-briefcase-line', 'blue'],
    ['Jobs Pending Review', $stats['jobs_pending'], 'ri-time-line', 'red'],
    ['Active Financial Aid', $stats['aid_programs'], 'ri-hand-coin-line', 'green'],
    ['Social Resource Count', $stats['services'], 'ri-heart-pulse-line', 'indigo'],
  ];
  foreach ($cards as [$label, $val, $icon, $color]):
  ?>
    <div class="col-sm-6 col-md-4 col-xl-3">
      <div class="card-premium-admin metric-card h-100 pb-4">
        <div class="metric-icon <?= $color ?>">
          <i class="<?= $icon ?>"></i>
        </div>
        <h3 class="h6 text-muted mb-1"><?= e($label) ?></h3>
        <div class="h2 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;"><?= $val ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

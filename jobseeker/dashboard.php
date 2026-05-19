<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_once dirname(__DIR__) . '/includes/job_apply_post.php';
require_role('job_seeker');

$pdo = db();
$uid = current_user()['id'];

job_apply_handle_post($pdo, $uid, 'jobseeker/dashboard.php');

$applied = [];
$stApp = $pdo->prepare('SELECT job_id FROM applications WHERE job_seeker_id = ?');
$stApp->execute([$uid]);
foreach ($stApp->fetchAll() as $r) {
    $applied[(int) $r['job_id']] = true;
}

$st = $pdo->prepare('SELECT COUNT(*) FROM applications WHERE job_seeker_id = ?');
$st->execute([$uid]);
$appCount = (int) $st->fetchColumn();

$pub = (int) $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'published'")->fetchColumn();

$n = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
$n->execute([$uid]);
$unread = (int) $n->fetchColumn();

$jobPreview = $pdo->query(
    "SELECT j.id, j.title, j.description, j.location, j.salary_range, rp.company_name, u.name AS recruiter_name
     FROM jobs j
     INNER JOIN users u ON u.id = j.recruiter_id
     LEFT JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
     WHERE j.status = 'published'
     ORDER BY j.created_at DESC
     LIMIT 6"
)->fetchAll();

$aidPreview = $pdo->query(
    "SELECT id, title, description, eligibility, contact_info
     FROM financial_aid_programs
     WHERE status = 'active' AND moderation_status = 'published'
     ORDER BY title
     LIMIT 6"
)->fetchAll();

$servicesPreview = $pdo->query(
    "SELECT id, name, description, exact_address, category, phone
     FROM social_services
     WHERE moderation_status = 'published'
     ORDER BY name
     LIMIT 6"
)->fetchAll();

function dash_excerpt(string $text, int $max = 140): string
{
    $t = trim(preg_replace('/\s+/', ' ', $text));
    if (strlen($t) <= $max) {
        return $t;
    }
    return substr($t, 0, $max) . '…';
}

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h1 class="h3 fw-bold mb-1 text-indigo" style="font-family: 'Outfit', sans-serif;">Welcome back, <?= e(current_user()['name']) ?>!</h1>
          <p class="text-muted mb-0">You have <span class="fw-bold text-dark"><?= $pub ?></span> new job opportunities waiting for you today.</p>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= e(app_url('jobseeker/jobs.php')) ?>" class="btn btn-indigo px-4 py-2 shadow-sm">
            <i class="ri-search-eye-line me-2"></i>Explore Opportunities
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-5 reveal-stagger">
  <div class="col-md-4 reveal reveal-up">
    <div class="card-premium-admin metric-card h-100 hover-lift d-flex flex-column align-items-center text-center p-4">
      <div class="metric-icon blue mb-3">
        <i class="ri-briefcase-line"></i>
      </div>
      <div>
        <h3 class="h6 text-muted fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">Open Positions</h3>
        <div class="h2 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;"><?= $pub ?></div>
      </div>
      <a href="<?= e(app_url('jobseeker/jobs.php')) ?>" class="stretched-link"></a>
    </div>
  </div>
  <div class="col-md-4 reveal reveal-up">
    <div class="card-premium-admin metric-card h-100 hover-lift d-flex flex-column align-items-center text-center p-4">
      <div class="metric-icon indigo mb-3">
        <i class="ri-send-plane-fill"></i>
      </div>
      <div>
        <h3 class="h6 text-muted fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">My Applications</h3>
        <div class="h2 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;"><?= $appCount ?></div>
      </div>
      <a href="<?= e(app_url('jobseeker/applications.php')) ?>" class="stretched-link"></a>
    </div>
  </div>
  <div class="col-md-4 reveal reveal-up">
    <div class="card-premium-admin metric-card h-100 hover-lift d-flex flex-column align-items-center text-center p-4">
      <div class="metric-icon red mb-3">
        <i class="ri-notification-3-line"></i>
      </div>
      <div>
        <h3 class="h6 text-muted fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">Unread Notices</h3>
        <div class="h2 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;"><?= $unread ?></div>
      </div>
      <a href="<?= e(app_url('jobseeker/notifications.php')) ?>" class="stretched-link"></a>
    </div>
  </div>
</div>

<section class="mb-5 pt-2">
  <div class="d-flex align-items-center justify-content-between mb-4 reveal reveal-fade">
    <h2 class="h4 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">Recommended Jobs</h2>
    <a href="<?= e(app_url('jobseeker/jobs.php')) ?>" class="btn btn-sm btn-light border px-3 rounded-pill">View all listings</a>
  </div>
  <div class="row g-4 reveal-stagger">
    <?php if (!$jobPreview): ?>
      <div class="col-12">
        <div class="card-premium-admin p-5 text-center bg-white">
          <i class="ri-ghost-line display-4 text-muted mb-3"></i>
          <p class="text-muted">No open positions right now. Check back soon.</p>
        </div>
      </div>
    <?php endif; ?>
    <?php foreach ($jobPreview as $j):
        $company = $j['company_name'] ?: $j['recruiter_name'];
        $desc = dash_excerpt((string) $j['description'], 100);
        ?>
      <div class="col-md-6 col-lg-4 reveal reveal-up">
        <div class="card-premium-admin h-100 d-flex flex-column hover-lift bg-white">
          <div class="p-4 flex-grow-1">
            <div class="d-flex justify-content-between mb-2">
              <span class="badge-admin badge-approved" style="font-size: 0.65rem;"><i class="ri-map-pin-line me-1"></i><?= e((string)$j['location']) ?></span>
              <span class="text-indigo fw-bold small"><?= e((string)$j['salary_range']) ?></span>
            </div>
            <h3 class="h6 fw-bold mb-1" style="font-family: 'Outfit', sans-serif; min-height: 2.5em; display: flex; align-items: center;"><?= e($j['title']) ?></h3>
            <p class="text-muted small mb-3"><i class="ri-building-line me-1"></i><?= e($company) ?></p>
            <?php if ($desc !== ''): ?>
              <p class="small text-muted mb-0" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;"><?= e($desc) ?></p>
            <?php endif; ?>
          </div>
          <div class="px-4 pb-4 mt-auto">
            <div class="d-grid shadow-none">
              <?php if (!empty($applied[(int) $j['id']])): ?>
                <button class="btn btn-light border py-2 disabled rounded-pill">
                  <i class="ri-checkbox-circle-line me-2 text-success"></i>Already Applied
                </button>
              <?php else: ?>
                <button type="button" class="btn btn-indigo py-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#applyModal<?= (int) $j['id'] ?>">
                  View & Apply <i class="ri-arrow-right-line ms-1"></i>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<div class="row g-4 mb-5 pt-2">
  <div class="col-lg-6 reveal reveal-left">
    <section class="h-100">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="h5 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;"><i class="ri-hand-coin-line text-indigo me-2"></i>Financial Support</h2>
        <a href="<?= e(app_url('jobseeker/financial-aid.php')) ?>" class="small text-indigo fw-bold text-decoration-none hover-underline">View all</a>
      </div>
      <div class="d-flex flex-column gap-3">
        <?php foreach ($aidPreview as $p): ?>
          <div class="card-premium-admin p-3 hover-lift bg-white">
            <div class="d-flex gap-3">
              <div class="metric-icon green mb-0 flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.1rem;">
                <i class="ri-money-dollar-circle-line"></i>
              </div>
              <div class="flex-grow-1 min-width-0">
                <h4 class="h6 fw-bold mb-1 text-truncate" style="font-family: 'Outfit', sans-serif;"><?= e($p['title']) ?></h4>
                <p class="text-muted mb-0 small text-truncate"><?= e((string)$p['contact_info']) ?></p>
              </div>
              <div class="align-self-center">
                <a href="<?= e(app_url('jobseeker/financial-aid.php')) ?>" class="btn btn-sm btn-light border-0 p-1 rounded-circle bg-light" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                   <i class="ri-arrow-right-s-line text-indigo" style="font-size: 1.2rem;"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
  <div class="col-lg-6 reveal reveal-right">
    <section class="h-100">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="h5 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;"><i class="ri-community-line text-indigo me-2"></i>Local Services</h2>
        <a href="<?= e(app_url('jobseeker/social-services.php')) ?>" class="small text-indigo fw-bold text-decoration-none hover-underline">Explore</a>
      </div>
      <div class="d-flex flex-column gap-3">
        <?php foreach ($servicesPreview as $s): ?>
          <div class="card-premium-admin p-3 hover-lift bg-white">
            <div class="d-flex gap-3">
              <div class="metric-icon indigo mb-0 flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.1rem;">
                <i class="ri-map-pin-2-line"></i>
              </div>
              <div class="flex-grow-1 min-width-0">
                <h4 class="h6 fw-bold mb-1 text-truncate" style="font-family: 'Outfit', sans-serif;"><?= e($s['name']) ?></h4>
                <p class="text-muted mb-0 small text-truncate"><?= e((string)$s['category']) ?> • <?= e((string)$s['exact_address']) ?></p>
              </div>
              <div class="align-self-center">
                <a href="<?= e(app_url('jobseeker/social-services.php')) ?>" class="btn btn-sm btn-light border-0 p-1 rounded-circle bg-light" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                   <i class="ri-arrow-right-s-line text-indigo" style="font-size: 1.2rem;"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</div>
<?php
$jobs = $jobPreview;
require dirname(__DIR__) . '/includes/partials/job_apply_modals.php';
?>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

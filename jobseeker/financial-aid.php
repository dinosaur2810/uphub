<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('job_seeker');

$pdo = db();
$programs = $pdo->query(
    "SELECT id, title, description, eligibility, contact_info, status FROM financial_aid_programs
     WHERE status = 'active' AND moderation_status = 'published' ORDER BY title"
)->fetchAll();

$pageTitle = 'Financial Aid';
$activeNav = 'aid';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Financial Support</h1>
          <p class="text-muted mb-0">Explore grants, scholarships, and aid available for you.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
           <label class="small text-muted fw-bold text-uppercase d-none d-sm-block">Filter:</label>
           <select class="form-select" id="aidStatusFilter" style="max-width: 200px;">
             <option value="all">All listings</option>
             <option value="active" selected>Active Only</option>
           </select>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4" id="aidCards">
  <?php if (!$programs): ?>
    <div class="col-12">
      <div class="card-premium-admin p-5 text-center reveal reveal-up">
        <i class="ri-hand-coin-line display-4 text-muted mb-3"></i>
        <h3 class="h5 text-muted">No programs available yet</h3>
        <p class="text-muted small">Check back soon for new financial support listings.</p>
      </div>
    </div>
  <?php endif; ?>

  <?php foreach ($programs as $p): ?>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card-premium-admin h-100 d-flex flex-column hover-lift" data-status="<?= e($p['status']) ?>">
        <div class="p-4 flex-grow-1">
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="metric-icon green mb-0" style="width: 32px; height: 32px; font-size: 0.9rem;">
              <i class="ri-money-dollar-circle-line"></i>
            </div>
            <span class="badge-admin badge-approved">Financial Aid</span>
          </div>

          <h2 class="h5 fw-bold mb-3" style="font-family: 'Outfit', sans-serif;"><?= e($p['title']) ?></h2>
          
          <div class="mb-3">
            <h6 class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.05em;">Description</h6>
            <p class="small text-muted mb-0"><?= nl2br(e((string) $p['description'])) ?></p>
          </div>

          <div class="mb-3">
            <h6 class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.05em;">Eligibility</h6>
            <p class="small text-muted mb-0"><?= nl2br(e((string) $p['eligibility'])) ?></p>
          </div>
        </div>

        <div class="px-4 pb-4 mt-auto">
          <div class="card-premium-admin bg-light border-0 p-3">
            <div class="d-flex align-items-center gap-2">
              <i class="ri-contacts-line text-indigo"></i>
              <div>
                <div class="small fw-bold text-indigo" style="font-size: 0.7rem;">CONTACT INFO</div>
                <div class="small fw-semibold"><?= e((string) $p['contact_info']) ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

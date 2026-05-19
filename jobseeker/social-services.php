<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_once dirname(__DIR__) . '/includes/services/MapsConfig.php';
require_role('job_seeker');

$pdo = db();
$services = $pdo->query(
    "SELECT id, name, description, exact_address, category, phone, latitude, longitude FROM social_services
     WHERE moderation_status = 'published' ORDER BY name"
)->fetchAll();

$pageTitle = 'Social Services';
$activeNav = 'services';
require_once dirname(__DIR__) . '/includes/partials/dashboard-start.php';
require_once dirname(__DIR__) . '/includes/partials/map-modal.php';
?>
<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Community Services</h1>
          <p class="text-muted mb-0">Browse verified local resources, shelters, and support centers.</p>
        </div>
        <div class="d-flex gap-2">
          <div class="badge-admin badge-approved px-3 py-2">
            <i class="ri-map-pin-user-line me-1"></i>Directory Access
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <?php if (!$services): ?>
    <div class="col-12">
      <div class="card-premium-admin p-5 text-center reveal reveal-up">
        <i class="ri-service-line display-4 text-muted mb-3"></i>
        <h3 class="h5 text-muted">No services listed yet</h3>
        <p class="text-muted small">Our team is working on adding more community resources.</p>
      </div>
    </div>
  <?php endif; ?>

  <?php foreach ($services as $s): ?>
    <div class="col-12 col-lg-6">
      <div class="card-premium-admin h-100 d-flex flex-column hover-lift">
        <div class="p-4 flex-grow-1">
          <div class="d-flex justify-content-between align-items-start mb-3">
             <div class="metric-icon indigo flex-shrink-0 mb-0" style="width: 40px; height: 40px; font-size: 1.1rem;">
               <i class="ri-heart-pulse-line"></i>
             </div>
             <span class="badge-admin badge-pending"><?= e((string) $s['category']) ?></span>
          </div>

          <h2 class="h5 fw-bold mb-2" style="font-family: 'Outfit', sans-serif;"><?= e($s['name']) ?></h2>
          <p class="text-muted small mb-4"><?= nl2br(e((string) $s['description'])) ?></p>
          
          <div class="space-y-2">
             <div class="d-flex align-items-center gap-2 text-muted small">
                <i class="ri-map-pin-line text-indigo"></i>
                <span><?= e((string) $s['exact_address']) ?></span>
             </div>
             <div class="d-flex align-items-center gap-2 text-muted small mt-2">
                <i class="ri-phone-line text-indigo"></i>
                <span><?= e((string) $s['phone']) ?></span>
             </div>
          </div>
        </div>

        <div class="px-4 pb-4 mt-auto">
          <?php if (!empty($s['exact_address'])): ?>
            <div class="d-grid shadow-none">
              <button class="btn btn-light border py-2 fw-semibold small" 
                      data-view-location 
                      data-address="<?= e((string) $s['exact_address']) ?>" 
                      data-title="<?= e($s['name']) ?>">
                <i class="ri-map-2-line me-2"></i>View on Map
              </button>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Jobs';
require __DIR__ . '/includes/partials/head.php';
?>

<div class="landing-page">
  <?php require __DIR__ . '/includes/partials/topbar-public.php'; ?>

  <!-- Page Hero -->
  <section class="section-premium section-alt py-5">
    <div class="container py-4 text-center">
      <div class="section-tag reveal reveal-fade">Career Center</div>
      <h1 class="display-3 fw-bold mb-4 reveal reveal-up">Find your next opportunity.</h1>
      <p class="premium-lead mx-auto reveal reveal-up" style="max-width: 700px;">
        Browse through verified job listings from employers who prioritize community development and local talent.
      </p>
      
      <!-- Styled Search Header (UI Only for now) -->
      <div class="card-premium mx-auto border-0 p-3 shadow-lg reveal reveal-up" style="max-width: 800px; transform: translateY(30px); border-radius: 1.5rem;">
        <div class="row g-2 align-items-center">
          <div class="col-md-5">
            <input type="text" class="form-control border-0 bg-light p-3" placeholder="Job Title or Keyword..." style="border-radius: 1rem;">
          </div>
          <div class="col-md-4">
             <select class="form-select border-0 bg-light p-3" style="border-radius: 1rem;">
               <option selected>All Locations</option>
               <option>Remote</option>
               <option>On-site</option>
             </select>
          </div>
          <div class="col-md-3">
             <button class="btn btn-brand w-100 p-3">Search Jobs</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-premium pt-5 mt-4">
    <div class="container mt-5">
      <div class="row g-4 reveal-stagger" id="jobsGrid">
        <?php
        $pdo = db();
        $st = $pdo->query(
            "SELECT j.*, u.name AS poster_name, rp.company_name FROM jobs j
             LEFT JOIN users u ON u.id = j.recruiter_id
             LEFT JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
             WHERE j.status IN ('published','approved') ORDER BY j.created_at DESC"
        );
        $jobs = $st->fetchAll();
        if (!$jobs) {
            echo '<div class="col-12"><div class="card-premium text-center py-5"><p class="text-muted mb-0 h5">No opportunities available yet.</p></div></div>';
        } else {
            foreach ($jobs as $j): ?>
          <div class="col-12 col-md-6 col-lg-4 reveal reveal-up">
            <div class="card-premium h-100 d-flex flex-column">
              <div class="d-flex justify-content-between mb-3 align-items-start">
                <div class="badge-premium badge-blue">Full Time</div>
                <div class="text-muted small"><?= e((string) date('M d, Y', strtotime((string)$j['created_at']))) ?></div>
              </div>
              
              <h5 class="fw-bold mb-1 h5"><?= e((string) $j['title']) ?></h5>
              <div class="small fw-semibold text-indigo mb-3">
                <?= e((string) ($j['company_name'] ?? $j['poster_name'] ?? '')) ?> • <?= e((string) $j['location']) ?>
              </div>
              
              <p class="small flex-grow-1 mb-4 text-muted" style="line-height: 1.5;">
                <?= e(substr((string) $j['description'], 0, 160)) ?><?= strlen((string)$j['description']) > 160 ? '...' : '' ?>
              </p>

              <?php if (!empty($j['exact_address'])): ?>
              <div class="small bg-light p-2 rounded mb-4" style="font-size: 0.75rem;">
                <span class="text-muted fw-bold">LOCATION:</span> <?= e((string) $j['exact_address']) ?>
              </div>
              <?php endif; ?>

              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top mt-auto">
                <div class="fw-bold text-indigo" style="font-size: 0.9rem;"><?= e((string) $j['salary_range']) ?></div>
                <div class="d-flex gap-2">
                  <?php if (!empty($j['exact_address'])): ?>
                  <button class="btn btn-sm btn-outline-uplift" style="border-radius:0.5rem;" data-view-location data-address="<?= e((string) $j['exact_address']) ?>" data-title="<?= e((string) $j['title']) ?>">Map</button>
                  <?php endif; ?>
                  <button class="btn btn-sm btn-brand text-white" style="border-radius:0.5rem;" data-apply-item data-apply-type="job" data-apply-id="<?= e((string) $j['id']) ?>">Apply Now</button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; }
        ?>
      </div>
    </div>
  </section>

  <?php require __DIR__ . '/includes/partials/apply-modal.php'; ?>
  <?php require __DIR__ . '/includes/partials/map-modal.php'; ?>
  <?php require __DIR__ . '/includes/partials/footer-public.php'; ?>
</div>

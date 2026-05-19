<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Financial Aid';
require __DIR__ . '/includes/partials/head.php';
?>

<div class="landing-page">
  <?php require __DIR__ . '/includes/partials/topbar-public.php'; ?>

  <!-- Page Hero -->
  <section class="section-premium section-alt py-5">
    <div class="container text-center py-4">
      <div class="section-tag reveal reveal-fade">Financial Support</div>
      <h1 class="display-3 fw-bold mb-4 text-brand reveal reveal-up">Empowering You With Financial Support.</h1>
      <p class="premium-lead mx-auto reveal reveal-up" style="max-width: 800px;">
        Discover verified financial aid programs, grants, and scholarships tailored to your community's needs. We help you navigate the application process with ease and transparency.
      </p>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center bg-transparent p-0">
          <li class="breadcrumb-item"><a href="<?= e(app_url('index.php')) ?>" class="text-indigo text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Financial Aid</li>
        </ol>
      </nav>
    </div>
  </section>

  <!-- Programs Grid -->
  <section class="section-premium pt-5">
    <div class="container">
      <div class="row g-4 reveal-stagger" id="aidGrid">
        <?php
        $pdo = db();
        $st = $pdo->query(
            "SELECT id, title, description, eligibility, contact_info, exact_address FROM financial_aid_programs
             WHERE status = 'active' AND moderation_status IN ('published', 'approved') 
             ORDER BY created_at DESC"
        );
        $programs = $st->fetchAll();
        if (!$programs) {
            echo '<div class="col-12"><div class="card-premium py-5 text-center"><p class="text-muted h5">No aid programs available yet.</p></div></div>';
        } else {
            foreach ($programs as $p): ?>
          <div class="col-12 col-md-6 col-lg-4 reveal reveal-up">
            <div class="card-premium h-100 d-flex flex-column border-0 shadow-sm transition-all">
              <div class="d-flex justify-content-between mb-4">
                 <div class="badge-premium badge-blue">ACTIVE PROGRAM</div>
                 <div class="text-muted small">Grant</div>
              </div>

              <h4 class="fw-bold mb-3 h5"><?= e((string) $p['title']) ?></h4>
              <p class="small text-muted flex-grow-1 mb-4" style="line-height: 1.6;">
                <?= e(substr((string) $p['description'], 0, 160)) ?><?= strlen((string)$p['description']) > 160 ? '...' : '' ?>
              </p>

              <div class="card mb-4 border-0 p-3" style="background: var(--uplift-soft-blue); border-radius: 1rem;">
                <div class="small fw-bold text-uppercase text-indigo mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Eligibility Criteria</div>
                <div class="small text-muted"><?= e((string) $p['eligibility']) ?></div>
              </div>

              <?php if (!empty($p['exact_address'])): ?>
              <div class="small mb-4 d-flex gap-2">
                 <i class="ri-map-pin-line text-indigo fw-bold"></i>
                 <span class="text-muted"><?= e((string) $p['exact_address']) ?></span>
              </div>
              <?php endif; ?>

              <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                <div>
                   <?php if (!empty($p['exact_address'])): ?>
                   <button class="btn btn-sm btn-outline-uplift" style="border-radius:0.5rem;" data-view-location data-address="<?= e((string) $p['exact_address']) ?>" data-title="<?= e((string) $p['title']) ?>">View Site</button>
                   <?php endif; ?>
                </div>
                <button class="btn btn-sm btn-brand text-white px-4" style="border-radius:0.5rem;" data-apply-item data-apply-type="aid" data-apply-id="<?= e((string) $p['id']) ?>">Apply now</button>
              </div>
            </div>
          </div>
        <?php endforeach; } ?>
      </div>
    </div>
  </section>

  <!-- Help Section -->
  <section class="section-premium section-alt">
    <div class="container text-center">
      <div class="row justify-content-center">
        <div class="col-lg-8">
           <h2 class="h1 fw-bold mb-4 reveal reveal-up">Unsure where to start?</h2>
           <p class="text-muted mb-5 reveal reveal-up">Our community managers are here to guide you through the available grants and scholarships. We help you find the resources that best fit your situation.</p>
           <div class="d-flex justify-content-center gap-3 reveal reveal-fade">
              <a href="<?= e(app_url('about.php')) ?>" class="btn btn-brand btn-lg">Learn How It Works</a>
              <a href="mailto:support@uplifthub.org" class="btn btn-outline-uplift btn-lg" style="border-radius:0.75rem;">Contact Support</a>
           </div>
        </div>
      </div>
    </div>
  </section>

  <?php require __DIR__ . '/includes/partials/apply-modal.php'; ?>
  <?php require __DIR__ . '/includes/partials/map-modal.php'; ?>
  <?php require __DIR__ . '/includes/partials/footer-public.php'; ?>
</div>

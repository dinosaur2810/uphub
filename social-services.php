<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Social Services';
require __DIR__ . '/includes/partials/head.php';
?>

<div class="landing-page">
  <?php require __DIR__ . '/includes/partials/topbar-public.php'; ?>

  <!-- Page Hero with Map Integration -->
  <section class="section-premium section-alt py-5">
    <div class="container text-center py-4">
      <div class="section-tag reveal reveal-fade">Community Care</div>
      <h1 class="display-3 fw-bold mb-4 reveal reveal-up">Access Local Social Services.</h1>
      <p class="premium-lead mx-auto mb-5 reveal reveal-up" style="max-width: 800px;">
        Find nearby shelters, clinics, food banks, and counseling centers. We connect you to local organizations that offer essential support in your time of need.
      </p>
      
      <!-- Prominent Map View Toggle (Great and Good) -->
      <div class="card-premium mx-auto border-0 p-0 shadow-lg reveal reveal-scale" style="max-width: 900px; transform: translateY(40px); overflow: hidden; border-radius: 2rem;">
         <div class="p-4 bg-indigo text-white d-flex justify-content-between align-items-center">
            <h4 class="h5 m-0 fw-bold">Live Community Map</h4>
            <div class="badge-premium border border-light text-white" style="font-size: 0.6rem;">REALTIME DATA</div>
         </div>
         <div class="position-relative" style="height: 300px; background: #e9ecef; display: flex; align-items: center; justify-content: center; background-image: linear-gradient(rgba(255,255,255,0.7), rgba(255,255,255,0.7)), url('https://upload.wikimedia.org/wikipedia/commons/e/e0/OpenStreetMap_logo.svg'); background-size: contain; background-repeat: no-repeat; background-position: center;">
            <div class="text-center p-4">
               <h5 class="fw-bold text-dark mb-3">Explore Services Visually</h5>
               <p class="small text-muted mb-4 mx-auto" style="max-width: 400px;">Click the button below to launch our interactive map showing all verified support centers near you.</p>
               <button class="btn btn-brand btn-lg" data-view-location data-address="General Location" data-title="Community Services Map">Launch Modern Map View</button>
            </div>
         </div>
      </div>
    </div>
  </section>

  <!-- Services Grid -->
  <section class="section-premium pt-5 mt-5">
    <div class="container">
      <div class="row g-4 reveal-stagger" id="servicesGrid">
        <?php
        $pdo = db();
        $st = $pdo->query(
            "SELECT id, name, description, phone, exact_address FROM social_services
             WHERE status = 'active' AND moderation_status IN ('published', 'approved') 
             ORDER BY created_at DESC"
        );
        $services = $st->fetchAll();
        if (!$services) {
            echo '<div class="col-12"><div class="card-premium py-5 text-center"><p class="text-muted h5">No services listed for this area yet.</p></div></div>';
        } else {
            foreach ($services as $s): ?>
          <div class="col-12 col-md-6 col-lg-4 reveal reveal-up">
            <div class="card-premium h-100 d-flex flex-column transition-all">
              <div class="d-flex justify-content-between mb-4">
                <div class="badge-premium badge-green">VERIFIED SERVICE</div>
                <div class="text-muted small">Support</div>
              </div>

              <h4 class="fw-bold mb-3 h5"><?= e((string) $s['name']) ?></h4>
              <p class="small text-muted flex-grow-1 mb-4" style="line-height: 1.6;">
                <?= e(substr((string) $s['description'], 0, 160)) ?><?= strlen((string)$s['description']) > 160 ? '...' : '' ?>
              </p>

              <div class="small mb-4 d-flex gap-2 p-2 rounded" style="background: var(--uplift-soft-blue); border: 1px solid var(--uplift-border);">
                 <span class="text-muted fw-bold" style="font-size: 0.7rem;">CONTACT:</span>
                 <span class="small text-muted text-truncate"><?= e((string) $s['phone']) ?></span>
              </div>

              <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                <div class="small d-flex gap-1 align-items-center">
                   <i class="ri-map-pin-line text-indigo"></i>
                   <span class="text-muted small text-truncate" style="max-width: 120px;"><?= e((string) $s['exact_address']) ?></span>
                </div>
                <div class="d-flex gap-2">
                   <?php if (!empty($s['exact_address'])): ?>
                   <button class="btn btn-sm btn-outline-uplift" style="border-radius:0.5rem;" data-view-location data-address="<?= e((string) $s['exact_address']) ?>" data-title="<?= e((string) $s['name']) ?>">Map</button>
                   <?php endif; ?>
                   <button class="btn btn-sm btn-brand text-white" style="border-radius:0.5rem;" data-apply-item data-apply-type="service" data-apply-id="<?= e((string) $s['id']) ?>">Inquiry</button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; } ?>
      </div>
    </div>
  </section>

  <!-- Resource Assistance -->
  <section class="section-premium section-alt">
    <div class="container text-center">
       <div class="newsletter-box shadow-xl reveal reveal-scale" style="background: linear-gradient(rgba(45, 91, 255, 0.9), rgba(45, 91, 255, 0.9)), url('<?= e(app_url('assets/img/resource_hero.png')) ?>'); background-size: cover; background-position: center;">
          <h2 class="display-6 fw-bold mb-4">Dedicated to helping our community</h2>
          <p class="mb-5 opacity-90 mx-auto" style="max-width: 600px;">If you are an organization providing social services, partner with us to reach those who need your help.</p>
          <a href="<?= e(app_url('register-recruiter.php')) ?>" class="btn btn-cta btn-lg">List Your Service</a>
       </div>
    </div>
  </section>

  <?php require __DIR__ . '/includes/partials/apply-modal.php'; ?>
  <?php require __DIR__ . '/includes/partials/map-modal.php'; ?>
  <?php require __DIR__ . '/includes/partials/footer-public.php'; ?>
</div>

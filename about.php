<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'About';
require __DIR__ . '/includes/partials/head.php';
?>

<div class="landing-page">
  <?php require __DIR__ . '/includes/partials/topbar-public.php'; ?>

  <!-- Page Hero -->
  <section class="section-premium section-alt py-5">
    <div class="container text-center py-4">
      <div class="section-tag reveal reveal-fade">About UpLiftHub</div>
      <h1 class="display-3 fw-bold mb-4 reveal reveal-up">Bridging the Gap to a Better Future.</h1>
      <p class="premium-lead mx-auto reveal reveal-up" style="max-width: 800px;">
        UpLiftHub is a dedicated community platform designed to connect individuals with essential resources, sustainable livelihoods, and the support needed to rebuild economic independence.
      </p>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center bg-transparent p-0">
          <li class="breadcrumb-item"><a href="<?= e(app_url('index.php')) ?>" class="text-indigo text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">About</li>
        </ol>
      </nav>
    </div>
  </section>

  <!-- Our Story Section -->
  <section class="section-premium">
    <div class="container">
      <div class="row g-5 align-items-center">
        <div class="col-lg-6 reveal reveal-left">
          <img src="<?= e(app_url('assets/img/M4.jpg')) ?>" alt="Our Team" class="img-fluid rounded-4 shadow-lg">
        </div>
        <div class="col-lg-6 reveal reveal-right">
          <div class="section-tag bg-green-light text-green">Our Mission</div>
          <h2 class="display-5 fw-bold mb-4">Practical support, dignity, and measurable impact.</h2>
          <p class="text-muted mb-4">
            Founded on the principles of community resilience and proactive assistance, UpLiftHub serves as a centralized bridge between those looking for help and those providing it. We believe that access to information is the first step toward self-sufficiency.
          </p>
          <div class="card-premium mb-4" style="background: var(--uplift-soft-blue); border: none;">
             <h4 class="h5 fw-bold mb-2">Goal focus</h4>
             <p class="small text-muted m-0">
               <strong>UpLiftHub</strong> We align our efforts with global goals by providing direct pathways to jobs, targeted financial grants, and critical community services.
             </p>
          </div>
          <a href="<?= e(app_url('register-jobseeker.php')) ?>" class="btn btn-brand">Join the Community</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Values Grid -->
  <section class="section-premium section-alt">
    <div class="container">
      <div class="text-center mb-5 reveal reveal-up">
        <h2 class="h1 fw-bold">Our Core Philosophy</h2>
        <p class="text-muted">Built for the community, powered by compassion.</p>
      </div>
      <div class="row g-4 reveal-stagger">
        <div class="col-md-4 reveal reveal-up">
          <div class="card-premium h-100">
            <div class="badge-premium badge-blue mb-3" style="display:inline-block;">Purpose</div>
            <h3 class="h5 fw-bold mb-3">Targeted Assistance</h3>
            <p class="small text-muted m-0">We don't just provide a list; we provide access to verified opportunities vetted by our community partners.</p>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-up">
          <div class="card-premium h-100">
            <div class="badge-premium badge-green mb-3" style="display:inline-block;">Mission</div>
            <h3 class="h5 fw-bold mb-3">Strengthening Resilience</h3>
            <p class="small text-muted m-0">Connecting people with practical resources that reduce vulnerability and help households regain stability.</p>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-up">
          <div class="card-premium h-100">
            <div class="badge-premium badge-orange mb-3" style="display:inline-block;">Vision</div>
            <h3 class="h5 fw-bold mb-3">Economic Independence</h3>
            <p class="small text-muted m-0">A future where everyone can find work, access support, and rebuild their self-sufficiency with dignity.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Get Involved CTA -->
  <section class="section-premium">
    <div class="container text-center">
      <div class="newsletter-box reveal reveal-scale">
        <h2 class="h1 fw-bold mb-4 text-white">Want to help make a difference?</h2>
        <p class="mb-5 text-white opacity-90 mx-auto" style="max-width: 600px;">Whether you are an individual looking for support or a partner offering it, there is a place for you in our hub.</p>
        <div class="d-flex justify-content-center gap-3">
           <a href="<?= e(app_url('register-recruiter.php')) ?>" class="btn btn-cta btn-lg">Partner With Us</a>
           <a href="<?= e(app_url('login.php')) ?>" class="btn btn-outline-light btn-lg" style="border-radius: 0.75rem;">Sign In</a>
        </div>
      </div>
    </div>
  </section>

  <?php require __DIR__ . '/includes/partials/footer-public.php'; ?>
</div>

<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';

$u = current_user();
if ($u) {
    match ($u['role']) {
        'job_seeker' => redirect('jobseeker/dashboard.php'),
        'recruiter' => redirect('recruiter/dashboard.php'),
        'admin' => redirect('admin/dashboard.php'),
        default => redirect('login.php'),
    };
}

$pageTitle = 'Home';
require __DIR__ . '/includes/partials/head.php';
?>

<div class="landing-page">
  <?php require __DIR__ . '/includes/partials/topbar-public.php'; ?>

  <!-- Hero Section -->
  <section class="section-premium d-flex align-items-center" style="min-height: 85vh; overflow: hidden;">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0 reveal hero-text-reveal">
          <div class="section-tag">Empowering Community</div>
          <h1 class="premium-title mb-4">Empowering Futures, One Connection at a Time.</h1>
          <p class="premium-lead">
            UpLiftHub is your dedicated community portal for job opportunities, financial aid, and essential social services. We bridge the gap between resources and those who need them most.
          </p>
          <div class="d-flex gap-3 mt-4">
            <a href="<?= e(app_url('register-jobseeker.php')) ?>" class="btn btn-brand">Get Started Today</a>
            <a href="<?= e(app_url('about.php')) ?>" class="btn btn-outline-uplift" style="border-radius: 0.75rem; padding: 0.65rem 1.4rem;">Learn More</a>
          </div>
        </div>
        <div class="col-lg-6 hero-entrance">
          <div class="hero-visual-wrapper">
            <div class="floating-stat" style="top: 10%; left: -5%;">
              <div class="badge-blue p-2 rounded-circle"><svg width="24" height="24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg></div>
              <div>
                <div class="fw-bold mb-0">1,240+</div>
                <div class="small text-muted">Jobs Placed</div>
              </div>
            </div>
            <div class="floating-stat" style="bottom: 15%; right: 0%;">
              <div class="badge-green p-2 rounded-circle"><svg width="24" height="24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></div>
              <div>
                <div class="fw-bold mb-0">98%</div>
                <div class="small text-muted">Success Rate</div>
              </div>
            </div>
            <img src="<?= e(app_url('assets/img/H1.jpg')) ?>" alt="Community Support" class="hero-image-main">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Logo Ticker -->
  <section class="py-5 border-bottom border-top">
    <div class="container text-center">
      <p class="small text-muted text-uppercase fw-bold mb-4" style="letter-spacing: 2px;">Trusted by Community Partners</p>
      <div class="d-flex flex-wrap justify-content-center align-items-center gap-5 logo-ticker reveal reveal-fade">
         <span class="h4 fw-bold text-muted">AIDNET</span>
         <span class="h4 fw-bold text-muted">CIVICLINK</span>
         <span class="h4 fw-bold text-muted">PATHWAY</span>
         <span class="h4 fw-bold text-muted">GLOBALCARE</span>
         <span class="h4 fw-bold text-muted">COMMUNITY-X</span>
      </div>
    </div>
  </section>

  <!-- How It Works Section -->
  <section class="section-premium section-alt">
    <div class="container">
      <div class="text-center mb-5 reveal reveal-up">
        <div class="section-tag">How It Works</div>
        <h2 class="h1 fw-bold">Three steps to change your life</h2>
      </div>
      <div class="row g-4 reveal-stagger">
        <div class="col-md-4 reveal reveal-up">
          <div class="step-card-premium">
            <div class="step-number-pill">1</div>
            <h3 class="h5 fw-bold mb-3">Create Your Account</h3>
            <p class="text-muted small mb-0">Join our community as a job seeker or recruiter to unlock personalized resource matching and tracking.</p>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-up">
          <div class="step-card-premium">
            <div class="step-number-pill">2</div>
            <h3 class="h5 fw-bold mb-3">Explore Resources</h3>
            <p class="text-muted small mb-0">Browse through hundreds of verified job listings, financial aid grants, and local community support services.</p>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-up">
          <div class="step-card-premium">
            <div class="step-number-pill">3</div>
            <h3 class="h5 fw-bold mb-3">Apply & Grow</h3>
            <p class="text-muted small mb-0">Submit applications with a single click and track your progress through our unified dashboard system.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Explore Best Jobs -->
  <section class="section-premium">
    <div class="container">
      <div class="d-flex justify-content-between align-items-end mb-5 reveal reveal-up">
        <div>
          <div class="section-tag">Career Center</div>
          <h2 class="h1 fw-bold m-0">Explore latest opportunities</h2>
        </div>
        <a href="<?= e(app_url('jobs.php')) ?>" class="text-brand fw-bold text-decoration-none">View all jobs <i class="ri-arrow-right-line"></i></a>
      </div>
      <div class="row g-4 reveal-stagger" id="featuredJobsGrid">
        <!-- JS will populate some here, but we can have static placeholders too -->
        <div class="col-md-4 reveal reveal-up">
          <div class="card-premium">
            <div class="d-flex justify-content-between mb-3">
              <span class="badge-premium badge-blue">Full Time</span>
              <span class="text-muted small">2 days ago</span>
            </div>
            <h4 class="h5 fw-bold mb-2">Community Coordinator</h4>
            <p class="small text-muted mb-4">Supporting local families through resource management and event coordination...</p>
            <div class="d-flex align-items-center gap-2 pt-3 border-top">
               <div class="bg-indigo p-1 rounded" style="width:30px; height:30px; background: rgba(45, 91, 255, 0.1);"></div>
               <span class="small fw-bold">Social Care Inc.</span>
            </div>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-up">
          <div class="card-premium">
            <div class="d-flex justify-content-between mb-3">
              <span class="badge-premium badge-green">Part Time</span>
              <span class="text-muted small">Just now</span>
            </div>
            <h4 class="h5 fw-bold mb-2">Data Entry Specialist</h4>
            <p class="small text-muted mb-4">Assisting in regional database management for social service mapping...</p>
            <div class="d-flex align-items-center gap-2 pt-3 border-top">
               <div class="bg-indigo p-1 rounded" style="width:30px; height:30px; background: rgba(52, 211, 153, 0.1);"></div>
               <span class="small fw-bold">City Logistics</span>
            </div>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-up">
          <div class="card-premium">
            <div class="d-flex justify-content-between mb-3">
              <span class="badge-premium badge-blue">Remote</span>
              <span class="text-muted small">5 hours ago</span>
            </div>
            <h4 class="h5 fw-bold mb-2">Grant Writer Assistant</h4>
            <p class="small text-muted mb-4">Writing proposals for local NGOs and community development centers...</p>
            <div class="d-flex align-items-center gap-2 pt-3 border-top">
               <div class="bg-indigo p-1 rounded" style="width:30px; height:30px; background: rgba(139, 92, 246, 0.1);"></div>
               <span class="small fw-bold">Global Aid</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Bento Sections -->
  <section class="section-premium section-alt">
    <div class="container">
      <div class="row g-5 align-items-center mb-5">
        <div class="col-lg-6 reveal reveal-left">
          <img src="<?= e(app_url('assets/img/H2.jpg')) ?>" alt="Job Seekers" class="bento-image shadow">
        </div>
        <div class="col-lg-6 reveal reveal-right">
          <div class="section-tag bg-green-light text-green">For Applicants</div>
          <h2 class="display-5 fw-bold mb-4">Find the perfect resources for your journey.</h2>
          <ul class="list-unstyled">
             <li class="mb-3 d-flex gap-2">
                <i class="ri-checkbox-circle-fill text-indigo"></i>
                <span>Verified job listings for community-focused roles.</span>
             </li>
             <li class="mb-3 d-flex gap-2">
                <i class="ri-checkbox-circle-fill text-indigo"></i>
                <span>Direct access to financial aid and emergency grants.</span>
             </li>
             <li class="mb-3 d-flex gap-2">
                <i class="ri-checkbox-circle-fill text-indigo"></i>
                <span>Interactive map of local shelters and health centers.</span>
             </li>
          </ul>
          <a href="<?= e(app_url('register-jobseeker.php')) ?>" class="btn btn-brand mt-3">Join as Job Seeker</a>
        </div>
      </div>

      <div class="row g-5 align-items-center flex-lg-row-reverse">
        <div class="col-lg-6 reveal reveal-right">
          <img src="<?= e(app_url('assets/img/H3.jpg')) ?>" alt="Recruiters" class="bento-image shadow">
        </div>
        <div class="col-lg-6 reveal reveal-left">
          <div class="section-tag bg-orange-light text-orange">For Community Partners</div>
          <h2 class="display-5 fw-bold mb-4">We help you reach the people who matter.</h2>
          <ul class="list-unstyled">
             <li class="mb-3 d-flex gap-2">
                <i class="ri-checkbox-circle-fill text-indigo"></i>
                <span>List your services and programs for thousands of seekers.</span>
             </li>
             <li class="mb-3 d-flex gap-2">
                <i class="ri-checkbox-circle-fill text-indigo"></i>
                <span>Modern recruitment dashboard for managing applicants.</span>
             </li>
             <li class="mb-3 d-flex gap-2">
                <i class="ri-checkbox-circle-fill text-indigo"></i>
                <span>Support local community development through collaboration.</span>
             </li>
          </ul>
          <a href="<?= e(app_url('register-recruiter.php')) ?>" class="btn btn-brand mt-3">Join as Partner</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Subscribe Section -->
  <section class="section-premium">
    <div class="container">
      <div class="newsletter-box reveal reveal-scale">
        <h2 class="display-6 fw-bold mb-4">Subscribe to get resource updates</h2>
        <p class="mb-5 opacity-90 mx-auto" style="max-width: 600px;">Join our mailing list to receive the latest news, job postings, and community aid updates directly in your inbox.</p>
        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center mx-auto" style="max-width: 500px;">
          <input type="email" id="subscribeEmail" class="form-control form-control-lg border-0" placeholder="Enter your email" style="border-radius: 0.75rem;">
          <button id="subscribeBtn" class="btn btn-cta btn-lg">Subscribe</button>
        </div>
        <div id="subscribeFeedback" class="mt-3 small" style="display: none;"></div>
      </div>
    </div>
  </section>

  <?php require __DIR__ . '/includes/partials/footer-public.php'; ?>
</div>

<script>
  // Subscription Handler
  document.addEventListener('DOMContentLoaded', function() {
    const subscribeBtn = document.getElementById('subscribeBtn');
    const subscribeEmail = document.getElementById('subscribeEmail');
    const feedback = document.getElementById('subscribeFeedback');

    if (subscribeBtn) {
      subscribeBtn.addEventListener('click', async function() {
        const email = subscribeEmail.value.trim();
        if (!email) return;

        // Reset feedback
        feedback.style.display = 'none';
        subscribeBtn.disabled = true;
        subscribeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
          const formData = new FormData();
          formData.append('email', email);

          const response = await fetch('api/subscribe.php', {
            method: 'POST',
            body: formData
          });

          const result = await response.json();
          feedback.textContent = result.message || result.error;
          feedback.className = `mt-3 small ${result.success ? 'text-white fw-bold' : 'text-warning'}`;
          feedback.style.display = 'block';

          if (result.success) {
            subscribeEmail.value = '';
          }
        } catch (error) {
          feedback.textContent = 'Something went wrong. Please try again.';
          feedback.className = 'mt-3 small text-warning';
          feedback.style.display = 'block';
        } finally {
          subscribeBtn.disabled = false;
          subscribeBtn.textContent = 'Subscribe';
        }
      });
    }

    if (window.initHomeFeatured) window.initHomeFeatured();
  });
</script>

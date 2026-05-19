<?php
// Public top navigation redesigned for Premium Indigo aesthetic
?>
<?php
  $cur = basename($_SERVER['SCRIPT_NAME'] ?? '');
  function _active($name, $cur) {
    return $cur === $name ? ' active' : '';
  }
?>
<nav class="navbar navbar-expand-lg sticky-top navbar-glass py-2">
  <div class="container">
    <a class="navbar-brand navbar-brand-premium me-lg-5" href="<?= e(app_url('index.php')) ?>">
      <div class="logo-icon">
        <i class="ri-rocket-fill text-white" style="font-size: 1.25rem;"></i>
      </div>
      <span>UpLift<span style="color:var(--uplift-indigo)">Hub</span></span>
    </a>

    <button class="navbar-toggler border-0 shadow-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-controls="publicNav" aria-expanded="false" aria-label="Toggle navigation">
      <div class="hamburger-premium">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </button>

    <div class="collapse navbar-collapse" id="publicNav">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link nav-link-premium<?= _active('index.php', $cur) ?>" href="<?= e(app_url('index.php')) ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link nav-link-premium<?= _active('about.php', $cur) ?>" href="<?= e(app_url('about.php')) ?>">About</a></li>
        <li class="nav-item"><a class="nav-link nav-link-premium<?= _active('jobs.php', $cur) ?>" href="<?= e(app_url('jobs.php')) ?>">Jobs</a></li>
        <li class="nav-item"><a class="nav-link nav-link-premium<?= _active('financial-aid.php', $cur) ?>" href="<?= e(app_url('financial-aid.php')) ?>">Financial Aid</a></li>
        <li class="nav-item"><a class="nav-link nav-link-premium<?= _active('social-services.php', $cur) ?>" href="<?= e(app_url('social-services.php')) ?>">Services</a></li>
      </ul>

      <hr class="d-lg-none my-3 opacity-10">
      <div class="d-flex flex-column flex-lg-row gap-3 align-items-stretch align-items-lg-center mt-lg-0 pb-3 pb-lg-0">
        <a class="btn btn-outline-uplift d-lg-none fw-semibold shadow-none" href="<?= e(app_url('login.php')) ?>">Login</a>
        <a class="text-decoration-none fw-semibold text-muted hover-indigo small d-none d-lg-block" href="<?= e(app_url('login.php')) ?>" style="transition:0.2s">Login</a>
        
        <div class="dropdown">
          <button class="btn btn-brand w-100 px-4 py-2 shadow-sm" type="button" data-bs-toggle="dropdown" style="font-size:0.9rem;">
            Join Now
          </button>
          <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" style="border-radius:1rem; min-width:200px;">
            <li><div class="dropdown-header small text-uppercase fw-bold text-muted pb-2">Create Account</div></li>
            <li><a class="dropdown-item rounded-3 py-2" href="<?= e(app_url('register-jobseeker.php')) ?>">
              <i class="ri-user-smile-line me-2 color-indigo"></i> Job Seeker
            </a></li>
            <li><a class="dropdown-item rounded-3 py-2" href="<?= e(app_url('register-recruiter.php')) ?>">
              <i class="ri-briefcase-line me-2 color-indigo"></i> Employer
            </a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>

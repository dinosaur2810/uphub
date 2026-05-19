<?php
declare(strict_types=1);
$nav = $activeNav ?? '';
?>
<aside class="sidebar uplift-sidebar" id="jobseekerSidebar">
  <div class="sidebar-brand">
    <div class="logo-icon bg-indigo">
      <i class="ri-rocket-fill text-white"></i>
    </div>
    <div class="ms-2 logo-container">
      <span class="logo-text d-block">UpLiftHub</span>
      <small class="brand-sub" style="font-size: 0.7rem; letter-spacing: 0.05em; text-transform: uppercase; color: #94A3B8;">Job Seeker</small>
    </div>
    <button class="btn btn-sm sidebar-close-btn border-0 rounded-circle" onclick="toggleMobileSidebar()" aria-label="Close sidebar" style="background: rgba(255, 255, 255, 0.1); color: #FFFFFF; border: 1px solid #334155;">
      <i class="ri-close-line h4 mb-0"></i>
    </button>
  </div>

  <nav class="nav flex-column flex-grow-1">
    <a class="nav-link-admin <?= $nav === 'dashboard' ? 'active' : '' ?>" href="<?= e(app_url('jobseeker/dashboard.php')) ?>" title="Dashboard">
      <i class="ri-dashboard-fill"></i>
      <span>Dashboard</span>
    </a>

    <div class="sidebar-label">Discovery</div>
    
    <a class="nav-link-admin <?= $nav === 'jobs' ? 'active' : '' ?>" href="<?= e(app_url('jobseeker/jobs.php')) ?>" title="Browse Jobs">
      <i class="ri-search-eye-line"></i>
      <span>Browse Jobs</span>
    </a>

    <a class="nav-link-admin <?= $nav === 'aid' ? 'active' : '' ?>" href="<?= e(app_url('jobseeker/financial-aid.php')) ?>" title="Financial Aid">
      <i class="ri-hand-coin-line"></i>
      <span>Financial Aid</span>
    </a>

    <a class="nav-link-admin <?= $nav === 'services' ? 'active' : '' ?>" href="<?= e(app_url('jobseeker/social-services.php')) ?>" title="Social Services">
      <i class="ri-service-line"></i>
      <span>Social Services</span>
    </a>

    <div class="sidebar-label">Personal</div>

    <a class="nav-link-admin <?= $nav === 'profile' ? 'active' : '' ?>" href="<?= e(app_url('jobseeker/profile.php')) ?>" title="My Profile">
      <i class="ri-user-smile-line"></i>
      <span>My Profile</span>
    </a>

    <a class="nav-link-admin <?= $nav === 'applications' ? 'active' : '' ?>" href="<?= e(app_url('jobseeker/applications.php')) ?>" title="My Applications">
      <i class="ri-file-list-3-line"></i>
      <span>My Applications</span>
    </a>

    <div class="sidebar-label">System</div>

    <a class="nav-link-admin <?= $nav === 'notifications' ? 'active' : '' ?>" href="<?= e(app_url('jobseeker/notifications.php')) ?>" title="Notifications">
      <i class="ri-notification-3-line"></i>
      <span>Notifications</span>
    </a>

    <div class="mt-auto border-top pt-2 mb-3">
      <a class="nav-link-admin text-danger" href="<?= e(app_url('logout.php')) ?>" title="Logout">
        <i class="ri-logout-box-r-line"></i>
        <span>Logout</span>
      </a>
    </div>
  </nav>
  
  <button id="jobseekerCollapseToggle" class="btn btn-sm btn-light border m-2 d-none d-lg-block" style="border-radius: 8px;">
    <i class="ri-arrow-left-double-line"></i>
  </button>
</aside>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('jobseekerSidebar');
    const toggle = document.getElementById('jobseekerCollapseToggle');
    const icon = toggle ? toggle.querySelector('i') : null;
    
    // Load state
    if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
      sidebar.classList.add('collapsed');
      if (icon) icon.className = 'ri-arrow-right-double-line';
    }

    if (toggle) {
      toggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('admin_sidebar_collapsed', isCollapsed);
        if (icon) icon.className = isCollapsed ? 'ri-arrow-right-double-line' : 'ri-arrow-left-double-line';
      });
    }

    // Mobile toggle
    window.toggleMobileSidebar = function() {
        sidebar.classList.toggle('show');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (backdrop) backdrop.classList.toggle('show');
    };
  });
</script>

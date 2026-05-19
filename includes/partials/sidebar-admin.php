<?php
declare(strict_types=1);
$nav = $activeNav ?? '';
?>
<aside class="sidebar uplift-sidebar" id="dashboardSidebar">
  <div class="sidebar-brand">
    <div class="logo-icon bg-indigo">
      <i class="ri-rocket-fill text-white"></i>
    </div>
    <div class="ms-2 logo-container">
      <span class="logo-text d-block">UpLiftHub</span>
      <small class="brand-sub" style="font-size: 0.7rem; letter-spacing: 0.05em; text-transform: uppercase; color: #94A3B8;">Administration</small>
    </div>
    <button class="btn btn-sm sidebar-close-btn border-0 rounded-circle" onclick="toggleMobileSidebar()" aria-label="Close sidebar" style="background: rgba(255, 255, 255, 0.1); color: #FFFFFF; border: 1px solid #334155;">
      <i class="ri-close-line h4 mb-0"></i>
    </button>
  </div>

  <nav class="nav flex-column flex-grow-1">
    <a class="nav-link-admin <?= $nav === 'dashboard' ? 'active' : '' ?>" href="<?= e(app_url('admin/dashboard.php')) ?>" title="Dashboard">
      <i class="ri-dashboard-3-line"></i>
      <span>Dashboard</span>
    </a>
    <div class="sidebar-label">Management</div>
    <a class="nav-link-admin <?= $nav === 'users' ? 'active' : '' ?>" href="<?= e(app_url('admin/users.php')) ?>" title="Manage Users">
      <i class="ri-user-settings-line"></i>
      <span>Manage Users</span>
    </a>
    <a class="nav-link-admin <?= $nav === 'recruiter-approval' ? 'active' : '' ?>" href="<?= e(app_url('admin/recruiter-approval.php')) ?>" title="Recruiter Approval">
      <i class="ri-shield-user-line"></i>
      <span>Recruiter Approval</span>
    </a>
    <a class="nav-link-admin <?= $nav === 'job-approval' ? 'active' : '' ?>" href="<?= e(app_url('admin/job-approval.php')) ?>" title="Job Post Approval">
      <i class="ri-briefcase-line"></i>
      <span>Job Post Approval</span>
    </a>
    <a class="nav-link-admin <?= $nav === 'recruiter-listings' ? 'active' : '' ?>" href="<?= e(app_url('admin/recruiter-listings-approval.php')) ?>" title="Recruiter Listings">
      <i class="ri-list-check-3"></i>
      <span>Recruiter Listings</span>
    </a>
    <div class="sidebar-label">Resources</div>
    <a class="nav-link-admin <?= $nav === 'aid' ? 'active' : '' ?>" href="<?= e(app_url('admin/financial-aid.php')) ?>" title="Financial Aid">
      <i class="ri-hand-coin-line"></i>
      <span>Financial Aid</span>
    </a>
    <a class="nav-link-admin <?= $nav === 'services' ? 'active' : '' ?>" href="<?= e(app_url('admin/social-services.php')) ?>" title="Social Services">
      <i class="ri-heart-pulse-line"></i>
      <span>Social Services</span>
    </a>
    <a class="nav-link-admin <?= $nav === 'reports' ? 'active' : '' ?>" href="<?= e(app_url('admin/reports.php')) ?>" title="Reports">
      <i class="ri-bar-chart-2-line"></i>
      <span>Reports</span>
    </a>
    
    <div class="mt-auto border-top pt-2 mb-3">
      <a class="nav-link-admin text-danger" href="<?= e(app_url('logout.php')) ?>" title="Logout">
        <i class="ri-logout-box-line"></i>
        <span>Logout</span>
      </a>
    </div>
  </nav>
  
  <button id="collapseToggle" class="btn btn-sm btn-light border m-2 d-none d-lg-block" style="border-radius: 8px;">
    <i class="ri-arrow-left-double-line"></i>
  </button>
</aside>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('dashboardSidebar');
    const toggle = document.getElementById('collapseToggle');
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

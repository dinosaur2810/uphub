<?php
declare(strict_types=1);
$nav = $activeNav ?? '';
?>
<aside class="sidebar uplift-sidebar" id="recruiterSidebar">
  <div class="sidebar-brand">
    <div class="logo-icon bg-indigo">
      <i class="ri-briefcase-line text-white"></i>
    </div>
    <div class="ms-2 logo-container">
      <span class="logo-text d-block">UpLiftHub</span>
      <small class="brand-sub" style="font-size: 0.7rem; letter-spacing: 0.05em; text-transform: uppercase; color: #94A3B8;">Recruiter</small>
    </div>
    <button class="btn btn-sm sidebar-close-btn border-0 rounded-circle" onclick="toggleMobileSidebar()" aria-label="Close sidebar" style="background: rgba(255, 255, 255, 0.1); color: #FFFFFF; border: 1px solid #334155;">
      <i class="ri-close-line h4 mb-0"></i>
    </button>
  </div>

  <nav class="nav flex-column flex-grow-1">
    <a class="nav-link-admin <?= $nav === 'dashboard' ? 'active' : '' ?>" href="<?= e(app_url('recruiter/dashboard.php')) ?>" title="Dashboard">
      <i class="ri-dashboard-3-line"></i>
      <span>Dashboard</span>
    </a>

    <div class="sidebar-label">Management</div>
    
    <a class="nav-link-admin <?= $nav === 'profile' ? 'active' : '' ?>" href="<?= e(app_url('recruiter/profile.php')) ?>" title="Company Profile">
      <i class="ri-building-line"></i>
      <span>Company Profile</span>
    </a>

    <a class="nav-link-admin <?= $nav === 'jobs' ? 'active' : '' ?>" href="<?= e(app_url('recruiter/manage-postings.php')) ?>" title="Manage Listings">
      <i class="ri-list-settings-line"></i>
      <span>Manage Listings</span>
    </a>

    <a class="nav-link-admin <?= $nav === 'post' ? 'active' : '' ?>" href="<?= e(app_url('recruiter/post-job.php')) ?>" title="Post Listing">
      <i class="ri-add-box-line"></i>
      <span>Post Listing</span>
    </a>

    <a class="nav-link-admin <?= $nav === 'applicants' ? 'active' : '' ?>" href="<?= e(app_url('recruiter/applicants.php')) ?>" title="Applicants">
      <i class="ri-user-search-line"></i>
      <span>Applicants</span>
    </a>

    <div class="sidebar-label">System</div>

    <a class="nav-link-admin <?= $nav === 'notifications' ? 'active' : '' ?>" href="<?= e(app_url('recruiter/notifications.php')) ?>" title="Notifications">
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
  
  <button id="recruiterCollapseToggle" class="btn btn-sm btn-light border m-2 d-none d-lg-block" style="border-radius: 8px;">
    <i class="ri-arrow-left-double-line"></i>
  </button>
</aside>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('recruiterSidebar');
    const toggle = document.getElementById('recruiterCollapseToggle');
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

<?php
declare(strict_types=1);
$u = current_user();
if ($u === null) {
    return;
}
require __DIR__ . '/head.php';
?>
<div class="dashboard-wrapper d-flex min-vh-100">
  <?php
  match ($u['role']) {
      'job_seeker' => require __DIR__ . '/sidebar-jobseeker.php',
      'recruiter' => require __DIR__ . '/sidebar-recruiter.php',
      'admin' => require __DIR__ . '/sidebar-admin.php',
      default => require __DIR__ . '/sidebar-jobseeker.php',
  };
  ?>
  <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleMobileSidebar()"></div>
  <div class="dashboard-main flex-grow-1 d-flex flex-column bg-light">
    <?php require __DIR__ . '/topbar.php'; ?>
    <main class="dashboard-content flex-grow-1 p-3 p-md-4">
      <?php require __DIR__ . '/alerts.php'; ?>

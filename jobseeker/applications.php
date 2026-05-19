<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('job_seeker');

$pdo = db();
$uid = current_user()['id'];
$rows = $pdo->prepare(
    "SELECT a.id, a.status, a.applied_at, a.cover_letter, j.title, j.location, rp.company_name
     FROM applications a
     INNER JOIN jobs j ON j.id = a.job_id
     LEFT JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
     WHERE a.job_seeker_id = ?
     ORDER BY a.applied_at DESC"
);
$rows->execute([$uid]);
$list = $rows->fetchAll();

$pageTitle = 'My Applications';
$activeNav = 'applications';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">My Applications</h1>
          <p class="text-muted mb-0">Track the status of your submitted job applications.</p>
        </div>
        <div class="d-flex align-items-center">
           <div class="metric-icon indigo mb-0" style="width: 48px; height: 48px;">
             <i class="ri-send-plane-fill"></i>
           </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card-premium-admin p-0 overflow-hidden reveal reveal-up">
  <div class="table-responsive">
    <table class="table table-premium mb-0">
      <thead>
        <tr>
          <th class="ps-4">Position</th>
          <th>Company</th>
          <th>Location</th>
          <th>Status</th>
          <th>Applied Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$list): ?>
          <tr>
            <td colspan="5" class="text-center p-5">
              <i class="ri-inbox-line display-4 text-muted mb-3 d-block"></i>
              <p class="text-muted mb-0">You have not applied to any jobs yet.</p>
              <a href="<?= e(app_url('jobseeker/jobs.php')) ?>" class="btn btn-sm btn-indigo mt-3">Browse Jobs</a>
            </td>
          </tr>
        <?php endif; ?>
        <?php foreach ($list as $r): ?>
          <tr>
            <td class="ps-4">
               <div class="fw-bold text-dark" style="font-family: 'Outfit', sans-serif;"><?= e($r['title']) ?></div>
               <small class="text-muted">ID: #<?= e((string)$r['id']) ?></small>
            </td>
            <td>
               <div class="d-flex align-items-center gap-2">
                  <i class="ri-building-line text-muted"></i>
                  <span class="fw-semibold text-muted small"><?= e((string) $r['company_name']) ?></span>
               </div>
            </td>
            <td>
               <span class="small text-muted"><?= e((string) $r['location']) ?></span>
            </td>
            <td>
              <?php 
              $status = $r['status'];
              $badgeClass = 'badge-pending';
              if ($status === 'approved') $badgeClass = 'badge-approved';
              if ($status === 'rejected') $badgeClass = 'badge-rejected';
              if ($status === 'shortlisted') $badgeClass = 'badge-pending bg-info text-white border-0';
              ?>
              <span class="badge-admin <?= $badgeClass ?>"><?= e(ucfirst($status)) ?></span>
            </td>
            <td>
              <div class="d-flex align-items-center gap-2 text-muted small">
                 <i class="ri-calendar-line text-indigo"></i>
                 <span><?= date('M d, Y', strtotime((string)$r['applied_at'])) ?></span>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

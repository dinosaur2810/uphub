<?php



declare(strict_types=1);



require_once dirname(__DIR__) . '/includes/init.php';

require_once dirname(__DIR__) . '/includes/job_apply_post.php';

require_role('job_seeker');



$pdo = db();

$uid = current_user()['id'];



job_apply_handle_post($pdo, $uid, 'jobseeker/jobs.php');



$jobs = $pdo->query(

    "SELECT j.id, j.title, j.description, j.location, j.salary_range, j.created_at, u.name AS recruiter_name, rp.company_name

     FROM jobs j

     INNER JOIN users u ON u.id = j.recruiter_id

     LEFT JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id

     WHERE j.status = 'published'

     ORDER BY j.created_at DESC"

)->fetchAll();



$applied = [];

$st = $pdo->prepare('SELECT job_id FROM applications WHERE job_seeker_id = ?');

$st->execute([$uid]);

foreach ($st->fetchAll() as $r) {

    $applied[(int) $r['job_id']] = true;

}



function jobs_excerpt(string $text, int $max = 160): string

{

    $t = trim(preg_replace('/\s+/', ' ', $text));

    if (strlen($t) <= $max) {

        return $t;

    }

    return substr($t, 0, $max) . '…';

}



$pageTitle = 'Browse Jobs';

$activeNav = 'jobs';

require dirname(__DIR__) . '/includes/partials/dashboard-start.php';

?>

<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Browse Opportunities</h1>
          <p class="text-muted mb-0">Discover roles tailored to your skills and preferences.</p>
        </div>
        <div class="position-relative" style="max-width: 400px; width: 100%;">
          <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
          <input type="search" class="form-control ps-5 py-2" id="jobSearchInput" placeholder="Title, company, or location...">
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 reveal-stagger" id="jobCardGrid">
  <?php if (!$jobs): ?>
    <div class="col-12">
      <div class="card-premium-admin p-5 text-center">
        <i class="ri-briefcase-line display-4 text-muted mb-3"></i>
        <h3 class="h5 text-muted">No open positions yet</h3>
        <p class="text-muted small">Check back soon for new opportunities.</p>
      </div>
    </div>
  <?php endif; ?>

  <?php foreach ($jobs as $j):
      $company = $j['company_name'] ?: $j['recruiter_name'];
      $search = strtolower($j['title'] . ' ' . ($j['company_name'] ?? '') . ' ' . $j['location'] . ' ' . $j['description']);
      $desc = jobs_excerpt((string) $j['description'], 120);
      ?>
    <div class="col-12 col-md-6 col-xl-4 job-card-col reveal reveal-up" data-search="<?= e($search) ?>">
      <div class="card-premium-admin h-100 d-flex flex-column hover-lift">
        <div class="p-4 flex-grow-1">
          <div class="d-flex justify-content-between mb-2 align-items-start">
            <span class="badge-admin badge-approved"><?= e((string)$j['location']) ?></span>
            <div class="text-indigo fw-bold small"><?= e((string)$j['salary_range']) ?></div>
          </div>
          
          <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;"><?= e($j['title']) ?></h2>
          <div class="d-flex align-items-center gap-2 mb-3">
            <i class="ri-building-line text-muted small"></i>
            <span class="text-muted small fw-semibold"><?= e($company) ?></span>
          </div>

          <?php if ($desc !== ''): ?>
            <p class="small text-muted mb-0" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"><?= e($desc) ?></p>
          <?php endif; ?>
        </div>

        <div class="px-4 pb-4 mt-auto">
          <div class="d-flex align-items-center justify-content-between mb-3 border-top pt-3">
             <div class="small text-muted">
               <i class="ri-time-line me-1"></i><?= date('M d, Y', strtotime((string)$j['created_at'])) ?>
             </div>
          </div>
          
          <div class="d-grid">
            <?php if (!empty($applied[(int) $j['id']])): ?>
              <button class="btn btn-light border py-2 disabled">
                <i class="ri-checkbox-circle-line me-2 text-success"></i>Applied
              </button>
            <?php else: ?>
              <button type="button" class="btn btn-indigo py-2" data-bs-toggle="modal" data-bs-target="#applyModal<?= (int) $j['id'] ?>">
                View Details
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>



<?php require dirname(__DIR__) . '/includes/partials/job_apply_modals.php'; ?>

<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>


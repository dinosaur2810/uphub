<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('recruiter');

if (!recruiter_is_approved()) {
    flash_set('warning', 'Applicant review is available after account approval.');
    redirect('recruiter/dashboard.php');
}

$pdo = db();
$uid = current_user()['id'];

// Handle application status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['application_id'])) {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
    } else {
        $applicationId = (int) $_POST['application_id'];
        $action = (string) $_POST['action'];
        $newStatus = '';
        $statusMessage = '';
        
        // Verify this application belongs to current recruiter
        $verifyStmt = $pdo->prepare(
            "SELECT a.id FROM applications a
             INNER JOIN jobs j ON j.id = a.job_id
             WHERE a.id = ? AND j.recruiter_id = ?"
        );
        $verifyStmt->execute([$applicationId, $uid]);
        
        if ($verifyStmt->fetch()) {
            if ($action === 'approve') {
                $newStatus = 'approved';
                $statusMessage = 'Application approved';
            } elseif ($action === 'reject') {
                $newStatus = 'rejected';
                $statusMessage = 'Application rejected';
            } elseif ($action === 'shortlist') {
                $newStatus = 'shortlisted';
                $statusMessage = 'Application shortlisted';
            }
            
            if ($newStatus !== '') {
                $updateStmt = $pdo->prepare(
                    "UPDATE applications SET status = ? WHERE id = ?"
                );
                $updateStmt->execute([$newStatus, $applicationId]);
                
                // Get job seeker info for notification
                $notifyStmt = $pdo->prepare(
                    "SELECT u.id, u.email, u.name, j.title FROM applications a
                     INNER JOIN users u ON u.id = a.job_seeker_id
                     INNER JOIN jobs j ON j.id = a.job_id
                     WHERE a.id = ?"
                );
                $notifyStmt->execute([$applicationId]);
                $applicant = $notifyStmt->fetch();
                
                if ($applicant) {
                    $subject = "Application Status Update: $statusMessage";
                    $message = "Your application for '{$applicant['title']}' has been $statusMessage.";
                    
                    // Use the actual user_id for notification
                    notify_user($pdo, (int) $applicant['id'], $subject, $message);
                }
                
                flash_set('success', "Application $statusMessage successfully.");
            }
        } else {
            flash_set('danger', 'Application not found or access denied.');
        }
    }
    
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF'] . (isset($_GET['q']) ? '?q=' . urlencode($_GET['q']) : ''));
    exit;
}

$q = trim((string) ($_GET['q'] ?? ''));
$sql = "SELECT a.id, a.status, a.applied_at, a.cover_letter, j.title AS job_title, j.id AS job_id,
        u.name AS seeker_name, u.email AS seeker_email, a.guest_name, a.guest_email
        FROM applications a
        INNER JOIN jobs j ON j.id = a.job_id
        LEFT JOIN users u ON u.id = a.job_seeker_id
        WHERE j.recruiter_id = ?";
$params = [$uid];
if ($q !== '') {
    $sql .= ' AND (j.title LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR a.guest_name LIKE ? OR a.guest_email LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$sql .= ' ORDER BY a.applied_at DESC';
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

$pageTitle = 'Applicants';
$activeNav = 'applicants';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
          <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Applicant Directory</h2>
          <p class="text-muted small mb-0">Track and manage candidates for your active job listings.</p>
        </div>
        <form class="d-flex gap-2" method="get" action="">
          <div class="input-group input-group-sm" style="width: 280px;">
            <span class="input-group-text bg-white border-end-0"><i class="ri-search-line text-muted"></i></span>
            <input type="search" class="form-control border-start-0" name="q" placeholder="Search job, name or email..." value="<?= e($q) ?>">
          </div>
          <button type="submit" class="btn btn-indigo btn-sm px-3" style="border-radius: 8px;">Filter</button>
        </form>
      </div>

      <div class="table-responsive">
        <table class="table-premium">
          <thead>
            <tr>
              <th>Candidate</th>
              <th>Applied For</th>
              <th>Status</th>
              <th>Date Applied</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="5" class="text-center text-muted p-5">No applications found matching your criteria.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
              <?php
                $statusBadge = match($r['status']) {
                    'approved' => 'approved',
                    'rejected' => 'rejected',
                    'shortlisted' => 'pending',
                    default => 'pending'
                };
                $name = (string) ($r['seeker_name'] ?? $r['guest_name'] ?? 'Guest');
                $email = (string) ($r['seeker_email'] ?? $r['guest_email'] ?? 'No email');
              ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-indigo-soft text-indigo rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.9rem;">
                      <?= e(strtoupper(substr($name, 0, 1))) ?>
                    </div>
                    <div>
                      <div class="fw-bold small"><?= e($name) ?></div>
                      <div class="text-muted" style="font-size: 0.75rem;">
                        <a href="javascript:void(0)" 
                           class="text-decoration-none text-muted hover-indigo"
                           data-bs-toggle="modal" 
                           data-bs-target="#contactModal"
                           data-application-id="<?= e((string)$r['id']) ?>"
                           data-name="<?= e($name) ?>"
                           data-email="<?= e($email) ?>"
                           data-job-title="<?= e((string)$r['job_title']) ?>">
                          <i class="ri-mail-line me-1"></i><?= e($email) ?>
                        </a>
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="fw-semibold small"><?= e($r['job_title']) ?></div>
                </td>
                <td><span class="badge-admin badge-<?= $statusBadge ?>"><?= e(ucfirst($r['status'])) ?></span></td>
                <td class="small text-muted"><?= date('M d, Y', strtotime((string)$r['applied_at'])) ?></td>
                <td class="text-end">
                  <div class="d-flex justify-content-end gap-2">
                    <a href="javascript:void(0)" 
                       class="btn btn-sm btn-light border" 
                       title="Email Candidate"
                       data-bs-toggle="modal" 
                       data-bs-target="#contactModal"
                       data-application-id="<?= e((string)$r['id']) ?>"
                       data-name="<?= e($name) ?>"
                       data-email="<?= e($email) ?>"
                       data-job-title="<?= e((string)$r['job_title']) ?>">
                      <i class="ri-mail-send-line text-indigo"></i>
                    </a>
                    <?php if ($r['status'] === 'submitted' || $r['status'] === 'shortlisted'): ?>
                      <form method="post" action="" class="d-inline">
                        <input type="hidden" name="application_id" value="<?= e((string) $r['id']) ?>">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                        
                        <?php if ($r['status'] === 'submitted'): ?>
                          <button type="submit" name="action" value="shortlist" class="btn btn-sm btn-light border" title="Shortlist"><i class="ri-star-line text-warning"></i></button>
                        <?php endif; ?>
                        
                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-light border" title="Hire/Approve"><i class="ri-check-line text-success"></i></button>
                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-light border text-danger" title="Reject"><i class="ri-close-line"></i></button>
                      </form>
                    <?php else: ?>
                      <span class="text-muted small">Finalized</span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php if (trim((string) $r['cover_letter']) !== ''): ?>
                <tr>
                  <td colspan="5" class="py-0 border-0">
                    <div class="px-5 pb-3 pt-1">
                      <div class="p-3 bg-light rounded-3 small border-start border-4 border-indigo-subtle">
                        <div class="fw-bold text-muted mb-1" style="font-size: 0.65rem; text-transform: uppercase;">Cover Letter / Note:</div>
                        <?= nl2br(e((string) $r['cover_letter'])) ?>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php 
require_once dirname(__DIR__) . '/includes/partials/contact-modal.php';
require_once dirname(__DIR__) . '/includes/partials/dashboard-end.php'; 
?>

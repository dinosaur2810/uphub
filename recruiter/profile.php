<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('recruiter');

$pdo = db();
$uid = current_user()['id'];

$st = $pdo->prepare(
    'SELECT u.name, u.email, rp.company_name, rp.contact, rp.location, rp.industry, rp.company_logo
     FROM users u
     INNER JOIN recruiter_profiles rp ON rp.user_id = u.id
     WHERE u.id = ? LIMIT 1'
);
$st->execute([$uid]);
$row = $st->fetch() ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $company = trim((string) ($_POST['company_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $contact = trim((string) ($_POST['contact'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $industry = trim((string) ($_POST['industry'] ?? ''));

        if ($name === '' || $company === '' || $contact === '' || $industry === '') {
            flash_set('warning', 'Please fill required fields.');
        } elseif ($email === '' || !validate_email($email)) {
            flash_set('warning', 'Valid email is required.');
        } else {
            $dup = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
            $dup->execute([$email, $uid]);
            if ($dup->fetch()) {
                flash_set('warning', 'That email is already in use.');
            } else {
                $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?')->execute([$name, $email, $uid]);
                $pdo->prepare(
                    'UPDATE recruiter_profiles SET company_name = ?, contact = ?, location = ?, industry = ? WHERE user_id = ?'
                )->execute([$company, $contact, $location, $industry, $uid]);

                if (!empty($_FILES['company_logo']['name'])) {
                    $logo = save_uploaded_image($_FILES['company_logo'], 'logos', 'rc_' . $uid);
                    if ($logo) {
                        $pdo->prepare('UPDATE recruiter_profiles SET company_logo = ? WHERE user_id = ?')->execute([$logo, $uid]);
                    }
                }
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                flash_set('success', 'Company profile updated.');
                redirect('recruiter/profile.php');
            }
        }
    }
    $st->execute([$uid]);
    $row = $st->fetch() ?: [];
}

$pageTitle = 'Company Profile';
$activeNav = 'profile';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
$logo = $row['company_logo'] ?? '';
?>
<div class="row g-4 mt-2">
  <div class="col-lg-8">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <div class="mb-4">
        <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Company Profile</h2>
        <p class="text-muted small mb-0">Manage your organization's public identity and contact information.</p>
      </div>

      <form method="post" enctype="multipart/form-data" data-live-validate="true" novalidate>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label small fw-bold">Primary Contact Name</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="ri-user-settings-line text-muted"></i></span>
              <input type="text" class="form-control border-start-0" name="name" required value="<?= e((string) ($row['name'] ?? '')) ?>">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Company / Organization</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="ri-building-line text-muted"></i></span>
              <input type="text" class="form-control border-start-0" name="company_name" required value="<?= e((string) ($row['company_name'] ?? '')) ?>">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Official Email</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="ri-mail-line text-muted"></i></span>
              <input type="email" class="form-control border-start-0" name="email" required value="<?= e((string) ($row['email'] ?? '')) ?>">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Industry Sector</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="ri-community-line text-muted"></i></span>
              <input type="text" class="form-control border-start-0" name="industry" required value="<?= e((string) ($row['industry'] ?? '')) ?>">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Contact Phone/Direct</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="ri-phone-line text-muted"></i></span>
              <input type="text" class="form-control border-start-0" name="contact" required value="<?= e((string) ($row['contact'] ?? '')) ?>">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Headquarters Location</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="ri-map-pin-line text-muted"></i></span>
              <input type="text" class="form-control border-start-0" name="location" value="<?= e((string) ($row['location'] ?? '')) ?>">
            </div>
          </div>
          
          <div class="col-12">
            <label class="form-label small fw-bold">Company Logo</label>
            <div class="d-flex align-items-center gap-4 p-3 bg-light rounded-3 border">
              <div class="flex-shrink-0">
                <?php if ($logo): ?>
                  <img src="<?= e(app_url('uploads/' . $logo)) ?>" alt="Logo" class="rounded-3 border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;" id="logoPreview">
                <?php else: ?>
                  <div class="rounded-3 border bg-white d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;" id="logoPreviewPlaceholder">
                    <i class="ri-image-add-line h3 text-muted mb-0"></i>
                  </div>
                  <img src="" alt="" id="logoPreview" class="rounded-3 border shadow-sm d-none" style="width: 80px; height: 80px; object-fit: cover;">
                <?php endif; ?>
              </div>
              <div class="flex-grow-1">
                <input type="file" class="form-control form-control-sm" id="company_logo" name="company_logo" accept="image/*" data-preview-target="logoPreview">
                <div class="text-muted mt-1" style="font-size: 0.65rem;">Recommended size: 200x200px. PNG, JPG or WebP.</div>
              </div>
            </div>
          </div>
        </div>
        
        <hr class="my-4 opacity-50">
        
        <div class="d-flex justify-content-end gap-2">
           <button type="submit" class="btn btn-indigo px-4 py-2">Save Profile Changes</button>
        </div>
      </form>
    </div>
  </div>
  
  <div class="col-lg-4">
    <div class="card-premium-admin p-4 h-100 reveal reveal-up">
      <h3 class="h6 fw-bold mb-3" style="font-family: 'Outfit', sans-serif;">Profile Status</h3>
      <div class="p-3 rounded-3 bg-indigo-soft mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="small fw-bold text-indigo">Completion</span>
          <span class="small fw-bold text-indigo">85%</span>
        </div>
        <div class="progress" style="height: 6px;">
          <div class="progress-bar bg-indigo" style="width: 85%"></div>
        </div>
      </div>
      
      <div class="small text-muted mb-3">
        <div class="d-flex gap-2 mb-2">
          <i class="ri-checkbox-circle-fill text-success"></i>
          <span>Account verified</span>
        </div>
        <div class="d-flex gap-2 mb-2">
          <i class="ri-checkbox-circle-fill text-success"></i>
          <span>Email linked</span>
        </div>
        <div class="d-flex gap-2 mb-2">
          <i class="ri-checkbox-circle-fill text-success"></i>
          <span>Public logo set</span>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

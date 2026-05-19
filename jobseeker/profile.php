<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('job_seeker');

$pdo = db();
$uid = current_user()['id'];

$st = $pdo->prepare(
    'SELECT u.name, u.email, p.contact, p.education, p.skills, p.location, p.income_bracket, p.profile_picture
     FROM users u
     LEFT JOIN job_seeker_profiles p ON p.user_id = u.id
     WHERE u.id = ? LIMIT 1'
);
$st->execute([$uid]);
$row = $st->fetch() ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $contact = trim((string) ($_POST['contact'] ?? ''));
        $education = trim((string) ($_POST['education'] ?? ''));
        $skills = trim((string) ($_POST['skills'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $income = trim((string) ($_POST['income_bracket'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        if ($name === '' || $contact === '') {
            flash_set('warning', 'Name and contact are required.');
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
                    'UPDATE job_seeker_profiles SET contact = ?, education = ?, skills = ?, location = ?, income_bracket = ? WHERE user_id = ?'
                )->execute([$contact, $education, $skills, $location, $income, $uid]);

                if (!empty($_FILES['profile_picture']['name'])) {
                    $pic = save_uploaded_image($_FILES['profile_picture'], 'profiles', 'js_' . $uid);
                    if ($pic) {
                        $pdo->prepare('UPDATE job_seeker_profiles SET profile_picture = ? WHERE user_id = ?')->execute([$pic, $uid]);
                    }
                }
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                flash_set('success', 'Profile updated successfully.');
                redirect('jobseeker/profile.php');
            }
        }
    }
    refresh_session_user($pdo);
    $st->execute([$uid]);
    $row = $st->fetch() ?: [];
}

$pageTitle = 'My Profile';
$activeNav = 'profile';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';

$incomeOptions = [
    '' => 'Select…',
    'Under $15,000' => 'Under $15,000',
    '$15,000 - $25,000' => '$15,000 - $25,000',
    '$25,000 - $40,000' => '$25,000 - $40,000',
    '$40,000+' => '$40,000+',
    'Prefer not to say' => 'Prefer not to say',
];
$pic = $row['profile_picture'] ?? '';
?>
<div class="row g-4 mb-4 mt-2">
  <div class="col-12">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">My Profile</h1>
          <p class="text-muted mb-0">Manage your personal information, resume details, and preferences.</p>
        </div>
        <div class="d-flex align-items-center">
           <div class="metric-icon indigo mb-0" style="width: 48px; height: 48px;">
             <i class="ri-user-settings-line"></i>
           </div>
        </div>
      </div>
    </div>
  </div>
</div>

<form method="post" enctype="multipart/form-data" data-live-validate="true" novalidate>
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  
  <div class="row g-4">
    <!-- Left Column: Basic Info -->
    <div class="col-lg-8">
      <div class="card-premium-admin p-4 mb-4 reveal reveal-up">
        <h2 class="h5 fw-bold mb-4" style="font-family: 'Outfit', sans-serif;">
          <i class="ri-profile-line text-indigo me-2"></i>Basic Information
        </h2>
        
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted h6" for="name">FULL NAME</label>
            <input type="text" class="form-control py-2" id="name" name="name" required value="<?= e((string) ($row['name'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted h6" for="email">EMAIL ADDRESS</label>
            <input type="email" class="form-control py-2" id="email" name="email" required value="<?= e((string) ($row['email'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted h6" for="contact">CONTACT PHONE</label>
            <input type="text" class="form-control py-2" id="contact" name="contact" required value="<?= e((string) ($row['contact'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold text-muted h6" for="location">CURRENT LOCATION</label>
            <input type="text" class="form-control py-2" id="location" name="location" value="<?= e((string) ($row['location'] ?? '')) ?>">
          </div>
        </div>
      </div>

      <div class="card-premium-admin p-4 mb-4 reveal reveal-up">
        <h2 class="h5 fw-bold mb-4" style="font-family: 'Outfit', sans-serif;">
          <i class="ri-graduation-cap-line text-indigo me-2"></i>Career & Education
        </h2>
        
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label small fw-bold text-muted h6" for="education">EDUCATION HISTORY</label>
            <textarea class="form-control" id="education" name="education" rows="3" placeholder="List your degrees, certifications, or workshops..."><?= e((string) ($row['education'] ?? '')) ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label small fw-bold text-muted h6" for="skills">TECHNICAL & SOFT SKILLS</label>
            <textarea class="form-control" id="skills" name="skills" rows="3" placeholder="e.g. JavaScript, Project Management, Graphic Design..."><?= e((string) ($row['skills'] ?? '')) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Picture & Meta -->
    <div class="col-lg-4">
      <div class="card-premium-admin p-4 mb-4 text-center reveal reveal-up">
        <h2 class="h6 fw-bold mb-4 text-start" style="font-family: 'Outfit', sans-serif;">Profile Picture</h2>
        <div class="mb-4 d-flex justify-content-center">
          <div class="position-relative">
            <?php if ($pic): ?>
              <img src="<?= e(app_url('uploads/' . $pic)) ?>" alt="" class="rounded-circle border border-4 border-white shadow" style="width: 120px; height: 120px; object-fit: cover;" id="jsPreview">
            <?php else: ?>
              <div class="rounded-circle bg-light border border-4 border-white shadow d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;" id="jsPreviewPlaceholder">
                <i class="ri-user-line display-6 text-muted"></i>
              </div>
              <img src="" alt="" id="jsPreview" class="rounded-circle border border-4 border-white shadow d-none" style="width: 120px; height: 120px; object-fit: cover;">
            <?php endif; ?>
          </div>
        </div>
        <div class="mb-3 text-start">
           <label class="form-label small fw-bold text-muted h6" for="profile_picture">UPDATE PHOTO</label>
           <input type="file" class="form-control form-control-sm" id="profile_picture" name="profile_picture" accept="image/*" data-preview-target="jsPreview">
           <small class="text-muted d-block mt-2">JPG, PNG or GIF. Max 2MB.</small>
        </div>
      </div>

      <div class="card-premium-admin p-4 mb-4 reveal reveal-up">
        <h2 class="h6 fw-bold mb-4" style="font-family: 'Outfit', sans-serif;">Job Preferences</h2>
        <div class="mb-0">
          <label class="form-label small fw-bold text-muted h6" for="income_bracket">INCOME BRACKET</label>
          <select class="form-select" id="income_bracket" name="income_bracket">
            <?php
            $cur = (string) ($row['income_bracket'] ?? '');
            foreach ($incomeOptions as $val => $label) {
                echo '<option value="' . e($val) . '"' . ($cur === $val ? ' selected' : '') . '>' . e($label) . '</option>';
            }
            ?>
          </select>
          <p class="small text-muted mt-3 mb-0">Set your expected income range to help us match you with better opportunities.</p>
        </div>
      </div>

      <div class="d-grid gap-3">
        <button type="submit" class="btn btn-indigo py-3 fw-bold">
          <i class="ri-save-line me-2"></i>Save Profile Changes
        </button>
        <a href="<?= e(app_url('jobseeker/dashboard.php')) ?>" class="btn btn-light border py-2">Cancel</a>
      </div>
    </div>
  </div>
</form>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

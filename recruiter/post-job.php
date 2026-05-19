<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('recruiter');

if (!recruiter_is_approved()) {
    flash_set('warning', 'You cannot post listings until an administrator approves your account.');
    redirect('recruiter/dashboard.php');
}

$pdo = db();
$uid = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        flash_set('danger', 'Invalid session.');
    } else {
        $listingType = (string) ($_POST['listing_type'] ?? 'job');
        $admins = $pdo->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll();

        if ($listingType === 'job') {
            $title = trim((string) ($_POST['title'] ?? ''));
            $desc = trim((string) ($_POST['description'] ?? ''));
            $loc = trim((string) ($_POST['location'] ?? ''));
            $exactAddr = trim((string) ($_POST['exact_address'] ?? ''));
            $sal = trim((string) ($_POST['salary_range'] ?? ''));
            if ($title === '') {
                flash_set('warning', 'Job title is required.');
            } elseif ($exactAddr === '') {
                flash_set('warning', 'Exact address is required.');
            } else {
                $ins = $pdo->prepare(
                    'INSERT INTO jobs (recruiter_id, title, description, location, exact_address, salary_range, status) VALUES (?,?,?,?,?,?,?)'
                );
                $ins->execute([$uid, $title, $desc, $loc, $exactAddr, $sal, 'pending_approval']);
                foreach ($admins as $a) {
                    notify_user($pdo, (int) $a['id'], 'New job pending approval: ' . $title, 'info');
                }
                flash_set('success', 'Job submitted for admin approval.');
                redirect('recruiter/manage-postings.php');
            }
        } elseif ($listingType === 'financial_aid') {
            $title = trim((string) ($_POST['fa_title'] ?? ''));
            $desc = trim((string) ($_POST['fa_description'] ?? ''));
            $elig = trim((string) ($_POST['fa_eligibility'] ?? ''));
            $contact = trim((string) ($_POST['fa_contact_info'] ?? ''));
            $exactAddr = trim((string) ($_POST['fa_exact_address'] ?? ''));
            if ($title === '') {
                flash_set('warning', 'Program title is required.');
            } elseif ($exactAddr === '') {
                flash_set('warning', 'Exact address is required.');
            } else {
                $ins = $pdo->prepare(
                    'INSERT INTO financial_aid_programs (title, description, eligibility, contact_info, exact_address, status, created_by, posted_by_user_id, moderation_status) VALUES (?,?,?,?,?,?,?,?,?)'
                );
                $ins->execute([$title, $desc, $elig, $contact, $exactAddr, 'active', $uid, $uid, 'pending_approval']);
                foreach ($admins as $a) {
                    notify_user($pdo, (int) $a['id'], 'New financial aid listing pending approval: ' . $title, 'info');
                }
                flash_set('success', 'Financial aid program submitted for admin approval.');
                redirect('recruiter/manage-postings.php');
            }
        } elseif ($listingType === 'social_service') {
            $name = trim((string) ($_POST['service_name'] ?? ''));
            $desc = trim((string) ($_POST['service_description'] ?? ''));
            $addr = trim((string) ($_POST['service_exact_address'] ?? ''));
            $cat = trim((string) ($_POST['service_category'] ?? ''));
            $phone = trim((string) ($_POST['service_phone'] ?? ''));
            $lat = ($_POST['service_latitude'] ?? '') !== '' ? (float) $_POST['service_latitude'] : null;
            $lng = ($_POST['service_longitude'] ?? '') !== '' ? (float) $_POST['service_longitude'] : null;
            if ($name === '') {
                flash_set('warning', 'Service name is required.');
            } elseif ($addr === '') {
                flash_set('warning', 'Exact address is required.');
            } else {
                $ins = $pdo->prepare(
                    'INSERT INTO social_services (name, description, exact_address, category, phone, latitude, longitude, posted_by_user_id, moderation_status) VALUES (?,?,?,?,?,?,?,?,?)'
                );
                $ins->execute([$name, $desc, $addr, $cat, $phone, $lat, $lng, $uid, 'pending_approval']);
                foreach ($admins as $a) {
                    notify_user($pdo, (int) $a['id'], 'New social service listing pending approval: ' . $name, 'info');
                }
                flash_set('success', 'Social service submitted for admin approval.');
                redirect('recruiter/manage-postings.php');
            }
        } else {
            flash_set('warning', 'Invalid listing type.');
        }
    }
}

$pageTitle = 'Post listing';
$activeNav = 'post';
require dirname(__DIR__) . '/includes/partials/dashboard-start.php';
?>
<div class="row g-4 mt-2">
  <div class="col-lg-8">
    <div class="card-premium-admin p-4 reveal reveal-fade">
      <div class="mb-4">
        <h2 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Create New Listing</h2>
        <p class="text-muted small mb-0">Select a category and provide details for your job opening, financial aid, or social service.</p>
      </div>

      <form method="post" id="listingForm" novalidate>
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        
        <div class="mb-5">
          <label class="form-label small fw-bold text-uppercase tracking-wider">Listing Category</label>
          <div class="row g-3">
            <div class="col-md-4">
              <input type="radio" class="btn-check" name="listing_type" id="typeJob" value="job" checked autocomplete="off">
              <label class="btn btn-outline-indigo w-100 py-3 d-flex flex-column align-items-center gap-2" for="typeJob" style="border-radius: 12px; border-width: 2px;">
                <i class="ri-briefcase-line h4 mb-0"></i>
                <span class="small fw-bold">Job Opening</span>
              </label>
            </div>
            <div class="col-md-4">
              <input type="radio" class="btn-check" name="listing_type" id="typeAid" value="financial_aid" autocomplete="off">
              <label class="btn btn-outline-indigo w-100 py-3 d-flex flex-column align-items-center gap-2" for="typeAid" style="border-radius: 12px; border-width: 2px;">
                <i class="ri-hand-coin-line h4 mb-0"></i>
                <span class="small fw-bold">Financial Aid</span>
              </label>
            </div>
            <div class="col-md-4">
              <input type="radio" class="btn-check" name="listing_type" id="typeService" value="social_service" autocomplete="off">
              <label class="btn btn-outline-indigo w-100 py-3 d-flex flex-column align-items-center gap-2" for="typeService" style="border-radius: 12px; border-width: 2px;">
                <i class="ri-heart-pulse-line h4 mb-0"></i>
                <span class="small fw-bold">Social Service</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Job Fields -->
        <div data-listing-fields="job" class="form-section-animate">
          <div class="row g-4">
            <div class="col-12">
              <label class="form-label small fw-bold">Work Title</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="ri-text"></i></span>
                <input type="text" class="form-control border-start-0" id="title" name="title" placeholder="e.g. Senior Software Engineer">
              </div>
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Introduction & Requirements</label>
              <textarea class="form-control" id="description" name="description" rows="5" placeholder="Outline the role, responsibilities, and qualifications..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">General Location</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="ri-map-pin-2-line"></i></span>
                <input type="text" class="form-control border-start-0" id="location" name="location" placeholder="City or Remote">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Salary Range / Rate</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="ri-money-dollar-circle-line"></i></span>
                <input type="text" class="form-control border-start-0" id="salary_range" name="salary_range" placeholder="e.g. $2,000 - $3,000 / month">
              </div>
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Precise Office/Work Address *</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="ri-compass-3-line"></i></span>
                <input type="text" class="form-control border-start-0" id="exact_address" name="exact_address" placeholder="Unit 123, Building Name, Street, City" required>
              </div>
              <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">This address will be used to show the location on our interactive maps.</small>
            </div>
          </div>
        </div>

        <!-- Financial Aid Fields -->
        <div data-listing-fields="financial_aid" class="form-section-animate d-none">
          <div class="row g-4">
            <div class="col-12">
              <label class="form-label small fw-bold">Program Title</label>
              <input type="text" class="form-control" id="fa_title" name="fa_title" placeholder="e.g. Educational Scholarship 2024">
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Program Benefits & Description</label>
              <textarea class="form-control" id="fa_description" name="fa_description" rows="4" placeholder="Detail what the program offers..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Who is Eligible?</label>
              <textarea class="form-control" id="fa_eligibility" name="fa_eligibility" rows="3" placeholder="Requirements for applicants (age, income, location etc.)"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Program Point of Contact</label>
              <input type="text" class="form-control" id="fa_contact_info" name="fa_contact_info" placeholder="Email or direct line">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Filing Address *</label>
              <input type="text" class="form-control" id="fa_exact_address" name="fa_exact_address" placeholder="Physical location to submit documents" required>
            </div>
          </div>
        </div>

        <!-- Social Service Fields -->
        <div data-listing-fields="social_service" class="form-section-animate d-none">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label small fw-bold">Service or Facility Name</label>
              <input type="text" class="form-control" id="service_name" name="service_name" placeholder="e.g. Community Health Center">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Category</label>
              <input type="text" class="form-control" id="service_category" name="service_category" placeholder="Health, Food, Employment...">
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Description of Services</label>
              <textarea class="form-control" id="service_description" name="service_description" rows="4"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Main Contact Number</label>
              <input type="text" class="form-control" id="service_phone" name="service_phone" placeholder="09XX XXX XXXX">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Service Delivery Address *</label>
              <input type="text" class="form-control" id="service_exact_address" name="service_exact_address" placeholder="Specific physical location" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">Latitude (Map)</label>
              <input type="text" class="form-control form-control-sm" id="service_latitude" name="service_latitude">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-muted">Longitude (Map)</label>
              <input type="text" class="form-control form-control-sm" id="service_longitude" name="service_longitude">
            </div>
          </div>
        </div>

        <hr class="my-5 opacity-50">

        <div class="d-flex justify-content-end gap-3">
          <a href="<?= e(app_url('recruiter/manage-postings.php')) ?>" class="btn btn-light px-4">Discard</a>
          <button type="submit" class="btn btn-indigo px-5 py-2">Submit Listing for Review</button>
        </div>
      </form>
    </div>
  </div>
  
  <div class="col-lg-4">
    <div class="card-premium-admin p-4 reveal reveal-up">
      <h3 class="h6 fw-bold mb-3" style="font-family: 'Outfit', sans-serif;"><i class="ri-information-line me-2 text-indigo"></i>Submission Guidelines</h3>
      <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
        <li class="d-flex gap-3">
          <div class="bg-green-soft text-green rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;">
            <i class="ri-check-line small"></i>
          </div>
          <span class="small text-muted">Every submission is reviewed by an moderator before being listed publicly.</span>
        </li>
        <li class="d-flex gap-3">
          <div class="bg-green-soft text-green rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;">
            <i class="ri-check-line small"></i>
          </div>
          <span class="small text-muted">Be clear and concise with your title to attract the right audience.</span>
        </li>
        <li class="d-flex gap-3">
          <div class="bg-blue-soft text-blue rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;">
            <i class="ri-question-line small"></i>
          </div>
          <span class="small text-muted">Use the "Exact Address" field to help candidates find the location on the map.</span>
        </li>
      </ul>
    </div>
  </div>
</div>

<style>
.form-section-animate {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.btn-check:checked + label {
    background: rgba(79, 70, 229, 0.08) !important;
    border-color: #4f46e5 !important;
    color: #4f46e5 !important;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioButtons = document.querySelectorAll('input[name="listing_type"]');
    const sections = document.querySelectorAll('[data-listing-fields]');
    
    function switchSection(type) {
        sections.forEach(sec => {
            if (sec.getAttribute('data-listing-fields') === type) {
                sec.classList.remove('d-none');
                sec.style.opacity = '0';
                sec.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    sec.style.opacity = '1';
                    sec.style.transform = 'translateY(0)';
                }, 50);
            } else {
                sec.classList.add('d-none');
            }
        });
    }

    radioButtons.forEach(radio => {
        radio.addEventListener('change', () => switchSection(radio.value));
    });
});
</script>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>
<?php require dirname(__DIR__) . '/includes/partials/dashboard-end.php'; ?>

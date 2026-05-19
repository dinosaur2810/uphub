<?php
/**
 * Contact Modal for Recruiters to message Job Seekers.
 * Requires Bootstrap 5 and Remix Icon.
 */
?>
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
      <div class="modal-header border-0 bg-indigo text-white p-4">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-white bg-opacity-25 rounded-circle p-2">
            <i class="ri-mail-send-line fs-4"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold" id="contactModalLabel" style="font-family: 'Outfit', sans-serif;">Message Candidate</h5>
            <p class="small mb-0 opacity-75">Your message will be sent via email.</p>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="contactForm">
          <input type="hidden" name="application_id" id="contact_application_id">
          <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
          
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">TO:</label>
            <div id="contact_candidate_name" class="fw-bold text-dark fs-5 mb-1"></div>
            <div id="contact_candidate_email" class="text-muted small"></div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">SUBJECT</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="ri-edit-line text-muted"></i></span>
              <input type="text" name="subject" id="contact_subject" class="form-control border-start-0 bg-light" placeholder="Enter subject line..." required>
            </div>
          </div>

          <div class="mb-0">
            <label class="form-label small fw-bold text-muted">MESSAGE</label>
            <textarea name="message" id="contact_message" class="form-control bg-light" rows="5" placeholder="Write your message here..." required style="resize: none;"></textarea>
          </div>
        </form>

        <div id="contactAlert" class="alert mt-3 d-none animate__animated animate__fadeIn" role="alert" style="border-radius: 12px; font-size: 0.9rem;"></div>
      </div>
      <div class="modal-footer border-0 p-4 pt-0">
        <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 500;">Cancel</button>
        <button id="contactSubmit" type="button" class="btn btn-indigo px-4 py-2" style="border-radius: 10px; font-weight: 500;">
          <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
          <span>Send Message</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactModal = document.getElementById('contactModal');
    if (!contactModal) return;

    // Handle opening modal via data attributes
    contactModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const appId = button.getAttribute('data-application-id');
        const name = button.getAttribute('data-name');
        const email = button.getAttribute('data-email');
        const jobTitle = button.getAttribute('data-job-title');

        document.getElementById('contact_application_id').value = appId;
        document.getElementById('contact_candidate_name').textContent = name;
        document.getElementById('contact_candidate_email').textContent = email;
        document.getElementById('contact_subject').value = 'Regarding your application for ' + jobTitle;
        document.getElementById('contact_message').value = 'Hello ' + name.split(' ')[0] + ',\n\n';
        
        // Reset alert
        const alert = document.getElementById('contactAlert');
        alert.classList.add('d-none');
    });

    // Handle AJAX submission
    const submitBtn = document.getElementById('contactSubmit');
    submitBtn.addEventListener('click', function() {
        const form = document.getElementById('contactForm');
        const alert = document.getElementById('contactAlert');
        const spinner = submitBtn.querySelector('.spinner-border');
        const btnText = submitBtn.querySelector('span:not(.spinner-border)');

        // Validate
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // UI State
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Sending...';
        alert.classList.add('d-none');

        const formData = new FormData(form);

        fetch('api/contact_candidate.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert.classList.remove('d-none');
            if (data.success) {
                alert.className = 'alert alert-success mt-3 animate__animated animate__fadeIn';
                alert.textContent = data.message;
                setTimeout(() => {
                    bootstrap.Modal.getInstance(contactModal).hide();
                }, 2000);
            } else {
                alert.className = 'alert alert-danger mt-3 animate__animated animate__fadeIn';
                alert.textContent = data.message;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            alert.classList.remove('d-none');
            alert.className = 'alert alert-danger mt-3';
            alert.textContent = 'A connection error occurred. Please try again.';
            submitBtn.disabled = false;
        })
        .finally(() => {
            spinner.classList.add('d-none');
            if (!alert.classList.contains('alert-success')) {
                btnText.textContent = 'Send Message';
            }
        });
    });
});
</script>

<?php
// Simple modal used across public pages for demonstration. JS handles submit via API.
?>
<div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="applyModalLabel">Apply</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="applyForm">
          <input type="hidden" name="item_type" id="apply_item_type" value="">
          <input type="hidden" name="item_id" id="apply_item_id" value="">
          <div class="mb-2">
            <label class="form-label">Your name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Message / cover note</label>
            <textarea name="message" class="form-control" rows="3"></textarea>
          </div>
        </form>
        <div id="applyFormAlert" class="alert d-none" role="alert"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button id="applyFormSubmit" type="button" class="btn btn-primary-uplift">Send application</button>
      </div>
    </div>
  </div>
</div>

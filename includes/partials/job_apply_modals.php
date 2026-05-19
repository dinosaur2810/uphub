<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $jobs */
/** @var array<int, true> $applied */

if (empty($jobs) || !is_array($jobs)) {
    return;
}
$applied = $applied ?? [];

foreach ($jobs as $j) {
    if (!empty($applied[(int) $j['id']])) {
        continue;
    }
    ?>
  <div class="modal fade" id="applyModal<?= (int) $j['id'] ?>" tabindex="-1" aria-labelledby="applyModalLabel<?= (int) $j['id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="post" action="">
          <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="apply">
          <input type="hidden" name="job_id" value="<?= (int) $j['id'] ?>">
          <div class="modal-header">
            <h2 class="modal-title h5" id="applyModalLabel<?= (int) $j['id'] ?>">Apply — <?= e($j['title']) ?></h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <label class="form-label" for="cover<?= (int) $j['id'] ?>">Cover letter (optional)</label>
            <textarea class="form-control" id="cover<?= (int) $j['id'] ?>" name="cover_letter" rows="4" placeholder="Briefly introduce yourself…"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary-uplift">Submit application</button>
          </div>
        </form>
      </div>
    </div>
  </div>
    <?php
}

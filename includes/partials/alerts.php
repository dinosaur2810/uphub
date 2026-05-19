<?php
declare(strict_types=1);
$f = flash_get();
if ($f):
  $map = [
    'success' => 'success',
    'danger' => 'danger',
    'warning' => 'warning',
    'info' => 'info',
  ];
  $cls = $map[$f['type']] ?? 'info';
?>
<div class="alert alert-<?= e($cls) ?> alert-dismissible fade show" role="alert">
  <?= e($f['message']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

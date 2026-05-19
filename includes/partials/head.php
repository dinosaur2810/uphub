<?php
declare(strict_types=1);
$title = $pageTitle ?? 'UpLiftHub';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> — UpLiftHub</title>
  <link rel="icon" type="image/png" href="<?= e(app_url('assets/img/favicon.png')) ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(app_url('assets/css/uplifthub.css')) ?>">
  <link rel="stylesheet" href="<?= e(app_url('assets/css/animations.css')) ?>">
  <?php if (str_contains($_SERVER['REQUEST_URI'], '/admin/') || str_contains($_SERVER['REQUEST_URI'], '/recruiter/') || str_contains($_SERVER['REQUEST_URI'], '/jobseeker/')): ?>
    <link rel="stylesheet" href="<?= e(app_url('assets/css/admin-premium.css')) ?>">
  <?php endif; ?>
</head>
<body class="uplift-body <?= (str_contains($_SERVER['REQUEST_URI'], '/admin/') || str_contains($_SERVER['REQUEST_URI'], '/recruiter/') || str_contains($_SERVER['REQUEST_URI'], '/jobseeker/')) ? 'admin-body' : '' ?>">

<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

if (!csrf_verify($_POST['_csrf'] ?? null)) {
    flash_set('danger', 'Invalid session. Please try again.');
    redirect('login.php');
}

$email = trim((string) ($_POST['email'] ?? ''));
if ($email === '' || !validate_email($email)) {
    flash_set('warning', 'Please enter a valid email address.');
    redirect('login.php');
}

$pdo = db();
$st = $pdo->prepare('SELECT id, email, name FROM users WHERE email = ? LIMIT 1');
$st->execute([$email]);
$user = $st->fetch();

if ($user) {
    $token = bin2hex(random_bytes(32));
    $exp = (new DateTimeImmutable('+' . RESET_TOKEN_HOURS . ' hours'))->format('Y-m-d H:i:s');
    $up = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
    $up->execute([$token, $exp, $user['id']]);
    $resetLink = app_url('reset-password.php?token=' . rawurlencode($token), true);
    error_log('[UpLiftHub] Password reset link for ' . $email . ': ' . $resetLink);
    send_notification_email($email, 'UpLiftHub password reset', 'Reset link: ' . $resetLink);
}

flash_set('success', 'If an account exists for that email, you will receive a password reset link shortly.');
redirect('login.php');

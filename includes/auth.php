<?php

declare(strict_types=1);

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => (int) $_SESSION['user_id'],
        'email' => (string) ($_SESSION['user_email'] ?? ''),
        'name' => (string) ($_SESSION['user_name'] ?? ''),
        'role' => (string) ($_SESSION['user_role'] ?? ''),
        'recruiter_status' => (string) ($_SESSION['recruiter_status'] ?? 'n/a'),
    ];
}

function login_user(array $row): void
{
    $_SESSION['user_id'] = (int) $row['id'];
    $_SESSION['user_email'] = $row['email'];
    $_SESSION['user_name'] = $row['name'];
    $_SESSION['user_role'] = $row['role'];
    $_SESSION['recruiter_status'] = $row['recruiter_status'] ?? 'n/a';
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function require_login(): void
{
    if (current_user() === null) {
        flash_set('warning', 'Please sign in to continue.');
        redirect('login.php');
    }
}

function require_role(string $role): void
{
    require_login();
    $u = current_user();
    if ($u === null || $u['role'] !== $role) {
        flash_set('danger', 'You do not have access to that page.');
        if ($u !== null) {
            match ($u['role']) {
                'job_seeker' => redirect('jobseeker/dashboard.php'),
                'recruiter' => redirect('recruiter/dashboard.php'),
                'admin' => redirect('admin/dashboard.php'),
                default => redirect('login.php'),
            };
        }
        redirect('login.php');
    }
}

function recruiter_is_approved(): bool
{
    $u = current_user();
    return $u !== null && $u['role'] === 'recruiter' && ($u['recruiter_status'] ?? '') === 'approved';
}

function refresh_session_user(PDO $pdo): void
{
    $u = current_user();
    if ($u === null) {
        return;
    }
    $st = $pdo->prepare('SELECT id, email, name, role, recruiter_status FROM users WHERE id = ? LIMIT 1');
    $st->execute([$u['id']]);
    $row = $st->fetch();
    if ($row) {
        login_user($row);
    }
}

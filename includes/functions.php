<?php

declare(strict_types=1);

function app_url(string $path = '', bool $absolute = false): string
{
    $base = rtrim(APP_BASE, '/');
    $path = ltrim($path, '/');
    
    if ($absolute && defined('APP_URL')) {
        $url = rtrim(APP_URL, '/');
        return $path === '' ? $url . '/' : $url . '/' . $path;
    }

    if ($path === '') {
        return $base === '' ? '/' : $base . '/';
    }
    return ($base === '' ? '' : $base) . '/' . $path;
}

function redirect(string $path): void
{
    if (str_starts_with($path, 'http')) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . app_url($path));
    }
    exit;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['_flash'])) {
        return null;
    }
    $f = $_SESSION['_flash'];
    unset($_SESSION['_flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_verify(?string $token): bool
{
    return is_string($token) && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}

function old(string $key, string $default = ''): string
{
    $v = $_SESSION['_old'][$key] ?? $default;
    return is_string($v) ? htmlspecialchars($v, ENT_QUOTES, 'UTF-8') : $default;
}

function old_clear(): void
{
    unset($_SESSION['_old']);
}

function set_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Min 8 chars, at least one letter and one number.
 */
function validate_password_strength(string $password): bool
{
    if (strlen($password) < 8) {
        return false;
    }
    return (bool) preg_match('/[A-Za-z]/', $password) && (bool) preg_match('/[0-9]/', $password);
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function save_uploaded_image(array $file, string $subdir, string $prefix): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    if (($file['size'] ?? 0) > UPLOAD_MAX_IMAGE_BYTES) {
        return null;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_MIMES, true)) {
        return null;
    }
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $ext = $extMap[$mime] ?? null;
    if ($ext === null) {
        return null;
    }
    $dir = dirname(__DIR__) . '/uploads/' . $subdir;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }
    return $subdir . '/' . $name;
}

/**
 * Sends an email notification using the Web3Forms API.
 */
/**
 * Sends an email notification using Symfony Mailer.
 */
function send_notification_email(string $to, string $subject, string $body): bool
{
    $dsn = MAILER_DSN;
    $from = MAILER_FROM;
    
    // Fallback to error log if no DSN is configured or using default
    if (empty($dsn) || $dsn === 'smtp://localhost:1025') {
        error_log('[UpLiftHub Email STUB] Symfony Mailer DSN not configured. To: ' . $to . ' | Sub: ' . $subject);
        return true;
    }

    try {
        $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
        $mailer = new \Symfony\Component\Mailer\Mailer($transport);
        
        $email = (new \Symfony\Component\Mime\Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->text($body);

        $mailer->send($email);
        return true;
    } catch (\Exception $e) {
        error_log('[UpLiftHub Email Error] Symfony Mailer failed: ' . $e->getMessage());
        return false;
    }
}

function notify_user(PDO $pdo, int $userId, string $message, string $type = 'info'): void
{
    $st = $pdo->prepare('INSERT INTO notifications (user_id, message, type) VALUES (?,?,?)');
    $st->execute([$userId, $message, $type]);
}

<?php
/**
 * UpLiftHub application configuration.
 * Environment variables override defaults (Docker / Jenkins / production).
 * Optional config.local.php for local secrets (gitignored).
 */

declare(strict_types=1);

if (is_readable(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
    return;
}

$env = static function (string $key, string $default = ''): string {
    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
};

define('APP_BASE', $env('APP_BASE', '/UpHub'));
define('APP_URL', $env('APP_URL', 'http://localhost/UpHub'));

define('DB_HOST', $env('DB_HOST', '127.0.0.1'));
define('DB_NAME', $env('DB_NAME', 'uphub'));
define('DB_USER', $env('DB_USER', 'root'));
define('DB_PASS', $env('DB_PASS', ''));
define('DB_CHARSET', $env('DB_CHARSET', 'utf8mb4'));

define('UPLOAD_MAX_IMAGE_BYTES', (int) $env('UPLOAD_MAX_IMAGE_BYTES', (string) (2 * 1024 * 1024)));
define('ALLOWED_IMAGE_MIMES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

define('GOOGLE_MAPS_API_KEY', $env('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY'));
define('MAILER_DSN', $env('MAILER_DSN', 'null://null'));
define('MAILER_FROM', $env('MAILER_FROM', 'noreply@example.com'));

define('SESSION_NAME', $env('SESSION_NAME', 'UPLIFTHUB_SESS'));
define('RESET_TOKEN_HOURS', (int) $env('RESET_TOKEN_HOURS', '24'));

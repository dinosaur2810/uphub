<?php
/**
 * Copy to config.local.php and adjust for local XAMPP development.
 * Docker and Jenkins set the same values via environment variables.
 */

declare(strict_types=1);

define('APP_BASE', '/UpHub');
define('APP_URL', 'http://localhost/UpHub');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'uphub');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('UPLOAD_MAX_IMAGE_BYTES', 2 * 1024 * 1024);
define('ALLOWED_IMAGE_MIMES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');
define('MAILER_DSN', 'null://null');
define('MAILER_FROM', 'noreply@example.com');

define('SESSION_NAME', 'UPLIFTHUB_SESS');
define('RESET_TOKEN_HOURS', 24);

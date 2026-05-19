<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

logout_user();
session_name(SESSION_NAME);
session_start();
flash_set('info', 'You have been signed out.');
redirect('login.php');

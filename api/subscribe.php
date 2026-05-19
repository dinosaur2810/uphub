<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$email = trim((string)($_POST['email'] ?? ''));

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email address is required.']);
    exit;
}

if (!validate_email($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please provide a valid email address.']);
    exit;
}

try {
    $pdo = db();
    
    // Check if duplicate
    $st = $pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
    $st->execute([$email]);
    if ($st->fetch()) {
        http_response_code(200); // OK, but already exists
        echo json_encode(['success' => true, 'message' => 'You are already subscribed!']);
        exit;
    }

    // Insert new subscriber
    $st = $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)");
    $st->execute([$email]);

    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing to UpLiftHub updates!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

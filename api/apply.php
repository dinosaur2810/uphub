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

$pdo = db();
$user = current_user();
$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$itemType = trim((string)($_POST['item_type'] ?? 'job'));
$itemId = (int)($_POST['item_id'] ?? 0);
$message = trim((string)($_POST['message'] ?? ''));

if ($itemId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid listing ID.']);
    exit;
}

// Validation for guests
if ($user === null && ($name === '' || $email === '')) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name and email are required for guest applications.']);
    exit;
}

try {
    $seekerId = $user ? $user['id'] : null;
    $gName = $user ? null : $name;
    $gEmail = $user ? null : $email;
    $jobId = ($itemType === 'job') ? $itemId : null;

    $st = $pdo->prepare(
        "INSERT INTO applications (item_type, job_id, job_seeker_id, guest_name, guest_email, cover_letter, status) 
         VALUES (?,?,?,?,?,?,?)"
    );
    $st->execute([$itemType, $jobId, $seekerId, $gName, $gEmail, $message, 'submitted']);
    $newId = $pdo->lastInsertId();

    // Notify the owner (recruiter/poster)
    $ownerId = null;
    $itemTitle = 'Listing';
    if ($itemType === 'job') {
        $st = $pdo->prepare("SELECT recruiter_id, title FROM jobs WHERE id = ?");
        $st->execute([$itemId]);
        $row = $st->fetch();
        $ownerId = $row ? (int) $row['recruiter_id'] : null;
        $itemTitle = $row ? $row['title'] : 'Job';
    } elseif ($itemType === 'financial_aid') {
        $st = $pdo->prepare("SELECT posted_by_user_id, title FROM financial_aid_programs WHERE id = ?");
        $st->execute([$itemId]);
        $row = $st->fetch();
        $ownerId = $row ? (int) $row['posted_by_user_id'] : null;
        $itemTitle = $row ? $row['title'] : 'Aid Program';
    } else { // social_service
        $st = $pdo->prepare("SELECT posted_by_user_id, name FROM social_services WHERE id = ?");
        $st->execute([$itemId]);
        $row = $st->fetch();
        $ownerId = $row ? (int) $row['posted_by_user_id'] : null;
        $itemTitle = $row ? $row['name'] : 'Social Service';
    }

    if ($ownerId) {
        $from = $user ? $user['name'] : $name;
        notify_user($pdo, $ownerId, "New application for '{$itemTitle}' from {$from}", 'info');
    }

    echo json_encode(['success' => true, 'message' => 'Application received successfully.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

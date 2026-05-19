<?php

declare(strict_types=1);

require_once dirname(dirname(__DIR__)) . '/includes/init.php';

// JSON Response helper
function json_response(bool $success, string $message): void {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Security Checks
$u = current_user();
if (!$u || $u['role'] !== 'recruiter') {
    json_response(false, 'Unauthorized access.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

if (!csrf_verify($_POST['_csrf'] ?? null)) {
    json_response(false, 'Invalid session token.');
}

// Data Validation
$applicationId = (int) ($_POST['application_id'] ?? 0);
$subject = trim((string) ($_POST['subject'] ?? ''));
$messageBody = trim((string) ($_POST['message'] ?? ''));

if ($applicationId <= 0 || $subject === '' || $messageBody === '') {
    json_response(false, 'Please fill in all required fields.');
}

$pdo = db();
$uid = current_user()['id'];

try {
    // 1. Verify ownership and get candidate info
    $st = $pdo->prepare("
        SELECT a.id, a.job_seeker_id, u.email as candidate_email, u.name as candidate_name, j.title as job_title
        FROM applications a
        INNER JOIN jobs j ON j.id = a.job_id
        INNER JOIN users u ON u.id = a.job_seeker_id
        WHERE a.id = ? AND j.recruiter_id = ?
        LIMIT 1
    ");
    $st->execute([$applicationId, $uid]);
    $app = $st->fetch();

    if (!$app) {
        json_response(false, 'Application not found or access denied.');
    }

    // 2. Send Email via Symfony Mailer
    $emailSent = send_notification_email(
        (string) $app['candidate_email'],
        $subject,
        $messageBody
    );

    if (!$emailSent) {
        json_response(false, 'Failed to send email. Please check your mail configuration.');
    }

    // 3. Log Communication to Database
    $logSt = $pdo->prepare("
        INSERT INTO communication_logs (application_id, sender_id, receiver_id, subject, message)
        VALUES (?, ?, ?, ?, ?)
    ");
    $logSt->execute([
        $applicationId,
        $uid,
        (int) $app['job_seeker_id'],
        $subject,
        $messageBody
    ]);

    // 4. (Bonus) In-App Notification for Candidate
    $notifMsg = "New message from recruiter regarding your application for '" . $app['job_title'] . "': " . $subject;
    notify_user($pdo, (int) $app['job_seeker_id'], $notifMsg, 'info');

    json_response(true, 'Message sent successfully!');

} catch (Exception $e) {
    error_log('[Contact API Error] ' . $e->getMessage());
    json_response(false, 'A server error occurred while sending your message.');
}

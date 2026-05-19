<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = db();
$st = $pdo->query(
    "SELECT f.id, f.title, f.description AS short, COALESCE(f.contact_info, rp.company_name, 'UpLiftHub') AS provider, 
     'grant' AS type, '2026-12-31' AS deadline, f.eligibility AS requirements_str
     FROM financial_aid_programs f
     LEFT JOIN recruiter_profiles rp ON rp.user_id = f.posted_by_user_id
     WHERE f.moderation_status = 'published' ORDER BY f.created_at DESC"
);
$res = $st->fetchAll(PDO::FETCH_ASSOC);

$aid = [];
foreach ($res as $r) {
    // Front-end expects an array for requirements
    $r['requirements'] = $r['requirements_str'] ? explode("\n", $r['requirements_str']) : [];
    unset($r['requirements_str']);
    $aid[] = $r;
}

echo json_encode(['data' => $aid]);

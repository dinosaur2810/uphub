<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = db();
$st = $pdo->query(
    "SELECT j.id, j.title, j.description AS short, COALESCE(rp.company_name, u.name, 'UpLiftHub') AS company, 
     COALESCE(j.location, j.exact_address) AS location, 'Social Work' AS category, j.salary_range AS salary, 
     'full-time' AS type, '[]' AS requirements_json
     FROM jobs j
     JOIN users u ON u.id = j.recruiter_id
     LEFT JOIN recruiter_profiles rp ON rp.user_id = j.recruiter_id
     WHERE j.status IN ('published','approved') ORDER BY j.created_at DESC"
);
$res = $st->fetchAll(PDO::FETCH_ASSOC);

$jobs = [];
foreach ($res as $r) {
    $r['requirements'] = json_decode((string) $r['requirements_json'], true) ?: [];
    unset($r['requirements_json']);
    $jobs[] = $r;
}

echo json_encode(['data' => $jobs]);

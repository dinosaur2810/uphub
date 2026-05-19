<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/init.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = db();
$st = $pdo->query(
    "SELECT id, name, description AS short, exact_address AS location, category, phone AS contact, latitude AS lat, longitude AS lng 
     FROM social_services WHERE moderation_status = 'published' ORDER BY created_at DESC"
);
$services = $st->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['data' => $services]);

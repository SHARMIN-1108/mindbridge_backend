<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$title = trim($input['title'] ?? '');
$time = trim($input['time'] ?? '8:00 AM');
$period = strtoupper(trim($input['period'] ?? 'MORNING'));
$subtitle = trim($input['subtitle'] ?? '');

if (empty($title)) {
    sendJson(400, ['success' => false, 'message' => 'Title is required']);
}

if (!in_array($period, ['MORNING', 'AFTERNOON', 'EVENING'])) {
    $period = 'MORNING';
}

$stmt = $pdo->prepare('INSERT INTO daily_schedules (user_id, title, time, period, subtitle, is_completed, schedule_date) VALUES (:uid, :title, :time, :period, :subtitle, 0, CURDATE())');
$stmt->execute([
    'uid' => $user['id'],
    'title' => $title,
    'time' => $time,
    'period' => $period,
    'subtitle' => empty($subtitle) ? null : $subtitle
]);

sendJson(201, [
    'success' => true,
    'message' => 'Routine item created',
    'id' => (string)$pdo->lastInsertId()
]);

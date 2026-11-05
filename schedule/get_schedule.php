<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT id, time, title, period, is_completed, subtitle FROM daily_schedules WHERE user_id = :uid ORDER BY id ASC');
$stmt->execute(['uid' => $user['id']]);
$rows = $stmt->fetchAll();

$items = [];
foreach ($rows as $row) {
    $items[] = [
        'id' => (string)$row['id'],
        'time' => $row['time'],
        'title' => $row['title'],
        'period' => $row['period'],
        'isCompleted' => (bool)$row['is_completed'],
        'subtitle' => $row['subtitle']
    ];
}

sendJson(200, [
    'success' => true,
    'dateText' => date('l, d F'),
    'subtitleText' => 'One gentle step at a time.',
    'items' => $items
]);

<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT id, title, subtitle, status, is_completed, is_today, reminder_time, reminder_date FROM reminders WHERE user_id = :uid ORDER BY is_today DESC, id ASC');
$stmt->execute(['uid' => $user['id']]);
$rows = $stmt->fetchAll();

$reminders = [];
foreach ($rows as $row) {
    $timeOrDate = $row['subtitle'] ?: ($row['reminder_time'] . ' - ' . $row['status']);
    $reminders[] = [
        'id' => (string)$row['id'],
        'title' => $row['title'],
        'timeOrDate' => $timeOrDate,
        'status' => $row['status'] ?: ($row['is_completed'] ? 'Completed' : 'To do'),
        'isCompleted' => (bool)$row['is_completed'],
        'isToday' => (bool)$row['is_today']
    ];
}

sendJson(200, [
    'success' => true,
    
    'dateText' => date('l, d F'),
    'subtitleText' => 'A little reminder for everyday moments.',
    'reminders' => $reminders
]);

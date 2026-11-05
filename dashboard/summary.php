<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$hour = (int)date('G');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 17) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

$dateText = date('l, d F Y');

// Fetch today's reminders
$stmt = $pdo->prepare('SELECT id, title, reminder_time as time, is_completed FROM reminders WHERE user_id = :uid AND is_today = 1 ORDER BY id ASC');
$stmt->execute(['uid' => $user['id']]);
$reminders = $stmt->fetchAll();

// Format reminders
$reminderList = [];
$completedCount = 0;
foreach ($reminders as $r) {
    $isDone = (bool)$r['is_completed'];
    if ($isDone) $completedCount++;
    $reminderList[] = [
        'id' => (string)$r['id'],
        'title' => $r['title'],
        'time' => $r['time'],
        'isCompleted' => $isDone
    ];
}

sendJson(200, [
    'success' => true,
    'dashboard' => [
        'userName' => $user['preferred_name'] ?: $user['name'],
        'dateText' => $dateText,
        'greeting' => $greeting,
        'reminders' => $reminderList,
        'completedCount' => $completedCount,
        'totalCount' => count($reminderList)
    ]
]);

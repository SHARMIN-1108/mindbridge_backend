<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$title = trim($input['title'] ?? '');
$time = trim($input['time'] ?? '10:00 AM');
$date = trim($input['date'] ?? 'Today');
$isToday = (!empty($input['isToday']) || strtolower($date) === 'today') ? 1 : 0;

if (empty($title)) {
    sendJson(400, ['success' => false, 'message' => 'Title is required']);
}

$subtitle = $time . ' - To do';
$stmt = $pdo->prepare('INSERT INTO reminders (user_id, title, reminder_time, reminder_date, subtitle, status, is_completed, is_today) VALUES (:uid, :title, :time, :date, :subtitle, "To do", 0, :isToday)');
$stmt->execute([
    'uid' => $user['id'],
    'title' => $title,
    'time' => $time,
    'date' => $date,
    'subtitle' => $subtitle,
    'isToday' => $isToday
]);

sendJson(201, [
    'success' => true,
    'message' => 'Reminder created successfully',
    'id' => (string)$pdo->lastInsertId()
]);

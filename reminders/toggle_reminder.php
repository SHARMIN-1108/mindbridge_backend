<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$id = $input['id'] ?? null;
if (!$id) {
    sendJson(400, ['success' => false, 'message' => 'Reminder ID is required']);
}

$stmt = $pdo->prepare('SELECT id, is_completed, reminder_time FROM reminders WHERE id = :id AND user_id = :uid LIMIT 1');
$stmt->execute(['id' => $id, 'uid' => $user['id']]);
$rem = $stmt->fetch();

if (!$rem) {
    sendJson(404, ['success' => false, 'message' => 'Reminder not found']);
}

$newStatus = $rem['is_completed'] ? 0 : 1;
$statusText = $newStatus ? 'Completed' : 'To do';
$newSubtitle = $rem['reminder_time'] . ' · ' . $statusText;

$update = $pdo->prepare('UPDATE reminders SET is_completed = :status, status = :statusText, subtitle = :subtitle WHERE id = :id');
$update->execute([
    'status' => $newStatus,
    'statusText' => $statusText,
    'subtitle' => $newSubtitle,
    'id' => $id
]);

sendJson(200, [
    'success' => true,
    'message' => 'Reminder toggled successfully',
    'id' => (string)$id,
    'isCompleted' => (bool)$newStatus,
    'status' => $statusText
]);

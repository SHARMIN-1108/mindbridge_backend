<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$id = $input['id'] ?? null;
if (!$id) {
    sendJson(400, ['success' => false, 'message' => 'Schedule item ID required']);
}

$stmt = $pdo->prepare('SELECT id, is_completed FROM daily_schedules WHERE id = :id AND user_id = :uid LIMIT 1');
$stmt->execute(['id' => $id, 'uid' => $user['id']]);
$item = $stmt->fetch();

if (!$item) {
    sendJson(404, ['success' => false, 'message' => 'Schedule item not found']);
}

$newStatus = $item['is_completed'] ? 0 : 1;
$update = $pdo->prepare('UPDATE daily_schedules SET is_completed = :status, subtitle = IF(:status = 1, "Done", subtitle) WHERE id = :id');
$update->execute(['status' => $newStatus, 'id' => $id]);

sendJson(200, [
    'success' => true,
    'message' => 'Schedule updated successfully',
    'id' => (string)$id,
    'isCompleted' => (bool)$newStatus
]);

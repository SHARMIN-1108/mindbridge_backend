<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$id = $input['id'] ?? null;
if (!$id) {
    sendJson(400, ['success' => false, 'message' => 'Conversation ID is required']);
}

$stmt = $pdo->prepare('DELETE FROM ai_conversations WHERE id = :id AND user_id = :uid');
$stmt->execute(['id' => $id, 'uid' => $user['id']]);

sendJson(200, [
    'success' => true,
    'message' => 'Conversation removed'
]);

<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$address = trim($input['address'] ?? '');
$iconEmoji = trim($input['iconEmoji'] ?? '🏡');

if (empty($title)) {
    sendJson(400, ['success' => false, 'message' => 'Place title is required']);
}

$stmt = $pdo->prepare('INSERT INTO safe_places (user_id, title, description, address, icon_emoji) VALUES (:uid, :title, :description, :address, :icon)');
$stmt->execute([
    'uid' => $user['id'],
    'title' => $title,
    'description' => $description,
    'address' => $address,
    'icon' => $iconEmoji
]);

sendJson(201, [
    'success' => true,
    'message' => 'Safe place added successfully',
    'id' => (string)$pdo->lastInsertId()
]);

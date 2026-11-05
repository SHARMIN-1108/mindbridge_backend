<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$name = trim($input['name'] ?? '');
$relationship = trim($input['relationship'] ?? '');
$notes = trim($input['notes'] ?? '');

if (empty($name)) {
    sendJson(400, ['success' => false, 'message' => 'Name is required']);
}

$stmt = $pdo->prepare('INSERT INTO important_people (user_id, name, relationship, notes) VALUES (:uid, :name, :rel, :notes)');
$stmt->execute([
    'uid' => $user['id'],
    'name' => $name,
    'rel' => $relationship,
    'notes' => $notes
]);

sendJson(201, [
    'success' => true,
    'message' => 'Person added successfully',
    'id' => (string)$pdo->lastInsertId()
]);

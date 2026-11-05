<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$title = trim($input['title'] ?? 'New Photo');
$category = strtoupper(trim($input['category'] ?? 'MY_FAMILY'));
$date = trim($input['date'] ?? date('d F Y'));
$personName = trim($input['personName'] ?? $user['name']);

$stmt = $pdo->prepare('INSERT INTO album_photos (user_id, title, date_taken, category, emoji_thumb, is_selected, person_name) VALUES (:uid, :title, :date, :cat, "👥", 0, :pname)');
$stmt->execute([
    'uid' => $user['id'],
    'title' => $title,
    'date' => $date,
    'cat' => $category,
    'pname' => $personName
]);

sendJson(201, [
    'success' => true,
    'message' => 'Photo added successfully',
    'id' => (string)$pdo->lastInsertId()
]);

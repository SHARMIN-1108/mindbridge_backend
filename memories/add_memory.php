<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$title = trim($input['title'] ?? '');
$date = trim($input['date'] ?? date('d F Y'));
$description = trim($input['description'] ?? '');
$people = trim($input['associatedPeople'] ?? $input['people'] ?? '');

if (empty($title)) {
    sendJson(400, ['success' => false, 'message' => 'Memory title is required']);
}

$subtitle = !empty($people) ? "$people in the moment." : $title;

$stmt = $pdo->prepare(
    'INSERT INTO memories (user_id, title, memory_date, subtitle, description, associated_people, media_status)
     VALUES (:uid, :title, :date, :subtitle, :description, :people, "Saved successfully")'
);

$stmt->execute([
    'uid' => $user['id'],
    'title' => $title,
    'date' => $date,
    'subtitle' => $subtitle,
    'description' => $description,
    'people' => $people
]);

$id = $pdo->lastInsertId();

// Also add a corresponding entry to album_photos if category is relevant
$albumStmt = $pdo->prepare(
    'INSERT INTO album_photos (user_id, title, date_taken, category, emoji_thumb, is_selected, person_name)
     VALUES (:uid, :title, :date, "MY_FAMILY", "👥", 0, :person)'
);
$albumStmt->execute([
    'uid' => $user['id'],
    'title' => $title,
    'date' => $date,
    'person' => $people ?: $user['name']
]);

sendJson(201, [
    'success' => true,
    'message' => 'Memory created successfully',
    'id' => (string)$id
]);

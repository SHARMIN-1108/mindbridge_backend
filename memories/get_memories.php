<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT id, memory_date as date, title, description, voice_duration as duration FROM memories WHERE user_id = :uid ORDER BY id DESC');
$stmt->execute(['uid' => $user['id']]);
$rows = $stmt->fetchAll();

$memories = [];
foreach ($rows as $row) {
    $memories[] = [
        'id' => (string)$row['id'],
        'date' => $row['date'],
        'title' => $row['title'],
        'description' => $row['description'],
        'duration' => $row['duration'] ?: '0:24'
    ];
}

sendJson(200, [
    'success' => true,
    'subtitle' => 'The people and moments that make you, you.',
    'memories' => $memories
]);

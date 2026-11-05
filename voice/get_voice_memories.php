<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT id, title, author, record_date as date, duration FROM voice_memories WHERE user_id = :uid ORDER BY id ASC');
$stmt->execute(['uid' => $user['id']]);
$records = $stmt->fetchAll();

$formatted = [];
foreach ($records as $r) {
    $formatted[] = [
        'id' => (string)$r['id'],
        'title' => $r['title'],
        'author' => $r['author'],
        'date' => $r['date'],
        'duration' => $r['duration']
    ];
}

sendJson(200, [
    'success' => true,
    'subtitle' => 'Familiar voices, ready to enjoy again.',
    'records' => $formatted
]);

<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT id, title, date_time as dateTime, snippet FROM ai_conversations WHERE user_id = :uid ORDER BY id DESC');
$stmt->execute(['uid' => $user['id']]);
$conversations = $stmt->fetchAll();

$formatted = [];
foreach ($conversations as $c) {
    $formatted[] = [
        'id' => (string)$c['id'],
        'title' => $c['title'],
        'dateTime' => $c['dateTime'],
        'snippet' => $c['snippet']
    ];
}

sendJson(200, [
    'success' => true,
    'conversations' => $formatted
]);

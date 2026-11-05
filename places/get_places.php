<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT id, title, description, icon_emoji as iconEmoji FROM safe_places WHERE user_id = :uid ORDER BY id ASC');
$stmt->execute(['uid' => $user['id']]);
$places = $stmt->fetchAll();

$formatted = [];
foreach ($places as $p) {
    $formatted[] = [
        'id' => (string)$p['id'],
        'title' => $p['title'],
        'description' => $p['description'],
        'iconEmoji' => $p['iconEmoji'] ?: '🏡'
    ];
}

sendJson(200, [
    'success' => true,
    'subtitle' => 'Places you know, with a little help to get there.',
    'places' => $formatted
]);

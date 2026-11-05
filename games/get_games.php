<?php
require_once __DIR__ . '/../config/config.php';

$difficulty = strtoupper(trim($_GET['difficulty'] ?? 'EASY'));
if (!in_array($difficulty, ['EASY', 'MEDIUM'])) {
    $difficulty = 'EASY';
}

$stmt = $pdo->prepare('SELECT id, title, description, thumbnail_emoji as thumbnailEmoji FROM games WHERE is_active = 1 AND difficulty = :diff ORDER BY id ASC');
$stmt->execute(['diff' => $difficulty]);
$games = $stmt->fetchAll();

if (empty($games)) {
    // Return all games if difficulty has no items
    $stmt = $pdo->query('SELECT id, title, description, thumbnail_emoji as thumbnailEmoji FROM games WHERE is_active = 1 ORDER BY id ASC');
    $games = $stmt->fetchAll();
}

$formatted = [];
foreach ($games as $g) {
    $formatted[] = [
        'id' => (string)$g['id'],
        'title' => $g['title'],
        'description' => $g['description'],
        'thumbnailEmoji' => $g['thumbnailEmoji']
    ];
}

sendJson(200, [
    'success' => true,
    'difficulty' => $difficulty,
    'games' => $formatted
]);

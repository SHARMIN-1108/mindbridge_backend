<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT id, name, relationship, notes FROM important_people WHERE user_id = :uid ORDER BY id ASC');
$stmt->execute(['uid' => $user['id']]);
$people = $stmt->fetchAll();

$formatted = [];
foreach ($people as $p) {
    $formatted[] = [
        'id' => (string)$p['id'],
        'name' => $p['name'],
        'relationship' => $p['relationship'],
        'notes' => $p['notes']
    ];
}

sendJson(200, [
    'success' => true,
    'people' => $formatted
]);

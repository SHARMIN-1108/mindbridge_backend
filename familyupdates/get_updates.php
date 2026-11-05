<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT id, update_type, title, sender_and_date, details_or_message, icon FROM family_updates WHERE user_id = :uid ORDER BY id ASC');
$stmt->execute(['uid' => $user['id']]);
$updates = $stmt->fetchAll();

$items = [];
foreach ($updates as $u) {
    $items[] = [
        'id' => (string)$u['id'],
        'type' => $u['update_type'],
        'title' => $u['title'],
        'senderAndDate' => $u['sender_and_date'],
        'details' => $u['details_or_message'],
        'icon' => $u['icon'] ?: '📷'
    ];
}

sendJson(200, [
    'success' => true,
    'subtitle' => 'A little news from the people you love.',
    'feedHeader' => 'Approved updates · Live feed',
    'feedItems' => $items
]);

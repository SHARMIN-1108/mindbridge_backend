<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);
$category = strtoupper(trim($_GET['category'] ?? 'MY_FAMILY'));

$allowed = ['MY_FAMILY', 'SPECIAL_DAYS', 'PLACES_I_LOVE'];
if (!in_array($category, $allowed)) {
    $category = 'MY_FAMILY';
}

$stmt = $pdo->prepare('SELECT id, title, date_taken as date, emoji_thumb as emojiThumb, is_selected as isSelected, person_name as personName FROM album_photos WHERE user_id = :uid AND category = :cat ORDER BY id ASC');
$stmt->execute(['uid' => $user['id'], 'cat' => $category]);
$photos = $stmt->fetchAll();

$formatted = [];
$selectedPerson = 'Lily';
foreach ($photos as $p) {
    $isSel = (bool)$p['isSelected'];
    if ($isSel && !empty($p['personName'])) {
        $selectedPerson = $p['personName'];
    }
    $formatted[] = [
        'id' => (string)$p['id'],
        'title' => $p['title'],
        'date' => $p['date'],
        'emojiThumb' => $p['emojiThumb'] ?: '👥',
        'isSelected' => $isSel
    ];
}

sendJson(200, [
    'success' => true,
    'subtitle' => 'Familiar faces. Favorite moments.',
    'selectedCategory' => $category,
    'selectedPersonName' => $selectedPerson,
    'photos' => $formatted
]);

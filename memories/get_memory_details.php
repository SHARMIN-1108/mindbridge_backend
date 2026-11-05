<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);
$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM memories WHERE id = :id AND user_id = :uid LIMIT 1');
    $stmt->execute(['id' => $id, 'uid' => $user['id']]);
    $memory = $stmt->fetch();
} else {
    // Return first memory as default detail
    $stmt = $pdo->prepare('SELECT * FROM memories WHERE user_id = :uid ORDER BY id ASC LIMIT 1');
    $stmt->execute(['uid' => $user['id']]);
    $memory = $stmt->fetch();
}

if (!$memory) {
    sendJson(404, ['success' => false, 'message' => 'Memory not found']);
}

sendJson(200, [
    'success' => true,
    'memory' => [
        'id' => (string)$memory['id'],
        'title' => $memory['title'],
        'date' => $memory['memory_date'],
        'subtitle' => $memory['subtitle'] ?: $memory['description'],
        'description' => $memory['description'],
        'associatedPeople' => 'With ' . ($memory['associated_people'] ?: $user['name']),
        'voiceNoteTitle' => $memory['voice_note_title'] ?: ($memory['title'] . ' voice note'),
        'voiceNoteSubtitle' => $memory['voice_note_subtitle'] ?: '0:24 • Playback example'
    ]
]);

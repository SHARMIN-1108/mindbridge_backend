<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$moods = [
    ['id' => '1', 'title' => 'Happy', 'icon' => '😊'],
    ['id' => '2', 'title' => 'Calm', 'icon' => '😌'],
    ['id' => '3', 'title' => 'Tired', 'icon' => '😴'],
    ['id' => '4', 'title' => 'Worried', 'icon' => '😟'],
    ['id' => '5', 'title' => 'Not Sure', 'icon' => '❓']
];

$stmt = $pdo->prepare('SELECT mood_id, note FROM daily_checkins WHERE user_id = :uid AND checkin_date = CURDATE() ORDER BY id DESC LIMIT 1');
$stmt->execute(['uid' => $user['id']]);
$today = $stmt->fetch();

$selectedMoodId = $today ? $today['mood_id'] : '2';
$noteText = $today ? $today['note'] : 'I enjoyed my tea in the garden.';

sendJson(200, [
    'success' => true,
    'moods' => $moods,
    'selectedMoodId' => (string)$selectedMoodId,
    'noteText' => $noteText
]);

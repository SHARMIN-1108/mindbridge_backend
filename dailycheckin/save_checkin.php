<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$moodId = $input['moodId'] ?? '2';
$note = trim($input['note'] ?? '');

$moodNames = [
    '1' => ['Happy', '😊'],
    '2' => ['Calm', '😌'],
    '3' => ['Tired', '😴'],
    '4' => ['Worried', '😟'],
    '5' => ['Not Sure', '❓']
];

$moodName = $moodNames[$moodId][0] ?? 'Calm';
$moodIcon = $moodNames[$moodId][1] ?? '😌';

$stmt = $pdo->prepare('INSERT INTO daily_checkins (user_id, mood_id, mood_name, mood_icon, note, checkin_date) VALUES (:uid, :mid, :mname, :micon, :note, CURDATE()) ON DUPLICATE KEY UPDATE mood_id=VALUES(mood_id), mood_name=VALUES(mood_name), mood_icon=VALUES(mood_icon), note=VALUES(note)');
$stmt->execute([
    'uid' => $user['id'],
    'mid' => $moodId,
    'mname' => $moodName,
    'micon' => $moodIcon,
    'note' => $note
]);

sendJson(200, [
    'success' => true,
    'message' => 'Daily check-in saved'
]);

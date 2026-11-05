<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$gameId = $input['gameId'] ?? 1;
$score = (int)($input['score'] ?? 100);
$duration = (int)($input['duration'] ?? 60);

$stmt = $pdo->prepare('INSERT INTO game_sessions (user_id, game_id, score, duration_seconds) VALUES (:uid, :gid, :score, :duration)');
$stmt->execute([
    'uid' => $user['id'],
    'gid' => $gameId,
    'score' => $score,
    'duration' => $duration
]);

sendJson(201, [
    'success' => true,
    'message' => 'Game score recorded successfully'
]);

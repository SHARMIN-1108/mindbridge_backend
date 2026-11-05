<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$message = trim($input['message'] ?? '');
if (empty($message)) {
    sendJson(400, ['success' => false, 'message' => 'Message is required']);
}

// Gentle, dementia-friendly AI companion response generator
$reply = "That's lovely to hear. I'm always here with you. Would you like to tell me more about that, or shall we listen to a favorite song?";

$lower = strtolower($message);
if (strpos($lower, 'memory') !== false || strpos($lower, 'picnic') !== false || strpos($lower, 'sea') !== false) {
    $reply = "I remember you enjoying that sunny picnic in the garden with Emma and Lily. Familiar moments bring so much warmth.";
} elseif (strpos($lower, 'walk') !== false || strpos($lower, 'garden') !== false) {
    $reply = "The garden is so peaceful today. Take a gentle breath of fresh air—there's no rush.";
} elseif (strpos($lower, 'call') !== false || strpos($lower, 'emma') !== false) {
    $reply = "Emma always loves talking with you. Your call is scheduled for this afternoon at 4:00 PM.";
} elseif (strpos($lower, 'tired') !== false || strpos($lower, 'worried') !== false) {
    $reply = "It is completely okay to feel that way. You are safe, and everything is being looked after. Rest whenever you like.";
}

// Save conversation snippet into ai_conversations
$title = mb_substr($message, 0, 40);
$timeStr = date('d F · g:i A');
$snippet = "You: " . mb_substr($message, 0, 60) . " | Companion: " . mb_substr($reply, 0, 60);

$stmt = $pdo->prepare('INSERT INTO ai_conversations (user_id, title, date_time, snippet) VALUES (:uid, :title, :timeStr, :snippet)');
$stmt->execute([
    'uid' => $user['id'],
    'title' => $title,
    'timeStr' => $timeStr,
    'snippet' => $snippet
]);

sendJson(200, [
    'success' => true,
    'reply' => $reply,
    'userName' => $user['preferred_name'] ?: $user['name'],
    'greeting' => "Hello, " . ($user['preferred_name'] ?: $user['name']) . ". How are you feeling today?",
    'subtitle' => "I'm here to listen, at your pace."
]);

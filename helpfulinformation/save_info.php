<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, ['success' => false, 'message' => 'POST method required']);
}

$user = authenticateUser($pdo);
$input = getJsonInput();

$pleaseCallMe = trim($input['pleaseCallMe'] ?? $user['name']);
$caregiverContact = trim($input['caregiverContact'] ?? '');
$phoneNumber = trim($input['phoneNumber'] ?? '');
$communicationStyle = trim($input['communicationStyle'] ?? '');
$importantNotes = trim($input['importantNotes'] ?? '');

$stmt = $pdo->prepare(
    'INSERT INTO helpful_information (user_id, please_call_me, caregiver_contact, phone_number, communication_style, important_notes)
     VALUES (:uid, :pcm, :cc, :pn, :cs, :in)
     ON DUPLICATE KEY UPDATE please_call_me=VALUES(please_call_me), caregiver_contact=VALUES(caregiver_contact), phone_number=VALUES(phone_number), communication_style=VALUES(communication_style), important_notes=VALUES(important_notes)'
);

$stmt->execute([
    'uid' => $user['id'],
    'pcm' => $pleaseCallMe,
    'cc' => $caregiverContact,
    'pn' => $phoneNumber,
    'cs' => $communicationStyle,
    'in' => $importantNotes
]);

sendJson(200, [
    'success' => true,
    'message' => 'Helpful information saved successfully'
]);

<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT please_call_me, caregiver_contact, phone_number, communication_style, important_notes, notice_text FROM helpful_information WHERE user_id = :uid LIMIT 1');
$stmt->execute(['uid' => $user['id']]);
$info = $stmt->fetch();

if (!$info) {
    $info = [
        'please_call_me' => $user['preferred_name'] ?: 'Margaret',
        'caregiver_contact' => 'Emma Wilson · Daughter',
        'phone_number' => '+1 (202) 555-0148',
        'communication_style' => 'Speak clearly and give me time to reply.',
        'important_notes' => 'I enjoy a quiet space and familiar names.',
        'notice_text' => 'Sharing is controlled by you or an authorized caregiver. Review the information before choosing to share it.'
    ];
}

sendJson(200, [
    'success' => true,
    'info' => [
        'pleaseCallMe' => $info['please_call_me'],
        'caregiverContact' => $info['caregiver_contact'],
        'phoneNumber' => $info['phone_number'],
        'communicationStyle' => $info['communication_style'],
        'importantNotes' => $info['important_notes'],
        'noticeText' => $info['notice_text'] ?: 'Sharing is controlled by you or an authorized caregiver.'
    ]
]);

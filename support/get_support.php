<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT name, phone_number as phone, notes as note FROM emergency_contacts WHERE user_id = :uid AND is_primary = 1 LIMIT 1');
$stmt->execute(['uid' => $user['id']]);
$contact = $stmt->fetch();

if (!$contact) {
    $contact = [
        'name' => 'Emma Wilson - Daughter',
        'phone' => '+1 (202) 555-0148',
        'note' => 'Available day and night'
    ];
}

sendJson(200, [
    'success' => true,
    'headerTitle' => 'A helping hand, when you need one.',
    'subtitle' => 'Choose the kind of help you need. You don\'t have to manage everything alone.',
    'emergencyContact' => [
        'name' => $contact['name'],
        'phone' => $contact['phone'],
        'note' => $contact['note']
    ],
    'noticeTitle' => 'Before real-world use',
    'noticeText' => 'Emergency calling must be configured and tested before real-world use. These are static example controls.'
]);

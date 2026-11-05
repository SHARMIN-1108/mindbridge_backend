<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT member_name as memberName, member_role as memberRole, footer_text as footerText, recent_updates as recentUpdates FROM caregiver_overview WHERE user_id = :uid LIMIT 1');
$stmt->execute(['uid' => $user['id']]);
$overview = $stmt->fetch();

if (!$overview) {
    $overview = [
        'memberName' => $user['name'] ?: 'Margaret Wilson',
        'memberRole' => 'Family member · Change',
        'footerText' => 'Example summary of app activity only. Review Margaret\'s sharing choices together.',
        'recentUpdates' => "Completed: Breakfast, Morning walk\nUpcoming: 4:00 PM Call with Emma\nNew: Family Picnic voice note added by Emma · 12 October"
    ];
}

sendJson(200, [
    'success' => true,
    'overview' => [
        'memberName' => $overview['memberName'],
        'memberRole' => $overview['memberRole'],
        'footerText' => $overview['footerText'],
        'recentUpdates' => $overview['recentUpdates']
    ]
]);

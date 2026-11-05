<?php
require_once __DIR__ . '/../config/config.php';

$user = authenticateUser($pdo);

$stmt = $pdo->prepare('SELECT id, metric_title as title, value_text as valueText, subtitle, progress, icon_emoji as iconEmoji, date_range as dateRange, banner_message as bannerMessage, footer_text as footerText FROM activity_progress WHERE user_id = :uid ORDER BY id ASC');
$stmt->execute(['uid' => $user['id']]);
$metrics = $stmt->fetchAll();

$dateRange = "5-11 October 2026";
$banner = "You spent time on activities you enjoy this week.";
$footer = "Every moment counts. Enjoy activities at your own pace.";

if (!empty($metrics)) {
    $dateRange = $metrics[0]['dateRange'];
    $banner = $metrics[0]['bannerMessage'];
    $footer = $metrics[0]['footerText'];
}

$formatted = [];
foreach ($metrics as $m) {
    $formatted[] = [
        'id' => (string)$m['id'],
        'title' => $m['title'],
        'valueText' => $m['valueText'],
        'subtitle' => $m['subtitle'],
        'progress' => (float)$m['progress'],
        'iconEmoji' => $m['iconEmoji']
    ];
}

sendJson(200, [
    'success' => true,
    'dateRange' => $dateRange,
    'bannerMessage' => $banner,
    'footerText' => $footer,
    'metrics' => $formatted
]);

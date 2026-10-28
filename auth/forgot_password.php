
<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, [
        'success' => false,
        'message' => 'POST method required.'
    ]);
    exit;
}

// Accept JSON or form-data.
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $input = is_array($input) ? $input : [];
} else {
    $input = $_POST;
}

$email = strtolower(trim($input['email'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJson(400, [
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT id FROM users WHERE email = :email LIMIT 1'
    );

    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Do not reveal whether an account exists.
    $response = [
        'success' => true,
        'message' => 'If an account exists for that email, a reset link will be sent.'
    ];

    if ($user) {
        // Generate a secure random token.
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        // Use MySQL's clock to avoid PHP/MySQL timezone mismatches.
        $stmt = $pdo->prepare(
            'UPDATE users
             SET reset_token_hash = :token_hash,
                 reset_token_expires =
                     DATE_ADD(NOW(), INTERVAL 15 MINUTE)
             WHERE id = :id'
        );

        $stmt->execute([
            'token_hash' => $tokenHash,
            'id' => $user['id']
        ]);

        // LOCAL TESTING ONLY. Remove before production.
        // In production, send a reset link through email.
        $response['debug_reset_token'] = $token;
    }

    sendJson(200, $response);

} catch (Throwable $e) {
    error_log('Forgot password error: ' . $e->getMessage());

    sendJson(500, [
        'success' => false,
        'message' => 'Unable to process the request right now.'
    ]);
}

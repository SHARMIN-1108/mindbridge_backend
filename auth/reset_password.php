
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

$token = trim($input['token'] ?? '');
$newPassword = $input['new_password'] ?? '';

if ($token === '' || !is_string($newPassword) || $newPassword === '') {
    sendJson(400, [
        'success' => false,
        'message' => 'Token and new_password are required.'
    ]);
    exit;
}

if (strlen($newPassword) < 8) {
    sendJson(400, [
        'success' => false,
        'message' => 'Password must be at least 8 characters.'
    ]);
    exit;
}

$tokenHash = hash('sha256', $token);

try {
    $pdo->beginTransaction();

    // Find a matching, unexpired token.
    $stmt = $pdo->prepare(
        'SELECT id
         FROM users
         WHERE reset_token_hash = :token_hash
           AND reset_token_expires > NOW()
         LIMIT 1
         FOR UPDATE'
    );

    $stmt->execute([
        'token_hash' => $tokenHash
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $pdo->rollBack();

        sendJson(400, [
            'success' => false,
            'message' => 'Invalid or expired reset token. Request a new token.'
        ]);
        exit;
    }

    // Store the password securely as a hash.
    $passwordHash = password_hash(
        $newPassword,
        PASSWORD_DEFAULT
    );

    // Consume the reset token and invalidate the old login token.
    $stmt = $pdo->prepare(
        'UPDATE users
         SET password = :password,
             reset_token_hash = NULL,
             reset_token_expires = NULL,
             api_token_hash = NULL
         WHERE id = :id'
    );

    $stmt->execute([
        'password' => $passwordHash,
        'id' => $user['id']
    ]);

    $pdo->commit();

    sendJson(200, [
        'success' => true,
        'message' => 'Password reset successfully. Please log in again.'
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Reset password error: ' . $e->getMessage());

    sendJson(500, [
        'success' => false,
        'message' => 'Unable to reset password right now.'
    ]);
}

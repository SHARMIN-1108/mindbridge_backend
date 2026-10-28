
<?php

require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    sendJson(405, [
        'success' => false,
        'message' => 'Use POST'
    ]);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    sendJson(400, [
        'success' => false,
        'message' => 'Invalid JSON request'
    ]);
}

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (
    $email === '' ||
    !is_string($password) ||
    $password === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    sendJson(400, [
        'success' => false,
        'message' => 'Valid email and password are required'
    ]);
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, name, email, password
         FROM users
         WHERE email = :email
         LIMIT 1'
    );

    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        sendJson(401, [
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $update = $pdo->prepare(
        'UPDATE users
         SET api_token_hash = :token
         WHERE id = :id'
    );

    $update->execute([
        ':token' => $tokenHash,
        ':id' => $user['id']
    ]);

    sendJson(200, [
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ]);

} catch (PDOException $e) {
    error_log('MindBridge login error: ' . $e->getMessage());

    sendJson(500, [
        'success' => false,
        'message' => 'Unable to log in'
    ]);
}

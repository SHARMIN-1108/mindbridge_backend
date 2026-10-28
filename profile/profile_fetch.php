
<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(405, ['success' => false, 'message' => 'GET method required']);
}

try {
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    $auth = $headers['Authorization']
        ?? $headers['authorization']
        ?? $_SERVER['HTTP_AUTHORIZATION']
        ?? '';

    if (!preg_match('/^Bearer\s+(.+)$/i', trim($auth), $matches)) {
        respond(401, ['success' => false, 'message' => 'Bearer token required']);
    }

    $tokenHash = hash('sha256', trim($matches[1]));

    $stmt = $pdo->prepare(
        'SELECT id, name, email, profile_picture
         FROM users
         WHERE api_token_hash = :token_hash
         LIMIT 1'
    );

    $stmt->execute(['token_hash' => $tokenHash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        respond(401, ['success' => false, 'message' => 'Invalid or expired token']);
    }

    respond(200, [
        'success' => true,
        'message' => 'Profile fetched successfully',
        'profile' => [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'profile_picture' => $user['profile_picture']
        ]
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    respond(500, ['success' => false, 'message' => 'Server error']);
}

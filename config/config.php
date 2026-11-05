<?php

// Allow CORS for local development and mobile network access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$dbname = 'mindbridge';
$username = 'root';
$password = ''; // Change if your MySQL has a password

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

function sendJson($status, $data) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getJsonInput() {
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return $_POST;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $_POST;
}

function authenticateUser(PDO $pdo, $allowFallback = true) {
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    $auth = $headers['Authorization']
        ?? $headers['authorization']
        ?? $_SERVER['HTTP_AUTHORIZATION']
        ?? ($_GET['token'] ?? '');

    if (preg_match('/^Bearer\s+(.+)$/i', trim($auth), $matches)) {
        $token = trim($matches[1]);
        $tokenHash = hash('sha256', $token);

        $stmt = $pdo->prepare(
            'SELECT id, name, preferred_name, email, role, phone_number, profile_picture
             FROM users WHERE api_token_hash = :token_hash LIMIT 1'
        );
        $stmt->execute(['token_hash' => $tokenHash]);
        $user = $stmt->fetch();
        if ($user) {
            return $user;
        }
    }

    if ($allowFallback) {
        // Fallback to default user 1 for seamless mobile / local testing
        $stmt = $pdo->query('SELECT id, name, preferred_name, email, role, phone_number, profile_picture FROM users ORDER BY id ASC LIMIT 1');
        $defaultUser = $stmt->fetch();
        if ($defaultUser) {
            return $defaultUser;
        }
    }

    sendJson(401, [
        'success' => false,
        'message' => 'Unauthorized. Please provide a valid Bearer token.'
    ]);
}


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

$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if ($name === '' || $email === '' || !is_string($password)) {
    sendJson(400, [
        'success' => false,
        'message' => 'Name, email and password are required'
    ]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJson(400, [
        'success' => false,
        'message' => 'Invalid email address'
    ]);
}

if (strlen($name) > 100 || strlen($email) > 255 ||
    strlen($password) < 8 || strlen($password) > 72) {
    sendJson(400, [
        'success' => false,
        'message' => 'Check name, email, and password length (8-72 bytes)'
    ]);
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password)
         VALUES (:name, :email, :password)'
    );

    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT)
    ]);

    sendJson(201, [
        'success' => true,
        'message' => 'Account created successfully'
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        sendJson(409, [
            'success' => false,
            'message' => 'Email is already registered'
        ]);
    }

    error_log($e->getMessage());

    sendJson(500, [
        'success' => false,
        'message' => 'Unable to create account'
    ]);
}


<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function getLoggedInUser(PDO $pdo) {
    $headers = function_exists('getallheaders')
        ? getallheaders()
        : [];

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
         FROM users WHERE api_token_hash = :token_hash LIMIT 1'
    );
    $stmt->execute(['token_hash' => $tokenHash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        respond(401, ['success' => false, 'message' => 'Invalid or expired token']);
    }

    return $user;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['success' => false, 'message' => 'POST method required']);
}

try {
    $user = getLoggedInUser($pdo);

    if (
        !isset($_FILES['profile_image']) ||
        $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK
    ) {
        respond(400, [
            'success' => false,
            'message' => 'Please select a profile image'
        ]);
    }

    $file = $_FILES['profile_image'];

    if ($file['size'] > 5 * 1024 * 1024) {
        respond(400, ['success' => false, 'message' => 'Image must be 5 MB or smaller']);
    }

    $imageInfo = @getimagesize($file['tmp_name']);

    if ($imageInfo === false) {
        respond(400, ['success' => false, 'message' => 'Invalid image file']);
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $mime = $imageInfo['mime'] ?? '';

    if (!isset($allowedTypes[$mime])) {
        respond(400, [
            'success' => false,
            'message' => 'Only JPG, PNG, and WEBP images are allowed'
        ]);
    }

    $uploadDir = __DIR__ . '/../uploads/profiles/';

    if (!is_dir($uploadDir) &&
        !mkdir($uploadDir, 0755, true) &&
        !is_dir($uploadDir)) {
        respond(500, ['success' => false, 'message' => 'Could not create upload folder']);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mime];
    $destination = $uploadDir . $filename;
    $relativePath = 'uploads/profiles/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        respond(500, ['success' => false, 'message' => 'Image upload failed']);
    }

    try {
        $stmt = $pdo->prepare(
            'UPDATE users SET profile_picture = :picture WHERE id = :id'
        );
        $stmt->execute([
            'picture' => $relativePath,
            'id' => $user['id']
        ]);
    } catch (Throwable $e) {
        if (is_file($destination)) {
            unlink($destination);
        }
        throw $e;
    }

    // Remove the previous image only if it was one of our generated filenames.
    $oldPath = $user['profile_picture'] ?? '';

    if (preg_match('~^uploads/profiles/[a-f0-9]{32}\.(jpg|png|webp)$~', $oldPath)) {
        $oldFile = dirname(__DIR__) . '/' . $oldPath;
        if (is_file($oldFile)) {
            unlink($oldFile);
        }
    }

    respond(200, [
        'success' => true,
        'message' => 'Profile picture added successfully',
        'profile' => [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'profile_picture' => $relativePath
        ]
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    respond(500, ['success' => false, 'message' => 'Server error']);
}

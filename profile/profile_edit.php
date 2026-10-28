
<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['success' => false, 'message' => 'POST method required']);
}

function authenticateUser(PDO $pdo) {
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
         FROM users WHERE api_token_hash = :token_hash LIMIT 1'
    );
    $stmt->execute(['token_hash' => $tokenHash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        respond(401, ['success' => false, 'message' => 'Invalid or expired token']);
    }

    return $user;
}

try {
    $user = authenticateUser($pdo);

    $name = $user['name'];

    if (isset($_POST['name'])) {
        $name = trim($_POST['name']);

        if ($name === '' || strlen($name) > 100) {
            respond(400, [
                'success' => false,
                'message' => 'Name must contain 1 to 100 characters'
            ]);
        }
    }

    $newRelativePath = $user['profile_picture'];
    $newDestination = null;

    // A picture is optional when editing the profile.
    if (
        isset($_FILES['profile_image']) &&
        $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['profile_image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            respond(400, ['success' => false, 'message' => 'Image upload failed']);
        }

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
        $newDestination = $uploadDir . $filename;
        $newRelativePath = 'uploads/profiles/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $newDestination)) {
            respond(500, ['success' => false, 'message' => 'Image upload failed']);
        }
    }

    try {
        $stmt = $pdo->prepare(
            'UPDATE users
             SET name = :name, profile_picture = :picture
             WHERE id = :id'
        );

        $stmt->execute([
            'name' => $name,
            'picture' => $newRelativePath,
            'id' => $user['id']
        ]);
    } catch (Throwable $e) {
        if ($newDestination !== null && is_file($newDestination)) {
            unlink($newDestination);
        }
        throw $e;
    }

    // Delete the old file only after the database update succeeds.
    $oldPath = $user['profile_picture'] ?? '';

    if (
        $newDestination !== null &&
        preg_match('~^uploads/profiles/[a-f0-9]{32}\.(jpg|png|webp)$~', $oldPath)
    ) {
        $oldFile = dirname(__DIR__) . '/' . $oldPath;

        if (is_file($oldFile)) {
            unlink($oldFile);
        }
    }

    respond(200, [
        'success' => true,
        'message' => 'Profile updated successfully',
        'profile' => [
            'id' => (int)$user['id'],
            'name' => $name,
            'email' => $user['email'],
            'profile_picture' => $newRelativePath
        ]
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    respond(500, ['success' => false, 'message' => 'Server error']);
}

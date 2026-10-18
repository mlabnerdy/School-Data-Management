<?php
require_once __DIR__ . '/config.php';

function bulletin_is_admin(): bool
{
    return strcasecmp(trim((string)($_SESSION['role'] ?? '')), 'Administrator') === 0;
}

function bulletin_require_admin(): void
{
    require_login();

    if (!bulletin_is_admin()) {
        http_response_code(403);
        exit('Access denied. Only the Administrator can manage bulletin announcements.');
    }
}

function bulletin_csrf_token(): string
{
    if (empty($_SESSION['bulletin_csrf'])) {
        $_SESSION['bulletin_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['bulletin_csrf'];
}

function bulletin_verify_csrf(): void
{
    $token = (string)($_POST['csrf_token'] ?? '');
    $saved = (string)($_SESSION['bulletin_csrf'] ?? '');

    if ($saved === '' || $token === '' || !hash_equals($saved, $token)) {
        http_response_code(419);
        exit('Your form session expired. Go back, refresh the page, and try again.');
    }
}

function bulletin_flash(string $type, string $message): void
{
    $_SESSION['bulletin_flash'] = ['type' => $type, 'message' => $message];
}

function bulletin_get_flash(): ?array
{
    if (empty($_SESSION['bulletin_flash'])) {
        return null;
    }
    $flash = $_SESSION['bulletin_flash'];
    unset($_SESSION['bulletin_flash']);
    return $flash;
}

/**
 * Validate and save a real image. Returns relative path or null when no file
 * was selected. Throws RuntimeException on an invalid upload.
 */
function bulletin_upload_image(string $field = 'image'): ?string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The image upload did not complete successfully.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('The uploaded file could not be verified.');
    }

    if ((int)$file['size'] < 1 || (int)$file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Images must be 5 MB or smaller.');
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        throw new RuntimeException('Upload a valid JPG, PNG, GIF, or WebP image.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, GIF, and WebP images are allowed.');
    }

    $uploadDir = __DIR__ . '/uploads/bulletins';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('The bulletin upload directory could not be created.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('The image could not be saved. Check folder permissions.');
    }

    return 'uploads/bulletins/' . $filename;
}

function bulletin_delete_local_image(?string $path): void
{
    if (!$path || !preg_match('~^uploads/bulletins/[a-f0-9]{32}\.(jpg|png|gif|webp)$~', $path)) {
        return;
    }

    $fullPath = __DIR__ . '/' . $path;
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'school_data_management');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Please check config.php and make sure MySQL is running.');
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function upload_file($field, $directory, $allowed, $maxBytes = 5242880) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    if ($_FILES[$field]['size'] > $maxBytes) return null;

    $original = $_FILES[$field]['name'];
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) return null;

    $root = __DIR__ . '/uploads/' . trim($directory, '/');
    if (!is_dir($root)) mkdir($root, 0775, true);

    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    $target = $root . '/' . $name;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) return null;

    return 'uploads/' . trim($directory, '/') . '/' . $name;
}

function delete_upload($path) {
    if (!$path) return;
    $full = __DIR__ . '/' . ltrim($path, '/');
    if (is_file($full)) @unlink($full);
}
?>

<?php

// Start the session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
*/

define('DB_HOST', 'localhost');
define('DB_NAME', 'school_data_management');
define('DB_USER', 'root');
define('DB_PASS', '');


/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/

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

    die(
        'Database connection failed. Please check config.php and make sure MySQL is running.'
    );
}


/*
|--------------------------------------------------------------------------
| Session Settings
|--------------------------------------------------------------------------
*/

// 10 minutes of inactivity
define('SESSION_IDLE_TIMEOUT', 600);

// 5 minutes after the website is closed
define('SESSION_CLOSED_TIMEOUT', 300);


/*
|--------------------------------------------------------------------------
| Require Login
|--------------------------------------------------------------------------
*/

function require_login()
{
    // Check if the user is logged in
    if (empty($_SESSION['user_id'])) {

        header('Location: login.php');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Check Closed Website Timeout
    |--------------------------------------------------------------------------
    */

    if (
        isset($_SESSION['closed_at']) &&
        (time() - (int) $_SESSION['closed_at']) >= SESSION_CLOSED_TIMEOUT
    ) {

        destroy_user_session();

        header('Location: login.php?timeout=1');
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Check Inactivity Timeout
    |--------------------------------------------------------------------------
    */

    if (isset($_SESSION['last_activity'])) {

        $inactiveTime = time() - (int) $_SESSION['last_activity'];

        if ($inactiveTime >= SESSION_IDLE_TIMEOUT) {

            destroy_user_session();

            header('Location: login.php?timeout=1');
            exit;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | User Is Active
    |--------------------------------------------------------------------------
    */

    $_SESSION['last_activity'] = time();

    // Remove the closed timestamp when the user returns
    unset($_SESSION['closed_at']);
}


/*
|--------------------------------------------------------------------------
| Destroy User Session
|--------------------------------------------------------------------------
*/

function destroy_user_session()
{
    // Clear all session data
    $_SESSION = [];


    // Remove the session cookie
    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }


    // Destroy the session
    session_destroy();
}


/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}


/*
|--------------------------------------------------------------------------
| Upload File
|--------------------------------------------------------------------------
*/

function upload_file(
    $field,
    $directory,
    $allowed,
    $maxBytes = 5242880
) {

    if (
        empty($_FILES[$field]) ||
        $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if ($_FILES[$field]['size'] > $maxBytes) {
        return null;
    }


    $original = $_FILES[$field]['name'];

    $ext = strtolower(
        pathinfo($original, PATHINFO_EXTENSION)
    );


    if (!in_array($ext, $allowed, true)) {
        return null;
    }


    $root = __DIR__ . '/uploads/' . trim($directory, '/');


    if (!is_dir($root)) {
        mkdir($root, 0775, true);
    }


    $name = bin2hex(random_bytes(12)) . '.' . $ext;

    $target = $root . '/' . $name;


    if (
        !move_uploaded_file(
            $_FILES[$field]['tmp_name'],
            $target
        )
    ) {
        return null;
    }


    return 'uploads/' .
        trim($directory, '/') .
        '/' .
        $name;
}


/*
|--------------------------------------------------------------------------
| Delete Upload
|--------------------------------------------------------------------------
*/

function delete_upload($path)
{
    if (!$path) {
        return;
    }


    $full = __DIR__ . '/' . ltrim($path, '/');


    if (is_file($full)) {
        @unlink($full);
    }
}

?>
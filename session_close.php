<?php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');


// Check if the user is logged in
if (empty($_SESSION['user_id'])) {

    echo json_encode([
        'success' => false
    ]);

    exit;
}


// Record when the website was closed
$_SESSION['closed_at'] = time();


echo json_encode([
    'success' => true
]);

?>
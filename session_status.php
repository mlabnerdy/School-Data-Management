<?php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');


// Check if the user is logged in
if (empty($_SESSION['user_id'])) {

    echo json_encode([
        'logged_in' => false,
        'expired' => true
    ]);

    exit;
}


// Check closed website timeout
if (
    isset($_SESSION['closed_at']) &&
    (time() - (int) $_SESSION['closed_at']) >= SESSION_CLOSED_TIMEOUT
) {

    destroy_user_session();

    echo json_encode([
        'logged_in' => false,
        'expired' => true
    ]);

    exit;
}


// Check inactivity timeout
if (isset($_SESSION['last_activity'])) {

    $inactiveTime =
        time() - (int) $_SESSION['last_activity'];


    if ($inactiveTime >= SESSION_IDLE_TIMEOUT) {

        destroy_user_session();

        echo json_encode([
            'logged_in' => false,
            'expired' => true
        ]);

        exit;
    }
}


// Session is still valid
echo json_encode([
    'logged_in' => true,
    'expired' => false,
    'remaining' =>
        SESSION_IDLE_TIMEOUT -
        (
            time() -
            (int) ($_SESSION['last_activity'] ?? time())
        )
]);

?>
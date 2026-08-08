<?php

require_once __DIR__ . '/config.php';

require_login();


// ==========================================================
// GET DOCUMENT ID
// ==========================================================

$id = (int) ($_GET['id'] ?? 0);

$return = $_GET['return'] ?? 'index.php';


// ==========================================================
// GET DOCUMENT
// ==========================================================

$stmt = $pdo->prepare("
    SELECT file_path
    FROM documents
    WHERE id = ?
");

$stmt->execute([$id]);

$document = $stmt->fetch();


// ==========================================================
// DELETE DOCUMENT
// ==========================================================

if ($document) {

    // Delete the physical file
    if (!empty($document['file_path'])) {
        delete_upload($document['file_path']);
    }

    // Delete database record
    $stmt = $pdo->prepare("
        DELETE FROM documents
        WHERE id = ?
    ");

    $stmt->execute([$id]);
}


// ==========================================================
// SAFE REDIRECT
// ==========================================================

// Prevent directory traversal
$return = str_replace(
    ['..', '\\'],
    '',
    $return
);

header('Location: ' . $return);
exit;
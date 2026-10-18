<?php
require_once __DIR__ . '/bulletin_helpers.php';
bulletin_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

bulletin_verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    bulletin_flash('error', 'Invalid announcement ID.');
    redirect('bulletin_manage.php');
}

$stmt = $pdo->prepare("SELECT image_path FROM bulletin_announcements WHERE id = :id");
$stmt->execute([':id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    bulletin_flash('error', 'Announcement not found.');
    redirect('bulletin_manage.php');
}

try {
    $stmt = $pdo->prepare("DELETE FROM bulletin_announcements WHERE id = :id");
    $stmt->execute([':id' => $id]);
    bulletin_delete_local_image($item['image_path']);
    bulletin_flash('success', 'Announcement deleted.');
} catch (Throwable $ex) {
    error_log('Bulletin delete error: ' . $ex->getMessage());
    bulletin_flash('error', 'The announcement could not be deleted.');
}
redirect('bulletin_manage.php');

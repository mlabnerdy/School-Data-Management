<?php
require_once __DIR__ . '/bulletin_helpers.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(404);
    exit('Announcement not found.');
}

$sql = "SELECT id, title, description, image_path, image_alt, category, status, publish_date
        FROM bulletin_announcements WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$announcement = $stmt->fetch();

$canViewDraft = bulletin_is_admin();
if (!$announcement ||
    ($announcement['status'] !== 'Published' && !$canViewDraft) ||
    ($announcement['status'] === 'Published' && $announcement['publish_date'] !== null
        && strtotime($announcement['publish_date']) > time() && !$canViewDraft)) {
    http_response_code(404);
    exit('Announcement not found.');
}
$pageStylesheets = ['assets/css/bulletin_board.css'];
require_once __DIR__ . '/header.php';
?>
<section class="detail-shell">
    <a class="back-link" href="bulletin_board.php">← Back to Bulletin Board</a>
    <?php if ($announcement['status'] !== 'Published'): ?><p class="draft-notice">Draft preview — only the Administrator can see this announcement.</p><?php endif; ?>
    <article class="detail-article">
        <img class="detail-image" src="<?= e($announcement['image_path']) ?>" alt="<?= e($announcement['image_alt'] ?: $announcement['title']) ?>">
        <div class="detail-copy">
            <div class="card-meta"><span><?= e($announcement['category']) ?></span><span><?= $announcement['publish_date'] ? e(date('F d, Y', strtotime($announcement['publish_date']))) : 'Announcement' ?></span></div>
            <h1><?= e($announcement['title']) ?></h1>
            <div class="detail-description"><?= nl2br(e($announcement['description'])) ?></div>
        </div>
    </article>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>

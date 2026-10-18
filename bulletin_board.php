<?php
require_once __DIR__ . '/bulletin_helpers.php';
require_login();

$pageTitle = 'Bulletin Board';
$search = trim((string)($_GET['q'] ?? ''));

$sql = "SELECT id, title, description, image_path, image_alt, category, is_featured, publish_date
        FROM bulletin_announcements
        WHERE status = 'Published'
          AND (publish_date IS NULL OR publish_date <= NOW())";
$params = [];

if ($search !== '') {
    $sql .= " AND (title LIKE :title_search OR description LIKE :description_search OR category LIKE :category_search)";
    $like = '%' . $search . '%';
    $params = [':title_search' => $like, ':description_search' => $like, ':category_search' => $like];
}
$sql .= " ORDER BY is_featured DESC, COALESCE(publish_date, created_at) DESC, id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$announcements = $stmt->fetchAll();
$featured = null;
foreach ($announcements as $item) {
    if ((int)$item['is_featured'] === 1) {
        $featured = $item;
        break;
    }
}
$pageStylesheets = ['assets/css/bulletin_board.css'];
require_once __DIR__ . '/header.php';
?>
<section class="bulletin-shell">
    <header class="board-header">
        <div>
            <p class="eyebrow">School Data Management System</p>
            <h1>Bulletin Board</h1>
            <p class="muted">Announcements, notices, and important school updates.</p>
        </div>
        <form class="search-form" method="get" action="bulletin_board.php">
            <label class="sr-only" for="q">Search announcements</label>
            <input id="q" type="search" name="q" placeholder="Search announcements" value="<?= e($search) ?>">
            <button type="submit">Search</button>
        </form>
    </header>

    <?php if ($featured): ?>
    <section class="featured">
        <div class="featured-copy">
            <span class="featured-label">Featured<?= $featured['category'] ? ' · ' . e($featured['category']) : '' ?></span>
            <h2><?= e($featured['title']) ?></h2>
            <p><?= nl2br(e($featured['description'])) ?></p>
            <a class="light-link" href="bulletin_view.php?id=<?= (int)$featured['id'] ?>">Read announcement <span aria-hidden="true">→</span></a>
        </div>
        <img src="<?= e($featured['image_path']) ?>" alt="<?= e($featured['image_alt'] ?: $featured['title']) ?>">
    </section>
    <?php endif; ?>

    <section class="announcements-section">
        <div class="section-heading">
            <div>
                <h2>Latest announcements</h2>
                <p class="muted"><?= count($announcements) ?> published announcement<?= count($announcements) === 1 ? '' : 's' ?></p>
            </div>
            <?php if (bulletin_is_admin()): ?>
                <a class="button button-primary" href="bulletin_manage.php">Manage announcements</a>
            <?php endif; ?>
        </div>

        <?php if (!$announcements): ?>
            <div class="empty-state">
                <h3>No announcements found</h3>
                <p><?= $search !== '' ? 'Try another search term.' : 'Published announcements will appear here.' ?></p>
            </div>
        <?php else: ?>
        <div class="announcement-grid">
            <?php foreach ($announcements as $item): ?>
            <article class="announcement-card">
                <a class="card-image" href="bulletin_view.php?id=<?= (int)$item['id'] ?>">
                    <img src="<?= e($item['image_path']) ?>" alt="<?= e($item['image_alt'] ?: $item['title']) ?>" loading="lazy">
                    <?php if ((int)$item['is_featured'] === 1): ?><span class="featured-pill">Featured</span><?php endif; ?>
                </a>
                <div class="card-content">
                    <div class="card-meta">
                        <span><?= e($item['category']) ?></span>
                        <span><?= $item['publish_date'] ? e(date('M d, Y', strtotime($item['publish_date']))) : 'Published' ?></span>
                    </div>
                    <h3><a href="bulletin_view.php?id=<?= (int)$item['id'] ?>"><?= e($item['title']) ?></a></h3>
                    <p><?= e(mb_strimwidth((string)$item['description'], 0, 150, '…', 'UTF-8')) ?></p>
                    <a class="text-link" href="bulletin_view.php?id=<?= (int)$item['id'] ?>">View details →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>

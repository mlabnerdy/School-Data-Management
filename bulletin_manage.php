<?php
require_once __DIR__ . '/bulletin_helpers.php';
bulletin_require_admin();

$stmt = $pdo->query("SELECT id, title, category, status, is_featured, publish_date, created_at
                     FROM bulletin_announcements
                     ORDER BY created_at DESC, id DESC");
$items = $stmt->fetchAll();
$flash = bulletin_get_flash();
$pageTitle = 'Manage Bulletin Board';
$pageStylesheets = ['assets/css/bulletin_manage.css'];
require_once __DIR__ . '/header.php';
?>
<section class="admin-shell">
    <header class="admin-header">
        <div>
            <p class="eyebrow">Administrator workspace</p>
            <h1>Manage Bulletin Board</h1>
            <p class="muted">Create announcements and control what appears on the public board.</p>
        </div>
        <div class="header-actions">
            <a class="button button-outline" href="bulletin_board.php">View board</a>
            <a class="button button-primary" href="bulletin_form.php">+ New announcement</a>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="notice <?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="table-panel">
        <div class="panel-heading">
            <div><h2>Announcements</h2><p class="muted"><?= count($items) ?> total record<?= count($items) === 1 ? '' : 's' ?></p></div>
            <input id="tableSearch" type="search" placeholder="Filter by title or category" aria-label="Filter announcements">
        </div>
        <div class="table-wrap">
        <table id="announcementTable">
            <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Publication date</th><th>Featured</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td data-label="Title"><strong><?= e($item['title']) ?></strong></td>
                    <td data-label="Category"><?= e($item['category']) ?></td>
                    <td data-label="Status"><span class="status status-<?= strtolower(e($item['status'])) ?>"><?= e($item['status']) ?></span></td>
                    <td data-label="Publication date"><?= $item['publish_date'] ? e(date('M d, Y g:i A', strtotime($item['publish_date']))) : '—' ?></td>
                    <td data-label="Featured"><?= (int)$item['is_featured'] === 1 ? 'Yes' : 'No' ?></td>
                    <td data-label="Actions" class="actions">
                        <a class="small-button" href="bulletin_form.php?id=<?= (int)$item['id'] ?>">Edit</a>
                        <?php if ($item['status'] === 'Published'): ?><a class="small-button" href="bulletin_view.php?id=<?= (int)$item['id'] ?>">Preview</a><?php endif; ?>
                        <form method="post" action="bulletin_delete.php" onsubmit="return confirm('Delete this announcement permanently? This also removes its uploaded image.');">
                            <input type="hidden" name="csrf_token" value="<?= e(bulletin_csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                            <button class="small-button danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$items): ?><tr><td colspan="6" class="empty-cell">No announcements yet. Create your first announcement.</td></tr><?php endif; ?>
            </tbody>
        </table>
        </div>
    </section>
</section>
<script>
document.getElementById('tableSearch').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#announcementTable tbody tr').forEach(function (row) {
        row.hidden = !row.innerText.toLowerCase().includes(query);
    });
});
</script>
<?php require_once __DIR__ . '/footer.php'; ?>

<?php
require_once __DIR__ . '/bulletin_helpers.php';
bulletin_require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$editing = $id && $id > 0;
$announcement = [
    'title' => '', 'description' => '', 'image_path' => '', 'image_alt' => '',
    'category' => 'General', 'status' => 'Draft', 'is_featured' => 0, 'publish_date' => ''
];

if ($editing) {
    $stmt = $pdo->prepare("SELECT * FROM bulletin_announcements WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        http_response_code(404);
        exit('Announcement not found.');
    }
    $announcement = $found;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    bulletin_verify_csrf();

    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $category = trim((string)($_POST['category'] ?? 'General'));
    $imageAlt = trim((string)($_POST['image_alt'] ?? ''));
    $status = ($_POST['status'] ?? 'Draft') === 'Published' ? 'Published' : 'Draft';
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $publishInput = trim((string)($_POST['publish_date'] ?? ''));

    if ($title === '' || mb_strlen($title) > 200) $errors[] = 'Title is required and must be 200 characters or fewer.';
    if ($description === '') $errors[] = 'Description is required.';
    if ($category === '' || mb_strlen($category) > 50) $errors[] = 'Choose a category of 50 characters or fewer.';
    if (mb_strlen($imageAlt) > 255) $errors[] = 'Image description must be 255 characters or fewer.';

    $publishDate = null;
    if ($publishInput !== '') {
        $date = DateTime::createFromFormat('Y-m-d\TH:i', $publishInput);
        if (!$date || $date->format('Y-m-d\TH:i') !== $publishInput) {
            $errors[] = 'Enter a valid publication date and time.';
        } else {
            $publishDate = $date->format('Y-m-d H:i:s');
        }
    } elseif ($status === 'Published') {
        $publishDate = date('Y-m-d H:i:s');
    }

    $newImagePath = null;
    try {
        $newImagePath = bulletin_upload_image('image');
    } catch (RuntimeException $ex) {
        $errors[] = $ex->getMessage();
    }

    $currentImage = (string)($announcement['image_path'] ?? '');
    if ($currentImage === '' && !$newImagePath) {
        $errors[] = 'Please upload an announcement image.';
    }

    if (!$errors) {
        $imagePath = $newImagePath ?: $currentImage;
        try {
            if ($featured) {
                $pdo->beginTransaction();
                $pdo->exec("UPDATE bulletin_announcements SET is_featured = 0");
            }

            if ($editing) {
                $sql = "UPDATE bulletin_announcements
                        SET title = :title, description = :description, image_path = :image_path,
                            image_alt = :image_alt, category = :category, status = :status,
                            is_featured = :is_featured, publish_date = :publish_date
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':title' => $title, ':description' => $description, ':image_path' => $imagePath,
                    ':image_alt' => $imageAlt !== '' ? $imageAlt : null, ':category' => $category,
                    ':status' => $status, ':is_featured' => $featured, ':publish_date' => $publishDate,
                    ':id' => $id
                ]);
            } else {
                $sql = "INSERT INTO bulletin_announcements
                        (title, description, image_path, image_alt, category, status, is_featured, publish_date, created_by)
                        VALUES (:title, :description, :image_path, :image_alt, :category, :status, :is_featured, :publish_date, :created_by)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':title' => $title, ':description' => $description, ':image_path' => $imagePath,
                    ':image_alt' => $imageAlt !== '' ? $imageAlt : null, ':category' => $category,
                    ':status' => $status, ':is_featured' => $featured, ':publish_date' => $publishDate,
                    ':created_by' => (int)$_SESSION['user_id']
                ]);
            }

            if ($featured) $pdo->commit();

            if ($newImagePath && $currentImage && $newImagePath !== $currentImage) {
                bulletin_delete_local_image($currentImage);
            }

            bulletin_flash('success', $status === 'Published' ? 'Announcement saved and published.' : 'Announcement saved as a draft.');
            redirect('bulletin_manage.php');
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($newImagePath) bulletin_delete_local_image($newImagePath);
            error_log('Bulletin save error: ' . $ex->getMessage());
            $errors[] = 'The announcement could not be saved. Check the server error log if the problem continues.';
        }
    } elseif ($newImagePath) {
        // Keep the existing record untouched if validation fails.
        bulletin_delete_local_image($newImagePath);
    }

    $announcement = array_merge($announcement, [
        'title' => $title, 'description' => $description, 'category' => $category,
        'image_alt' => $imageAlt, 'status' => $status, 'is_featured' => $featured,
        'publish_date' => $publishDate ? date('Y-m-d\TH:i', strtotime($publishDate)) : '',
        'image_path' => $announcement['image_path'] ?? ''
    ]);
}
$flash = bulletin_get_flash();
$pageTitle = 'Bulletin Announcement Form';
$pageStylesheets = ['assets/css/bulletin_manage.css'];
require_once __DIR__ . '/header.php';
?>

    <a class="back-link" href="bulletin_manage.php">← Back to management</a>
    <header class="form-header">
        <p class="eyebrow">Administrator workspace</p>
        <h1><?= $editing ? 'Edit announcement' : 'Create announcement' ?></h1>
        <p class="muted">Published announcements appear on the Bulletin Board page.</p>
    </header>

    <?php if ($errors): ?>
        <div class="notice error"><strong>Please fix the following:</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form class="announcement-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(bulletin_csrf_token()) ?>">
        <div class="form-main">
            <label for="title">Announcement title <span>*</span></label>
            <input id="title" name="title" maxlength="200" required value="<?= e($announcement['title']) ?>">

            <label for="description">Description <span>*</span></label>
            <textarea id="description" name="description" rows="7" required><?= e($announcement['description']) ?></textarea>

            <label for="category">Category <span>*</span></label>
            <select id="category" name="category" required>
                <?php foreach (['General','Event','Academics','Community','Update','Notice','Administrative','Property'] as $category): ?>
                    <option value="<?= e($category) ?>" <?= $announcement['category'] === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="publish_date">Publication date and time</label>
            <input id="publish_date" type="datetime-local" name="publish_date" value="<?= e($announcement['publish_date'] ? date('Y-m-d\TH:i', strtotime($announcement['publish_date'])) : '') ?>">
            <small>Leave blank to publish immediately when status is Published. Future dates remain hidden until that time.</small>
        </div>

        <aside class="form-side">
            <label for="image">Announcement image <?= $editing ? '' : '<span>*</span>' ?></label>
            <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" <?= $editing ? '' : 'required' ?>>
            <small>JPG, PNG, GIF, or WebP. Maximum 5 MB.</small>
            <?php if ($announcement['image_path']): ?>
                <img class="image-preview" id="imagePreview" src="<?= e($announcement['image_path']) ?>" alt="Current announcement image">
            <?php else: ?>
                <img class="image-preview" id="imagePreview" alt="Image preview" hidden>
            <?php endif; ?>

            <label for="image_alt">Image description (accessibility)</label>
            <input id="image_alt" name="image_alt" maxlength="255" value="<?= e($announcement['image_alt']) ?>" placeholder="Describe the image briefly">

            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="Draft" <?= $announcement['status'] === 'Draft' ? 'selected' : '' ?>>Save as draft</option>
                <option value="Published" <?= $announcement['status'] === 'Published' ? 'selected' : '' ?>>Publish announcement</option>
            </select>

            <label class="checkbox-label"><input type="checkbox" name="is_featured" value="1" <?= (int)$announcement['is_featured'] === 1 ? 'checked' : '' ?>> Pin as featured announcement</label>
            <p class="help-note">Only one announcement can be featured at a time.</p>

            <div class="form-actions">
                <button class="button button-primary" type="submit"><?= $announcement['status'] === 'Published' ? 'Save announcement' : 'Save draft' ?></button>
                <a class="button button-outline" href="bulletin_manage.php">Cancel</a>
            </div>
        </aside>
    </form>
</section>
<script>
const imageInput = document.getElementById('image');
const imagePreview = document.getElementById('imagePreview');
imageInput.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) return;
    if (imagePreview.dataset.objectUrl) URL.revokeObjectURL(imagePreview.dataset.objectUrl);
    const url = URL.createObjectURL(file);
    imagePreview.src = url;
    imagePreview.dataset.objectUrl = url;
    imagePreview.hidden = false;
});
</script>
<?php require_once __DIR__ . '/footer.php'; ?>

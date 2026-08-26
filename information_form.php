<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Information Board';

// Role
$userRole = $_SESSION['role'] ?? '';

$isAdmin = strcasecmp($userRole, 'Administrator') === 0;

if (!$isAdmin) {
    header('Location: information.php');
    exit;
}

// ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$isEdit = $id > 0;

// Defaults
$title = '';
$category = '';
$description = '';
$schoolYear = '';
$datePosted = date('Y-m-d');
$status = 'Published';

$fileName = '';
$filePath = '';
$fileType = '';

$error = '';

$uploadDir = __DIR__ . '/uploads/information/';

// Categories
$categories = [
    'Administrative',
    'Property',
    'Procurement',
    'Inventory',
    'Memorandum',
    'Other'
];

// Load information
if ($isEdit) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM information
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $information = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$information) {
        header('Location: information.php');
        exit;
    }

    $title = $information['title'] ?? '';
    $category = $information['category'] ?? '';
    $description = $information['description'] ?? '';
    $schoolYear = $information['school_year'] ?? '';
    $datePosted = $information['date_posted'] ?? date('Y-m-d');
    $status = $information['status'] ?? 'Published';

    $fileName = $information['file_name'] ?? '';
    $filePath = $information['file_path'] ?? '';
    $fileType = $information['file_type'] ?? '';
}

// Save information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $schoolYear = trim($_POST['school_year'] ?? '');
    $datePosted = trim($_POST['date_posted'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $newFileName = $fileName;
    $newFilePath = $filePath;
    $newFileType = $fileType;

    // Validate
    if ($title === '') {

        $error = 'Title is required.';

    } elseif (
        $category === '' ||
        !in_array($category, $categories, true)
    ) {

        $error = 'Please select a valid category.';

    } elseif ($description === '') {

        $error = 'Description is required.';

    } elseif ($datePosted === '') {

        $error = 'Date posted is required.';

    } elseif (!in_array($status, ['Published', 'Draft'], true)) {

        $error = 'Invalid status selected.';
    }

    // Upload document
    if (
        $error === '' &&
        isset($_FILES['document']) &&
        $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['document']['error'] !== UPLOAD_ERR_OK) {

            $error = 'Document upload failed.';

        } else {

            $file = $_FILES['document'];

            $originalName = basename($file['name']);

            $extension = strtolower(
                pathinfo(
                    $originalName,
                    PATHINFO_EXTENSION
                )
            );

            $allowedExtensions = [
                'pdf',
                'doc',
                'docx',
                'xls',
                'xlsx'
            ];

            $maxSize = 10 * 1024 * 1024;

            if (!in_array($extension, $allowedExtensions, true)) {

                $error = 'Invalid document type. PDF, DOC, DOCX, XLS, and XLSX are allowed.';

            } elseif ($file['size'] > $maxSize) {

                $error = 'Document size must not exceed 10 MB.';

            } else {

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $safeName = preg_replace(
                    '/[^A-Za-z0-9._-]/',
                    '_',
                    $originalName
                );

                $newFileName =
                    uniqid('info_', true) . '_' . $safeName;

                $destination =
                    $uploadDir . $newFileName;

                if (move_uploaded_file(
                    $file['tmp_name'],
                    $destination
                )) {

                    $newFileNameForDatabase = $originalName;

                    $newFilePath =
                        'uploads/information/' . $newFileName;

                    $newFileType =
                        $file['type'] ?? $extension;

                    // Delete old file when replacing
                    if (
                        $isEdit &&
                        $filePath !== '' &&
                        file_exists(
                            __DIR__ . '/' . $filePath
                        )
                    ) {

                        unlink(
                            __DIR__ . '/' . $filePath
                        );
                    }

                    $newFileName =
                        $newFileNameForDatabase;

                } else {

                    $error = 'Unable to save the uploaded document.';
                }
            }
        }
    }

    // Require document for new entries
    if (
        $error === '' &&
        !$isEdit &&
        $newFilePath === ''
    ) {

        $error = 'Please upload a document.';
    }

    // Current user
    $currentUserId = $_SESSION['user_id'] ?? null;

    // Insert
    if ($error === '' && !$isEdit) {

        $stmt = $pdo->prepare("
            INSERT INTO information
            (
                title,
                category,
                description,
                school_year,
                date_posted,
                status,
                file_name,
                file_path,
                file_type,
                created_by,
                updated_by
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([
            $title,
            $category,
            $description,
            $schoolYear !== '' ? $schoolYear : null,
            $datePosted,
            $status,
            $newFileName,
            $newFilePath,
            $newFileType,
            $currentUserId,
            $currentUserId
        ]);

        header('Location: information.php');
        exit;
    }

    // Update
    if ($error === '' && $isEdit) {

        $stmt = $pdo->prepare("
            UPDATE information
            SET
                title = ?,
                category = ?,
                description = ?,
                school_year = ?,
                date_posted = ?,
                status = ?,
                file_name = ?,
                file_path = ?,
                file_type = ?,
                updated_by = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $title,
            $category,
            $description,
            $schoolYear !== '' ? $schoolYear : null,
            $datePosted,
            $status,
            $newFileName,
            $newFilePath,
            $newFileType,
            $currentUserId,
            $id
        ]);

        header('Location: information.php');
        exit;
    }
}

require_once __DIR__ . '/header.php';

?>

<link
    rel="stylesheet"
    href="assets/information_form.css"
>

<div class="page-container">

    <div class="page-header">

        <div class="page-header-content">

            <h1>
                <?= $isEdit ? 'Edit Information' : 'Add Information' ?>
            </h1>

            <p>
                <?= $isEdit
                    ? 'Update an information board document.'
                    : 'Upload a new information board document.'
                ?>
            </p>

        </div>

        <a
            href="information.php"
            class="back-button"
        >
            Back
        </a>

    </div>

    <div class="form-card">

        <?php if ($error !== ''): ?>

            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            action=""
            enctype="multipart/form-data"
        >

            <!-- Title -->
            <div class="form-group">

                <label for="title">
                    Title
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="255"
                    value="<?= htmlspecialchars($title) ?>"
                    required
                >

            </div>

            <!-- Category, School Year, Date -->
            <div class="form-row">

                <div class="form-group">

                    <label for="category">
                        Category
                        <span class="required">*</span>
                    </label>

                    <select
                        id="category"
                        name="category"
                        required
                    >

                        <option value="">
                            Select Category
                        </option>

                        <?php foreach ($categories as $itemCategory): ?>

                            <option
                                value="<?= htmlspecialchars($itemCategory) ?>"
                                <?= $category === $itemCategory
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                <?= htmlspecialchars($itemCategory) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label for="school_year">
                        School Year
                    </label>

                    <input
                        type="text"
                        id="school_year"
                        name="school_year"
                        maxlength="20"
                        value="<?= htmlspecialchars($schoolYear) ?>"
                        placeholder="e.g. 2026-2027"
                    >

                </div>

                <div class="form-group">

                    <label for="date_posted">
                        Date Posted
                        <span class="required">*</span>
                    </label>

                    <input
                        type="date"
                        id="date_posted"
                        name="date_posted"
                        value="<?= htmlspecialchars($datePosted) ?>"
                        required
                    >

                </div>

            </div>

            <!-- Description -->
            <div class="form-group">

                <label for="description">
                    Description
                    <span class="required">*</span>
                </label>

                <textarea
                    id="description"
                    name="description"
                    maxlength="5000"
                    required
                ><?= htmlspecialchars($description) ?></textarea>

            </div>

            <!-- Document -->
            <div class="form-group">

                <label for="document">

                    Document

                    <?php if (!$isEdit): ?>
                        <span class="required">*</span>
                    <?php endif; ?>

                </label>

                <input
                    type="file"
                    id="document"
                    name="document"
                    accept=".pdf,.doc,.docx,.xls,.xlsx"
                    <?= !$isEdit ? 'required' : '' ?>
                >

                <small>
                    PDF, DOC, DOCX, XLS, or XLSX.
                    Maximum 10 MB.
                </small>

                <?php if ($isEdit && $fileName !== ''): ?>

                    <div class="current-document">

                        <span>
                            Current document:
                        </span>

                        <strong>
                            <?= htmlspecialchars($fileName) ?>
                        </strong>

                    </div>

                <?php endif; ?>

            </div>

            <!-- Status -->
            <div class="form-group">

                <label for="status">
                    Status
                    <span class="required">*</span>
                </label>

                <select
                    id="status"
                    name="status"
                    required
                >

                    <option
                        value="Published"
                        <?= $status === 'Published'
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Published
                    </option>

                    <option
                        value="Draft"
                        <?= $status === 'Draft'
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Draft
                    </option>

                </select>

            </div>

            <!-- Buttons -->
            <div class="form-actions">

                <a
                    href="information.php"
                    class="button cancel-button"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="button save-button"
                >

                    <?= $isEdit
                        ? 'Update Information'
                        : 'Upload Information'
                    ?>

                </button>

            </div>

        </form>

    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
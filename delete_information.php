<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Delete Information';

// Only Administrator can delete
$userRole = $_SESSION['role'] ?? '';

$isAdmin = strcasecmp($userRole, 'Administrator') === 0;

if (!$isAdmin) {
    redirect('information.php');
}

// Get ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    redirect('information.php');
}

// Get information
$stmt = $pdo->prepare("
    SELECT *
    FROM information
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$information = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$information) {
    redirect('information.php');
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Delete database record
    $stmt = $pdo->prepare("
        DELETE FROM information
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    // Delete uploaded document
    $filePath = $information['file_path'] ?? '';

    if ($filePath !== '') {

        $fullPath = __DIR__ . '/' . $filePath;

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    redirect('information.php');
}

// Header
include 'header.php';

?>

<link
    rel="stylesheet"
    href="assets/information_form.css"
>

<div class="page-container">

    <div class="page-header">

        <div class="page-header-content">

            <h1>
                Delete Information
            </h1>

            <p>
                Remove an information board record.
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

        <div class="form-group">

            <label>
                Information
            </label>

            <div class="information-view-value">

                <?= e(
                    $information['title']
                    ?? 'Untitled Information'
                ) ?>

            </div>

        </div>


        <div class="form-group">

            <label>
                Category
            </label>

            <div class="information-view-value">

                <?= e(
                    $information['category']
                    ?? 'Not specified'
                ) ?>

            </div>

        </div>


        <div class="form-group">

            <label>
                Document
            </label>

            <div class="information-view-value">

                <?php if (!empty($information['file_name'])): ?>

                    <?= e($information['file_name']) ?>

                <?php else: ?>

                    <span class="text-muted">
                        No document attached.
                    </span>

                <?php endif; ?>

            </div>

        </div>


        <div class="error-message">

            <strong>
                Warning
            </strong>

            <br>

            This action cannot be undone.

            The information record and its attached
            document will be permanently deleted.

        </div>


        <form
            method="POST"
            action=""
        >

            <div class="form-actions">

                <a
                    href="information.php"
                    class="button cancel-button"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="button"
                    style="background:#dc3545;color:#fff;border:none;"
                >

                    <i class="bi bi-trash me-1"></i>

                    Delete Information

                </button>

            </div>

        </form>

    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
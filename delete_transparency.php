<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Delete Transparency';

// Only Administrator can delete
$userRole = $_SESSION['role'] ?? '';

$isAdmin = strcasecmp(
    $userRole,
    'Administrator'
) === 0;

if (!$isAdmin) {

    redirect('transparency.php');

}


// Get ID
$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {

    redirect('transparency.php');

}


// Get transparency record
$stmt = $pdo->prepare("
    SELECT *
    FROM transparency
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$transparency = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transparency) {

    redirect('transparency.php');

}


// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // Delete uploaded document
        if (!empty($transparency['file_path'])) {

            delete_upload(
                $transparency['file_path']
            );

        }


        // Delete database record
        $stmt = $pdo->prepare("
            DELETE FROM transparency
            WHERE id = ?
        ");

        $stmt->execute([$id]);


        redirect('transparency.php');

    }

    catch (PDOException $e) {

        redirect('transparency.php');

    }

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
                Delete Transparency
            </h1>

            <p>
                Remove a transparency board record.
            </p>

        </div>


        <a
            href="transparency.php"
            class="back-button"
        >
            Back
        </a>

    </div>


    <div class="form-card">


        <!-- Title -->

        <div class="form-group">

            <label>
                Transparency
            </label>

            <div class="information-view-value">

                <?= e(
                    $transparency['title']
                    ?? 'Untitled Transparency'
                ) ?>

            </div>

        </div>


        <!-- Category -->

        <div class="form-group">

            <label>
                Category
            </label>

            <div class="information-view-value">

                <?= e(
                    $transparency['category']
                    ?? 'Not specified'
                ) ?>

            </div>

        </div>


        <!-- School Year -->

        <div class="form-group">

            <label>
                School Year
            </label>

            <div class="information-view-value">

                <?= e(
                    $transparency['school_year']
                    ?? 'Not specified'
                ) ?>

            </div>

        </div>


        <!-- Status -->

        <div class="form-group">

            <label>
                Status
            </label>

            <div class="information-view-value">

                <?= e(
                    $transparency['status']
                    ?? 'Published'
                ) ?>

            </div>

        </div>


        <!-- Document -->

        <div class="form-group">

            <label>
                Document
            </label>

            <div class="information-view-value">

                <?php if (!empty($transparency['file_name'])): ?>

                    <?= e(
                        $transparency['file_name']
                    ) ?>

                <?php else: ?>

                    <span class="text-muted">
                        No document attached.
                    </span>

                <?php endif; ?>

            </div>

        </div>


        <!-- Warning -->

        <div class="error-message">

            <strong>
                Warning
            </strong>

            <br>

            This action cannot be undone.

            The transparency record and its attached
            document will be permanently deleted.

        </div>


        <!-- Confirmation -->

        <form
            method="POST"
            action=""
        >

            <div class="form-actions">


                <a
                    href="transparency.php"
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

                    Delete Transparency

                </button>


            </div>

        </form>


    </div>

</div>


<?php require_once __DIR__ . '/footer.php'; ?>
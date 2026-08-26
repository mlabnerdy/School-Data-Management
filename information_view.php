<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'View Information';

// Role
$userRole = $_SESSION['role'] ?? '';

$isAdmin = strcasecmp($userRole, 'Administrator') === 0;
$isStaff = strcasecmp($userRole, 'Staff') === 0;
$isTeacher = strcasecmp($userRole, 'Teacher') === 0;

// Access
if (!$isAdmin && !$isStaff && !$isTeacher) {
    redirect('index.php');
}

// ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    redirect('information.php');
}

// Load information
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

// Staff and Teachers can only view Published information
if (
    !$isAdmin &&
    ($information['status'] ?? '') !== 'Published'
) {
    redirect('information.php');
}

// Data
$title = $information['title'] ?? 'Untitled Information';
$category = $information['category'] ?? '';
$description = $information['description'] ?? '';
$schoolYear = $information['school_year'] ?? '';
$datePosted = $information['date_posted'] ?? '';
$status = $information['status'] ?? 'Published';

$fileName = $information['file_name'] ?? '';
$filePath = $information['file_path'] ?? '';
$fileType = $information['file_type'] ?? '';

// File icon
$fileIcon = 'bi-file-earmark-text';

if ($filePath !== '') {

    $extension = strtolower(
        pathinfo($filePath, PATHINFO_EXTENSION)
    );

    if ($extension === 'pdf') {

        $fileIcon = 'bi-file-earmark-pdf';

    } elseif (
        in_array(
            $extension,
            ['doc', 'docx'],
            true
        )
    ) {

        $fileIcon = 'bi-file-earmark-word';

    } elseif (
        in_array(
            $extension,
            ['xls', 'xlsx'],
            true
        )
    ) {

        $fileIcon = 'bi-file-earmark-spreadsheet';

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

    <!-- Header -->
    <div class="page-header">

        <div class="page-header-content">

            <h1>
                Information Details
            </h1>

            <p>
                View information board details.
            </p>

        </div>

        <a
            href="information.php"
            class="back-button"
        >
            Back
        </a>

    </div>


    <!-- Information Card -->
    <div class="form-card information-view-card">


        <!-- Title -->
        <div class="form-group">

            <label>
                Title
            </label>

            <div class="information-view-value">

                <?= e($title) ?>

            </div>

        </div>


        <!-- Category / School Year -->
        <div class="form-row">

            <div class="form-group">

                <label>
                    Category
                </label>

                <div class="information-view-value">

                    <?= $category !== ''
                        ? e($category)
                        : 'Not specified'
                    ?>

                </div>

            </div>


            <div class="form-group">

                <label>
                    School Year
                </label>

                <div class="information-view-value">

                    <?= $schoolYear !== ''
                        ? e($schoolYear)
                        : 'Not specified'
                    ?>

                </div>

            </div>


            <div class="form-group">

                <label>
                    Date Posted
                </label>

                <div class="information-view-value">

                    <?= $datePosted !== ''
                        ? e($datePosted)
                        : 'Not specified'
                    ?>

                </div>

            </div>

        </div>


        <!-- Status -->
        <div class="form-group">

            <label>
                Status
            </label>

            <div class="information-view-value">

                <?php if ($status === 'Published'): ?>

                    <span class="badge bg-success">
                        Published
                    </span>

                <?php elseif ($status === 'Draft'): ?>

                    <span class="badge bg-warning text-dark">
                        Draft
                    </span>

                <?php else: ?>

                    <span class="badge bg-secondary">
                        Archived
                    </span>

                <?php endif; ?>

            </div>

        </div>


        <!-- Description -->
        <div class="form-group">

            <label>
                Description
            </label>

            <div class="information-description">

                <?php if ($description !== ''): ?>

                    <?= nl2br(e($description)) ?>

                <?php else: ?>

                    <span class="text-muted">
                        No description provided.
                    </span>

                <?php endif; ?>

            </div>

        </div>


        <!-- Document -->
        <?php if ($fileName !== ''): ?>

            <div class="form-group">

                <label>
                    Document
                </label>


                <?php if ($isAdmin): ?>

                    <div class="current-document information-document">

                        <div>

                            <i
                                class="bi <?= e($fileIcon) ?>"
                            ></i>

                            <strong>
                                <?= e($fileName) ?>
                            </strong>

                        </div>


                        <a
                            href="<?= e($filePath) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-sm btn-outline-primary"
                        >

                            <i class="bi bi-eye me-1"></i>

                            View Document

                        </a>

                    </div>


                <?php else: ?>

                    <div class="current-document information-document">

                        <div>

                            <i class="bi bi-lock"></i>

                            <span>
                                Document access restricted.
                            </span>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        <?php endif; ?>


        <!-- Actions -->
        <div class="form-actions">

            <a
                href="information.php"
                class="button cancel-button"
            >
                Back to Information Board
            </a>


            <?php if ($isAdmin): ?>

                <a
                    href="information_form.php?id=<?= (int) $id ?>"
                    class="button save-button"
                >

                    <i class="bi bi-pencil me-1"></i>

                    Edit Information

                </a>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
<?php

require_once __DIR__ . '/config.php';

require_login();

$id = (int)($_GET['id'] ?? 0);


/* =========================================================
   CURRENT USER ROLE
========================================================= */

$userRole = $_SESSION['role'] ?? '';

$isAdmin = strcasecmp(
    $userRole,
    'Administrator'
) === 0;


/* =========================================================
   GET TRANSPARENCY RECORD
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM transparency
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$record = $stmt->fetch();

if (!$record) {

    redirect('transparency.php');

}


/* =========================================================
   PAGE
========================================================= */

$pageTitle = 'Transparency Details';

include __DIR__ . '/header.php';

?>

<link
    rel="stylesheet"
    href="assets/transparency_view.css"
>


<div class="transparency-view-page">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="transparency-view-header">

        <div class="transparency-header-left">

            <div class="transparency-page-icon">

                <i class="bi bi-megaphone"></i>

            </div>


            <div>

                <div class="transparency-page-label">

                    TRANSPARENCY BOARD

                </div>


                <h2 class="fw-bold mb-1">

                    Transparency Details

                </h2>


                <p class="text-muted mb-0">

                    View transparency information and documents.

                </p>

            </div>

        </div>


        <div class="transparency-header-actions">

            <a
                href="transparency.php"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Back

            </a>


            <?php if ($isAdmin): ?>

                <a
                    href="transparency_form.php?id=<?= (int)$id ?>"
                    class="btn btn-primary"
                >

                    <i class="bi bi-pencil me-1"></i>

                    Edit

                </a>

            <?php endif; ?>

        </div>

    </div>


    <!-- =====================================================
         TITLE / SUMMARY CARD
    ====================================================== -->

    <div class="transparency-summary-card">


        <div class="transparency-summary-icon">

            <i class="bi bi-file-earmark-text"></i>

        </div>


        <div class="transparency-summary-content">

            <div class="transparency-summary-label">

                TRANSPARENCY ITEM

            </div>


            <h3 class="fw-bold mb-2">

                <?= e(
                    $record['title']
                    ?? 'Untitled Transparency Item'
                ) ?>

            </h3>


            <div class="transparency-summary-meta">


                <?php if (!empty($record['category'])): ?>

                    <span class="transparency-category-badge">

                        <i class="bi bi-folder2 me-1"></i>

                        <?= e($record['category']) ?>

                    </span>

                <?php endif; ?>


                <?php

                $status = $record['status'] ?? 'Published';

                ?>


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

    </div>


    <!-- =====================================================
         BASIC INFORMATION
    ====================================================== -->

    <div class="transparency-info-card mt-4">


        <div class="transparency-card-header">

            <div>

                <h5 class="fw-bold mb-1">

                    Transparency Information

                </h5>


                <p class="text-muted small mb-0">

                    Basic information about this transparency item.

                </p>

            </div>


            <div class="transparency-card-icon">

                <i class="bi bi-info-circle"></i>

            </div>

        </div>


        <div class="transparency-info-grid">


            <!-- Title -->

            <div class="transparency-info-item">

                <div class="transparency-info-label">

                    <i class="bi bi-file-text"></i>

                    Title

                </div>


                <div class="transparency-info-value">

                    <?= !empty($record['title'])
                        ? e($record['title'])
                        : '—'
                    ?>

                </div>

            </div>


            <!-- Category -->

            <div class="transparency-info-item">

                <div class="transparency-info-label">

                    <i class="bi bi-folder2"></i>

                    Category

                </div>


                <div class="transparency-info-value">

                    <?= !empty($record['category'])
                        ? e($record['category'])
                        : '—'
                    ?>

                </div>

            </div>


            <!-- School Year -->

            <div class="transparency-info-item">

                <div class="transparency-info-label">

                    <i class="bi bi-calendar3"></i>

                    School Year

                </div>


                <div class="transparency-info-value">

                    <?= !empty($record['school_year'])
                        ? e($record['school_year'])
                        : '—'
                    ?>

                </div>

            </div>


            <!-- Date Posted -->

            <div class="transparency-info-item">

                <div class="transparency-info-label">

                    <i class="bi bi-calendar-event"></i>

                    Date Posted

                </div>


                <div class="transparency-info-value">

                    <?= !empty($record['date_posted'])
                        ? e($record['date_posted'])
                        : '—'
                    ?>

                </div>

            </div>


            <!-- Status -->

            <div class="transparency-info-item">

                <div class="transparency-info-label">

                    <i class="bi bi-check-circle"></i>

                    Status

                </div>


                <div class="transparency-info-value">

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


            <!-- Record ID -->

            <div class="transparency-info-item">

                <div class="transparency-info-label">

                    <i class="bi bi-hash"></i>

                    Record ID

                </div>


                <div class="transparency-info-value">

                    <?= (int)$record['id'] ?>

                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         DESCRIPTION
    ====================================================== -->

    <div class="transparency-info-card mt-4">


        <div class="transparency-card-header">

            <div>

                <h5 class="fw-bold mb-1">

                    Description

                </h5>


                <p class="text-muted small mb-0">

                    Details and information about this item.

                </p>

            </div>


            <div class="transparency-card-icon">

                <i class="bi bi-card-text"></i>

            </div>

        </div>


        <div class="transparency-description-content">


            <?php if (!empty($record['description'])): ?>

                <?= nl2br(
                    e($record['description'])
                ) ?>

            <?php else: ?>

                <span class="text-muted">

                    No description provided.

                </span>

            <?php endif; ?>


        </div>

    </div>


    <!-- =====================================================
         DOCUMENT ATTACHMENT
    ====================================================== -->

    <div class="transparency-info-card mt-4">


        <div class="transparency-card-header">

            <div>

                <h5 class="fw-bold mb-1">

                    Document Attachment

                </h5>


                <p class="text-muted small mb-0">

                    Supporting document uploaded for this
                    transparency item.

                </p>

            </div>


            <div class="transparency-card-icon">

                <i class="bi bi-paperclip"></i>

            </div>

        </div>


        <div class="transparency-document-content">


            <?php if (!empty($record['file_path'])): ?>


                <div class="transparency-document-card">


                    <div class="transparency-document-icon">

                        <i class="bi bi-file-earmark-text"></i>

                    </div>


                    <div class="transparency-document-info">

                        <div class="fw-semibold">

                            <?= e(
                                $record['file_name']
                                ?? 'Attached Document'
                            ) ?>

                        </div>


                        <small class="text-muted">

                            <?php if (
                                !empty($record['file_type'])
                            ): ?>

                                <?= e(
                                    $record['file_type']
                                ) ?>

                            <?php else: ?>

                                Attached document

                            <?php endif; ?>

                        </small>

                    </div>


                    <div class="transparency-document-actions">

                        <a
                            href="<?= e(
                                $record['file_path']
                            ) ?>"
                            target="_blank"
                            class="btn btn-sm btn-outline-primary"
                        >

                            <i class="bi bi-eye me-1"></i>

                            View

                        </a>


                        <a
                            href="<?= e(
                                $record['file_path']
                            ) ?>"
                            download
                            class="btn btn-sm btn-primary"
                        >

                            <i class="bi bi-download me-1"></i>

                            Download

                        </a>

                    </div>


                </div>


            <?php else: ?>


                <div class="transparency-no-document">


                    <div class="transparency-no-document-icon">

                        <i class="bi bi-folder2-open"></i>

                    </div>


                    <h6 class="fw-bold mb-1">

                        No document attached

                    </h6>


                    <p class="text-muted small mb-3">

                        There is currently no supporting document
                        uploaded for this transparency item.

                    </p>


                    <?php if ($isAdmin): ?>

                        <a
                            href="transparency_form.php?id=<?= (int)$id ?>"
                            class="btn btn-sm btn-outline-primary"
                        >

                            <i class="bi bi-upload me-1"></i>

                            Upload Document

                        </a>

                    <?php endif; ?>


                </div>


            <?php endif; ?>


        </div>

    </div>


    <!-- =====================================================
         RECORD INFORMATION
    ====================================================== -->

    <div class="transparency-info-card mt-4">


        <div class="transparency-card-header">

            <div>

                <h5 class="fw-bold mb-1">

                    Record Information

                </h5>


                <p class="text-muted small mb-0">

                    System information about this record.

                </p>

            </div>


            <div class="transparency-card-icon">

                <i class="bi bi-clock-history"></i>

            </div>

        </div>


        <div class="transparency-info-grid">


            <!-- Created -->

            <div class="transparency-info-item">

                <div class="transparency-info-label">

                    <i class="bi bi-calendar-plus"></i>

                    Created

                </div>


                <div class="transparency-info-value">

                    <?= !empty(
                        $record['created_at']
                    )
                        ? e($record['created_at'])
                        : '—'
                    ?>

                </div>

            </div>


            <!-- Updated -->

            <div class="transparency-info-item">

                <div class="transparency-info-label">

                    <i class="bi bi-calendar-check"></i>

                    Last Updated

                </div>


                <div class="transparency-info-value">

                    <?= !empty(
                        $record['updated_at']
                    )
                        ? e($record['updated_at'])
                        : '—'
                    ?>

                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         BOTTOM ACTIONS
    ====================================================== -->

    <div class="transparency-bottom-actions mt-4">


        <a
            href="transparency.php"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Back to Transparency Board

        </a>


        <?php if ($isAdmin): ?>


            <div class="transparency-bottom-right">


                <!-- Edit -->

                <a
                    href="transparency_form.php?id=<?= (int)$id ?>"
                    class="btn btn-primary"
                >

                    <i class="bi bi-pencil me-1"></i>

                    Edit Item

                </a>


                <!-- Delete -->

                <a
                    href="delete_transparency.php?id=<?= (int)$id ?>"
                    class="btn btn-danger"
                    data-confirm="Delete this transparency item?"
                >

                    <i class="bi bi-trash me-1"></i>

                    Delete

                </a>


            </div>


        <?php endif; ?>


    </div>


</div>


<?php include 'footer.php'; ?>
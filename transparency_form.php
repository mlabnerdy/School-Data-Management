<?php

require_once __DIR__ . '/config.php';

require_login();


// ==========================================================
// Administrator-Only Access
// ==========================================================

$userRole = $_SESSION['role'] ?? '';

if (strcasecmp($userRole, 'Administrator') !== 0) {

    redirect('transparency.php');

}


$pageTitle = 'Transparency Form';

$id = (int)($_GET['id'] ?? 0);

$isEdit = $id > 0;

$record = [];

$error = '';


// ==========================================================
// Current Logged-in User
// ==========================================================

$currentUserId = (int)($_SESSION['user_id'] ?? 0);


// ==========================================================
// Transparency Categories
// ==========================================================

$categories = [
    'School Profile',
    'School Budget',
    'Financial Reports',
    'Procurement',
    'Projects',
    'Accomplishments',
    'Personnel',
    'School Forms',
    'Other'
];


// ==========================================================
// Status Options
// ==========================================================

$statuses = [
    'Published',
    'Draft',
    'Archived'
];


// ==========================================================
// Load Existing Record
// ==========================================================

if ($isEdit) {

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
}


// ==========================================================
// Process Form
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // ======================================================
    // Collect Form Data
    // ======================================================

    $data = [

        'title' => trim(
            $_POST['title'] ?? ''
        ),

        'category' => trim(
            $_POST['category'] ?? ''
        ),

        'description' => trim(
            $_POST['description'] ?? ''
        ),

        'school_year' => trim(
            $_POST['school_year'] ?? ''
        ),

        'date_posted' => !empty(
            $_POST['date_posted'] ?? ''
        )
            ? $_POST['date_posted']
            : null,

        'status' => trim(
            $_POST['status'] ?? 'Published'
        )

    ];


    // ======================================================
    // Validate
    // ======================================================

    if ($currentUserId <= 0) {

        $error =
            'Unable to identify the current user. Please log in again.';

    } elseif ($data['title'] === '') {

        $error =
            'Transparency item title is required.';

    } elseif (
        mb_strlen($data['title']) > 255
    ) {

        $error =
            'Transparency item title must not exceed 255 characters.';

    } elseif (
        $data['category'] !== '' &&
        !in_array(
            $data['category'],
            $categories,
            true
        )
    ) {

        $error =
            'Please select a valid transparency category.';

    } elseif (
        $data['status'] !== '' &&
        !in_array(
            $data['status'],
            $statuses,
            true
        )
    ) {

        $error =
            'Please select a valid status.';

    } elseif (
        $data['school_year'] !== '' &&
        !preg_match(
            '/^[0-9]{4}(-[0-9]{4})?$/',
            $data['school_year']
        )
    ) {

        $error =
            'School Year must use a format such as 2026-2027.';

    }


    // ======================================================
    // Save Record
    // ======================================================

    if ($error === '') {

        try {


            // ==================================================
            // UPDATE
            // ==================================================

            if ($isEdit) {

                /*
                 * created_by remains unchanged.
                 *
                 * updated_by becomes the currently logged-in
                 * Administrator who performed the update.
                 */

                $sql = "
                    UPDATE transparency SET

                        updated_by = ?,

                        title = ?,
                        category = ?,
                        description = ?,
                        school_year = ?,
                        date_posted = ?,
                        status = ?

                    WHERE id = ?
                ";

                $params = [

                    $currentUserId,

                    $data['title'],

                    $data['category'] !== ''
                        ? $data['category']
                        : null,

                    $data['description'] !== ''
                        ? $data['description']
                        : null,

                    $data['school_year'] !== ''
                        ? $data['school_year']
                        : null,

                    $data['date_posted'],

                    $data['status'],

                    $id

                ];

                $stmt = $pdo->prepare($sql);

                $stmt->execute($params);

            }


            // ==================================================
            // INSERT
            // ==================================================

            else {

                $sql = "
                    INSERT INTO transparency (

                        title,
                        category,
                        description,
                        school_year,
                        date_posted,
                        status,

                        created_by,
                        updated_by

                    )
                    VALUES (

                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,

                        ?,
                        ?

                    )
                ";

                $params = [

                    $data['title'],

                    $data['category'] !== ''
                        ? $data['category']
                        : null,

                    $data['description'] !== ''
                        ? $data['description']
                        : null,

                    $data['school_year'] !== ''
                        ? $data['school_year']
                        : null,

                    $data['date_posted'],

                    $data['status'],

                    $currentUserId,

                    $currentUserId

                ];

                $stmt = $pdo->prepare($sql);

                $stmt->execute($params);

                $id = (int)$pdo->lastInsertId();

                $isEdit = true;

            }


            // ==================================================
            // Upload Attachment
            // ==================================================

            if (
                isset($_FILES['document']) &&
                $_FILES['document']['error'] !==
                    UPLOAD_ERR_NO_FILE
            ) {

                $newFile = upload_file(
                    'document',
                    'transparency',
                    [
                        'pdf',
                        'doc',
                        'docx',
                        'xls',
                        'xlsx',
                        'ppt',
                        'pptx',
                        'jpg',
                        'jpeg',
                        'png',
                        'webp'
                    ],
                    10485760
                );


                if ($newFile) {


                    // ------------------------------------------
                    // Delete Existing File
                    // ------------------------------------------

                    if (
                        !empty(
                            $record['file_path']
                        )
                    ) {

                        delete_upload(
                            $record['file_path']
                        );

                    }


                    $originalFileName =
                        $_FILES['document']['name']
                        ?? '';

                    $fileType =
                        $_FILES['document']['type']
                        ?? '';


                    // ------------------------------------------
                    // Save File Information
                    // ------------------------------------------

                    $stmt = $pdo->prepare("
                        UPDATE transparency

                        SET
                            file_name = ?,
                            file_path = ?,
                            file_type = ?

                        WHERE id = ?
                    ");

                    $stmt->execute([

                        $originalFileName,

                        $newFile,

                        $fileType,

                        $id

                    ]);

                }

            }


            // ==================================================
            // Open Saved Record
            // ==================================================

            redirect(
                'transparency_view.php?id=' . $id
            );

        }


        // ======================================================
        // Database Error
        // ======================================================

        catch (PDOException $e) {

            $error =
                'Unable to save the transparency record: '
                . $e->getMessage();

        }

    }


    // ======================================================
    // Keep Entered Values
    // ======================================================

    $record = array_merge(
        $record,
        $data
    );

}


include 'header.php';

?>

<link
    rel="stylesheet"
    href="assets/transparency_form.css"
>


<div class="transparency-form-page">


    <!-- ======================================================
         PAGE HEADER
    ======================================================= -->

    <div class="transparency-form-header">

        <div>

            <div class="transparency-form-label">

                TRANSPARENCY BOARD

            </div>


            <h2 class="transparency-title">

                <?= $isEdit
                    ? 'Edit Transparency Item'
                    : 'Add Transparency Item'
                ?>

            </h2>


            <p class="transparency-subtitle">

                <?= $isEdit
                    ? 'Update the transparency information below.'
                    : 'Add a new transparency document or report.'
                ?>

            </p>

        </div>


        <a
            href="transparency.php"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Back to Board

        </a>

    </div>


    <!-- ======================================================
         ERROR
    ======================================================= -->

    <?php if ($error): ?>

        <div class="alert alert-danger transparency-form-alert">

            <i class="bi bi-exclamation-circle-fill me-2"></i>

            <?= e($error) ?>

        </div>

    <?php endif; ?>


    <!-- ======================================================
         FORM
    ======================================================= -->

    <form
        method="POST"
        enctype="multipart/form-data"
    >

        <div class="row g-4">


            <!-- ==================================================
                 MAIN CONTENT
            =================================================== -->

            <div class="col-12 col-lg-8">


                <!-- ==================================================
                     BASIC INFORMATION
                =================================================== -->

                <div class="transparency-form-card">

                    <div class="transparency-form-card-header">

                        <div class="transparency-section-icon">

                            <i class="bi bi-file-earmark-text"></i>

                        </div>


                        <div>

                            <h5>
                                Transparency Information
                            </h5>

                            <p>
                                Enter the details of the transparency item.
                            </p>

                        </div>

                    </div>


                    <div class="transparency-form-card-body">

                        <div class="row g-3">


                            <!-- Title -->

                            <div class="col-12">

                                <label class="form-label">

                                    Title

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>


                                <input
                                    type="text"
                                    name="title"
                                    class="form-control"
                                    value="<?= e(
                                        $record['title'] ?? ''
                                    ) ?>"
                                    maxlength="255"
                                    required
                                    placeholder="e.g. School Financial Report 2026"
                                >


                                <div class="form-text">

                                    Enter the official title of the
                                    transparency document or report.

                                </div>

                            </div>


                            <!-- Category -->

                            <div class="col-12 col-md-6">

                                <label class="form-label">

                                    Category

                                </label>


                                <select
                                    name="category"
                                    class="form-select"
                                >

                                    <option value="">

                                        Select Category

                                    </option>


                                    <?php foreach (
                                        $categories
                                        as $category
                                    ): ?>

                                        <option
                                            value="<?= e(
                                                $category
                                            ) ?>"
                                            <?= (
                                                ($record[
                                                    'category'
                                                ] ?? '') ===
                                                $category
                                            )
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >

                                            <?= e(
                                                $category
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- School Year -->

                            <div class="col-12 col-md-6">

                                <label class="form-label">

                                    School Year

                                </label>


                                <input
                                    type="text"
                                    name="school_year"
                                    class="form-control"
                                    value="<?= e(
                                        $record[
                                            'school_year'
                                        ] ?? ''
                                    ) ?>"
                                    maxlength="20"
                                    placeholder="e.g. 2026-2027"
                                >


                                <div class="form-text">

                                    Example:
                                    <strong>2026-2027</strong>

                                </div>

                            </div>


                            <!-- Date Posted -->

                            <div class="col-12 col-md-6">

                                <label class="form-label">

                                    Date Posted

                                </label>


                                <input
                                    type="date"
                                    name="date_posted"
                                    class="form-control"
                                    value="<?= e(
                                        $record[
                                            'date_posted'
                                        ] ?? ''
                                    ) ?>"
                                >

                            </div>


                            <!-- Status -->

                            <div class="col-12 col-md-6">

                                <label class="form-label">

                                    Status

                                </label>


                                <select
                                    name="status"
                                    class="form-select"
                                >

                                    <?php foreach (
                                        $statuses
                                        as $status
                                    ): ?>

                                        <option
                                            value="<?= e(
                                                $status
                                            ) ?>"
                                            <?= (
                                                ($record[
                                                    'status'
                                                ] ?? 'Published') ===
                                                $status
                                            )
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >

                                            <?= e(
                                                $status
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- Description -->

                            <div class="col-12">

                                <label class="form-label">

                                    Description

                                </label>


                                <textarea
                                    name="description"
                                    class="form-control"
                                    rows="7"
                                    maxlength="5000"
                                    placeholder="Enter details about this transparency item..."
                                ><?= e(
                                    $record[
                                        'description'
                                    ] ?? ''
                                ) ?></textarea>


                                <div class="form-text">

                                    Provide a short explanation or
                                    relevant details about this item.

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     ATTACHMENT
                =================================================== -->

                <div class="transparency-form-card mt-4">

                    <div class="transparency-form-card-header">

                        <div class="transparency-section-icon">

                            <i class="bi bi-paperclip"></i>

                        </div>


                        <div>

                            <h5>
                                Document Attachment
                            </h5>

                            <p>
                                Upload the supporting transparency document.
                            </p>

                        </div>

                    </div>


                    <div class="transparency-form-card-body">


                        <?php if (
                            !empty(
                                $record['file_path']
                            )
                        ): ?>


                            <!-- Existing File -->

                            <div class="transparency-current-file">

                                <div class="transparency-file-icon">

                                    <i class="bi bi-file-earmark-text"></i>

                                </div>


                                <div class="transparency-file-info">

                                    <div class="fw-semibold">

                                        <?= e(
                                            $record[
                                                'file_name'
                                            ]
                                            ?? 'Current Document'
                                        ) ?>

                                    </div>


                                    <small class="text-muted">

                                        Current attachment

                                    </small>

                                </div>


                                <a
                                    href="<?= e(
                                        $record[
                                            'file_path'
                                        ]
                                    ) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-sm btn-outline-primary"
                                >

                                    <i class="bi bi-eye me-1"></i>

                                    View

                                </a>

                            </div>


                            <hr class="my-4">

                        <?php endif; ?>


                        <!-- New File -->

                        <label class="form-label">

                            <?= $isEdit
                                ? 'Replace Document'
                                : 'Upload Document'
                            ?>

                        </label>


                        <input
                            type="file"
                            name="document"
                            class="form-control"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp"
                        >


                        <div class="form-text">

                            Allowed formats:

                            PDF, DOC, DOCX, XLS, XLSX,
                            PPT, PPTX, JPG, JPEG, PNG, WEBP.

                            Maximum file size: 10 MB.

                        </div>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 SIDE PANEL
            =================================================== -->

            <div class="col-12 col-lg-4">


                <!-- ==================================================
                     SAVE CARD
                =================================================== -->

                <div class="transparency-form-card transparency-save-card">

                    <div class="transparency-form-card-header">

                        <div class="transparency-section-icon">

                            <i class="bi bi-check2-circle"></i>

                        </div>


                        <div>

                            <h5>
                                Save Record
                            </h5>

                            <p>
                                Review your information before saving.
                            </p>

                        </div>

                    </div>


                    <div class="transparency-save-body">


                        <div class="transparency-save-info">

                            <i class="bi bi-info-circle"></i>

                            <span>

                                Make sure the title, category,
                                school year, and attachment are
                                correct before saving.

                            </span>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100 transparency-save-btn"
                        >

                            <i class="bi bi-check-lg me-1"></i>

                            <?= $isEdit
                                ? 'Update Transparency Item'
                                : 'Save Transparency Item'
                            ?>

                        </button>


                        <a
                            href="transparency.php"
                            class="btn btn-outline-secondary w-100 mt-2"
                        >

                            Cancel

                        </a>

                    </div>

                </div>


                <!-- ==================================================
                     STATUS INFORMATION
                =================================================== -->

                <div class="transparency-form-card mt-4">

                    <div class="transparency-form-card-header">

                        <div class="transparency-section-icon">

                            <i class="bi bi-lightbulb"></i>

                        </div>


                        <div>

                            <h5>
                                Status Guide
                            </h5>

                            <p>
                                What each status means.
                            </p>

                        </div>

                    </div>


                    <div class="transparency-status-guide">


                        <div class="transparency-status-item">

                            <span class="badge bg-success">

                                Published

                            </span>

                            <p>

                                The transparency item is currently
                                available on the board.

                            </p>

                        </div>


                        <div class="transparency-status-item">

                            <span class="badge bg-warning text-dark">

                                Draft

                            </span>

                            <p>

                                The item is being prepared and is
                                not yet ready for publication.

                            </p>

                        </div>


                        <div class="transparency-status-item">

                            <span class="badge bg-secondary">

                                Archived

                            </span>

                            <p>

                                The item has been kept for record
                                purposes but is no longer active.

                            </p>

                        </div>


                    </div>

                </div>


            </div>

        </div>

    </form>

</div>


<?php include 'footer.php'; ?>
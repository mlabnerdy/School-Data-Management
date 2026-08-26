<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Transparency Board';


// ==========================================================
// Current User Role
// ==========================================================

$userRole = $_SESSION['role'] ?? '';

$isAdmin = strcasecmp(
    $userRole,
    'Administrator'
) === 0;

$isStaff = strcasecmp(
    $userRole,
    'Staff'
) === 0;

$isTeacher = strcasecmp(
    $userRole,
    'Teacher'
) === 0;


// ==========================================================
// Access Check
// ==========================================================
//
// Transparency Board can be viewed by:
//
// - Administrator
// - Staff
// - Teacher
//
// Only Administrator can manage records.
//
// ==========================================================

if (
    !$isAdmin &&
    !$isStaff &&
    !$isTeacher
) {

    redirect('index.php');

}


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
// Search Filters
// ==========================================================

$q = trim(
    $_GET['q'] ?? ''
);

$category = trim(
    $_GET['category'] ?? ''
);

$status = trim(
    $_GET['status'] ?? ''
);


// ==========================================================
// Validate Filters
// ==========================================================

if (
    $category !== '' &&
    !in_array(
        $category,
        $categories,
        true
    )
) {

    $category = '';

}


if (
    $status !== '' &&
    !in_array(
        $status,
        $statuses,
        true
    )
) {

    $status = '';

}


// ==========================================================
// Build Query
// ==========================================================

$sql = "

    SELECT *

    FROM transparency

";

$where = [];

$params = [];


// ==========================================================
// VISIBILITY RULE
// ==========================================================
//
// Administrator:
//     Can see all statuses.
//
// Staff / Teacher:
//     Can only see Published records.
//
// This restriction is applied directly to the SQL query
// so Draft and Archived records are not exposed to
// Staff or Teacher users.
//
// ==========================================================

if (!$isAdmin) {

    $where[] = "status = ?";

    $params[] = 'Published';

}


// ==========================================================
// Search
// ==========================================================

if ($q !== '') {

    $where[] = "

        (

            title LIKE ?

            OR description LIKE ?

            OR school_year LIKE ?

            OR category LIKE ?

        )

    ";

    $search = "%{$q}%";

    $params[] = $search;

    $params[] = $search;

    $params[] = $search;

    $params[] = $search;

}


// ==========================================================
// Category Filter
// ==========================================================

if ($category !== '') {

    $where[] = "category = ?";

    $params[] = $category;

}


// ==========================================================
// Status Filter
// ==========================================================
//
// Administrator:
//     Can filter Published / Draft / Archived.
//
// Staff / Teacher:
//     Even if they request ?status=Draft or
//     ?status=Archived manually, the visibility rule
//     above keeps the query restricted to Published.
//
// ==========================================================

if ($status !== '') {

    $where[] = "status = ?";

    $params[] = $status;

}


// ==========================================================
// WHERE
// ==========================================================

if (!empty($where)) {

    $sql .= "

        WHERE

        " . implode(
            " AND ",
            $where
        );

}


// ==========================================================
// Ordering
// ==========================================================

$sql .= "

    ORDER BY

        date_posted DESC,

        id DESC

";


// ==========================================================
// Execute Query
// ==========================================================

$stmt = $pdo->prepare($sql);

$stmt->execute(
    $params
);

$rows = $stmt->fetchAll();


// ==========================================================
// Include Header
// ==========================================================

include 'header.php';

?>

<link
    rel="stylesheet"
    href="assets/transparency.css"
>


<div class="transparency-page">


    <!-- ======================================================
         PAGE HEADER
    ======================================================= -->

    <div class="transparency-page-header">

        <div class="transparency-header-left">

            <div class="transparency-page-icon">

                <i class="bi bi-megaphone"></i>

            </div>


            <div>

                <div class="transparency-page-label">

                    TRANSPARENCY BOARD

                </div>


                <h2 class="fw-bold mb-1">

                    Transparency Board

                </h2>


                <p class="text-muted mb-0">

                    View school transparency information,
                    reports, and documents.

                </p>

            </div>

        </div>


        <!-- ==================================================
             ADMIN ONLY
        =================================================== -->

        <?php if ($isAdmin): ?>

            <div class="transparency-header-actions">

                <a
                    href="transparency_form.php"
                    class="btn btn-primary"
                >

                    <i class="bi bi-plus-lg me-1"></i>

                    Add Transparency Item

                </a>

            </div>

        <?php endif; ?>

    </div>


    <!-- ======================================================
         SEARCH & FILTER
    ======================================================= -->

    <div class="transparency-search-card">

        <form
            method="GET"
            class="transparency-search-form"
        >


            <!-- Search -->

            <div class="transparency-search-main">

                <div class="transparency-search-input">

                    <i class="bi bi-search"></i>

                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        value="<?= e($q) ?>"
                        placeholder="Search transparency items..."
                        aria-label="Search transparency items"
                    >

                </div>

            </div>


            <!-- Category -->

            <div class="transparency-filter">

                <select
                    name="category"
                    class="form-select"
                >

                    <option value="">

                        All Categories

                    </option>


                    <?php foreach (
                        $categories
                        as $itemCategory
                    ): ?>

                        <option
                            value="<?= e($itemCategory) ?>"
                            <?= $category === $itemCategory
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= e($itemCategory) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Status -->

            <div class="transparency-filter">

                <select
                    name="status"
                    class="form-select"
                >

                    <option value="">

                        All Statuses

                    </option>


                    <?php foreach (
                        $statuses
                        as $itemStatus
                    ): ?>

                        <option
                            value="<?= e($itemStatus) ?>"
                            <?= $status === $itemStatus
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= e($itemStatus) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Search Button -->

            <button
                type="submit"
                class="btn btn-primary transparency-search-btn"
            >

                <i class="bi bi-search me-1"></i>

                Search

            </button>


            <!-- Reset -->

            <?php if (
                $q !== '' ||
                $category !== '' ||
                $status !== ''
            ): ?>

                <a
                    href="transparency.php"
                    class="btn btn-outline-secondary transparency-reset-btn"
                >

                    <i class="bi bi-arrow-counterclockwise me-1"></i>

                    Reset

                </a>

            <?php endif; ?>

        </form>

    </div>


    <!-- ======================================================
         RECORD HEADER
    ======================================================= -->

    <div class="transparency-record-header">

        <div>

            <h5 class="fw-bold mb-1">

                Transparency Records

            </h5>


            <p class="text-muted small mb-0">

                <?= count($rows) ?>

                item<?= count($rows) !== 1 ? 's' : '' ?>

                found

            </p>

        </div>


        <div class="transparency-record-icon">

            <i class="bi bi-file-earmark-text"></i>

        </div>

    </div>


    <!-- ======================================================
         RECORDS
    ======================================================= -->

    <div class="transparency-records">


        <?php if (!empty($rows)): ?>


            <?php foreach ($rows as $r): ?>


                <div class="transparency-item-card">


                    <!-- ==================================================
                         ITEM ICON
                    =================================================== -->

                    <div class="transparency-item-icon">

                        <?php

                        $itemIcon =
                            'bi-file-earmark-text';


                        if (
                            !empty(
                                $r['file_path']
                            )
                        ) {

                            $extension =
                                strtolower(
                                    pathinfo(
                                        $r['file_path'],
                                        PATHINFO_EXTENSION
                                    )
                                );


                            if (
                                in_array(
                                    $extension,
                                    [
                                        'jpg',
                                        'jpeg',
                                        'png',
                                        'webp'
                                    ],
                                    true
                                )
                            ) {

                                $itemIcon =
                                    'bi-file-earmark-image';

                            } elseif (
                                in_array(
                                    $extension,
                                    [
                                        'xls',
                                        'xlsx'
                                    ],
                                    true
                                )
                            ) {

                                $itemIcon =
                                    'bi-file-earmark-spreadsheet';

                            } elseif (
                                in_array(
                                    $extension,
                                    [
                                        'ppt',
                                        'pptx'
                                    ],
                                    true
                                )
                            ) {

                                $itemIcon =
                                    'bi-file-earmark-slides';

                            } elseif (
                                in_array(
                                    $extension,
                                    [
                                        'doc',
                                        'docx'
                                    ],
                                    true
                                )
                            ) {

                                $itemIcon =
                                    'bi-file-earmark-word';

                            } elseif (
                                $extension === 'pdf'
                            ) {

                                $itemIcon =
                                    'bi-file-earmark-pdf';

                            }

                        }

                        ?>


                        <i
                            class="bi <?= e($itemIcon) ?>"
                        ></i>

                    </div>


                    <!-- ==================================================
                         MAIN INFORMATION
                    =================================================== -->

                    <div class="transparency-item-content">


                        <div class="transparency-item-top">


                            <div class="transparency-item-title">

                                <?= e(
                                    $r['title']
                                    ??
                                    'Untitled Transparency Item'
                                ) ?>

                            </div>


                            <!-- Status -->

                            <?php

                            $itemStatus =
                                $r['status']
                                ??
                                'Published';

                            ?>


                            <?php if (
                                $itemStatus === 'Published'
                            ): ?>

                                <span class="badge bg-success">

                                    Published

                                </span>


                            <?php elseif (
                                $itemStatus === 'Draft'
                            ): ?>

                                <span class="badge bg-warning text-dark">

                                    Draft

                                </span>


                            <?php else: ?>

                                <span class="badge bg-secondary">

                                    Archived

                                </span>

                            <?php endif; ?>


                        </div>


                        <!-- ==================================================
                             META
                        =================================================== -->

                        <div class="transparency-item-meta">


                            <?php if (
                                !empty(
                                    $r['category']
                                )
                            ): ?>

                                <span>

                                    <i class="bi bi-folder2"></i>

                                    <?= e(
                                        $r['category']
                                    ) ?>

                                </span>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $r['school_year']
                                )
                            ): ?>

                                <span>

                                    <i class="bi bi-calendar3"></i>

                                    SY

                                    <?= e(
                                        $r['school_year']
                                    ) ?>

                                </span>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $r['date_posted']
                                )
                            ): ?>

                                <span>

                                    <i class="bi bi-calendar-event"></i>

                                    <?= e(
                                        $r['date_posted']
                                    ) ?>

                                </span>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $r['file_path']
                                )
                            ): ?>

                                <span>

                                    <i class="bi bi-paperclip"></i>

                                    Attachment

                                </span>

                            <?php endif; ?>


                        </div>


                        <!-- ==================================================
                             DESCRIPTION
                        =================================================== -->

                        <?php if (
                            !empty(
                                $r['description']
                            )
                        ): ?>

                            <div
                                class="transparency-item-description"
                            >

                                <?= nl2br(
                                    e(
                                        $r['description']
                                    )
                                ) ?>

                            </div>


                        <?php else: ?>

                            <div
                                class="transparency-item-description text-muted"
                            >

                                No description provided.

                            </div>

                        <?php endif; ?>


                        <!-- ==================================================
                             FILE
                        =================================================== -->

                        <?php if (
                            !empty(
                                $r['file_path']
                            )
                        ): ?>

                            <div
                                class="transparency-item-file"
                            >

                                <div
                                    class="transparency-file-left"
                                >

                                    <i class="bi bi-paperclip"></i>

                                    <span>

                                        <?= e(
                                            $r['file_name']
                                            ??
                                            'Attached Document'
                                        ) ?>

                                    </span>

                                </div>


                                <a
                                    href="<?= e(
                                        $r['file_path']
                                    ) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-sm btn-outline-primary"
                                >

                                    <i class="bi bi-eye me-1"></i>

                                    View Document

                                </a>

                            </div>

                        <?php endif; ?>


                    </div>


                    <!-- ==================================================
                         ACTIONS
                    =================================================== -->

                    <div class="transparency-item-actions">


                        <!-- ==================================================
                             VIEW
                        =================================================== -->

                        <a
                            href="transparency_view.php?id=<?= (int)$r['id'] ?>"
                            class="btn btn-sm btn-outline-primary"
                            title="View Transparency Item"
                        >

                            <i class="bi bi-eye"></i>

                            <span>
                                View
                            </span>

                        </a>


                        <!-- ==================================================
                             ADMIN ONLY: EDIT
                        =================================================== -->

                        <?php if ($isAdmin): ?>

                            <a
                                href="transparency_form.php?id=<?= (int)$r['id'] ?>"
                                class="btn btn-sm btn-primary"
                                title="Edit Transparency Item"
                            >

                                <i class="bi bi-pencil"></i>

                                <span>
                                    Edit
                                </span>

                            </a>


                            <!-- ==================================================
                                 ADMIN ONLY: DELETE
                            =================================================== -->

                            <a
                                href="delete_transparency.php?id=<?= (int)$r['id'] ?>"
                                class="btn btn-sm btn-danger"
                                data-confirm="Delete this transparency item?"
                                title="Delete Transparency Item"
                            >

                                <i class="bi bi-trash"></i>

                                <span>
                                    Delete
                                </span>

                            </a>

                        <?php endif; ?>


                    </div>


                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <!-- ==================================================
                 EMPTY STATE
            =================================================== -->

            <div class="transparency-empty-state">


                <div class="transparency-empty-icon">

                    <i class="bi bi-file-earmark-x"></i>

                </div>


                <h5 class="fw-bold mb-1">

                    No transparency records found

                </h5>


                <?php if (
                    $q !== '' ||
                    $category !== '' ||
                    $status !== ''
                ): ?>


                    <p class="text-muted mb-3">

                        No transparency items matched
                        your current search or filters.

                    </p>


                    <a
                        href="transparency.php"
                        class="btn btn-outline-primary btn-sm"
                    >

                        <i class="bi bi-arrow-counterclockwise me-1"></i>

                        Reset Filters

                    </a>


                <?php else: ?>


                    <?php if ($isAdmin): ?>

                        <p class="text-muted mb-3">

                            Start by adding your first
                            transparency item.

                        </p>


                        <a
                            href="transparency_form.php"
                            class="btn btn-primary btn-sm"
                        >

                            <i class="bi bi-plus-lg me-1"></i>

                            Add Transparency Item

                        </a>

                    <?php else: ?>

                        <p class="text-muted mb-0">

                            There are currently no
                            transparency items available.

                        </p>

                    <?php endif; ?>


                <?php endif; ?>


            </div>


        <?php endif; ?>


    </div>


</div>


<?php include 'footer.php'; ?>
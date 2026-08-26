<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Information Board';

// Role
$userRole = $_SESSION['role'] ?? '';

$isAdmin = strcasecmp($userRole, 'Administrator') === 0;
$isStaff = strcasecmp($userRole, 'Staff') === 0;
$isTeacher = strcasecmp($userRole, 'Teacher') === 0;

// Access
if (!$isAdmin && !$isStaff && !$isTeacher) {
    redirect('index.php');
}

// Categories
$categories = [
    'Administrative',
    'Property',
    'Procurement',
    'Inventory',
    'Memorandum',
    'Other'
];

// Statuses
$statuses = [
    'Published',
    'Draft',
    'Archived'
];

// Filters
$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$status = trim($_GET['status'] ?? '');

// Validate category
if (
    $category !== '' &&
    !in_array($category, $categories, true)
) {
    $category = '';
}

// Validate status
if (
    $status !== '' &&
    !in_array($status, $statuses, true)
) {
    $status = '';
}

// Query
$sql = "
    SELECT *
    FROM information
";

$where = [];
$params = [];

// Staff and Teachers only see Published
if (!$isAdmin) {

    $where[] = "status = ?";
    $params[] = 'Published';

}

// Search
if ($q !== '') {

    $where[] = "
        (
            title LIKE ?
            OR description LIKE ?
            OR category LIKE ?
            OR school_year LIKE ?
            OR file_name LIKE ?
        )
    ";

    $search = "%{$q}%";

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

// Category filter
if ($category !== '') {

    $where[] = "category = ?";
    $params[] = $category;

}

// Status filter
// Only Administrator can filter Draft / Archived
if ($status !== '' && $isAdmin) {

    $where[] = "status = ?";
    $params[] = $status;

}

// WHERE
if (!empty($where)) {

    $sql .= "
        WHERE
        " . implode(" AND ", $where);

}

// Order
$sql .= "
    ORDER BY
        date_posted DESC,
        id DESC
";

// Execute
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Header
include 'header.php';

?>

<link
    rel="stylesheet"
    href="assets/transparency.css"
>

<div class="transparency-page">

    <!-- Page Header -->
    <div class="transparency-page-header">

        <div class="transparency-header-left">

            <div class="transparency-page-icon">
                <i class="bi bi-info-circle"></i>
            </div>

            <div>

                <div class="transparency-page-label">
                    INFORMATION BOARD
                </div>

                <h2 class="fw-bold mb-1">
                    Information Board
                </h2>

                <p class="text-muted mb-0">
                    View school administrative and property information.
                </p>

            </div>

        </div>

        <?php if ($isAdmin): ?>

            <div class="transparency-header-actions">

                <a
                    href="information_form.php"
                    class="btn btn-primary"
                >

                    <i class="bi bi-plus-lg me-1"></i>

                    Add Information

                </a>

            </div>

        <?php endif; ?>

    </div>


    <!-- Search & Filter -->
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
                        placeholder="Search information..."
                        aria-label="Search information"
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

                    <?php foreach ($categories as $itemCategory): ?>

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
            <?php if ($isAdmin): ?>

                <div class="transparency-filter">

                    <select
                        name="status"
                        class="form-select"
                    >

                        <option value="">
                            All Statuses
                        </option>

                        <?php foreach ($statuses as $itemStatus): ?>

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

            <?php endif; ?>


            <!-- Search -->
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
                    href="information.php"
                    class="btn btn-outline-secondary transparency-reset-btn"
                >

                    <i class="bi bi-arrow-counterclockwise me-1"></i>

                    Reset

                </a>

            <?php endif; ?>

        </form>

    </div>


    <!-- Record Header -->
    <div class="transparency-record-header">

        <div>

            <h5 class="fw-bold mb-1">
                Information Records
            </h5>

            <p class="text-muted small mb-0">

                <?= count($rows) ?>

                item<?= count($rows) !== 1 ? 's' : '' ?>

                found

            </p>

        </div>

        <div class="transparency-record-icon">

            <i class="bi bi-info-square"></i>

        </div>

    </div>


    <!-- Records -->
    <div class="transparency-records">

        <?php if (!empty($rows)): ?>

            <?php foreach ($rows as $r): ?>

                <?php

                $itemStatus = $r['status'] ?? 'Published';

                $itemIcon = 'bi-file-earmark-text';

                if (!empty($r['file_path'])) {

                    $extension = strtolower(
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

                        $itemIcon = 'bi-file-earmark-image';

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

                    } elseif ($extension === 'pdf') {

                        $itemIcon =
                            'bi-file-earmark-pdf';

                    }

                }

                ?>

                <div class="transparency-item-card">


                    <!-- File Icon -->
                    <div class="transparency-item-icon">

                        <i
                            class="bi <?= e($itemIcon) ?>"
                        ></i>

                    </div>


                    <!-- Content -->
                    <div class="transparency-item-content">


                        <!-- Title & Status -->
                        <div class="transparency-item-top">

                            <div class="transparency-item-title">

                                <?= e(
                                    $r['title']
                                    ??
                                    'Untitled Information'
                                ) ?>

                            </div>


                            <?php if ($itemStatus === 'Published'): ?>

                                <span class="badge bg-success">
                                    Published
                                </span>

                            <?php elseif ($itemStatus === 'Draft'): ?>

                                <span class="badge bg-warning text-dark">
                                    Draft
                                </span>

                            <?php else: ?>

                                <span class="badge bg-secondary">
                                    Archived
                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- Meta -->
                        <div class="transparency-item-meta">

                            <?php if (!empty($r['category'])): ?>

                                <span>

                                    <i class="bi bi-folder2"></i>

                                    <?= e($r['category']) ?>

                                </span>

                            <?php endif; ?>


                            <?php if (!empty($r['school_year'])): ?>

                                <span>

                                    <i class="bi bi-calendar3"></i>

                                    SY <?= e($r['school_year']) ?>

                                </span>

                            <?php endif; ?>


                            <?php if (!empty($r['date_posted'])): ?>

                                <span>

                                    <i class="bi bi-calendar-event"></i>

                                    <?= e($r['date_posted']) ?>

                                </span>

                            <?php endif; ?>


                            <?php if (!empty($r['file_name'])): ?>

                                <span>

                                    <i class="bi bi-paperclip"></i>

                                    Attachment

                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- Description -->
                        <?php if (!empty($r['description'])): ?>

                            <div class="transparency-item-description">

                                <?= nl2br(
                                    e($r['description'])
                                ) ?>

                            </div>

                        <?php else: ?>

                            <div
                                class="transparency-item-description text-muted"
                            >

                                No description provided.

                            </div>

                        <?php endif; ?>


                        <!-- Attachment -->
                        <?php if (!empty($r['file_path'])): ?>

                            <div class="transparency-item-file">

                                <div class="transparency-file-left">

                                    <i class="bi bi-paperclip"></i>

                                    <span>

                                        <?= e(
                                            $r['file_name']
                                            ??
                                            'Attached Document'
                                        ) ?>

                                    </span>

                                </div>


                                <?php if ($isAdmin): ?>

                                    <a
                                        href="<?= e($r['file_path']) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-sm btn-outline-primary"
                                    >

                                        <i class="bi bi-eye me-1"></i>

                                        View Document

                                    </a>

                                <?php else: ?>

                                    <span class="text-muted small">

                                        <i class="bi bi-lock me-1"></i>

                                        Document access restricted

                                    </span>

                                <?php endif; ?>

                            </div>

                        <?php endif; ?>

                    </div>


                    <!-- Actions -->
                    <div class="transparency-item-actions">


                        <!-- View -->
                        <a
                            href="information_view.php?id=<?= (int) $r['id'] ?>"
                            class="btn btn-sm btn-outline-primary"
                            title="View Information"
                        >

                            <i class="bi bi-eye"></i>

                            <span>
                                View
                            </span>

                        </a>


                        <!-- Admin Actions -->
                        <?php if ($isAdmin): ?>

                            <a
                                href="information_form.php?id=<?= (int) $r['id'] ?>"
                                class="btn btn-sm btn-primary"
                                title="Edit Information"
                            >

                                <i class="bi bi-pencil"></i>

                                <span>
                                    Edit
                                </span>

                            </a>


                                <a
                                    href="delete_information.php?id=<?= (int) $r['id'] ?>"
                                    class="btn btn-sm btn-danger"
                                    data-confirm="Delete this information item?"
                                    title="Delete Information"
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


            <!-- Empty State -->
            <div class="transparency-empty-state">

                <div class="transparency-empty-icon">

                    <i class="bi bi-info-square"></i>

                </div>


                <h5 class="fw-bold mb-1">

                    No information records found

                </h5>


                <?php if (
                    $q !== '' ||
                    $category !== '' ||
                    $status !== ''
                ): ?>

                    <p class="text-muted mb-3">

                        No information items matched
                        your current search or filters.

                    </p>


                    <a
                        href="information.php"
                        class="btn btn-outline-primary btn-sm"
                    >

                        <i class="bi bi-arrow-counterclockwise me-1"></i>

                        Reset Filters

                    </a>


                <?php else: ?>


                    <?php if ($isAdmin): ?>

                        <p class="text-muted mb-3">

                            Start by adding your first
                            Information Board item.

                        </p>


                        <a
                            href="information_form.php"
                            class="btn btn-primary btn-sm"
                        >

                            <i class="bi bi-plus-lg me-1"></i>

                            Add Information

                        </a>

                    <?php else: ?>

                        <p class="text-muted mb-0">

                            There are currently no
                            Information Board items available.

                        </p>

                    <?php endif; ?>

                <?php endif; ?>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php include 'footer.php'; ?>
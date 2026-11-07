<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Dashboard';


// Current user's role

$userRole = $_SESSION['role'] ?? '';

$isAdmin = strcasecmp($userRole, 'Administrator') === 0;
$isStaff = strcasecmp($userRole, 'Staff') === 0;
$isTeacher = strcasecmp($userRole, 'Teacher') === 0;


// ==========================================================
// DASHBOARD STATISTICS
// ==========================================================

$students = $pdo
    ->query("SELECT COUNT(*) FROM students")
    ->fetchColumn();

$teachers = $pdo
    ->query("SELECT COUNT(*) FROM teachers")
    ->fetchColumn();

$staff = $pdo
    ->query("SELECT COUNT(*) FROM staff")
    ->fetchColumn();

$docs = $pdo
    ->query("SELECT COUNT(*) FROM documents")
    ->fetchColumn();


// ==========================================================
// INFORMATION BOARD STATISTICS
// ==========================================================

$informationPublished = $pdo
    ->query("
        SELECT COUNT(*)
        FROM information
        WHERE status = 'Published'
    ")
    ->fetchColumn();

$informationDocuments = $pdo
    ->query("
        SELECT COUNT(*)
        FROM information
        WHERE status = 'Published'
        AND (
            (file_name IS NOT NULL AND file_name <> '')
            OR
            (file_path IS NOT NULL AND file_path <> '')
        )
    ")
    ->fetchColumn();


// ==========================================================
// TRANSPARENCY BOARD STATISTICS
// ==========================================================

$transparencyPublished = $pdo
    ->query("
        SELECT COUNT(*)
        FROM transparency
        WHERE status = 'Published'
    ")
    ->fetchColumn();

$transparencyDocuments = $pdo
    ->query("
        SELECT COUNT(*)
        FROM transparency
        WHERE status = 'Published'
        AND (
            (file_name IS NOT NULL AND file_name <> '')
            OR
            (file_path IS NOT NULL AND file_path <> '')
        )
    ")
    ->fetchColumn();


// ==========================================================
// RECENT STUDENTS
// ==========================================================

$recentStudents = $pdo->query("
    SELECT
        s.lrn AS record_id,
        s.full_name,
        s.created_at,
        s.updated_at,

        CASE
            WHEN s.updated_at IS NOT NULL
                 AND s.updated_at <> s.created_at
            THEN s.updated_at
            ELSE s.created_at
        END AS activity_date,

        COALESCE(
            NULLIF(u_updated.full_name, ''),
            NULLIF(u_updated.role, ''),
            NULLIF(u_updated.username, ''),

            NULLIF(u_created.full_name, ''),
            NULLIF(u_created.role, ''),
            NULLIF(u_created.username, ''),

            'Unknown User'
        ) AS action_by

    FROM students s

    LEFT JOIN users u_created
        ON u_created.id = s.created_by

    LEFT JOIN users u_updated
        ON u_updated.id = s.updated_by

    ORDER BY
        COALESCE(s.updated_at, s.created_at) DESC

    LIMIT 5
")->fetchAll();


// ==========================================================
// RECENT TEACHERS
// ==========================================================

$recentTeachers = [];

if ($isAdmin || $isStaff) {

    $recentTeachers = $pdo->query("
        SELECT
            t.employee_id AS record_id,
            t.full_name,
            t.created_at,
            t.updated_at,

            CASE
                WHEN t.updated_at IS NOT NULL
                     AND t.updated_at <> t.created_at
                THEN t.updated_at
                ELSE t.created_at
            END AS activity_date,

            COALESCE(
                NULLIF(u_updated.full_name, ''),
                NULLIF(u_updated.role, ''),
                NULLIF(u_updated.username, ''),

                NULLIF(u_created.full_name, ''),
                NULLIF(u_created.role, ''),
                NULLIF(u_created.username, ''),

                'Unknown User'
            ) AS action_by

        FROM teachers t

        LEFT JOIN users u_created
            ON u_created.id = t.created_by

        LEFT JOIN users u_updated
            ON u_updated.id = t.updated_by

        ORDER BY
            COALESCE(t.updated_at, t.created_at) DESC

        LIMIT 5
    ")->fetchAll();

}


// ==========================================================
// RECENT STAFF
// ==========================================================

$recentStaff = [];

if ($isAdmin) {

    $recentStaff = $pdo->query("
        SELECT
            st.employee_id,
            st.full_name,
            st.created_at,
            st.updated_at,

            CASE
                WHEN st.updated_at IS NOT NULL
                     AND st.updated_at <> st.created_at
                THEN st.updated_at
                ELSE st.created_at
            END AS activity_date,

            COALESCE(
                NULLIF(u_updated.full_name, ''),
                NULLIF(u_updated.role, ''),
                NULLIF(u_updated.username, ''),

                NULLIF(u_created.full_name, ''),
                NULLIF(u_created.role, ''),
                NULLIF(u_created.username, ''),

                'Unknown User'
            ) AS action_by

        FROM staff st

        LEFT JOIN users u_created
            ON u_created.id = st.created_by

        LEFT JOIN users u_updated
            ON u_updated.id = st.updated_by

        ORDER BY
            COALESCE(st.updated_at, st.created_at) DESC

        LIMIT 5
    ")->fetchAll();

}


// ==========================================================
// RECENT INFORMATION BOARD
// ==========================================================

$recentInformation = [];

if ($isAdmin || $isStaff || $isTeacher) {

    $recentInformation = $pdo->query("
        SELECT
            i.id,
            i.title,
            i.category,
            i.description,
            i.school_year,
            i.date_posted,
            i.file_name,
            i.file_path,
            i.file_type,
            i.status,
            i.created_by,
            i.updated_by,
            i.created_at,
            i.updated_at,

            COALESCE(
                i.date_posted,
                DATE(i.updated_at),
                DATE(i.created_at)
            ) AS activity_date,

            COALESCE(
                NULLIF(u_updated.full_name, ''),
                NULLIF(u_updated.role, ''),
                NULLIF(u_updated.username, ''),

                NULLIF(u_created.full_name, ''),
                NULLIF(u_created.role, ''),
                NULLIF(u_created.username, ''),

                'Unknown User'
            ) AS action_by

        FROM information i

        LEFT JOIN users u_created
            ON u_created.id = i.created_by

        LEFT JOIN users u_updated
            ON u_updated.id = i.updated_by

        WHERE i.status = 'Published'

        ORDER BY
            COALESCE(
                i.date_posted,
                DATE(i.updated_at),
                DATE(i.created_at)
            ) DESC

        LIMIT 5
    ")->fetchAll();

}


// ==========================================================
// RECENT TRANSPARENCY BOARD
// ==========================================================

$recentTransparency = [];

if ($isAdmin || $isStaff || $isTeacher) {

    $recentTransparency = $pdo->query("
        SELECT
            t.id,
            t.title,
            t.category,
            t.description,
            t.school_year,
            t.date_posted,
            t.file_name,
            t.file_path,
            t.file_type,
            t.status,
            t.created_by,
            t.updated_by,
            t.created_at,
            t.updated_at,

            COALESCE(
                t.date_posted,
                DATE(t.updated_at),
                DATE(t.created_at)
            ) AS activity_date,

            COALESCE(
                NULLIF(u_updated.full_name, ''),
                NULLIF(u_updated.role, ''),
                NULLIF(u_updated.username, ''),

                NULLIF(u_created.full_name, ''),
                NULLIF(u_created.role, ''),
                NULLIF(u_created.username, ''),

                'Unknown User'
            ) AS action_by

        FROM transparency t

        LEFT JOIN users u_created
            ON u_created.id = t.created_by

        LEFT JOIN users u_updated
            ON u_updated.id = t.updated_by

        WHERE t.status = 'Published'

        ORDER BY
            COALESCE(
                t.date_posted,
                DATE(t.updated_at),
                DATE(t.created_at)
            ) DESC

        LIMIT 5
    ")->fetchAll();

}


// ==========================================================
// LOAD HEADER
// ==========================================================

include 'header.php';

?>

<link rel="stylesheet" href="assets/dashboard.css">

<?php

/*
 * Renders the rows of one "recent records" table.
 * $idKey is the column that holds the record number
 * (record_id for students/teachers, employee_id for staff).
 */
function sdms_record_rows(array $rows, string $idKey, string $emptyText): void
{
    if (!$rows) {
        echo '<tr><td colspan="4" class="sdms-empty">' . e($emptyText) . '</td></tr>';
        return;
    }

    foreach ($rows as $r) {
        $name = (string)($r['full_name'] ?? '');
        $by   = (string)($r['action_by'] ?? 'Unknown User');
        ?>
        <tr>
            <td>
                <div class="sdms-person">
                    <span class="sdms-avatar"><?= e(mb_strtoupper(mb_substr($name !== '' ? $name : 'U', 0, 1))) ?></span>
                    <?= e($name) ?>
                </div>
            </td>
            <td><span class="sdms-pill"><?= e($r[$idKey] ?? '') ?></span></td>
            <td class="sdms-muted"><?= e($r['activity_date'] ?? '') ?></td>
            <td class="sdms-muted"><?= e($by) ?></td>
        </tr>
        <?php
    }
}

/* Renders one board list (Information / Transparency). */
function sdms_board_card(string $title, string $subtitle, string $href, array $items, string $emptyText): void
{
    ?>
    <section class="sdms-card">
        <div class="sdms-card-head">
            <div>
                <h2 class="sdms-card-title"><?= e($title) ?></h2>
                <p class="sdms-muted sdms-small"><?= e($subtitle) ?></p>
            </div>
            <a class="sdms-link" href="<?= e($href) ?>">View all</a>
        </div>

        <?php if (!$items): ?>
            <p class="sdms-empty"><?= e($emptyText) ?></p>
        <?php else: ?>
            <ul class="sdms-feed">
                <?php foreach ($items as $r): ?>
                    <li>
                        <div class="sdms-feed-main">
                            <div class="sdms-feed-title"><?= e($r['title']) ?></div>
                            <div class="sdms-muted sdms-small">
                                <?= e(!empty($r['category']) ? $r['category'] : 'General') ?>
                                &middot; <?= e($r['activity_date']) ?>
                            </div>
                        </div>
                        <span class="sdms-muted sdms-small"><?= e($r['action_by'] ?? 'Unknown User') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php
}

$showTeachers = $isAdmin || $isStaff;
$showStaff    = $isAdmin;
$showBoards   = $isAdmin || $isStaff || $isTeacher;

/*
 * Header button colors. The text color is picked from the button color
 * (white on dark, dark on light) and applied inline, so no theme or
 * navigation stylesheet can turn the label the same color as the button.
 */
$dashBtnBg  = (string)($sdmsGlobalTheme['button_color'] ?? '#0d6efd');
$dashBtnHex = ltrim($dashBtnBg, '#');
if (strlen($dashBtnHex) === 3) {
    $dashBtnHex = preg_replace('/(.)/', '$1$1', $dashBtnHex);
}
$dashBtnFg = '#ffffff';
if (preg_match('/^[0-9a-fA-F]{6}$/', $dashBtnHex)) {
    $dashBtnLuma = (
        0.299 * hexdec(substr($dashBtnHex, 0, 2)) +
        0.587 * hexdec(substr($dashBtnHex, 2, 2)) +
        0.114 * hexdec(substr($dashBtnHex, 4, 2))
    ) / 255;
    $dashBtnFg = $dashBtnLuma > 0.6 ? '#111827' : '#ffffff';
}

?>

<div class="sdms-dashboard">

    <!-- Page header -->
    <header class="sdms-page-head">
        <div>
            <h1 class="sdms-page-title">Dashboard</h1>
            <p class="sdms-muted">
                Welcome back, <?= e($_SESSION['full_name'] ?? 'User') ?>.
            </p>
        </div>
        <a class="sdms-dash-btn" id="recordsAction" href="students.php"
           style="background: <?= e($dashBtnBg) ?> !important; color: <?= e($dashBtnFg) ?> !important; border: 0 !important;">
            <i class="bi bi-mortarboard" id="recordsActionIcon"
               style="color: inherit !important;"></i>
            <span id="recordsActionLabel"
                  style="color: inherit !important;">Manage students</span>
        </a>
    </header>


    <!-- Statistics -->
    <div class="sdms-stats">

        <a class="sdms-card sdms-stat" href="students.php">
            <div>
                <div class="sdms-stat-label">Students</div>
                <div class="sdms-stat-number"><?= e($students) ?></div>
                <div class="sdms-stat-sub">Registered students</div>
            </div>
            <span class="sdms-stat-icon"><i class="bi bi-mortarboard"></i></span>
        </a>

        <?php if ($showTeachers): ?>
            <a class="sdms-card sdms-stat" href="teachers.php">
                <div>
                    <div class="sdms-stat-label">Teachers</div>
                    <div class="sdms-stat-number"><?= e($teachers) ?></div>
                    <div class="sdms-stat-sub">Registered teachers</div>
                </div>
                <span class="sdms-stat-icon"><i class="bi bi-person-workspace"></i></span>
            </a>
        <?php endif; ?>

        <?php if ($showStaff): ?>
            <a class="sdms-card sdms-stat" href="staff.php">
                <div>
                    <div class="sdms-stat-label">Staff</div>
                    <div class="sdms-stat-number"><?= e($staff) ?></div>
                    <div class="sdms-stat-sub">Registered staff</div>
                </div>
                <span class="sdms-stat-icon"><i class="bi bi-person-badge"></i></span>
            </a>

            <div class="sdms-card sdms-stat">
                <div>
                    <div class="sdms-stat-label">Documents</div>
                    <div class="sdms-stat-number"><?= e($docs) ?></div>
                    <div class="sdms-stat-sub">Stored documents</div>
                </div>
                <span class="sdms-stat-icon"><i class="bi bi-file-earmark-text"></i></span>
            </div>
        <?php endif; ?>

    </div>


    <!-- Recently added records (tabbed) -->
    <section class="sdms-card">
        <div class="sdms-card-head">
            <div>
                <h2 class="sdms-card-title">Recently added records</h2>
                <p class="sdms-muted sdms-small">Latest activity across people records</p>
            </div>

            <ul class="nav sdms-tabs" id="recordTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-students" data-bs-toggle="pill"
                            data-href="students.php" data-label="Manage students" data-icon="bi-mortarboard"
                            data-bs-target="#pane-students" type="button" role="tab"
                            aria-controls="pane-students" aria-selected="true">Students</button>
                </li>
                <?php if ($showTeachers): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-teachers" data-bs-toggle="pill"
                                data-href="teachers.php" data-label="Manage teachers" data-icon="bi-person-workspace"
                                data-bs-target="#pane-teachers" type="button" role="tab"
                                aria-controls="pane-teachers" aria-selected="false">Teachers</button>
                    </li>
                <?php endif; ?>
                <?php if ($showStaff): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-staff" data-bs-toggle="pill"
                                data-href="staff.php" data-label="Manage staff" data-icon="bi-person-badge"
                                data-bs-target="#pane-staff" type="button" role="tab"
                                aria-controls="pane-staff" aria-selected="false">Staff</button>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="tab-content">

            <div class="tab-pane fade show active" id="pane-students" role="tabpanel" aria-labelledby="tab-students" tabindex="0">
                <div class="table-responsive">
                    <table class="table sdms-table">
                        <thead><tr><th>Name</th><th>LRN</th><th>Date</th><th>Created / Updated by</th></tr></thead>
                        <tbody><?php sdms_record_rows($recentStudents, 'record_id', 'No student records yet.'); ?></tbody>
                    </table>
                </div>
            </div>

            <?php if ($showTeachers): ?>
                <div class="tab-pane fade" id="pane-teachers" role="tabpanel" aria-labelledby="tab-teachers" tabindex="0">
                    <div class="table-responsive">
                        <table class="table sdms-table">
                            <thead><tr><th>Name</th><th>Employee No.</th><th>Date</th><th>Created / Updated by</th></tr></thead>
                            <tbody><?php sdms_record_rows($recentTeachers, 'record_id', 'No teacher records yet.'); ?></tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($showStaff): ?>
                <div class="tab-pane fade" id="pane-staff" role="tabpanel" aria-labelledby="tab-staff" tabindex="0">
                    <div class="table-responsive">
                        <table class="table sdms-table">
                            <thead><tr><th>Name</th><th>Employee ID</th><th>Date</th><th>Created / Updated by</th></tr></thead>
                            <tbody><?php sdms_record_rows($recentStaff, 'employee_id', 'No staff records yet.'); ?></tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </section>


    <!-- Boards -->
    <?php if ($showBoards): ?>
        <div class="sdms-two">
            <?php
            sdms_board_card(
                'Information Board',
                $informationPublished . ' published · ' . $informationDocuments . ' with documents',
                'information.php',
                $recentInformation,
                'No published information yet.'
            );

            sdms_board_card(
                'Transparency Board',
                $transparencyPublished . ' published · ' . $transparencyDocuments . ' with documents',
                'transparency.php',
                $recentTransparency,
                'No published transparency records yet.'
            );
            ?>
        </div>
    <?php endif; ?>

</div>

<script>
/* The header button follows whichever records tab is active. */
document.addEventListener('DOMContentLoaded', function () {
    var action = document.getElementById('recordsAction');
    var label  = document.getElementById('recordsActionLabel');
    var icon   = document.getElementById('recordsActionIcon');

    if (!action || !label || !icon) { return; }

    document.querySelectorAll('#recordTabs [data-bs-toggle="pill"]').forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function () {
            action.setAttribute('href', tab.dataset.href);
            label.textContent = tab.dataset.label;
            icon.className = 'bi ' + tab.dataset.icon;
        });
    });
});
</script>

<?php include 'footer.php'; ?>
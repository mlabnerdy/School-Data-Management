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

<link
    rel="stylesheet"
    href="assets/dashboard.css"
>


<!-- Dashboard welcome -->

<div class="mb-4">

    <h2 class="fw-bold mb-1">
        Dashboard
    </h2>

    <p class="text-muted mb-0">
        Welcome back,
        <?= e($_SESSION['full_name'] ?? 'User') ?>.
    </p>

</div>


<!-- ==========================================================
     DASHBOARD STATISTICS
=========================================================== -->

<div class="dashboard-statistics mb-4">


    <!-- Main statistics -->

    <div class="row g-4 justify-content-center">


        <!-- Students -->

        <div class="col-12 col-sm-6 col-lg-3">

            <a
                href="students.php"
                class="dashboard-stat-link"
            >

                <div class="dashboard-stat-card">

                    <div class="stat-content">

                        <span class="stat-label">
                            Students
                        </span>

                        <span class="stat-number">
                            <?= e($students) ?>
                        </span>

                        <span class="stat-description">
                            Registered students
                        </span>

                    </div>

                    <div class="stat-icon stat-icon-blue">

                        <i class="bi bi-mortarboard-fill"></i>

                    </div>

                </div>

            </a>

        </div>


        <!-- Teachers -->

        <?php if ($isAdmin || $isStaff): ?>

            <div class="col-12 col-sm-6 col-lg-3">

                <a
                    href="teachers.php"
                    class="dashboard-stat-link"
                >

                    <div class="dashboard-stat-card">

                        <div class="stat-content">

                            <span class="stat-label">
                                Teachers
                            </span>

                            <span class="stat-number">
                                <?= e($teachers) ?>
                            </span>

                            <span class="stat-description">
                                Registered teachers
                            </span>

                        </div>

                        <div class="stat-icon stat-icon-blue">

                            <i class="bi bi-person-workspace"></i>

                        </div>

                    </div>

                </a>

            </div>

        <?php endif; ?>


        <!-- Staff -->

        <?php if ($isAdmin): ?>

            <div class="col-12 col-sm-6 col-lg-3">

                <a
                    href="staff.php"
                    class="dashboard-stat-link"
                >

                    <div class="dashboard-stat-card">

                        <div class="stat-content">

                            <span class="stat-label">
                                Staff
                            </span>

                            <span class="stat-number">
                                <?= e($staff) ?>
                            </span>

                            <span class="stat-description">
                                Registered staff
                            </span>

                        </div>

                        <div class="stat-icon stat-icon-blue">

                            <i class="bi bi-people-fill"></i>

                        </div>

                    </div>

                </a>

            </div>

        <?php endif; ?>


        <!-- Documents -->

        <?php if ($isAdmin): ?>

            <div class="col-12 col-sm-6 col-lg-3">

                <div class="dashboard-stat-card">

                    <div class="stat-content">

                        <span class="stat-label">
                            Documents
                        </span>

                        <span class="stat-number">
                            <?= e($docs) ?>
                        </span>

                        <span class="stat-description">
                            Stored documents
                        </span>

                    </div>

                    <div class="stat-icon stat-icon-blue">

                        <i class="bi bi-file-earmark-text-fill"></i>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>


    <!-- Information and Transparency -->

    <?php if ($isAdmin || $isStaff || $isTeacher): ?>

        <div class="row g-4 justify-content-center mt-1">


            <!-- Information Board -->

            <div class="col-12 col-sm-6 col-lg-4">

                <a
                    href="information.php"
                    class="dashboard-stat-link"
                >

                    <div class="dashboard-stat-card">

                        <div class="stat-content">

                            <span class="stat-label">
                                Information Board
                            </span>

                            <span class="stat-number stat-number-small">

                                <?= e($informationPublished) ?>

                                <span class="stat-inline-label">
                                    Published
                                </span>

                            </span>

                            <span class="stat-description">

                                <?= e($informationDocuments) ?>

                                Document<?= $informationDocuments == 1 ? '' : 's' ?>

                            </span>

                        </div>

                        <div class="stat-icon stat-icon-blue">

                            <i class="bi bi-info-circle-fill"></i>

                        </div>

                    </div>

                </a>

            </div>


            <!-- Transparency Board -->

            <div class="col-12 col-sm-6 col-lg-4">

                <a
                    href="transparency.php"
                    class="dashboard-stat-link"
                >

                    <div class="dashboard-stat-card">

                        <div class="stat-content">

                            <span class="stat-label">
                                Transparency Board
                            </span>

                            <span class="stat-number stat-number-small">

                                <?= e($transparencyPublished) ?>

                                <span class="stat-inline-label">
                                    Published
                                </span>

                            </span>

                            <span class="stat-description">

                                <?= e($transparencyDocuments) ?>

                                Document<?= $transparencyDocuments == 1 ? '' : 's' ?>

                            </span>

                        </div>

                        <div class="stat-icon stat-icon-blue">

                            <i class="bi bi-megaphone-fill"></i>

                        </div>

                    </div>

                </a>

            </div>


        </div>

    <?php endif; ?>

</div>


<!-- ==========================================================
     RECENT STUDENTS
=========================================================== -->

<div class="dashboard-record-card mb-4">

    <div class="records-header">

        <div>

            <h5 class="records-title">
                Recent Students
            </h5>

            <p class="records-subtitle">
                Latest student records
            </p>

        </div>

        <div class="records-icon">

            <i class="bi bi-mortarboard"></i>

        </div>

    </div>


    <div class="table-responsive">

        <table class="table dashboard-table">

            <thead>

                <tr>

                    <th>
                        LRN
                    </th>

                    <th>
                        Name
                    </th>

                    <th>
                        Date Added
                    </th>

                    <th>
                        Created / Updated By
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php foreach ($recentStudents as $r): ?>

                    <tr>

                        <td>

                            <span class="record-id">
                                <?= e($r['record_id']) ?>
                            </span>

                        </td>


                        <td>

                            <div class="record-name">

                                <div class="record-avatar">

                                    <?= e(
                                        strtoupper(
                                            substr(
                                                $r['full_name'] ?? 'U',
                                                0,
                                                1
                                            )
                                        )
                                    ) ?>

                                </div>

                                <?= e($r['full_name']) ?>

                            </div>

                        </td>


                        <td>

                            <span class="record-date">

                                <i class="bi bi-calendar3"></i>

                                <?= e($r['activity_date']) ?>

                            </span>

                        </td>


                        <td>

                            <div class="record-name">

                                <div class="record-avatar">

                                    <?= e(
                                        strtoupper(
                                            substr(
                                                $r['action_by'] ?? 'U',
                                                0,
                                                1
                                            )
                                        )
                                    ) ?>

                                </div>

                                <?= e(
                                    $r['action_by']
                                    ?? 'Unknown User'
                                ) ?>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>


                <?php if (!$recentStudents): ?>

                    <tr>

                        <td
                            colspan="4"
                            class="text-center text-muted py-4"
                        >

                            No student records yet.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- ==========================================================
     RECENT TEACHERS
=========================================================== -->

<?php if ($isAdmin || $isStaff): ?>

    <div class="dashboard-record-card mb-4">

        <div class="records-header">

            <div>

                <h5 class="records-title">
                    Recent Teachers
                </h5>

                <p class="records-subtitle">
                    Latest teacher records
                </p>

            </div>

            <div class="records-icon">

                <i class="bi bi-person-workspace"></i>

            </div>

        </div>


        <div class="table-responsive">

            <table class="table dashboard-table">

                <thead>

                    <tr>

                        <th>
                            Employee No.
                        </th>

                        <th>
                            Name
                        </th>

                        <th>
                            Date Added
                        </th>

                        <th>
                            Created / Updated By
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($recentTeachers as $r): ?>

                        <tr>

                            <td>

                                <span class="record-id">
                                    <?= e($r['record_id']) ?>
                                </span>

                            </td>


                            <td>

                                <div class="record-name">

                                    <div class="record-avatar">

                                        <?= e(
                                            strtoupper(
                                                substr(
                                                    $r['full_name'] ?? 'U',
                                                    0,
                                                    1
                                                )
                                            )
                                        ) ?>

                                    </div>

                                    <?= e($r['full_name']) ?>

                                </div>

                            </td>


                            <td>

                                <span class="record-date">

                                    <i class="bi bi-calendar3"></i>

                                    <?= e($r['activity_date']) ?>

                                </span>

                            </td>


                            <td>

                                <div class="record-name">

                                    <div class="record-avatar">

                                        <?= e(
                                            strtoupper(
                                                substr(
                                                    $r['action_by'] ?? 'U',
                                                    0,
                                                    1
                                                )
                                            )
                                        ) ?>

                                    </div>

                                    <?= e(
                                        $r['action_by']
                                        ?? 'Unknown User'
                                    ) ?>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                    <?php if (!$recentTeachers): ?>

                        <tr>

                            <td
                                colspan="4"
                                class="text-center text-muted py-4"
                            >

                                No teacher records yet.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

<?php endif; ?>


<!-- ==========================================================
     RECENT STAFF
=========================================================== -->

<?php if ($isAdmin): ?>

    <div class="dashboard-record-card mb-4">

        <div class="records-header">

            <div>

                <h5 class="records-title">
                    Recent Staff
                </h5>

                <p class="records-subtitle">
                    Latest staff records
                </p>

            </div>

            <div class="records-icon">

                <i class="bi bi-people-fill"></i>

            </div>

        </div>


        <div class="table-responsive">

            <table class="table dashboard-table">

                <thead>

                    <tr>

                        <th>
                            Employee ID
                        </th>

                        <th>
                            Name
                        </th>

                        <th>
                            Date Added
                        </th>

                        <th>
                            Created / Updated By
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($recentStaff as $r): ?>

                    <tr>

                        <td>

                            <span class="record-id">
                                <?= e($r['employee_id']) ?>
                            </span>

                        </td>


                        <td>

                            <div class="record-name">

                                <div class="record-avatar">

                                    <?= e(
                                        strtoupper(
                                            substr(
                                                $r['full_name'] ?? 'U',
                                                0,
                                                1
                                            )
                                        )
                                    ) ?>

                                </div>

                                <?= e($r['full_name']) ?>

                            </div>

                        </td>


                        <td>

                            <span class="record-date">

                                <i class="bi bi-calendar3"></i>

                                <?= e($r['activity_date']) ?>

                            </span>

                        </td>


                        <td>

                            <div class="record-name">

                                <div class="record-avatar">

                                    <?= e(
                                        strtoupper(
                                            substr(
                                                $r['action_by'] ?? 'U',
                                                0,
                                                1
                                            )
                                        )
                                    ) ?>

                                </div>

                                <?= e(
                                    $r['action_by']
                                    ?? 'Unknown User'
                                ) ?>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>


                <?php if (!$recentStaff): ?>

                    <tr>

                        <td
                            colspan="4"
                            class="text-center text-muted py-4"
                        >

                            No staff records yet.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

<?php endif; ?>


<!-- ==========================================================
     RECENT INFORMATION BOARD
=========================================================== -->

<?php if ($isAdmin || $isStaff || $isTeacher): ?>

    <div class="dashboard-record-card mb-4">

        <div class="records-header">

            <div>

                <h5 class="records-title">
                    Recent Information Board
                </h5>

                <p class="records-subtitle">
                    Latest published information
                </p>

            </div>

            <div class="records-icon">

                <i class="bi bi-info-circle"></i>

            </div>

        </div>


        <div class="table-responsive">

            <table class="table dashboard-table">

                <thead>

                    <tr>

                        <th>
                            Title
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Date Posted
                        </th>

                        <th>
                            Published By
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($recentInformation as $r): ?>

                        <tr>

                            <td>

                                <div class="record-name">

                                    <div class="record-avatar">

                                        <i class="bi bi-info-circle"></i>

                                    </div>

                                    <?= e($r['title']) ?>

                                </div>

                            </td>


                            <td>

                                <?php if (!empty($r['category'])): ?>

                                    <span class="record-id">
                                        <?= e($r['category']) ?>
                                    </span>

                                <?php else: ?>

                                    <span class="text-muted">
                                        General
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <span class="record-date">

                                    <i class="bi bi-calendar3"></i>

                                    <?= e($r['activity_date']) ?>

                                </span>

                            </td>


                            <td>

                                <div class="record-name">

                                    <div class="record-avatar">

                                        <?= e(
                                            strtoupper(
                                                substr(
                                                    $r['action_by'] ?? 'U',
                                                    0,
                                                    1
                                                )
                                            )
                                        ) ?>

                                    </div>

                                    <?= e(
                                        $r['action_by']
                                        ?? 'Unknown User'
                                    ) ?>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                    <?php if (!$recentInformation): ?>

                        <tr>

                            <td
                                colspan="4"
                                class="text-center text-muted py-4"
                            >

                                <i
                                    class="bi bi-info-circle fs-3 d-block mb-2"
                                ></i>

                                No published information yet.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

<?php endif; ?>


<!-- ==========================================================
     RECENT TRANSPARENCY BOARD
=========================================================== -->

<?php if ($isAdmin || $isStaff || $isTeacher): ?>

    <div class="dashboard-record-card">

        <div class="records-header">

            <div>

                <h5 class="records-title">
                    Recent Transparency Board
                </h5>

                <p class="records-subtitle">
                    Latest published transparency records
                </p>

            </div>

            <div class="records-icon">

                <i class="bi bi-megaphone"></i>

            </div>

        </div>


        <div class="table-responsive">

            <table class="table dashboard-table">

                <thead>

                    <tr>

                        <th>
                            Title
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Date Posted
                        </th>

                        <th>
                            Published By
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($recentTransparency as $r): ?>

                        <tr>

                            <td>

                                <div class="record-name">

                                    <div class="record-avatar">

                                        <i class="bi bi-megaphone"></i>

                                    </div>

                                    <?= e($r['title']) ?>

                                </div>

                            </td>


                            <td>

                                <?php if (!empty($r['category'])): ?>

                                    <span class="record-id">
                                        <?= e($r['category']) ?>
                                    </span>

                                <?php else: ?>

                                    <span class="text-muted">
                                        General
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <span class="record-date">

                                    <i class="bi bi-calendar3"></i>

                                    <?= e($r['activity_date']) ?>

                                </span>

                            </td>


                            <td>

                                <div class="record-name">

                                    <div class="record-avatar">

                                        <?= e(
                                            strtoupper(
                                                substr(
                                                    $r['action_by'] ?? 'U',
                                                    0,
                                                    1
                                                )
                                            )
                                        ) ?>

                                    </div>

                                    <?= e(
                                        $r['action_by']
                                        ?? 'Unknown User'
                                    ) ?>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                    <?php if (!$recentTransparency): ?>

                        <tr>

                            <td
                                colspan="4"
                                class="text-center text-muted py-4"
                            >

                                <i
                                    class="bi bi-megaphone fs-3 d-block mb-2"
                                ></i>

                                No published transparency records yet.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

<?php endif; ?>


<?php include 'footer.php'; ?>
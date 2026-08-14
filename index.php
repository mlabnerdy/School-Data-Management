<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Dashboard';

$userRole = $_SESSION['role'] ?? '';

$isAdmin = ($userRole === 'Administrator');
$isStaff = ($userRole === 'Staff');
$isTeacher = ($userRole === 'Teacher');


/* Statistics */

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


/* Recent Students */

$recentStudents = $pdo->query("
    SELECT
        lrn AS record_id,
        full_name,
        created_at
    FROM students
    ORDER BY created_at DESC
    LIMIT 8
")->fetchAll();


/* Recent Teachers */

$recentTeachers = [];

if ($isAdmin || $isStaff) {

    $recentTeachers = $pdo->query("
        SELECT
            employee_id AS record_id,
            full_name,
            created_at
        FROM teachers
        ORDER BY created_at DESC
        LIMIT 8
    ")->fetchAll();

}


include 'header.php';

?>

<link
    rel="stylesheet"
    href="assets/dashboard.css"
>


<!-- Dashboard Header -->

<div class="mb-4">

    <h2 class="fw-bold mb-1">
        Dashboard
    </h2>

    <p class="text-muted mb-0">

        Welcome back,
        <?= e($_SESSION['full_name'] ?? 'User') ?>.

    </p>

</div>


<!-- Statistics -->

<div class="dashboard-stats row g-4 mb-4">


    <!-- Students -->

    <div class="col-12 col-sm-6 col-xl-3">

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

        <div class="col-12 col-sm-6 col-xl-3">

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

        <div class="col-12 col-sm-6 col-xl-3">

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

        <div class="col-12 col-sm-6 col-xl-3">

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


<!-- Recent Students -->

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

                                    <?= strtoupper(
                                        substr(
                                            $r['full_name'],
                                            0,
                                            1
                                        )
                                    ) ?>

                                </div>

                                <?= e($r['full_name']) ?>

                            </div>

                        </td>


                        <td>

                            <span class="record-date">

                                <i class="bi bi-calendar3"></i>

                                <?= e($r['created_at']) ?>

                            </span>

                        </td>

                    </tr>

                <?php endforeach; ?>


                <?php if (!$recentStudents): ?>

                    <tr>

                        <td
                            colspan="3"
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


<!-- Recent Teachers -->

<?php if ($isAdmin || $isStaff): ?>

    <div class="dashboard-record-card">

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

                                        <?= strtoupper(
                                            substr(
                                                $r['full_name'],
                                                0,
                                                1
                                            )
                                        ) ?>

                                    </div>

                                    <?= e($r['full_name']) ?>

                                </div>

                            </td>


                            <td>

                                <span class="record-date">

                                    <i class="bi bi-calendar3"></i>

                                    <?= e($r['created_at']) ?>

                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                    <?php if (!$recentTeachers): ?>

                        <tr>

                            <td
                                colspan="3"
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


<?php include 'footer.php'; ?>
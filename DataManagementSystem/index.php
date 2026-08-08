<?php
require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Dashboard';

$students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$teachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$staff = $pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();
$docs = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();

$recent = $pdo->query("
    SELECT student_id AS record_id, full_name, 'Student' AS type, created_at
    FROM students

    UNION ALL

    SELECT employee_id AS record_id, full_name, 'Teacher' AS type, created_at
    FROM teachers

    UNION ALL

    SELECT employee_id AS record_id, full_name, 'Staff' AS type, created_at
    FROM staff

    ORDER BY created_at DESC
    LIMIT 8
")->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Dashboard</h2>
        <p class="text-muted mb-0">
            Overview of stored school records.
        </p>
    </div>
</div>

<div class="row g-3 mb-4">

    <!-- Students -->
    <div class="col-md-6 col-xl-3">
        <a href="students.php" class="text-decoration-none text-dark">
            <div class="card stat-card p-4">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted">Students</div>
                        <div class="display-6 fw-bold">
                            <?= $students ?>
                        </div>
                    </div>

                    <i class="bi bi-mortarboard fs-2 text-success"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Teachers -->
    <div class="col-md-6 col-xl-3">
        <a href="teachers.php" class="text-decoration-none text-dark">
            <div class="card stat-card p-4">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted">Teachers</div>
                        <div class="display-6 fw-bold">
                            <?= $teachers ?>
                        </div>
                    </div>

                    <i class="bi bi-person-workspace fs-2 text-success"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Staff -->
    <div class="col-md-6 col-xl-3">
        <a href="staff.php" class="text-decoration-none text-dark">
            <div class="card stat-card p-4">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted">Staff</div>
                        <div class="display-6 fw-bold">
                            <?= $staff ?>
                        </div>
                    </div>

                    <i class="bi bi-people fs-2 text-success"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Documents -->
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card p-4">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-muted">Documents</div>
                    <div class="display-6 fw-bold">
                        <?= $docs ?>
                    </div>
                </div>

                <i class="bi bi-file-earmark-text fs-2 text-success"></i>
            </div>
        </div>
    </div>

</div>

<!-- Recent Records -->
<div class="card p-4">

    <h5 class="fw-bold mb-3">
        Recently Added Records
    </h5>

    <div class="table-responsive">

        <table class="table table-hover">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Date Added</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($recent as $r): ?>

                    <tr>

                        <td>
                            <?= e($r['record_id']) ?>
                        </td>

                        <td>
                            <?= e($r['full_name']) ?>
                        </td>

                        <td>
                            <span class="badge text-bg-light">
                                <?= e($r['type']) ?>
                            </span>
                        </td>

                        <td>
                            <?= e($r['created_at']) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (!$recent): ?>

                    <tr>
                        <td colspan="4"
                            class="text-center text-muted py-4">
                            No records yet.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php include 'footer.php'; ?>
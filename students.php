<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Student List';

$q = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM students";
$params = [];

if ($q !== '') {
    $sql .= " WHERE student_id LIKE ? OR full_name LIKE ?";
    $params = [
        "%$q%",
        "%$q%"
    ];
}

$sql .= " ORDER BY full_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll();

include 'header.php';
?>


<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

    <div>
        <h2 class="fw-bold mb-1">Student List</h2>

        <p class="text-muted mb-0">
            Search, view, and edit stored student records.
        </p>
    </div>

    <a href="student_form.php" class="btn btn-success">
        <i class="bi bi-plus-lg"></i>
        Add Student
    </a>

</div>


<!-- Student Table Card -->
<div class="card p-4">


    <!-- Search -->
    <form method="GET" class="row g-2 mb-4">

        <div class="col-md-6">

            <input
                type="text"
                name="q"
                class="form-control"
                value="<?= e($q) ?>"
                placeholder="Search by Student ID or name"
            >

        </div>


        <div class="col-auto">

            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-search"></i>
                Search
            </button>

        </div>


        <?php if ($q !== ''): ?>

            <div class="col-auto">

                <a href="students.php" class="btn btn-outline-danger">
                    <i class="bi bi-x-lg"></i>
                    Clear
                </a>

            </div>

        <?php endif; ?>

    </form>


    <!-- Student Table -->
    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>

                <tr>
                    <th>Photo</th>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Contact</th>
                    <th>Grade / Section</th>
                    <th class="text-end">Actions</th>
                </tr>

            </thead>


            <tbody>

                <?php if (!empty($rows)): ?>

                    <?php foreach ($rows as $r): ?>

                        <tr>

                            <!-- Photo -->
                            <td>

                                <?php if (!empty($r['photo'])): ?>

                                    <img
                                        src="<?= e($r['photo']) ?>"
                                        alt="Student Photo"
                                        class="avatar"
                                    >

                                <?php else: ?>

                                    <div class="avatar bg-light d-flex align-items-center justify-content-center">

                                        <i class="bi bi-person text-muted"></i>

                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- Student ID -->
                            <td>
                                <?= e($r['student_id']) ?>
                            </td>


                            <!-- Full Name -->
                            <td>
                                <strong>
                                    <?= e($r['full_name']) ?>
                                </strong>
                            </td>


                            <!-- Contact -->
                            <td>
                                <?= !empty($r['contact_number'])
                                    ? e($r['contact_number'])
                                    : '<span class="text-muted">—</span>' ?>
                            </td>


                            <!-- Grade / Section -->
                            <td>
                                <?= !empty($r['grade_section'])
                                    ? e($r['grade_section'])
                                    : '<span class="text-muted">—</span>' ?>
                            </td>


                            <!-- Actions -->
                            <td class="text-end">

                                <a
                                    href="student_view.php?id=<?= (int)$r['id'] ?>"
                                    class="btn btn-sm btn-outline-success"
                                >
                                    <i class="bi bi-eye"></i>
                                    View
                                </a>

                                <a
                                    href="student_form.php?id=<?= (int)$r['id'] ?>"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    <i class="bi bi-pencil"></i>
                                    Edit
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <!-- No Records -->
                    <tr>

                        <td
                            colspan="6"
                            class="text-center text-muted py-5"
                        >

                            <i class="bi bi-mortarboard fs-2 d-block mb-2"></i>

                            No student records found.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<?php include 'footer.php'; ?>
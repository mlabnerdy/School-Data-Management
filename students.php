<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Students';

/*
|--------------------------------------------------------------------------
| User Role
|--------------------------------------------------------------------------
*/

$role = $_SESSION['role'] ?? '';

$isAdmin = strcasecmp($role, 'Administrator') === 0;


/*
|--------------------------------------------------------------------------
| Search Filters
|--------------------------------------------------------------------------
*/

$q = trim($_GET['q'] ?? '');

$schoolYear = trim($_GET['school_year'] ?? '');


/*
|--------------------------------------------------------------------------
| Load Active School Years
|--------------------------------------------------------------------------
*/

$schoolYearStmt = $pdo->query("
    SELECT school_year
    FROM school_years
    WHERE is_active = 1
    ORDER BY school_year DESC
");

$schoolYears = $schoolYearStmt->fetchAll(PDO::FETCH_COLUMN);


/*
|--------------------------------------------------------------------------
| Student Query
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM students";

$where = [];
$params = [];


/*
|--------------------------------------------------------------------------
| Search by LRN or Name
|--------------------------------------------------------------------------
*/

if ($q !== '') {

    $where[] = "(lrn LIKE ? OR full_name LIKE ?)";

    $params[] = "%$q%";
    $params[] = "%$q%";
}


/*
|--------------------------------------------------------------------------
| Filter by School Year
|--------------------------------------------------------------------------
*/

if ($schoolYear !== '') {

    $where[] = "school_year = ?";

    $params[] = $schoolYear;
}


/*
|--------------------------------------------------------------------------
| Apply WHERE Conditions
|--------------------------------------------------------------------------
*/

if (!empty($where)) {

    $sql .= " WHERE " . implode(" AND ", $where);

}


/*
|--------------------------------------------------------------------------
| Sort
|--------------------------------------------------------------------------
*/

$sql .= " ORDER BY full_name ASC";


/*
|--------------------------------------------------------------------------
| Execute Student Query
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll();


include 'header.php';
?>

<link rel="stylesheet" href="assets/students.css">


<!-- Page Header -->

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            Student List
        </h2>

        <p class="text-muted mb-0">
            Search, view, and manage student information.
        </p>

    </div>


    <div class="d-flex flex-wrap gap-2">

        <?php if ($isAdmin): ?>

            <!-- Manage School Years -->

            <a
                href="school_years.php"
                class="btn btn-outline-primary"
            >
                <i class="bi bi-calendar3 me-1"></i>
                Manage School Years
            </a>

        <?php endif; ?>


        <!-- Add Student -->

        <a
            href="student_form.php"
            class="btn btn-primary students-add-btn"
        >
            <i class="bi bi-plus-lg me-1"></i>
            Add Student
        </a>

    </div>

</div>


<!-- Search -->

<form
    method="GET"
    class="students-search-form row g-2 mb-4"



    <!-- Search by LRN / Name -->

    <div class="col-12 col-md students-search-col">

        <div class="students-search-input">

            <i class="bi bi-search"></i>

            <input
                type="text"
                name="q"
                class="form-control"
                value="<?= e($q) ?>"
                placeholder="Search by Student LRN or name"
                aria-label="Search students"
            >

        </div>

    </div>


    <!-- School Year -->

    <div class="col-12 col-md-auto">

        <select
            name="school_year"
            class="form-select"
            aria-label="Filter by School Year"
        >

            <option value="">
                All School Years
            </option>


            <?php foreach ($schoolYears as $year): ?>

                <option
                    value="<?= e($year) ?>"
                    <?= $schoolYear === $year ? 'selected' : '' ?>
                >
                    <?= e($year) ?>
                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <!-- Search Button -->

    <div class="col-12 col-md-auto">

        <button
            type="submit"
            class="btn btn-primary students-search-btn"
        >
            <i class="bi bi-search me-1"></i>
            Search
        </button>

    </div>


    <!-- Clear Button -->

    <?php if ($q !== '' || $schoolYear !== ''): ?>

        <div class="col-12 col-md-auto">

            <a
                href="students.php"
                class="btn btn-outline-secondary students-clear-btn"
            >
                <i class="bi bi-x-lg me-1"></i>
                Clear
            </a>

        </div>

    <?php endif; ?>


</form>


<!-- Student Records -->

<div class="students-table-header">

    <div>

        <h5 class="fw-bold mb-1">
            Student Records
        </h5>

        <p class="text-muted small mb-0">

            <?= count($rows) ?>
            student<?= count($rows) !== 1 ? 's' : '' ?>
            found

        </p>

    </div>


    <div class="students-table-icon">

        <i class="bi bi-people"></i>

    </div>

</div>


<div class="table-responsive">

    <table class="table students-table align-middle mb-0">


        <thead>

            <tr>

                <th>
                    Student
                </th>

                <th>
                    Student ID
                </th>

                <th>
                    School Year
                </th>

                <th>
                    Contact
                </th>

                <th>
                    Grade / Section
                </th>

                <th class="text-end">
                    Actions
                </th>

            </tr>

        </thead>


        <tbody>


            <?php if (!empty($rows)): ?>


                <?php foreach ($rows as $r): ?>


                    <tr>


                        <!-- Student -->

                        <td>

                            <div class="d-flex align-items-center gap-3">


                                <?php if (!empty($r['photo'])): ?>

                                    <img
                                        src="<?= e($r['photo']) ?>"
                                        alt="<?= e($r['full_name']) ?>"
                                        class="students-avatar"
                                    >

                                <?php else: ?>

                                    <div class="students-avatar students-avatar-placeholder">

                                        <i class="bi bi-person"></i>

                                    </div>

                                <?php endif; ?>


                                <div>

                                    <div class="fw-semibold">

                                        <?= e($r['full_name']) ?>

                                    </div>

                                    <small class="text-muted">

                                        Student

                                    </small>

                                </div>

                            </div>

                        </td>


                        <!-- Student ID -->

                        <td>

                            <span class="student-id-badge">

                                <?= e($r['lrn']) ?>

                            </span>

                        </td>


                        <!-- School Year -->

                        <td>

                            <?php if (!empty($r['school_year'])): ?>

                                <span class="grade-badge">

                                    <?= e($r['school_year']) ?>

                                </span>

                            <?php else: ?>

                                <span class="text-muted">
                                    —
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- Contact -->

                        <td>

                            <?php if (!empty($r['contact_number'])): ?>

                                <div class="student-contact">

                                    <i class="bi bi-telephone me-1"></i>

                                    <?= e($r['contact_number']) ?>

                                </div>

                            <?php else: ?>

                                <span class="text-muted">
                                    —
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- Grade / Section -->

                        <td>

                            <?php if (!empty($r['grade_section'])): ?>

                                <span class="grade-badge">

                                    <?= e($r['grade_section']) ?>

                                </span>

                            <?php else: ?>

                                <span class="text-muted">
                                    —
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- Actions -->

                        <td class="text-end">

                            <div class="student-actions">


                                <!-- View -->

                                <a
                                    href="student_view.php?id=<?= (int)$r['id'] ?>"
                                    class="btn btn-sm btn-outline-primary"
                                    title="View Student"
                                >

                                    <i class="bi bi-eye"></i>

                                    <span>
                                        View
                                    </span>

                                </a>


                                <!-- Edit -->

                                <a
                                    href="student_form.php?id=<?= (int)$r['id'] ?>"
                                    class="btn btn-sm btn-primary"
                                    title="Edit Student"
                                >

                                    <i class="bi bi-pencil"></i>

                                    <span>
                                        Edit
                                    </span>

                                </a>


                                <!-- Delete -->

                                <a
                                    href="delete_student.php?id=<?= (int)$r['id'] ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Delete Student"
                                    onclick="return confirm('Are you sure you want to delete this student record?');"
                                >

                                    <i class="bi bi-trash"></i>

                                    <span>
                                        Delete
                                    </span>

                                </a>


                            </div>

                        </td>


                    </tr>


                <?php endforeach; ?>


            <?php else: ?>


                <!-- Empty State -->

                <tr>

                    <td colspan="6">


                        <div class="students-empty-state">


                            <div class="students-empty-icon">

                                <i class="bi bi-mortarboard"></i>

                            </div>


                            <h5 class="fw-bold mb-1">

                                No student records found

                            </h5>


                            <?php if ($q !== '' || $schoolYear !== ''): ?>


                                <p class="text-muted mb-3">

                                    No students matched

                                    <?php if ($q !== ''): ?>

                                        "<strong><?= e($q) ?></strong>"

                                    <?php endif; ?>


                                    <?php if ($q !== '' && $schoolYear !== ''): ?>

                                        and

                                    <?php endif; ?>


                                    <?php if ($schoolYear !== ''): ?>

                                        School Year
                                        "<strong><?= e($schoolYear) ?></strong>"

                                    <?php endif; ?>.

                                </p>


                                <a
                                    href="students.php"
                                    class="btn btn-outline-primary btn-sm"
                                >

                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                    Clear Search

                                </a>


                            <?php else: ?>


                                <p class="text-muted mb-3">

                                    Start by adding your first student record.

                                </p>


                                <a
                                    href="student_form.php"
                                    class="btn btn-primary btn-sm"
                                >

                                    <i class="bi bi-plus-lg me-1"></i>
                                    Add Student

                                </a>


                            <?php endif; ?>


                        </div>


                    </td>

                </tr>


            <?php endif; ?>


        </tbody>


    </table>

</div>


<?php include 'footer.php'; ?>
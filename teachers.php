<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Teachers';


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

$q = trim($_GET['q'] ?? '');

$searchType = $_GET['search_type'] ?? 'all';

$allowedSearchTypes = [
    'all',
    'id',
    'name',
    'position'
];

if (!in_array($searchType, $allowedSearchTypes, true)) {
    $searchType = 'all';
}


/*
|--------------------------------------------------------------------------
| Build Query
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM teachers";

$params = [];


if ($q !== '') {

    switch ($searchType) {

        case 'id':

            $sql .= " WHERE employee_id LIKE ?";

            $params[] = "%{$q}%";

            break;


        case 'name':

            $sql .= " WHERE full_name LIKE ?";

            $params[] = "%{$q}%";

            break;


        case 'position':

            $sql .= " WHERE position_department LIKE ?";

            $params[] = "%{$q}%";

            break;


        case 'all':
        default:

            $sql .= "
                WHERE employee_id LIKE ?
                OR full_name LIKE ?
                OR position_department LIKE ?
            ";

            $params = [
                "%{$q}%",
                "%{$q}%",
                "%{$q}%"
            ];

            break;
    }
}


$sql .= " ORDER BY full_name ASC";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$rows = $stmt->fetchAll();


include 'header.php';

?>

<link rel="stylesheet" href="assets/teachers.css">


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            Teacher List
        </h2>

        <p class="text-muted mb-0">
            Search, view, and manage teacher information.
        </p>

    </div>


    <a
        href="teacher_form.php"
        class="btn btn-primary teachers-add-btn"
    >

        <i class="bi bi-plus-lg me-1"></i>

        Add Teacher

    </a>

</div>


<!-- =========================================================
     SEARCH
========================================================= -->

<form
    method="GET"
    class="teachers-search-form mb-4"
>

    <div class="row g-2 align-items-center">


        <!-- Search Type -->

        <div class="col-12 col-md-3 col-lg-3">

            <select
                name="search_type"
                class="form-select"
                aria-label="Search by"
            >

                <option
                    value="all"
                    <?= $searchType === 'all'
                        ? 'selected'
                        : '' ?>
                >
                    Search All
                </option>

                <option
                    value="id"
                    <?= $searchType === 'id'
                        ? 'selected'
                        : '' ?>
                >
                    Employee ID
                </option>

                <option
                    value="name"
                    <?= $searchType === 'name'
                        ? 'selected'
                        : '' ?>
                >
                    Name
                </option>

                <option
                    value="position"
                    <?= $searchType === 'position'
                        ? 'selected'
                        : '' ?>
                >
                    Position
                </option>

            </select>

        </div>


        <!-- Search Input -->

        <div class="col-12 col-md-6 col-lg-6">

            <div class="teachers-search-input">

                <i class="bi bi-search"></i>

                <input
                    type="text"
                    name="q"
                    class="form-control"
                    value="<?= e($q) ?>"
                    placeholder="Enter search keyword..."
                    aria-label="Search teachers"
                >

            </div>

        </div>


        <!-- Search -->

        <div class="col-6 col-md-1 col-lg-1">

            <button
                type="submit"
                class="btn btn-primary w-100 teachers-search-btn"
                title="Search"
            >

                <i class="bi bi-search"></i>

                <span class="d-none d-lg-inline ms-1">
                    Search
                </span>

            </button>

        </div>


        <!-- Reset -->

        <div class="col-6 col-md-2 col-lg-2">

            <a
                href="teachers.php"
                class="btn btn-outline-secondary w-100 teachers-clear-btn"
            >

                <i class="bi bi-arrow-counterclockwise me-1"></i>

                Reset

            </a>

        </div>

    </div>

</form>


<!-- =========================================================
     SEARCH STATUS
========================================================= -->

<?php if ($q !== ''): ?>

    <div class="alert alert-light border mb-4">

        <i class="bi bi-funnel me-1"></i>

        Searching
        <strong>
            <?= e($q) ?>
        </strong>

        in

        <strong>

            <?php

            switch ($searchType) {

                case 'id':
                    echo 'Employee ID';
                    break;

                case 'name':
                    echo 'Name';
                    break;

                case 'position':
                    echo 'Position';
                    break;

                default:
                    echo 'All Fields';
                    break;
            }

            ?>

        </strong>

        —

        <?= count($rows) ?>

        result<?= count($rows) !== 1 ? 's' : '' ?>

    </div>

<?php endif; ?>


<!-- =========================================================
     TEACHER RECORDS HEADER
========================================================= -->

<div class="teachers-table-header">

    <div>

        <h5 class="fw-bold mb-1">
            Teacher Records
        </h5>

        <p class="text-muted small mb-0">

            <?= count($rows) ?>

            teacher<?= count($rows) !== 1
                ? 's'
                : ''
            ?>

            found

        </p>

    </div>


    <div class="teachers-table-icon">

        <i class="bi bi-person-workspace"></i>

    </div>

</div>


<!-- =========================================================
     TEACHER TABLE
========================================================= -->

<div class="table-responsive">

    <table class="table teachers-table align-middle mb-0">

        <thead>

            <tr>

                <th>Teacher</th>

                <th>Employee ID</th>

                <th>Contact</th>

                <th>Position / Department</th>

                <th>Plantilla No.</th>

                <th class="text-end">
                    Actions
                </th>

            </tr>

        </thead>


        <tbody>


        <?php if (!empty($rows)): ?>


            <?php foreach ($rows as $r): ?>

                <tr>


                    <!-- Teacher -->

                    <td>

                        <div class="d-flex align-items-center gap-3">

                            <?php if (!empty($r['photo'])): ?>

                                <img
                                    src="<?= e($r['photo']) ?>"
                                    alt="<?= e($r['full_name']) ?>"
                                    class="teachers-avatar"
                                >

                            <?php else: ?>

                                <div class="teachers-avatar teachers-avatar-placeholder">

                                    <i class="bi bi-person"></i>

                                </div>

                            <?php endif; ?>


                            <div>

                                <div class="fw-semibold">

                                    <?= e(
                                        $r['full_name']
                                    ) ?>

                                </div>

                                <small class="text-muted">
                                    Teacher
                                </small>

                            </div>

                        </div>

                    </td>


                    <!-- Employee ID -->

                    <td>

                        <?php if (
                            !empty(
                                $r['employee_id']
                            )
                        ): ?>

                            <span class="teacher-id-badge">

                                <?= e(
                                    $r['employee_id']
                                ) ?>

                            </span>

                        <?php else: ?>

                            <span class="text-muted">
                                —
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- Contact -->

                    <td>

                        <?php if (
                            !empty(
                                $r['contact_number']
                            )
                        ): ?>

                            <div class="teacher-contact">

                                <i class="bi bi-telephone me-1"></i>

                                <?= e(
                                    $r['contact_number']
                                ) ?>

                            </div>

                        <?php else: ?>

                            <span class="text-muted">
                                —
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- Position -->

                    <td>

                        <?php if (
                            !empty(
                                $r[
                                    'position_department'
                                ]
                            )
                        ): ?>

                            <span class="position-badge">

                                <?= e(
                                    $r[
                                        'position_department'
                                    ]
                                ) ?>

                            </span>

                        <?php else: ?>

                            <span class="text-muted">
                                —
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- Plantilla -->

                    <td>

                        <?php if (
                            !empty(
                                $r['plantilla_no']
                            )
                        ): ?>

                            <span class="plantilla-badge">

                                <?= e(
                                    $r['plantilla_no']
                                ) ?>

                            </span>

                        <?php else: ?>

                            <span class="text-muted">
                                —
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- Actions -->

                    <td class="text-end">

                        <div class="teacher-actions">


                            <!-- View -->

                            <a
                                href="teacher_view.php?id=<?= (int)$r['id'] ?>"
                                class="btn btn-sm btn-outline-primary"
                                title="View Teacher"
                            >

                                <i class="bi bi-eye"></i>

                                <span>
                                    View
                                </span>

                            </a>


                            <!-- Edit -->

                            <a
                                href="teacher_form.php?id=<?= (int)$r['id'] ?>"
                                class="btn btn-sm btn-primary"
                                title="Edit Teacher"
                            >

                                <i class="bi bi-pencil"></i>

                                <span>
                                    Edit
                                </span>

                            </a>


                            <!-- Delete -->

                            <a
                                href="delete_teacher.php?id=<?= (int)$r['id'] ?>"
                                class="btn btn-sm btn-danger"
                                data-confirm="Delete this teacher record?"
                            >

                                <i class="bi bi-trash me-1"></i>

                                Delete

                            </a>


                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>


        <?php else: ?>


            <!-- =================================================
                 EMPTY STATE
            ================================================== -->

            <tr>

                <td colspan="6">

                    <div class="teachers-empty-state">

                        <div class="teachers-empty-icon">

                            <i class="bi bi-person-workspace"></i>

                        </div>


                        <h5 class="fw-bold mb-1">

                            No teacher records found

                        </h5>


                        <?php if ($q !== ''): ?>

                            <p class="text-muted mb-3">

                                No teachers matched

                                "<strong>
                                    <?= e($q) ?>
                                </strong>".

                            </p>


                            <a
                                href="teachers.php"
                                class="btn btn-outline-primary btn-sm"
                            >

                                <i class="bi bi-arrow-counterclockwise me-1"></i>

                                Reset Search

                            </a>


                        <?php else: ?>

                            <p class="text-muted mb-3">

                                Start by adding your first
                                teacher record.

                            </p>


                            <a
                                href="teacher_form.php"
                                class="btn btn-primary btn-sm"
                            >

                                <i class="bi bi-plus-lg me-1"></i>

                                Add Teacher

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
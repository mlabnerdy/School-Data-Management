<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'School Year Management';

/*
|--------------------------------------------------------------------------
| Administrator Access
|--------------------------------------------------------------------------
*/

$role = $_SESSION['role'] ?? '';

$isAdmin = strcasecmp($role, 'Administrator') === 0;

if (!$isAdmin) {

    http_response_code(403);

    exit('Administrator access required.');

}


/*
|--------------------------------------------------------------------------
| Handle Actions
|--------------------------------------------------------------------------
*/

$message = '';
$error = '';

$action = $_POST['action'] ?? '';

/*
|--------------------------------------------------------------------------
| Add School Year
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {

    $schoolYear = trim($_POST['school_year'] ?? '');

    /*
    | Validate format: YYYY-YYYY
    */

    if (!preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {

        $error = 'Please enter a valid school year, for example 2026-2027.';

    } else {

        /*
        | Validate consecutive years
        */

        $parts = explode('-', $schoolYear);

        $startYear = (int)$parts[0];
        $endYear   = (int)$parts[1];

        if ($endYear !== $startYear + 1) {

            $error = 'The school year must contain consecutive years, for example 2026-2027.';

        } else {

            /*
            | Check duplicate
            */

            $stmt = $pdo->prepare("
                SELECT id
                FROM school_years
                WHERE school_year = ?
                LIMIT 1
            ");

            $stmt->execute([$schoolYear]);

            if ($stmt->fetch()) {

                $error = 'That school year already exists.';

            } else {

                /*
                | Insert
                */

                $stmt = $pdo->prepare("
                    INSERT INTO school_years
                        (school_year, is_active)
                    VALUES
                        (?, 1)
                ");

                $stmt->execute([$schoolYear]);

                $message = 'School year added successfully.';

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| Edit School Year
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {

    $id = (int)($_POST['id'] ?? 0);

    $schoolYear = trim($_POST['school_year'] ?? '');

    if ($id <= 0) {

        $error = 'Invalid school year record.';

    } elseif (!preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {

        $error = 'Please enter a valid school year, for example 2026-2027.';

    } else {

        $parts = explode('-', $schoolYear);

        $startYear = (int)$parts[0];
        $endYear   = (int)$parts[1];

        if ($endYear !== $startYear + 1) {

            $error = 'The school year must contain consecutive years, for example 2026-2027.';

        } else {

            /*
            | Check duplicate excluding current record
            */

            $stmt = $pdo->prepare("
                SELECT id
                FROM school_years
                WHERE school_year = ?
                AND id != ?
                LIMIT 1
            ");

            $stmt->execute([
                $schoolYear,
                $id
            ]);

            if ($stmt->fetch()) {

                $error = 'That school year already exists.';

            } else {

                $stmt = $pdo->prepare("
                    UPDATE school_years
                    SET school_year = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $schoolYear,
                    $id
                ]);

                $message = 'School year updated successfully.';

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| Activate / Deactivate School Year
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle') {

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {

        $error = 'Invalid school year record.';

    } else {

        $stmt = $pdo->prepare("
            SELECT id, school_year, is_active
            FROM school_years
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $year = $stmt->fetch();

        if (!$year) {

            $error = 'School year not found.';

        } else {

            $newStatus = ((int)$year['is_active'] === 1) ? 0 : 1;

            $stmt = $pdo->prepare("
                UPDATE school_years
                SET is_active = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $newStatus,
                $id
            ]);

            if ($newStatus === 1) {

                $message = $year['school_year'] . ' has been activated.';

            } else {

                $message = $year['school_year'] . ' has been deactivated.';

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| Load School Years
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        school_year,
        is_active,
        created_at
    FROM school_years
    ORDER BY school_year DESC
");

$schoolYears = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Counts
|--------------------------------------------------------------------------
*/

$totalYears = count($schoolYears);

$activeYears = 0;
$inactiveYears = 0;

foreach ($schoolYears as $year) {

    if ((int)$year['is_active'] === 1) {

        $activeYears++;

    } else {

        $inactiveYears++;

    }

}


include 'header.php';

?>

<div class="container-fluid">


    <!-- Page Header -->

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                School Year Management
            </h2>

            <p class="text-muted mb-0">
                Add, edit, and manage school years used by the Student system.
            </p>

        </div>


        <a
            href="students.php"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Back to Students
        </a>

    </div>


    <!-- Messages -->

    <?php if ($message !== ''): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-check-circle me-1"></i>

            <?= e($message) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

    <?php endif; ?>


    <?php if ($error !== ''): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-exclamation-triangle me-1"></i>

            <?= e($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

    <?php endif; ?>


    <!-- Summary Cards -->

    <div class="row g-3 mb-4">


        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between">

                        <div>

                            <div class="text-muted small">
                                Total School Years
                            </div>

                            <div class="fs-3 fw-bold">
                                <?= $totalYears ?>
                            </div>

                        </div>

                        <div class="text-primary fs-2">
                            <i class="bi bi-calendar3"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between">

                        <div>

                            <div class="text-muted small">
                                Active School Years
                            </div>

                            <div class="fs-3 fw-bold text-success">
                                <?= $activeYears ?>
                            </div>

                        </div>

                        <div class="text-success fs-2">
                            <i class="bi bi-check-circle"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between">

                        <div>

                            <div class="text-muted small">
                                Inactive School Years
                            </div>

                            <div class="fs-3 fw-bold text-secondary">
                                <?= $inactiveYears ?>
                            </div>

                        </div>

                        <div class="text-secondary fs-2">
                            <i class="bi bi-dash-circle"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


    </div>


    <div class="row g-4">


        <!-- Add School Year -->

        <div class="col-12 col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <h5 class="fw-bold mb-1">
                        Add School Year
                    </h5>

                    <p class="text-muted small mb-4">
                        Add a new school year to the Student system.
                    </p>


                    <form method="POST">

                        <input
                            type="hidden"
                            name="action"
                            value="add"
                        >


                        <div class="mb-3">

                            <label
                                for="school_year"
                                class="form-label fw-semibold"
                            >
                                School Year
                            </label>

                            <input
                                type="text"
                                id="school_year"
                                name="school_year"
                                class="form-control"
                                placeholder="2026-2027"
                                maxlength="9"
                                pattern="[0-9]{4}-[0-9]{4}"
                                inputmode="numeric"
                                required
                            >

                            <div class="form-text">
                                Format: YYYY-YYYY
                            </div>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i class="bi bi-plus-lg me-1"></i>

                            Add School Year

                        </button>

                    </form>

                </div>

            </div>

        </div>


        <!-- School Year List -->

        <div class="col-12 col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-body">


                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <h5 class="fw-bold mb-1">
                                School Years
                            </h5>

                            <p class="text-muted small mb-0">
                                Active years appear in the Student filters.
                            </p>

                        </div>

                        <i class="bi bi-calendar-range fs-3 text-primary"></i>

                    </div>


                    <div class="table-responsive">

                        <table class="table align-middle mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        School Year
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Created
                                    </th>

                                    <th class="text-end">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php if (!empty($schoolYears)): ?>


                                    <?php foreach ($schoolYears as $year): ?>


                                        <tr>


                                            <!-- School Year -->

                                            <td>

                                                <span class="fw-semibold">

                                                    <?= e($year['school_year']) ?>

                                                </span>

                                            </td>


                                            <!-- Status -->

                                            <td>

                                                <?php if ((int)$year['is_active'] === 1): ?>

                                                    <span class="badge text-bg-success">

                                                        Active

                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge text-bg-secondary">

                                                        Inactive

                                                    </span>

                                                <?php endif; ?>

                                            </td>


                                            <!-- Created -->

                                            <td>

                                                <small class="text-muted">

                                                    <?= e(
                                                        date(
                                                            'M d, Y',
                                                            strtotime($year['created_at'])
                                                        )
                                                    ) ?>

                                                </small>

                                            </td>


                                            <!-- Actions -->

                                            <td class="text-end">

                                                <div class="d-flex justify-content-end gap-2">


                                                    <!-- Edit -->

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editSchoolYearModal"
                                                        data-id="<?= (int)$year['id'] ?>"
                                                        data-school-year="<?= e($year['school_year']) ?>"
                                                    >

                                                        <i class="bi bi-pencil"></i>

                                                        <span class="d-none d-md-inline">
                                                            Edit
                                                        </span>

                                                    </button>


                                                    <!-- Activate / Deactivate -->

                                                    <form
                                                        method="POST"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to <?= (int)$year['is_active'] === 1 ? 'deactivate' : 'activate' ?> <?= e($year['school_year']) ?>?');"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="toggle"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?= (int)$year['id'] ?>"
                                                        >


                                                        <?php if ((int)$year['is_active'] === 1): ?>

                                                            <button
                                                                type="submit"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Deactivate"
                                                            >

                                                                <i class="bi bi-pause-circle"></i>

                                                                <span class="d-none d-md-inline">
                                                                    Disable
                                                                </span>

                                                            </button>

                                                        <?php else: ?>

                                                            <button
                                                                type="submit"
                                                                class="btn btn-sm btn-outline-success"
                                                                title="Activate"
                                                            >

                                                                <i class="bi bi-play-circle"></i>

                                                                <span class="d-none d-md-inline">
                                                                    Enable
                                                                </span>

                                                            </button>

                                                        <?php endif; ?>


                                                    </form>


                                                </div>

                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                <?php else: ?>


                                    <tr>

                                        <td
                                            colspan="4"
                                            class="text-center py-5"
                                        >

                                            <div class="text-muted">

                                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>

                                                <div class="fw-semibold">
                                                    No school years found.
                                                </div>

                                                <small>
                                                    Add a school year using the form.
                                                </small>

                                            </div>

                                        </td>

                                    </tr>


                                <?php endif; ?>


                            </tbody>

                        </table>

                    </div>


                </div>

            </div>

        </div>


    </div>

</div>


<!-- Edit School Year Modal -->

<div
    class="modal fade"
    id="editSchoolYearModal"
    tabindex="-1"
    aria-labelledby="editSchoolYearModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">


            <div class="modal-header">

                <h5
                    class="modal-title fw-bold"
                    id="editSchoolYearModalLabel"
                >
                    Edit School Year
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <form method="POST">

                <div class="modal-body">

                    <input
                        type="hidden"
                        name="action"
                        value="edit"
                    >

                    <input
                        type="hidden"
                        name="id"
                        id="edit_school_year_id"
                        value=""
                    >


                    <div class="mb-3">

                        <label
                            for="edit_school_year"
                            class="form-label fw-semibold"
                        >
                            School Year
                        </label>

                        <input
                            type="text"
                            id="edit_school_year"
                            name="school_year"
                            class="form-control"
                            maxlength="9"
                            pattern="[0-9]{4}-[0-9]{4}"
                            inputmode="numeric"
                            required
                        >

                        <div class="form-text">
                            Format: YYYY-YYYY
                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-check-lg me-1"></i>

                        Save Changes

                    </button>

                </div>

            </form>


        </div>

    </div>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const editModal = document.getElementById('editSchoolYearModal');

    if (!editModal) {
        return;
    }

    editModal.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget;

        if (!button) {
            return;
        }

        const id = button.getAttribute('data-id');

        const schoolYear = button.getAttribute('data-school-year');


        const idInput =
            document.getElementById('edit_school_year_id');

        const schoolYearInput =
            document.getElementById('edit_school_year');


        if (idInput) {

            idInput.value = id;

        }


        if (schoolYearInput) {

            schoolYearInput.value = schoolYear;

        }

    });

});

</script>


<?php include 'footer.php'; ?>
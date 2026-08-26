<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Staff';


// ==========================================================
// SEARCH
// ==========================================================

$q = trim($_GET['q'] ?? '');

$searchBy = $_GET['search_by'] ?? 'all';

$allowedSearchTypes = [
    'all',
    'id',
    'name',
    'position'
];

if (!in_array($searchBy, $allowedSearchTypes, true)) {
    $searchBy = 'all';
}


// ==========================================================
// GET STAFF RECORDS
// ==========================================================

$sql = "
    SELECT *
    FROM staff
";

$params = [];


// ==========================================================
// APPLY SEARCH
// ==========================================================

if ($q !== '') {

    $searchValue = "%{$q}%";


    switch ($searchBy) {


        // --------------------------------------------------
        // ID
        // --------------------------------------------------

        case 'id':

            $sql .= "
                WHERE employee_id LIKE ?
            ";

            $params[] = $searchValue;

            break;


        // --------------------------------------------------
        // NAME
        // --------------------------------------------------

        case 'name':

            $sql .= "
                WHERE full_name LIKE ?
            ";

            $params[] = $searchValue;

            break;


        // --------------------------------------------------
        // POSITION
        // --------------------------------------------------

        case 'position':

            $sql .= "
                WHERE position_department LIKE ?
            ";

            $params[] = $searchValue;

            break;


        // --------------------------------------------------
        // ALL
        // --------------------------------------------------

        case 'all':
        default:

            $sql .= "
                WHERE
                    employee_id LIKE ?
                    OR full_name LIKE ?
                    OR position_department LIKE ?
            ";

            $params = [
                $searchValue,
                $searchValue,
                $searchValue
            ];

            break;

    }

}


// ==========================================================
// SORT
// ==========================================================

$sql .= "
    ORDER BY full_name ASC
";


// ==========================================================
// EXECUTE
// ==========================================================

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$rows = $stmt->fetchAll();


include 'header.php';

?>

<link
    rel="stylesheet"
    href="assets/staff.css"
>


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            Staff List
        </h2>

        <p class="text-muted mb-0">
            Search, view, and manage staff information.
        </p>

    </div>


    <a
        href="staff_form.php"
        class="btn btn-primary staff-add-btn"
    >

        <i class="bi bi-plus-lg me-1"></i>

        Add Staff

    </a>

</div>


<!-- =========================================================
     SEARCH BAR
========================================================= -->

<form
    method="GET"
    class="staff-search-form mb-4"
>


    <div class="row g-2 align-items-end">


        <!-- =====================================================
             SEARCH INPUT
        ====================================================== -->

        <div class="col-12 col-lg">

            <label
                for="q"
                class="form-label fw-semibold"
            >
                Search Staff
            </label>


            <div class="staff-search-input">

                <i class="bi bi-search"></i>

                <input
                    type="text"
                    name="q"
                    id="q"
                    class="form-control"
                    value="<?= e($q) ?>"
                    placeholder="Search staff..."
                    maxlength="150"
                    autocomplete="off"
                >

            </div>

        </div>


        <!-- =====================================================
             SEARCH BY
        ====================================================== -->

        <div class="col-12 col-sm-6 col-lg-auto">

            <label
                for="search_by"
                class="form-label fw-semibold"
            >
                Search by
            </label>


            <select
                name="search_by"
                id="search_by"
                class="form-select"
            >

                <option
                    value="all"
                    <?= $searchBy === 'all'
                        ? 'selected'
                        : '' ?>
                >
                    All
                </option>

                <option
                    value="id"
                    <?= $searchBy === 'id'
                        ? 'selected'
                        : '' ?>
                >
                    ID
                </option>

                <option
                    value="name"
                    <?= $searchBy === 'name'
                        ? 'selected'
                        : '' ?>
                >
                    Name
                </option>

                <option
                    value="position"
                    <?= $searchBy === 'position'
                        ? 'selected'
                        : '' ?>
                >
                    Position
                </option>

            </select>

        </div>


        <!-- =====================================================
             SEARCH BUTTON
        ====================================================== -->

        <div class="col-12 col-sm-3 col-lg-auto">

            <button
                type="submit"
                class="btn btn-primary w-100 staff-search-btn"
            >

                <i class="bi bi-search me-1"></i>

                Search

            </button>

        </div>


        <!-- =====================================================
             RESET BUTTON
        ====================================================== -->

        <div class="col-12 col-sm-3 col-lg-auto">

            <a
                href="staff.php"
                class="btn btn-outline-secondary w-100 staff-clear-btn"
            >

                <i class="bi bi-arrow-counterclockwise me-1"></i>

                Reset

            </a>

        </div>


    </div>

</form>


<!-- =========================================================
     ACTIVE SEARCH
========================================================= -->

<?php if ($q !== ''): ?>

    <div class="mb-3">

        <small class="text-muted">

            Searching for

            <strong>
                "<?= e($q) ?>"
            </strong>

            by

            <strong>

                <?php

                switch ($searchBy) {

                    case 'id':
                        echo 'ID';
                        break;

                    case 'name':
                        echo 'Name';
                        break;

                    case 'position':
                        echo 'Position';
                        break;

                    default:
                        echo 'All';
                        break;

                }

                ?>

            </strong>

        </small>

    </div>

<?php endif; ?>


<!-- =========================================================
     STAFF RECORDS HEADER
========================================================= -->

<div class="staff-table-header">

    <div>

        <h5 class="fw-bold mb-1">
            Staff Records
        </h5>


        <p class="text-muted small mb-0">

            <?= count($rows) ?>

            staff

            <?= count($rows) !== 1
                ? 'members'
                : 'member'
            ?>

            found

        </p>

    </div>


    <div class="staff-table-icon">

        <i class="bi bi-person-badge"></i>

    </div>

</div>


<!-- =========================================================
     STAFF TABLE
========================================================= -->

<div class="table-responsive">

    <table class="table staff-table align-middle mb-0">

        <thead>

            <tr>

                <th>
                    Staff
                </th>

                <th>
                    Employee ID
                </th>

                <th>
                    Contact
                </th>

                <th>
                    Position / Department
                </th>

                <th>
                    Plantilla No.
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


                        <!-- =================================================
                             STAFF
                        ================================================== -->

                        <td>

                            <div class="d-flex align-items-center gap-3">


                                <?php if (!empty($r['photo'])): ?>

                                    <img
                                        src="<?= e($r['photo']) ?>"
                                        alt="<?= e(
                                            $r['full_name']
                                        ) ?>"
                                        class="staff-avatar"
                                    >

                                <?php else: ?>

                                    <div
                                        class="
                                            staff-avatar
                                            staff-avatar-placeholder
                                        "
                                    >

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

                                        Staff

                                    </small>

                                </div>

                            </div>

                        </td>


                        <!-- =================================================
                             EMPLOYEE ID
                        ================================================== -->

                        <td>

                            <span class="staff-id-badge">

                                <?= e(
                                    $r['employee_id']
                                ) ?>

                            </span>

                        </td>


                        <!-- =================================================
                             CONTACT
                        ================================================== -->

                        <td>


                            <?php if (
                                !empty(
                                    $r['contact_number']
                                )
                            ): ?>

                                <div class="staff-contact">

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


                        <!-- =================================================
                             POSITION
                        ================================================== -->

                        <td>


                            <?php if (
                                !empty(
                                    $r['position_department']
                                )
                            ): ?>

                                <span class="staff-position-badge">

                                    <?= e(
                                        $r['position_department']
                                    ) ?>

                                </span>

                            <?php else: ?>

                                <span class="text-muted">
                                    —
                                </span>

                            <?php endif; ?>


                        </td>


                        <!-- =================================================
                             PLANTILLA
                        ================================================== -->

                        <td>


                            <?php if (
                                !empty(
                                    $r['plantilla_no']
                                )
                            ): ?>

                                <span class="staff-plantilla-badge">

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


                        <!-- =================================================
                             ACTIONS
                        ================================================== -->

                        <td class="text-end">

                            <div class="staff-actions">


                                <!-- View -->

                                <a
                                    href="staff_view.php?id=<?= (int)$r['id'] ?>"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-primary
                                    "
                                    title="View Staff"
                                >

                                    <i class="bi bi-eye"></i>

                                    <span>
                                        View
                                    </span>

                                </a>


                                <!-- Edit -->

                                <a
                                    href="staff_form.php?id=<?= (int)$r['id'] ?>"
                                    class="
                                        btn
                                        btn-sm
                                        btn-primary
                                    "
                                    title="Edit Staff"
                                >

                                    <i class="bi bi-pencil"></i>

                                    <span>
                                        Edit
                                    </span>

                                </a>


                                <!-- Delete -->

                                <a
                                    href="delete_staff.php?id=<?= (int)$r['id'] ?>"
                                    class="btn btn-sm btn-danger"
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


                        <div class="staff-empty-state">


                            <div class="staff-empty-icon">

                                <i class="bi bi-person-badge"></i>

                            </div>


                            <h5 class="fw-bold mb-1">

                                No staff records found

                            </h5>


                            <?php if ($q !== ''): ?>


                                <p class="text-muted mb-3">

                                    No staff members matched

                                    <strong>
                                        "<?= e($q) ?>"
                                    </strong>

                                    in

                                    <strong>

                                        <?php

                                        switch ($searchBy) {

                                            case 'id':
                                                echo 'ID';
                                                break;

                                            case 'name':
                                                echo 'Name';
                                                break;

                                            case 'position':
                                                echo 'Position';
                                                break;

                                            default:
                                                echo 'All fields';
                                                break;

                                        }

                                        ?>

                                    </strong>.

                                </p>


                                <a
                                    href="staff.php"
                                    class="
                                        btn
                                        btn-outline-primary
                                        btn-sm
                                    "
                                >

                                    <i
                                        class="
                                            bi
                                            bi-arrow-counterclockwise
                                            me-1
                                        "
                                    ></i>

                                    Reset Search

                                </a>


                            <?php else: ?>


                                <p class="text-muted mb-3">

                                    Start by adding your first
                                    staff record.

                                </p>


                                <a
                                    href="staff_form.php"
                                    class="btn btn-primary btn-sm"
                                >

                                    <i class="bi bi-plus-lg me-1"></i>

                                    Add Staff

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
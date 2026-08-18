<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Staff';

$q = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM staff";
$params = [];

if ($q !== '') {
    $sql .= " WHERE employee_id LIKE ? OR full_name LIKE ?";
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

<link rel="stylesheet" href="assets/staff.css">


<!-- Page Header -->
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


<!-- Search -->
<form method="GET" class="staff-search-form row g-2 mb-4">

    <div class="col-12 col-md-auto staff-search-col">

        <div class="staff-search-input">

            <i class="bi bi-search"></i>

            <input
                type="text"
                name="q"
                class="form-control"
                value="<?= e($q) ?>"
                placeholder="Search by Employee ID or name"
                aria-label="Search staff"
            >

        </div>

    </div>


    <div class="col-12 col-md-auto">

        <button
            type="submit"
            class="btn btn-primary staff-search-btn"
        >
            <i class="bi bi-search me-1"></i>
            Search
        </button>

    </div>


    <?php if ($q !== ''): ?>

        <div class="col-12 col-md-auto">

            <a
                href="staff.php"
                class="btn btn-outline-secondary staff-clear-btn"
            >
                <i class="bi bi-x-lg me-1"></i>
                Clear
            </a>

        </div>

    <?php endif; ?>

</form>


<!-- Staff Records Header -->
<div class="staff-table-header">

    <div>

        <h5 class="fw-bold mb-1">
            Staff Records
        </h5>

        <p class="text-muted small mb-0">

            <?= count($rows) ?>
            staff<?= count($rows) !== 1 ? ' members' : ' member' ?>
            found

        </p>

    </div>


    <div class="staff-table-icon">

        <i class="bi bi-person-badge"></i>

    </div>

</div>


<!-- Staff Table -->
<div class="table-responsive">

    <table class="table staff-table align-middle mb-0">

        <thead>

            <tr>

                <th>Staff</th>

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

                        <!-- Staff -->
                        <td>

                            <div class="d-flex align-items-center gap-3">

                                <?php if (!empty($r['photo'])): ?>

                                    <img
                                        src="<?= e($r['photo']) ?>"
                                        alt="<?= e($r['full_name']) ?>"
                                        class="staff-avatar"
                                    >

                                <?php else: ?>

                                    <div class="staff-avatar staff-avatar-placeholder">

                                        <i class="bi bi-person"></i>

                                    </div>

                                <?php endif; ?>


                                <div>

                                    <div class="fw-semibold">

                                        <?= e($r['full_name']) ?>

                                    </div>

                                    <small class="text-muted">
                                        Staff
                                    </small>

                                </div>

                            </div>

                        </td>


                        <!-- Employee ID -->
                        <td>

                            <span class="staff-id-badge">

                                <?= e($r['employee_id']) ?>

                            </span>

                        </td>


                        <!-- Contact -->
                        <td>

                            <?php if (!empty($r['contact_number'])): ?>

                                <div class="staff-contact">

                                    <i class="bi bi-telephone me-1"></i>

                                    <?= e($r['contact_number']) ?>

                                </div>

                            <?php else: ?>

                                <span class="text-muted">
                                    —
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- Position / Department -->
                        <td>

                            <?php if (!empty($r['position_department'])): ?>

                                <span class="staff-position-badge">

                                    <?= e($r['position_department']) ?>

                                </span>

                            <?php else: ?>

                                <span class="text-muted">
                                    —
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- Plantilla No. -->
                        <td>

                            <?php if (!empty($r['plantilla_no'])): ?>

                                <span class="staff-plantilla-badge">

                                    <?= e($r['plantilla_no']) ?>

                                </span>

                            <?php else: ?>

                                <span class="text-muted">
                                    —
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- Actions -->
                        <td class="text-end">

                            <div class="staff-actions">

                                <!-- View -->
                                <a
                                    href="staff_view.php?id=<?= (int)$r['id'] ?>"
                                    class="btn btn-sm btn-outline-primary"
                                    title="View Staff"
                                >
                                    <i class="bi bi-eye"></i>
                                    <span>View</span>
                                </a>


                                <!-- Edit -->
                                <a
                                    href="staff_form.php?id=<?= (int)$r['id'] ?>"
                                    class="btn btn-sm btn-primary"
                                    title="Edit Staff"
                                >
                                    <i class="bi bi-pencil"></i>
                                    <span>Edit</span>
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

                <!-- Empty State -->
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
                                    "<strong><?= e($q) ?></strong>".

                                </p>


                                <a
                                    href="staff.php"
                                    class="btn btn-outline-primary btn-sm"
                                >
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                    Clear Search
                                </a>


                            <?php else: ?>

                                <p class="text-muted mb-3">
                                    Start by adding your first staff record.
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
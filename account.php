<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'My Account';

$userId = (int)($_SESSION['user_id'] ?? 0);
$role   = $_SESSION['role'] ?? '';

$isAdmin   = strcasecmp($role, 'Administrator') === 0;
$isTeacher = strcasecmp($role, 'Teacher') === 0;
$isStaff   = strcasecmp($role, 'Staff') === 0;


/*
|--------------------------------------------------------------------------
| Get Account
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, full_name, username, role
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$userId]);

$account = $stmt->fetch();

if (!$account) {
    exit('Account not found.');
}


/*
|--------------------------------------------------------------------------
| Get Profile
|--------------------------------------------------------------------------
*/

$profile = null;

if ($isTeacher) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM teachers
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $profile = $stmt->fetch();
}

if ($isStaff) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM staff
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $profile = $stmt->fetch();
}


include 'header.php';

?>

<link rel="stylesheet" href="assets/account.css">


<div class="account-page">

    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="account-header">

        <span class="account-label">
            ACCOUNT SETTINGS
        </span>

        <h2 class="fw-bold mb-1">
            My Account
        </h2>

        <p class="text-muted mb-0">
            Manage your account and personal information.
        </p>

    </div>


    <!-- =========================================================
         SUCCESS MESSAGE
    ========================================================== -->

    <?php if (!empty($_SESSION['success'])): ?>

        <div class="alert alert-success">

            <i class="bi bi-check-circle me-2"></i>

            <?= e($_SESSION['success']) ?>

        </div>

        <?php unset($_SESSION['success']); ?>

    <?php endif; ?>


    <!-- =========================================================
         ERROR MESSAGE
    ========================================================== -->

    <?php if (!empty($_SESSION['error'])): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-circle me-2"></i>

            <?= e($_SESSION['error']) ?>

        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>


    <!-- =========================================================
         ACCOUNT INFORMATION
    ========================================================== -->

    <div class="account-card mb-4">

        <div class="account-card-header">

            <div class="account-icon">
                <i class="bi bi-person-circle"></i>
            </div>

            <div>

                <h5 class="mb-1">
                    Account Information
                </h5>

                <p class="text-muted mb-0">
                    Update your account information.
                </p>

            </div>

        </div>


        <form method="POST" action="account_edit.php">

            <input
                type="hidden"
                name="action"
                value="update_account"
            >


            <div class="row g-3">

                <!-- Full Name -->

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        class="form-control"
                        value="<?= e($account['full_name']) ?>"
                        required
                    >

                </div>


                <!-- Username -->

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="<?= e($account['username']) ?>"
                        required
                    >

                </div>


                <!-- Role -->

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Role
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= e($account['role']) ?>"
                        readonly
                    >

                </div>


                <!-- Current Password -->

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Current Password
                    </label>

                    <input
                        type="password"
                        name="current_password"
                        class="form-control"
                        placeholder="Required only when changing password"
                    >

                </div>


                <!-- New Password -->

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        New Password
                    </label>

                    <input
                        type="password"
                        name="new_password"
                        class="form-control"
                        placeholder="Leave blank to keep current password"
                    >

                </div>


                <!-- Confirm Password -->

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Confirm New Password
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        class="form-control"
                        placeholder="Confirm new password"
                    >

                </div>

            </div>


            <div class="mt-4">

                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="bi bi-check-circle me-1"></i>

                    Save Account Changes

                </button>

            </div>

        </form>

    </div>


    <!-- =========================================================
         TEACHER INFORMATION
    ========================================================== -->

    <?php if ($isTeacher): ?>

        <div class="account-card mb-4">

            <div class="account-card-header">

                <div class="account-icon">
                    <i class="bi bi-person-workspace"></i>
                </div>

                <div>

                    <h5 class="mb-1">
                        Teacher Information
                    </h5>

                    <p class="text-muted mb-0">
                        Update your teacher information.
                    </p>

                </div>

            </div>


            <form method="POST" action="account_edit.php">

                <input
                    type="hidden"
                    name="action"
                    value="update_profile"
                >

                <input
                    type="hidden"
                    name="profile_type"
                    value="teacher"
                >


                <div class="row g-3">

                    <!-- Employee No. -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Employee No.
                        </label>

                        <input
                            type="text"
                            name="employee_id"
                            class="form-control"
                            value="<?= e($profile['employee_id'] ?? '') ?>"
                            placeholder="Enter employee number"
                        >

                    </div>


                    <!-- Plantilla No. -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Plantilla No.
                        </label>

                        <input
                            type="text"
                            name="plantilla_no"
                            class="form-control"
                            value="<?= e($profile['plantilla_no'] ?? '') ?>"
                            placeholder="Enter plantilla number"
                        >

                    </div>


                    <!-- Full Name -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Full Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= e($account['full_name']) ?>"
                            readonly
                        >

                        <small class="text-muted">
                            This is linked to your account name.
                        </small>

                    </div>


                    <!-- First Day of Service -->

                    <div class="col-md-6">

                        <label class="form-label">
                            First Day of Service
                        </label>

                        <input
                            type="date"
                            name="first_day_of_service"
                            class="form-control"
                            value="<?= e($profile['first_day_of_service'] ?? '') ?>"
                        >

                    </div>


                    <!-- Current / Latest Appointment -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Current / Latest Appointment
                        </label>

                        <input
                            type="text"
                            name="current_latest_appointment"
                            class="form-control"
                            value="<?= e($profile['current_latest_appointment'] ?? '') ?>"
                            placeholder="e.g. Teacher I"
                        >

                    </div>


                    <!-- Position / Department -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Position / Department
                        </label>

                        <input
                            type="text"
                            name="position_department"
                            class="form-control"
                            value="<?= e($profile['position_department'] ?? '') ?>"
                            placeholder="e.g. Teacher I / Mathematics"
                        >

                    </div>


                    <!-- Contact Number -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Contact Number
                        </label>

                        <input
                            type="text"
                            name="contact_number"
                            class="form-control"
                            value="<?= e($profile['contact_number'] ?? '') ?>"
                            placeholder="Enter contact number"
                        >

                    </div>


                    <!-- DepEd Email -->

                    <div class="col-md-6">

                        <label class="form-label">
                            DepEd Email
                        </label>

                        <input
                            type="email"
                            name="deped_email"
                            class="form-control"
                            value="<?= e($profile['deped_email'] ?? '') ?>"
                            placeholder="sample@deped.gov.ph"
                        >

                    </div>


                    <!-- Personal Email -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Personal Email
                        </label>

                        <input
                            type="email"
                            name="personal_email"
                            class="form-control"
                            value="<?= e($profile['personal_email'] ?? '') ?>"
                            placeholder="sample@email.com"
                        >

                    </div>


                    <!-- Birthdate -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Birthdate
                        </label>

                        <input
                            type="date"
                            name="birthdate"
                            class="form-control"
                            value="<?= e($profile['birthdate'] ?? '') ?>"
                        >

                    </div>


                    <!-- Gender -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Gender
                        </label>

                        <select
                            name="gender"
                            class="form-select"
                        >

                            <option value="">
                                Select Gender
                            </option>

                            <option
                                value="Male"
                                <?= (($profile['gender'] ?? '') === 'Male') ? 'selected' : '' ?>
                            >
                                Male
                            </option>

                            <option
                                value="Female"
                                <?= (($profile['gender'] ?? '') === 'Female') ? 'selected' : '' ?>
                            >
                                Female
                            </option>

                            <option
                                value="Other"
                                <?= (($profile['gender'] ?? '') === 'Other') ? 'selected' : '' ?>
                            >
                                Other
                            </option>

                        </select>

                    </div>


                    <!-- Address -->

                    <div class="col-12">

                        <label class="form-label">
                            Address
                        </label>

                        <textarea
                            name="address"
                            class="form-control"
                            rows="3"
                            placeholder="Enter complete address"
                        ><?= e($profile['address'] ?? '') ?></textarea>

                    </div>


                    <!-- Degree Finished -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Degree Finished
                        </label>

                        <input
                            type="text"
                            name="degree_finished"
                            class="form-control"
                            value="<?= e($profile['degree_finished'] ?? '') ?>"
                            placeholder="e.g. Bachelor of Secondary Education"
                        >

                    </div>


                    <!-- Specialization -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Specialization / PRC Eligibility
                        </label>

                        <input
                            type="text"
                            name="specialization_prc_eligibility"
                            class="form-control"
                            value="<?= e($profile['specialization_prc_eligibility'] ?? '') ?>"
                            placeholder="Enter specialization or PRC eligibility"
                        >

                    </div>


                    <!-- TIN -->

                    <div class="col-md-6">

                        <label class="form-label">
                            TIN No.
                        </label>

                        <input
                            type="text"
                            name="tin_no"
                            class="form-control"
                            value="<?= e($profile['tin_no'] ?? '') ?>"
                            placeholder="Enter TIN number"
                        >

                    </div>

                </div>


                <div class="mt-4">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-check-circle me-1"></i>

                        Save Teacher Information

                    </button>

                </div>

            </form>

        </div>

    <?php endif; ?>


    <!-- =========================================================
         STAFF INFORMATION
    ========================================================== -->

    <?php if ($isStaff): ?>

        <div class="account-card mb-4">

            <div class="account-card-header">

                <div class="account-icon">
                    <i class="bi bi-people-fill"></i>
                </div>

                <div>

                    <h5 class="mb-1">
                        Staff Information
                    </h5>

                    <p class="text-muted mb-0">
                        Update your staff information.
                    </p>

                </div>

            </div>


            <form method="POST" action="account_edit.php">

                <input
                    type="hidden"
                    name="action"
                    value="update_profile"
                >

                <input
                    type="hidden"
                    name="profile_type"
                    value="staff"
                >


                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">
                            Employee No.
                        </label>

                        <input
                            type="text"
                            name="employee_id"
                            class="form-control"
                            value="<?= e($profile['employee_id'] ?? '') ?>"
                            placeholder="Enter employee number"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Plantilla No.
                        </label>

                        <input
                            type="text"
                            name="plantilla_no"
                            class="form-control"
                            value="<?= e($profile['plantilla_no'] ?? '') ?>"
                            placeholder="Enter plantilla number"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Full Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= e($account['full_name']) ?>"
                            readonly
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            First Day of Service
                        </label>

                        <input
                            type="date"
                            name="first_day_of_service"
                            class="form-control"
                            value="<?= e($profile['first_day_of_service'] ?? '') ?>"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Current / Latest Appointment
                        </label>

                        <input
                            type="text"
                            name="current_latest_appointment"
                            class="form-control"
                            value="<?= e($profile['current_latest_appointment'] ?? '') ?>"
                            placeholder="Enter appointment"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Position / Department
                        </label>

                        <input
                            type="text"
                            name="position_department"
                            class="form-control"
                            value="<?= e($profile['position_department'] ?? '') ?>"
                            placeholder="e.g. Administrative Assistant"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Contact Number
                        </label>

                        <input
                            type="text"
                            name="contact_number"
                            class="form-control"
                            value="<?= e($profile['contact_number'] ?? '') ?>"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            DepEd Email
                        </label>

                        <input
                            type="email"
                            name="deped_email"
                            class="form-control"
                            value="<?= e($profile['deped_email'] ?? '') ?>"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Personal Email
                        </label>

                        <input
                            type="email"
                            name="personal_email"
                            class="form-control"
                            value="<?= e($profile['personal_email'] ?? '') ?>"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Birthdate
                        </label>

                        <input
                            type="date"
                            name="birthdate"
                            class="form-control"
                            value="<?= e($profile['birthdate'] ?? '') ?>"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Gender
                        </label>

                        <select
                            name="gender"
                            class="form-select"
                        >

                            <option value="">
                                Select Gender
                            </option>

                            <option
                                value="Male"
                                <?= (($profile['gender'] ?? '') === 'Male') ? 'selected' : '' ?>
                            >
                                Male
                            </option>

                            <option
                                value="Female"
                                <?= (($profile['gender'] ?? '') === 'Female') ? 'selected' : '' ?>
                            >
                                Female
                            </option>

                            <option
                                value="Other"
                                <?= (($profile['gender'] ?? '') === 'Other') ? 'selected' : '' ?>
                            >
                                Other
                            </option>

                        </select>

                    </div>


                    <div class="col-12">

                        <label class="form-label">
                            Address
                        </label>

                        <textarea
                            name="address"
                            class="form-control"
                            rows="3"
                            placeholder="Enter complete address"
                        ><?= e($profile['address'] ?? '') ?></textarea>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Degree Finished
                        </label>

                        <input
                            type="text"
                            name="degree_finished"
                            class="form-control"
                            value="<?= e($profile['degree_finished'] ?? '') ?>"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Specialization / PRC Eligibility
                        </label>

                        <input
                            type="text"
                            name="specialization_prc_eligibility"
                            class="form-control"
                            value="<?= e($profile['specialization_prc_eligibility'] ?? '') ?>"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            TIN No.
                        </label>

                        <input
                            type="text"
                            name="tin_no"
                            class="form-control"
                            value="<?= e($profile['tin_no'] ?? '') ?>"
                        >

                    </div>

                </div>


                <div class="mt-4">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-check-circle me-1"></i>

                        Save Staff Information

                    </button>

                </div>

            </form>

        </div>

    <?php endif; ?>


    <!-- =========================================================
         EXPORT DATA
    ========================================================== -->

    <div class="account-card mb-4">

        <div class="account-card-header">

            <div class="account-icon">
                <i class="bi bi-file-earmark-excel"></i>
            </div>

            <div>

                <h5 class="mb-1">
                    Export Data
                </h5>

                <p class="text-muted mb-0">
                    Export school records to an Excel-compatible file.
                </p>

            </div>

        </div>


        <div class="row g-3">

            <!-- Students -->

            <?php if ($isTeacher || $isStaff || $isAdmin): ?>

                <div class="col-md-4">

                    <div class="export-card">

                        <div class="export-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>

                        <h6>
                            Export Students
                        </h6>

                        <p>
                            Export all student information.
                        </p>

                        <button
                            type="button"
                            class="btn btn-primary btn-sm w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#exportStudentsModal"
                        >

                            <i class="bi bi-download me-1"></i>

                            Export Students

                        </button>

                    </div>

                </div>

            <?php endif; ?>


            <!-- Teachers -->

            <?php if ($isStaff || $isAdmin): ?>

                <div class="col-md-4">

                    <div class="export-card">

                        <div class="export-icon">
                            <i class="bi bi-person-workspace"></i>
                        </div>

                        <h6>
                            Export Teachers
                        </h6>

                        <p>
                            Export all teacher information.
                        </p>

                        <button
                            type="button"
                            class="btn btn-primary btn-sm w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#exportTeachersModal"
                        >

                            <i class="bi bi-download me-1"></i>

                            Export Teachers

                        </button>

                    </div>

                </div>

            <?php endif; ?>


            <!-- Staff -->

            <?php if ($isAdmin): ?>

                <div class="col-md-4">

                    <div class="export-card">

                        <div class="export-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>

                        <h6>
                            Export Staff
                        </h6>

                        <p>
                            Export all staff information.
                        </p>

                        <button
                            type="button"
                            class="btn btn-primary btn-sm w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#exportStaffModal"
                        >

                            <i class="bi bi-download me-1"></i>

                            Export Staff

                        </button>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- =========================================================
         SYSTEM BACKUP
    ========================================================== -->

    <?php if ($isAdmin): ?>

        <div class="account-card mb-4">

            <div class="account-card-header">

                <div class="account-icon">
                    <i class="bi bi-database-fill-down"></i>
                </div>

                <div>

                    <h5 class="mb-1">
                        System Backup
                    </h5>

                    <p class="text-muted mb-0">
                        Create a backup of the school database.
                    </p>

                </div>

            </div>


            <div class="backup-card">

                <div>

                    <h6 class="mb-1">
                        Database Backup
                    </h6>

                    <p class="text-muted mb-0">
                        Download an SQL backup of the system database.
                    </p>

                </div>


                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#backupModal"
                >

                    <i class="bi bi-database-down me-1"></i>

                    Export SQL Backup

                </button>

            </div>

        </div>

    <?php endif; ?>

</div>


<!-- =========================================================
     STUDENT EXPORT MODAL
========================================================== -->

<?php if ($isTeacher || $isStaff || $isAdmin): ?>

<div class="modal fade" id="exportStudentsModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="POST" action="export_excel.php">

                <input
                    type="hidden"
                    name="type"
                    value="students"
                >

                <div class="modal-header">

                    <h5 class="modal-title">
                        Export Students
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <p class="text-muted">
                        Enter your account password to confirm this export.
                    </p>

                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                    >

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-download me-1"></i>
                        Export
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php endif; ?>


<!-- =========================================================
     TEACHER EXPORT MODAL
========================================================== -->

<?php if ($isStaff || $isAdmin): ?>

<div class="modal fade" id="exportTeachersModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="POST" action="export.php">

                <input
                    type="hidden"
                    name="type"
                    value="teachers"
                >

                <div class="modal-header">

                    <h5 class="modal-title">
                        Export Teachers
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <p class="text-muted">
                        Enter your account password to confirm this export.
                    </p>

                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                    >

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-download me-1"></i>
                        Export
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php endif; ?>


<!-- =========================================================
     STAFF EXPORT MODAL
========================================================== -->

<?php if ($isAdmin): ?>

<div class="modal fade" id="exportStaffModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="POST" action="export.php">

                <input
                    type="hidden"
                    name="type"
                    value="staff"
                >

                <div class="modal-header">

                    <h5 class="modal-title">
                        Export Staff
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <p class="text-muted">
                        Enter your account password to confirm this export.
                    </p>

                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                    >

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-download me-1"></i>
                        Export
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php endif; ?>


<!-- =========================================================
     DATABASE BACKUP MODAL
========================================================== -->

<?php if ($isAdmin): ?>

<div class="modal fade" id="backupModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="POST" action="backup.php">

                <div class="modal-header">

                    <h5 class="modal-title">
                        System Backup
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <div class="alert alert-warning">

                        <i class="bi bi-exclamation-triangle me-2"></i>

                        This will download a backup of the
                        school database.

                    </div>


                    <label class="form-label">
                        Administrator Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                    >

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-database-down me-1"></i>

                        Download Backup

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php endif; ?>


<?php include 'footer.php'; ?>
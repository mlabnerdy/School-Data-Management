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
| Get Current Account
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        full_name,
        username,
        role
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$userId]);

$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) {
    exit('Account not found.');
}


/*
|--------------------------------------------------------------------------
| Get Profile
|--------------------------------------------------------------------------
*/

$profile = null;


/*
|--------------------------------------------------------------------------
| Teacher Profile
|--------------------------------------------------------------------------
*/

if ($isTeacher) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM teachers
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
}


/*
|--------------------------------------------------------------------------
| Staff Profile
|--------------------------------------------------------------------------
*/

if ($isStaff) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM staff
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
}


/*
|--------------------------------------------------------------------------
| Get All Users - ADMIN ONLY
|--------------------------------------------------------------------------
*/

$allUsers = [];

if ($isAdmin) {

    $stmt = $pdo->query("
        SELECT
            id,
            full_name,
            username,
            role
        FROM users
        ORDER BY full_name ASC
    ");

    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


include 'header.php';

?>

<link rel="stylesheet" href="assets/account.css">


<style>

/* =========================================================
   PASSWORD FIELD
========================================================= */

.password-wrapper {
    position: relative;
    width: 100%;
}

.password-wrapper .password-input {
    padding-right: 48px;
}

.password-toggle {
    position: absolute;
    top: 50%;
    right: 8px;
    transform: translateY(-50%);

    width: 36px;
    height: 36px;

    border: none;
    background: transparent;

    display: flex;
    align-items: center;
    justify-content: center;

    color: #6c757d;

    cursor: pointer;

    border-radius: 6px;

    z-index: 5;
}

.password-toggle:hover {
    background: #f1f3f5;
    color: #0d6efd;
}

.password-toggle:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.15);
}

.password-toggle i {
    font-size: 18px;
}


/* =========================================================
   DELETE MODAL
========================================================= */

.delete-user-warning {
    background: #fff8e1;
    border: 1px solid #ffd666;
    border-radius: 10px;
    padding: 14px 16px;
}


/* =========================================================
   EXPORT / BACKUP
========================================================= */

.export-card {
    height: 100%;
}

.backup-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 576px) {

    .backup-card {
        flex-direction: column;
        align-items: stretch;
    }

    .backup-card .btn {
        width: 100%;
    }

}

</style>


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


        <form
            method="POST"
            action="account_edit.php"
        >

            <input
                type="hidden"
                name="action"
                value="update_account"
            >


            <div class="row g-3">


                <!-- FULL NAME -->

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


                <!-- USERNAME -->

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


                <!-- ROLE -->

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


                <!-- CURRENT PASSWORD -->

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Current Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="current_password"
                            class="form-control password-input"
                            placeholder="Required when changing password"
                            autocomplete="current-password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword(this)"
                            aria-label="Show password"
                        >

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>

                </div>


                <!-- NEW PASSWORD -->

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        New Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="new_password"
                            class="form-control password-input"
                            placeholder="Leave blank to keep current"
                            autocomplete="new-password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword(this)"
                            aria-label="Show password"
                        >

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>

                </div>


                <!-- CONFIRM NEW PASSWORD -->

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Confirm New Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="confirm_password"
                            class="form-control password-input"
                            placeholder="Confirm new password"
                            autocomplete="new-password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword(this)"
                            aria-label="Show password"
                        >

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>

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
         ADMIN USER MANAGEMENT
    ========================================================== -->

    <?php if ($isAdmin): ?>

    <div class="account-card mb-4">

        <div class="account-card-header">

            <div class="account-icon">

                <i class="bi bi-people-fill"></i>

            </div>

            <div>

                <h5 class="mb-1">
                    User Account Management
                </h5>

                <p class="text-muted mb-0">
                    Add, edit, or delete system accounts.
                </p>

            </div>

        </div>


        <!-- ADD USER -->

        <div class="mb-4">

            <button
                type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#addUserModal"
            >

                <i class="bi bi-person-plus me-1"></i>

                Add User

            </button>

        </div>


        <!-- USER TABLE -->

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>
                            Name
                        </th>

                        <th>
                            Username
                        </th>

                        <th>
                            Position
                        </th>

                        <th class="text-end">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($allUsers as $user): ?>

                    <tr>

                        <td>
                            <?= e($user['full_name']) ?>
                        </td>

                        <td>
                            <?= e($user['username']) ?>
                        </td>

                        <td>

                            <?php if (
                                strcasecmp($user['role'], 'Administrator') === 0
                            ): ?>

                                <span class="badge bg-danger">
                                    Administrator
                                </span>

                            <?php elseif (
                                strcasecmp($user['role'], 'Teacher') === 0
                            ): ?>

                                <span class="badge bg-primary">
                                    Teacher
                                </span>

                            <?php else: ?>

                                <span class="badge bg-secondary">
                                    Staff
                                </span>

                            <?php endif; ?>

                        </td>


                        <td class="text-end">

                            <!-- EDIT -->

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editUserModal<?= (int)$user['id'] ?>"
                                title="Edit User"
                            >

                                <i class="bi bi-pencil"></i>

                            </button>


                            <!-- DELETE -->

                            <?php if (
                                (int)$user['id'] !== $userId
                            ): ?>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteUserModal<?= (int)$user['id'] ?>"
                                    title="Delete User"
                                >

                                    <i class="bi bi-trash"></i>

                                </button>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>


                <?php if (!$allUsers): ?>

                    <tr>

                        <td
                            colspan="4"
                            class="text-center text-muted py-4"
                        >

                            No user accounts found.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <?php endif; ?>


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


        <form
            method="POST"
            action="account_edit.php"
        >

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


                <!-- NAME -->

                <div class="col-md-6">

                    <label class="form-label">
                        Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= e($account['full_name']) ?>"
                        readonly
                    >

                </div>


                <!-- CONTACT -->

                <div class="col-md-6">

                    <label class="form-label">
                        Contact Number
                    </label>

                    <input
                        type="text"
                        name="contact_number"
                        class="form-control"
                        value="<?= e($profile['contact_number'] ?? '') ?>"
                        placeholder="09XXXXXXXXX"
                        maxlength="11"
                        pattern="09[0-9]{9}"
                    >

                </div>


                <!-- BIRTHDATE -->

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


                <!-- GENDER -->

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

                    </select>

                </div>


                <!-- ADDRESS -->

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

            </div>


            <hr class="my-4">


            <div class="row g-3">


                <!-- EMPLOYEE -->

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


                <!-- PLANTILLA -->

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


            <hr class="my-4">


            <div class="row g-3">


                <!-- FIRST DAY -->

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


                <!-- POSITION -->

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


                <!-- APPOINTMENT -->

                <div class="col-md-6">

                    <label class="form-label">
                        Current / Latest Appointment
                    </label>

                    <input
                        type="date"
                        name="current_latest_appointment"
                        class="form-control"
                        value="<?= e($profile['current_latest_appointment'] ?? '') ?>"
                    >

                </div>

            </div>


            <hr class="my-4">


            <div class="row g-3">


                <!-- DEGREE -->

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


                <!-- SPECIALIZATION -->

                <div class="col-md-6">

                    <label class="form-label">
                        Specialization / PRC Eligibility
                    </label>

                    <input
                        type="text"
                        name="specialization_prc_eligibility"
                        class="form-control"
                        value="<?= e($profile['specialization_prc_eligibility'] ?? '') ?>"
                        placeholder="e.g. Mathematics / Licensed Professional Teacher"
                    >

                </div>

            </div>


            <hr class="my-4">


            <div class="row g-3">


                <!-- DEPED EMAIL -->

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


                <!-- PERSONAL EMAIL -->

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

                <i class="bi bi-person-badge-fill"></i>

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


        <form
            method="POST"
            action="account_edit.php"
        >

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
                        Name
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
                        Contact Number
                    </label>

                    <input
                        type="text"
                        name="contact_number"
                        class="form-control"
                        value="<?= e($profile['contact_number'] ?? '') ?>"
                        placeholder="09XXXXXXXXX"
                        maxlength="11"
                        pattern="09[0-9]{9}"
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

            </div>


            <hr class="my-4">


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
                        Position / Department
                    </label>

                    <input
                        type="text"
                        name="position_department"
                        class="form-control"
                        value="<?= e($profile['position_department'] ?? '') ?>"
                        placeholder="Enter position / department"
                    >

                </div>


                <div class="col-md-6">

                    <label class="form-label">
                        Current / Latest Appointment
                    </label>

                    <input
                        type="date"
                        name="current_latest_appointment"
                        class="form-control"
                        value="<?= e($profile['current_latest_appointment'] ?? '') ?>"
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
                        placeholder="Enter TIN number"
                    >

                </div>

            </div>


            <hr class="my-4">


            <div class="row g-3">


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

            </div>


            <hr class="my-4">


            <div class="row g-3">


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


            <!-- STUDENTS -->

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


            <!-- TEACHERS -->

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


            <!-- STAFF -->

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
         DATABASE BACKUP
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
     ADD USER MODAL
========================================================== -->

<?php if ($isAdmin): ?>

<div
    class="modal fade"
    id="addUserModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                method="POST"
                action="account_edit.php"
            >

                <input
                    type="hidden"
                    name="action"
                    value="add_user"
                >


                <div class="modal-header">

                    <h5 class="modal-title">
                        Add User Account
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-12">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Username
                            </label>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Position
                            </label>

                            <select
                                name="role"
                                class="form-select"
                                required
                            >

                                <option value="Teacher">
                                    Teacher
                                </option>

                                <option value="Staff">
                                    Staff
                                </option>

                                <option value="Administrator">
                                    Administrator
                                </option>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Password
                            </label>

                            <div class="password-wrapper">

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control password-input"
                                    required
                                    autocomplete="new-password"
                                >

                                <button
                                    type="button"
                                    class="password-toggle"
                                    onclick="togglePassword(this)"
                                    aria-label="Show password"
                                >

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Confirm Password
                            </label>

                            <div class="password-wrapper">

                                <input
                                    type="password"
                                    name="confirm_password"
                                    class="form-control password-input"
                                    required
                                    autocomplete="new-password"
                                >

                                <button
                                    type="button"
                                    class="password-toggle"
                                    onclick="togglePassword(this)"
                                    aria-label="Show password"
                                >

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>

                    </div>

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

                        <i class="bi bi-person-plus me-1"></i>

                        Add User

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php endif; ?>


<!-- =========================================================
     EDIT USER MODALS
========================================================== -->

<?php if ($isAdmin): ?>

<?php foreach ($allUsers as $user): ?>

<div
    class="modal fade"
    id="editUserModal<?= (int)$user['id'] ?>"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                method="POST"
                action="account_edit.php"
            >

                <input
                    type="hidden"
                    name="action"
                    value="admin_update_user"
                >

                <input
                    type="hidden"
                    name="user_id"
                    value="<?= (int)$user['id'] ?>"
                >


                <div class="modal-header">

                    <h5 class="modal-title">
                        Edit User Account
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-12">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                value="<?= e($user['full_name']) ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Username
                            </label>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="<?= e($user['username']) ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Position
                            </label>

                            <select
                                name="role"
                                class="form-select"
                                required
                            >

                                <option
                                    value="Teacher"
                                    <?= strcasecmp($user['role'], 'Teacher') === 0 ? 'selected' : '' ?>
                                >
                                    Teacher
                                </option>

                                <option
                                    value="Staff"
                                    <?= strcasecmp($user['role'], 'Staff') === 0 ? 'selected' : '' ?>
                                >
                                    Staff
                                </option>

                                <option
                                    value="Administrator"
                                    <?= strcasecmp($user['role'], 'Administrator') === 0 ? 'selected' : '' ?>
                                >
                                    Administrator
                                </option>

                            </select>

                        </div>


                        <div class="col-12">

                            <label class="form-label">
                                New Password
                            </label>

                            <div class="password-wrapper">

                                <input
                                    type="password"
                                    name="new_password"
                                    class="form-control password-input"
                                    placeholder="Leave blank to keep current password"
                                    autocomplete="new-password"
                                >

                                <button
                                    type="button"
                                    class="password-toggle"
                                    onclick="togglePassword(this)"
                                    aria-label="Show password"
                                >

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>


                        <div class="col-12">

                            <label class="form-label">
                                Confirm Password
                            </label>

                            <div class="password-wrapper">

                                <input
                                    type="password"
                                    name="confirm_password"
                                    class="form-control password-input"
                                    placeholder="Required only when changing password"
                                    autocomplete="new-password"
                                >

                                <button
                                    type="button"
                                    class="password-toggle"
                                    onclick="togglePassword(this)"
                                    aria-label="Show password"
                                >

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>

                    </div>

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

                        <i class="bi bi-check-circle me-1"></i>

                        Save Changes

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php endforeach; ?>

<?php endif; ?>


<!-- =========================================================
     DELETE USER MODALS
========================================================== -->

<?php if ($isAdmin): ?>

<?php foreach ($allUsers as $user): ?>

<?php if ((int)$user['id'] !== $userId): ?>

<div
    class="modal fade"
    id="deleteUserModal<?= (int)$user['id'] ?>"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                method="POST"
                action="account_edit.php"
            >

                <input
                    type="hidden"
                    name="action"
                    value="delete_user"
                >

                <input
                    type="hidden"
                    name="user_id"
                    value="<?= (int)$user['id'] ?>"
                >


                <div class="modal-header">

                    <h5 class="modal-title text-danger">

                        <i class="bi bi-trash me-2"></i>

                        Delete User Account

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <div class="delete-user-warning mb-4">

                        <i class="bi bi-exclamation-triangle me-2"></i>

                        You are about to permanently delete:

                        <strong>
                            <?= e($user['full_name']) ?>
                        </strong>

                        <br>

                        Username:

                        <strong>
                            <?= e($user['username']) ?>
                        </strong>

                    </div>


                    <p class="mb-3">

                        For security, enter your
                        <strong>Administrator password</strong>
                        to confirm this action.

                    </p>


                    <label class="form-label fw-semibold">
                        Administrator Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="admin_password"
                            class="form-control password-input"
                            placeholder="Enter your administrator password"
                            required
                            autocomplete="current-password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword(this)"
                            aria-label="Show password"
                        >

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>

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
                        class="btn btn-danger"
                    >

                        <i class="bi bi-trash me-1"></i>

                        Delete Account

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php endif; ?>

<?php endforeach; ?>

<?php endif; ?>


<!-- =========================================================
     EXPORT STUDENTS MODAL
========================================================== -->

<?php if ($isTeacher || $isStaff || $isAdmin): ?>

<div
    class="modal fade"
    id="exportStudentsModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                method="POST"
                action="export_excel.php"
                class="download-form"
            >

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


                    <label class="form-label fw-semibold">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="password"
                            class="form-control password-input"
                            required
                            autocomplete="current-password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword(this)"
                            aria-label="Show password"
                        >

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>

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
     EXPORT TEACHERS MODAL
========================================================== -->

<?php if ($isStaff || $isAdmin): ?>

<div
    class="modal fade"
    id="exportTeachersModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                method="POST"
                action="export_excel.php"
                class="download-form"
            >

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


                    <label class="form-label fw-semibold">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="password"
                            class="form-control password-input"
                            required
                            autocomplete="current-password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword(this)"
                            aria-label="Show password"
                        >

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>

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
     EXPORT STAFF MODAL
========================================================== -->

<?php if ($isAdmin): ?>

<div
    class="modal fade"
    id="exportStaffModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                method="POST"
                action="export_excel.php"
                class="download-form"
            >

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


                    <label class="form-label fw-semibold">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="password"
                            class="form-control password-input"
                            required
                            autocomplete="current-password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword(this)"
                            aria-label="Show password"
                        >

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>

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

<div
    class="modal fade"
    id="backupModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                method="POST"
                action="backup_sql.php"
                class="download-form"
            >

                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="bi bi-database-fill-down me-2"></i>

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


                    <label class="form-label fw-semibold">
                        Administrator Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="password"
                            class="form-control password-input"
                            placeholder="Enter your administrator password"
                            required
                            autocomplete="current-password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword(this)"
                            aria-label="Show password"
                        >

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>

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


<!-- =========================================================
     PASSWORD TOGGLE
========================================================== -->

<script>

window.togglePassword = function(button) {

    if (!button) {
        return;
    }


    const wrapper =
        button.closest('.password-wrapper');


    if (!wrapper) {
        return;
    }


    const input =
        wrapper.querySelector('.password-input');


    const icon =
        button.querySelector('i');


    if (!input || !icon) {
        return;
    }


    if (input.type === 'password') {

        input.type = 'text';


        icon.classList.remove(
            'bi-eye'
        );

        icon.classList.add(
            'bi-eye-slash'
        );


        button.setAttribute(
            'aria-label',
            'Hide password'
        );

    } else {

        input.type = 'password';


        icon.classList.remove(
            'bi-eye-slash'
        );

        icon.classList.add(
            'bi-eye'
        );


        button.setAttribute(
            'aria-label',
            'Show password'
        );

    }

};


</script>


<!-- =========================================================
     DOWNLOAD / EXPORT HANDLING
========================================================== -->

<script>

document.addEventListener('DOMContentLoaded', function () {


    /*
    |--------------------------------------------------------------------------
    | HANDLE EXPORT AND BACKUP FORMS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | There is ONLY ONE submit handler for .download-form.
    |
    | We DO NOT automatically hide the modal.
    | The modal will remain open if the password is incorrect.
    |
    */

    const forms =
        document.querySelectorAll('.download-form');


    forms.forEach(function (form) {


        form.addEventListener('submit', async function (event) {


            /*
            |--------------------------------------------------------------------------
            | STOP NORMAL FORM SUBMISSION
            |--------------------------------------------------------------------------
            */

            event.preventDefault();


            /*
            |--------------------------------------------------------------------------
            | GET MODAL
            |--------------------------------------------------------------------------
            */

            const modalElement =
                form.closest('.modal');


            let modalInstance = null;


            if (modalElement) {

                modalInstance =
                    bootstrap.Modal.getOrCreateInstance(
                        modalElement
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | GET BUTTON
            |--------------------------------------------------------------------------
            */

            const submitButton =
                form.querySelector(
                    'button[type="submit"]'
                );


            const originalButtonHTML =
                submitButton
                    ? submitButton.innerHTML
                    : '';


            /*
            |--------------------------------------------------------------------------
            | REMOVE OLD ERROR
            |--------------------------------------------------------------------------
            */

            const oldError =
                form.querySelector(
                    '.download-error'
                );


            if (oldError) {

                oldError.remove();

            }


            /*
            |--------------------------------------------------------------------------
            | SHOW PROCESSING
            |--------------------------------------------------------------------------
            */

            if (submitButton) {

                submitButton.disabled = true;

                submitButton.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"></span>' +
                    'Preparing...';

            }


            try {


                /*
                |--------------------------------------------------------------------------
                | SEND REQUEST
                |--------------------------------------------------------------------------
                */

                const response =
                    await fetch(
                        form.action,
                        {
                            method: 'POST',
                            body: new FormData(form)
                        }
                    );


                /*
                |--------------------------------------------------------------------------
                | GET CONTENT TYPE
                |--------------------------------------------------------------------------
                */

                const contentType =
                    response.headers.get(
                        'content-type'
                    ) || '';


                /*
                |--------------------------------------------------------------------------
                | JSON RESPONSE
                |--------------------------------------------------------------------------
                |
                | Incorrect passwords should return JSON.
                |
                */

                if (
                    contentType
                        .toLowerCase()
                        .includes('application/json')
                ) {


                    const data =
                        await response.json();


                    /*
                    |--------------------------------------------------------------------------
                    | SERVER REPORTED ERROR
                    |--------------------------------------------------------------------------
                    */

                    if (!data.success) {


                        showDownloadError(
                            form,
                            data.message ||
                            'Incorrect password.'
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | KEEP MODAL OPEN
                        |--------------------------------------------------------------------------
                        */

                        if (modalInstance) {

                            modalInstance.show();

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | PUT CURSOR BACK IN PASSWORD FIELD
                        |--------------------------------------------------------------------------
                        */

                        const passwordInput =
                            form.querySelector(
                                'input[name="password"]'
                            );


                        if (passwordInput) {

                            passwordInput.focus();

                        }


                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SUCCESS JSON
                    |--------------------------------------------------------------------------
                    |
                    | If the endpoint sends JSON success without a file,
                    | show the success message and stop.
                    |
                    */

                    if (
                        data.success &&
                        data.download === false
                    ) {

                        showDownloadSuccess(
                            form,
                            data.message ||
                            'Operation completed successfully.'
                        );


                        return;

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | CHECK HTTP STATUS
                |--------------------------------------------------------------------------
                */

                if (!response.ok) {

                    showDownloadError(
                        form,
                        'Unable to prepare the download. Please try again.'
                    );


                    if (modalInstance) {

                        modalInstance.show();

                    }


                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | DOWNLOAD FILE
                |--------------------------------------------------------------------------
                */

                const blob =
                    await response.blob();


                /*
                |--------------------------------------------------------------------------
                | GET FILE NAME
                |--------------------------------------------------------------------------
                */

                let filename =
                    'download';


                const disposition =
                    response.headers.get(
                        'content-disposition'
                    );


                if (disposition) {

                    const match =
                        disposition.match(
                            /filename\*?=(?:UTF-8''|")?([^";]+)"?/i
                        );


                    if (match && match[1]) {

                        filename =
                            decodeURIComponent(
                                match[1]
                            );

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | CREATE DOWNLOAD URL
                |--------------------------------------------------------------------------
                */

                const url =
                    window.URL.createObjectURL(
                        blob
                    );


                /*
                |--------------------------------------------------------------------------
                | CREATE DOWNLOAD LINK
                |--------------------------------------------------------------------------
                */

                const link =
                    document.createElement('a');


                link.href = url;

                link.download = filename;


                document.body.appendChild(link);


                link.click();


                link.remove();


                /*
                |--------------------------------------------------------------------------
                | CLEAN UP
                |--------------------------------------------------------------------------
                */

                setTimeout(function () {

                    window.URL.revokeObjectURL(url);

                }, 1000);


                /*
                |--------------------------------------------------------------------------
                | CLOSE MODAL ONLY AFTER SUCCESSFUL DOWNLOAD
                |--------------------------------------------------------------------------
                */

                if (modalInstance) {

                    modalInstance.hide();

                }


            } catch (error) {


                console.error(
                    'Download error:',
                    error
                );


                /*
                |--------------------------------------------------------------------------
                | SHOW ERROR
                |--------------------------------------------------------------------------
                */

                showDownloadError(
                    form,
                    'Unable to prepare the download. Please try again.'
                );


                /*
                |--------------------------------------------------------------------------
                | KEEP MODAL OPEN
                |--------------------------------------------------------------------------
                */

                if (modalInstance) {

                    modalInstance.show();

                }

            } finally {


                /*
                |--------------------------------------------------------------------------
                | RESTORE BUTTON
                |--------------------------------------------------------------------------
                */

                if (submitButton) {

                    submitButton.disabled = false;

                    submitButton.innerHTML =
                        originalButtonHTML;

                }

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | SHOW ERROR
    |--------------------------------------------------------------------------
    */

    function showDownloadError(form, message)
    {

        /*
        |--------------------------------------------------------------------------
        | REMOVE EXISTING ERROR
        |--------------------------------------------------------------------------
        */

        const oldError =
            form.querySelector(
                '.download-error'
            );


        if (oldError) {

            oldError.remove();

        }


        /*
        |--------------------------------------------------------------------------
        | CREATE ERROR
        |--------------------------------------------------------------------------
        */

        const error =
            document.createElement('div');


        error.className =
            'alert alert-danger download-error mt-3 mb-0';


        error.innerHTML =
            '<i class="bi bi-exclamation-circle me-2"></i>' +
            escapeHtml(message);


        /*
        |--------------------------------------------------------------------------
        | ADD ERROR TO MODAL BODY
        |--------------------------------------------------------------------------
        */

        const modalBody =
            form.querySelector(
                '.modal-body'
            );


        if (modalBody) {

            modalBody.appendChild(error);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | SHOW SUCCESS
    |--------------------------------------------------------------------------
    */

    function showDownloadSuccess(form, message)
    {

        const oldMessage =
            form.querySelector(
                '.download-success'
            );


        if (oldMessage) {

            oldMessage.remove();

        }


        const success =
            document.createElement('div');


        success.className =
            'alert alert-success download-success mt-3 mb-0';


        success.innerHTML =
            '<i class="bi bi-check-circle me-2"></i>' +
            escapeHtml(message);


        const modalBody =
            form.querySelector(
                '.modal-body'
            );


        if (modalBody) {

            modalBody.appendChild(success);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(text)
    {

        const div =
            document.createElement('div');


        div.textContent =
            text;


        return div.innerHTML;

    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR PASSWORDS WHEN MODAL IS CLOSED
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.modal').forEach(
        function (modal) {


            modal.addEventListener(
                'hidden.bs.modal',
                function () {


                    /*
                    |--------------------------------------------------------------------------
                    | CLEAR PASSWORD FIELDS
                    |--------------------------------------------------------------------------
                    */

                    modal.querySelectorAll(
                        'input[type="password"], input.password-input'
                    ).forEach(
                        function (input) {

                            input.value = '';

                            input.type = 'password';

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | RESET EYE ICONS
                    |--------------------------------------------------------------------------
                    */

                    modal.querySelectorAll(
                        '.password-toggle i'
                    ).forEach(
                        function (icon) {

                            icon.classList.remove(
                                'bi-eye-slash'
                            );

                            icon.classList.add(
                                'bi-eye'
                            );

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | RESET ARIA LABELS
                    |--------------------------------------------------------------------------
                    */

                    modal.querySelectorAll(
                        '.password-toggle'
                    ).forEach(
                        function (button) {

                            button.setAttribute(
                                'aria-label',
                                'Show password'
                            );

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | REMOVE EXPORT ERRORS
                    |--------------------------------------------------------------------------
                    */

                    modal.querySelectorAll(
                        '.download-error, .download-success'
                    ).forEach(
                        function (message) {

                            message.remove();

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | RESET DOWNLOAD BUTTONS
                    |--------------------------------------------------------------------------
                    */

                    modal.querySelectorAll(
                        '.download-form button[type="submit"]'
                    ).forEach(
                        function (button) {

                            button.disabled = false;


                            if (
                                modal.id === 'backupModal'
                            ) {

                                button.innerHTML =
                                    '<i class="bi bi-database-down me-1"></i>' +
                                    'Download Backup';

                            } else {

                                button.innerHTML =
                                    '<i class="bi bi-download me-1"></i>' +
                                    'Export';

                            }

                        }
                    );

                }
            );

        }
    );

});

</script>


<?php include 'footer.php'; ?>
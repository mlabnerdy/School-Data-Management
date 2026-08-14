<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'My Account';

$userId = (int)($_SESSION['user_id'] ?? 0);
$userRole = $_SESSION['role'] ?? '';

$isAdmin = ($userRole === 'Administrator');

$error = '';
$success = '';

/* Get current user */
$stmt = $pdo->prepare("
    SELECT id, username, full_name, role
    FROM users
    WHERE id = ?
");

$stmt->execute([$userId]);

$user = $stmt->fetch();

if (!$user) {
    redirect('logout.php');
}


/* Update own account */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {

    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';

    if ($fullName === '' || $username === '') {

        $error = 'Full Name and Username are required.';

    } elseif ($currentPassword === '') {

        $error = 'Enter your current password to save changes.';

    } else {

        $stmt = $pdo->prepare("
            SELECT password
            FROM users
            WHERE id = ?
        ");

        $stmt->execute([$userId]);

        $account = $stmt->fetch();

        if (
            !$account ||
            !password_verify($currentPassword, $account['password'])
        ) {

            $error = 'Current password is incorrect.';

        } else {

            /* Check username */
            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE username = ?
                AND id != ?
            ");

            $stmt->execute([
                $username,
                $userId
            ]);

            if ($stmt->fetch()) {

                $error = 'Username is already taken.';

            } else {

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET
                        full_name = ?,
                        username = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $fullName,
                    $username,
                    $userId
                ]);

                $_SESSION['full_name'] = $fullName;
                $_SESSION['username'] = $username;

                $user['full_name'] = $fullName;
                $user['username'] = $username;

                $success = 'Account information updated successfully.';
            }
        }
    }
}


/* Change own password */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {

        $error = 'All password fields are required.';

    } elseif (strlen($newPassword) < 6) {

        $error = 'New password must be at least 6 characters.';

    } elseif ($newPassword !== $confirmPassword) {

        $error = 'New passwords do not match.';

    } else {

        $stmt = $pdo->prepare("
            SELECT password
            FROM users
            WHERE id = ?
        ");

        $stmt->execute([$userId]);

        $account = $stmt->fetch();

        if (
            !$account ||
            !password_verify($currentPassword, $account['password'])
        ) {

            $error = 'Current password is incorrect.';

        } else {

            $hashedPassword = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare("
                UPDATE users
                SET password = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $hashedPassword,
                $userId
            ]);

            $success = 'Password changed successfully.';
        }
    }
}


/* Admin account management */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['account_action'])
) {

    if (!$isAdmin) {

        $error = 'You do not have permission to manage accounts.';

    } else {

        $action = $_POST['account_action'];

        $targetId = (int)($_POST['target_id'] ?? 0);

        $adminPassword = $_POST['admin_password'] ?? '';


        /* Verify Administrator password */
        if ($adminPassword === '') {

            $error = 'Administrator password is required.';

        } else {

            $stmt = $pdo->prepare("
                SELECT password
                FROM users
                WHERE id = ?
                AND role = 'Administrator'
            ");

            $stmt->execute([$userId]);

            $adminAccount = $stmt->fetch();

            if (
                !$adminAccount ||
                !password_verify(
                    $adminPassword,
                    $adminAccount['password']
                )
            ) {

                $error = 'Administrator password is incorrect.';

            } else {


                /* ADD ACCOUNT */
                if ($action === 'add') {

                    $fullName = trim($_POST['new_full_name'] ?? '');
                    $username = trim($_POST['new_username'] ?? '');
                    $password = $_POST['new_password'] ?? '';
                    $role = $_POST['new_role'] ?? '';


                    if (
                        $fullName === '' ||
                        $username === '' ||
                        $password === ''
                    ) {

                        $error = 'All account fields are required.';

                    } elseif (
                        !in_array(
                            $role,
                            ['Teacher', 'Staff'],
                            true
                        )
                    ) {

                        $error = 'Please select a valid role.';

                    } elseif (strlen($password) < 6) {

                        $error = 'Account password must be at least 6 characters.';

                    } else {

                        $stmt = $pdo->prepare("
                            SELECT id
                            FROM users
                            WHERE username = ?
                        ");

                        $stmt->execute([$username]);

                        if ($stmt->fetch()) {

                            $error = 'Username is already taken.';

                        } else {

                            $hashedPassword = password_hash(
                                $password,
                                PASSWORD_DEFAULT
                            );

                            $stmt = $pdo->prepare("
                                INSERT INTO users
                                (
                                    full_name,
                                    username,
                                    password,
                                    role
                                )
                                VALUES (?, ?, ?, ?)
                            ");

                            $stmt->execute([
                                $fullName,
                                $username,
                                $hashedPassword,
                                $role
                            ]);

                            $success =
                                $role .
                                ' account added successfully.';
                        }
                    }
                }


                /* EDIT ACCOUNT */
                elseif ($action === 'edit') {

                    $fullName = trim(
                        $_POST['edit_full_name'] ?? ''
                    );

                    $username = trim(
                        $_POST['edit_username'] ?? ''
                    );

                    $role = $_POST['edit_role'] ?? '';

                    $newPassword =
                        $_POST['edit_password'] ?? '';


                    if ($targetId <= 0) {

                        $error = 'Invalid account.';

                    } elseif (
                        $fullName === '' ||
                        $username === ''
                    ) {

                        $error =
                            'Full Name and Username are required.';

                    } elseif (
                        !in_array(
                            $role,
                            ['Teacher', 'Staff'],
                            true
                        )
                    ) {

                        $error = 'Invalid account role.';

                    } else {

                        /* Get target account */
                        $stmt = $pdo->prepare("
                            SELECT id, role
                            FROM users
                            WHERE id = ?
                        ");

                        $stmt->execute([$targetId]);

                        $target = $stmt->fetch();


                        if (!$target) {

                            $error = 'Account not found.';

                        } elseif (
                            $target['role'] === 'Administrator'
                        ) {

                            $error =
                                'Administrator accounts cannot be edited here.';

                        } else {

                            /* Check username */
                            $stmt = $pdo->prepare("
                                SELECT id
                                FROM users
                                WHERE username = ?
                                AND id != ?
                            ");

                            $stmt->execute([
                                $username,
                                $targetId
                            ]);

                            if ($stmt->fetch()) {

                                $error =
                                    'Username is already taken.';

                            } else {


                                /* Update password too */
                                if ($newPassword !== '') {

                                    if (strlen($newPassword) < 6) {

                                        $error =
                                            'New password must be at least 6 characters.';

                                    } else {

                                        $hashedPassword =
                                            password_hash(
                                                $newPassword,
                                                PASSWORD_DEFAULT
                                            );

                                        $stmt = $pdo->prepare("
                                            UPDATE users
                                            SET
                                                full_name = ?,
                                                username = ?,
                                                role = ?,
                                                password = ?
                                            WHERE id = ?
                                            AND role IN ('Teacher', 'Staff')
                                        ");

                                        $stmt->execute([
                                            $fullName,
                                            $username,
                                            $role,
                                            $hashedPassword,
                                            $targetId
                                        ]);

                                        $success =
                                            'Account updated successfully.';
                                    }

                                } else {

                                    /* Keep old password */
                                    $stmt = $pdo->prepare("
                                        UPDATE users
                                        SET
                                            full_name = ?,
                                            username = ?,
                                            role = ?
                                        WHERE id = ?
                                        AND role IN ('Teacher', 'Staff')
                                    ");

                                    $stmt->execute([
                                        $fullName,
                                        $username,
                                        $role,
                                        $targetId
                                    ]);

                                    $success =
                                        'Account updated successfully.';
                                }
                            }
                        }
                    }
                }


                /* DELETE ACCOUNT */
                elseif ($action === 'delete') {

                    if ($targetId <= 0) {

                        $error = 'Invalid account.';

                    } elseif ($targetId === $userId) {

                        $error =
                            'You cannot delete your own account.';

                    } else {

                        /* Get target role */
                        $stmt = $pdo->prepare("
                            SELECT role
                            FROM users
                            WHERE id = ?
                        ");

                        $stmt->execute([$targetId]);

                        $target = $stmt->fetch();


                        if (!$target) {

                            $error = 'Account not found.';

                        } elseif (
                            $target['role'] === 'Administrator'
                        ) {

                            $error =
                                'Administrator accounts cannot be deleted.';

                        } else {

                            $stmt = $pdo->prepare("
                                DELETE FROM users
                                WHERE id = ?
                                AND role IN ('Teacher', 'Staff')
                            ");

                            $stmt->execute([$targetId]);

                            if ($stmt->rowCount() > 0) {

                                $success =
                                    'Account deleted successfully.';

                            } else {

                                $error =
                                    'Unable to delete the account.';
                            }
                        }
                    }
                }
            }
        }
    }
}


/* Get Teacher and Staff accounts */
$accounts = [];

if ($isAdmin) {

    $stmt = $pdo->query("
        SELECT
            id,
            username,
            full_name,
            role,
            created_at
        FROM users
        WHERE role IN ('Teacher', 'Staff')
        ORDER BY role ASC, full_name ASC
    ");

    $accounts = $stmt->fetchAll();
}


include 'header.php';

?>

<link rel="stylesheet" href="assets/account.css">


<div class="account-page">


    <!-- Page Header -->
    <div class="account-header">

        <div>

            <div class="account-label">
                ACCOUNT
            </div>

            <h2 class="fw-bold mb-1">
                My Account
            </h2>

            <p class="text-muted mb-0">
                Manage your account information and password.
            </p>

        </div>


        <a
            href="index.php"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Dashboard
        </a>

    </div>


    <!-- Messages -->

    <?php if ($error): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-circle me-2"></i>

            <?= e($error) ?>

        </div>

    <?php endif; ?>


    <?php if ($success): ?>

        <div class="alert alert-success">

            <i class="bi bi-check-circle me-2"></i>

            <?= e($success) ?>

        </div>

    <?php endif; ?>


    <!-- Own Account -->

    <div class="row g-4">


        <!-- Account Information -->

        <div class="col-12 col-lg-7">

            <div class="account-card">

                <div class="account-card-header">

                    <div class="account-card-heading">

                        <div class="account-icon">
                            <i class="bi bi-person-vcard"></i>
                        </div>

                        <div>

                            <h5 class="fw-bold mb-1">
                                Account Information
                            </h5>

                            <p class="text-muted small mb-0">
                                Update your name and username.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="account-card-body">

                    <form method="post">

                        <div class="mb-3">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                value="<?= e($user['full_name']) ?>"
                                maxlength="150"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Username
                            </label>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="<?= e($user['username']) ?>"
                                maxlength="80"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Role
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="<?= e($user['role']) ?>"
                                readonly
                            >

                        </div>


                        <div class="mb-4">

                            <label class="form-label">
                                Current Password
                            </label>

                            <input
                                type="password"
                                name="current_password"
                                class="form-control"
                                required
                            >

                            <div class="form-text">
                                Required to save account changes.
                            </div>

                        </div>


                        <button
                            type="submit"
                            name="update_account"
                            value="1"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-check-lg me-1"></i>

                            Save Changes

                        </button>

                    </form>

                </div>

            </div>

        </div>


        <!-- Change Password -->

        <div class="col-12 col-lg-5">

            <div class="account-card">

                <div class="account-card-header">

                    <div class="account-card-heading">

                        <div class="account-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>

                        <div>

                            <h5 class="fw-bold mb-1">
                                Change Password
                            </h5>

                            <p class="text-muted small mb-0">
                                Update your login password.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="account-card-body">

                    <form method="post">

                        <div class="mb-3">

                            <label class="form-label">
                                Current Password
                            </label>

                            <input
                                type="password"
                                name="current_password"
                                class="form-control"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                New Password
                            </label>

                            <input
                                type="password"
                                name="new_password"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>


                        <div class="mb-4">

                            <label class="form-label">
                                Confirm New Password
                            </label>

                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            name="change_password"
                            value="1"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-key me-1"></i>

                            Change Password

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>


    <?php if ($isAdmin): ?>


        <!-- Admin Account Management -->

        <div class="account-card mt-4">

            <div class="account-card-header">

                <div class="account-card-heading">

                    <div class="account-icon">
                        <i class="bi bi-people"></i>
                    </div>

                    <div>

                        <h5 class="fw-bold mb-1">
                            Staff & Teacher Accounts
                        </h5>

                        <p class="text-muted small mb-0">
                            Add, edit, and delete Staff and Teacher accounts.
                        </p>

                    </div>

                </div>


                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addAccountModal"
                >

                    <i class="bi bi-plus-lg me-1"></i>

                    Add Account

                </button>

            </div>


            <div class="table-responsive">

                <table class="table account-table align-middle mb-0">

                    <thead>

                        <tr>

                            <th>
                                Full Name
                            </th>

                            <th>
                                Username
                            </th>

                            <th>
                                Role
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

                        <?php foreach ($accounts as $account): ?>

                            <tr>

                                <td>
                                    <?= e($account['full_name']) ?>
                                </td>

                                <td>
                                    <?= e($account['username']) ?>
                                </td>

                                <td>

                                    <?php if ($account['role'] === 'Teacher'): ?>

                                        <span class="badge bg-primary">
                                            Teacher
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Staff
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= e($account['created_at']) ?>
                                </td>

                                <td>

                                    <div class="d-flex justify-content-end gap-2">


                                        <!-- Edit -->

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editAccountModal"
                                            data-id="<?= (int)$account['id'] ?>"
                                            data-name="<?= e($account['full_name']) ?>"
                                            data-username="<?= e($account['username']) ?>"
                                            data-role="<?= e($account['role']) ?>"
                                        >

                                            <i class="bi bi-pencil"></i>

                                            Edit

                                        </button>


                                        <!-- Delete -->

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteAccountModal"
                                            data-id="<?= (int)$account['id'] ?>"
                                            data-name="<?= e($account['full_name']) ?>"
                                        >

                                            <i class="bi bi-trash"></i>

                                            Delete

                                        </button>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>


                        <?php if (!$accounts): ?>

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center py-5"
                                >

                                    <i class="bi bi-people fs-2 text-muted"></i>

                                    <p class="text-muted mt-2 mb-0">
                                        No Staff or Teacher accounts yet.
                                    </p>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    <?php endif; ?>


</div>


<?php if ($isAdmin): ?>


<!-- Add Account Modal -->

<div
    class="modal fade"
    id="addAccountModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="post">

                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="bi bi-person-plus me-2"></i>

                        Add Account

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <div class="alert alert-info small">

                        <i class="bi bi-shield-lock me-1"></i>

                        Administrator password is required.

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="new_full_name"
                            class="form-control"
                            maxlength="150"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Username
                        </label>

                        <input
                            type="text"
                            name="new_username"
                            class="form-control"
                            maxlength="80"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Role
                        </label>

                        <select
                            name="new_role"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Role
                            </option>

                            <option value="Teacher">
                                Teacher
                            </option>

                            <option value="Staff">
                                Staff
                            </option>

                        </select>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Account Password
                        </label>

                        <input
                            type="password"
                            name="new_password"
                            class="form-control"
                            minlength="6"
                            required
                        >

                    </div>


                    <div>

                        <label class="form-label">
                            Administrator Password
                        </label>

                        <input
                            type="password"
                            name="admin_password"
                            class="form-control"
                            required
                        >

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
                        name="account_action"
                        value="add"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-plus-lg me-1"></i>

                        Add Account

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- Edit Account Modal -->

<div
    class="modal fade"
    id="editAccountModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="post">

                <input
                    type="hidden"
                    name="target_id"
                    id="edit_target_id"
                >


                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="bi bi-pencil-square me-2"></i>

                        Edit Account

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <div class="alert alert-warning small">

                        <i class="bi bi-shield-lock me-1"></i>

                        Administrator password is required
                        to edit this account.

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="edit_full_name"
                            id="edit_full_name"
                            class="form-control"
                            maxlength="150"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Username
                        </label>

                        <input
                            type="text"
                            name="edit_username"
                            id="edit_username"
                            class="form-control"
                            maxlength="80"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Role
                        </label>

                        <select
                            name="edit_role"
                            id="edit_role"
                            class="form-select"
                            required
                        >

                            <option value="Teacher">
                                Teacher
                            </option>

                            <option value="Staff">
                                Staff
                            </option>

                        </select>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            New Password
                        </label>

                        <input
                            type="password"
                            name="edit_password"
                            class="form-control"
                            minlength="6"
                        >

                        <div class="form-text">
                            Leave blank to keep the current password.
                        </div>

                    </div>


                    <div>

                        <label class="form-label">
                            Administrator Password
                        </label>

                        <input
                            type="password"
                            name="admin_password"
                            class="form-control"
                            required
                        >

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
                        name="account_action"
                        value="edit"
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


<!-- Delete Account Modal -->

<div
    class="modal fade"
    id="deleteAccountModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="post">

                <input
                    type="hidden"
                    name="target_id"
                    id="delete_target_id"
                >


                <div class="modal-header">

                    <h5 class="modal-title text-danger">

                        <i class="bi bi-trash me-2"></i>

                        Delete Account

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <p>
                        Are you sure you want to delete
                        <strong id="delete_account_name"></strong>?
                    </p>


                    <div class="alert alert-danger small">

                        <i class="bi bi-exclamation-triangle me-1"></i>

                        This action cannot be undone.

                    </div>


                    <label class="form-label">
                        Administrator Password
                    </label>

                    <input
                        type="password"
                        name="admin_password"
                        class="form-control"
                        required
                    >

                    <div class="form-text">
                        Enter your Administrator password
                        to confirm deletion.
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
                        name="account_action"
                        value="delete"
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


<script>
document.addEventListener('DOMContentLoaded', function () {

    /* Edit modal */

    const editModal =
        document.getElementById('editAccountModal');

    if (editModal) {

        editModal.addEventListener(
            'show.bs.modal',
            function (event) {

                const button = event.relatedTarget;

                document.getElementById('edit_target_id').value =
                    button.getAttribute('data-id');

                document.getElementById('edit_full_name').value =
                    button.getAttribute('data-name');

                document.getElementById('edit_username').value =
                    button.getAttribute('data-username');

                document.getElementById('edit_role').value =
                    button.getAttribute('data-role');

            }
        );

    }


    /* Delete modal */

    const deleteModal =
        document.getElementById('deleteAccountModal');

    if (deleteModal) {

        deleteModal.addEventListener(
            'show.bs.modal',
            function (event) {

                const button = event.relatedTarget;

                document.getElementById('delete_target_id').value =
                    button.getAttribute('data-id');

                document.getElementById('delete_account_name').textContent =
                    button.getAttribute('data-name');

            }
        );

    }

});
</script>


<?php include 'footer.php'; ?>
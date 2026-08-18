<?php

require_once __DIR__ . '/config.php';

require_login();

/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

$userId = (int)($_SESSION['user_id'] ?? 0);
$role   = $_SESSION['role'] ?? '';

if ($userId <= 0) {
    redirect('login.php');
}

/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$userId]);

$user = $stmt->fetch();

if (!$user) {
    exit('Account not found.');
}

/*
|--------------------------------------------------------------------------
| Get Action
|--------------------------------------------------------------------------
*/

$action = $_POST['action'] ?? '';

/*
|--------------------------------------------------------------------------
| UPDATE OWN ACCOUNT
|--------------------------------------------------------------------------
*/

if ($action === 'update_account') {

    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || $username === '') {

        $_SESSION['error'] =
            'Full name and username are required.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Duplicate Username
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM users
        WHERE username = ?
        AND id != ?
        LIMIT 1
    ");

    $stmt->execute([
        $username,
        $userId
    ]);

    if ($stmt->fetch()) {

        $_SESSION['error'] =
            'Username is already being used.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Keep Existing Password
    |--------------------------------------------------------------------------
    */

    $passwordHash = $user['password'];

    /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */

    if ($newPassword !== '') {

        if ($currentPassword === '') {

            $_SESSION['error'] =
                'Enter your current password to change your password.';

            redirect('account.php');
        }

        if (!password_verify(
            $currentPassword,
            $user['password']
        )) {

            $_SESSION['error'] =
                'Current password is incorrect.';

            redirect('account.php');
        }

        if ($newPassword !== $confirmPassword) {

            $_SESSION['error'] =
                'New passwords do not match.';

            redirect('account.php');
        }

        if (strlen($newPassword) < 6) {

            $_SESSION['error'] =
                'New password must be at least 6 characters.';

            redirect('account.php');
        }

        $passwordHash = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Account
    |--------------------------------------------------------------------------
    */

    try {

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE users
            SET
                full_name = ?,
                username = ?,
                password = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $fullName,
            $username,
            $passwordHash,
            $userId
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Teacher Name
        |--------------------------------------------------------------------------
        */

        if (strcasecmp($role, 'Teacher') === 0) {

            $stmt = $pdo->prepare("
                UPDATE teachers
                SET full_name = ?
                WHERE user_id = ?
            ");

            $stmt->execute([
                $fullName,
                $userId
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Staff Table
        |--------------------------------------------------------------------------
        |
        | Your staff table DOES NOT contain user_id.
        |
        | Therefore we intentionally do not run:
        |
        | UPDATE staff WHERE user_id = ?
        |
        */

        $pdo->commit();

        $_SESSION['full_name'] = $fullName;
        $_SESSION['username']  = $username;

        $_SESSION['success'] =
            'Account information successfully updated.';

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['error'] =
            'Unable to update account information: ' .
            $e->getMessage();
    }

    redirect('account.php');
}


/*
|--------------------------------------------------------------------------
| ADMIN - ADD USER
|--------------------------------------------------------------------------
*/

if ($action === 'add_user') {

    /*
    | Only Administrator can do this
    */

    if (strcasecmp($role, 'Administrator') !== 0) {

        $_SESSION['error'] =
            'Only administrators can manage user accounts.';

        redirect('account.php');
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $newRole  = trim($_POST['role'] ?? '');

    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    if (
        $fullName === '' ||
        $username === '' ||
        $newRole === '' ||
        $password === '' ||
        $confirmPassword === ''
    ) {

        $_SESSION['error'] =
            'All required fields must be completed.';

        redirect('account.php');
    }

    if (!in_array(
        $newRole,
        ['Teacher', 'Staff', 'Administrator'],
        true
    )) {

        $_SESSION['error'] =
            'Invalid user role.';

        redirect('account.php');
    }

    if ($password !== $confirmPassword) {

        $_SESSION['error'] =
            'Passwords do not match.';

        redirect('account.php');
    }

    if (strlen($password) < 6) {

        $_SESSION['error'] =
            'Password must be at least 6 characters.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Duplicate Username
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->execute([$username]);

    if ($stmt->fetch()) {

        $_SESSION['error'] =
            'Username is already being used.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    */

    try {

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare("
            INSERT INTO users (
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
            $passwordHash,
            $newRole
        ]);

        $_SESSION['success'] =
            'User account successfully created.';

    } catch (Throwable $e) {

        $_SESSION['error'] =
            'Unable to create user account: ' .
            $e->getMessage();
    }

    redirect('account.php');
}


/*
|--------------------------------------------------------------------------
| ADMIN - UPDATE USER
|--------------------------------------------------------------------------
*/

if ($action === 'admin_update_user') {

    /*
    | Only Administrator
    */

    if (strcasecmp($role, 'Administrator') !== 0) {

        $_SESSION['error'] =
            'Only administrators can manage user accounts.';

        redirect('account.php');
    }

    $targetUserId = (int)($_POST['user_id'] ?? 0);

    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $newRole  = trim($_POST['role'] ?? '');

    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($targetUserId <= 0) {

        $_SESSION['error'] =
            'Invalid user account.';

        redirect('account.php');
    }

    if (
        $fullName === '' ||
        $username === '' ||
        $newRole === ''
    ) {

        $_SESSION['error'] =
            'Full name, username, and role are required.';

        redirect('account.php');
    }

    if (!in_array(
        $newRole,
        ['Teacher', 'Staff', 'Administrator'],
        true
    )) {

        $_SESSION['error'] =
            'Invalid user role.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Check Target User
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$targetUserId]);

    $targetUser = $stmt->fetch();

    if (!$targetUser) {

        $_SESSION['error'] =
            'User account not found.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Duplicate Username
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM users
        WHERE username = ?
        AND id != ?
        LIMIT 1
    ");

    $stmt->execute([
        $username,
        $targetUserId
    ]);

    if ($stmt->fetch()) {

        $_SESSION['error'] =
            'Username is already being used.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Password
    |--------------------------------------------------------------------------
    */

    $passwordHash = $targetUser['password'];

    if ($newPassword !== '') {

        if ($newPassword !== $confirmPassword) {

            $_SESSION['error'] =
                'New passwords do not match.';

            redirect('account.php');
        }

        if (strlen($newPassword) < 6) {

            $_SESSION['error'] =
                'New password must be at least 6 characters.';

            redirect('account.php');
        }

        $passwordHash = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    try {

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE users
            SET
                full_name = ?,
                username = ?,
                password = ?,
                role = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $fullName,
            $username,
            $passwordHash,
            $newRole,
            $targetUserId
        ]);

        /*
        |--------------------------------------------------------------------------
        | Keep Teacher Profile Name Synchronized
        |--------------------------------------------------------------------------
        */

        if (strcasecmp($newRole, 'Teacher') === 0) {

            $stmt = $pdo->prepare("
                UPDATE teachers
                SET full_name = ?
                WHERE user_id = ?
            ");

            $stmt->execute([
                $fullName,
                $targetUserId
            ]);
        }

        $pdo->commit();

        $_SESSION['success'] =
            'User account successfully updated.';

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['error'] =
            'Unable to update user account: ' .
            $e->getMessage();
    }

    redirect('account.php');
}


/*
|--------------------------------------------------------------------------
| ADMIN - DELETE USER
|--------------------------------------------------------------------------
*/

if ($action === 'delete_user') {

    /*
    |--------------------------------------------------------------------------
    | Administrator Only
    |--------------------------------------------------------------------------
    */

    if (strcasecmp($role, 'Administrator') !== 0) {

        $_SESSION['error'] =
            'Only administrators can delete user accounts.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Target User
    |--------------------------------------------------------------------------
    */

    $targetUserId = (int)($_POST['user_id'] ?? 0);

    /*
    |--------------------------------------------------------------------------
    | Administrator Password
    |--------------------------------------------------------------------------
    */

    $adminPassword = $_POST['admin_password'] ?? '';

    if ($targetUserId <= 0) {

        $_SESSION['error'] =
            'Invalid user account.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Never Allow Admin To Delete Own Account
    |--------------------------------------------------------------------------
    */

    if ($targetUserId === $userId) {

        $_SESSION['error'] =
            'You cannot delete your own administrator account.';

        redirect('account.php');
    }

    if ($adminPassword === '') {

        $_SESSION['error'] =
            'Enter your administrator password to continue.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Verify Administrator Password
    |--------------------------------------------------------------------------
    */

    if (!password_verify(
        $adminPassword,
        $user['password']
    )) {

        /*
        | Return a special flag so account.php can show popup
        */

        $_SESSION['delete_password_error'] =
            'Incorrect administrator password. The account was not deleted.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Get Target Account
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$targetUserId]);

    $targetUser = $stmt->fetch();

    if (!$targetUser) {

        $_SESSION['error'] =
            'User account not found.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Account
    |--------------------------------------------------------------------------
    */

    try {

        $pdo->beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | Delete Teacher Profile
        |--------------------------------------------------------------------------
        |
        | teachers HAS user_id, so this is safe.
        */

        $stmt = $pdo->prepare("
            DELETE FROM teachers
            WHERE user_id = ?
        ");

        $stmt->execute([
            $targetUserId
        ]);

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT:
        |
        | staff DOES NOT HAVE user_id according to your database.
        |
        | Therefore we DO NOT run:
        |
        | DELETE FROM staff WHERE user_id = ?
        |
        | because that caused your previous SQL error.
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Delete User
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            DELETE FROM users
            WHERE id = ?
        ");

        $stmt->execute([
            $targetUserId
        ]);

        if ($stmt->rowCount() !== 1) {

            throw new RuntimeException(
                'The user account could not be deleted.'
            );
        }

        $pdo->commit();

        $_SESSION['delete_success'] =
            'User account "' .
            $targetUser['username'] .
            '" was successfully deleted.';

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['error'] =
            'Unable to delete user account: ' .
            $e->getMessage();
    }

    redirect('account.php');
}


/*
|--------------------------------------------------------------------------
| UPDATE TEACHER / STAFF PROFILE
|--------------------------------------------------------------------------
*/

if ($action === 'update_profile') {

    $profileType = $_POST['profile_type'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    $employeeId = trim(
        $_POST['employee_id'] ?? ''
    );

    $plantillaNo = trim(
        $_POST['plantilla_no'] ?? ''
    );

    $firstDay = $_POST['first_day_of_service'] ?? null;

    $appointment = trim(
        $_POST['current_latest_appointment'] ?? ''
    );

    $positionDepartment = trim(
        $_POST['position_department'] ?? ''
    );

    $contactNumber = trim(
        $_POST['contact_number'] ?? ''
    );

    $depedEmail = trim(
        $_POST['deped_email'] ?? ''
    );

    $personalEmail = trim(
        $_POST['personal_email'] ?? ''
    );

    $birthdate = $_POST['birthdate'] ?? null;

    $gender = trim(
        $_POST['gender'] ?? ''
    );

    $address = trim(
        $_POST['address'] ?? ''
    );

    $degreeFinished = trim(
        $_POST['degree_finished'] ?? ''
    );

    $specialization = trim(
        $_POST['specialization_prc_eligibility'] ?? ''
    );

    $tinNo = trim(
        $_POST['tin_no'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | Empty Dates -> NULL
    |--------------------------------------------------------------------------
    */

    if ($firstDay === '') {
        $firstDay = null;
    }

    if ($birthdate === '') {
        $birthdate = null;
    }

    if ($appointment === '') {
        $appointment = null;
    }

    /*
    |--------------------------------------------------------------------------
    | TEACHER
    |--------------------------------------------------------------------------
    */

    if (
        $profileType === 'teacher' &&
        strcasecmp($role, 'Teacher') === 0
    ) {

        try {

            $pdo->beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Check Existing Teacher
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT id
                FROM teachers
                WHERE user_id = ?
                LIMIT 1
            ");

            $stmt->execute([
                $userId
            ]);

            $teacher = $stmt->fetch();

            /*
            |--------------------------------------------------------------------------
            | UPDATE
            |--------------------------------------------------------------------------
            */

            if ($teacher) {

                $stmt = $pdo->prepare("
                    UPDATE teachers
                    SET
                        employee_id = ?,
                        full_name = ?,
                        plantilla_no = ?,
                        first_day_of_service = ?,
                        current_latest_appointment = ?,
                        position_department = ?,
                        contact_number = ?,
                        deped_email = ?,
                        personal_email = ?,
                        birthdate = ?,
                        gender = ?,
                        address = ?,
                        degree_finished = ?,
                        specialization_prc_eligibility = ?,
                        tin_no = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE user_id = ?
                ");

                $stmt->execute([
                    $employeeId,
                    $user['full_name'],
                    $plantillaNo,
                    $firstDay,
                    $appointment,
                    $positionDepartment,
                    $contactNumber,
                    $depedEmail,
                    $personalEmail,
                    $birthdate,
                    $gender,
                    $address,
                    $degreeFinished,
                    $specialization,
                    $tinNo,
                    $userId
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | INSERT
            |--------------------------------------------------------------------------
            */

            else {

                $stmt = $pdo->prepare("
                    INSERT INTO teachers (
                        user_id,
                        employee_id,
                        full_name,
                        plantilla_no,
                        first_day_of_service,
                        current_latest_appointment,
                        position_department,
                        contact_number,
                        deped_email,
                        personal_email,
                        birthdate,
                        gender,
                        address,
                        degree_finished,
                        specialization_prc_eligibility,
                        tin_no
                    )
                    VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ");

                $stmt->execute([
                    $userId,
                    $employeeId,
                    $user['full_name'],
                    $plantillaNo,
                    $firstDay,
                    $appointment,
                    $positionDepartment,
                    $contactNumber,
                    $depedEmail,
                    $personalEmail,
                    $birthdate,
                    $gender,
                    $address,
                    $degreeFinished,
                    $specialization,
                    $tinNo
                ]);
            }

            $pdo->commit();

            $_SESSION['success'] =
                'Teacher information successfully saved.';

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if (
                (($e->errorInfo[0] ?? '') === '23000') &&
                (($e->errorInfo[1] ?? 0) == 1062)
            ) {

                $_SESSION['error'] =
                    'The Employee No. "' .
                    $employeeId .
                    '" is already assigned to another teacher. Please enter a different Employee No.';

            } else {

                $_SESSION['error'] =
                    'Unable to save teacher information: ' .
                    $e->getMessage();
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $_SESSION['error'] =
                'Unable to save teacher information: ' .
                $e->getMessage();
        }

        redirect('account.php');
    }


    /*
    |--------------------------------------------------------------------------
    | STAFF
    |--------------------------------------------------------------------------
    |
    | Your current staff table does not contain user_id.
    | This section only works if you are editing a staff profile
    | through another mechanism that identifies the staff record.
    |
    | We intentionally do not use staff.user_id.
    |--------------------------------------------------------------------------
    */

    if (
        $profileType === 'staff' &&
        strcasecmp($role, 'Staff') === 0
    ) {

        $_SESSION['error'] =
            'Staff profile editing requires a staff account link because the staff table does not contain user_id.';

        redirect('account.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Invalid Profile
    |--------------------------------------------------------------------------
    */

    $_SESSION['error'] =
        'Invalid profile update request.';

    redirect('account.php');
}


/*
|--------------------------------------------------------------------------
| Invalid Request
|--------------------------------------------------------------------------
*/

$_SESSION['error'] =
    'Invalid request.';

redirect('account.php');
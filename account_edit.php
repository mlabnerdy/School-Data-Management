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
| UPDATE ACCOUNT
|--------------------------------------------------------------------------
*/

if ($action === 'update_account') {

    $fullName = trim(
        $_POST['full_name'] ?? ''
    );

    $username = trim(
        $_POST['username'] ?? ''
    );

    $currentPassword = $_POST['current_password'] ?? '';

    $newPassword = $_POST['new_password'] ?? '';

    $confirmPassword = $_POST['confirm_password'] ?? '';


    /*
    | Required fields
    */

    if ($fullName === '' || $username === '') {

        $_SESSION['error'] =
            'Full name and username are required.';

        redirect('account.php');
    }


    /*
    | Check duplicate username
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
    | Keep current password
    */

    $passwordHash = $user['password'];


    /*
    | Change password
    */

    if ($newPassword !== '') {

        if ($currentPassword === '') {

            $_SESSION['error'] =
                'Enter your current password to change your password.';

            redirect('account.php');
        }


        if (
            !password_verify(
                $currentPassword,
                $user['password']
            )
        ) {

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
    | Update account
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
        | Update linked teacher name
        */

        if (
            strcasecmp($role, 'Teacher') === 0
        ) {

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
        | Update linked staff name
        */

        if (
            strcasecmp($role, 'Staff') === 0
        ) {

            $stmt = $pdo->prepare("
                UPDATE staff
                SET full_name = ?
                WHERE user_id = ?
            ");

            $stmt->execute([
                $fullName,
                $userId
            ]);
        }


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
            'Unable to update account information: '
            . $e->getMessage();
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
    | Convert empty dates to NULL
    |--------------------------------------------------------------------------
    */

    if ($firstDay === '') {
        $firstDay = null;
    }

    if ($birthdate === '') {
        $birthdate = null;
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
            | Check if teacher already exists
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
            | Update existing teacher
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
            | Create teacher record
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


            /*
            | Commit
            */

            $pdo->commit();


            $_SESSION['success'] =
                'Teacher information successfully saved.';

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }


            /*
            | Duplicate Employee No.
            */

            if (
                $e->errorInfo[0] ?? '' === '23000' &&
                ($e->errorInfo[1] ?? 0) == 1062
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
    */

    if (
        $profileType === 'staff' &&
        strcasecmp($role, 'Staff') === 0
    ) {

        try {

            $pdo->beginTransaction();


            /*
            | Check existing staff
            */

            $stmt = $pdo->prepare("
                SELECT id
                FROM staff
                WHERE user_id = ?
                LIMIT 1
            ");

            $stmt->execute([
                $userId
            ]);

            $staff = $stmt->fetch();


            /*
            |--------------------------------------------------------------------------
            | Update existing staff
            |--------------------------------------------------------------------------
            */

            if ($staff) {

                $stmt = $pdo->prepare("
                    UPDATE staff
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
            | Create staff record
            |--------------------------------------------------------------------------
            */

            else {

                $stmt = $pdo->prepare("
                    INSERT INTO staff (
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


            /*
            | Commit
            */

            $pdo->commit();


            $_SESSION['success'] =
                'Staff information successfully saved.';

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }


            /*
            | Duplicate Employee No.
            */

            if (
                ($e->errorInfo[0] ?? '') === '23000' &&
                ($e->errorInfo[1] ?? 0) == 1062
            ) {

                $_SESSION['error'] =
                    'The Employee No. "' .
                    $employeeId .
                    '" is already assigned to another staff member. Please enter a different Employee No.';

            } else {

                $_SESSION['error'] =
                    'Unable to save staff information: ' .
                    $e->getMessage();
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }


            $_SESSION['error'] =
                'Unable to save staff information: ' .
                $e->getMessage();
        }


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
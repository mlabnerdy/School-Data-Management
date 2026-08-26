<?php

require_once __DIR__ . '/config.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;

$record = [];
$error = '';

/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

$currentUserId = (int)($_SESSION['user_id'] ?? 0);


/*
|--------------------------------------------------------------------------
| Load Existing Teacher
|--------------------------------------------------------------------------
*/

if ($isEdit) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM teachers
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $record = $stmt->fetch();

    if (!$record) {

        redirect('teachers.php');

    }

}


/*
|--------------------------------------------------------------------------
| Save Teacher
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [

        /*
        | Basic Information
        */

        'employee_id' => trim(
            $_POST['employee_id'] ?? ''
        ),

        'full_name' => trim(
            $_POST['full_name'] ?? ''
        ),

        'date_of_birth' => !empty(
            $_POST['date_of_birth']
        )
            ? $_POST['date_of_birth']
            : null,

        'gender' => trim(
            $_POST['gender'] ?? ''
        ),

        'contact_number' => trim(
            $_POST['contact_number'] ?? ''
        ),

        'address' => trim(
            $_POST['address'] ?? ''
        ),


        /*
        | Employment Information
        */

        'plantilla_no' => trim(
            $_POST['plantilla_no'] ?? ''
        ),

        'tin_no' => trim(
            $_POST['tin_no'] ?? ''
        ),

        'first_day_of_service' => !empty(
            $_POST['first_day_of_service']
        )
            ? $_POST['first_day_of_service']
            : null,

        'position_department' => trim(
            $_POST['position_department'] ?? ''
        ),

        'current_latest_appointment' => trim(
            $_POST['current_latest_appointment'] ?? ''
        ),


        /*
        | Education
        */

        'degree_finished' => trim(
            $_POST['degree_finished'] ?? ''
        ),

        'specialization_prc_eligibility' => trim(
            $_POST['specialization_prc_eligibility'] ?? ''
        ),


        /*
        | Contact Information
        */

        'deped_email' => trim(
            $_POST['deped_email'] ?? ''
        ),

        'personal_email' => trim(
            $_POST['personal_email'] ?? ''
        ),


        /*
        | Other Information
        */

        'other_info' => trim(
            $_POST['other_info'] ?? ''
        ),


        /*
        |--------------------------------------------------------------------------
        | Planner #2 - Teacher & Staff Information
        |--------------------------------------------------------------------------
        */

        'gsis_no' => trim(
            $_POST['gsis_no'] ?? ''
        ),

        'philhealth_no' => trim(
            $_POST['philhealth_no'] ?? ''
        ),

        'salary_grade' => trim(
            $_POST['salary_grade'] ?? ''
        ),

        'step' => trim(
            $_POST['step'] ?? ''
        ),

        'training_accomplishments' => trim(
            $_POST['training_accomplishments'] ?? ''
        )

    ];


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */


    /*
    | Employee ID
    */

    if (
        $data['employee_id'] !== '' &&
        !preg_match(
            '/^[A-Za-z0-9\-]+$/',
            $data['employee_id']
        )
    ) {

        $error =
            'Employee ID may only contain letters, numbers, and hyphens.';

    }


    /*
    | Full Name
    */

    elseif ($data['full_name'] === '') {

        $error =
            'Full Name is required.';

    }

    elseif (
        !preg_match(
            "/^[A-Za-zÀ-ÿ .'-]+$/",
            $data['full_name']
        )
    ) {

        $error =
            'Full Name must not contain numbers or invalid characters.';

    }


    /*
    | Contact Number
    */

    elseif (
        $data['contact_number'] !== '' &&
        !preg_match(
            '/^09[0-9]{9}$/',
            $data['contact_number']
        )
    ) {

        $error =
            'Mobile Number must be a valid Philippine mobile number.';

    }


    /*
    | TIN
    */

    elseif (
        $data['tin_no'] !== '' &&
        !preg_match(
            '/^[0-9\-]+$/',
            $data['tin_no']
        )
    ) {

        $error =
            'TIN No. must contain numbers and hyphens only.';

    }


    /*
    |--------------------------------------------------------------------------
    | Planner #2 Validation
    |--------------------------------------------------------------------------
    */


    /*
    | GSIS No. - Numbers Only
    */

    elseif (
        $data['gsis_no'] !== '' &&
        !preg_match(
            '/^[0-9]+$/',
            $data['gsis_no']
        )
    ) {

        $error =
            'GSIS No. must contain numbers only.';

    }


    /*
    | PhilHealth No. - Numbers Only
    */

    elseif (
        $data['philhealth_no'] !== '' &&
        !preg_match(
            '/^[0-9]+$/',
            $data['philhealth_no']
        )
    ) {

        $error =
            'PhilHealth No. must contain numbers only.';

    }


    /*
    | Salary Grade - 1 to 33
    */

    elseif (
        $data['salary_grade'] !== '' &&
        (
            !ctype_digit($data['salary_grade']) ||
            (int)$data['salary_grade'] < 1 ||
            (int)$data['salary_grade'] > 33
        )
    ) {

        $error =
            'Salary Grade must be between SG 1 and SG 33.';

    }


    /*
    | Step - 1 to 8
    */

    elseif (
        $data['step'] !== '' &&
        (
            !ctype_digit($data['step']) ||
            (int)$data['step'] < 1 ||
            (int)$data['step'] > 8
        )
    ) {

        $error =
            'Step must be between Step 1 and Step 8.';

    }


    /*
    | Email Validation
    */

    elseif (
        $data['deped_email'] !== '' &&
        !filter_var(
            $data['deped_email'],
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Please enter a valid DepEd email address.';

    }


    elseif (
        $data['personal_email'] !== '' &&
        !filter_var(
            $data['personal_email'],
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Please enter a valid personal email address.';

    }


    /*
    |--------------------------------------------------------------------------
    | Save to Database
    |--------------------------------------------------------------------------
    */

    if (!$error) {

        try {


            /*
            |--------------------------------------------------------------------------
            | EDIT EXISTING TEACHER
            |--------------------------------------------------------------------------
            */

            if ($isEdit) {

                $sql = "

                    UPDATE teachers

                    SET

                        employee_id = ?,
                        full_name = ?,
                        date_of_birth = ?,
                        gender = ?,
                        contact_number = ?,
                        address = ?,

                        plantilla_no = ?,
                        tin_no = ?,
                        first_day_of_service = ?,
                        position_department = ?,
                        current_latest_appointment = ?,

                        degree_finished = ?,
                        specialization_prc_eligibility = ?,

                        deped_email = ?,
                        personal_email = ?,

                        other_info = ?,

                        gsis_no = ?,
                        philhealth_no = ?,
                        salary_grade = ?,
                        step = ?,
                        training_accomplishments = ?,

                        updated_by = ?,
                        updated_at = NOW()

                    WHERE id = ?

                ";


                $params = [

                    $data['employee_id'],

                    $data['full_name'],

                    $data['date_of_birth'],

                    $data['gender'],

                    $data['contact_number'],

                    $data['address'],

                    $data['plantilla_no'],

                    $data['tin_no'],

                    $data['first_day_of_service'],

                    $data['position_department'],

                    $data['current_latest_appointment'],

                    $data['degree_finished'],

                    $data['specialization_prc_eligibility'],

                    $data['deped_email'],

                    $data['personal_email'],

                    $data['other_info'],

                    /*
                    | Planner #2
                    */

                    $data['gsis_no'],

                    $data['philhealth_no'],

                    $data['salary_grade'],

                    $data['step'],

                    $data['training_accomplishments'],

                    /*
                    | Audit
                    */

                    $currentUserId,

                    $id

                ];


                $stmt = $pdo->prepare($sql);

                $stmt->execute($params);

            }


            /*
            |--------------------------------------------------------------------------
            | CREATE NEW TEACHER
            |--------------------------------------------------------------------------
            */

            else {

                $sql = "

                    INSERT INTO teachers (

                        employee_id,
                        full_name,
                        date_of_birth,
                        gender,
                        contact_number,
                        address,

                        plantilla_no,
                        tin_no,
                        first_day_of_service,
                        position_department,
                        current_latest_appointment,

                        degree_finished,
                        specialization_prc_eligibility,

                        deped_email,
                        personal_email,

                        other_info,

                        gsis_no,
                        philhealth_no,
                        salary_grade,
                        step,
                        training_accomplishments,

                        created_by

                    )

                    VALUES (

                        ?, ?, ?, ?, ?, ?,

                        ?, ?, ?, ?, ?,

                        ?, ?,

                        ?, ?,

                        ?,

                        ?, ?, ?, ?, ?,

                        ?

                    )

                ";


                $params = [

                    $data['employee_id'],

                    $data['full_name'],

                    $data['date_of_birth'],

                    $data['gender'],

                    $data['contact_number'],

                    $data['address'],

                    $data['plantilla_no'],

                    $data['tin_no'],

                    $data['first_day_of_service'],

                    $data['position_department'],

                    $data['current_latest_appointment'],

                    $data['degree_finished'],

                    $data['specialization_prc_eligibility'],

                    $data['deped_email'],

                    $data['personal_email'],

                    $data['other_info'],

                    /*
                    | Planner #2
                    */

                    $data['gsis_no'],

                    $data['philhealth_no'],

                    $data['salary_grade'],

                    $data['step'],

                    $data['training_accomplishments'],

                    /*
                    | Audit
                    */

                    $currentUserId

                ];


                $stmt = $pdo->prepare($sql);

                $stmt->execute($params);


                /*
                | Get new teacher ID
                */

                $id = (int)$pdo->lastInsertId();

                $isEdit = true;

            }


            /*
            |--------------------------------------------------------------------------
            | Photo Upload
            |--------------------------------------------------------------------------
            */

            if (
                !empty($_FILES['photo']['name']) &&
                $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                $newPhoto = upload_file(
                    'photo',
                    'teacher',
                    [
                        'jpg',
                        'jpeg',
                        'png',
                        'webp'
                    ],
                    5242880
                );


                if ($newPhoto) {


                    /*
                    | Delete old photo
                    */

                    if (!empty($record['photo'])) {

                        delete_upload(
                            $record['photo']
                        );

                    }


                    $stmt = $pdo->prepare("

                        UPDATE teachers

                        SET photo = ?

                        WHERE id = ?

                    ");

                    $stmt->execute([

                        $newPhoto,

                        $id

                    ]);

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Redirect
            |--------------------------------------------------------------------------
            */

            redirect(
                "teacher_view.php?id=" . $id
            );


        } catch (PDOException $e) {

            $error =
                'Could not save the teacher record.';

        }

    }

}


/*
|--------------------------------------------------------------------------
| Reload Record
|--------------------------------------------------------------------------
*/

if ($isEdit) {

    $stmt = $pdo->prepare("

        SELECT *

        FROM teachers

        WHERE id = ?

        LIMIT 1

    ");

    $stmt->execute([$id]);

    $record = $stmt->fetch() ?: [];

}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $record = $_POST;

}


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = $isEdit
    ? 'Edit Teacher'
    : 'Add Teacher';

include 'header.php';

?>

<link
    rel="stylesheet"
    href="assets/teacher_form.css"
>


<div class="teacher-form-page">


    <!-- ==================================================
         HEADER
    =================================================== -->

    <div class="teacher-form-header">

        <div>

            <div class="teacher-form-label">

                TEACHER RECORD

            </div>


            <h2 class="fw-bold mb-1">

                <?= $isEdit
                    ? 'Edit Teacher'
                    : 'Add Teacher' ?>

            </h2>


            <p class="text-muted mb-0">

                <?= $isEdit

                    ? 'Update the teacher information below.'

                    : 'Add a new teacher record to the system.'
                ?>

            </p>

        </div>


        <a
            href="teachers.php"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Back to List

        </a>

    </div>


    <!-- ==================================================
         ERROR
    =================================================== -->

    <?php if ($error): ?>

        <div class="alert alert-danger teacher-form-alert">

            <i class="bi bi-exclamation-circle-fill me-2"></i>

            <?= e($error) ?>

        </div>

    <?php endif; ?>


    <!-- ==================================================
         FORM
    =================================================== -->

    <form
        method="post"
        enctype="multipart/form-data"
        id="teacherForm"
        novalidate
    >


        <div class="row g-4">


            <!-- ==================================================
                 LEFT COLUMN
            =================================================== -->

            <div class="col-12 col-lg-8">


                <!-- ==================================================
                     BASIC INFORMATION
                =================================================== -->

                <div class="teacher-form-card">


                    <div class="teacher-form-card-header">


                        <div class="teacher-section-icon">

                            <i class="bi bi-person-vcard"></i>

                        </div>


                        <div>

                            <h5>

                                Basic Information

                            </h5>


                            <p>

                                Enter the teacher's basic information.

                            </p>

                        </div>


                    </div>


                    <div class="teacher-form-card-body">


                        <div class="row g-3">


                            <!-- Employee ID -->

                            <div class="col-12 col-md-6">

                                <label
                                    for="employee_id"
                                    class="form-label"
                                >

                                    Employee ID

                                </label>


                                <input
                                    type="text"
                                    name="employee_id"
                                    id="employee_id"
                                    class="form-control"
                                    value="<?= e(
                                        $record['employee_id'] ?? ''
                                    ) ?>"
                                    maxlength="30"
                                    placeholder="Employee ID"
                                >

                            </div>


                            <!-- Full Name -->

                            <div class="col-12">

                                <label
                                    for="full_name"
                                    class="form-label"
                                >

                                    Full Name

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>


                                <input
                                    type="text"
                                    name="full_name"
                                    id="full_name"
                                    class="form-control"
                                    value="<?= e(
                                        $record['full_name'] ?? ''
                                    ) ?>"
                                    maxlength="100"
                                    placeholder="e.g. Juan Dela Cruz"
                                    required
                                >

                            </div>


                            <!-- Birthdate -->

                            <div class="col-12 col-md-4">

                                <label
                                    for="date_of_birth"
                                    class="form-label"
                                >

                                    Birthdate

                                </label>


                                <input
                                    type="date"
                                    name="date_of_birth"
                                    id="date_of_birth"
                                    class="form-control"
                                    value="<?= e(
                                        $record['date_of_birth'] ?? ''
                                    ) ?>"
                                >

                            </div>


                            <!-- Gender -->

                            <div class="col-12 col-md-4">

                                <label
                                    for="gender"
                                    class="form-label"
                                >

                                    Gender

                                </label>


                                <select
                                    name="gender"
                                    id="gender"
                                    class="form-select"
                                >

                                    <option value="">
                                        Select Gender
                                    </option>


                                    <?php
                                    $genders = [
                                        'Male',
                                        'Female',
                                        'Other'
                                    ];
                                    ?>


                                    <?php foreach ($genders as $gender): ?>

                                        <option
                                            value="<?= e($gender) ?>"
                                            <?= (
                                                ($record['gender'] ?? '')
                                                === $gender
                                            )
                                                ? 'selected'
                                                : '' ?>
                                        >

                                            <?= e($gender) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- Contact -->

                            <div class="col-12 col-md-4">

                                <label
                                    for="contact_number"
                                    class="form-label"
                                >

                                    Mobile Number

                                </label>


                                <input
                                    type="tel"
                                    name="contact_number"
                                    id="contact_number"
                                    class="form-control"
                                    value="<?= e(
                                        $record['contact_number'] ?? ''
                                    ) ?>"
                                    maxlength="11"
                                    pattern="09[0-9]{9}"
                                    inputmode="numeric"
                                    placeholder="09XXXXXXXXX"
                                >

                            </div>


                            <!-- Address -->

                            <div class="col-12">

                                <label
                                    for="address"
                                    class="form-label"
                                >

                                    Address

                                </label>


                                <textarea
                                    name="address"
                                    id="address"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Enter complete address"
                                ><?= e(
                                    $record['address'] ?? ''
                                ) ?></textarea>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     EMPLOYMENT INFORMATION
                =================================================== -->

                <div class="teacher-form-card mt-4">


                    <div class="teacher-form-card-header">


                        <div class="teacher-section-icon">

                            <i class="bi bi-briefcase"></i>

                        </div>


                        <div>

                            <h5>

                                Employment Information

                            </h5>


                            <p>

                                Enter employment and appointment details.

                            </p>

                        </div>


                    </div>


                    <div class="teacher-form-card-body">


                        <div class="row g-3">


                            <!-- Plantilla -->

                            <div class="col-12 col-md-6">

                                <label
                                    for="plantilla_no"
                                    class="form-label"
                                >

                                    Plantilla No.

                                </label>


                                <input
                                    type="text"
                                    name="plantilla_no"
                                    id="plantilla_no"
                                    class="form-control"
                                    value="<?= e(
                                        $record['plantilla_no'] ?? ''
                                    ) ?>"
                                    maxlength="50"
                                >

                            </div>


                            <!-- TIN -->

                            <div class="col-12 col-md-6">

                                <label
                                    for="tin_no"
                                    class="form-label"
                                >

                                    TIN No.

                                </label>


                                <input
                                    type="text"
                                    name="tin_no"
                                    id="tin_no"
                                    class="form-control"
                                    value="<?= e(
                                        $record['tin_no'] ?? ''
                                    ) ?>"
                                    maxlength="30"
                                    placeholder="TIN number"
                                >

                            </div>


                            <!-- First Day -->

                            <div class="col-12 col-md-6">

                                <label
                                    for="first_day_of_service"
                                    class="form-label"
                                >

                                    First Day of Service

                                </label>


                                <input
                                    type="date"
                                    name="first_day_of_service"
                                    id="first_day_of_service"
                                    class="form-control"
                                    value="<?= e(
                                        $record['first_day_of_service'] ?? ''
                                    ) ?>"
                                >

                            </div>


                            <!-- Position -->

                            <div class="col-12 col-md-6">

                                <label
                                    for="position_department"
                                    class="form-label"
                                >

                                    Position / Department

                                </label>


                                <input
                                    type="text"
                                    name="position_department"
                                    id="position_department"
                                    class="form-control"
                                    value="<?= e(
                                        $record['position_department'] ?? ''
                                    ) ?>"
                                    maxlength="100"
                                >

                            </div>


                            <!-- Appointment -->

                            <div class="col-12">

                                <label
                                    for="current_latest_appointment"
                                    class="form-label"
                                >

                                    Current / Latest Appointment

                                </label>


                                <textarea
                                    name="current_latest_appointment"
                                    id="current_latest_appointment"
                                    class="form-control"
                                    rows="3"
                                ><?= e(
                                    $record['current_latest_appointment'] ?? ''
                                ) ?></textarea>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     TEACHER & STAFF INFORMATION
                     PLANNER #2
                =================================================== -->

                <div class="teacher-form-card mt-4">


                    <div class="teacher-form-card-header">


                        <div class="teacher-section-icon">

                            <i class="bi bi-person-badge"></i>

                        </div>


                        <div>

                            <h5>

                                Teacher & Staff Information

                            </h5>


                            <p>

                                Government identification,
                                salary grade, step, and accomplishments.

                            </p>

                        </div>


                    </div>


                    <div class="teacher-form-card-body">


                        <div class="row g-3">


                            <!-- GSIS -->

                            <div class="col-12 col-md-6">

                                <label
                                    for="gsis_no"
                                    class="form-label"
                                >

                                    GSIS No.

                                </label>


                                <input
                                    type="text"
                                    name="gsis_no"
                                    id="gsis_no"
                                    class="form-control"
                                    value="<?= e(
                                        $record['gsis_no'] ?? ''
                                    ) ?>"
                                    maxlength="30"
                                    inputmode="numeric"
                                    pattern="[0-9]+"
                                    placeholder="Numbers only"
                                >


                                <div class="form-text">

                                    Numbers only.

                                </div>

                            </div>


                            <!-- PhilHealth -->

                            <div class="col-12 col-md-6">

                                <label
                                    for="philhealth_no"
                                    class="form-label"
                                >

                                    PhilHealth No.

                                </label>


                                <input
                                    type="text"
                                    name="philhealth_no"
                                    id="philhealth_no"
                                    class="form-control"
                                    value="<?= e(
                                        $record['philhealth_no'] ?? ''
                                    ) ?>"
                                    maxlength="30"
                                    inputmode="numeric"
                                    pattern="[0-9]+"
                                    placeholder="Numbers only"
                                >


                                <div class="form-text">

                                    Numbers only.

                                </div>

                            </div>


                            <!-- Salary Grade -->

                            <div class="col-12 col-md-6">

                                <label
                                    for="salary_grade"
                                    class="form-label"
                                >

                                    Salary Grade

                                </label>


                                <select
                                    name="salary_grade"
                                    id="salary_grade"
                                    class="form-select"
                                >

                                    <option value="">
                                        Select Salary Grade
                                    </option>


                                    <?php
                                    for (
                                        $sg = 1;
                                        $sg <= 33;
                                        $sg++
                                    ):
                                    ?>

                                        <option
                                            value="<?= $sg ?>"
                                            <?= (
                                                (string)(
                                                    $record['salary_grade']
                                                    ?? ''
                                                )
                                                === (string)$sg
                                            )
                                                ? 'selected'
                                                : '' ?>
                                        >

                                            SG <?= $sg ?>

                                        </option>

                                    <?php endfor; ?>

                                </select>


                                <div class="form-text">

                                    Select SG 1–33.

                                </div>

                            </div>


                            <!-- Step -->

                            <div class="col-12 col-md-6">

                                <label
                                    for="step"
                                    class="form-label"
                                >

                                    Step

                                </label>


                                <select
                                    name="step"
                                    id="step"
                                    class="form-select"
                                >

                                    <option value="">
                                        Select Step
                                    </option>


                                    <?php
                                    for (
                                        $step = 1;
                                        $step <= 8;
                                        $step++
                                    ):
                                    ?>

                                        <option
                                            value="<?= $step ?>"
                                            <?= (
                                                (string)(
                                                    $record['step']
                                                    ?? ''
                                                )
                                                === (string)$step
                                            )
                                                ? 'selected'
                                                : '' ?>
                                        >

                                            Step <?= $step ?>

                                        </option>

                                    <?php endfor; ?>

                                </select>


                                <div class="form-text">

                                    Select Step 1–8.

                                </div>

                            </div>


                            <!-- Training -->

                            <div class="col-12">

                                <label
                                    for="training_accomplishments"
                                    class="form-label"
                                >

                                    Training and Accomplishments

                                </label>


                                <textarea
                                    name="training_accomplishments"
                                    id="training_accomplishments"
                                    class="form-control"
                                    rows="6"
                                    maxlength="5000"
                                    placeholder="Enter trainings, seminars, certificates, awards, accomplishments, and other relevant achievements..."
                                ><?= e(
                                    $record[
                                        'training_accomplishments'
                                    ] ?? ''
                                ) ?></textarea>


                                <div class="form-text">

                                    You may enter multiple trainings
                                    and accomplishments.

                                </div>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     EDUCATION
                =================================================== -->

                <div class="teacher-form-card mt-4">


                    <div class="teacher-form-card-header">


                        <div class="teacher-section-icon">

                            <i class="bi bi-mortarboard"></i>

                        </div>


                        <div>

                            <h5>

                                Education

                            </h5>


                            <p>

                                Enter educational background
                                and eligibility information.

                            </p>

                        </div>

                    </div>


                    <div class="teacher-form-card-body">


                        <div class="row g-3">


                            <!-- Degree -->

                            <div class="col-12">

                                <label
                                    for="degree_finished"
                                    class="form-label"
                                >

                                    Degree Finished

                                </label>


                                <textarea
                                    name="degree_finished"
                                    id="degree_finished"
                                    class="form-control"
                                    rows="3"
                                ><?= e(
                                    $record['degree_finished'] ?? ''
                                ) ?></textarea>

                            </div>


                            <!-- Specialization -->

                            <div class="col-12">

                                <label
                                    for="specialization_prc_eligibility"
                                    class="form-label"
                                >

                                    Specialization / PRC Eligibility

                                </label>


                                <textarea
                                    name="specialization_prc_eligibility"
                                    id="specialization_prc_eligibility"
                                    class="form-control"
                                    rows="3"
                                ><?= e(
                                    $record[
                                        'specialization_prc_eligibility'
                                    ] ?? ''
                                ) ?></textarea>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     CONTACT INFORMATION
                =================================================== -->

                <div class="teacher-form-card mt-4">


                    <div class="teacher-form-card-header">


                        <div class="teacher-section-icon">

                            <i class="bi bi-envelope"></i>

                        </div>


                        <div>

                            <h5>

                                Contact Information

                            </h5>


                            <p>

                                Enter DepEd and personal email addresses.

                            </p>

                        </div>

                    </div>


                    <div class="teacher-form-card-body">


                        <div class="row g-3">


                            <!-- DepEd Email -->

                            <div class="col-12 col-md-6">

                                <label
                                    for="deped_email"
                                    class="form-label"
                                >

                                    DepEd Email

                                </label>


                                <input
                                    type="email"
                                    name="deped_email"
                                    id="deped_email"
                                    class="form-control"
                                    value="<?= e(
                                        $record['deped_email'] ?? ''
                                    ) ?>"
                                    maxlength="150"
                                    placeholder="name@deped.gov.ph"
                                >

                            </div>


                            <!-- Personal Email -->

                            <div class="col-12 col-md-6">

                                <label
                                    for="personal_email"
                                    class="form-label"
                                >

                                    Personal Email

                                </label>


                                <input
                                    type="email"
                                    name="personal_email"
                                    id="personal_email"
                                    class="form-control"
                                    value="<?= e(
                                        $record['personal_email'] ?? ''
                                    ) ?>"
                                    maxlength="150"
                                >

                            </div>


                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     OTHER INFORMATION
                =================================================== -->

                <div class="teacher-form-card mt-4">


                    <div class="teacher-form-card-header">


                        <div class="teacher-section-icon">

                            <i class="bi bi-info-circle"></i>

                        </div>


                        <div>

                            <h5>

                                Other Information

                            </h5>


                            <p>

                                Add any other relevant information.

                            </p>

                        </div>

                    </div>


                    <div class="teacher-form-card-body">


                        <textarea
                            name="other_info"
                            id="other_info"
                            class="form-control"
                            rows="5"
                            placeholder="Additional information..."
                        ><?= e(
                            $record['other_info'] ?? ''
                        ) ?></textarea>


                    </div>

                </div>


            </div>


            <!-- ==================================================
                 RIGHT COLUMN
            =================================================== -->

            <div class="col-12 col-lg-4">


                <!-- ==================================================
                     PROFILE PHOTO
                =================================================== -->

                <div class="teacher-form-card teacher-photo-card">


                    <div class="teacher-form-card-header">


                        <div class="teacher-section-icon">

                            <i class="bi bi-camera"></i>

                        </div>


                        <div>

                            <h5>

                                Profile Photo

                            </h5>


                            <p>

                                Upload a teacher profile picture.

                            </p>

                        </div>

                    </div>


                    <div class="teacher-photo-body">


                        <?php if (!empty($record['photo'])): ?>


                            <img
                                src="<?= e(
                                    $record['photo']
                                ) ?>"
                                alt="Teacher Photo"
                                class="teacher-profile-preview"
                            >


                        <?php else: ?>


                            <div class="teacher-photo-placeholder">

                                <i class="bi bi-person"></i>

                                <span>

                                    No photo uploaded

                                </span>

                            </div>


                        <?php endif; ?>


                        <input
                            type="file"
                            name="photo"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                        >


                        <div class="form-text">

                            JPG, JPEG, PNG, or WEBP.
                            Maximum file size: 5 MB.

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100 teacher-save-btn"
                        >

                            <i class="bi bi-check-lg me-1"></i>


                            <?= $isEdit

                                ? 'Update Teacher'

                                : 'Save Teacher'

                            ?>


                        </button>


                    </div>

                </div>


            </div>


        </div>

    </form>

</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        /*
        |--------------------------------------------------------------------------
        | Elements
        |--------------------------------------------------------------------------
        */

        const contactNumber =
            document.getElementById(
                'contact_number'
            );


        const gsisNo =
            document.getElementById(
                'gsis_no'
            );


        const philhealthNo =
            document.getElementById(
                'philhealth_no'
            );


        const employeeId =
            document.getElementById(
                'employee_id'
            );


        const fullName =
            document.getElementById(
                'full_name'
            );


        /*
        |--------------------------------------------------------------------------
        | Numbers Only Function
        |--------------------------------------------------------------------------
        */

        function numbersOnly(input) {

            if (!input) {
                return;
            }


            input.value =
                input.value.replace(
                    /\D/g,
                    ''
                );

        }


        /*
        |--------------------------------------------------------------------------
        | GSIS
        |--------------------------------------------------------------------------
        */

        if (gsisNo) {

            gsisNo.addEventListener(
                'input',
                function () {

                    numbersOnly(this);

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | PhilHealth
        |--------------------------------------------------------------------------
        */

        if (philhealthNo) {

            philhealthNo.addEventListener(
                'input',
                function () {

                    numbersOnly(this);

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Contact Number
        |--------------------------------------------------------------------------
        */

        if (contactNumber) {

            contactNumber.addEventListener(
                'input',
                function () {

                    this.value =
                        this.value
                            .replace(
                                /\D/g,
                                ''
                            )
                            .slice(
                                0,
                                11
                            );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Full Name
        |--------------------------------------------------------------------------
        */

        if (fullName) {

            fullName.addEventListener(
                'input',
                function () {

                    this.value =
                        this.value.replace(
                            /[^A-Za-zÀ-ÿ .'-]/g,
                            ''
                        );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Employee ID
        |--------------------------------------------------------------------------
        */

        if (employeeId) {

            employeeId.addEventListener(
                'input',
                function () {

                    this.value =
                        this.value.replace(
                            /[^A-Za-z0-9\-]/g,
                            ''
                        );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Form Validation
        |--------------------------------------------------------------------------
        */

        const form =
            document.getElementById(
                'teacherForm'
            );


        if (form) {

            form.addEventListener(
                'submit',
                function (event) {


                    /*
                    | GSIS
                    */

                    if (
                        gsisNo &&
                        gsisNo.value !== '' &&
                        !/^[0-9]+$/.test(
                            gsisNo.value
                        )
                    ) {

                        event.preventDefault();

                        alert(
                            'GSIS No. must contain numbers only.'
                        );

                        gsisNo.focus();

                        return;

                    }


                    /*
                    | PhilHealth
                    */

                    if (
                        philhealthNo &&
                        philhealthNo.value !== '' &&
                        !/^[0-9]+$/.test(
                            philhealthNo.value
                        )
                    ) {

                        event.preventDefault();

                        alert(
                            'PhilHealth No. must contain numbers only.'
                        );

                        philhealthNo.focus();

                        return;

                    }


                    /*
                    | Salary Grade
                    */

                    const salaryGrade =
                        document.getElementById(
                            'salary_grade'
                        );


                    if (
                        salaryGrade &&
                        salaryGrade.value !== ''
                    ) {

                        const value =
                            parseInt(
                                salaryGrade.value,
                                10
                            );


                        if (
                            isNaN(value) ||
                            value < 1 ||
                            value > 33
                        ) {

                            event.preventDefault();

                            alert(
                                'Salary Grade must be between SG 1 and SG 33.'
                            );

                            salaryGrade.focus();

                            return;

                        }

                    }


                    /*
                    | Step
                    */

                    const step =
                        document.getElementById(
                            'step'
                        );


                    if (
                        step &&
                        step.value !== ''
                    ) {

                        const value =
                            parseInt(
                                step.value,
                                10
                            );


                        if (
                            isNaN(value) ||
                            value < 1 ||
                            value > 8
                        ) {

                            event.preventDefault();

                            alert(
                                'Step must be between Step 1 and Step 8.'
                            );

                            step.focus();

                            return;

                        }

                    }

                }
            );

        }

    }
);

</script>


<?php

include 'footer.php';

?>
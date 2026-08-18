<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Teacher Form';

$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;

$record = [];
$error = '';


// ==========================================================
// LOAD EXISTING TEACHER
// ==========================================================

if ($isEdit) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM teachers
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $record = $stmt->fetch();

    if (!$record) {
        redirect('teachers.php');
    }
}


// ==========================================================
// FORM SUBMISSION
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [

        'employee_id' => trim($_POST['employee_id'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),

        'date_of_birth' => !empty($_POST['date_of_birth'])
            ? $_POST['date_of_birth']
            : null,

        'gender' => trim($_POST['gender'] ?? ''),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),

        'plantilla_no' => trim($_POST['plantilla_no'] ?? ''),
        'tin_no' => trim($_POST['tin_no'] ?? ''),
        'first_day_of_service' => !empty($_POST['first_day_of_service'])
            ? $_POST['first_day_of_service']
            : null,

        'position_department' => trim($_POST['position_department'] ?? ''),
        'current_latest_appointment' => trim(
            $_POST['current_latest_appointment'] ?? ''
        ),

        'degree_finished' => trim($_POST['degree_finished'] ?? ''),

        'specialization_prc_eligibility' => trim(
            $_POST['specialization_prc_eligibility'] ?? ''
        ),

        'deped_email' => trim($_POST['deped_email'] ?? ''),
        'personal_email' => trim($_POST['personal_email'] ?? ''),

        'other_info' => trim($_POST['other_info'] ?? '')
    ];


    // ======================================================
    // VALIDATION
    // ======================================================

    if ($data['employee_id'] === '') {

        $error = 'Teacher / Employee ID is required.';

    } elseif ($data['full_name'] === '') {

        $error = 'Full Name is required.';

    } elseif (
        $data['full_name'] !== '' &&
        !preg_match("/^[A-Za-zÀ-ÿ .'-]+$/", $data['full_name'])
    ) {

        $error = 'Full Name must not contain numbers or invalid characters.';

    } elseif (
        $data['contact_number'] !== '' &&
        !preg_match('/^09[0-9]{9}$/', $data['contact_number'])
    ) {

        $error = 'Contact Number must be a valid Philippine mobile number.';

    } elseif (
        $data['deped_email'] !== '' &&
        !filter_var($data['deped_email'], FILTER_VALIDATE_EMAIL)
    ) {

        $error = 'Please enter a valid DepEd Email.';

    } elseif (
        $data['personal_email'] !== '' &&
        !filter_var($data['personal_email'], FILTER_VALIDATE_EMAIL)
    ) {

        $error = 'Please enter a valid Personal Email.';

    }


    // ======================================================
    // SAVE RECORD
    // ======================================================

    if ($error === '') {

        try {

            if ($isEdit) {

                $sql = "
                    UPDATE teachers SET

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

                        other_info = ?

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

                    $id
                ];

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

            } else {

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

                        other_info

                    )
                    VALUES (
                        ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?,
                        ?, ?,
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

                    $data['other_info']
                ];

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $id = (int)$pdo->lastInsertId();
                $isEdit = true;
            }


            // ==================================================
            // PROFILE PHOTO
            // ==================================================

            if (
                isset($_FILES['photo']) &&
                $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                $newPhoto = upload_file(
                    'photo',
                    'teacher',
                    ['jpg', 'jpeg', 'png', 'webp'],
                    5242880
                );

                if ($newPhoto) {

                    if (!empty($record['photo'])) {
                        delete_upload($record['photo']);
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


            // ==================================================
            // REDIRECT
            // ==================================================

            redirect("teacher_view.php?id=" . $id);


        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {

                $error =
                    'Teacher/Employee ID already exists. Please use a different ID.';

            } else {

                $error =
                    'Unable to save the teacher record.';
            }
        }
    }


    // Keep entered values
    $record = array_merge($record, $data);
}

include 'header.php';

?>

<link rel="stylesheet" href="assets/teacher_form.css">


<div class="teacher-form-page">

    <!-- ======================================================
         PAGE HEADER
    ======================================================= -->

    <div class="teacher-form-header">

        <div>

            <div class="teacher-form-label">
                TEACHER RECORD
            </div>

            <h2 class="teacher-title">
                <?= $isEdit ? 'Edit Teacher' : 'Add Teacher' ?>
            </h2>

            <p class="teacher-subtitle">
                <?= $isEdit
                    ? 'Update the teacher information below.'
                    : 'Add a new teacher record to the system.' ?>
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


    <!-- ======================================================
         ERROR
    ======================================================= -->

    <?php if ($error): ?>

        <div class="alert alert-danger teacher-form-alert">

            <i class="bi bi-exclamation-circle-fill me-2"></i>

            <?= e($error) ?>

        </div>

    <?php endif; ?>


    <!-- ======================================================
         FORM
    ======================================================= -->

    <form
        method="POST"
        enctype="multipart/form-data"
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
                                Personal information of the teacher.
                            </p>

                        </div>

                    </div>


                    <div class="teacher-form-card-body">

                        <div class="row g-3">


                            <!-- FULL NAME -->

                            <div class="col-12">

                                <label class="form-label">
                                    Full Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="full_name"
                                    id="full_name"
                                    class="form-control"
                                    value="<?= e($record['full_name'] ?? '') ?>"
                                    maxlength="100"
                                    required
                                    placeholder="e.g. Juan Dela Cruz"
                                >

                            </div>


                            <!-- BIRTHDATE -->

                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    Birthdate
                                </label>

                                <input
                                    type="date"
                                    name="date_of_birth"
                                    class="form-control"
                                    value="<?= e($record['date_of_birth'] ?? '') ?>"
                                >

                            </div>


                            <!-- GENDER -->

                            <div class="col-12 col-md-4">

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

                                    <?php foreach (
                                        ['Male', 'Female', 'Other']
                                        as $gender
                                    ): ?>

                                        <option
                                            value="<?= e($gender) ?>"
                                            <?= (($record['gender'] ?? '') === $gender)
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            <?= e($gender) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- CONTACT NUMBER -->

                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    Contact Number
                                </label>

                                <input
                                    type="tel"
                                    name="contact_number"
                                    id="contact_number"
                                    class="form-control"
                                    value="<?= e($record['contact_number'] ?? '') ?>"
                                    maxlength="11"
                                    pattern="09[0-9]{9}"
                                    inputmode="numeric"
                                    placeholder="09XXXXXXXXX"
                                >

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
                                ><?= e($record['address'] ?? '') ?></textarea>

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
                                Employment and appointment details.
                            </p>

                        </div>

                    </div>


                    <div class="teacher-form-card-body">

                        <div class="row g-3">


                            <!-- EMPLOYEE NO -->

                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    Employee No.
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="employee_id"
                                    class="form-control"
                                    value="<?= e($record['employee_id'] ?? '') ?>"
                                    required
                                    placeholder="Employee number"
                                >

                            </div>


                            <!-- PLANTILLA -->

                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    Plantilla No.
                                </label>

                                <input
                                    type="text"
                                    name="plantilla_no"
                                    class="form-control"
                                    value="<?= e($record['plantilla_no'] ?? '') ?>"
                                    placeholder="Plantilla number"
                                >

                            </div>


                            <!-- TIN -->

                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    TIN No.
                                </label>

                                <input
                                    type="text"
                                    name="tin_no"
                                    class="form-control"
                                    value="<?= e($record['tin_no'] ?? '') ?>"
                                    placeholder="TIN number"
                                >

                            </div>


                            <!-- FIRST DAY -->

                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    First Day of Service
                                </label>

                                <input
                                    type="date"
                                    name="first_day_of_service"
                                    class="form-control"
                                    value="<?= e($record['first_day_of_service'] ?? '') ?>"
                                >

                            </div>


                            <!-- POSITION / DEPARTMENT -->

                            <div class="col-12">

                                <label class="form-label">
                                    Position / Department
                                </label>

                                <input
                                    type="text"
                                    name="position_department"
                                    class="form-control"
                                    value="<?= e($record['position_department'] ?? '') ?>"
                                    placeholder="e.g. Teacher III / Senior High School"
                                >

                            </div>


                            <!-- CURRENT APPOINTMENT -->

                            <div class="col-12">

                                <label class="form-label">
                                    Current / Latest Appointment
                                </label>

                                <textarea
                                    name="current_latest_appointment"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Enter current or latest appointment"
                                ><?= e($record['current_latest_appointment'] ?? '') ?></textarea>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     EDUCATION & ELIGIBILITY
                =================================================== -->

                <div class="teacher-form-card mt-4">

                    <div class="teacher-form-card-header">

                        <div class="teacher-section-icon">
                            <i class="bi bi-mortarboard"></i>
                        </div>

                        <div>

                            <h5>
                                Education & Eligibility
                            </h5>

                            <p>
                                Educational background and professional eligibility.
                            </p>

                        </div>

                    </div>


                    <div class="teacher-form-card-body">

                        <div class="row g-3">


                            <!-- DEGREE -->

                            <div class="col-12">

                                <label class="form-label">
                                    Degree Finished
                                </label>

                                <input
                                    type="text"
                                    name="degree_finished"
                                    class="form-control"
                                    value="<?= e($record['degree_finished'] ?? '') ?>"
                                    placeholder="e.g. Bachelor of Secondary Education"
                                >

                            </div>


                            <!-- SPECIALIZATION -->

                            <div class="col-12">

                                <label class="form-label">
                                    Specialization / PRC Eligibility
                                </label>

                                <textarea
                                    name="specialization_prc_eligibility"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Enter specialization, PRC eligibility, license details, etc."
                                ><?= e($record['specialization_prc_eligibility'] ?? '') ?></textarea>

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
                                Official and personal contact details.
                            </p>

                        </div>

                    </div>


                    <div class="teacher-form-card-body">

                        <div class="row g-3">


                            <!-- DEPED EMAIL -->

                            <div class="col-12">

                                <label class="form-label">
                                    DepEd Email
                                </label>

                                <input
                                    type="email"
                                    name="deped_email"
                                    class="form-control"
                                    value="<?= e($record['deped_email'] ?? '') ?>"
                                    placeholder="teacher@deped.gov.ph"
                                >

                            </div>


                            <!-- PERSONAL EMAIL -->

                            <div class="col-12">

                                <label class="form-label">
                                    Personal Email
                                </label>

                                <input
                                    type="email"
                                    name="personal_email"
                                    class="form-control"
                                    value="<?= e($record['personal_email'] ?? '') ?>"
                                    placeholder="personal@email.com"
                                >

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     ADDITIONAL INFORMATION
                =================================================== -->

                <div class="teacher-form-card mt-4">

                    <div class="teacher-form-card-header">

                        <div class="teacher-section-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>

                        <div>

                            <h5>
                                Additional Information
                            </h5>

                            <p>
                                Other relevant teacher information.
                            </p>

                        </div>

                    </div>


                    <div class="teacher-form-card-body">

                        <label class="form-label">
                            Other Relevant Information
                        </label>

                        <textarea
                            name="other_info"
                            class="form-control"
                            rows="5"
                            placeholder="Additional information..."
                        ><?= e($record['other_info'] ?? '') ?></textarea>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 RIGHT COLUMN - PHOTO
            =================================================== -->

            <div class="col-12 col-lg-4">

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
                                src="<?= e($record['photo']) ?>"
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
                                : 'Save Teacher' ?>

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const fullName =
        document.getElementById('full_name');

    const contactNumber =
        document.getElementById('contact_number');


    // ======================================================
    // CLEAN NAME
    // ======================================================

    if (fullName) {

        fullName.addEventListener('input', function () {

            this.value = this.value.replace(
                /[^A-Za-zÀ-ÿ .'-]/g,
                ''
            );

        });

    }


    // ======================================================
    // CLEAN PHONE NUMBER
    // ======================================================

    if (contactNumber) {

        contactNumber.addEventListener('input', function () {

            this.value = this.value
                .replace(/\D/g, '')
                .slice(0, 11);

        });

    }

});

</script>


<?php include 'footer.php'; ?>
<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Staff Form';

$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;

$record = [];
$error = '';


// ==========================================================
// Current Logged-in User
// ==========================================================

$currentUserId = (int)($_SESSION['user_id'] ?? 0);


// ==========================================================
// Load Existing Staff Record
// ==========================================================

if ($isEdit) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM staff
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $record = $stmt->fetch();

    if (!$record) {

        redirect('staff.php');

    }
}


// ==========================================================
// Process Form
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [

        'employee_id' => trim(
            $_POST['employee_id'] ?? ''
        ),

        'full_name' => trim(
            $_POST['full_name'] ?? ''
        ),

        'date_of_birth' => !empty(
            $_POST['date_of_birth'] ?? ''
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

        'plantilla_no' => trim(
            $_POST['plantilla_no'] ?? ''
        ),

        'tin_no' => trim(
            $_POST['tin_no'] ?? ''
        ),

        'first_day_of_service' => !empty(
            $_POST['first_day_of_service'] ?? ''
        )
            ? $_POST['first_day_of_service']
            : null,

        'position_department' => trim(
            $_POST['position_department'] ?? ''
        ),

        'current_latest_appointment' => !empty(
            $_POST['current_latest_appointment'] ?? ''
        )
            ? $_POST['current_latest_appointment']
            : null,

        'degree_finished' => trim(
            $_POST['degree_finished'] ?? ''
        ),

        'specialization_prc_eligibility' => trim(
            $_POST['specialization_prc_eligibility'] ?? ''
        ),

        'deped_email' => trim(
            $_POST['deped_email'] ?? ''
        ),

        'personal_email' => trim(
            $_POST['personal_email'] ?? ''
        ),

        'other_info' => trim(
            $_POST['other_info'] ?? ''
        )
    ];


    // ======================================================
    // Validate Form
    // ======================================================

    if ($currentUserId <= 0) {

        $error =
            'Unable to identify the current user. Please log in again.';

    } elseif ($data['employee_id'] === '') {

        $error =
            'Staff / Employee ID is required.';

    } elseif ($data['full_name'] === '') {

        $error =
            'Full Name is required.';

    } elseif (
        !preg_match(
            "/^[A-Za-zÀ-ÿ .'-]+$/",
            $data['full_name']
        )
    ) {

        $error =
            'Full Name must not contain numbers or invalid characters.';

    } elseif (
        $data['contact_number'] !== '' &&
        !preg_match(
            '/^09[0-9]{9}$/',
            $data['contact_number']
        )
    ) {

        $error =
            'Contact Number must be a valid Philippine mobile number.';

    } elseif (
        $data['deped_email'] !== '' &&
        !filter_var(
            $data['deped_email'],
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Please enter a valid DepEd Email.';

    } elseif (
        $data['personal_email'] !== '' &&
        !filter_var(
            $data['personal_email'],
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Please enter a valid Personal Email.';

    }


    // ======================================================
    // Save Record
    // ======================================================

    if ($error === '') {

        try {

            // ==================================================
            // UPDATE EXISTING STAFF
            // ==================================================

            if ($isEdit) {

                /*
                 * IMPORTANT:
                 *
                 * created_by is NOT changed.
                 *
                 * updated_by becomes the currently logged-in user.
                 *
                 * user_id is also NOT changed here because
                 * user_id represents the staff member's account,
                 * not the person performing the update.
                 */

                $sql = "
                    UPDATE staff SET

                        updated_by = ?,

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

                    // Person who updated the record
                    $currentUserId,

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

            }


            // ==================================================
            // INSERT NEW STAFF
            // ==================================================

            else {

                /*
                 * When creating a new staff record:
                 *
                 * created_by = current logged-in user
                 * updated_by = current logged-in user
                 *
                 * user_id is left NULL unless the staff member
                 * is later connected to an actual user account.
                 */

                $sql = "
                    INSERT INTO staff (

                        user_id,
                        created_by,
                        updated_by,

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
                        NULL,
                        ?, ?,

                        ?, ?, ?, ?, ?, ?,

                        ?, ?, ?, ?, ?,

                        ?, ?,

                        ?, ?,

                        ?
                    )
                ";

                $params = [

                    // Creator
                    $currentUserId,

                    // Last updater
                    $currentUserId,

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
            // Upload Photo
            // ==================================================

            if (
                isset($_FILES['photo']) &&
                $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                $newPhoto = upload_file(
                    'photo',
                    'staff',
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
                     * Delete old photo when replacing it.
                     */

                    if (!empty($record['photo'])) {

                        delete_upload(
                            $record['photo']
                        );

                    }


                    $stmt = $pdo->prepare("
                        UPDATE staff
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
            // Open Saved Record
            // ==================================================

            redirect(
                "staff_view.php?id=" . $id
            );

        }


        // ======================================================
        // Database Error
        // ======================================================

        catch (PDOException $e) {

            /*
             * Duplicate employee ID
             */

            if ($e->getCode() === '23000') {

                $error =
                    'Staff / Employee ID already exists. Please use a different ID.';

            } else {

                $error =
                    'Unable to save the staff record: '
                    . $e->getMessage();

            }

        }

    }


    // ======================================================
    // Keep Entered Values
    // ======================================================

    $record = array_merge(
        $record,
        $data
    );

}


include 'header.php';

?>

<link
    rel="stylesheet"
    href="assets/staff_form.css"
>


<div class="staff-form-page">


    <!-- ======================================================
         Page Header
    ======================================================= -->

    <div class="staff-form-header">

        <div>

            <div class="staff-form-label">
                STAFF RECORD
            </div>

            <h2 class="staff-title">

                <?= $isEdit
                    ? 'Edit Staff'
                    : 'Add Staff'
                ?>

            </h2>

            <p class="staff-subtitle">

                <?= $isEdit
                    ? 'Update the staff information below.'
                    : 'Add a new staff record to the system.'
                ?>

            </p>

        </div>


        <a
            href="staff.php"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Back to List

        </a>

    </div>


    <!-- ======================================================
         Error Message
    ======================================================= -->

    <?php if ($error): ?>

        <div class="alert alert-danger staff-form-alert">

            <i class="bi bi-exclamation-circle-fill me-2"></i>

            <?= e($error) ?>

        </div>

    <?php endif; ?>


    <!-- ======================================================
         Staff Form
    ======================================================= -->

    <form
        method="POST"
        enctype="multipart/form-data"
    >

        <div class="row g-4">


            <!-- ==================================================
                 Main Information
            =================================================== -->

            <div class="col-12 col-lg-8">


                <!-- ==================================================
                     Basic Information
                =================================================== -->

                <div class="staff-form-card">

                    <div class="staff-form-card-header">

                        <div class="staff-section-icon">

                            <i class="bi bi-person-vcard"></i>

                        </div>

                        <div>

                            <h5>
                                Basic Information
                            </h5>

                            <p>
                                Personal information of the staff member.
                            </p>

                        </div>

                    </div>


                    <div class="staff-form-card-body">

                        <div class="row g-3">


                            <!-- Full Name -->

                            <div class="col-12">

                                <label class="form-label">

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
                                    maxlength="150"
                                    required
                                    placeholder="e.g. Juan Dela Cruz"
                                >

                            </div>


                            <!-- Birthdate -->

                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    Birthdate
                                </label>

                                <input
                                    type="date"
                                    name="date_of_birth"
                                    class="form-control"
                                    value="<?= e(
                                        $record['date_of_birth'] ?? ''
                                    ) ?>"
                                >

                            </div>


                            <!-- Gender -->

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
                                        [
                                            'Male',
                                            'Female',
                                            'Other'
                                        ] as $gender
                                    ): ?>

                                        <option
                                            value="<?= e($gender) ?>"
                                            <?= (
                                                ($record['gender'] ?? '') ===
                                                $gender
                                            )
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >

                                            <?= e($gender) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- Contact Number -->

                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    Contact Number
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

                                <label class="form-label">
                                    Address
                                </label>

                                <textarea
                                    name="address"
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
                     Employment Information
                =================================================== -->

                <div class="staff-form-card mt-4">

                    <div class="staff-form-card-header">

                        <div class="staff-section-icon">

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


                    <div class="staff-form-card-body">

                        <div class="row g-3">


                            <!-- Employee ID -->

                            <div class="col-12 col-md-6">

                                <label class="form-label">

                                    Staff / Employee ID

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="text"
                                    name="employee_id"
                                    class="form-control"
                                    value="<?= e(
                                        $record['employee_id'] ?? ''
                                    ) ?>"
                                    maxlength="50"
                                    required
                                    placeholder="Employee number"
                                >

                            </div>


                            <!-- Plantilla -->

                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    Plantilla No.
                                </label>

                                <input
                                    type="text"
                                    name="plantilla_no"
                                    class="form-control"
                                    value="<?= e(
                                        $record['plantilla_no'] ?? ''
                                    ) ?>"
                                    maxlength="50"
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
                                    value="<?= e(
                                        $record['tin_no'] ?? ''
                                    ) ?>"
                                    maxlength="30"
                                    placeholder="TIN number"
                                >

                            </div>


                            <!-- First Day of Service -->

                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    First Day of Service
                                </label>

                                <input
                                    type="date"
                                    name="first_day_of_service"
                                    class="form-control"
                                    value="<?= e(
                                        $record[
                                            'first_day_of_service'
                                        ] ?? ''
                                    ) ?>"
                                >

                            </div>


                            <!-- Position -->

                            <div class="col-12">

                                <label class="form-label">
                                    Position / Department
                                </label>

                                <input
                                    type="text"
                                    name="position_department"
                                    class="form-control"
                                    value="<?= e(
                                        $record[
                                            'position_department'
                                        ] ?? ''
                                    ) ?>"
                                    maxlength="150"
                                    placeholder="e.g. Administrative Staff / Finance"
                                >

                            </div>


                            <!-- Latest Appointment -->

                            <div class="col-12">

                                <label class="form-label">
                                    Current / Latest Appointment
                                </label>

                                <input
                                    type="date"
                                    name="current_latest_appointment"
                                    class="form-control"
                                    value="<?= e(
                                        $record[
                                            'current_latest_appointment'
                                        ] ?? ''
                                    ) ?>"
                                >

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     Education & Eligibility
                =================================================== -->

                <div class="staff-form-card mt-4">

                    <div class="staff-form-card-header">

                        <div class="staff-section-icon">

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


                    <div class="staff-form-card-body">

                        <div class="row g-3">


                            <!-- Degree -->

                            <div class="col-12">

                                <label class="form-label">
                                    Degree Finished
                                </label>

                                <input
                                    type="text"
                                    name="degree_finished"
                                    class="form-control"
                                    value="<?= e(
                                        $record[
                                            'degree_finished'
                                        ] ?? ''
                                    ) ?>"
                                    maxlength="255"
                                    placeholder="e.g. Bachelor of Science in Business Administration"
                                >

                            </div>


                            <!-- Specialization / PRC -->

                            <div class="col-12">

                                <label class="form-label">
                                    Specialization / PRC Eligibility
                                </label>

                                <textarea
                                    name="specialization_prc_eligibility"
                                    class="form-control"
                                    rows="4"
                                    maxlength="255"
                                    placeholder="Enter specialization, PRC eligibility, license details, etc."
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
                     Contact Information
                =================================================== -->

                <div class="staff-form-card mt-4">

                    <div class="staff-form-card-header">

                        <div class="staff-section-icon">

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


                    <div class="staff-form-card-body">

                        <div class="row g-3">


                            <!-- DepEd Email -->

                            <div class="col-12">

                                <label class="form-label">
                                    DepEd Email
                                </label>

                                <input
                                    type="email"
                                    name="deped_email"
                                    class="form-control"
                                    value="<?= e(
                                        $record[
                                            'deped_email'
                                        ] ?? ''
                                    ) ?>"
                                    maxlength="150"
                                    placeholder="staff@deped.gov.ph"
                                >

                            </div>


                            <!-- Personal Email -->

                            <div class="col-12">

                                <label class="form-label">
                                    Personal Email
                                </label>

                                <input
                                    type="email"
                                    name="personal_email"
                                    class="form-control"
                                    value="<?= e(
                                        $record[
                                            'personal_email'
                                        ] ?? ''
                                    ) ?>"
                                    maxlength="150"
                                    placeholder="personal@email.com"
                                >

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     Additional Information
                =================================================== -->

                <div class="staff-form-card mt-4">

                    <div class="staff-form-card-header">

                        <div class="staff-section-icon">

                            <i class="bi bi-info-circle"></i>

                        </div>

                        <div>

                            <h5>
                                Additional Information
                            </h5>

                            <p>
                                Other relevant staff information.
                            </p>

                        </div>

                    </div>


                    <div class="staff-form-card-body">

                        <label class="form-label">
                            Other Relevant Information
                        </label>

                        <textarea
                            name="other_info"
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
                 Profile Photo
            =================================================== -->

            <div class="col-12 col-lg-4">

                <div class="staff-form-card staff-photo-card">


                    <div class="staff-form-card-header">

                        <div class="staff-section-icon">

                            <i class="bi bi-camera"></i>

                        </div>

                        <div>

                            <h5>
                                Profile Photo
                            </h5>

                            <p>
                                Upload a staff profile picture.
                            </p>

                        </div>

                    </div>


                    <div class="staff-photo-body">


                        <!-- Current Photo -->

                        <?php if (!empty($record['photo'])): ?>

                            <img
                                src="<?= e($record['photo']) ?>"
                                alt="Staff Photo"
                                class="staff-profile-preview"
                            >

                        <?php else: ?>

                            <div class="staff-photo-placeholder">

                                <i class="bi bi-person"></i>

                                <span>
                                    No photo uploaded
                                </span>

                            </div>

                        <?php endif; ?>


                        <!-- Select Photo -->

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


                        <!-- Save -->

                        <button
                            type="submit"
                            class="btn btn-primary w-100 staff-save-btn"
                        >

                            <i class="bi bi-check-lg me-1"></i>

                            <?= $isEdit
                                ? 'Update Staff'
                                : 'Save Staff'
                            ?>

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {


    // ======================================================
    // Full Name
    // ======================================================

    const fullName =
        document.getElementById('full_name');


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


    // ======================================================
    // Contact Number
    // ======================================================

    const contactNumber =
        document.getElementById('contact_number');


    if (contactNumber) {

        contactNumber.addEventListener(
            'input',
            function () {

                this.value =
                    this.value
                        .replace(/\D/g, '')
                        .slice(0, 11);

            }
        );

    }

});

</script>


<?php include 'footer.php'; ?>
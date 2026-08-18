<?php

require_once __DIR__ . '/config.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$record = [];
$error = '';

/*
 * Logged-in user
 */
$currentUserId = (int)($_SESSION['user_id'] ?? 0);


/*
 * Load existing student
 */
if ($isEdit) {

    $stmt = $pdo->prepare(
        "SELECT * FROM students WHERE id = ?"
    );

    $stmt->execute([$id]);

    $record = $stmt->fetch();

    if (!$record) {
        redirect('students.php');
    }
}


/*
 * Save Student
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [

        'lrn' => trim($_POST['lrn'] ?? ''),

        'school_id' => '500634',

        'full_name' => trim($_POST['full_name'] ?? ''),

        'date_of_birth' => !empty($_POST['date_of_birth'])
            ? $_POST['date_of_birth']
            : null,

        'gender' => trim($_POST['gender'] ?? ''),

        'contact_number' => trim($_POST['contact_number'] ?? ''),

        'address' => trim($_POST['address'] ?? ''),

        'parent_guardian' => trim(
            $_POST['parent_guardian'] ?? ''
        ),

        'school_year' => trim(
            $_POST['school_year'] ?? ''
        ),

        'grade_section' => trim(
            $_POST['grade_section'] ?? ''
        ),

        'emergency_name' => trim(
            $_POST['emergency_name'] ?? ''
        ),

        'emergency_address' => trim(
            $_POST['emergency_address'] ?? ''
        ),

        'emergency_contact' => trim(
            $_POST['emergency_contact'] ?? ''
        ),

        'other_info' => trim(
            $_POST['other_info'] ?? ''
        )

    ];


    /*
     * LRN
     */
    if (
        $data['lrn'] !== '' &&
        !preg_match('/^[0-9]{12}$/', $data['lrn'])
    ) {

        $error =
            'LRN must contain exactly 12 numbers.';

    }


    /*
     * Full Name
     */
    elseif (
        !preg_match(
            "/^[A-Za-zÀ-ÿ .'-]+$/",
            $data['full_name']
        )
    ) {

        $error =
            'Full Name must not contain numbers or special characters.';

    }


    /*
     * Parent / Guardian
     */
    elseif (
        $data['parent_guardian'] !== '' &&
        !preg_match(
            "/^[A-Za-zÀ-ÿ .'-]+$/",
            $data['parent_guardian']
        )
    ) {

        $error =
            'Parent / Guardian name must not contain numbers or special characters.';

    }


    /*
     * Emergency Name
     */
    elseif (
        $data['emergency_name'] !== '' &&
        !preg_match(
            "/^[A-Za-zÀ-ÿ .'-]+$/",
            $data['emergency_name']
        )
    ) {

        $error =
            'Emergency contact name must not contain numbers or special characters.';

    }


    /*
     * Student Mobile
     */
    elseif (
        $data['contact_number'] !== '' &&
        !preg_match(
            '/^09[0-9]{9}$/',
            $data['contact_number']
        )
    ) {

        $error =
            'Mobile Number must be a valid Philippine number.';

    }


    /*
     * Emergency Mobile
     */
    elseif (
        $data['emergency_contact'] !== '' &&
        !preg_match(
            '/^09[0-9]{9}$/',
            $data['emergency_contact']
        )
    ) {

        $error =
            'Emergency Contact No. must be a valid Philippine number.';

    }


    /*
     * Required fields
     */
    elseif ($data['full_name'] === '') {

        $error =
            'Full Name is required.';

    }


    /*
     * Save to Database
     */
    if (!$error) {

        try {

            /*
             * EDIT EXISTING STUDENT
             */
            if ($isEdit) {

                $sql = "
                    UPDATE students SET

                        lrn = ?,
                        school_id = ?,
                        full_name = ?,
                        date_of_birth = ?,
                        gender = ?,
                        contact_number = ?,
                        address = ?,
                        parent_guardian = ?,
                        school_year = ?,
                        grade_section = ?,
                        emergency_name = ?,
                        emergency_address = ?,
                        emergency_contact = ?,
                        other_info = ?,

                        updated_by = ?,
                        updated_at = NOW()

                    WHERE id = ?
                ";

                $params = [

                    $data['lrn'],

                    $data['school_id'],

                    $data['full_name'],

                    $data['date_of_birth'],

                    $data['gender'],

                    $data['contact_number'],

                    $data['address'],

                    $data['parent_guardian'],

                    $data['school_year'],

                    $data['grade_section'],

                    $data['emergency_name'],

                    $data['emergency_address'],

                    $data['emergency_contact'],

                    $data['other_info'],

                    /*
                     * Who updated the record
                     */
                    $currentUserId,

                    $id

                ];

                $pdo
                    ->prepare($sql)
                    ->execute($params);

            }


            /*
             * CREATE NEW STUDENT
             */
            else {

                $sql = "
                    INSERT INTO students
                    (
                        lrn,
                        school_id,
                        full_name,
                        date_of_birth,
                        gender,
                        contact_number,
                        address,
                        parent_guardian,
                        school_year,
                        grade_section,
                        emergency_name,
                        emergency_address,
                        emergency_contact,
                        other_info,
                        created_by
                    )

                    VALUES
                    (
                        ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?
                    )
                ";

                $params = [

                    $data['lrn'],

                    $data['school_id'],

                    $data['full_name'],

                    $data['date_of_birth'],

                    $data['gender'],

                    $data['contact_number'],

                    $data['address'],

                    $data['parent_guardian'],

                    $data['school_year'],

                    $data['grade_section'],

                    $data['emergency_name'],

                    $data['emergency_address'],

                    $data['emergency_contact'],

                    $data['other_info'],

                    /*
                     * Who created the record
                     */
                    $currentUserId

                ];

                $pdo
                    ->prepare($sql)
                    ->execute($params);


                /*
                 * Get newly created student ID
                 */
                $id = (int)$pdo->lastInsertId();

                $isEdit = true;

            }


            /*
             * Photo
             */
            if (!empty($_FILES['photo']['name'])) {

                $newPhoto = upload_file(
                    'photo',
                    'student',
                    ['jpg', 'jpeg', 'png', 'webp'],
                    5242880
                );

                if ($newPhoto) {

                    if (!empty($record['photo'])) {

                        delete_upload(
                            $record['photo']
                        );

                    }

                    $stmt = $pdo->prepare(
                        "
                        UPDATE students
                        SET photo = ?
                        WHERE id = ?
                        "
                    );

                    $stmt->execute([
                        $newPhoto,
                        $id
                    ]);

                }

            }


            /*
             * Redirect after successful save
             */
            redirect(
                "student_view.php?id=$id"
            );


        } catch (PDOException $e) {

            $error =
                'Could not save the student record.';

        }

    }

}


/*
 * Keep values
 */
if ($isEdit) {

    $stmt = $pdo->prepare(
        "SELECT * FROM students WHERE id = ?"
    );

    $stmt->execute([$id]);

    $record = $stmt->fetch() ?: [];

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $record = $_POST;

}


include 'header.php';

?>

<link rel="stylesheet" href="assets/studentform.css">

<div class="student-form-page">

    <div class="student-form-header">

        <div>
            <div class="student-form-label">
                STUDENT RECORD
            </div>

            <h2 class="fw-bold mb-1">
                <?= $isEdit ? 'Edit Student' : 'Add Student' ?>
            </h2>

            <p class="text-muted mb-0">
                <?= $isEdit
                    ? 'Update the student information below.'
                    : 'Add a new student record to the system.' ?>
            </p>
        </div>

        <a href="students.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to List
        </a>

    </div>

    <?php if ($error): ?>

        <div class="alert alert-danger student-form-alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <form
        method="post"
        enctype="multipart/form-data"
        id="studentForm"
        novalidate
    >

        <div class="row g-4">

            <div class="col-12 col-lg-8">

                <div class="student-form-card">

                    <div class="student-form-card-header">

                        <div class="student-section-icon">
                            <i class="bi bi-person-vcard"></i>
                        </div>

                        <div>
                            <h5 class="fw-bold mb-1">
                                Student Information
                            </h5>

                            <p class="text-muted small mb-0">
                                Enter the student's basic information.
                            </p>
                        </div>

                    </div>

                    <div class="student-form-card-body">

                        <div class="row g-3">

                            <!-- LRN -->
                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    LRN
                                </label>

                                <input
                                    type="text"
                                    name="lrn"
                                    id="lrn"
                                    class="form-control"
                                    value="<?= e($record['lrn'] ?? '') ?>"
                                    maxlength="12"
                                    pattern="[0-9]{12}"
                                    inputmode="numeric"
                                    placeholder="12-digit LRN"
                                >

                                <div class="form-text">
                                    Maximum 12 numbers.
                                </div>

                            </div>

                            <!-- SCHOOL ID -->
                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    School ID No.
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="500634"
                                    readonly
                                >

                            </div>

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
                                    pattern="[A-Za-zÀ-ÿ .'-]+"
                                    placeholder="e.g. Juan Dela Cruz"
                                    required
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

                                    <?php foreach (['Male', 'Female', 'Other'] as $gender): ?>

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

                            <!-- SCHOOL YEAR -->
                            <div class="col-12 col-md-4">

                                <label class="form-label">
                                    School Year
                                </label>

                                <input
                                    type="text"
                                    name="school_year"
                                    id="school_year"
                                    class="form-control"
                                    value="<?= e($record['school_year'] ?? '') ?>"
                                    maxlength="9"
                                    pattern="[0-9]{4}-[0-9]{4}"
                                    placeholder="2026-2027"
                                >

                            </div>

                            <!-- MOBILE -->
                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    Mobile Number
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

                            <!-- GRADE SECTION -->
                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    Grade & Section
                                </label>

                                <input
                                    type="text"
                                    name="grade_section"
                                    class="form-control"
                                    value="<?= e($record['grade_section'] ?? '') ?>"
                                    maxlength="50"
                                    placeholder="e.g. Grade 12 - Einstein"
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

                            <!-- PARENT GUARDIAN -->
                            <div class="col-12">

                                <label class="form-label">
                                    Parent / Guardian
                                </label>

                                <input
                                    type="text"
                                    name="parent_guardian"
                                    id="parent_guardian"
                                    class="form-control"
                                    value="<?= e($record['parent_guardian'] ?? '') ?>"
                                    maxlength="100"
                                    placeholder="e.g. Maria Dela Cruz"
                                >

                            </div>

                            <!-- EMERGENCY -->
                            <div class="col-12">

                                <div class="border rounded-3 p-3 mt-2">

                                    <h6 class="fw-bold mb-3">
                                        In Case of Emergency
                                    </h6>

                                    <div class="row g-3">

                                        <div class="col-12">

                                            <label class="form-label">
                                                Name
                                            </label>

                                            <input
                                                type="text"
                                                name="emergency_name"
                                                id="emergency_name"
                                                class="form-control"
                                                value="<?= e($record['emergency_name'] ?? '') ?>"
                                                maxlength="100"
                                                placeholder="Emergency contact name"
                                            >

                                        </div>

                                        <div class="col-12">

                                            <label class="form-label">
                                                Address
                                            </label>

                                            <textarea
                                                name="emergency_address"
                                                class="form-control"
                                                rows="2"
                                                placeholder="Emergency contact address"
                                            ><?= e($record['emergency_address'] ?? '') ?></textarea>

                                        </div>

                                        <div class="col-12">

                                            <label class="form-label">
                                                Contact No.
                                            </label>

                                            <input
                                                type="tel"
                                                name="emergency_contact"
                                                id="emergency_contact"
                                                class="form-control"
                                                value="<?= e($record['emergency_contact'] ?? '') ?>"
                                                maxlength="11"
                                                pattern="09[0-9]{9}"
                                                inputmode="numeric"
                                                placeholder="09XXXXXXXXX"
                                            >

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!-- OTHER INFORMATION -->
                            <div class="col-12">

                                <label class="form-label">
                                    Other Relevant Information
                                </label>

                                <textarea
                                    name="other_info"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Additional information..."
                                ><?= e($record['other_info'] ?? '') ?></textarea>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- PHOTO -->
            <div class="col-12 col-lg-4">

                <div class="student-form-card student-photo-card">

                    <div class="student-form-card-header">

                        <div class="student-section-icon">
                            <i class="bi bi-camera"></i>
                        </div>

                        <div>
                            <h5 class="fw-bold mb-1">
                                Profile Photo
                            </h5>

                            <p class="text-muted small mb-0">
                                Upload a student profile picture.
                            </p>
                        </div>

                    </div>

                    <div class="student-photo-body">

                        <?php if (!empty($record['photo'])): ?>

                            <img
                                src="<?= e($record['photo']) ?>"
                                alt="Student Photo"
                                class="student-profile-preview"
                            >

                        <?php else: ?>

                            <div class="student-photo-placeholder">
                                <i class="bi bi-person"></i>
                                <span>No photo uploaded</span>
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
                            class="btn btn-primary w-100 student-save-btn"
                        >

                            <i class="bi bi-check-lg me-1"></i>

                            <?= $isEdit
                                ? 'Update Student'
                                : 'Save Student' ?>

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const lrn = document.getElementById('lrn');
    const fullName = document.getElementById('full_name');
    const parentGuardian = document.getElementById('parent_guardian');
    const emergencyName = document.getElementById('emergency_name');
    const contactNumber = document.getElementById('contact_number');
    const emergencyContact = document.getElementById('emergency_contact');
    const schoolYear = document.getElementById('school_year');

    // LRN
    if (lrn) {
        lrn.addEventListener('input', function () {
            this.value = this.value
                .replace(/\D/g, '')
                .slice(0, 12);
        });
    }

    // Names
    function cleanName(input) {
        input.value = input.value.replace(
            /[^A-Za-zÀ-ÿ .'-]/g,
            ''
        );
    }

    if (fullName) {
        fullName.addEventListener('input', function () {
            cleanName(this);
        });
    }

    if (parentGuardian) {
        parentGuardian.addEventListener('input', function () {
            cleanName(this);
        });
    }

    if (emergencyName) {
        emergencyName.addEventListener('input', function () {
            cleanName(this);
        });
    }

    // Contact numbers
    function cleanPhone(input) {
        input.value = input.value
            .replace(/\D/g, '')
            .slice(0, 11);
    }

    if (contactNumber) {
        contactNumber.addEventListener('input', function () {
            cleanPhone(this);
        });
    }

    if (emergencyContact) {
        emergencyContact.addEventListener('input', function () {
            cleanPhone(this);
        });
    }

    // School year
    if (schoolYear) {
        schoolYear.addEventListener('input', function () {

            this.value = this.value
                .replace(/\D/g, '')
                .slice(0, 8);

            if (this.value.length > 4) {
                this.value =
                    this.value.slice(0, 4) +
                    '-' +
                    this.value.slice(4);
            }

        });
    }

});
</script>

<?php include 'footer.php'; ?>
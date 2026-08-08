<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Teacher Form';

$id = (int) ($_GET['id'] ?? 0);
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
        'address' => trim($_POST['address'] ?? ''),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'position_department' => trim($_POST['position_department'] ?? ''),
        'other_info' => trim($_POST['other_info'] ?? '')
    ];


    // ======================================================
    // VALIDATION
    // ======================================================

    if ($data['employee_id'] === '') {

        $error = 'Teacher/Employee ID is required.';

    } elseif ($data['full_name'] === '') {

        $error = 'Full name is required.';

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
                        address = ?,
                        contact_number = ?,
                        email = ?,
                        position_department = ?,
                        other_info = ?
                    WHERE id = ?
                ";

                $params = [
                    $data['employee_id'],
                    $data['full_name'],
                    $data['date_of_birth'],
                    $data['gender'],
                    $data['address'],
                    $data['contact_number'],
                    $data['email'],
                    $data['position_department'],
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
                        address,
                        contact_number,
                        email,
                        position_department,
                        other_info
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";

                $params = [
                    $data['employee_id'],
                    $data['full_name'],
                    $data['date_of_birth'],
                    $data['gender'],
                    $data['address'],
                    $data['contact_number'],
                    $data['email'],
                    $data['position_department'],
                    $data['other_info']
                ];

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $id = (int) $pdo->lastInsertId();
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

                    // Delete old photo
                    if (!empty($record['photo'])) {
                        delete_upload($record['photo']);
                    }

                    // Save new photo
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
            // REDIRECT AFTER SUCCESS
            // ==================================================

            redirect("teacher_view.php?id=" . $id);


        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {

                $error = 'Teacher/Employee ID already exists. Please use a different ID.';

            } else {

                $error = 'Unable to save the teacher record.';
            }
        }
    }


    // Keep entered values if validation fails
    $record = array_merge($record, $data);
}

?>

<?php include 'header.php'; ?>


<!-- ==========================================================
     PAGE HEADER
========================================================== -->

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            <?= $isEdit ? 'Edit Teacher' : 'Add Teacher' ?>
        </h2>

        <p class="text-muted mb-0">
            Store the basic teacher record information.
        </p>

    </div>


    <a
        href="teachers.php"
        class="btn btn-outline-secondary"
    >
        <i class="bi bi-arrow-left"></i>
        Back to List
    </a>

</div>


<!-- ==========================================================
     ERROR MESSAGE
========================================================== -->

<?php if ($error): ?>

    <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-2"></i>
        <?= e($error) ?>
    </div>

<?php endif; ?>


<!-- ==========================================================
     TEACHER FORM
========================================================== -->

<form
    method="POST"
    enctype="multipart/form-data"
>

    <div class="row g-4">


        <!-- ==================================================
             TEACHER INFORMATION
        =================================================== -->

        <div class="col-lg-8">

            <div class="card p-4">

                <h5 class="fw-bold mb-4">
                    Teacher Information
                </h5>


                <div class="row g-3">


                    <!-- Employee ID -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Teacher / Employee ID
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="employee_id"
                            class="form-control"
                            value="<?= e($record['employee_id'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- Full Name -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Full Name
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="full_name"
                            class="form-control"
                            value="<?= e($record['full_name'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- Date of Birth -->
                    <div class="col-md-4">

                        <label class="form-label">
                            Date of Birth
                        </label>

                        <input
                            type="date"
                            name="date_of_birth"
                            class="form-control"
                            value="<?= e($record['date_of_birth'] ?? '') ?>"
                        >

                    </div>


                    <!-- Gender -->
                    <div class="col-md-4">

                        <label class="form-label">
                            Gender
                        </label>

                        <select
                            name="gender"
                            class="form-select"
                        >

                            <option value="">
                                Select
                            </option>

                            <?php foreach (['Male', 'Female', 'Other'] as $gender): ?>

                                <option
                                    value="<?= e($gender) ?>"
                                    <?= (($record['gender'] ?? '') === $gender) ? 'selected' : '' ?>
                                >
                                    <?= e($gender) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Contact Number -->
                    <div class="col-md-4">

                        <label class="form-label">
                            Contact Number
                        </label>

                        <input
                            type="text"
                            name="contact_number"
                            class="form-control"
                            value="<?= e($record['contact_number'] ?? '') ?>"
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
                            rows="2"
                        ><?= e($record['address'] ?? '') ?></textarea>

                    </div>


                    <!-- Email -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?= e($record['email'] ?? '') ?>"
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
                            value="<?= e($record['position_department'] ?? '') ?>"
                        >

                    </div>


                    <!-- Other Information -->
                    <div class="col-12">

                        <label class="form-label">
                            Other Relevant Information
                        </label>

                        <textarea
                            name="other_info"
                            class="form-control"
                            rows="3"
                        ><?= e($record['other_info'] ?? '') ?></textarea>

                    </div>

                </div>

            </div>

        </div>


        <!-- ==================================================
             PROFILE PHOTO
        =================================================== -->

        <div class="col-lg-4">

            <div class="card p-4">

                <h5 class="fw-bold mb-4">
                    Profile Photo
                </h5>


                <?php if (!empty($record['photo'])): ?>

                    <img
                        src="<?= e($record['photo']) ?>"
                        alt="Teacher Photo"
                        class="profile-photo mb-3 d-block"
                    >

                <?php else: ?>

                    <div class="text-center text-muted py-4">

                        <i class="bi bi-person-circle fs-1"></i>

                        <p class="mb-0 mt-2">
                            No photo uploaded
                        </p>

                    </div>

                <?php endif; ?>


                <input
                    type="file"
                    name="photo"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <div class="form-text">
                    JPG, JPEG, PNG, or WEBP. Maximum 5 MB.
                </div>


                <button
                    type="submit"
                    class="btn btn-success w-100 mt-4"
                >

                    <i class="bi bi-check-lg"></i>

                    <?= $isEdit ? 'Update Teacher' : 'Save Teacher' ?>

                </button>

            </div>

        </div>

    </div>

</form>


<?php include 'footer.php'; ?>
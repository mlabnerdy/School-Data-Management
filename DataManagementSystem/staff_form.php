```php
<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Staff Form';

$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;

$record = [];
$error = '';


// ==========================================================
// LOAD STAFF RECORD
// ==========================================================

if ($isEdit) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM staff
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $record = $stmt->fetch();

    if (!$record) {
        redirect('staff.php');
    }
}


// ==========================================================
// SAVE STAFF RECORD
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

    if (
        $data['employee_id'] === '' ||
        $data['full_name'] === ''
    ) {

        $error = 'Staff/Employee ID and full name are required.';

    }


    // ======================================================
    // INSERT / UPDATE
    // ======================================================

    if ($error === '') {

        try {

            if ($isEdit) {

                $sql = "
                    UPDATE staff SET
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
                    INSERT INTO staff (
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
                    'staff',
                    ['jpg', 'jpeg', 'png', 'webp'],
                    5242880
                );

                if ($newPhoto) {

                    // Delete old photo when replacing it
                    if (!empty($record['photo'])) {
                        delete_upload($record['photo']);
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
            // REDIRECT
            // ==================================================

            redirect("staff_view.php?id=" . $id);

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {

                $error = 'Staff/Employee ID already exists. Please use a different ID.';

            } else {

                $error = 'Could not save the staff record.';

            }
        }
    }


    // Keep entered data if there is an error
    if ($error !== '') {
        $record = array_merge($record, $data);
    }
}


// ==========================================================
// HEADER
// ==========================================================

include 'header.php';

?>


<!-- ==========================================================
     PAGE HEADER
========================================================== -->

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            <?= $isEdit ? 'Edit Staff' : 'Add Staff' ?>
        </h2>

        <p class="text-muted mb-0">
            Store the basic staff record information.
        </p>

    </div>


    <a
        href="staff.php"
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
     STAFF FORM
========================================================== -->

<form
    method="POST"
    enctype="multipart/form-data"
>

    <div class="row g-4">


        <!-- ==================================================
             STAFF INFORMATION
        =================================================== -->

        <div class="col-lg-8">

            <div class="card p-4">

                <h5 class="fw-bold mb-4">
                    Staff Information
                </h5>


                <div class="row g-3">


                    <!-- Staff ID -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Staff / Employee ID *
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="employee_id"
                            value="<?= e($record['employee_id'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- Full Name -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Full Name *
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="full_name"
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
                            class="form-control"
                            name="date_of_birth"
                            value="<?= e($record['date_of_birth'] ?? '') ?>"
                        >

                    </div>


                    <!-- Gender -->
                    <div class="col-md-4">

                        <label class="form-label">
                            Gender
                        </label>

                        <select
                            class="form-select"
                            name="gender"
                        >

                            <option value="">
                                Select
                            </option>

                            <?php foreach (['Male', 'Female', 'Other'] as $g): ?>

                                <option
                                    value="<?= e($g) ?>"
                                    <?= (($record['gender'] ?? '') === $g) ? 'selected' : '' ?>
                                >
                                    <?= e($g) ?>
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
                            class="form-control"
                            name="contact_number"
                            value="<?= e($record['contact_number'] ?? '') ?>"
                        >

                    </div>


                    <!-- Address -->
                    <div class="col-12">

                        <label class="form-label">
                            Address
                        </label>

                        <textarea
                            class="form-control"
                            name="address"
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
                            class="form-control"
                            name="email"
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
                            class="form-control"
                            name="position_department"
                            value="<?= e($record['position_department'] ?? '') ?>"
                        >

                    </div>


                    <!-- Other Information -->
                    <div class="col-12">

                        <label class="form-label">
                            Other Relevant Information
                        </label>

                        <textarea
                            class="form-control"
                            name="other_info"
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

                <label class="form-label fw-semibold">
                    Profile Photo
                </label>


                <?php if (!empty($record['photo'])): ?>

                    <img
                        src="<?= e($record['photo']) ?>"
                        alt="Staff Photo"
                        class="profile-photo mb-3 d-block"
                    >

                <?php endif; ?>


                <input
                    type="file"
                    class="form-control"
                    name="photo"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <div class="form-text">
                    JPG, PNG, or WEBP. Maximum 5 MB.
                </div>


                <button
                    type="submit"
                    class="btn btn-success w-100 mt-4"
                >

                    <i class="bi bi-check-lg"></i>

                    <?= $isEdit ? 'Update Staff' : 'Save Staff' ?>

                </button>

            </div>

        </div>

    </div>

</form>


<?php include 'footer.php'; ?>
```

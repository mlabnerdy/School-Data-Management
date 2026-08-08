<?php

require_once __DIR__ . '/config.php';

require_login();

$id = (int) ($_GET['id'] ?? 0);

/*
|--------------------------------------------------------------------------
| Get Staff Record
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
$stmt->execute([$id]);

$r = $stmt->fetch();

if (!$r) {
    redirect('staff.php');
}

/*
|--------------------------------------------------------------------------
| Upload Document
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_document'])) {

    if (!empty($_FILES['document']['name'])) {

        $path = upload_file(
            'document',
            'documents',
            ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'],
            10485760
        );

        if ($path) {

            $name = $_FILES['document']['name'];
            $type = $_FILES['document']['type'] ?? '';

            $stmt = $pdo->prepare("
                INSERT INTO documents
                (owner_type, owner_id, document_name, file_path, file_type)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                'staff',
                $id,
                $name,
                $path,
                $type
            ]);
        }
    }

    redirect("staff_view.php?id=$id");
}

/*
|--------------------------------------------------------------------------
| Get Staff Documents
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT *
    FROM documents
    WHERE owner_type = ?
    AND owner_id = ?
    ORDER BY uploaded_at DESC
");

$stmt->execute(['staff', $id]);

$docs = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = 'Staff Details';

include __DIR__ . '/header.php';

?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

    <div>
        <h2 class="fw-bold mb-1">
            <?= e($r['full_name']) ?>
        </h2>

        <p class="text-muted mb-0">
            <?= e($r['employee_id']) ?>
        </p>
    </div>

    <div>

        <a
            class="btn btn-outline-secondary"
            href="staff.php"
        >
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

        <a
            class="btn btn-primary"
            href="staff_form.php?id=<?= $id ?>"
        >
            <i class="bi bi-pencil"></i>
            Edit
        </a>

    </div>

</div>


<!-- =========================================================
     STAFF INFORMATION
========================================================= -->

<div class="row g-4">

    <!-- Information -->
    <div class="col-lg-8">

        <div class="card p-4">

            <h5 class="fw-bold mb-4">
                Staff Information
            </h5>

            <div class="row g-4">

                <!-- Full Name -->
                <div class="col-md-6">
                    <div class="text-muted small">
                        Full Name
                    </div>

                    <div class="fw-semibold">
                        <?= e($r['full_name']) ?>
                    </div>
                </div>


                <!-- Employee ID -->
                <div class="col-md-6">
                    <div class="text-muted small">
                        Staff / Employee ID
                    </div>

                    <div class="fw-semibold">
                        <?= e($r['employee_id']) ?>
                    </div>
                </div>


                <!-- Date of Birth -->
                <div class="col-md-4">

                    <div class="text-muted small">
                        Date of Birth
                    </div>

                    <div>
                        <?= !empty($r['date_of_birth'])
                            ? e($r['date_of_birth'])
                            : '—'
                        ?>
                    </div>

                </div>


                <!-- Gender -->
                <div class="col-md-4">

                    <div class="text-muted small">
                        Gender
                    </div>

                    <div>
                        <?= !empty($r['gender'])
                            ? e($r['gender'])
                            : '—'
                        ?>
                    </div>

                </div>


                <!-- Contact -->
                <div class="col-md-4">

                    <div class="text-muted small">
                        Contact Number
                    </div>

                    <div>
                        <?= !empty($r['contact_number'])
                            ? e($r['contact_number'])
                            : '—'
                        ?>
                    </div>

                </div>


                <!-- Address -->
                <div class="col-12">

                    <div class="text-muted small">
                        Address
                    </div>

                    <div>
                        <?= !empty($r['address'])
                            ? nl2br(e($r['address']))
                            : '—'
                        ?>
                    </div>

                </div>


                <!-- Email -->
                <div class="col-md-6">

                    <div class="text-muted small">
                        Email
                    </div>

                    <div>
                        <?= !empty($r['email'])
                            ? e($r['email'])
                            : '—'
                        ?>
                    </div>

                </div>


                <!-- Position -->
                <div class="col-md-6">

                    <div class="text-muted small">
                        Position / Department
                    </div>

                    <div>
                        <?= !empty($r['position_department'])
                            ? e($r['position_department'])
                            : '—'
                        ?>
                    </div>

                </div>


                <!-- Other Information -->
                <div class="col-12">

                    <div class="text-muted small">
                        Other Relevant Information
                    </div>

                    <div>
                        <?= !empty($r['other_info'])
                            ? nl2br(e($r['other_info']))
                            : '—'
                        ?>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Profile Photo -->
    <div class="col-lg-4">

        <div class="card p-4 text-center">

            <?php if (!empty($r['photo'])): ?>

                <img
                    src="<?= e($r['photo']) ?>"
                    class="profile-photo mx-auto mb-3"
                    alt="Staff Photo"
                >

            <?php else: ?>

                <div
                    class="profile-photo mx-auto mb-3 d-flex align-items-center justify-content-center bg-light fs-1 text-muted"
                >
                    <i class="bi bi-person"></i>
                </div>

            <?php endif; ?>


            <h6 class="fw-bold mb-1">
                <?= e($r['full_name']) ?>
            </h6>

            <small class="text-muted">
                Staff Profile
            </small>

        </div>

    </div>

</div>


<!-- =========================================================
     DOCUMENTS
========================================================= -->

<div class="card p-4 mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h5 class="fw-bold mb-1">
                Documents
            </h5>

            <small class="text-muted">
                Upload and manage staff documents.
            </small>
        </div>

    </div>


    <!-- Upload Form -->

    <form
        method="post"
        enctype="multipart/form-data"
        class="row g-2 mb-4"
    >

        <div class="col-md-8">

            <input
                class="form-control"
                type="file"
                name="document"
                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"
                required
            >

            <div class="form-text">
                PDF, DOC, DOCX, JPG, PNG, or WEBP. Maximum 10 MB.
            </div>

        </div>


        <div class="col-md-4">

            <button
                type="submit"
                class="btn btn-success w-100"
                name="upload_document"
                value="1"
            >
                <i class="bi bi-upload"></i>
                Upload Document
            </button>

        </div>

    </form>


    <!-- Documents Table -->

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>

                <tr>

                    <th>
                        Document
                    </th>

                    <th>
                        Uploaded
                    </th>

                    <th class="text-end">
                        Actions
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php if ($docs): ?>

                    <?php foreach ($docs as $d): ?>

                        <tr>

                            <td>

                                <i class="bi bi-file-earmark-text me-2"></i>

                                <?= e($d['document_name']) ?>

                            </td>


                            <td>

                                <?= e($d['uploaded_at']) ?>

                            </td>


                            <td class="text-end">

                                <a
                                    target="_blank"
                                    class="btn btn-sm btn-outline-primary"
                                    href="<?= e($d['file_path']) ?>"
                                >
                                    <i class="bi bi-eye"></i>
                                    View
                                </a>


                                <a
                                    data-confirm="Delete this document?"
                                    class="btn btn-sm btn-outline-danger"
                                    href="delete_document.php?id=<?= (int)$d['id'] ?>&return=<?= urlencode('staff_view.php?id=' . $id) ?>"
                                >
                                    <i class="bi bi-trash"></i>
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="3"
                            class="text-center text-muted py-4"
                        >
                            <i class="bi bi-folder2-open fs-3 d-block mb-2"></i>

                            No documents uploaded.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<?php include __DIR__ . '/footer.php'; ?>
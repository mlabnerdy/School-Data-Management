<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Teacher Details';

$id = (int) ($_GET['id'] ?? 0);


// ==========================================================
// GET TEACHER
// ==========================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM teachers
    WHERE id = ?
");

$stmt->execute([$id]);

$teacher = $stmt->fetch();

if (!$teacher) {
    redirect('teachers.php');
}


// ==========================================================
// UPLOAD DOCUMENT
// ==========================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['upload_document'])
) {

    if (
        isset($_FILES['document']) &&
        $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $path = upload_file(
            'document',
            'documents',
            [
                'pdf',
                'doc',
                'docx',
                'jpg',
                'jpeg',
                'png',
                'webp'
            ],
            10485760
        );

        if ($path) {

            $documentName = $_FILES['document']['name'];
            $documentType = $_FILES['document']['type'] ?? '';

            $stmt = $pdo->prepare("
                INSERT INTO documents (
                    owner_type,
                    owner_id,
                    document_name,
                    file_path,
                    file_type
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                'teacher',
                $id,
                $documentName,
                $path,
                $documentType
            ]);
        }
    }

    redirect("teacher_view.php?id=" . $id);
}


// ==========================================================
// GET TEACHER DOCUMENTS
// ==========================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM documents
    WHERE owner_type = ?
    AND owner_id = ?
    ORDER BY uploaded_at DESC
");

$stmt->execute([
    'teacher',
    $id
]);

$documents = $stmt->fetchAll();


// ==========================================================
// HEADER
// ==========================================================

include 'header.php';

?>


<!-- ==========================================================
     PAGE HEADER
========================================================== -->

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            <?= e($teacher['full_name']) ?>
        </h2>

        <p class="text-muted mb-0">
            Employee ID:
            <?= e($teacher['employee_id']) ?>
        </p>

    </div>


    <div>

        <a
            href="teachers.php"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

        <a
            href="teacher_form.php?id=<?= (int) $id ?>"
            class="btn btn-primary"
        >
            <i class="bi bi-pencil"></i>
            Edit
        </a>

    </div>

</div>


<!-- ==========================================================
     TEACHER INFORMATION + PHOTO
========================================================== -->

<div class="row g-4">


    <!-- Teacher Information -->
    <div class="col-lg-8">

        <div class="card p-4">

            <h5 class="fw-bold mb-4">
                Teacher Information
            </h5>


            <div class="row g-4">


                <!-- Full Name -->
                <div class="col-md-6">

                    <div class="text-muted small">
                        Full Name
                    </div>

                    <div class="fw-semibold">
                        <?= e($teacher['full_name']) ?>
                    </div>

                </div>


                <!-- Employee ID -->
                <div class="col-md-6">

                    <div class="text-muted small">
                        Teacher / Employee ID
                    </div>

                    <div class="fw-semibold">
                        <?= e($teacher['employee_id']) ?>
                    </div>

                </div>


                <!-- Date of Birth -->
                <div class="col-md-4">

                    <div class="text-muted small">
                        Date of Birth
                    </div>

                    <div>
                        <?= !empty($teacher['date_of_birth'])
                            ? e($teacher['date_of_birth'])
                            : '—' ?>
                    </div>

                </div>


                <!-- Gender -->
                <div class="col-md-4">

                    <div class="text-muted small">
                        Gender
                    </div>

                    <div>
                        <?= !empty($teacher['gender'])
                            ? e($teacher['gender'])
                            : '—' ?>
                    </div>

                </div>


                <!-- Contact Number -->
                <div class="col-md-4">

                    <div class="text-muted small">
                        Contact Number
                    </div>

                    <div>
                        <?= !empty($teacher['contact_number'])
                            ? e($teacher['contact_number'])
                            : '—' ?>
                    </div>

                </div>


                <!-- Address -->
                <div class="col-12">

                    <div class="text-muted small">
                        Address
                    </div>

                    <div>
                        <?= !empty($teacher['address'])
                            ? nl2br(e($teacher['address']))
                            : '—' ?>
                    </div>

                </div>


                <!-- Email -->
                <div class="col-md-6">

                    <div class="text-muted small">
                        Email
                    </div>

                    <div>
                        <?= !empty($teacher['email'])
                            ? e($teacher['email'])
                            : '—' ?>
                    </div>

                </div>


                <!-- Position / Department -->
                <div class="col-md-6">

                    <div class="text-muted small">
                        Position / Department
                    </div>

                    <div>
                        <?= !empty($teacher['position_department'])
                            ? e($teacher['position_department'])
                            : '—' ?>
                    </div>

                </div>


                <!-- Other Information -->
                <div class="col-12">

                    <div class="text-muted small">
                        Other Relevant Information
                    </div>

                    <div>
                        <?= !empty($teacher['other_info'])
                            ? nl2br(e($teacher['other_info']))
                            : '—' ?>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ======================================================
         PROFILE PHOTO
    ======================================================= -->

    <div class="col-lg-4">

        <div class="card p-4 text-center">

            <?php if (!empty($teacher['photo'])): ?>

                <img
                    src="<?= e($teacher['photo']) ?>"
                    alt="Teacher Photo"
                    class="profile-photo mx-auto mb-3"
                >

            <?php else: ?>

                <div
                    class="profile-photo mx-auto mb-3 d-flex align-items-center justify-content-center bg-light fs-1 text-muted"
                >
                    <i class="bi bi-person"></i>
                </div>

            <?php endif; ?>


            <h6 class="fw-bold mb-1">
                <?= e($teacher['full_name']) ?>
            </h6>

            <small class="text-muted">
                Profile Photo
            </small>

        </div>

    </div>

</div>


<!-- ==========================================================
     DOCUMENTS
========================================================== -->

<div class="card p-4 mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h5 class="fw-bold mb-1">
                Documents
            </h5>

            <p class="text-muted small mb-0">
                Upload and manage teacher documents.
            </p>

        </div>

    </div>


    <!-- Upload Document -->
    <form
        method="POST"
        enctype="multipart/form-data"
        class="row g-2 mb-4"
    >

        <div class="col-md-8">

            <input
                type="file"
                name="document"
                class="form-control"
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
                name="upload_document"
                value="1"
                class="btn btn-success w-100"
            >
                <i class="bi bi-upload"></i>
                Upload Document
            </button>

        </div>

    </form>


    <!-- Documents Table -->
    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead>

                <tr>
                    <th>Document</th>
                    <th>Uploaded</th>
                    <th class="text-end">Actions</th>
                </tr>

            </thead>


            <tbody>

                <?php if (!empty($documents)): ?>

                    <?php foreach ($documents as $document): ?>

                        <tr>

                            <td>

                                <i class="bi bi-file-earmark me-2"></i>

                                <?= e($document['document_name']) ?>

                            </td>


                            <td>
                                <?= e($document['uploaded_at']) ?>
                            </td>


                            <td class="text-end">

                                <a
                                    href="<?= e($document['file_path']) ?>"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    <i class="bi bi-eye"></i>
                                    View / Download
                                </a>


                                <a
                                    href="delete_document.php?id=<?= (int) $document['id'] ?>&return=teacher_view.php%3Fid%3D<?= (int) $id ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    data-confirm="Delete this document?"
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
                            class="text-center text-muted py-5"
                        >

                            <i class="bi bi-file-earmark fs-2 d-block mb-2"></i>

                            No documents uploaded.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<?php include 'footer.php'; ?>
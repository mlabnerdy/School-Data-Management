<?php

require_once __DIR__ . '/config.php';

require_login();

$id = (int)($_GET['id'] ?? 0);

/*
|--------------------------------------------------------------------------
| Get Student
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);

$r = $stmt->fetch();

if (!$r) {
    redirect('students.php');
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
                'student',
                $id,
                $name,
                $path,
                $type
            ]);
        }
    }

    redirect("student_view.php?id=$id");
}


/*
|--------------------------------------------------------------------------
| Get Student Documents
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT *
    FROM documents
    WHERE owner_type = ?
    AND owner_id = ?
    ORDER BY uploaded_at DESC
");

$stmt->execute(['student', $id]);

$docs = $stmt->fetchAll();

$pageTitle = 'Student Details';

include __DIR__ . '/header.php';

?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

    <div>
        <h2 class="fw-bold mb-1">
            <?= e($r['full_name']) ?>
        </h2>

        <p class="text-muted mb-0">
            <?= e($r['student_id']) ?>
        </p>
    </div>

    <div>

        <a
            class="btn btn-outline-secondary"
            href="students.php"
        >
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

        <a
            class="btn btn-primary"
            href="student_form.php?id=<?= $id ?>"
        >
            <i class="bi bi-pencil"></i>
            Edit
        </a>

    </div>

</div>


<!-- Student Information -->
<div class="row g-4">

    <!-- Information -->
    <div class="col-lg-8">

        <div class="card p-4">

            <h5 class="fw-bold mb-4">
                Student Information
            </h5>

            <div class="row g-4">

                <div class="col-md-6">
                    <strong>Full Name</strong>
                    <div>
                        <?= e($r['full_name']) ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <strong>Student ID</strong>
                    <div>
                        <?= e($r['student_id']) ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <strong>Date of Birth</strong>
                    <div>
                        <?= !empty($r['date_of_birth'])
                            ? e($r['date_of_birth'])
                            : '—' ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <strong>Gender</strong>
                    <div>
                        <?= !empty($r['gender'])
                            ? e($r['gender'])
                            : '—' ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <strong>Contact Number</strong>
                    <div>
                        <?= !empty($r['contact_number'])
                            ? e($r['contact_number'])
                            : '—' ?>
                    </div>
                </div>

                <div class="col-12">
                    <strong>Address</strong>

                    <div>
                        <?= !empty($r['address'])
                            ? nl2br(e($r['address']))
                            : '—' ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <strong>Parent/Guardian</strong>

                    <div>
                        <?= !empty($r['parent_guardian'])
                            ? e($r['parent_guardian'])
                            : '—' ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <strong>Grade/Section</strong>

                    <div>
                        <?= !empty($r['grade_section'])
                            ? e($r['grade_section'])
                            : '—' ?>
                    </div>
                </div>

                <div class="col-12">
                    <strong>Other Relevant Information</strong>

                    <div>
                        <?= !empty($r['other_info'])
                            ? nl2br(e($r['other_info']))
                            : '—' ?>
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
                    alt="Student Photo"
                >

            <?php else: ?>

                <div
                    class="profile-photo mx-auto mb-3 d-flex align-items-center justify-content-center bg-light fs-1 text-muted"
                >
                    <i class="bi bi-person"></i>
                </div>

            <?php endif; ?>

            <h6 class="fw-bold mb-0">
                <?= e($r['full_name']) ?>
            </h6>

            <small class="text-muted">
                Profile Photo
            </small>

        </div>

    </div>

</div>


<!-- Documents -->
<div class="card p-4 mt-4">

    <h5 class="fw-bold mb-3">
        Documents
    </h5>


    <!-- Upload Document -->
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
                    <th>Document</th>
                    <th>Uploaded</th>
                    <th class="text-end">Actions</th>
                </tr>

            </thead>

            <tbody>

                <?php if (!empty($docs)): ?>

                    <?php foreach ($docs as $d): ?>

                        <tr>

                            <td>
                                <i class="bi bi-file-earmark me-1"></i>
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
                                    View / Download
                                </a>

                                <a
                                    data-confirm="Delete this document?"
                                    class="btn btn-sm btn-outline-danger"
                                    href="delete_document.php?id=<?= (int)$d['id'] ?>&return=student_view.php%3Fid%3D<?= $id ?>"
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
                            No documents uploaded.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<?php include __DIR__ . '/footer.php'; ?>
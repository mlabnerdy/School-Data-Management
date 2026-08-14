<?php

require_once __DIR__ . '/config.php';

require_login();

$id = (int)($_GET['id'] ?? 0);

// Get student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);

$r = $stmt->fetch();

if (!$r) {
    redirect('students.php');
}

// Upload document
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

// Get documents
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

<link rel="stylesheet" href="assets/student-view.css">


<!-- Page Header -->
<div class="student-view-header">

    <div class="student-heading">

        <div class="student-heading-icon">
            <i class="bi bi-mortarboard"></i>
        </div>

        <div>

            <h2 class="student-title">
                <?= e($r['full_name']) ?>
            </h2>

            <p class="student-subtitle">
                LRN:
                <strong>
                    <?= !empty($r['lrn']) ? e($r['lrn']) : '—' ?>
                </strong>
            </p>

        </div>

    </div>


    <div class="student-header-actions">

        <a
            href="students.php"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>

        <a
            href="student_form.php?id=<?= $id ?>"
            class="btn btn-primary"
        >
            <i class="bi bi-pencil me-1"></i>
            Edit
        </a>

    </div>

</div>


<!-- Student Profile -->
<div class="row g-4">


    <!-- Information -->
    <div class="col-lg-8">

        <div class="student-card">

            <div class="student-card-header">

                <div>

                    <h5>
                        Student Information
                    </h5>

                    <p>
                        Basic information about the student.
                    </p>

                </div>

                <div class="student-card-icon">
                    <i class="bi bi-person-vcard"></i>
                </div>

            </div>


            <div class="student-info-grid">


                <!-- LRN -->
                <div class="student-info-item">

                    <span class="student-info-label">
                        LRN
                    </span>

                    <span class="student-info-value">
                        <?= !empty($r['lrn'])
                            ? e($r['lrn'])
                            : '—' ?>
                    </span>

                </div>


                <!-- School ID -->
                <div class="student-info-item">

                    <span class="student-info-label">
                        School ID No.
                    </span>

                    <span class="student-info-value">

                        <span class="student-id-badge">
                            <?= e($r['school_id'] ?? '500634') ?>
                        </span>

                    </span>

                </div>


                <!-- Full Name -->
                <div class="student-info-item student-info-full">

                    <span class="student-info-label">
                        Full Name
                    </span>

                    <span class="student-info-value">
                        <?= e($r['full_name']) ?>
                    </span>

                </div>


                <!-- Birthdate -->
                <div class="student-info-item">

                    <span class="student-info-label">
                        Birthdate
                    </span>

                    <span class="student-info-value">
                        <?= !empty($r['date_of_birth'])
                            ? e($r['date_of_birth'])
                            : '—' ?>
                    </span>

                </div>


                <!-- Gender -->
                <div class="student-info-item">

                    <span class="student-info-label">
                        Gender
                    </span>

                    <span class="student-info-value">
                        <?= !empty($r['gender'])
                            ? e($r['gender'])
                            : '—' ?>
                    </span>

                </div>


                <!-- School Year -->
                <div class="student-info-item">

                    <span class="student-info-label">
                        School Year
                    </span>

                    <span class="student-info-value">
                        <?= !empty($r['school_year'])
                            ? e($r['school_year'])
                            : '—' ?>
                    </span>

                </div>


                <!-- Grade Section -->
                <div class="student-info-item">

                    <span class="student-info-label">
                        Grade & Section
                    </span>

                    <span class="student-info-value">

                        <?php if (!empty($r['grade_section'])): ?>

                            <span class="grade-badge">
                                <?= e($r['grade_section']) ?>
                            </span>

                        <?php else: ?>

                            —

                        <?php endif; ?>

                    </span>

                </div>


                <!-- Contact -->
                <div class="student-info-item">

                    <span class="student-info-label">
                        Contact Number
                    </span>

                    <span class="student-info-value">

                        <?php if (!empty($r['contact_number'])): ?>

                            <span class="student-contact">
                                <i class="bi bi-telephone"></i>
                                <?= e($r['contact_number']) ?>
                            </span>

                        <?php else: ?>

                            —

                        <?php endif; ?>

                    </span>

                </div>


                <!-- Address -->
                <div class="student-info-item student-info-full">

                    <span class="student-info-label">
                        Address
                    </span>

                    <span class="student-info-value">
                        <?= !empty($r['address'])
                            ? nl2br(e($r['address']))
                            : '—' ?>
                    </span>

                </div>


                <!-- Parent Guardian -->
                <div class="student-info-item student-info-full">

                    <span class="student-info-label">
                        Parent / Guardian
                    </span>

                    <span class="student-info-value">
                        <?= !empty($r['parent_guardian'])
                            ? e($r['parent_guardian'])
                            : '—' ?>
                    </span>

                </div>


                <!-- Emergency -->
                <div class="student-info-item student-info-full">

                    <span class="student-info-label">
                        In Case of Emergency
                    </span>

                    <div class="student-info-value">

                        <div class="mb-2">
                            <strong>Name:</strong>
                            <?= !empty($r['emergency_name'])
                                ? e($r['emergency_name'])
                                : '—' ?>
                        </div>

                        <div class="mb-2">
                            <strong>Address:</strong>
                            <?= !empty($r['emergency_address'])
                                ? nl2br(e($r['emergency_address']))
                                : '—' ?>
                        </div>

                        <div>
                            <strong>Contact No.:</strong>

                            <?php if (!empty($r['emergency_contact'])): ?>

                                <span class="student-contact">
                                    <i class="bi bi-telephone"></i>
                                    <?= e($r['emergency_contact']) ?>
                                </span>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- Other Information -->
                <div class="student-info-item student-info-full">

                    <span class="student-info-label">
                        Other Relevant Information
                    </span>

                    <span class="student-info-value">
                        <?= !empty($r['other_info'])
                            ? nl2br(e($r['other_info']))
                            : '—' ?>
                    </span>

                </div>

            </div>

        </div>

    </div>


    <!-- Profile Photo -->
    <div class="col-lg-4">

        <div class="student-card profile-card">

            <div class="student-card-header">

                <div>

                    <h5>
                        Profile Photo
                    </h5>

                    <p>
                        Student profile picture.
                    </p>

                </div>

                <div class="student-card-icon">
                    <i class="bi bi-camera"></i>
                </div>

            </div>


            <div class="profile-photo-wrapper">

                <?php if (!empty($r['photo'])): ?>

                    <img
                        src="<?= e($r['photo']) ?>"
                        class="student-profile-photo"
                        alt="<?= e($r['full_name']) ?>"
                    >

                <?php else: ?>

                    <div class="student-profile-placeholder">
                        <i class="bi bi-person"></i>
                    </div>

                <?php endif; ?>

            </div>


            <div class="profile-name">
                <?= e($r['full_name']) ?>
            </div>

            <div class="profile-id">
                LRN:
                <?= !empty($r['lrn'])
                    ? e($r['lrn'])
                    : '—' ?>
            </div>

        </div>

    </div>

</div>


<!-- Documents -->
<div class="student-card documents-card">

    <div class="student-card-header">

        <div>

            <h5>
                Documents
            </h5>

            <p>
                Upload and manage student documents.
            </p>

        </div>

        <div class="student-card-icon">
            <i class="bi bi-folder2-open"></i>
        </div>

    </div>


    <!-- Upload Document -->
    <form
        method="post"
        enctype="multipart/form-data"
        class="document-upload-form"
    >

        <div class="document-input-wrapper">

            <i class="bi bi-cloud-arrow-up"></i>

            <input
                class="form-control"
                type="file"
                name="document"
                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"
                required
            >

        </div>


        <button
            type="submit"
            class="btn btn-primary document-upload-btn"
            name="upload_document"
            value="1"
        >
            <i class="bi bi-upload me-1"></i>
            Upload Document
        </button>

    </form>


    <div class="document-help">
        Accepted files: PDF, DOC, DOCX, JPG, JPEG, PNG, WEBP.
        Maximum file size: 10 MB.
    </div>


    <!-- Documents -->
    <div class="documents-table-wrapper">

        <div class="table-responsive">

            <table class="table student-documents-table align-middle mb-0">

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

                                    <div class="document-name">

                                        <div class="document-icon">
                                            <i class="bi bi-file-earmark"></i>
                                        </div>

                                        <div>

                                            <div class="fw-semibold">
                                                <?= e($d['document_name']) ?>
                                            </div>

                                            <?php if (!empty($d['file_type'])): ?>

                                                <small class="text-muted">
                                                    <?= e($d['file_type']) ?>
                                                </small>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <span class="document-date">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= e($d['uploaded_at']) ?>
                                    </span>

                                </td>


                                <td>

                                    <div class="document-actions">

                                        <a
                                            href="<?= e($d['file_path']) ?>"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary"
                                            title="View or Download"
                                        >
                                            <i class="bi bi-eye"></i>
                                            <span>View</span>
                                        </a>


                                        <a
                                            href="delete_document.php?id=<?= (int)$d['id'] ?>&return=student_view.php%3Fid%3D<?= $id ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            data-confirm="Delete this document?"
                                            title="Delete Document"
                                        >
                                            <i class="bi bi-trash"></i>
                                            <span>Delete</span>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="3">

                                <div class="documents-empty">

                                    <div class="documents-empty-icon">
                                        <i class="bi bi-folder2-open"></i>
                                    </div>

                                    <h6>
                                        No documents uploaded
                                    </h6>

                                    <p>
                                        Upload a document using the form above.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include 'footer.php'; ?>
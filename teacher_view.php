<?php

require_once __DIR__ . '/config.php';

require_login();

$pageTitle = 'Teacher Details';

$id = (int) ($_GET['id'] ?? 0);


/* =========================================================
   GET TEACHER
========================================================= */

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


/* =========================================================
   UPLOAD DOCUMENT
========================================================= */

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


/* =========================================================
   GET TEACHER DOCUMENTS
========================================================= */

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


/* =========================================================
   HELPER FOR EMPTY VALUES
========================================================= */

function teacher_value($value)
{
    return !empty($value) ? e($value) : '—';
}


/* =========================================================
   HEADER
========================================================= */

include __DIR__ . '/header.php';

?>

<link rel="stylesheet" href="assets/teacher_view.css">


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="teacher-view-header">

    <div class="teacher-header-left">

        <div class="teacher-page-icon">
            <i class="bi bi-person-vcard"></i>
        </div>

        <div>
            <h2 class="fw-bold mb-1">
                Teacher Details
            </h2>

            <p class="text-muted mb-0">
                View and manage teacher information.
            </p>
        </div>

    </div>


    <div class="teacher-header-actions">

        <a
            href="teachers.php"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>

        <a
            href="teacher_form.php?id=<?= (int) $id ?>"
            class="btn btn-primary"
        >
            <i class="bi bi-pencil me-1"></i>
            Edit
        </a>

    </div>

</div>


<!-- =========================================================
     TEACHER PROFILE
========================================================= -->

<div class="teacher-profile-card">

    <!-- Profile Picture -->

    <div class="teacher-profile-photo">

        <?php if (!empty($teacher['photo'])): ?>

            <img
                src="<?= e($teacher['photo']) ?>"
                alt="<?= e($teacher['full_name']) ?>"
            >

        <?php else: ?>

            <div class="teacher-photo-placeholder">
                <i class="bi bi-person"></i>
            </div>

        <?php endif; ?>

    </div>


    <!-- Profile Information -->

    <div class="teacher-profile-info">

        <div class="teacher-profile-label">
            TEACHER PROFILE
        </div>

        <h3 class="fw-bold mb-1">
            <?= teacher_value($teacher['full_name']) ?>
        </h3>

        <p class="text-muted mb-2">
            <?= teacher_value($teacher['position_department']) ?>
        </p>

        <span class="teacher-id-badge">
            <i class="bi bi-person-badge me-1"></i>
            Employee No.
            <?= teacher_value($teacher['employee_id']) ?>
        </span>

    </div>

</div>


<!-- =========================================================
     BASIC INFORMATION
========================================================= -->

<div class="teacher-info-card mt-4">

    <div class="teacher-card-header">

        <div>
            <h5 class="fw-bold mb-1">
                Basic Information
            </h5>

            <p class="text-muted small mb-0">
                Personal information of the teacher.
            </p>
        </div>

        <div class="teacher-card-icon">
            <i class="bi bi-person"></i>
        </div>

    </div>


    <div class="teacher-info-grid">

        <!-- Contact Number -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-telephone"></i>
                Contact Number
            </div>

            <div class="teacher-info-value">
                <?= teacher_value($teacher['contact_number']) ?>
            </div>

        </div>


        <!-- Birthdate -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-calendar-event"></i>
                Birthdate
            </div>

            <div class="teacher-info-value">
                <?= teacher_value($teacher['date_of_birth']) ?>
            </div>

        </div>


        <!-- Gender -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-gender-ambiguous"></i>
                Gender
            </div>

            <div class="teacher-info-value">
                <?= teacher_value($teacher['gender']) ?>
            </div>

        </div>


        <!-- Address -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-geo-alt"></i>
                Address
            </div>

            <div class="teacher-info-value">
                <?=
                    !empty($teacher['address'])
                        ? nl2br(e($teacher['address']))
                        : '—'
                ?>
            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     EMPLOYMENT INFORMATION
========================================================= -->

<div class="teacher-info-card mt-4">

    <div class="teacher-card-header">

        <div>
            <h5 class="fw-bold mb-1">
                Employment Information
            </h5>

            <p class="text-muted small mb-0">
                Employment and appointment details.
            </p>
        </div>

        <div class="teacher-card-icon">
            <i class="bi bi-briefcase"></i>
        </div>

    </div>


    <div class="teacher-info-grid">

        <!-- Employee No. -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-person-badge"></i>
                Employee No.
            </div>

            <div class="teacher-info-value">
                <?= teacher_value($teacher['employee_id']) ?>
            </div>

        </div>


        <!-- Plantilla No. -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-card-list"></i>
                Plantilla No.
            </div>

            <div class="teacher-info-value">
                <?= teacher_value($teacher['plantilla_no']) ?>
            </div>

        </div>


        <!-- TIN No. -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-credit-card"></i>
                TIN No.
            </div>

            <div class="teacher-info-value">
                <?= teacher_value($teacher['tin_no']) ?>
            </div>

        </div>


        <!-- First Day of Service -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-calendar-check"></i>
                First Day of Service
            </div>

            <div class="teacher-info-value">
                <?= teacher_value($teacher['first_day_of_service']) ?>
            </div>

        </div>


        <!-- Position / Department -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-building"></i>
                Position / Department
            </div>

            <div class="teacher-info-value">
                <?= teacher_value($teacher['position_department']) ?>
            </div>

        </div>


        <!-- Current / Latest Appointment -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-briefcase"></i>
                Current / Latest Appointment
            </div>

            <div class="teacher-info-value">
                <?= teacher_value($teacher['current_latest_appointment']) ?>
            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     EDUCATION & ELIGIBILITY
========================================================= -->

<div class="teacher-info-card mt-4">

    <div class="teacher-card-header">

        <div>
            <h5 class="fw-bold mb-1">
                Education &amp; Eligibility
            </h5>

            <p class="text-muted small mb-0">
                Educational background and professional eligibility.
            </p>
        </div>

        <div class="teacher-card-icon">
            <i class="bi bi-mortarboard"></i>
        </div>

    </div>


    <div class="teacher-info-grid">

        <!-- Degree Finished -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-mortarboard"></i>
                Degree Finished
            </div>

            <div class="teacher-info-value">
                <?= teacher_value($teacher['degree_finished']) ?>
            </div>

        </div>


        <!-- Specialization / PRC Eligibility -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-award"></i>
                Specialization / PRC Eligibility
            </div>

            <div class="teacher-info-value">
                <?=
                    !empty($teacher['specialization_prc_eligibility'])
                        ? nl2br(e($teacher['specialization_prc_eligibility']))
                        : '—'
                ?>
            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     CONTACT INFORMATION
========================================================= -->

<div class="teacher-info-card mt-4">

    <div class="teacher-card-header">

        <div>
            <h5 class="fw-bold mb-1">
                Contact Information
            </h5>

            <p class="text-muted small mb-0">
                Official and personal email information.
            </p>
        </div>

        <div class="teacher-card-icon">
            <i class="bi bi-envelope"></i>
        </div>

    </div>


    <div class="teacher-info-grid">

        <!-- DepEd Email -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-envelope"></i>
                DepEd Email
            </div>

            <div class="teacher-info-value teacher-email">
                <?= teacher_value($teacher['deped_email']) ?>
            </div>

        </div>


        <!-- Personal Email -->
        <div class="teacher-info-item">

            <div class="teacher-info-label">
                <i class="bi bi-envelope-at"></i>
                Personal Email
            </div>

            <div class="teacher-info-value teacher-email">
                <?= teacher_value($teacher['personal_email']) ?>
            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     ADDITIONAL INFORMATION
========================================================= -->

<div class="teacher-info-card mt-4">

    <div class="teacher-card-header">

        <div>
            <h5 class="fw-bold mb-1">
                Additional Information
            </h5>

            <p class="text-muted small mb-0">
                Other relevant information about the teacher.
            </p>
        </div>

        <div class="teacher-card-icon">
            <i class="bi bi-info-circle"></i>
        </div>

    </div>


    <div class="teacher-additional-info">

        <div class="teacher-additional-item">

            <div class="teacher-info-label">
                <i class="bi bi-card-text"></i>
                Other Relevant Information
            </div>

            <div class="teacher-info-value">
                <?=
                    !empty($teacher['other_info'])
                        ? nl2br(e($teacher['other_info']))
                        : '—'
                ?>
            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     DOCUMENTS
========================================================= -->

<div class="teacher-info-card mt-4">

    <div class="teacher-card-header">

        <div>
            <h5 class="fw-bold mb-1">
                Documents
            </h5>

            <p class="text-muted small mb-0">
                Upload and manage teacher documents.
            </p>
        </div>

        <div class="teacher-card-icon">
            <i class="bi bi-folder"></i>
        </div>

    </div>


    <!-- Upload -->

    <form
        method="POST"
        enctype="multipart/form-data"
        class="teacher-upload-form"
    >

        <div class="teacher-file-input">

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


        <button
            type="submit"
            name="upload_document"
            value="1"
            class="btn btn-primary teacher-upload-btn"
        >
            <i class="bi bi-upload me-1"></i>
            Upload Document
        </button>

    </form>


    <!-- Document Table -->

    <div class="teacher-documents-table table-responsive">

        <table class="table align-middle mb-0">

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

                                <div class="teacher-document-name">

                                    <div class="teacher-document-icon">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>

                                    <span>
                                        <?= e($document['document_name']) ?>
                                    </span>

                                </div>

                            </td>


                            <td>
                                <?= e($document['uploaded_at']) ?>
                            </td>


                            <td class="text-end">

                                <div class="teacher-document-actions">

                                    <a
                                        href="<?= e($document['file_path']) ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary"
                                        title="View Document"
                                    >
                                        <i class="bi bi-eye"></i>
                                        <span>View</span>
                                    </a>

                                    <a
                                        href="delete_document.php?id=<?= (int) $document['id'] ?>&return=<?= urlencode('teacher_view.php?id=' . $id) ?>"
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

                            <div class="teacher-empty-documents text-center">

                                <div class="teacher-empty-icon">
                                    <i class="bi bi-folder2-open"></i>
                                </div>

                                <h6 class="fw-bold mb-1">
                                    No documents uploaded
                                </h6>

                                <p class="text-muted small mb-0">
                                    Teacher documents will appear here.
                                </p>

                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<?php include __DIR__ . '/footer.php'; ?>
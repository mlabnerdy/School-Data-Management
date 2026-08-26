<?php

require_once __DIR__ . '/config.php';

require_login();

$id = (int) ($_GET['id'] ?? 0);


/* =========================================================
   GET STAFF RECORD
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM staff
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$staff = $stmt->fetch();

if (!$staff) {
    redirect('staff.php');
}


/* =========================================================
   HELPER FOR EMPTY VALUES
========================================================= */

function staff_value($value)
{
    return !empty($value) ? e($value) : '—';
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

            $documentName =
                $_FILES['document']['name'] ?? 'Document';

            $documentType =
                $_FILES['document']['type'] ?? '';

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
                'staff',
                $id,
                $documentName,
                $path,
                $documentType
            ]);
        }
    }

    redirect("staff_view.php?id=" . $id);
}


/* =========================================================
   GET STAFF DOCUMENTS
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM documents
    WHERE owner_type = ?
    AND owner_id = ?
    ORDER BY uploaded_at DESC
");

$stmt->execute([
    'staff',
    $id
]);

$documents = $stmt->fetchAll();


/* =========================================================
   PAGE
========================================================= */

$pageTitle = 'Staff Details';

include __DIR__ . '/header.php';

?>

<link rel="stylesheet" href="assets/staff_view.css">


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="staff-view-header">

    <div class="staff-header-left">

        <div class="staff-page-icon">
            <i class="bi bi-person-badge"></i>
        </div>

        <div>

            <h2 class="fw-bold mb-1">
                Staff Details
            </h2>

            <p class="text-muted mb-0">
                View and manage staff information.
            </p>

        </div>

    </div>


    <div class="staff-header-actions">

        <a
            href="staff.php"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>

        <a
            href="staff_form.php?id=<?= (int) $id ?>"
            class="btn btn-primary"
        >
            <i class="bi bi-pencil me-1"></i>
            Edit
        </a>

    </div>

</div>


<!-- =========================================================
     STAFF PROFILE
========================================================= -->

<div class="staff-profile-card">

    <div class="staff-profile-photo">

        <?php if (!empty($staff['photo'])): ?>

            <img
                src="<?= e($staff['photo']) ?>"
                alt="<?= e($staff['full_name'] ?? 'Staff') ?>"
            >

        <?php else: ?>

            <div class="staff-photo-placeholder">
                <i class="bi bi-person"></i>
            </div>

        <?php endif; ?>

    </div>


    <div class="staff-profile-info">

        <div class="staff-profile-label">
            STAFF PROFILE
        </div>

        <h3 class="fw-bold mb-1">
            <?= staff_value($staff['full_name'] ?? '') ?>
        </h3>

        <p class="text-muted mb-2">
            <?= staff_value($staff['position_department'] ?? '') ?>
        </p>

        <span class="staff-id-badge">

            <i class="bi bi-person-badge me-1"></i>

            Employee No.

            <?= staff_value($staff['employee_id'] ?? '') ?>

        </span>

    </div>

</div>


<!-- =========================================================
     BASIC INFORMATION
========================================================= -->

<div class="staff-info-card mt-4">

    <div class="staff-card-header">

        <div>

            <h5 class="fw-bold mb-1">
                Basic Information
            </h5>

            <p class="text-muted small mb-0">
                Personal information and basic details.
            </p>

        </div>

        <div class="staff-card-icon">
            <i class="bi bi-person-vcard"></i>
        </div>

    </div>


    <div class="staff-info-grid">


        <!-- Employee ID -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-person-badge"></i>

                Staff / Employee ID

            </div>

            <div class="staff-info-value">
                <?= staff_value($staff['employee_id'] ?? '') ?>
            </div>

        </div>


        <!-- Contact Number -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-telephone"></i>

                Contact Number

            </div>

            <div class="staff-info-value">
                <?= staff_value($staff['contact_number'] ?? '') ?>
            </div>

        </div>


        <!-- Birthdate -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-calendar-event"></i>

                Birthdate

            </div>

            <div class="staff-info-value">
                <?= staff_value($staff['date_of_birth'] ?? '') ?>
            </div>

        </div>


        <!-- Gender -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-gender-ambiguous"></i>

                Gender

            </div>

            <div class="staff-info-value">
                <?= staff_value($staff['gender'] ?? '') ?>
            </div>

        </div>


        <!-- Address -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-geo-alt"></i>

                Address

            </div>

            <div class="staff-info-value">

                <?php if (!empty($staff['address'])): ?>

                    <?= nl2br(e($staff['address'])) ?>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     EMPLOYMENT INFORMATION
========================================================= -->

<div class="staff-info-card mt-4">

    <div class="staff-card-header">

        <div>

            <h5 class="fw-bold mb-1">
                Employment Information
            </h5>

            <p class="text-muted small mb-0">
                Staff employment and appointment details.
            </p>

        </div>

        <div class="staff-card-icon">
            <i class="bi bi-briefcase"></i>
        </div>

    </div>


    <div class="staff-info-grid">


        <!-- Employee No. -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-person-badge"></i>

                Employee No.

            </div>

            <div class="staff-info-value">
                <?= staff_value($staff['employee_id'] ?? '') ?>
            </div>

        </div>


        <!-- Plantilla No. -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-card-list"></i>

                Plantilla No.

            </div>

            <div class="staff-info-value">
                <?= staff_value($staff['plantilla_no'] ?? '') ?>
            </div>

        </div>


        <!-- TIN No. -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-credit-card"></i>

                TIN No.

            </div>

            <div class="staff-info-value">
                <?= staff_value($staff['tin_no'] ?? '') ?>
            </div>

        </div>


        <!-- First Day of Service -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-calendar-check"></i>

                First Day of Service

            </div>

            <div class="staff-info-value">
                <?= staff_value($staff['first_day_of_service'] ?? '') ?>
            </div>

        </div>


        <!-- Position / Department -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-building"></i>

                Position / Department

            </div>

            <div class="staff-info-value">
                <?= staff_value($staff['position_department'] ?? '') ?>
            </div>

        </div>


        <!-- Current / Latest Appointment -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-briefcase"></i>

                Current / Latest Appointment

            </div>

            <div class="staff-info-value">
                <?= staff_value($staff['current_latest_appointment'] ?? '') ?>
            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     GOVERNMENT & COMPENSATION INFORMATION
========================================================= -->

<div class="staff-info-card mt-4">

    <div class="staff-card-header">

        <div>

            <h5 class="fw-bold mb-1">
                Government &amp; Compensation Information
            </h5>

            <p class="text-muted small mb-0">
                Government identification and salary information.
            </p>

        </div>

        <div class="staff-card-icon">
            <i class="bi bi-bank"></i>
        </div>

    </div>


    <div class="staff-info-grid">


        <!-- GSIS No. -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-credit-card-2-front"></i>

                GSIS No.

            </div>

            <div class="staff-info-value">

                <?php if (array_key_exists('gsis_no', $staff)): ?>

                    <?= staff_value($staff['gsis_no']) ?>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>

        </div>


        <!-- PhilHealth No. -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-heart-pulse"></i>

                PhilHealth No.

            </div>

            <div class="staff-info-value">

                <?php if (array_key_exists('philhealth_no', $staff)): ?>

                    <?= staff_value($staff['philhealth_no']) ?>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>

        </div>


        <!-- Salary Grade -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-cash-stack"></i>

                Salary Grade

            </div>

            <div class="staff-info-value">

                <?php if (
                    array_key_exists('salary_grade', $staff) &&
                    $staff['salary_grade'] !== null &&
                    $staff['salary_grade'] !== ''
                ): ?>

                    SG <?= e($staff['salary_grade']) ?>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>

        </div>


        <!-- Step -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-bar-chart-steps"></i>

                Step

            </div>

            <div class="staff-info-value">

                <?php if (
                    array_key_exists('step', $staff) &&
                    $staff['step'] !== null &&
                    $staff['step'] !== ''
                ): ?>

                    Step <?= e($staff['step']) ?>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     EDUCATION & ELIGIBILITY
========================================================= -->

<div class="staff-info-card mt-4">

    <div class="staff-card-header">

        <div>

            <h5 class="fw-bold mb-1">
                Education &amp; Eligibility
            </h5>

            <p class="text-muted small mb-0">
                Educational background and professional eligibility information.
            </p>

        </div>

        <div class="staff-card-icon">
            <i class="bi bi-mortarboard"></i>
        </div>

    </div>


    <div class="staff-info-grid">


        <!-- Degree Finished -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-mortarboard"></i>

                Degree Finished

            </div>

            <div class="staff-info-value">
                <?= staff_value($staff['degree_finished'] ?? '') ?>
            </div>

        </div>


        <!-- Specialization / PRC -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-award"></i>

                Specialization / PRC Eligibility

            </div>

            <div class="staff-info-value">

                <?php if (
                    !empty(
                        $staff['specialization_prc_eligibility'] ?? ''
                    )
                ): ?>

                    <?= nl2br(
                        e(
                            $staff[
                                'specialization_prc_eligibility'
                            ]
                        )
                    ) ?>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     TRAINING & ACCOMPLISHMENTS
========================================================= -->

<div class="staff-info-card mt-4">

    <div class="staff-card-header">

        <div>

            <h5 class="fw-bold mb-1">
                Training &amp; Accomplishments
            </h5>

            <p class="text-muted small mb-0">
                Training, seminars, certifications, awards, and other accomplishments.
            </p>

        </div>

        <div class="staff-card-icon">
            <i class="bi bi-trophy"></i>
        </div>

    </div>


    <div class="staff-additional-info">

        <div class="staff-additional-item">

            <div class="staff-info-label">

                <i class="bi bi-award"></i>

                Training and Accomplishments

            </div>

            <div class="staff-info-value">

                <?php if (
                    array_key_exists(
                        'training_accomplishments',
                        $staff
                    ) &&
                    !empty(
                        $staff['training_accomplishments']
                    )
                ): ?>

                    <?= nl2br(
                        e(
                            $staff[
                                'training_accomplishments'
                            ]
                        )
                    ) ?>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     CONTACT INFORMATION
========================================================= -->

<div class="staff-info-card mt-4">

    <div class="staff-card-header">

        <div>

            <h5 class="fw-bold mb-1">
                Contact Information
            </h5>

            <p class="text-muted small mb-0">
                Official and personal contact details.
            </p>

        </div>

        <div class="staff-card-icon">
            <i class="bi bi-envelope"></i>
        </div>

    </div>


    <div class="staff-info-grid">


        <!-- DepEd Email -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-envelope"></i>

                DepEd Email

            </div>

            <div class="staff-info-value staff-email">

                <?php if (!empty($staff['deped_email'])): ?>

                    <a
                        href="mailto:<?= e($staff['deped_email']) ?>"
                        class="text-decoration-none"
                    >
                        <?= e($staff['deped_email']) ?>
                    </a>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>

        </div>


        <!-- Personal Email -->

        <div class="staff-info-item">

            <div class="staff-info-label">

                <i class="bi bi-envelope-at"></i>

                Personal Email

            </div>

            <div class="staff-info-value staff-email">

                <?php if (!empty($staff['personal_email'])): ?>

                    <a
                        href="mailto:<?= e($staff['personal_email']) ?>"
                        class="text-decoration-none"
                    >
                        <?= e($staff['personal_email']) ?>
                    </a>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     ADDITIONAL INFORMATION
========================================================= -->

<div class="staff-info-card mt-4">

    <div class="staff-card-header">

        <div>

            <h5 class="fw-bold mb-1">
                Additional Information
            </h5>

            <p class="text-muted small mb-0">
                Other relevant staff information.
            </p>

        </div>

        <div class="staff-card-icon">
            <i class="bi bi-info-circle"></i>
        </div>

    </div>


    <div class="staff-additional-info">

        <div class="staff-additional-item">

            <div class="staff-info-label">

                <i class="bi bi-card-text"></i>

                Other Relevant Information

            </div>

            <div class="staff-info-value">

                <?php if (!empty($staff['other_info'])): ?>

                    <?= nl2br(e($staff['other_info'])) ?>

                <?php else: ?>

                    —

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     DOCUMENTS
========================================================= -->

<div class="staff-info-card mt-4">

    <div class="staff-card-header">

        <div>

            <h5 class="fw-bold mb-1">
                Documents
            </h5>

            <p class="text-muted small mb-0">
                Upload and manage staff documents.
            </p>

        </div>

        <div class="staff-card-icon">
            <i class="bi bi-folder"></i>
        </div>

    </div>


    <!-- =====================================================
         UPLOAD DOCUMENT
    ====================================================== -->

    <form
        method="POST"
        enctype="multipart/form-data"
        class="staff-upload-form"
    >

        <div class="staff-file-input">

            <input
                type="file"
                name="document"
                class="form-control"
                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"
                required
            >

            <div class="form-text">
                PDF, DOC, DOCX, JPG, PNG, or WEBP.
                Maximum 10 MB.
            </div>

        </div>


        <button
            type="submit"
            name="upload_document"
            value="1"
            class="btn btn-primary staff-upload-btn"
        >

            <i class="bi bi-upload me-1"></i>

            Upload Document

        </button>

    </form>


    <!-- =====================================================
         DOCUMENT TABLE
    ====================================================== -->

    <div class="staff-documents-table table-responsive">

        <table class="table align-middle mb-0">

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

                <?php if (!empty($documents)): ?>

                    <?php foreach ($documents as $document): ?>

                        <tr>

                            <td>

                                <div class="staff-document-name">

                                    <div class="staff-document-icon">

                                        <i class="bi bi-file-earmark-text"></i>

                                    </div>

                                    <span>

                                        <?= e(
                                            $document['document_name']
                                            ?? 'Document'
                                        ) ?>

                                    </span>

                                </div>

                            </td>


                            <td>

                                <?= e(
                                    $document['uploaded_at']
                                    ?? '—'
                                ) ?>

                            </td>


                            <td class="text-end">

                                <div class="staff-document-actions">


                                    <!-- View -->

                                    <a
                                        href="<?= e(
                                            $document['file_path']
                                            ?? '#'
                                        ) ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary"
                                        title="View Document"
                                    >

                                        <i class="bi bi-eye"></i>

                                        <span>
                                            View
                                        </span>

                                    </a>


                                    <!-- Delete -->

                                    <a
                                        href="delete_document.php?id=<?= (int)($document['id'] ?? 0) ?>&return=<?= urlencode('staff_view.php?id=' . $id) ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        data-confirm="Delete this document?"
                                        title="Delete Document"
                                    >

                                        <i class="bi bi-trash"></i>

                                        <span>
                                            Delete
                                        </span>

                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="3">

                            <div class="staff-empty-documents text-center">

                                <div class="staff-empty-icon">

                                    <i class="bi bi-folder2-open"></i>

                                </div>

                                <h6 class="fw-bold mb-1">
                                    No documents uploaded
                                </h6>

                                <p class="text-muted small mb-0">
                                    Staff documents will appear here.
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
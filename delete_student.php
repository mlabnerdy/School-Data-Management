<?php

require_once __DIR__ . '/config.php';

require_login();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('students.php');
}


/* Get student */

$stmt = $pdo->prepare("
    SELECT photo
    FROM students
    WHERE id = ?
");

$stmt->execute([$id]);

$student = $stmt->fetch();

if (!$student) {
    redirect('students.php');
}


/* Teacher password confirmation */

$userRole = $_SESSION['role'] ?? '';

if ($userRole === 'Teacher') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        include 'header.php';
        ?>

        <div class="container py-5">

            <div class="row justify-content-center">

                <div class="col-md-5">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body p-4">

                            <div class="text-center mb-4">

                                <i class="bi bi-shield-lock text-danger fs-1"></i>

                                <h4 class="fw-bold mt-3">
                                    Confirm Deletion
                                </h4>

                                <p class="text-muted">
                                    Enter your account password to delete this student.
                                </p>

                            </div>


                            <form method="POST">

                                <div class="mb-3">

                                    <label class="form-label">
                                        Password
                                    </label>

                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Enter your password"
                                        required
                                    >

                                </div>


                                <div class="d-flex gap-2">

                                    <a
                                        href="students.php"
                                        class="btn btn-light w-50"
                                    >
                                        Cancel
                                    </a>

                                    <button
                                        type="submit"
                                        class="btn btn-danger w-50"
                                    >
                                        <i class="bi bi-trash me-1"></i>
                                        Confirm Delete
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <?php
        include 'footer.php';

        exit;
    }


    /* Verify password */

    $password = $_POST['password'] ?? '';

    if (empty($password)) {

        redirect('students.php');

    }


    $stmt = $pdo->prepare("
        SELECT password
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$_SESSION['user_id']]);

    $user = $stmt->fetch();


    if (!$user || !password_verify($password, $user['password'])) {

        include 'header.php';
        ?>

        <div class="container py-5">

            <div class="row justify-content-center">

                <div class="col-md-5">

                    <div class="alert alert-danger">

                        <h5 class="alert-heading">
                            Incorrect Password
                        </h5>

                        <p class="mb-3">
                            The password you entered is incorrect.
                            The student was not deleted.
                        </p>

                        <a
                            href="students.php"
                            class="btn btn-danger btn-sm"
                        >
                            Back to Students
                        </a>

                    </div>

                </div>

            </div>

        </div>

        <?php
        include 'footer.php';

        exit;
    }

}


/* Delete profile photo */

if (!empty($student['photo'])) {

    delete_upload($student['photo']);

}


/* Get documents */

$stmt = $pdo->prepare("
    SELECT file_path
    FROM documents
    WHERE owner_type = ?
    AND owner_id = ?
");

$stmt->execute([
    'student',
    $id
]);

$documents = $stmt->fetchAll();


/* Delete document files */

foreach ($documents as $document) {

    if (!empty($document['file_path'])) {

        delete_upload($document['file_path']);

    }

}


/* Delete document records */

$stmt = $pdo->prepare("
    DELETE FROM documents
    WHERE owner_type = ?
    AND owner_id = ?
");

$stmt->execute([
    'student',
    $id
]);


/* Delete student */

$stmt = $pdo->prepare("
    DELETE FROM students
    WHERE id = ?
");

$stmt->execute([$id]);


redirect('students.php');
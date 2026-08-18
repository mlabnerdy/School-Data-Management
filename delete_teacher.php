<?php

require_once __DIR__ . '/config.php';

require_login();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('teachers.php');
}


/*
|--------------------------------------------------------------------------
| Get Teacher
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        full_name,
        photo
    FROM teachers
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$teacher = $stmt->fetch();

if (!$teacher) {
    redirect('teachers.php');
}


/*
|--------------------------------------------------------------------------
| Password Confirmation
|--------------------------------------------------------------------------
*/

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';

    if ($password === '') {

        $error =
            'Please enter your account password.';

    } else {

        $currentUserId =
            (int)($_SESSION['user_id'] ?? 0);


        if ($currentUserId <= 0) {

            $error =
                'Unable to identify your account. Please log in again.';

        } else {

            /*
            |------------------------------------------------------------------
            | Get Logged-in User Password
            |------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT password
                FROM users
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([
                $currentUserId
            ]);

            $user = $stmt->fetch();


            /*
            |------------------------------------------------------------------
            | Verify Password
            |------------------------------------------------------------------
            */

            if (
                !$user ||
                !password_verify(
                    $password,
                    $user['password']
                )
            ) {

                $error =
                    'Incorrect password. The teacher record was not deleted.';

            } else {

                try {

                    $pdo->beginTransaction();


                    /*
                    |----------------------------------------------------------
                    | Delete Teacher Documents
                    |----------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        SELECT file_path
                        FROM documents
                        WHERE owner_type = ?
                        AND owner_id = ?
                    ");

                    $stmt->execute([
                        'teacher',
                        $id
                    ]);

                    $documents =
                        $stmt->fetchAll();


                    /*
                    |----------------------------------------------------------
                    | Delete Document Files
                    |----------------------------------------------------------
                    */

                    foreach ($documents as $document) {

                        if (!empty($document['file_path'])) {

                            delete_upload(
                                $document['file_path']
                            );

                        }
                    }


                    /*
                    |----------------------------------------------------------
                    | Delete Document Records
                    |----------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        DELETE FROM documents
                        WHERE owner_type = ?
                        AND owner_id = ?
                    ");

                    $stmt->execute([
                        'teacher',
                        $id
                    ]);


                    /*
                    |----------------------------------------------------------
                    | Delete Teacher Photo
                    |----------------------------------------------------------
                    */

                    if (!empty($teacher['photo'])) {

                        delete_upload(
                            $teacher['photo']
                        );

                    }


                    /*
                    |----------------------------------------------------------
                    | Delete Teacher
                    |----------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        DELETE FROM teachers
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $id
                    ]);


                    $pdo->commit();


                    /*
                    |----------------------------------------------------------
                    | Success Page
                    |----------------------------------------------------------
                    */

                    include 'header.php';
                    ?>

                    <div class="container py-5">

                        <div class="row justify-content-center">

                            <div class="col-md-6">

                                <div class="card border-0 shadow-sm">

                                    <div class="card-body p-5 text-center">

                                        <div class="mb-3">

                                            <i
                                                class="bi bi-check-circle-fill text-success"
                                                style="font-size: 4rem;"
                                            ></i>

                                        </div>

                                        <h4 class="fw-bold mb-2">

                                            Teacher Deleted Successfully

                                        </h4>

                                        <p class="text-muted mb-4">

                                            The teacher record
                                            <strong>
                                                <?= e(
                                                    $teacher['full_name']
                                                ) ?>
                                            </strong>
                                            has been permanently deleted.

                                        </p>

                                        <a
                                            href="teachers.php"
                                            class="btn btn-primary"
                                        >

                                            <i class="bi bi-arrow-left me-1"></i>

                                            Back to Teachers

                                        </a>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <?php
                    include 'footer.php';
                    exit;

                } catch (Throwable $e) {

                    if ($pdo->inTransaction()) {

                        $pdo->rollBack();

                    }

                    $error =
                        'Unable to delete the teacher record. '
                        . 'Please try again.';
                }
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| Confirmation Page
|--------------------------------------------------------------------------
*/

include 'header.php';

?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <div class="text-center mb-4">

                        <i
                            class="bi bi-shield-lock text-danger"
                            style="font-size: 3.5rem;"
                        ></i>

                        <h4 class="fw-bold mt-3">
                            Confirm Teacher Deletion
                        </h4>

                        <p class="text-muted">

                            You are about to permanently delete

                            <strong>
                                <?= e($teacher['full_name']) ?>
                            </strong>.

                            Enter your account password to continue.

                        </p>

                    </div>


                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <i class="bi bi-exclamation-circle me-2"></i>

                            <?= e($error) ?>

                        </div>

                    <?php endif; ?>


                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label">
                                Account Password
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    name="password"
                                    id="deletePassword"
                                    class="form-control"
                                    placeholder="Enter your password"
                                    required
                                    autocomplete="current-password"
                                >

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    id="togglePassword"
                                    aria-label="Show password"
                                >

                                    <i
                                        class="bi bi-eye"
                                        id="passwordIcon"
                                    ></i>

                                </button>

                            </div>

                        </div>


                        <div class="d-flex gap-2">

                            <a
                                href="teachers.php"
                                class="btn btn-light w-50"
                            >

                                <i class="bi bi-x-lg me-1"></i>

                                Cancel

                            </a>

                            <button
                                type="submit"
                                class="btn btn-danger w-50"
                            >

                                <i class="bi bi-trash me-1"></i>

                                Delete Teacher

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const password =
        document.getElementById('deletePassword');

    const toggle =
        document.getElementById('togglePassword');

    const icon =
        document.getElementById('passwordIcon');


    if (toggle && password && icon) {

        toggle.addEventListener('click', function () {

            if (password.type === 'password') {

                password.type = 'text';

                icon.classList.remove('bi-eye');

                icon.classList.add('bi-eye-slash');

                toggle.setAttribute(
                    'aria-label',
                    'Hide password'
                );

            } else {

                password.type = 'password';

                icon.classList.remove('bi-eye-slash');

                icon.classList.add('bi-eye');

                toggle.setAttribute(
                    'aria-label',
                    'Show password'
                );

            }

        });

    }

});

</script>


<?php include 'footer.php'; ?>
<?php
require_once __DIR__ . '/config.php';

$userRole = $_SESSION['role'] ?? '';
?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?= e($pageTitle ?? 'School Data Management System') ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <link href="assets/style.css" rel="stylesheet">

    <link href="assets/navigation.css" rel="stylesheet">

</head>


<body class="bg-light">


<?php if (!empty($_SESSION['user_id'])): ?>

<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">

    <div class="container-fluid px-4">


        <!-- Logo -->

        <a
            class="navbar-brand fw-bold text-primary"
            href="index.php"
        >

            <i class="bi bi-building"></i>

            School Data Management

        </a>


        <!-- Mobile Button -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNav"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <div
            class="collapse navbar-collapse"
            id="mainNav"
        >


            <!-- Navigation -->

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">


                <!-- Dashboard -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="index.php"
                    >

                        <i class="bi bi-speedometer2 me-1"></i>

                        Dashboard

                    </a>

                </li>


                <!-- Students -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="students.php"
                    >

                        <i class="bi bi-mortarboard me-1"></i>

                        Students

                    </a>

                </li>


                <!-- Teachers -->

                <?php if (
                    $userRole === 'Administrator' ||
                    $userRole === 'Staff'
                ): ?>

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="teachers.php"
                        >

                            <i class="bi bi-person-workspace me-1"></i>

                            Teachers

                        </a>

                    </li>

                <?php endif; ?>


                <!-- Staff -->

                <?php if ($userRole === 'Administrator'): ?>

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="staff.php"
                        >

                            <i class="bi bi-people me-1"></i>

                            Staff

                        </a>

                    </li>

                <?php endif; ?>


            </ul>


            <!-- User -->

            <div class="d-flex align-items-center gap-3">

                <a
                    href="account.php"
                    class="text-decoration-none text-muted small"
                >

                    <i class="bi bi-person-circle me-1"></i>

                    <?= e($_SESSION['full_name'] ?? 'User') ?>

                </a>


                <a
                    class="btn btn-outline-danger btn-sm"
                    href="logout.php"
                >

                    <i class="bi bi-box-arrow-right me-1"></i>

                    Logout

                </a>

            </div>


        </div>

    </div>

</nav>

<?php endif; ?>


<main class="container-fluid px-4 py-4">
<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        redirect('index.php');
    }

    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - School Data Management System</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="assets/style.css" rel="stylesheet">
    <link href="assets/login.css" rel="stylesheet">
</head>

<body class="login-page">

    <div class="login-container">

        <div class="login-card">

            <!-- Logo -->
            <div class="login-logo">
                <img
                    src="assets/logo/logo.jpg"
                    alt="School Logo"
                >
            </div>

            <!-- Header -->
            <div class="text-center mb-4">

                <h2 class="login-title">
                    School Data Management
                </h2>

                <p class="login-subtitle">
                    Sign in to manage school records
                </p>

            </div>

            <!-- Error Message -->
            <?php if ($error): ?>

                <div class="alert alert-danger login-alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= e($error) ?>
                </div>

            <?php endif; ?>

            <!-- Login Form -->
            <form method="post">

                <!-- Username -->
                <div class="mb-3">

                    <label for="username" class="form-label">
                        Username
                    </label>

                    <div class="input-group login-input">

                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            placeholder="Enter your username"
                            autocomplete="username"
                            required
                            autofocus
                        >

                    </div>

                </div>


                <!-- Password -->
                <div class="mb-4">

                    <label for="password" class="form-label">
                        Password
                    </label>

                    <div class="input-group login-input">

                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="btn password-toggle"
                            id="togglePassword"
                            aria-label="Show password"
                        >
                            <i class="bi bi-eye" id="passwordIcon"></i>
                        </button>

                    </div>

                </div>


                <!-- Login Button -->
                <button
                    type="submit"
                    class="btn login-btn w-100"
                >
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Sign In
                </button>

            </form>


            <!-- Default Account -->
            <div class="login-help">

                <i class="bi bi-info-circle me-1"></i>

                <span>
                    Default account:
                    <strong>admin</strong> /
                    <strong>adminako123</strong>
                </span>

            </div>


            <!-- Footer -->
            <div class="login-footer">
                School Data Management System
            </div>

        </div>

    </div>


    <!-- Password Toggle -->
    <script>

        const togglePassword =
            document.getElementById('togglePassword');

        const password =
            document.getElementById('password');

        const passwordIcon =
            document.getElementById('passwordIcon');


        togglePassword.addEventListener('click', function () {

            const isPassword =
                password.getAttribute('type') === 'password';

            password.setAttribute(
                'type',
                isPassword ? 'text' : 'password'
            );

            passwordIcon.classList.toggle(
                'bi-eye',
                !isPassword
            );

            passwordIcon.classList.toggle(
                'bi-eye-slash',
                isPassword
            );

            togglePassword.setAttribute(
                'aria-label',
                isPassword
                    ? 'Hide password'
                    : 'Show password'
            );

        });

    </script>
</body>
</html>


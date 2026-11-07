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

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];

        redirect('index.php');
    }

    $error = 'Invalid username or password.';
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="Sta. Elena Integrated School Data Management System">
    <title>Login - Sta. Elena Integrated School</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="assets/style.css" rel="stylesheet">
    <link href="assets/login.css" rel="stylesheet">
</head>

<body class="login-page">

<div class="login-wrapper">

    <!-- =====================================================
         LEFT SIDE - SCHOOL INFORMATION
         ===================================================== -->
    <section class="school-section">
        <div class="school-overlay"></div>

        <div class="school-content">

            <div class="school-brand">
                <div class="school-logo">
                    <img src="assets/logo/schoollogo.png"
                         alt="Sta. Elena Integrated School Logo"
                         loading="eager">
                </div>

                <div>
                    <span class="school-kicker">ESTABLISHED 1950</span>
                    <h1 class="school-name">Sta. Elena<br>Integrated School</h1>
                    <p class="school-location">
                        <i class="bi bi-geo-alt-fill"></i>
                        Población, Sta. Elena, Camarines Norte
                    </p>
                </div>
            </div>

            <div class="school-intro">
                <span class="section-label">OUR STORY</span>
                <h2>Rooted in History.<br>Focused on the Future.</h2>
                <p>
                    From a small elementary school established in 1950,
                    Sta. Elena Integrated School has grown into a community
                    committed to providing meaningful and inclusive education
                    for every learner.
                </p>
            </div>

            <!-- HISTORY -->
            <div class="history-card">
                <div class="card-heading">
                    <div class="heading-icon"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <span class="section-label">HISTORY OF THE SCHOOL</span>
                        <h3>Our Journey</h3>
                    </div>
                </div>

                <div class="history-timeline">
                    <div class="timeline-item">
                        <span class="timeline-year">1950</span>
                        <p>
                            The school was established at Brgy. Población, Sta. Elena,
                            Camarines Norte, on a 1,573 sq.m. site donated by the
                            Codiamat and Del Moro families. Mr. Antonio Fermo served
                            as Teacher-in-Charge from 1950 to 1958.
                        </p>
                    </div>

                    <div class="timeline-item">
                        <span class="timeline-year">1968</span>
                        <p>
                            The school grew and an School Principal I was assigned.
                        </p>
                    </div>

                    <div class="timeline-item">
                        <span class="timeline-year">1977</span>
                        <p>
                            Sta. Elena Elementary School became a Principal II school.
                        </p>
                    </div>

                    <div class="timeline-item">
                        <span class="timeline-year">2016</span>
                        <p>
                            The school was converted to Sta. Elena Integrated School.
                            A permit was granted on April 14, 2016, and Junior High
                            School began in School Year 2016–2017 with 80 Grade 7 students.
                        </p>
                    </div>

                    <div class="timeline-item">
                        <span class="timeline-year">2020</span>
                        <p>
                            Two volunteers funded by the LGU were assigned for Grade 11
                            to support student engagement at the Senior High School level.
                        </p>
                    </div>

                    <div class="timeline-item">
                        <span class="timeline-year">2021</span>
                        <p>
                            By June 2021, two teaching items were deployed,
                            supporting the operationalization of the program's intended outputs.
                        </p>
                    </div>
                </div>
            </div>

            <!-- CORE VALUES -->
            <div class="values-section">
                <div class="card-heading compact-heading">
                    <div class="heading-icon"><i class="bi bi-stars"></i></div>
                    <div>
                        <span class="section-label">DEPED CORE VALUES</span>
                        <h3>Values We Live By</h3>
                    </div>
                </div>

                <div class="values-grid">
                    <div class="value-item">
                        <i class="bi bi-heart-fill"></i>
                        <span>Maka-Diyos</span>
                    </div>
                    <div class="value-item">
                        <i class="bi bi-people-fill"></i>
                        <span>Maka-tao</span>
                    </div>
                    <div class="value-item">
                        <i class="bi bi-leaf-fill"></i>
                        <span>Makakalikasan</span>
                    </div>
                    <div class="value-item">
                        <i class="bi bi-flag-fill"></i>
                        <span>Makabansa</span>
                    </div>
                </div>
            </div>

            <!-- MISSION / VISION -->
            <div class="mission-vision-grid">
                <article class="mv-card">
                    <div class="mv-icon"><i class="bi bi-bullseye"></i></div>
                    <span class="section-label">DEPED MISSION</span>
                    <p>
                        To protect and promote the right of every Filipino to quality,
                        equitable, culture-based, and complete basic education where
                        students learn in a child-friendly, gender-sensitive, safe,
                        and motivating environment.
                    </p>
                    <p>
                        Teachers facilitate learning and constantly nurture every learner;
                        administrators and staff ensure an enabling and supportive environment;
                        and families, communities, and stakeholders share responsibility
                        for developing lifelong learners.
                    </p>
                </article>

                <article class="mv-card">
                    <div class="mv-icon"><i class="bi bi-eye-fill"></i></div>
                    <span class="section-label">DEPED VISION</span>
                    <p>
                        We dream of Filipinos who passionately love their country and
                        whose values and competencies enable them to realize their full
                        potential and contribute meaningfully to building the nation.
                    </p>
                    <p>
                        As a learner-centered public institution, the Department of Education
                        continuously commits itself to better serve its stakeholders.
                    </p>
                </article>
            </div>

            <div class="school-footer">
                <span>
                    <i class="bi bi-geo-alt-fill"></i>
                    Purok 7, Barangay Sta. Elena (Poblacion), Sta. Elena, Camarines Norte
                </span>
                <span>
                    <i class="bi bi-telephone-fill"></i>
                    0915-818-2455
                </span>
            </div>

        </div>
    </section>

    <!-- =====================================================
         RIGHT SIDE - LOGIN
         ===================================================== -->
    <section class="login-section">
        <div class="login-card">

            <div class="login-logo">
                <div class="login-logo-ring">
                    <img src="assets/logo/logo2.png" alt="School Logo" loading="eager">
                </div>
            </div>

            <div class="login-header">
                <span class="login-kicker">SCHOOL DATA MANAGEMENT SYSTEM</span>
                <h2 class="login-title">Welcome</h2>
                <p class="login-subtitle">
                    Sign in to securely manage school records and information.
                </p>
            </div>

            <?php if (isset($_GET['timeout'])): ?>
                <div class="alert alert-warning login-alert" role="alert">
                    <i class="bi bi-clock-history"></i>
                    <span>
                        Your session has expired because you were inactive for a specific
                        period of time. Please log in again.
                    </span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger login-alert" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="on" class="login-form">

                <div class="form-field">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group login-input">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text"
                               id="username"
                               name="username"
                               class="form-control"
                               placeholder="Enter your username"
                               autocomplete="username"
                               required
                               autofocus>
                    </div>
                </div>

                <div class="form-field password-field">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group login-input">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control"
                               placeholder="Enter your password"
                               autocomplete="current-password"
                               required>

                        <button type="button"
                                class="btn password-toggle"
                                id="togglePassword"
                                aria-label="Show password">
                            <i class="bi bi-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn login-btn w-100">
                    <span>Sign In</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="login-help">
                <div class="help-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div>
                    <strong>Authorized Access</strong>
                    <span>Use your registered school account to continue.</span>
                </div>
            </div>

            <div class="login-footer">
                <span>Sta. Elena Integrated School</span>
                <span class="footer-dot">•</span>
                <span>School Data Management System</span>
            </div>

        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');

    if (!togglePassword || !password || !passwordIcon) return;

    togglePassword.addEventListener('click', function () {
        const isPassword = password.getAttribute('type') === 'password';

        password.setAttribute('type', isPassword ? 'text' : 'password');

        passwordIcon.classList.toggle('bi-eye', !isPassword);
        passwordIcon.classList.toggle('bi-eye-slash', isPassword);

        togglePassword.setAttribute(
            'aria-label',
            isPassword ? 'Hide password' : 'Show password'
        );
    });
});
</script>

</body>
</html>

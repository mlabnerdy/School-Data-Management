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
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - School Data Management System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="login-wrap">
<div class="card login-card p-4">
  <div class="text-center mb-4">
    <div class="fs-1 text-success">🏫</div>
    <h3 class="fw-bold mb-1">School Data Management</h3>
    <p class="text-muted mb-0">Sign in to manage records</p>
  </div>
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <label class="form-label">Username</label>
    <input class="form-control mb-3" name="username" required autofocus>
    <label class="form-label">Password</label>
    <input class="form-control mb-4" type="password" name="password" required>
    <button class="btn btn-success w-100">Login</button>
  </form>
  <div class="text-center text-muted small mt-3">Default: admin / admin123</div>
</div>
</div>
</body>
</html>

<?php
require_once __DIR__ . '/../config.php';

if (current_admin_id() !== null) {
    header("Location: /admin/dashboard.php");
    exit;
}

$error = null;
$form = ['username' => $_POST['username'] ?? ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf();
    $username = trim($form['username']);
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare("SELECT * FROM admin_users WHERE lower(username) = lower(?)");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin === false || !verify_password($password, $admin['password_hash'])) {
        $error = "Invalid admin username or password.";
    } else {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        flash('success', 'Welcome back, ' . $admin['display_name'] . '.');
        header("Location: /admin/dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | ACMECA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{background:#0b1f3a;}
.card{border:none;border-radius:18px;box-shadow:0 10px 25px rgba(0,0,0,.3);}
</style>
</head>
<body class="d-flex align-items-center" style="min-height:100vh;">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-4">
<div class="text-center text-white mb-4">
<i class="bi bi-shield-lock-fill" style="font-size:3rem;"></i>
<h3 class="mt-2">ACMECA Admin</h3>
</div>
<?php if ($error) { ?>
<div class="alert alert-danger shadow"><?= e($error) ?></div>
<?php } ?>
<div class="card">
<div class="card-body p-4">
<form method="post">
<?= csrf_field() ?>
<div class="mb-3">
<label class="form-label">Admin Username</label>
<input type="text" class="form-control" name="username" value="<?= e($form['username']) ?>" required autofocus>
</div>
<div class="mb-4">
<label class="form-label">Password</label>
<input type="password" class="form-control" name="password" required>
</div>
<div class="d-grid">
<button type="submit" class="btn btn-dark btn-lg">Log in</button>
</div>
</form>
</div>
</div>
</div>
</div>
</div>
</body>
</html>

<?php
require_once __DIR__ . '/config.php';

if (current_user_id() !== null) {
    header("Location: /apply.php");
    exit;
}

$error = null;
$form = ['username' => $_POST['username'] ?? ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf();

    $username = trim($form['username']);
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare("SELECT * FROM users WHERE lower(username) = lower(?)");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user === false || !verify_password($password, $user['password_hash'])) {
        $error = "Invalid username or password.";
    } else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        flash('success', 'Welcome back, ' . $user['full_name'] . '.');
        header("Location: /apply.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log in | ACMECA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
.hero{background:linear-gradient(135deg,#0b1f3a,#124b82);padding:60px 0;color:white;}
.section-card{border:none;border-radius:18px;box-shadow:0 10px 25px rgba(0,0,0,.12);}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<section class="hero">
<div class="container text-center">
<h1 class="display-5 fw-bold">Log In</h1>
<p class="lead mt-2">Access your applicant dashboard.</p>
</div>
</section>

<div class="container py-5">

<?php foreach (get_flashes() as $f) { ?>
<div class="alert alert-<?= e($f['category'] === 'danger' ? 'danger' : ($f['category'] === 'success' ? 'success' : 'info')) ?> shadow">
  <?= e($f['message']) ?>
</div>
<?php } ?>

<?php if ($error) { ?>
<div class="alert alert-danger shadow"><?= e($error) ?></div>
<?php } ?>

<div class="row justify-content-center">
<div class="col-lg-5">
<div class="card section-card">
<div class="card-body p-5">

<form method="post">
<?= csrf_field() ?>

<div class="mb-3">
<label class="form-label">Username</label>
<input type="text" class="form-control" name="username" value="<?= e($form['username']) ?>" required autocomplete="username" autofocus>
</div>

<div class="mb-4">
<label class="form-label">Password</label>
<input type="password" class="form-control" name="password" required autocomplete="current-password">
</div>

<div class="d-grid">
<button type="submit" class="btn btn-primary btn-lg">Log in</button>
</div>
</form>

<div class="text-center mt-3">
<a href="/forgot_password.php">Forgot your password?</a><br><br>
Don't have an account? <a href="/register.php">Register</a>
</div>

</div>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

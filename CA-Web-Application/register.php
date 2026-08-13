<?php
require_once __DIR__ . '/config.php';

$errors = [];
$form = [
    'full_name' => $_POST['full_name'] ?? '',
    'username' => $_POST['username'] ?? '',
    'email' => $_POST['email'] ?? '',
];

if (current_user_id() !== null) {
    header("Location: /apply.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf();

    $full_name = trim($form['full_name']);
    $username = trim($form['username']);
    $email = strtolower(trim($form['email']));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($full_name === '') {
        $errors[] = "Full name is required.";
    }
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,64}$/', $username)) {
        $errors[] = "Username must be 3-64 characters: letters, numbers, . _ -";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        $errors[] = "Password must be at least " . MIN_PASSWORD_LENGTH . " characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $stmt = db()->prepare("SELECT id FROM users WHERE lower(username) = lower(?)");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = "That username is already taken.";
        }
        $stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "An account with that email already exists.";
        }
    }

    if (empty($errors)) {
        $insert = db()->prepare(
            "INSERT INTO users (username, email, full_name, password_hash) VALUES (?, ?, ?, ?)"
        );
        $insert->execute([$username, $email, $full_name, hash_password($password)]);

        flash('success', 'Account created. You can now log in.');
        header("Location: /login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register | ACMECA</title>
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
<h1 class="display-5 fw-bold">Create an Account</h1>
<p class="lead mt-2">Register to request, track, and download certificates.</p>
</div>
</section>

<div class="container py-5">

<?php foreach (get_flashes() as $f) { ?>
<div class="alert alert-<?= e($f['category'] === 'danger' ? 'danger' : ($f['category'] === 'success' ? 'success' : 'info')) ?> shadow">
  <?= e($f['message']) ?>
</div>
<?php } ?>

<?php if (!empty($errors)) { ?>
<div class="alert alert-danger shadow">
<h5><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following:</h5>
<ul class="mb-0">
<?php foreach ($errors as $err) { ?>
  <li><?= e($err) ?></li>
<?php } ?>
</ul>
</div>
<?php } ?>

<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card section-card">
<div class="card-body p-5">

<form method="post">
<?= csrf_field() ?>

<div class="mb-3">
<label class="form-label">Full name</label>
<input type="text" class="form-control" name="full_name" value="<?= e($form['full_name']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label">Username</label>
<input type="text" class="form-control" name="username" value="<?= e($form['username']) ?>" required autocomplete="username">
</div>

<div class="mb-3">
<label class="form-label">Email address</label>
<input type="email" class="form-control" name="email" value="<?= e($form['email']) ?>" required autocomplete="email">
</div>

<div class="mb-3">
<label class="form-label">Password</label>
<input type="password" class="form-control" name="password" required autocomplete="new-password" minlength="<?= MIN_PASSWORD_LENGTH ?>">
<div class="form-text">At least <?= MIN_PASSWORD_LENGTH ?> characters.</div>
</div>

<div class="mb-4">
<label class="form-label">Confirm password</label>
<input type="password" class="form-control" name="confirm_password" required autocomplete="new-password">
</div>

<div class="d-grid">
<button type="submit" class="btn btn-primary btn-lg">Create account</button>
</div>
</form>

<div class="text-center mt-3">
Already have an account? <a href="/login.php">Log in</a>
</div>

</div>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

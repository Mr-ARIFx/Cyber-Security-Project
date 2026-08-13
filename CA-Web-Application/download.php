<?php
require_once __DIR__ . "/config.php";
require_login();

// Handle actual file download
if (isset($_GET['id'])) {
    $request_id = trim($_GET['id']);

    $stmt = db()->prepare("SELECT * FROM certificate_requests WHERE request_id = ? AND user_id = ?");
    $stmt->execute([$request_id, current_user_id()]);
    $cert = $stmt->fetch();

    if ($cert === false) {
        flash('danger', 'Certificate not found, or you do not have permission to download it.');
        header("Location: /download.php");
        exit;
    }

    if ($cert['status'] !== 'Issued' || empty($cert['cert_file_path']) || !file_exists($cert['cert_file_path'])) {
        flash('warning', 'This certificate has not been issued yet, or the file is unavailable.');
        header("Location: /download.php");
        exit;
    }

    $filename = preg_replace('/[^A-Za-z0-9_.-]/', '_', $cert['domain_name']) . '.crt';
    header('Content-Type: application/x-pem-file');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($cert['cert_file_path']));
    readfile($cert['cert_file_path']);
    exit;
}

// Otherwise, list the user's issued certificates
$stmt = db()->prepare("
    SELECT * FROM certificate_requests
    WHERE user_id = ? AND status = 'Issued'
    ORDER BY issued_at DESC
");
$stmt->execute([current_user_id()]);
$issued = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Download Certificate | ACMECA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<section class="py-5 bg-dark text-white">
<div class="container text-center">
<h1 class="display-4 fw-bold">
Download Issued Certificate
</h1>
<p class="lead">
Download certificates that have been issued to your account.
</p>
</div>
</section>
<div class="container py-5">
<div class="row justify-content-center">
<div class="col-lg-8">

<?php foreach (get_flashes() as $f) { ?>
<div class="alert alert-<?= e($f['category']) ?> shadow"><?= e($f['message']) ?></div>
<?php } ?>

<div class="card shadow-lg border-0">
<div class="card-body p-5">
<h3 class="mb-4">
<i class="bi bi-download"></i>
Your Issued Certificates
</h3>

<?php if (empty($issued)) { ?>
<div class="alert alert-info">
You don't have any issued certificates yet. Check your
<a href="/status.php">request status</a> to see where things stand.
</div>
<?php } else { ?>
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>Domain</th>
<th>Type</th>
<th>Issued</th>
<th>Expires</th>
<th>Serial</th>
<th></th>
</tr>
</thead>
<tbody>
<?php foreach ($issued as $c) { ?>
<tr>
<td><?= e($c['domain_name']) ?></td>
<td><?= e($c['cert_type']) ?></td>
<td><?= e($c['issued_at']) ?></td>
<td><?= e($c['expires_at']) ?></td>
<td><code><?= e($c['serial_number']) ?></code></td>
<td>
<a href="/download.php?id=<?= urlencode($c['request_id']) ?>" class="btn btn-sm btn-success">
<i class="bi bi-download"></i> Download
</a>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php } ?>

<hr class="my-5">
<div class="alert alert-warning mb-0">
<strong>Security Notice</strong>
<ul class="mb-0 mt-2">
<li>Your private key is never stored by ACMECA — only public certificates are available here.</li>
<li>You can only see and download certificates issued to your own account.</li>
</ul>
</div>

</div>
</div>
</div>
</div>
</div>
<footer class="bg-dark text-white text-center py-4">
© 2026 ACMECA Certificate Authority
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

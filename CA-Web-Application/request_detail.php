<?php
require_once __DIR__ . '/config.php';
require_login();

$request_id = $_GET['id'] ?? '';
$submitted = isset($_GET['submitted']) && $_GET['submitted'] == '1';

if ($request_id === '') {
    flash('warning', 'No request specified.');
    header("Location: /status.php");
    exit;
}

$stmt = db()->prepare("SELECT * FROM certificate_requests WHERE request_id = ? AND user_id = ?");
$stmt->execute([$request_id, current_user_id()]);
$req = $stmt->fetch();

if ($req === false) {
    flash('danger', 'Request not found, or you do not have permission to view it.');
    header("Location: /status.php");
    exit;
}

$status_badge = [
    'Pending'  => 'warning',
    'Approved' => 'info',
    'Issued'   => 'success',
    'Rejected' => 'danger',
    'Revoked'  => 'secondary',
][$req['status']] ?? 'secondary';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Request Detail | ACMECA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
.hero{background:linear-gradient(135deg,#0b1f3a,#124b82);padding:60px 0;color:white;}
.section-card{border:none;border-radius:18px;box-shadow:0 10px 25px rgba(0,0,0,.12);}
.csr-box{background:#0b1f3a;color:#c8f7c5;padding:20px;border-radius:12px;font-size:.8rem;white-space:pre-wrap;word-break:break-all;max-height:300px;overflow:auto;}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<section class="hero">
<div class="container text-center">
<h1 class="display-5 fw-bold">Certificate Request</h1>
<p class="lead mt-2">Request ID: <code class="text-white"><?= e($req['request_id']) ?></code></p>
</div>
</section>

<div class="container py-5">

<?php foreach (get_flashes() as $f) { ?>
<div class="alert alert-<?= e($f['category']) ?> shadow"><?= e($f['message']) ?></div>
<?php } ?>

<?php if ($submitted) { ?>
<div class="alert alert-success shadow">
<h5><i class="bi bi-check-circle-fill"></i> Request submitted successfully!</h5>
<p class="mb-0">Your certificate request has been received and is now <strong>Pending</strong> review by an administrator.</p>
</div>
<?php } ?>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card section-card mb-4">
<div class="card-body p-4">

<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Request Details</h4>
<span class="badge bg-<?= e($status_badge) ?> fs-6"><?= e($req['status']) ?></span>
</div>

<table class="table table-borderless">
<tr><th style="width:220px;">Certificate Type</th><td><?= e($req['cert_type']) ?></td></tr>
<tr><th>Domain Name</th><td><?= e($req['domain_name']) ?></td></tr>
<?php if (!empty($req['san_list'])) { ?>
<tr><th>Subject Alt. Names</th><td><?= e($req['san_list']) ?></td></tr>
<?php } ?>
<tr><th>Key Algorithm</th><td><?= e($req['key_algorithm']) ?></td></tr>
<tr><th>Organization</th><td><?= e($req['organization_name']) ?></td></tr>
<tr><th>Location</th><td><?= e(trim(($req['city'] ?? '') . ', ' . ($req['state'] ?? '') . ', ' . ($req['country'] ?? ''), ', ')) ?></td></tr>
<tr><th>Submitted</th><td><?= e($req['created_at']) ?></td></tr>
<?php if (!empty($req['issued_at'])) { ?>
<tr><th>Issued</th><td><?= e($req['issued_at']) ?></td></tr>
<?php } ?>
<?php if (!empty($req['serial_number'])) { ?>
<tr><th>Serial Number</th><td><code><?= e($req['serial_number']) ?></code></td></tr>
<?php } ?>
</table>

<?php if ($req['status'] === 'Issued' && !empty($req['cert_file_path'])) { ?>
<a href="/download.php?id=<?= urlencode($req['request_id']) ?>" class="btn btn-success">
<i class="bi bi-download"></i> Download Certificate
</a>
<?php } ?>

</div>
</div>

<div class="card section-card">
<div class="card-body p-4">
<h5 class="mb-3">Submitted CSR</h5>
<div class="csr-box"><?= e($req['csr_text']) ?></div>
</div>
</div>

<div class="text-center mt-4">
<a href="/status.php" class="btn btn-outline-primary">
<i class="bi bi-arrow-left"></i> Back to My Requests
</a>
</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

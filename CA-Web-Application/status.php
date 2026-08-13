<?php
require_once __DIR__ . '/config.php';
require_login();

$lookup_id = trim($_GET['request_id'] ?? '');
$lookup_result = null;
$lookup_error = null;

if ($lookup_id !== '') {
    $stmt = db()->prepare("SELECT * FROM certificate_requests WHERE request_id = ? AND user_id = ?");
    $stmt->execute([$lookup_id, current_user_id()]);
    $lookup_result = $stmt->fetch();
    if ($lookup_result === false) {
        $lookup_error = "No request found with that ID for your account.";
    }
}

$stmt = db()->prepare("SELECT * FROM certificate_requests WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([current_user_id()]);
$my_requests = $stmt->fetchAll();

function status_badge_class(string $status): string {
    return [
        'Pending'  => 'warning',
        'Approved' => 'info',
        'Issued'   => 'success',
        'Rejected' => 'danger',
        'Revoked'  => 'secondary',
    ][$status] ?? 'secondary';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Application Status | ACMECA</title>
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
<h1 class="display-5 fw-bold">Application Status</h1>
<p class="lead mt-2">Track your certificate requests.</p>
</div>
</section>

<div class="container py-5">

<?php foreach (get_flashes() as $f) { ?>
<div class="alert alert-<?= e($f['category']) ?> shadow"><?= e($f['message']) ?></div>
<?php } ?>

<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card section-card mb-4">
<div class="card-body p-4">
<h5><i class="bi bi-search text-primary"></i> Look up by Request ID</h5>
<form method="get" class="row g-2 mt-2">
<div class="col-9">
<input type="text" class="form-control" name="request_id"
       placeholder="e.g. a1b2c3d4-e5f6-..." value="<?= e($lookup_id) ?>">
</div>
<div class="col-3 d-grid">
<button class="btn btn-primary">Check Status</button>
</div>
</form>

<?php if ($lookup_error) { ?>
<div class="alert alert-danger mt-3 mb-0"><?= e($lookup_error) ?></div>
<?php } ?>

<?php if ($lookup_result) { ?>
<table class="table table-bordered mt-3 mb-0">
<tr><th width="35%">Request ID</th><td><code><?= e($lookup_result['request_id']) ?></code></td></tr>
<tr><th>Certificate Type</th><td><?= e($lookup_result['cert_type']) ?></td></tr>
<tr><th>Domain</th><td><?= e($lookup_result['domain_name']) ?></td></tr>
<tr><th>Status</th><td><span class="badge bg-<?= e(status_badge_class($lookup_result['status'])) ?>"><?= e($lookup_result['status']) ?></span></td></tr>
<tr><th>Submitted</th><td><?= e($lookup_result['created_at']) ?></td></tr>
</table>
<div class="mt-3">
<a href="/request_detail.php?id=<?= urlencode($lookup_result['request_id']) ?>" class="btn btn-outline-primary btn-sm">
View Full Details
</a>
</div>
<?php } ?>
</div>
</div>

<div class="card section-card">
<div class="card-body p-4">
<h5 class="mb-3"><i class="bi bi-list-ul text-primary"></i> My Requests</h5>

<?php if (empty($my_requests)) { ?>
<p class="text-muted mb-0">You haven't submitted any certificate requests yet.
<a href="/apply.php">Apply for one now.</a></p>
<?php } else { ?>
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>Request ID</th>
<th>Type</th>
<th>Domain</th>
<th>Status</th>
<th>Submitted</th>
<th></th>
</tr>
</thead>
<tbody>
<?php foreach ($my_requests as $r) { ?>
<tr>
<td><code><?= e(substr($r['request_id'], 0, 8)) ?>...</code></td>
<td><?= e($r['cert_type']) ?></td>
<td><?= e($r['domain_name']) ?></td>
<td><span class="badge bg-<?= e(status_badge_class($r['status'])) ?>"><?= e($r['status']) ?></span></td>
<td><?= e($r['created_at']) ?></td>
<td>
<a href="/request_detail.php?id=<?= urlencode($r['request_id']) ?>" class="btn btn-sm btn-outline-primary">
View
</a>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php } ?>

</div>
</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

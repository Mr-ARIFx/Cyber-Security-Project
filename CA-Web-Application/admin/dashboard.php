<?php
require_once __DIR__ . '/../config.php';
require_admin();

function ca_dir_for(string $cert_type): ?string {
    switch (strtoupper($cert_type)) {
        case 'DV': return DV_CA_DIR;
        case 'OV': return OV_CA_DIR;
        case 'EV': return EV_CA_DIR;
        default: return null;
    }
}

function ext_section_for(string $cert_type): string {
    return match(strtoupper($cert_type)) {
        'OV' => 'ov_server_cert',
        'EV' => 'ev_server_cert',
        default => 'server_cert', // DV
    };
}

function audit(int $admin_id, string $action, string $target_type, string $target_id, string $result): void {
    $stmt = db()->prepare(
        "INSERT INTO audit_log (admin_id, action, target_type, target_id, result) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$admin_id, $action, $target_type, $target_id, $result]);
}

$admin_id = current_admin_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action     = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? '';

    $stmt = db()->prepare("SELECT * FROM certificate_requests WHERE request_id = ?");
    $stmt->execute([$request_id]);
    $req = $stmt->fetch();

    if ($req === false) {
        flash('danger', 'Request not found.');

    } elseif ($action === 'approve') {
        db()->prepare("UPDATE certificate_requests SET status = 'Approved' WHERE request_id = ?")
            ->execute([$request_id]);
        audit($admin_id, 'approve', 'certificate_request', $request_id, 'success');
        flash('success', "Request $request_id approved.");

    } elseif ($action === 'reject') {
        $reason = trim($_POST['reason'] ?? 'Not specified');
        db()->prepare("UPDATE certificate_requests SET status = 'Rejected', revocation_reason = ? WHERE request_id = ?")
            ->execute([$reason, $request_id]);
        audit($admin_id, 'reject', 'certificate_request', $request_id, 'success');
        flash('warning', "Request $request_id rejected.");

    } elseif ($action === 'issue') {
        $ca_dir = ca_dir_for($req['cert_type']);
        if ($ca_dir === null || !is_dir($ca_dir)) {
            flash('danger', "CA directory for {$req['cert_type']} is not configured on this server.");
            audit($admin_id, 'issue', 'certificate_request', $request_id, 'failed: ca dir missing');
        } else {
            $csr_path = tempnam(sys_get_temp_dir(), 'csr_');
            $cert_out = tempnam(sys_get_temp_dir(), 'crt_');
            file_put_contents($csr_path, $req['csr_text']);

            $config_file     = $ca_dir . '/openssl.cnf';
            $passphrase_file = $ca_dir . '/passphrase.txt';
            $ext_section     = ext_section_for($req['cert_type']);

            if (!file_exists($passphrase_file)) {
                flash('danger', "Passphrase file missing for {$req['cert_type']} CA at $passphrase_file.");
                audit($admin_id, 'issue', 'certificate_request', $request_id, 'failed: passphrase file missing');
                header("Location: /admin/dashboard.php");
                exit;
            }

            $cmd = sprintf(
                'openssl ca -batch -config %s -passin file:%s -in %s -out %s -extensions %s -days 365 2>&1',
                escapeshellarg($config_file),
                escapeshellarg($passphrase_file),
                escapeshellarg($csr_path),
                escapeshellarg($cert_out),
                escapeshellarg($ext_section)
            );
            exec($cmd, $output, $exit_code);

            if ($exit_code === 0 && file_exists($cert_out) && filesize($cert_out) > 0) {
                $cert_contents = file_get_contents($cert_out);
                $serial = trim(shell_exec(sprintf(
                    'openssl x509 -in %s -serial -noout 2>/dev/null',
                    escapeshellarg($cert_out)
                )) ?? '');
                $serial = str_replace('serial=', '', $serial);

                $storage_dir = __DIR__ . '/../storage/issued';
                if (!is_dir($storage_dir)) mkdir($storage_dir, 0775, true);
                $cert_path = $storage_dir . '/' . $req['request_id'] . '.crt';
                file_put_contents($cert_path, $cert_contents);

                $expires = (new DateTime('+365 days'))->format('Y-m-d H:i:s');
                db()->prepare(
                    "UPDATE certificate_requests
                     SET status = 'Issued', serial_number = ?, cert_file_path = ?,
                         issued_at = datetime('now'), expires_at = ?
                     WHERE request_id = ?"
                )->execute([$serial, $cert_path, $expires, $request_id]);

                audit($admin_id, 'issue', 'certificate_request', $request_id, 'success');
                flash('success', "Certificate issued for $request_id (using $ext_section).");
            } else {
                audit($admin_id, 'issue', 'certificate_request', $request_id, 'failed: ' . implode(' ', $output));
                flash('danger', "Signing failed: " . e(implode(' ', $output)));
            }
            @unlink($csr_path);
            @unlink($cert_out);
        }

    } elseif ($action === 'revoke') {
        $reason = trim($_POST['reason'] ?? 'Not specified');
        db()->prepare(
            "UPDATE certificate_requests
             SET status = 'Revoked', revoked_at = datetime('now'), revocation_reason = ?
             WHERE request_id = ?"
        )->execute([$reason, $request_id]);
        audit($admin_id, 'revoke', 'certificate_request', $request_id, 'success');
        flash('warning', "Request $request_id revoked.");
    }

    header("Location: /admin/dashboard.php");
    exit;
}

$filter = $_GET['status'] ?? '';
if ($filter !== '') {
    $stmt = db()->prepare("
        SELECT cr.*, u.username, u.email
        FROM certificate_requests cr
        JOIN users u ON u.id = cr.user_id
        WHERE cr.status = ?
        ORDER BY cr.created_at DESC
    ");
    $stmt->execute([$filter]);
} else {
    $stmt = db()->query("
        SELECT cr.*, u.username, u.email
        FROM certificate_requests cr
        JOIN users u ON u.id = cr.user_id
        ORDER BY cr.created_at DESC
    ");
}
$requests = $stmt->fetchAll();

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
<title>Admin Dashboard | ACMECA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background: #f4f6f9; }
.navbar { background: #0b1f3a !important; }
.ev-badge  { background:#7c3aed; color:white; font-size:.7rem; padding:2px 7px; border-radius:10px; }
.ov-badge  { background:#0369a1; color:white; font-size:.7rem; padding:2px 7px; border-radius:10px; }
.dv-badge  { background:#15803d; color:white; font-size:.7rem; padding:2px 7px; border-radius:10px; }
</style>
</head>
<body>
<nav class="navbar navbar-dark">
<div class="container">
<span class="navbar-brand fw-bold">
<i class="bi bi-shield-lock-fill text-info"></i> ACMECA Admin
</span>
<div>
<span class="text-white me-3"><?= e($_SESSION['admin_username'] ?? '') ?></span>
<a href="/admin/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
</div>
</div>
</nav>

<div class="container py-4">

<?php foreach (get_flashes() as $f) { ?>
<div class="alert alert-<?= e($f['category']) ?> shadow"><?= e($f['message']) ?></div>
<?php } ?>

<div class="d-flex justify-content-between align-items-center mb-3">
<h3>Certificate Requests</h3>
<div class="d-flex gap-1 flex-wrap">
<a href="?status="         class="btn btn-sm btn-outline-secondary">All</a>
<a href="?status=Pending"  class="btn btn-sm btn-outline-warning">Pending</a>
<a href="?status=Approved" class="btn btn-sm btn-outline-info">Approved</a>
<a href="?status=Issued"   class="btn btn-sm btn-outline-success">Issued</a>
<a href="?status=Rejected" class="btn btn-sm btn-outline-danger">Rejected</a>
<a href="?status=Revoked"  class="btn btn-sm btn-outline-dark">Revoked</a>
</div>
</div>

<div class="card shadow-sm">
<div class="table-responsive">
<table class="table table-hover mb-0 align-middle">
<thead class="table-light">
<tr>
<th>Request ID</th>
<th>Applicant</th>
<th>Type</th>
<th>Domain</th>
<th>Status</th>
<th>Submitted</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($requests as $r) { ?>
<tr>
<td><code><?= e(substr($r['request_id'], 0, 8)) ?>...</code></td>
<td>
<?= e($r['username']) ?>
<br><small class="text-muted"><?= e($r['email']) ?></small>
</td>
<td><span class="<?= strtolower(e($r['cert_type'])) ?>-badge"><?= e($r['cert_type']) ?></span></td>
<td><?= e($r['domain_name']) ?></td>
<td><span class="badge bg-<?= e(status_badge_class($r['status'])) ?>"><?= e($r['status']) ?></span></td>
<td><?= e($r['created_at']) ?></td>
<td>
<button class="btn btn-sm btn-outline-secondary"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#row-<?= e($r['id']) ?>">
Details
</button>
</td>
</tr>

<tr class="collapse" id="row-<?= e($r['id']) ?>">
<td colspan="7">
<div class="p-3 bg-light rounded">

<div class="row mb-3">

<!-- Organization / Domain -->
<div class="col-md-4">
<h6 class="text-muted mb-2">Organization</h6>
<table class="table table-sm table-borderless mb-0">
<tr><th style="width:120px;">Legal Name</th><td><?= e($r['organization_name'] ?: '—') ?></td></tr>
<tr><th>Country</th><td><?= e($r['country'] ?: '—') ?></td></tr>
<tr><th>State</th><td><?= e($r['state'] ?: '—') ?></td></tr>
<tr><th>City</th><td><?= e($r['city'] ?: '—') ?></td></tr>
<tr><th>SAN List</th><td><?= e($r['san_list'] ?: '—') ?></td></tr>
</table>
</div>

<!-- Applicant -->
<div class="col-md-4">
<h6 class="text-muted mb-2">Applicant</h6>
<table class="table table-sm table-borderless mb-0">
<tr><th style="width:120px;">Username</th><td><?= e($r['username']) ?></td></tr>
<tr><th>Email</th><td><?= e($r['email']) ?></td></tr>
<tr><th>Key Algorithm</th><td><?= e($r['key_algorithm']) ?></td></tr>
<tr><th>Cert Type</th><td><?= e($r['cert_type']) ?></td></tr>
<?php if (!empty($r['serial_number'])) { ?>
<tr><th>Serial</th><td><code><?= e($r['serial_number']) ?></code></td></tr>
<?php } ?>
<?php if (!empty($r['issued_at'])) { ?>
<tr><th>Issued</th><td><?= e($r['issued_at']) ?></td></tr>
<?php } ?>
<?php if (!empty($r['expires_at'])) { ?>
<tr><th>Expires</th><td><?= e($r['expires_at']) ?></td></tr>
<?php } ?>
</table>
</div>

<!-- EV-specific fields -->
<?php if ($r['cert_type'] === 'EV') { ?>
<div class="col-md-4">
<h6 class="text-muted mb-2">
<span class="ev-badge">EV</span> Extended Validation Details
</h6>
<table class="table table-sm table-borderless mb-0">
<tr>
<th style="width:120px;">Reg. Number</th>
<td>
<?php
$ev_reg = $r['org_registration_number'] ?? null;
if (!empty($ev_reg)) {
    echo '<code>' . e($ev_reg) . '</code>';
} else {
    echo '<span class="text-warning"><i class="bi bi-exclamation-triangle"></i> Not stored yet — DB column pending</span>';
}
?>
</td>
</tr>
<tr>
<th>Reg. Address</th>
<td>
<?php
$ev_addr = $r['org_address'] ?? null;
if (!empty($ev_addr)) {
    echo e($ev_addr);
} else {
    echo '<span class="text-warning"><i class="bi bi-exclamation-triangle"></i> Not stored yet — DB column pending</span>';
}
?>
</td>
</tr>
</table>
<div class="alert alert-info p-2 small mt-2 mb-0">
<i class="bi bi-info-circle"></i>
EV requires independent verification of business registration before issuance.
</div>
</div>
<?php } ?>

</div>

<!-- CSR -->
<p class="mb-1"><strong>CSR:</strong></p>
<pre style="max-height:150px;overflow:auto;font-size:.75rem;background:#0f172a;color:#00ff99;padding:12px;border-radius:8px;"><?= e($r['csr_text']) ?></pre>

<!-- Action buttons -->
<div class="d-flex gap-2 flex-wrap mt-3">

<?php if ($r['status'] === 'Pending') { ?>
<form method="post" class="d-inline">
<?= csrf_field() ?>
<input type="hidden" name="request_id" value="<?= e($r['request_id']) ?>">
<input type="hidden" name="action" value="approve">
<button class="btn btn-sm btn-info">
<i class="bi bi-check-circle"></i> Approve
</button>
</form>

<form method="post" class="d-inline" onsubmit="return confirm('Reject this request?');">
<?= csrf_field() ?>
<input type="hidden" name="request_id" value="<?= e($r['request_id']) ?>">
<input type="hidden" name="action" value="reject">
<div class="input-group input-group-sm" style="width:320px;">
<input type="text" name="reason" placeholder="Rejection reason" class="form-control">
<button class="btn btn-danger">
<i class="bi bi-x-circle"></i> Reject
</button>
</div>
</form>
<?php } ?>

<?php if ($r['status'] === 'Approved') { ?>
<form method="post" class="d-inline"
      onsubmit="return confirm('Issue certificate? Verify all details before proceeding.');">
<?= csrf_field() ?>
<input type="hidden" name="request_id" value="<?= e($r['request_id']) ?>">
<input type="hidden" name="action" value="issue">
<button class="btn btn-sm btn-success">
<i class="bi bi-award"></i> Issue Certificate
<small class="opacity-75">(<?= e(ext_section_for($r['cert_type'])) ?>)</small>
</button>
</form>
<?php } ?>

<?php if ($r['status'] === 'Issued') { ?>
<form method="post" class="d-inline"
      onsubmit="return confirm('Revoke this certificate? This cannot be undone.');">
<?= csrf_field() ?>
<input type="hidden" name="request_id" value="<?= e($r['request_id']) ?>">
<input type="hidden" name="action" value="revoke">
<div class="input-group input-group-sm" style="width:320px;">
<input type="text" name="reason" placeholder="Revocation reason" class="form-control">
<button class="btn btn-warning">
<i class="bi bi-slash-circle"></i> Revoke
</button>
</div>
</form>
<?php } ?>

<?php if (!empty($r['revocation_reason']) && $r['status'] === 'Revoked') { ?>
<span class="align-self-center text-muted small">
Reason: <?= e($r['revocation_reason']) ?>
</span>
<?php } ?>

</div>
</div>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

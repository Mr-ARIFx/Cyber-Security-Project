<?php
require_once __DIR__ . '/config.php';
require_login();

$user_id = current_user_id();
$stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();

$csrText = $_GET['csr'] ?? '';
$success = false;
$applicationID = "";
$errors = [];

// Retain submitted values on validation failure so the form doesn't blank out
$form = [
    'type' => $_POST['type'] ?? 'DV',
    'name' => $_POST['name'] ?? ($current_user['full_name'] ?? ''),
    'email' => $_POST['email'] ?? ($current_user['email'] ?? ''),
    'domain' => $_POST['domain'] ?? '',
    'san_extra' => $_POST['san_extra'] ?? '',
    'org_legal_name' => $_POST['org_legal_name'] ?? '',
    'org_country' => $_POST['org_country'] ?? '',
    'org_state' => $_POST['org_state'] ?? '',
    'org_city' => $_POST['org_city'] ?? '',
    'org_registration_number' => $_POST['org_registration_number'] ?? '',
    'org_address' => $_POST['org_address'] ?? '',
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf();

    $cert_type = $form['type'];
    if (!in_array($cert_type, ['DV', 'OV', 'EV'], true)) {
        $errors[] = "Invalid certificate type selected.";
    }

    if (trim($form['name']) === '') $errors[] = "Applicant name is required.";
    if (trim($form['email']) === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }
    if (trim($form['domain']) === '') $errors[] = "Domain name is required.";

    // OV and EV both require organization identity details
    if ($cert_type === 'OV' || $cert_type === 'EV') {
        if (trim($form['org_legal_name']) === '') $errors[] = "Organization legal name is required for OV/EV.";
        if (trim($form['org_country']) === '')    $errors[] = "Organization country is required for OV/EV.";
        if (trim($form['org_state']) === '')      $errors[] = "Organization state/division is required for OV/EV.";
        if (trim($form['org_city']) === '')       $errors[] = "Organization city is required for OV/EV.";
    }

    // EV requires additional organizational detail on top of OV
    if ($cert_type === 'EV') {
        if (trim($form['org_registration_number']) === '') {
            $errors[] = "Business registration number is required for EV.";
        }
        if (trim($form['org_address']) === '') {
            $errors[] = "Registered organization address is required for EV.";
        }
    }

    // CSR: from pasted text OR uploaded file, never both silently ignored
    $csr = '';
    if (!empty($_POST['csr_text'])) {
        $csr = trim($_POST['csr_text']);
    } elseif (isset($_FILES['csr_file']) && $_FILES['csr_file']['error'] === UPLOAD_ERR_OK) {
        $csr = file_get_contents($_FILES['csr_file']['tmp_name']);
    }

    // Step 1: Basic structure check
    if ($csr === '' || strpos($csr, 'BEGIN CERTIFICATE REQUEST') === false) {
        $errors[] = "A valid CSR is required — paste CSR text or upload a .csr/.pem file.";
    }

    // Step 2: Cryptographic validation — only runs if step 1 passed
    if (empty($errors)) {
        // Check the CSR parses correctly and contains a valid public key
        $csr_resource = openssl_csr_get_public_key($csr);
        if ($csr_resource === false) {
            $errors[] = "CSR is malformed or corrupted — it could not be parsed. Please regenerate your CSR and try again.";
        } else {
            // Verify the CSR's self-signature (Proof of Possession):
            // confirms the submitter actually holds the private key matching
            // the public key embedded in the CSR — prevents CSR replay attacks.
            $tmp = tempnam(sys_get_temp_dir(), 'csr_verify_');
            file_put_contents($tmp, $csr);
            $verify_cmd = sprintf(
                'openssl req -in %s -verify -noout 2>&1',
                escapeshellarg($tmp)
            );
            exec($verify_cmd, $verify_output, $verify_exit);
            @unlink($tmp);

            $verify_text = strtolower(implode(' ', $verify_output));
            if ($verify_exit !== 0 || strpos($verify_text, 'verify ok') === false) {
                $errors[] = "CSR signature verification failed — the CSR may be corrupted or tampered with. Please regenerate your CSR and try again.";
            }
        }
    }

    if (empty($errors)) {
        // Build the SAN list: always include the bare domain, plus anything
        // the applicant added (e.g. wildcard) — a wildcard alone does not
        // cover the bare domain, so both entries are required.
        $san_parts = array_filter(array_map('trim', explode(',', $form['domain'] . ',' . $form['san_extra'])));
        $san_list = implode(',', array_unique($san_parts));

        $request_id = generate_request_id();

	$insert = db()->prepare("
   	   INSERT INTO certificate_requests
       	     (request_id, user_id, cert_type, key_algorithm, domain_name, san_list,
              organization_name, country, state, city, csr_text,
              org_registration_number, org_address, status, created_at)
           VALUES
             (:request_id, :user_id, :cert_type, :key_algorithm, :domain_name, :san_list,
              :organization_name, :country, :state, :city, :csr_text,
              :org_registration_number, :org_address, 'Pending', datetime('now'))
        ");
         $insert->execute([
   	   ':request_id'             => $request_id,
           ':user_id'                => $user_id,
           ':cert_type'              => $cert_type,
           ':key_algorithm'          => 'RSA',
           ':domain_name'            => $form['domain'],
           ':san_list'               => $san_list,
           ':organization_name'      => $form['org_legal_name'],
           ':country'                => $form['org_country'],
           ':state'                  => $form['org_state'],
           ':city'                   => $form['org_city'],
           ':csr_text'               => $csr,
	   ':org_registration_number' => $form['org_registration_number'] ?? '',
	   ':org_address' => $form['org_address'] ?? '',
      ]);

        // Post/Redirect/Get pattern: prevents duplicate submissions on refresh
        header("Location: /request_detail.php?id=" . urlencode($request_id) . "&submitted=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Apply for Certificate | ACMECA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
.hero{background:linear-gradient(135deg,#0b1f3a,#124b82);padding:70px 0;color:white;}
.section-card{border:none;border-radius:18px;box-shadow:0 10px 25px rgba(0,0,0,.12);}
.org-fields{display:none;}
.org-fields.active{display:block;}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<section class="hero">
<div class="container text-center">
<h1 class="display-4 fw-bold">Apply for an SSL Certificate</h1>
<p class="lead mt-3">Submit your CSR securely to the ACMECA Certificate Authority.</p>
</div>
</section>

<div class="container py-5">

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
<div class="col-lg-9">
<div class="card section-card">
<div class="card-body p-5">
<h2 class="mb-4"><i class="bi bi-send-fill"></i> Certificate Application</h2>

<form method="post" enctype="multipart/form-data" id="applyForm">
<?= csrf_field() ?>

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Certificate Type</label>
<select class="form-select" name="type" id="certType" required>
<option value="DV" <?= $form['type']==='DV'?'selected':'' ?>>Domain Validation (DV)</option>
<option value="OV" <?= $form['type']==='OV'?'selected':'' ?>>Organization Validation (OV)</option>
<option value="EV" <?= $form['type']==='EV'?'selected':'' ?>>Extended Validation (EV)</option>
</select>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Applicant Name</label>
<input type="text" class="form-control" name="name" value="<?= e($form['name']) ?>" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Email Address</label>
<input type="email" class="form-control" name="email" value="<?= e($form['email']) ?>" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Domain Name</label>
<input type="text" class="form-control" name="domain" placeholder="example.com" value="<?= e($form['domain']) ?>" required>
</div>

<div class="col-md-12 mb-3">
<label class="form-label">Additional SAN entries <span class="text-muted">(comma-separated, optional — e.g. wildcard)</span></label>
<input type="text" class="form-control" name="san_extra" placeholder="*.example.com" value="<?= e($form['san_extra']) ?>">
<div class="form-text">Your domain name above is always included automatically. A wildcard alone does not cover the bare domain — add both if needed.</div>
</div>
</div>

<div id="orgFields" class="org-fields border rounded p-4 mb-4 bg-light">
<h5 class="mb-3"><i class="bi bi-building"></i> Organization Details <span class="text-muted small">(required for OV / EV)</span></h5>
<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Organization Legal Name</label>
<input type="text" class="form-control" name="org_legal_name" value="<?= e($form['org_legal_name']) ?>">
</div>
<div class="col-md-2 mb-3">
<label class="form-label">Country</label>
<input type="text" class="form-control" name="org_country" maxlength="2" placeholder="BD" value="<?= e($form['org_country']) ?>">
</div>
<div class="col-md-4 mb-3">
<label class="form-label">State / Division</label>
<input type="text" class="form-control" name="org_state" value="<?= e($form['org_state']) ?>">
</div>
<div class="col-md-6 mb-3">
<label class="form-label">City</label>
<input type="text" class="form-control" name="org_city" value="<?= e($form['org_city']) ?>">
</div>
</div>

<div id="evFields" class="org-fields">
<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Business Registration Number <span class="text-muted small">(EV only)</span></label>
<input type="text" class="form-control" name="org_registration_number" value="<?= e($form['org_registration_number']) ?>">
</div>
<div class="col-md-6 mb-3">
<label class="form-label">Registered Address <span class="text-muted small">(EV only)</span></label>
<input type="text" class="form-control" name="org_address" value="<?= e($form['org_address']) ?>">
</div>
</div>
</div>
</div>

<div class="mb-4">
<label class="form-label">Paste CSR</label>
<textarea class="form-control" rows="10" name="csr_text" placeholder="Paste your generated CSR here..."><?= e($csrText) ?></textarea>
</div>

<div class="text-center mb-3"><strong>OR</strong></div>

<div class="mb-4">
<label class="form-label">Upload CSR File</label>
<input type="file" class="form-control" accept=".csr,.pem" name="csr_file">
<div class="form-text">Only upload CSR files. Never upload your Private Key.</div>
</div>

<div class="alert alert-warning">
<b>Security Notice</b>
<ul class="mt-2 mb-0">
<li>Your private key must remain on your own computer.</li>
<li>ACMECA never requests or stores applicant private keys.</li>
<li>Only Certificate Signing Requests (CSR) are accepted.</li>
<li>Your CSR is cryptographically verified before acceptance.</li>
<li>Your application is assigned a unique Request ID upon submission.</li>
</ul>
</div>

<div class="d-grid mt-4">
<button type="submit" class="btn btn-primary btn-lg">
<i class="bi bi-send-fill"></i> Submit Application
</button>
</div>
</form>
</div>
</div>
</div>

<div class="col-lg-3">
<div class="card section-card mb-4">
<div class="card-body">
<h4><i class="bi bi-info-circle-fill"></i> Application Steps</h4>
<hr>
<ol class="mb-0">
<li>Generate a CSR.</li>
<li>Complete this application.</li>
<li>Submit your request.</li>
<li>Administrator validates the request.</li>
<li>Certificate is issued.</li>
<li>Download from My Applications.</li>
</ol>
</div>
</div>
<div class="card section-card">
<div class="card-body">
<h4><i class="bi bi-shield-lock-fill"></i> Security</h4>
<hr>
<p>✅ HTTPS Protected</p>
<p>✅ RSA / ECC Certificates</p>
<p>✅ Private Keys Never Uploaded</p>
<p>✅ CSR Cryptographic Validation</p>
<p>✅ Proof of Possession Check</p>
<p>✅ Ownership-Enforced Downloads</p>
<p class="mb-0">✅ ACMECA PKI</p>
</div>
</div>
</div>
</div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5">
<div class="container">
<h5>ACMECA Certificate Authority</h5>
<p class="mb-0">Secure PKI • SSL/TLS Certificates • Cyber Security Project</p>
</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Show/hide organization fields based on certificate type.
// Convenience only — real enforcement is server-side above,
// since client-side hiding can always be bypassed.
function updateOrgFields() {
  const type = document.getElementById('certType').value;
  const orgFields = document.getElementById('orgFields');
  const evFields = document.getElementById('evFields');
  orgFields.classList.toggle('active', type === 'OV' || type === 'EV');
  evFields.classList.toggle('active', type === 'EV');
}
document.getElementById('certType').addEventListener('change', updateOrgFields);
updateOrgFields();
</script>
</body>
</html>

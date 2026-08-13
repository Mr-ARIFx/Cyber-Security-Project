<?php
require_once __DIR__ . "/config.php";

$privateKey = "";
$publicKey  = "";
$csr        = "";
$gen_error  = "";

if (isset($_POST['generate'])) {

    $country = strtoupper(trim($_POST['country'] ?? ''));

    if (!preg_match('/^[A-Z]{2}$/', $country)) {
        $gen_error = "Country must be exactly 2 letters (ISO code), e.g. US, BD, GB. You entered: \"" . htmlspecialchars($_POST['country'] ?? '') . "\"";
    } else {

        $keyBits = (int)$_POST['keysize'];

        $config = array(
            "private_key_bits" => $keyBits,
            "private_key_type" => OPENSSL_KEYTYPE_RSA
        );

        $key = openssl_pkey_new($config);

        if ($key === false) {
            $gen_error = "Failed to generate RSA key pair: " . openssl_error_string();
        } else {
            openssl_pkey_export($key, $privateKey);
            $details = openssl_pkey_get_details($key);
            $publicKey = $details['key'];

            $dn = array(
                "countryName"            => $country,
                "stateOrProvinceName"     => $_POST['state'] ?? '',
                "localityName"            => $_POST['city'] ?? '',
                "organizationName"        => $_POST['org'] ?? '',
                "organizationalUnitName"  => $_POST['ou'] ?? '',
                "commonName"              => $_POST['cn'] ?? '',
                "emailAddress"            => $_POST['email'] ?? ''
            );

            // Remove empty optional fields so OpenSSL doesn't choke on blanks
            $dn = array_filter($dn, function ($v) { return trim($v) !== ''; });

            $csrResource = openssl_csr_new($dn, $key, $config);

            if ($csrResource === false) {
                $gen_error = "Failed to generate CSR. OpenSSL error: " . openssl_error_string();
            } else {
                $exported = openssl_csr_export($csrResource, $csr);
                if (!$exported) {
                    $gen_error = "Failed to export CSR: " . openssl_error_string();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Generate CSR | ACMECA</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="css/style.css">

<style>

body{

background:#f4f7fb;

}

.hero{

background:linear-gradient(135deg,#07162b,#123b67);

padding:70px 0;

color:white;

}

.section-card{

border:none;

border-radius:18px;

box-shadow:0 10px 30px rgba(0,0,0,.12);

margin-bottom:30px;

}

.code-box{

background:#0f172a;

color:#00ff99;

padding:18px;

border-radius:10px;

height:260px;

overflow:auto;

white-space:pre-wrap;

font-family:Consolas,monospace;

font-size:14px;

}

.badge-box{

padding:10px 18px;

border-radius:30px;

background:#ffffff;

color:#003566;

font-weight:700;

display:inline-block;

margin:5px;

border:1px solid rgba(13,59,102,.2);

}

.btn-generate{

padding:14px;

font-size:18px;

}

</style>

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<section class="hero">

<div class="container text-center">

<h1 class="display-4 fw-bold">

RSA Key Pair & CSR Generator

</h1>

<p class="lead mt-3">

Generate a Private Key, Public Key and CSR directly in your browser session.

Nothing is stored on the ACMECA server.

</p>

<div class="mt-4">

<span class="badge-box">RSA</span>

<span class="badge-box">OpenSSL</span>

<span class="badge-box">PKI</span>

<span class="badge-box">CSR</span>

<span class="badge-box">Secure</span>

</div>

</div>

</section>

<div class="container py-5">

<?php if (!empty($gen_error)) { ?>
<div class="alert alert-danger shadow">
<i class="bi bi-exclamation-triangle-fill"></i>
<strong>Generation failed:</strong> <?= htmlspecialchars($gen_error) ?>
</div>
<?php } ?>

<div class="row">

<div class="col-lg-8">

<div class="card section-card">

<div class="card-body">

<h3>

<i class="bi bi-cpu"></i>

Generate Certificate Request

</h3>

<hr>

<form method="post">

<div class="row">

<div class="col-md-6 mb-3">

<label>Country (2-letter code)</label>

<input
type="text"
name="country"
class="form-control text-uppercase"
value="<?= htmlspecialchars($_POST['country'] ?? 'BD') ?>"
maxlength="2"
minlength="2"
pattern="[A-Za-z]{2}"
title="Exactly 2 letters, e.g. US, BD, GB"
required>

</div>

<div class="col-md-6 mb-3">

<label>State</label>

<input
type="text"
name="state"
class="form-control"
value="<?= htmlspecialchars($_POST['state'] ?? 'Dhaka') ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label>City</label>

<input
type="text"
name="city"
class="form-control"
value="<?= htmlspecialchars($_POST['city'] ?? 'Dhaka') ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label>Organization</label>

<input
type="text"
name="org"
class="form-control"
value="<?= htmlspecialchars($_POST['org'] ?? '') ?>"
placeholder="ACMECA"
required>

</div>

<div class="col-md-6 mb-3">

<label>Department</label>

<input
type="text"
name="ou"
class="form-control"
value="<?= htmlspecialchars($_POST['ou'] ?? '') ?>"
placeholder="IT">

</div>

<div class="col-md-6 mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
required>

</div>

<div class="col-md-12 mb-3">

<label>Common Name (Domain)</label>

<input
type="text"
name="cn"
class="form-control"
value="<?= htmlspecialchars($_POST['cn'] ?? '') ?>"
placeholder="www.example.com"
required>

</div>

<div class="col-md-6 mb-4">

<label>RSA Key Size</label>

<select
name="keysize"
class="form-select">

<option value="2048">2048 Bit</option>

<option value="4096">4096 Bit</option>

</select>

</div>

<div class="col-md-6 d-grid align-items-end mb-4">

<button
type="submit"
name="generate"
class="btn btn-primary btn-generate">

<i class="bi bi-lightning-charge-fill"></i>

Generate RSA Key Pair & CSR

</button>

</div>

</div>

</form>

</div>

</div>

<div class="card section-card">

<div class="card-body">

<h3>

<i class="bi bi-lock-fill"></i>

Private Key

</h3>

<div class="code-box" id="privateKey"><?= htmlspecialchars($privateKey) ?></div>

<div class="mt-3">

<button class="btn btn-success" type="button" onclick="copyText('privateKey')">

<i class="bi bi-copy"></i>

Copy

</button>

<button class="btn btn-dark" type="button" onclick="downloadFile('private.pem',document.getElementById('privateKey').innerText)">

<i class="bi bi-download"></i>

Download

</button>

</div>

</div>

</div>

<div class="card section-card">

<div class="card-body">

<h3>

<i class="bi bi-unlock-fill"></i>

Public Key

</h3>

<div class="code-box" id="publicKey"><?= htmlspecialchars($publicKey) ?></div>

<div class="mt-3">

<button class="btn btn-success" type="button" onclick="copyText('publicKey')">

<i class="bi bi-copy"></i>

Copy

</button>

<button class="btn btn-dark" type="button" onclick="downloadFile('public.pem',document.getElementById('publicKey').innerText)">

<i class="bi bi-download"></i>

Download

</button>

</div>

</div>

</div>

<div class="card section-card">

<div class="card-body">

<h3>

<i class="bi bi-file-earmark-lock2-fill"></i>

Certificate Signing Request (CSR)

</h3>

<div class="code-box" id="csr"><?= htmlspecialchars($csr) ?></div>

<div class="mt-3">

<button class="btn btn-success" type="button" onclick="copyText('csr')">

<i class="bi bi-copy"></i>

Copy

</button>

<button class="btn btn-dark" type="button" onclick="downloadFile('request.csr',document.getElementById('csr').innerText)">

<i class="bi bi-download"></i>

Download

</button>

<a href="apply.php" class="btn btn-primary">

Submit CSR

</a>

</div>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card section-card">

<div class="card-body">

<h3>

<i class="bi bi-shield-check"></i>

Security

</h3>

<hr>

<p>✅ RSA 2048 / 4096 Bit</p>

<p>✅ OpenSSL Generated</p>

<p>✅ HTTPS Protected</p>

<p>✅ No Private Key Stored</p>

<p>✅ Generated In Memory</p>

<p>✅ Copy & Download Supported</p>

</div>

</div>

<div class="card section-card">

<div class="card-body">

<h4>

Generation Steps

</h4>

<hr>

<ol>

<li>Fill in certificate details.</li>

<li>Select RSA key size.</li>

<li>Generate Key Pair.</li>

<li>Copy or Download files.</li>

<li>Submit CSR for issuance.</li>

</ol>

</div>

</div>

</div>

</div>

</div>

<footer class="bg-dark text-white text-center py-4">

© 2026 ACMECA Certificate Authority

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function showToast(message){

const toast=document.createElement("div");

toast.className="toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-4 show";

toast.innerHTML=`
<div class="d-flex">
<div class="toast-body">${message}</div>
<button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentElement.parentElement.remove()"></button>
</div>`;

document.body.appendChild(toast);

setTimeout(()=>{

toast.remove();

},2500);

}

function copyText(id){

const text=document.getElementById(id).innerText;

if(text.trim()==""){

showToast("Nothing to copy.");

return;

}

navigator.clipboard.writeText(text).then(()=>{

showToast("Copied successfully.");

});

}

function downloadFile(filename,text){

if(text.trim()==""){

showToast("Nothing to download.");

return;

}

const blob=new Blob([text],{type:"text/plain"});

const link=document.createElement("a");

link.href=URL.createObjectURL(blob);

link.download=filename;

document.body.appendChild(link);

link.click();

document.body.removeChild(link);

URL.revokeObjectURL(link.href);

showToast(filename+" downloaded.");

}

</script>

</body>

</html>

<?php require_once __DIR__ . "/config.php"; ?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Apply | ACMECA Certificate Authority</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<section class="py-5 bg-dark text-white">

<div class="container text-center">

<h1 class="display-4 fw-bold">

Certificate Application Portal

</h1>

<p class="lead mt-4">

Submit applications for DV, OV and EV SSL Certificates securely through the ACMECA Certificate Authority.

</p>

<div class="mt-5">

<a href="apply.php" class="btn btn-info btn-lg me-3">

Apply Now

</a>

<a href="status.php" class="btn btn-outline-light btn-lg">

Check Status

</a>

</div>

</div>

</section>

<section class="py-5">

<div class="container">

<div class="row g-4">

<div class="col-md-4">

<div class="card h-100 shadow">

<div class="card-body text-center">

<i class="bi bi-send-fill fs-1 text-primary"></i>

<h3 class="mt-3">

Submit Application

</h3>

<p>

Complete the certificate application form and upload your CSR.

</p>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card h-100 shadow">

<div class="card-body text-center">

<i class="bi bi-search fs-1 text-success"></i>

<h3 class="mt-3">

Track Progress

</h3>

<p>

Monitor your application status using your Application ID.

</p>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card h-100 shadow">

<div class="card-body text-center">

<i class="bi bi-download fs-1 text-danger"></i>

<h3 class="mt-3">

Download Certificate

</h3>

<p>

Download your issued certificate and certificate chain after approval.

</p>

</div>

</div>

</div>

</div>

</div>

</section>

<footer class="bg-dark text-white text-center py-3">

© 2026 ACMECA Certificate Authority

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

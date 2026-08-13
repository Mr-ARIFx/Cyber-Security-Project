<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
<div class="container">
<a class="navbar-brand fw-bold" href="index.php">
<i class="bi bi-shield-lock-fill text-info"></i>
ACMECA Apply
</a>
<button class="navbar-toggler"
type="button"
data-bs-toggle="collapse"
data-bs-target="#navbar">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbar">
<ul class="navbar-nav ms-auto">
<li class="nav-item">
<a class="nav-link" href="index.php">Home</a>
</li>
<li class="nav-item">
<a class="nav-link" href="generate.php">Generate CSR</a>
</li>
<li class="nav-item">
<a class="nav-link" href="apply.php">Apply</a>
</li>
<li class="nav-item">
<a class="nav-link" href="status.php">Status</a>
</li>
<li class="nav-item">
<a class="nav-link" href="download.php">Download</a>
</li>
<li class="nav-item">
<a class="nav-link" href="contact.php">Contact</a>
</li>
<?php if (current_user_id() !== null) { ?>
<li class="nav-item">
<a class="nav-link disabled" style="opacity:.7;">
<i class="bi bi-person-circle"></i>
<?= e($_SESSION['username'] ?? '') ?>
</a>
</li>
<li class="nav-item">
<a class="nav-link" href="/logout.php">
<i class="bi bi-box-arrow-right"></i>
Logout
</a>
</li>
<?php } else { ?>
<li class="nav-item">
<a class="nav-link" href="/login.php">
<i class="bi bi-box-arrow-in-right"></i>
Login
</a>
</li>
<li class="nav-item">
<a class="nav-link" href="/register.php">Register</a>
</li>
<?php } ?>
<li class="nav-item">
<a class="nav-link" href="https://acmeca.com">
<i class="bi bi-house-door-fill"></i>
Main Website
</a>
</li>
</ul>
</div>
</div>
</nav>

<?php
require_once __DIR__ . '/../config.php';
unset($_SESSION['admin_id'], $_SESSION['admin_username']);
flash('info', 'You have been logged out of the admin panel.');
header("Location: /admin/login.php");
exit;

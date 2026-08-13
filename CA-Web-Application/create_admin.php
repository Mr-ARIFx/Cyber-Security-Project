<?php
/**
 * create_admin.php — CLI-only script to create the first admin account.
 * Run via: php create_admin.php <username> <password> [display_name]
 * Do NOT expose this file on the web. Delete or restrict it after use.
 */
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

require_once __DIR__ . '/config.php';

$username = $argv[1] ?? null;
$password = $argv[2] ?? null;
$display_name = $argv[3] ?? $username;

if (!$username || !$password) {
    die("Usage: php create_admin.php <username> <password> [display_name]\n");
}

if (strlen($password) < MIN_PASSWORD_LENGTH) {
    die("Password must be at least " . MIN_PASSWORD_LENGTH . " characters.\n");
}

$stmt = db()->prepare("SELECT id FROM admin_users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    die("An admin with that username already exists.\n");
}

$insert = db()->prepare(
    "INSERT INTO admin_users (username, display_name, password_hash) VALUES (?, ?, ?)"
);
$insert->execute([$username, $display_name, hash_password($password)]);

echo "Admin account '$username' created successfully.\n";

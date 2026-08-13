<?php
/**
 * includes/auth.php — password hashing, session guards, CSRF protection.
 */
function hash_password(string $plain): string {
    return password_hash($plain, PASSWORD_ARGON2ID);
}
function verify_password(string $plain, string $hash): bool {
    return password_verify($plain, $hash);
}
function current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}
function require_login(): void {
    if (current_user_id() === null) {
        flash('warning', 'Please log in to continue.');
        header('Location: /login.php');
        exit;
    }
}
function current_admin_id(): ?int {
    return $_SESSION['admin_id'] ?? null;
}
function require_admin(): void {
    if (current_admin_id() === null) {
        header('Location: /admin/login.php');
        exit;
    }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}
function verify_csrf(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    if ($expected === '' || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        die('Invalid or expired form submission. Please go back and try again.');
    }
}

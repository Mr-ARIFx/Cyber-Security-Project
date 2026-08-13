<?php
/**
 * includes/functions.php — small shared helpers used across every page.
 */

function flash(string $category, string $message): void {
    if (!isset($_SESSION['flashes'])) {
        $_SESSION['flashes'] = [];
    }
    $_SESSION['flashes'][] = ['category' => $category, 'message' => $message];
}

function get_flashes(): array {
    $flashes = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);
    return $flashes;
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function generate_request_id(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function generate_reset_token(int $user_id): array {
    $raw_token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $raw_token);
    $expires_at = (new DateTime("+" . RESET_TOKEN_EXPIRY_MINUTES . " minutes"))->format('Y-m-d H:i:s');

    $stmt = db()->prepare(
        "INSERT INTO password_reset_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)"
    );
    $stmt->execute([$user_id, $token_hash, $expires_at]);

    return ['raw_token' => $raw_token, 'token_hash' => $token_hash];
}

function find_valid_reset_token(string $raw_token): ?array {
    $token_hash = hash('sha256', $raw_token);
    $stmt = db()->prepare(
        "SELECT * FROM password_reset_tokens WHERE token_hash = ? AND used = 0 AND expires_at > datetime('now')"
    );
    $stmt->execute([$token_hash]);
    $row = $stmt->fetch();
    return $row ?: null;
}

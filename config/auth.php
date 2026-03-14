<?php
// config/auth.php — User authentication helpers

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Check if a citizen user is logged in ──────────────────────────────────
function user_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ── Get current user data from session ────────────────────────────────────
function current_user(): array {
    if (!user_logged_in()) return [];
    return [
        'id'        => $_SESSION['user_id'],
        'full_name' => $_SESSION['user_name'] ?? '',
        'email'     => $_SESSION['user_email'] ?? '',
        'phone'     => $_SESSION['user_phone'] ?? '',
        'ward'      => $_SESSION['user_ward'] ?? '',
        'avatar'    => $_SESSION['user_avatar'] ?? '',
    ];
}

// ── Require login — redirect to login page with return URL ────────────────
function require_login(string $redirect_after = ''): void {
    if (!user_logged_in()) {
        // Use provided page name, or fall back to current URI
        $return = $redirect_after ?: basename($_SERVER['PHP_SELF']);
        header('Location: login.php?redirect=' . urlencode($return));
        exit;
    }
}

// ── Login a user — set session variables ──────────────────────────────────
function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']     = $user['id'];
    $_SESSION['user_name']   = $user['full_name'];
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_phone']  = $user['phone'];
    $_SESSION['user_ward']   = $user['ward'] ?? '';
    $_SESSION['user_avatar'] = $user['avatar'] ?? '';
}

// ── Logout ─────────────────────────────────────────────────────────────────
function logout_user(): void {
    $_SESSION = [];
    session_destroy();
}

// ── Get user avatar initials color ────────────────────────────────────────
function avatar_color(string $name): string {
    $colors = ['#0a4d8c','#1a7a4a','#c0392b','#8e44ad','#e8a000','#2980b9','#16a085'];
    return $colors[ord($name[0]) % count($colors)];
}

// ── Get first name ────────────────────────────────────────────────────────
function first_name(string $full): string {
    return explode(' ', trim($full))[0];
}
?>

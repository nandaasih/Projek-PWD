<?php
// includes/helpers.php
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function base_path(string $path = ""): string {
    // Sesuaikan jika folder project kamu bukan /FINAL_P
    $base = "/FINAL_P";
    return $base . $path;
}

function redirect(string $path): void {
    header("Location: " . base_path($path));
    exit;
}

// CSRF helpers
function generate_csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

function verify_csrf_token(?string $token): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($token) || empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Simple flash helper (via session)
function flash_set(string $key, string $msg): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION['flash_'.$key] = $msg;
}

function flash_get(string $key): ?string {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $k = 'flash_'.$key;
    if (!empty($_SESSION[$k])) {
        $v = $_SESSION[$k];
        unset($_SESSION[$k]);
        return $v;
    }
    return null;
}

// Notification helpers (require $conn when calling)
function notif_unread_count($conn, int $user_id): int {
    $user_id = (int)$user_id;
    $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM notifikasi WHERE user_id=$user_id AND is_read=0");
    if (!$r) return 0;
    $row = mysqli_fetch_assoc($r);
    return (int)($row['c'] ?? 0);
}

function notif_fetch($conn, int $user_id, int $limit = 6): array {
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    $q = mysqli_query($conn, "SELECT id, title, message, is_read, created_at FROM notifikasi WHERE user_id=$user_id ORDER BY created_at DESC LIMIT $limit");
    if (!$q) return [];
    $rows = [];
    while ($r = mysqli_fetch_assoc($q)) $rows[] = $r;
    return $rows;
}

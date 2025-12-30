<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

// Accept POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// CSRF (if provided) - will block when missing/invalid
$token = $_POST['csrf_token'] ?? '';
verify_csrf_token($token);

$userId = (int)($_SESSION['user_id'] ?? 0);
$id = $_POST['id'] ?? '';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($userId <= 0) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    } else {
        redirect('/login.php');
    }
    exit;
}

$affected = 0;
if ($id === 'all') {
    $stmt = mysqli_prepare($conn, "UPDATE notifikasi SET is_read=1 WHERE user_id=? AND is_read=0");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
    }
} else {
    $nid = (int)$id;
    if ($nid > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE notifikasi SET is_read=1 WHERE id=? AND user_id=? AND is_read=0");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $nid, $userId);
            mysqli_stmt_execute($stmt);
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'updated' => (int)$affected]);
    exit;
}

// non-AJAX fallback: redirect back
$back = $_SERVER['HTTP_REFERER'] ?? ($ _SESSION['role'] === 'admin' ? base_path('/admin/index.php') : base_path('/user/dashboard.php'));
redirect($back);

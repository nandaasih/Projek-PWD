<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

// CSRF protection
if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    flash_set('error', 'Token CSRF tidak valid');
    redirect('/admin/user_list.php');
}

$userId = (int)($_POST['id'] ?? 0);
if ($userId <= 0) {
    flash_set('error', 'User ID tidak valid');
    redirect('/admin/user_list.php');
}

// Prevent self-deletion
if ($userId === (int)$_SESSION['user_id']) {
    flash_set('error', 'Anda tidak bisa menghapus akun sendiri!');
    redirect('/admin/user_list.php');
}

// Fetch user to check if exists
$check = mysqli_query($conn, "SELECT id FROM users WHERE id=$userId LIMIT 1");
if (!$check || mysqli_num_rows($check) === 0) {
    flash_set('error', 'User tidak ditemukan');
    redirect('/admin/user_list.php');
}

// Delete user's profile picture if exists
$pp_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
if (mysqli_num_rows($pp_check) > 0) {
    $pp_result = mysqli_query($conn, "SELECT profile_picture FROM users WHERE id=$userId");
    if ($pp_result) {
        $pp_row = mysqli_fetch_assoc($pp_result);
        if (!empty($pp_row['profile_picture'])) {
            $pp_path = __DIR__ . '/../' . $pp_row['profile_picture'];
            if (file_exists($pp_path)) {
                @unlink($pp_path);
            }
        }
    }
}

// Delete all reservations for this user
$delete_reservations = mysqli_prepare($conn, "DELETE FROM reservasi WHERE user_id=?");
if ($delete_reservations === false) {
    error_log('DB prepare failed (delete reservations): ' . mysqli_error($conn));
    flash_set('error', 'Gagal menghapus reservasi user');
    redirect('/admin/user_list.php');
}
mysqli_stmt_bind_param($delete_reservations, "i", $userId);
mysqli_stmt_execute($delete_reservations);
mysqli_stmt_close($delete_reservations);

// Delete all notifications for this user
$delete_notif = mysqli_prepare($conn, "DELETE FROM notifikasi WHERE user_id=?");
if ($delete_notif === false) {
    error_log('DB prepare failed (delete notifications): ' . mysqli_error($conn));
    flash_set('error', 'Gagal menghapus notifikasi user');
    redirect('/admin/user_list.php');
}
mysqli_stmt_bind_param($delete_notif, "i", $userId);
mysqli_stmt_execute($delete_notif);
mysqli_stmt_close($delete_notif);

// Delete the user
$delete_user = mysqli_prepare($conn, "DELETE FROM users WHERE id=?");
if ($delete_user === false) {
    error_log('DB prepare failed (delete user): ' . mysqli_error($conn));
    flash_set('error', 'Gagal menghapus user');
    redirect('/admin/user_list.php');
}
mysqli_stmt_bind_param($delete_user, "i", $userId);
if (mysqli_stmt_execute($delete_user)) {
    mysqli_stmt_close($delete_user);
    flash_set('success', '✅ User berhasil dihapus beserta semua data terkait');
    redirect('/admin/user_list.php');
} else {
    error_log('DB execute failed (delete user): ' . mysqli_error($conn));
    mysqli_stmt_close($delete_user);
    flash_set('error', 'Gagal menghapus user');
    redirect('/admin/user_list.php');
}
?>

<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

// validate CSRF
if (!verify_csrf_token($_POST['csrf_token'] ?? $_GET['csrf'] ?? null)) {
  flash_set('error', 'Token CSRF tidak valid');
  redirect('/admin/reservasi_list.php');
}

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

 $stmt = mysqli_prepare($conn, "SELECT * FROM reservasi WHERE id=?");
 if ($stmt === false) { error_log('DB prepare failed (select reservasi): '.mysqli_error($conn)); redirect('/admin/reservasi_list.php'); }
 mysqli_stmt_bind_param($stmt, "i", $id);
 mysqli_stmt_execute($stmt);
 $res_stmt = mysqli_stmt_get_result($stmt);
 $r = $res_stmt ? mysqli_fetch_assoc($res_stmt) : null;
 mysqli_stmt_close($stmt);
 if (!$r) redirect('/admin/reservasi_list.php');

 $cek = mysqli_prepare($conn,
   "SELECT id FROM reservasi
    WHERE ruangan_id=? AND tanggal=? AND status='approved'
    AND NOT (waktu_selesai <= ? OR waktu_mulai >= ?)
    LIMIT 1"
 );
 if ($cek === false) { error_log('DB prepare failed (cek bentrok): '.mysqli_error($conn)); redirect('/admin/reservasi_list.php'); }
 mysqli_stmt_bind_param($cek, "isss", $r['ruangan_id'], $r['tanggal'], $r['waktu_mulai'], $r['waktu_selesai']);
 mysqli_stmt_execute($cek);
 $res_chk = mysqli_stmt_get_result($cek);
 if ($res_chk && mysqli_num_rows($res_chk) > 0) {
   // bentrok dengan jadwal approved yang sudah ada
   mysqli_stmt_close($cek);
   redirect('/admin/reservasi_list.php');
 }
 mysqli_stmt_close($cek);

 $update = mysqli_prepare($conn, "UPDATE reservasi SET status='approved' WHERE id=?");
 if ($update === false) { error_log('DB prepare failed (update reservasi): '.mysqli_error($conn)); redirect('/admin/reservasi_list.php'); }
 mysqli_stmt_bind_param($update, "i", $id);
 mysqli_stmt_execute($update);
 mysqli_stmt_close($update);

 $audit = mysqli_prepare($conn, "INSERT INTO audit_log(admin_id,action,reservasi_id,detail) VALUES(?,?,?,?)");
 if ($audit) {
   $user_id = (int)$_SESSION['user_id'];
   $action = 'approve';
   $detail = '';
   // types: int, string, int, string
   mysqli_stmt_bind_param($audit, "isis", $user_id, $action, $id, $detail);
   mysqli_stmt_execute($audit);
   mysqli_stmt_close($audit);
 } else {
   error_log('DB prepare failed (audit insert): '.mysqli_error($conn));
 }

// Create notification for the user who made the reservation
$user_id_for_notif = (int)($r['user_id'] ?? 0);
if ($user_id_for_notif > 0) {
  $room = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM ruangan WHERE id=" . (int)$r['ruangan_id'] . " LIMIT 1"));
  $room_name = $room['nama'] ?? 'ruangan';
  $date = date('d M Y', strtotime($r['tanggal'] ?? ''));
  $title = 'Reservasi Disetujui';
  $message = "Reservasi Anda untuk $room_name pada $date telah disetujui.";
  $insn = mysqli_prepare($conn, "INSERT INTO notifikasi (user_id, title, message) VALUES (?, ?, ?)");
  if ($insn) {
    mysqli_stmt_bind_param($insn, "iss", $user_id_for_notif, $title, $message);
    mysqli_stmt_execute($insn);
    mysqli_stmt_close($insn);
  }
}

redirect('/admin/reservasi_list.php');

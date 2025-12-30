<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf_token($_POST['csrf_token'] ?? '');

$userId = (int)$_SESSION['user_id'];

$ruangan_id = (int)($_POST['ruangan_id'] ?? 0);
$tanggal    = $_POST['tanggal'] ?? '';
$mulai      = $_POST['mulai'] ?? '';
$selesai    = $_POST['selesai'] ?? '';
$jumlah     = (int)($_POST['jumlah_peserta'] ?? 1);
$catatan    = trim($_POST['catatan'] ?? '');

if (!$ruangan_id || !$tanggal || !$mulai || !$selesai) redirect('/user/reservasi_add.php?error=Form tidak lengkap');
if ($mulai >= $selesai) redirect('/user/reservasi_add.php?error=Waktu tidak valid');

$room = mysqli_fetch_assoc(mysqli_query($conn, "SELECT kapasitas FROM ruangan WHERE id=$ruangan_id AND status='aktif'"));
if (!$room) redirect('/user/reservasi_add.php?error=Ruangan tidak valid');
if ($jumlah > (int)$room['kapasitas']) redirect('/user/reservasi_add.php?error=Jumlah peserta melebihi kapasitas');

$cek = mysqli_prepare($conn,
  "SELECT id FROM reservasi
   WHERE ruangan_id=? AND tanggal=? AND status IN ('pending','approved')
   AND NOT (waktu_selesai <= ? OR waktu_mulai >= ?)
   LIMIT 1"
);
mysqli_stmt_bind_param($cek, "isss", $ruangan_id, $tanggal, $mulai, $selesai);
mysqli_stmt_execute($cek);
$res = mysqli_stmt_get_result($cek);
if (mysqli_num_rows($res) > 0) redirect('/user/reservasi_add.php?error=Jadwal bentrok');

$ins = mysqli_prepare($conn,
  "INSERT INTO reservasi(user_id,ruangan_id,tanggal,waktu_mulai,waktu_selesai,jumlah_peserta,catatan,status)
   VALUES(?,?,?,?,?,?,?,'pending')"
);
mysqli_stmt_bind_param($ins, "iisssis", $userId, $ruangan_id, $tanggal, $mulai, $selesai, $jumlah, $catatan);
mysqli_stmt_execute($ins);

$reservasi_id = mysqli_insert_id($conn);

// Notify all admins about new reservation
$notif_stmt = mysqli_prepare($conn, "INSERT INTO notifikasi (user_id, title, message) VALUES (?, ?, ?)");
if ($notif_stmt) {
  $admins = mysqli_query($conn, "SELECT id FROM users WHERE role='admin'");
  $title = 'Reservasi Baru';
  $msg = 'Ada reservasi baru (ID: ' . (int)$reservasi_id . ') menunggu verifikasi.';
  if ($admins) {
    while ($a = mysqli_fetch_assoc($admins)) {
      $admin_id = (int)$a['id'];
      mysqli_stmt_bind_param($notif_stmt, "iss", $admin_id, $title, $msg);
      mysqli_stmt_execute($notif_stmt);
    }
  }
  mysqli_stmt_close($notif_stmt);
}

redirect('/user/reservasi_history.php');

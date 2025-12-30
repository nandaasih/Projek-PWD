<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf_token($_POST['csrf_token'] ?? '');

// Simple server-side validation
$nama = trim($_POST['nama'] ?? '');
$lok  = trim($_POST['lokasi'] ?? '');
$kap  = (int)($_POST['kapasitas'] ?? 1);
$f    = trim($_POST['fasilitas'] ?? '');
$st   = $_POST['status'] ?? 'aktif';

if ($nama === '') {
  // Missing required field, redirect back to form
  redirect('/admin/ruangan_tambah.php');
}

$q = mysqli_prepare($conn, "INSERT INTO ruangan (nama, lokasi, kapasitas, fasilitas, status) VALUES (?, ?, ?, ?, ?)");
if (!$q) {
  // Query prepare failed
  redirect('/admin/ruangan_tambah.php');
}

mysqli_stmt_bind_param($q, "ssiss", $nama, $lok, $kap, $f, $st);
$ok = mysqli_stmt_execute($q);

if (!$ok) {
  // insertion failed
  redirect('/admin/ruangan_tambah.php');
}

redirect('/admin/ruangan_list.php');

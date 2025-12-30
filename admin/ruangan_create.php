<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$nama = trim($_POST['nama'] ?? '');
$lok  = trim($_POST['lokasi'] ?? '');
$kap  = (int)($_POST['kapasitas'] ?? 1);
$f    = trim($_POST['fasilitas'] ?? '');
$st   = $_POST['status'] ?? 'aktif';

$q = mysqli_prepare($conn, "INSERT INTO ruangan(nama,lokasi,kapasitas,fasilitas,status) VALUES(?,?,?,?,?)");
mysqli_stmt_bind_param($q, "ssiss", $nama, $lok, $kap, $f, $st);
mysqli_stmt_execute($q);

redirect('/admin/ruangan_list.php');

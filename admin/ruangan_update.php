<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf_token($_POST['csrf_token'] ?? '');

$id = (int)($_POST['id'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$lok  = trim($_POST['lokasi'] ?? '');
$kap  = (int)($_POST['kapasitas'] ?? 1);
$f    = trim($_POST['fasilitas'] ?? '');
$st   = $_POST['status'] ?? 'aktif';

$q = mysqli_prepare($conn, "UPDATE ruangan SET nama=?, lokasi=?, kapasitas=?, fasilitas=?, status=? WHERE id=?");
mysqli_stmt_bind_param($q, "ssissi", $nama, $lok, $kap, $f, $st, $id);
mysqli_stmt_execute($q);

redirect('/admin/ruangan_list.php');

<?php
require_once __DIR__ . '/../includes/database.php';

header('Content-Type: application/json');

$ruangan_id = (int)($_GET['ruangan_id'] ?? 0);
$tanggal    = $_GET['tanggal'] ?? '';
$mulai      = $_GET['mulai'] ?? '';
$selesai    = $_GET['selesai'] ?? '';

if (!$ruangan_id || !$tanggal || !$mulai || !$selesai) {
  echo json_encode(['ok'=>false,'msg'=>'parameter kurang']); exit;
}

$q = mysqli_prepare($conn,
  "SELECT id FROM reservasi
   WHERE ruangan_id=? AND tanggal=? AND status IN ('pending','approved')
   AND NOT (waktu_selesai <= ? OR waktu_mulai >= ?)
   LIMIT 1"
);
mysqli_stmt_bind_param($q, "isss", $ruangan_id, $tanggal, $mulai, $selesai);
mysqli_stmt_execute($q);
$res = mysqli_stmt_get_result($q);

echo json_encode(['ok'=> (mysqli_num_rows($res)===0)]);

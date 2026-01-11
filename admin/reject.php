<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  redirect('/admin/reservasi_list.php');
}

// fetch reservation for context
$res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT r.*, u.fullname, ru.nama AS ruangan FROM reservasi r JOIN users u ON u.id=r.user_id JOIN ruangan ru ON ru.id=r.ruangan_id WHERE r.id=$id LIMIT 1"));
if (!$res) redirect('/admin/reservasi_list.php');

$title = "Reject Reservasi #$id";
ob_start();
?>

<h2 class="h">Tolak Reservasi</h2>
<div class="card">
  <p><strong>User:</strong> <?= e($res['fullname']) ?></p>
  <p><strong>Ruangan:</strong> <?= e($res['ruangan']) ?></p>
  <p><strong>Tanggal:</strong> <?= e($res['tanggal']) ?> <?= e($res['waktu_mulai']) ?> - <?= e($res['waktu_selesai']) ?></p>

  <form method="post" action="<?= base_path('/actions/reject.php') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <div style="margin:12px 0">
      <label for="reason">Alasan penolakan (optional, akan terlihat oleh user)</label>
      <textarea name="reason" id="reason" rows="5" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:8px"></textarea>
    </div>
    <p>
      <button class="btn danger" type="submit">Tolak Reservasi</button>
      <a class="btn" href="<?= base_path('/admin/reservasi_list.php') ?>">Batal</a>
    </p>
  </form>
</div>

<?php 
$page_content = ob_get_clean();
require __DIR__ . '/../templates/layout-admin.php';
?>
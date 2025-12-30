<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = (int)($_GET['id'] ?? 0);
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ruangan WHERE id=$id"));
if (!$r) redirect('/admin/ruangan_list.php');

$title="Edit Ruangan";
require __DIR__ . '/../templates/header.php';
?>
<h2 class="h">Edit Ruangan</h2>
<form method="post" action="<?= base_path('/actions/ruangan_update.php') ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
  <label>Nama</label><input class="input" name="nama" value="<?= e($r['nama']) ?>" required>
  <label>Lokasi</label><input class="input" name="lokasi" value="<?= e($r['lokasi'] ?? '') ?>">
  <label>Kapasitas</label><input class="input" type="number" name="kapasitas" min="1" value="<?= (int)$r['kapasitas'] ?>" required>
  <label>Fasilitas</label><input class="input" name="fasilitas" value="<?= e($r['fasilitas'] ?? '') ?>">
  <label>Status</label>
  <select class="input" name="status">
    <option value="aktif" <?= $r['status']==='aktif'?'selected':'' ?>>aktif</option>
    <option value="nonaktif" <?= $r['status']==='nonaktif'?'selected':'' ?>>nonaktif</option>
  </select>
  <p style="margin-top:12px"><button class="btn ok" type="submit">Update</button></p>
</form>
<?php require __DIR__ . '/../templates/footer.php'; ?>

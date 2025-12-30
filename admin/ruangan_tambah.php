<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/helpers.php';

$title="Tambah Ruangan";
require __DIR__ . '/../templates/header.php';
?>
<h2 class="h">Tambah Ruangan</h2>
<form method="post" action="<?= base_path('/actions/ruangan_create.php') ?>">
  <?= csrf_field() ?>
  <label>Nama</label><input class="input" name="nama" required>
  <label>Lokasi</label><input class="input" name="lokasi">
  <label>Kapasitas</label><input class="input" type="number" name="kapasitas" min="1" value="1" required>
  <label>Fasilitas</label><input class="input" name="fasilitas">
  <label>Status</label>
  <select class="input" name="status">
    <option value="aktif">aktif</option>
    <option value="nonaktif">nonaktif</option>
  </select>
  <p style="margin-top:12px"><button class="btn ok" type="submit">Simpan</button></p>
</form>
<?php require __DIR__ . '/../templates/footer.php'; ?>

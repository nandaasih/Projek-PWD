<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$result = mysqli_query($conn, 'SELECT * FROM ruangan ORDER BY id DESC');
$ruangan = mysqli_fetch_all($result, MYSQLI_ASSOC);

$title = "Daftar Ruangan";
require __DIR__ . '/../templates/header.php';
?>

<section class="admin-section">
  <div class="section-header">
    <div class="section-title-wrapper">
      <h1 class="page-title">🚪 Daftar Ruangan</h1>
      <p class="page-subtitle">Kelola semua ruangan yang tersedia dalam sistem</p>
    </div>
    <a href="<?= base_path('/admin/ruangan_tambah.php') ?>" class="btn-add-new">
      <span>➕</span> Tambah Ruangan
    </a>
  </div>

  <?php if (count($ruangan) > 0): ?>
    <div class="rooms-grid">
      <?php foreach ($ruangan as $row): ?>
        <div class="room-card">
          <div class="room-header">
            <div class="room-name-section">
              <h3 class="room-name"><?= e($row['nama']) ?></h3>
              <span class="room-capacity">
                <span class="capacity-icon">👥</span>
                <?= (int)$row['kapasitas'] ?> orang
              </span>
            </div>
          </div>

          <div class="room-body">
            <div class="room-info-item">
              <span class="info-label">📍 Lokasi</span>
              <span class="info-value"><?= !empty($row['lokasi']) ? e($row['lokasi']) : '<em style="color:#999;">Belum ditentukan</em>' ?></span>
            </div>
            <div class="room-info-item">
              <span class="info-label">🆔 ID Ruangan</span>
              <span class="info-value">#<?= (int)$row['id'] ?></span>
            </div>
          </div>

          <div class="room-footer">
            <a href="<?= base_path("/admin/ruangan_edit.php?id=" . (int)$row['id']) ?>" class="room-action edit">
              <span>✏️</span> Edit
            </a>
            <form method="POST" action="<?= base_path("/admin/ruangan_hapus.php") ?>" style="display:inline;">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
              <button type="submit" class="room-action delete" onclick="return confirm('Yakin ingin menghapus ruangan ini?')">
                <span>🗑️</span> Hapus
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon">🚪</div>
      <h3>Belum Ada Ruangan</h3>
      <p>Tidak ada ruangan yang terdaftar. Mulai dengan menambahkan ruangan baru.</p>
      <a href="<?= base_path('/admin/ruangan_tambah.php') ?>" class="btn-add-new">
        <span>➕</span> Tambah Ruangan Pertama
      </a>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/../templates/footer.php'; ?>

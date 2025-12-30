<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ambil daftar semua ruangan aktif
$stmt = mysqli_prepare($conn, "SELECT id, nama, lokasi, kapasitas, fasilitas, status FROM ruangan WHERE status='aktif' ORDER BY nama");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ruangan_list = mysqli_fetch_all($result, MYSQLI_ASSOC);

$title = "Daftar Ruangan";
require __DIR__ . '/../templates/header.php';
?>

<section class="ruangan-section">
  <div class="ruangan-wrapper">
    
    <!-- Back Button -->
    <a href="<?= base_path('/user/dashboard.php') ?>" class="back-link">
      <span>← Kembali ke Dashboard</span>
    </a>

    <!-- Page Header -->
    <div class="ruangan-header">
      <h1>🚪 Daftar Ruangan</h1>
      <p>Pilih ruangan yang ingin Anda reservasi</p>
    </div>

    <!-- Ruangan Grid -->
    <div class="ruangan-grid">
      <?php if (empty($ruangan_list)): ?>
        <div class="no-data">
          <p>📭 Tidak ada ruangan tersedia</p>
        </div>
      <?php else: ?>
        <?php foreach ($ruangan_list as $room): ?>
          <div class="ruangan-card">
            <div class="room-header">
              <h3 class="room-name"><?= e($room['nama']) ?></h3>
              <span class="room-status">✓ Aktif</span>
            </div>

            <div class="room-details">
              <?php if (!empty($room['lokasi'])): ?>
                <div class="detail-item">
                  <span class="detail-icon">📍</span>
                  <span class="detail-text"><?= e($room['lokasi']) ?></span>
                </div>
              <?php endif; ?>

              <div class="detail-item">
                <span class="detail-icon">👥</span>
                <span class="detail-text">Kapasitas: <?= (int)$room['kapasitas'] ?> orang</span>
              </div>

              <?php if (!empty($room['fasilitas'])): ?>
                <div class="detail-item">
                  <span class="detail-icon">⭐</span>
                  <div class="fasilitas-list">
                    <?php 
                    $fasilitas = explode(',', $room['fasilitas']);
                    foreach ($fasilitas as $f): 
                      $f = trim($f);
                      if (!empty($f)):
                    ?>
                      <span class="facility-badge"><?= e($f) ?></span>
                    <?php 
                      endif;
                    endforeach; 
                    ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <div class="room-action">
              <a href="<?= base_path('/user/reservasi_add.php') ?>" class="btn btn-primary">
                <span>📅</span> Pesan Ruangan Ini
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>
</section>

<style>
.ruangan-section {
  background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
  min-height: 100vh;
  padding: 24px 0 40px 0;
}

.ruangan-wrapper {
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 18px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #0070f3;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 24px;
  transition: color 0.2s;
}

.back-link:hover {
  color: #0051cc;
  text-decoration: underline;
}

.ruangan-header {
  margin-bottom: 32px;
}

.ruangan-header h1 {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 700;
  color: #0f172a;
}

.ruangan-header p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
}

.ruangan-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.ruangan-card {
  background: #fff;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  border: 1px solid #e5e7eb;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.ruangan-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0, 112, 243, 0.12);
  border-color: #0070f3;
}

.room-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.room-name {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
}

.room-status {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  background: #d1fae5;
  color: #065f46;
  white-space: nowrap;
}

.room-details {
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex: 1;
}

.detail-item {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  font-size: 13px;
  color: #334155;
}

.detail-icon {
  font-size: 16px;
  flex-shrink: 0;
  margin-top: 2px;
}

.detail-text {
  flex: 1;
}

.fasilitas-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.facility-badge {
  display: inline-block;
  padding: 4px 10px;
  background: #e0e7ff;
  color: #3730a3;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
}

.room-action {
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.no-data {
  grid-column: 1 / -1;
  padding: 40px;
  text-align: center;
  background: #f9fafb;
  border-radius: 12px;
  border: 2px dashed #e5e7eb;
  color: #64748b;
}

@media (max-width: 768px) {
  .ruangan-wrapper {
    padding: 0 12px;
  }

  .ruangan-grid {
    grid-template-columns: 1fr;
  }

  .ruangan-header h1 {
    font-size: 22px;
  }
}
</style>

<?php require __DIR__ . '/../templates/footer.php'; ?>

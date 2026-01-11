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

// Start output buffering
ob_start();
?>

<div class="ruangan-list-wrapper">
    <div class="ruangan-header">
        <h2 class="section-title">🏛️ Daftar Ruangan</h2>
        <p style="color: #6b7280; margin: 8px 0 0 0;">Pilih ruangan yang ingin Anda reservasi</p>
    </div>

    <!-- Ruangan Grid -->
    <div class="ruangan-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; margin-top: 24px;">
      <?php if (empty($ruangan_list)): ?>
        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
          <p style="font-size: 18px; color: #6b7280; margin: 0;">📭 Tidak ada ruangan tersedia</p>
        </div>
      <?php else: ?>
        <?php foreach ($ruangan_list as $room): ?>
          <div style="padding: 20px; background: white; border-radius: 8px; border: 1px solid #e5e7eb; display: flex; flex-direction: column; gap: 12px;">
            <div>
              <div style="display: flex; justify-content: space-between; align-items: start; gap: 12px; margin-bottom: 12px;">
                <h3 style="font-size: 16px; font-weight: 600; margin: 0; flex: 1;">
                  🚪 <?= e($room['nama']) ?>
                </h3>
                <span style="padding: 4px 10px; background: #d1fae5; color: #065f46; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap;">✓ Aktif</span>
              </div>

              <?php if (!empty($room['lokasi'])): ?>
                <div style="display: flex; gap: 8px; align-items: center; color: #6b7280; font-size: 14px; margin-bottom: 8px;">
                  <span>📍</span>
                  <span><?= e($room['lokasi']) ?></span>
                </div>
              <?php endif; ?>

              <div style="display: flex; gap: 8px; align-items: center; color: #6b7280; font-size: 14px; margin-bottom: 12px;">
                <span>👥</span>
                <span>Kapasitas: <?= (int)$room['kapasitas'] ?> orang</span>
              </div>

              <?php if (!empty($room['fasilitas'])): ?>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                  <div style="color: #6b7280; font-size: 13px; font-weight: 500;">⭐ Fasilitas:</div>
                  <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    <?php 
                    $fasilitas = explode(',', $room['fasilitas']);
                    foreach ($fasilitas as $f): 
                      $f = trim($f);
                      if (!empty($f)):
                    ?>
                      <span style="display: inline-block; padding: 4px 10px; background: #e0e7ff; color: #3730a3; border-radius: 6px; font-size: 12px; font-weight: 500;"><?= e($f) ?></span>
                    <?php 
                      endif;
                    endforeach; 
                    ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <a href="<?= base_path('/user/reservasi_add.php?ruangan_id=') . (int)$room['id'] ?>" class="btn btn-primary" style="padding: 10px 16px; text-decoration: none; text-align: center; background: #0066cc; color: white; border-radius: 6px; font-weight: 600; margin-top: auto;">
              📅 Pesan Ruangan Ini
            </a>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
</div>

<?php
// Capture the HTML content
$page_content = ob_get_clean();

// Load the user layout template with admin style
require __DIR__ . '/../templates/layout-user-admin.php';
?>

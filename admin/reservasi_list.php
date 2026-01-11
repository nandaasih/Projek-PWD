<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

 $list = mysqli_query($conn,
   "SELECT r.*, u.fullname, ru.nama AS ruangan
    FROM reservasi r
    JOIN users u ON u.id=r.user_id
    JOIN ruangan ru ON ru.id=r.ruangan_id
    ORDER BY r.status='pending' DESC, r.tanggal DESC, r.waktu_mulai DESC"
 );
 if ($list === false) {
     error_log('DB error (reservasi_list): ' . mysqli_error($conn));
 }

$title="Kelola Reservasi";

// Start output buffering to capture HTML content
ob_start();
?>

<!-- Flash Messages -->
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div style="padding: 14px 16px; background: #d1fae5; color: #065f46; border-radius: 8px; border: 1px solid #6ee7b7; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <span style="font-size: 18px;">✅</span>
    <div>
      <strong>Berhasil!</strong>
      <div style="font-size: 13px;"><?= flash_get('success') ?></div>
    </div>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div style="padding: 14px 16px; background: #fee2e2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <span style="font-size: 18px;">❌</span>
    <div>
      <strong>Error!</strong>
      <div style="font-size: 13px;"><?= flash_get('error') ?></div>
    </div>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div style="margin-bottom: 24px;">
  <h1 style="margin: 0; font-size: 28px; font-weight: 700; color: #1f2937;">📋 Kelola Reservasi</h1>
  <p style="margin: 8px 0 0 0; color: #6b7280; font-size: 14px;">Kelola semua permintaan reservasi ruangan</p>
</div>

<div style="display: flex; flex-direction: column; gap: 16px;">
  <?php 
    $count = 0;
    while($r=mysqli_fetch_assoc($list)): 
      $count++;
      $status = $r['status'];
      $status_display = '';
      $status_bg = '';
      $status_text = '';
      
      if ($status === 'pending') {
        $status_display = '⏳ Menunggu Persetujuan';
        $status_bg = '#fef3c7';
        $status_text = '#92400e';
      } elseif ($status === 'approved') {
        $status_display = '✅ Disetujui';
        $status_bg = '#d1fae5';
        $status_text = '#065f46';
      } elseif ($status === 'rejected') {
        $status_display = '❌ Ditolak';
        $status_bg = '#fee2e2';
        $status_text = '#991b1b';
      } else {
        $status_display = ucfirst($status);
        $status_bg = '#f3f4f6';
        $status_text = '#1f2937';
      }
  ?>
    <div style="padding: 20px; background: white; border-radius: 8px; border: 1px solid #e5e7eb; display: grid; grid-template-columns: 1fr auto; gap: 24px; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
      <!-- Reservation Details (Left) -->
      <div>
        <div style="margin-bottom: 16px;">
          <div style="font-weight: 700; color: #1f2937; font-size: 16px; margin-bottom: 4px;">👤 <?= e($r['fullname']) ?></div>
          <div style="color: #6b7280; font-size: 14px;">🚪 <?= e($r['ruangan']) ?></div>
        </div>
        <div style="display: flex; gap: 20px; color: #6b7280; font-size: 13px;">
          <div style="display: flex; align-items: center; gap: 6px;">📅 <?= date('d M Y', strtotime($r['tanggal'])) ?></div>
          <div style="display: flex; align-items: center; gap: 6px;">⏰ <?= e($r['waktu_mulai']) ?> - <?= e($r['waktu_selesai']) ?></div>
        </div>
      </div>

      <!-- Status and Actions (Right) -->
      <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; justify-content: flex-end;">
        <div style="padding: 8px 16px; background: <?= $status_bg ?>; border-radius: 6px; font-weight: 600; font-size: 13px; color: <?= $status_text ?>; white-space: nowrap;">
          <?= $status_display ?>
        </div>
        
        <?php if ($r['status'] === 'pending'): ?>
          <form method="POST" action="<?= base_path('/admin/approve.php') ?>" style="display: inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button type="submit" style="padding: 8px 16px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; transition: transform 0.2s;">✅ Setujui</button>
          </form>
          <a href="<?= base_path('/admin/reject.php?id='.$r['id']) ?>" onclick="return confirm('Yakin ingin menolak reservasi ini?')" style="padding: 8px 16px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; text-decoration: none; display: inline-block; transition: transform 0.2s;">❌ Tolak</a>
        <?php else: ?>
          <a href="<?= base_path('/admin/reservasi_view.php?id='.$r['id']) ?>" style="padding: 8px 16px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; text-decoration: none; display: inline-block; transition: transform 0.2s;">👁️ Lihat</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endwhile; ?>

  <?php if ($count === 0): ?>
    <div style="padding: 40px 20px; text-align: center; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
      <div style="font-size: 32px; margin-bottom: 12px;">✨</div>
      <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">Tidak ada reservasi</div>
      <div style="color: #6b7280; font-size: 14px;">Semua reservasi sudah diproses</div>
    </div>
  <?php endif; ?>
</div>

<?php
// Capture the HTML content
$page_content = ob_get_clean();

// Load the admin layout template which handles header, sidebar, footer
require __DIR__ . '/../templates/layout-admin.php';
?>

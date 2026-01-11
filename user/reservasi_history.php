<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = (int)$_SESSION['user_id'];

// Ambil riwayat reservasi
$list = mysqli_query($conn,
  "SELECT r.*, ru.nama AS ruangan, ru.lokasi
   FROM reservasi r
   JOIN ruangan ru ON ru.id=r.ruangan_id
   WHERE r.user_id=$userId
   ORDER BY r.tanggal DESC, r.waktu_mulai DESC"
);

$reservations = mysqli_fetch_all($list, MYSQLI_ASSOC);

$title = "Riwayat Reservasi";

// Start output buffering
ob_start();

// Helper function untuk format tanggal
function format_date($date) {
  return date('d M Y', strtotime($date));
}

// Helper function untuk get status badge
function get_status_badge($status) {
  $badges = [
    'pending' => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => '⏳', 'text' => 'Menunggu'],
    'approved' => ['bg' => '#d1fae5', 'color' => '#065f46', 'icon' => '✓', 'text' => 'Disetujui'],
    'rejected' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'icon' => '✗', 'text' => 'Ditolak'],
    'canceled' => ['bg' => '#e5e7eb', 'color' => '#374151', 'icon' => '✕', 'text' => 'Dibatalkan']
  ];
  return $badges[$status] ?? ['bg' => '#e5e7eb', 'color' => '#374151', 'icon' => '-', 'text' => ucfirst($status)];
}
?>

<div class="history-wrapper">
    <div class="history-header" style="margin-bottom: 24px;">
        <h2 class="section-title">⏰ Riwayat Reservasi</h2>
        <p style="color: #6b7280; margin: 8px 0 0 0;">Kelola dan lihat semua reservasi Anda</p>
    </div>

    <!-- Reservations -->
    <?php if (empty($reservations)): ?>
      <div style="padding: 40px; text-align: center; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
        <p style="font-size: 18px; color: #6b7280; margin: 0;">📭 Belum Ada Reservasi</p>
        <p style="color: #9ca3af; margin-top: 8px;">Anda belum memiliki riwayat reservasi.</p>
        <a href="<?= base_path('/user/reservasi_add.php') ?>" class="btn btn-primary" style="margin-top: 12px; padding: 10px 20px;">+ Buat Reservasi</a>
      </div>
    <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 12px;">
        <?php foreach ($reservations as $res): 
          $status_info = get_status_badge($res['status']);
          $date = strtotime($res['tanggal']);
          $today = strtotime(date('Y-m-d'));
          $is_past = $date < $today;
        ?>
          <div style="padding: 20px; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: start; gap: 16px; margin-bottom: 16px;">
              <div style="flex: 1;">
                <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 8px 0;">
                  🚪 <?= e($res['ruangan']) ?>
                </h3>
                <?php if (!empty($res['lokasi'])): ?>
                  <p style="margin: 0; color: #6b7280; font-size: 14px;">
                    📍 <?= e($res['lokasi']) ?>
                  </p>
                <?php endif; ?>
              </div>
              <span style="padding: 6px 12px; background: <?= $status_info['bg'] ?>; color: <?= $status_info['color'] ?>; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap;">
                <?= $status_info['icon'] ?> <?= $status_info['text'] ?>
              </span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
              <div>
                <span style="color: #9ca3af; font-size: 12px; font-weight: 500;">Tanggal</span>
                <div style="color: #1f2937; font-weight: 600; font-size: 14px;">📅 <?= format_date($res['tanggal']) ?></div>
              </div>
              <div>
                <span style="color: #9ca3af; font-size: 12px; font-weight: 500;">Waktu</span>
                <div style="color: #1f2937; font-weight: 600; font-size: 14px;">⏰ <?= e($res['waktu_mulai']) ?> - <?= e($res['waktu_selesai']) ?></div>
              </div>
              <div>
                <span style="color: #9ca3af; font-size: 12px; font-weight: 500;">Peserta</span>
                <div style="color: #1f2937; font-weight: 600; font-size: 14px;">👥 <?= (int)$res['jumlah_peserta'] ?> orang</div>
              </div>
            </div>

            <?php if (!empty($res['keterangan'])): ?>
              <div style="padding: 12px; background: #f3f4f6; border-radius: 6px; margin-bottom: 12px; border-left: 3px solid #3b82f6;">
                <strong style="font-size: 13px;">Catatan:</strong>
                <div style="font-size: 13px; color: #6b7280; margin-top: 4px;"><?= e($res['keterangan']) ?></div>
              </div>
            <?php endif; ?>

            <?php if ($res['status'] === 'rejected' && !empty($res['reject_reason'])): ?>
              <div style="padding: 12px; background: #fee2e2; border-radius: 6px; margin-bottom: 12px; border-left: 3px solid #dc2626;">
                <strong style="font-size: 13px; color: #991b1b;">Alasan Penolakan:</strong>
                <div style="font-size: 13px; color: #7f1d1d; margin-top: 4px;"><?= e($res['reject_reason']) ?></div>

                <?php
                  $roomLink = base_path('/user/ruangan_list.php');
                  $admin_contact_q = mysqli_query($conn, "SELECT fullname, email FROM users WHERE role='admin' LIMIT 1");
                  $admin_contact = $admin_contact_q ? mysqli_fetch_assoc($admin_contact_q) : null;
                  $admin_email = $admin_contact['email'] ?? '';
                ?>
                <div style="margin-top: 8px; font-size: 12px; color: #7f1d1d;">
                  <div>• Lihat daftar ruangan: <a href="<?= $roomLink ?>" style="color: #991b1b; font-weight: 600;">Daftar Ruangan</a></div>
                  <?php if (!empty($admin_email) && filter_var($admin_email, FILTER_VALIDATE_EMAIL)): ?>
                    <div style="margin-top: 6px;">• Hubungi admin: <a href="mailto:<?= e($admin_email) ?>" style="color: #991b1b; font-weight: 600;"><?= e($admin_email) ?></a></div>
                  <?php else: ?>
                    <div style="margin-top: 6px;">• Hubungi admin untuk informasi lebih lanjut.</div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <div style="display: flex; gap: 8px;">
              <a href="<?= base_path('/user/reservasi_view.php?id=' . (int)$res['id']) ?>" class="btn btn-sm btn-info" style="padding: 8px 16px; text-decoration: none; text-align: center; background: #0066cc; color: white; border-radius: 6px; font-weight: 600; font-size: 13px;">
                👁️ Lihat Detail
              </a>
              <?php if ($res['status'] === 'pending'): ?>
                <form method="POST" action="<?= base_path('/user/reservasi_delete.php') ?>" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$res['id'] ?>">
                  <button type="submit" class="btn btn-sm" style="padding: 8px 16px; background: #dc2626; color: white; border: none; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;" onclick="return confirm('Batalkan reservasi ini?')">
                    🗑️ Batalkan
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
</div>

<?php
// Capture the HTML content
$page_content = ob_get_clean();

// Load the user layout template with admin style
require __DIR__ . '/../templates/layout-user-admin.php';
?>


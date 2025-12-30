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
require __DIR__ . '/../templates/header.php';

// Helper function untuk format tanggal
function format_date($date) {
  return date('d M Y', strtotime($date));
}

// Helper function untuk get status badge
function get_status_badge($status) {
  $badges = [
    'pending' => ['bg' => '#fed7aa', 'color' => '#92400e', 'icon' => '⏳'],
    'approved' => ['bg' => '#d1fae5', 'color' => '#065f46', 'icon' => '✓'],
    'rejected' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'icon' => '✗'],
    'canceled' => ['bg' => '#e5e7eb', 'color' => '#374151', 'icon' => '✕']
  ];
  return $badges[$status] ?? ['bg' => '#e5e7eb', 'color' => '#374151', 'icon' => '-'];
}
?>

<section class="history-section">
  <div class="history-wrapper">
    
    <!-- Back Button -->
    <a href="<?= base_path('/user/dashboard.php') ?>" class="back-link">
      <span>← Kembali ke Dashboard</span>
    </a>

    <!-- Page Header -->
    <div class="history-header">
      <h1>📜 Riwayat Reservasi</h1>
      <p>Kelola dan lihat semua reservasi Anda</p>
    </div>

    <!-- Reservations -->
    <?php if (empty($reservations)): ?>
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>Belum Ada Reservasi</h3>
        <p>Anda belum memiliki riwayat reservasi. <a href="<?= base_path('/user/reservasi_add.php') ?>" class="empty-link">Buat reservasi baru</a></p>
      </div>
    <?php else: ?>
      <div class="reservations-container">
        <?php foreach ($reservations as $res): 
          $status_info = get_status_badge($res['status']);
          $date = strtotime($res['tanggal']);
          $today = strtotime(date('Y-m-d'));
          $is_past = $date < $today;
        ?>
          <div class="history-card <?= $is_past ? 'past' : 'upcoming' ?>">
            <div class="card-status">
              <span class="status-badge" style="background: <?= $status_info['bg'] ?>; color: <?= $status_info['color'] ?>;">
                <?= $status_info['icon'] ?> <?= ucfirst($res['status']) ?>
              </span>
            </div>

            <div class="card-content">
              <div class="content-top">
                <div class="room-info">
                  <h3 class="room-name">🚪 <?= e($res['ruangan']) ?></h3>
                  <?php if (!empty($res['lokasi'])): ?>
                    <p class="room-location">📍 <?= e($res['lokasi']) ?></p>
                  <?php endif; ?>
                </div>
                <div class="reservation-meta">
                  <div class="meta-item">
                    <span class="meta-label">Tanggal</span>
                    <span class="meta-value"><?= format_date($res['tanggal']) ?></span>
                  </div>
                  <div class="meta-item">
                    <span class="meta-label">Waktu</span>
                    <span class="meta-value"><?= $res['waktu_mulai'] ?> - <?= $res['waktu_selesai'] ?></span>
                  </div>
                  <div class="meta-item">
                    <span class="meta-label">Peserta</span>
                    <span class="meta-value">👥 <?= (int)$res['jumlah_peserta'] ?> orang</span>
                  </div>
                </div>
              </div>

              <?php if (!empty($res['keterangan'])): ?>
                <div class="reservation-note">
                  <strong>Catatan:</strong> <?= e($res['keterangan']) ?>
                </div>
              <?php endif; ?>

              <?php if ($res['status'] === 'rejected' && !empty($res['reject_reason'])): ?>
                <div class="reservation-reject">
                  <strong>Alasan Penolakan:</strong>
                  <div class="reject-text"><?= e($res['reject_reason']) ?></div>

                  <?php
                    $roomLink = base_path('/user/ruangan_list.php');
                    $admin_contact_q = mysqli_query($conn, "SELECT fullname, email FROM users WHERE role='admin' LIMIT 1");
                    $admin_contact = $admin_contact_q ? mysqli_fetch_assoc($admin_contact_q) : null;
                    $admin_email = $admin_contact['email'] ?? '';
                  ?>
                  <div style="margin-top:8px;font-size:13px;color:#7f1d1d">
                    <div>• Lihat daftar ruangan: <a href="<?= $roomLink ?>">Daftar Ruangan</a></div>
                    <?php if (!empty($admin_email) && filter_var($admin_email, FILTER_VALIDATE_EMAIL)): ?>
                      <div style="margin-top:6px">• Hubungi admin: <a href="mailto:<?= e($admin_email) ?>"><?= e($admin_email) ?></a></div>
                    <?php else: ?>
                      <div style="margin-top:6px">• Hubungi admin untuk informasi lebih lanjut.</div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>

              <div class="card-actions">
                <?php if ($res['status'] === 'pending'): ?>
                  <a href="<?= base_path('/user/reservasi_view.php?id=' . $res['id']) ?>" class="btn btn-secondary btn-small">
                    <span>👁️</span> Lihat Detail
                  </a>
                  <form method="POST" action="<?= base_path('/user/reservasi_delete.php') ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$res['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Batalkan reservasi ini?')">
                      <span>✕</span> Batalkan
                    </button>
                  </form>
                <?php else: ?>
                  <a href="<?= base_path('/user/reservasi_view.php?id=' . $res['id']) ?>" class="btn btn-secondary btn-small">
                    <span>👁️</span> Lihat Detail
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<style>
.history-section {
  background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
  min-height: 100vh;
  padding: 24px 0 40px 0;
}

.history-wrapper {
  max-width: 900px;
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

.history-header {
  margin-bottom: 32px;
}

.history-header h1 {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 700;
  color: #0f172a;
}

.history-header p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
}

.empty-state {
  background: #fff;
  border-radius: 14px;
  padding: 48px 24px;
  text-align: center;
  border: 2px dashed #e5e7eb;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
  display: block;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
  font-weight: 700;
  color: #0f172a;
}

.empty-state p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
}

.empty-link {
  color: #0070f3;
  text-decoration: none;
  font-weight: 600;
}

.empty-link:hover {
  text-decoration: underline;
}

.reservations-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.history-card {
  background: #fff;
  border-radius: 14px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: all 0.2s;
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.history-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
  border-color: #0070f3;
}

.history-card.past {
  opacity: 0.85;
}

.card-status {
  flex-shrink: 0;
  min-width: 120px;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
}

.card-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.content-top {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.room-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.room-name {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
}

.room-location {
  margin: 0;
  font-size: 13px;
  color: #64748b;
}

.reservation-meta {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.meta-item {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  font-size: 13px;
}

.meta-label {
  color: #94a3b8;
  font-weight: 500;
}

.meta-value {
  color: #0f172a;
  font-weight: 600;
}

.reservation-note {
  padding: 10px 12px;
  background: #f9fafb;
  border-radius: 8px;
  border-left: 3px solid #0070f3;
  font-size: 13px;
  color: #334155;
}

.reservation-note strong {
  color: #0f172a;
}

.card-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.reservation-reject {
  margin-top: 8px;
  padding: 12px;
  background: #fff5f5;
  border-left: 4px solid #ef4444;
  border-radius: 8px;
  color: #7f1d1d;
  font-size: 13px;
}

.reservation-reject .reject-text {
  margin-top: 6px;
  white-space: pre-wrap;
}

@media (max-width: 768px) {
  .history-wrapper {
    padding: 0 12px;
  }

  .content-top {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .reservation-meta {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
  }

  .meta-item {
    flex-direction: column;
    gap: 4px;
  }

  .card-actions {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }

  .history-card {
    flex-direction: column;
    gap: 12px;
  }

  .card-status {
    min-width: auto;
  }
}

@media (max-width: 480px) {
  .history-section {
    padding: 12px 0 32px 0;
  }

  .history-header h1 {
    font-size: 22px;
  }

  .history-card {
    padding: 16px;
  }

  .content-top {
    gap: 8px;
  }

  .reservation-meta {
    grid-template-columns: 1fr;
  }

  .room-name {
    font-size: 15px;
  }

  .reservation-note {
    font-size: 12px;
  }
}
</style>

<?php require __DIR__ . '/../templates/footer.php'; ?>


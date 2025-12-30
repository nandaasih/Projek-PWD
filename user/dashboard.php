<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = (int)$_SESSION['user_id'];

// Statistik Reservasi
$tot = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM reservasi WHERE user_id=$userId"))['c'];
$aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM reservasi WHERE user_id=$userId AND status='approved'"))['c'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM reservasi WHERE user_id=$userId AND status='pending'"))['c'];
$rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM reservasi WHERE user_id=$userId AND status='rejected'"))['c'];

// Today's reservations
$today = date('Y-m-d');
$today_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM reservasi WHERE user_id=$userId AND tanggal='$today'"))['c'];

// Active reservations (approved, tanggal >= hari ini)
$active_res_q = mysqli_query($conn, "SELECT r.id, r.tanggal, r.waktu_mulai, r.waktu_selesai, ru.nama FROM reservasi r LEFT JOIN ruangan ru ON r.ruangan_id = ru.id WHERE r.user_id=$userId AND r.status='approved' AND r.tanggal >= '$today' ORDER BY r.tanggal ASC, r.waktu_mulai ASC LIMIT 5");
$active_reservations = mysqli_fetch_all($active_res_q, MYSQLI_ASSOC);

// Latest Reservations
$latest_reservations = mysqli_query($conn, "SELECT r.id, r.tanggal, r.waktu_mulai, r.waktu_selesai, r.status, ru.nama FROM reservasi r LEFT JOIN ruangan ru ON r.ruangan_id = ru.id WHERE r.user_id=$userId ORDER BY r.created_at DESC LIMIT 20");
$latest = mysqli_fetch_all($latest_reservations, MYSQLI_ASSOC);

// User Info
$user_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT fullname, email FROM users WHERE id=$userId"));

// Profile picture
$profile_pic_url = '';
$check_pp = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
if (mysqli_num_rows($check_pp) > 0) {
  $pp_res = mysqli_query($conn, "SELECT profile_picture FROM users WHERE id=$userId");
  $pp_row = mysqli_fetch_assoc($pp_res);
  if (!empty($pp_row['profile_picture'])) {
    $profile_pic_url = base_path('/' . $pp_row['profile_picture']);
  }
}

// Mark notifications as read when opening the dashboard (automatic)
@mysqli_query($conn, "UPDATE notifikasi SET is_read=1 WHERE user_id=$userId AND is_read=0");

$title = "Dashboard User";
require __DIR__ . '/../templates/header.php';
?>

<section class="user-dashboard">
  <div class="dashboard-wrapper">
    <div class="dashboard-grid">
      <aside class="sidebar">
        <div class="brand">🏢 Reservasi — <span style="color:#0b63d7">Dashboard</span></div>
        <nav class="nav">
          <a href="<?= base_path('/user/dashboard.php') ?>" class="active"><span class="icon">🏠</span> Dashboard</a>
          <a href="<?= base_path('/user/reservasi_add.php') ?>"><span class="icon">📅</span> Buat Reservasi</a>
          <a href="<?= base_path('/user/ruangan_list.php') ?>"><span class="icon">🚪</span> Ruangan</a>
          <a href="<?= base_path('/user/reservasi_history.php') ?>"><span class="icon">📜</span> Riwayat</a>
          <a href="<?= base_path('/user/profil.php') ?>"><span class="icon">👤</span> Profil</a>
          <a href="<?= base_path('/actions/logout.php') ?>"><span class="icon">🔓</span> Logout</a>
        </nav>
      </aside>
      <div class="dashboard-main">
    
    <!-- Header Section with Avatar and Welcome -->
    <div class="dashboard-header">
      <div class="header-content">
        <div class="header-left">
          <div class="header-avatar">
            <?php if (!empty($profile_pic_url)): ?>
              <img src="<?= $profile_pic_url ?>" alt="<?= e($user_info['fullname'] ?? 'User') ?>">
            <?php else: ?>
              <div class="avatar-initial"><?= strtoupper(substr($user_info['fullname'] ?? 'U', 0, 1)) ?></div>
            <?php endif; ?>
          </div>
          <div class="header-info">
            <h1 class="header-name">Halo, <?= e($_SESSION['name'] ?? 'User') ?> 👋</h1>
            <p class="header-subtitle">Kelola dan pantau semua reservasi ruangan Anda</p>
          </div>
        </div>
        <div class="header-right">
          <a href="<?= base_path('/user/profil_view.php') ?>" class="header-link">Lihat Profil</a>
        </div>
      </div>
    </div>

    <!-- Hero / Booking CTA (inspired by MySantika style) -->
    <div class="user-hero">
      <div class="hero-inner">
        <div class="hero-left">
          <h2 class="hero-title">Pesan Ruangan dengan Cepat</h2>
          <p class="hero-sub">Cari dan pesan ruangan cocok untuk meeting, pelatihan, atau acara Anda. Sistem kami menampilkan ketersediaan real-time.</p>

          <div class="hero-cta">
            <a href="<?= base_path('/user/reservasi_add.php') ?>" class="btn btn-primary hero-btn">Buat Reservasi</a>
            <a href="<?= base_path('/user/cek_ketersediaan.php') ?>" class="btn hero-btn-outline">Cek Ketersediaan</a>
          </div>
        </div>

        <!-- hero-right removed: room search intentionally disabled -->
      </div>
    </div>

    <!-- Notification Section -->
    <div class="notification-section">
      <?php
        $unread_count = notif_unread_count($conn, $userId);
        $notifs = notif_fetch($conn, $userId, 6);
      ?>
      <div class="notif-header" style="display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:12px">
          <h3 style="margin:0">Notifikasi</h3>
          <?php if ($unread_count > 0): ?>
            <div class="notif-badge"><?= (int)$unread_count ?> belum dibaca</div>
          <?php endif; ?>
        </div>
        <div>
          <button class="btn small mark-all-notif" type="button">Tandai semua dibaca</button>
        </div>
      </div>

      <script>window.CSRF_TOKEN = '<?= e(generate_csrf_token()) ?>';</script>
      <?php if (count($notifs) > 0): ?>
        <div class="notif-list">
          <?php foreach ($notifs as $n): ?>
            <div class="notification-card <?= $n['is_read'] == 0 ? 'notification-unread' : 'notification-read' ?>">
              <div class="notif-icon"><?= $n['is_read'] == 0 ? '🔔' : '•' ?></div>
              <div class="notif-content">
                <strong><?= e($n['title']) ?></strong>
                <div class="notif-msg"><?= e($n['message']) ?></div>
                <small class="notif-time"><?= e($n['created_at']) ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="notification-card notification-success"><span class="notif-icon">✓</span><div class="notif-content"><strong>Semua Lancar</strong><br><small>Tidak ada notifikasi terbaru</small></div></div>
      <?php endif; ?>
    </div>

    <!-- Today's Summary -->
    <section class="today-summary">
      <h2 class="section-title">📊 Ringkasan Statistik</h2>
      <div class="summary-grid">
        <div class="summary-card card-today">
          <div class="summary-icon-wrapper">
            <div class="summary-icon">📋</div>
          </div>
          <div class="summary-info">
            <div class="summary-value"><?= (int)$tot ?></div>
            <div class="summary-label">Total Reservasi</div>
          </div>
        </div>
        <div class="summary-card card-approved">
          <div class="summary-icon-wrapper">
            <div class="summary-icon">✅</div>
          </div>
          <div class="summary-info">
            <div class="summary-value"><?= (int)$aktif ?></div>
            <div class="summary-label">Disetujui</div>
          </div>
        </div>
        <div class="summary-card card-pending">
          <div class="summary-icon-wrapper">
            <div class="summary-icon">⏳</div>
          </div>
          <div class="summary-info">
            <div class="summary-value"><?= (int)$pending ?></div>
            <div class="summary-label">Menunggu</div>
          </div>
        </div>
        <div class="summary-card card-rejected">
          <div class="summary-icon-wrapper">
            <div class="summary-icon">❌</div>
          </div>
          <div class="summary-info">
            <div class="summary-value"><?= (int)$rejected ?></div>
            <div class="summary-label">Ditolak</div>
          </div>
        </div>
        </div>
      </div>
    </div>
  </section>

    <!-- Quick Menu -->
    <section class="quick-menu">
      <h2 class="section-title">⚡ Menu Cepat</h2>
      <div class="quick-menu-grid">
        <a href="<?= base_path('/user/reservasi_add.php') ?>" class="quick-menu-item create">
          <div class="menu-icon">📅</div>
          <div class="menu-text">
            <strong>Buat Reservasi</strong>
            <small>Pesan ruangan baru</small>
          </div>
        </a>
        <a href="<?= base_path('/user/ruangan_list.php') ?>" class="quick-menu-item rooms">
          <div class="menu-icon">🚪</div>
          <div class="menu-text">
            <strong>Lihat Ruangan</strong>
            <small>Cek daftar semua ruangan</small>
          </div>
        </a>
        <a href="<?= base_path('/user/cek_ketersediaan.php') ?>" class="quick-menu-item availability">
          <div class="menu-icon">📊</div>
          <div class="menu-text">
            <strong>Cek Ketersediaan</strong>
            <small>Lihat slot yang tersedia</small>
          </div>
        </a>
        <a href="<?= base_path('/user/reservasi_history.php') ?>" class="quick-menu-item history">
          <div class="menu-icon">📜</div>
          <div class="menu-text">
            <strong>Riwayat Reservasi</strong>
            <small>Lihat semua reservasi Anda</small>
          </div>
        </a>
      </div>
    </section>

    <!-- Active Reservations -->
    <section class="active-reservations">
      <h2 class="section-title">🔴 Reservasi Aktif (<?= count($active_reservations) ?>)</h2>
      <?php if (count($active_reservations) > 0): ?>
        <div class="active-res-list">
          <?php foreach ($active_reservations as $res): ?>
            <div class="active-res-card">
              <div class="res-header">
                <h3 class="res-room"><?= e($res['nama'] ?? 'Ruangan') ?></h3>
                <span class="res-status">✅ Disetujui</span>
              </div>
              <div class="res-details">
                <p><strong>📅 Tanggal:</strong> <?= date('d M Y', strtotime($res['tanggal'])) ?></p>
                <p><strong>⏰ Waktu:</strong> <?= date('H:i', strtotime($res['waktu_mulai'])) ?> - <?= date('H:i', strtotime($res['waktu_selesai'])) ?></p>
              </div>
              <div class="res-actions">
                <a href="<?= base_path('/user/reservasi_view.php?id=') ?><?= (int)$res['id'] ?>" class="btn small">Lihat</a>
                <a href="<?= base_path('/user/reservasi_delete.php?id=') ?><?= (int)$res['id'] ?>" class="btn small danger" data-confirm="Batalkan reservasi ini?">Batalkan</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="no-active">
          <p>📭 Tidak ada reservasi aktif. <a href="<?= base_path('/user/reservasi_add.php') ?>">Buat reservasi baru</a></p>
        </div>
      <?php endif; ?>
    </section>

    <!-- Recent Reservations -->
    <section class="recent-reservations">
      <div class="section-header">
        <h2 class="section-title">⏰ Reservasi Terbaru</h2>
        <div class="section-actions">
          <div class="search-wrap">
            <input type="search" id="reservasiSearch" placeholder="Cari ruangan atau tanggal (mis. Meeting)" class="search-input">
            <button id="reservasiClear" class="search-clear" aria-label="Hapus pencarian">✕</button>
          </div>
          <a href="<?= base_path('/user/reservasi_history.php') ?>" class="view-all">Lihat Semua →</a>
        </div>
      </div>
      
      <?php if (count($latest) > 0): ?>
        <div class="reservations-list" id="reservationsList">
          <?php foreach ($latest as $res): ?>
            <div class="reservation-item" data-room="<?= e($res['nama']) ?>" data-date="<?= e($res['tanggal']) ?>">
              <div class="reservation-left">
                <div class="reservation-room">
                  <span class="room-icon">🚪</span>
                  <div class="room-info">
                    <h4 class="room-name"><?= e($res['nama'] ?? 'Ruangan') ?></h4>
                    <p class="room-date">
                      <?= date('d M Y', strtotime($res['tanggal'])) ?> 
                      | <?= date('H:i', strtotime($res['waktu_mulai'])) ?> - <?= date('H:i', strtotime($res['waktu_selesai'])) ?>
                    </p>
                  </div>
                </div>
              </div>
              <div class="reservation-right">
                <span class="status-badge status-badge-<?= $res['status'] ?>">
                  <?php 
                    if ($res['status'] === 'approved') echo '✅ Disetujui';
                    elseif ($res['status'] === 'pending') echo '⏳ Menunggu';
                    else echo '❌ Ditolak';
                  ?>
                </span>
                <div class="reservation-actions">
                  <a href="<?= base_path('/user/reservasi_view.php?id=') ?><?= (int)$res['id'] ?>" class="btn small">Lihat</a>
                  <a href="<?= base_path('/user/reservasi_delete.php?id=') ?><?= (int)$res['id'] ?>" class="btn small danger" data-confirm="Batalkan reservasi ini?">Batalkan</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div id="reservationsNoResults" class="no-results" style="display:none;margin-top:12px;padding:12px;border-radius:8px;background:#fff3cd;color:#856404;border:1px solid #ffe8a1">🔎 Tidak ada hasil pencarian.</div>
        <?php if ((int)$tot > 20): ?>
          <div class="load-more-wrap" style="margin-top:12px;text-align:center">
            <button id="loadMoreReservations" class="btn" data-url="<?= base_path('/user/reservasi_list_ajax.php') ?>" data-offset="20">Muat Lagi</button>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="no-data">
          <p>📭 Belum ada reservasi. Mulai dengan membuat reservasi baru.</p>
        </div>
      <?php endif; ?>
    </section>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../templates/footer.php'; ?>
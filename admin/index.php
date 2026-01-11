<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

// Fetch key statistics
// Safe count helper
function safe_count($conn, $sql) {
  $r = mysqli_query($conn, $sql);
  if (!$r) {
    error_log('DB error (count): ' . mysqli_error($conn) . ' -- SQL: ' . $sql);
    return 0;
  }
  $row = mysqli_fetch_assoc($r);
  return (int)($row['c'] ?? 0);
}

$total_users = safe_count($conn, "SELECT COUNT(*) c FROM users");
$total_rooms = safe_count($conn, "SELECT COUNT(*) c FROM ruangan");
$pending_reservations = safe_count($conn, "SELECT COUNT(*) c FROM reservasi WHERE status='pending'");
$approved_reservations = safe_count($conn, "SELECT COUNT(*) c FROM reservasi WHERE status='approved'");
$rejected_reservations = safe_count($conn, "SELECT COUNT(*) c FROM reservasi WHERE status='rejected'");
$total_reservations = safe_count($conn, "SELECT COUNT(*) c FROM reservasi");

// Fetch reservation data for the last 7 days
$weekly_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $count = safe_count($conn, "SELECT COUNT(*) c FROM reservasi WHERE DATE(created_at)='$date'");
    $weekly_data[date('D', strtotime($date))] = (int)$count;
}

// Fetch room utilization status
$room_status = [];
$rooms_result = mysqli_query($conn,"SELECT nama, COUNT(reservasi.id) as bookings FROM ruangan LEFT JOIN reservasi ON ruangan.id = reservasi.ruangan_id AND reservasi.status='approved' GROUP BY ruangan.id ORDER BY bookings DESC LIMIT 5");
if ($rooms_result === false) {
  error_log('DB error (rooms_result): ' . mysqli_error($conn));
} else {
  while ($row = mysqli_fetch_assoc($rooms_result)) {
    $room_status[] = $row;
  }
}

// Recent pending reservations for quick actions
$recent_pending_q = mysqli_query($conn, "SELECT r.id, r.tanggal, r.waktu_mulai, r.waktu_selesai, u.fullname, ru.nama AS ruangan FROM reservasi r JOIN users u ON u.id=r.user_id JOIN ruangan ru ON ru.id=r.ruangan_id WHERE r.status='pending' ORDER BY r.tanggal ASC, r.waktu_mulai ASC LIMIT 6");
$recent_pending = [];
if ($recent_pending_q === false) {
  error_log('DB error (recent_pending): ' . mysqli_error($conn));
} else {
  while ($row = mysqli_fetch_assoc($recent_pending_q)) $recent_pending[] = $row;
}

$title="Dashboard Admin";

// Mark admin notifications as read when opening admin dashboard (automatic)
$adminId = (int)($_SESSION['user_id'] ?? 0);
if ($adminId > 0) {
  @mysqli_query($conn, "UPDATE notifikasi SET is_read=1 WHERE user_id=$adminId AND is_read=0");
}

ob_start();
?>
?>

<section class="admin-dashboard">
  <div class="dashboard-wrapper">
    <div class="dashboard-grid">
      <div class="dashboard-main">
    
    <!-- Welcome Section -->
    <div style="margin-bottom: 24px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; color: white;">
      <h2 style="margin: 0 0 8px 0; font-size: 24px; font-weight: 700;">Selamat Datang di Admin Panel 👋</h2>
      <p style="margin: 0; font-size: 14px; color: rgba(255, 255, 255, 0.9);">Pantau dan kelola semua aspek sistem reservasi ruangan dengan mudah</p>
    </div>
    
    <!-- Admin Notification Section -->
    <div class="admin-notifications" style="margin-top:12px;margin-bottom:18px">
      <?php
        $adminId = (int)($_SESSION['user_id'] ?? 0);
        $admin_unread = notif_unread_count($conn, $adminId);
        $admin_notifs = notif_fetch($conn, $adminId, 6);
      ?>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
        <div style="display:flex;align-items:center;gap:12px">
          <h3 style="margin:0">Notifikasi Admin</h3>
          <?php if ($admin_unread > 0): ?>
            <div style="background:#ef4444;color:#fff;padding:6px 10px;border-radius:18px;font-weight:600"><?= (int)$admin_unread ?> belum dibaca</div>
          <?php endif; ?>
        </div>
        <div>
          <button class="btn small mark-all-notif" type="button">Tandai semua dibaca</button>
        </div>
      </div>
      <script>window.CSRF_TOKEN = '<?= e(generate_csrf_token()) ?>';</script>
      <?php if (count($admin_notifs) > 0): ?>
        <div style="display:flex;flex-direction:column;gap:8px">
          <?php foreach ($admin_notifs as $an): ?>
            <div style="padding:10px;border-radius:8px;background:#fff;border:1px solid #e5e7eb">
              <strong><?= e($an['title']) ?></strong>
              <div style="font-size:13px;color:#374151"><?= e($an['message']) ?></div>
              <small style="color:#6b7280"><?= e($an['created_at']) ?></small>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div style="padding:12px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;color:#64748b">Tidak ada notifikasi.</div>
      <?php endif; ?>
    </div>

    <!-- Key Metrics -->
    <div class="metrics-grid">
      <div class="metric-card metric-users">
        <div class="metric-icon">👥</div>
        <div class="metric-content">
          <div class="metric-value"><a href="<?= base_path('/admin/user_stats.php') ?>"><?php echo (int)$total_users ?></a></div>
          <div class="metric-label">Total Pengguna</div>
        </div>
      </div>

      <div class="metric-card metric-rooms">
        <div class="metric-icon">🏢</div>
        <div class="metric-content">
          <div class="metric-value"><?= (int)$total_rooms ?></div>
          <div class="metric-label">Total Ruangan</div>
        </div>
      </div>

      <div class="metric-card metric-reservations">
        <div class="metric-icon">📅</div>
        <div class="metric-content">
          <div class="metric-value"><?= (int)$total_reservations ?></div>
          <div class="metric-label">Total Reservasi</div>
        </div>
      </div>

      <div class="metric-card metric-pending">
        <div class="metric-icon">⏳</div>
        <div class="metric-content">
          <div class="metric-value"><?= (int)$pending_reservations ?></div>
          <div class="metric-label">Menunggu Persetujuan</div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
      <!-- Weekly Reservations Chart -->
      <div class="chart-card">
        <h3 class="chart-title">Reservasi 7 Hari Terakhir</h3>
        <div class="chart-container">
          <canvas id="weeklyChart"></canvas>
        </div>
      </div>

      <!-- Reservation Status Distribution -->
      <div class="chart-card">
        <h3 class="chart-title">Status Reservasi</h3>
        <div class="chart-container">
          <canvas id="statusChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Recent Pending Reservations -->
    <div class="recent-pending">
      <h3 class="section-heading">🕒 Reservasi Menunggu (Cepat Tanggapi)</h3>
      <div class="pending-list">
        <?php if (count($recent_pending) === 0): ?>
          <div style="padding:18px;background:#fff;border-radius:10px;border:1px solid #e5e7eb;color:#64748b">Tidak ada reservasi menunggu.</div>
        <?php else: ?>
          <?php foreach ($recent_pending as $p): ?>
            <div class="pending-item">
              <div class="pending-left">
                <div class="pending-room">🚪 <?= e($p['ruangan']) ?></div>
                <div class="pending-meta"><?= date('d M Y', strtotime($p['tanggal'])) ?> • <?= e($p['waktu_mulai']) ?> - <?= e($p['waktu_selesai']) ?></div>
                <div class="pending-user">👤 <?= e($p['fullname']) ?></div>
              </div>
              <div class="pending-actions">
                <a href="<?= base_path('/admin/approve.php?id='.$p['id']) ?>" class="btn btn-ok">Approve</a>
                <a href="<?= base_path('/admin/reject.php?id='.$p['id']) ?>" class="btn btn-danger">Reject</a>
                <a href="<?= base_path('/admin/reservasi_view.php?id='.$p['id']) ?>" class="btn btn-secondary">Lihat</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Status Overview -->
    <div class="status-overview">
      <h3 class="section-heading">📊 Ringkasan Status Reservasi</h3>
      <div class="status-cards">
        <div class="status-card approved">
          <div class="status-number"><?= (int)$approved_reservations ?></div>
          <div class="status-name">Disetujui</div>
        </div>
        <div class="status-card pending">
          <div class="status-number"><?= (int)$pending_reservations ?></div>
          <div class="status-name">Menunggu</div>
        </div>
        <div class="status-card rejected">
          <div class="status-number"><?= (int)$rejected_reservations ?></div>
          <div class="status-name">Ditolak</div>
        </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Weekly Chart
const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
new Chart(weeklyCtx, {
  type: 'line',
  data: {
    labels: <?= json_encode(array_keys($weekly_data)) ?>,
    datasets: [{
      label: 'Reservasi',
      data: <?= json_encode(array_values($weekly_data)) ?>,
      borderColor: '#0070f3',
      backgroundColor: 'rgba(0, 112, 243, 0.1)',
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#0070f3',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 5,
      pointHoverRadius: 7
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: {
        display: true,
        labels: { color: '#64748b', font: { size: 12 } }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: { color: 'rgba(0,0,0,0.05)' },
        ticks: { color: '#64748b' }
      },
      x: {
        grid: { display: false },
        ticks: { color: '#64748b' }
      }
    }
  }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
  type: 'doughnut',
  data: {
    labels: ['Disetujui', 'Menunggu', 'Ditolak'],
    datasets: [{
      data: [<?= (int)$approved_reservations ?>, <?= (int)$pending_reservations ?>, <?= (int)$rejected_reservations ?>],
      backgroundColor: [
        '#10b981',
        '#f59e0b',
        '#ef4444'
      ],
      borderColor: '#fff',
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: {
        position: 'bottom',
        labels: { color: '#64748b', font: { size: 12 }, padding: 15 }
      }
    }
  }
});
</script>

<?php 
$page_content = ob_get_clean();
require __DIR__ . '/../templates/layout-admin.php';
?>

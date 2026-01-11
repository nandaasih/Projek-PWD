<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

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
$admin_count = safe_count($conn, "SELECT COUNT(*) c FROM users WHERE role='admin'");
$user_count = safe_count($conn, "SELECT COUNT(*) c FROM users WHERE role='user'");

$daily = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $c = safe_count($conn, "SELECT COUNT(*) c FROM users WHERE DATE(created_at)='$d'");
    $daily[] = ['date' => $d, 'count' => $c];
}

$labels = array_map(function($it){ return date('d M', strtotime($it['date'])); }, $daily);
$counts = array_map(function($it){ return (int)$it['count']; }, $daily);

$title = 'Statistik Pengguna';
ob_start();
?>

<section class="admin-section">
  <div class="section-header">
    <h1 class="page-title">📊 Statistik Pengguna</h1>
    <p class="page-subtitle">Ringkasan jumlah pengguna dan pendaftaran 7 hari terakhir</p>
  </div>

  <div class="stats-grid" style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:18px">
    <div class="stat-card" style="flex:1;min-width:180px;padding:12px;background:#fff;border-radius:8px;border:1px solid #e5e7eb">
      <div style="font-size:20px;font-weight:700"><?= (int)$total_users ?></div>
      <div style="color:#6b7280">Total Pengguna</div>
    </div>
    <div class="stat-card" style="flex:1;min-width:180px;padding:12px;background:#fff;border-radius:8px;border:1px solid #e5e7eb">
      <div style="font-size:20px;font-weight:700"><?= (int)$admin_count ?></div>
      <div style="color:#6b7280">Admin</div>
    </div>
    <div class="stat-card" style="flex:1;min-width:180px;padding:12px;background:#fff;border-radius:8px;border:1px solid #e5e7eb">
      <div style="font-size:20px;font-weight:700"><?= (int)$user_count ?></div>
      <div style="color:#6b7280">User Regular</div>
    </div>
  </div>

  <div class="chart-card" style="background:#fff;padding:18px;border-radius:8px;border:1px solid #e5e7eb">
    <h3>Registrasi 7 Hari Terakhir</h3>
    <canvas id="usersChart" style="max-width:100%;height:220px"></canvas>
  </div>

</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('usersChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($labels) ?>,
    datasets: [{
      label: 'Pendaftaran',
      data: <?= json_encode($counts) ?>,
      backgroundColor: '#3b82f6',
      borderRadius: 6,
    }]
  },
  options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
});
</script>

<?php 
$page_content = ob_get_clean();
require __DIR__ . '/../templates/layout-admin.php';
?>

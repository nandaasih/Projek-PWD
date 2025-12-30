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
require __DIR__ . '/../templates/header.php';
?>
<h2 class="h">Kelola Reservasi</h2>

<div class="admin-reservations">
  <?php while($r=mysqli_fetch_assoc($list)): 
    $status = $r['status'];
    $badge = '';
    if ($status === 'pending') $badge = '<span class="status-pill pending">⏳ Menunggu</span>';
    elseif ($status === 'approved') $badge = '<span class="status-pill approved">✅ Disetujui</span>';
    elseif ($status === 'rejected') $badge = '<span class="status-pill rejected">❌ Ditolak</span>';
    else $badge = '<span class="status-pill">'.e(ucfirst($status)).'</span>';
  ?>
    <div class="admin-res-card">
      <div class="card-left">
        <div class="card-user">👤 <?= e($r['fullname']) ?></div>
        <div class="card-room">🚪 <?= e($r['ruangan']) ?></div>
        <div class="card-datetime">📅 <?= date('d M Y', strtotime($r['tanggal'])) ?> • ⏰ <?= e($r['waktu_mulai']) ?> - <?= e($r['waktu_selesai']) ?></div>
      </div>
      <div class="card-right">
        <div class="card-status"><?= $badge ?></div>
        <div class="admin-res-actions">
          <?php if ($r['status'] === 'pending'): ?>
            <a class="btn ok" href="<?= base_path('/admin/approve.php?id='.$r['id']) ?>">Approve</a>
            <a class="btn danger" href="<?= base_path('/admin/reject.php?id='.$r['id']) ?>">Reject</a>
          <?php else: ?>
            <a class="btn" href="<?= base_path('/admin/reservasi_view.php?id='.$r['id']) ?>">Lihat</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>

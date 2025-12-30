<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . base_path('/admin/reservasi_list.php'));
    exit;
}

// Fetch reservation with related user and room info
$sql = "SELECT r.*, u.fullname AS pemesan, u.email AS pemesan_email, ru.nama as ruangan_nama, ru.lokasi as ruangan_lokasi FROM reservasi r LEFT JOIN users u ON r.user_id = u.id LEFT JOIN ruangan ru ON r.ruangan_id = ru.id WHERE r.id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$res = mysqli_fetch_assoc($result);

if (!$res) {
    header('Location: ' . base_path('/admin/reservasi_list.php'));
    exit;
}

$title = "Detail Reservasi (Admin)";
require __DIR__ . '/../templates/header.php';
?>

<section class="container" style="margin-top:24px;max-width:920px">
  <h1 style="margin-bottom:12px">📋 Detail Reservasi (Admin)</h1>

  <div class="reservation-detail-card" style="background:var(--card-bg);padding:20px;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 6px 20px rgba(15,23,42,0.04)">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
      <div style="flex:1;min-width:240px">
        <h2 style="margin:0 0 6px;font-size:20px;">🚪 <?= e($res['ruangan_nama'] ?? 'Ruangan') ?></h2>
        <div style="color:var(--muted);font-size:13px;">Lokasi: <?= e($res['ruangan_lokasi'] ?? '-') ?></div>
        <div style="margin-top:10px;color:#334155">
          <div>📅 <strong><?= date('d M Y', strtotime($res['tanggal'])) ?></strong></div>
          <div>⏰ <strong><?= date('H:i', strtotime($res['waktu_mulai'])) ?> - <?= date('H:i', strtotime($res['waktu_selesai'])) ?></strong></div>
          <div>👥 <strong><?= (int)($res['jumlah_peserta'] ?? 0) ?></strong> peserta</div>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
        <?php
          $st = $res['status'];
          if ($st === 'approved') echo '<div class="status-badge status-badge-approved">✅ Disetujui</div>';
          elseif ($st === 'pending') echo '<div class="status-badge status-badge-pending">⏳ Menunggu</div>';
          elseif ($st === 'rejected') echo '<div class="status-badge status-badge-rejected">❌ Ditolak</div>';
          else echo '<div class="status-badge">' . e(ucfirst($st)) . '</div>';
        ?>
        <div style="display:flex;gap:8px">
          <?php if ($res['status'] === 'pending'): ?>
            <form method="POST" action="<?= base_path('/admin/approve.php') ?>" style="display:inline;">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$res['id'] ?>">
              <button type="submit" class="btn btn-primary">✅ Approve</button>
            </form>
            <a href="<?= base_path('/admin/reject.php?id=') ?><?= (int)$res['id'] ?>" class="btn btn-danger">❌ Reject</a>
          <?php else: ?>
            <a href="<?= base_path('/admin/reservasi_list.php') ?>" class="btn btn-secondary">← Kembali</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div style="margin-top:16px;padding:12px;background:#fbfdff;border-left:4px solid var(--primary);border-radius:8px;color:#334155">
      <strong>Catatan Pemesan:</strong>
      <div style="margin-top:8px;white-space:pre-wrap"><?= e($res['catatan'] ?? '') ?></div>
      <div style="margin-top:8px;color:#475569;font-size:13px">Pemesan: <?= e($res['pemesan']) ?> &middot; <?= e($res['pemesan_email']) ?></div>
    </div>

    <?php if ($res['status'] === 'rejected' && !empty($res['reject_reason'])): ?>
      <div style="margin-top:12px;padding:12px;background:#fff5f5;border-left:4px solid #ef4444;border-radius:8px;color:#7f1d1d">
        <strong>Alasan Penolakan:</strong>
        <div style="margin-top:8px;white-space:pre-wrap"><?= e($res['reject_reason']) ?></div>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/../templates/footer.php'; ?>

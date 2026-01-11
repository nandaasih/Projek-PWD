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
ob_start();
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
            <button onclick="confirmApprove(<?= (int)$res['id'] ?>)" style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">✅ Approve</button>
            <a href="<?= base_path('/admin/reject.php?id=') ?><?= (int)$res['id'] ?>" onclick="return confirm('Yakin ingin menolak reservasi ini?')" style="padding: 10px 20px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; text-decoration: none; display: inline-block;">❌ Reject</a>
          <?php else: ?>
            <a href="<?= base_path('/admin/reservasi_list.php') ?>" style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; text-decoration: none; display: inline-block;">← Kembali</a>
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

<?php 
$page_content = ob_get_clean();
require __DIR__ . '/../templates/layout-admin.php';
?>

<script>
function confirmApprove(id) {
  if (confirm('Yakin ingin menyetujui reservasi ini?')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_path('/admin/approve.php') ?>';
    
    const tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = 'csrf_token';
    tokenInput.value = window.CSRF_TOKEN || '';
    
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = id;
    
    form.appendChild(tokenInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
  }
}
</script>

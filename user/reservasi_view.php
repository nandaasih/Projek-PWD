<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . base_path('/user/reservasi_history.php'));
    exit;
}

// Fetch reservation with room info
$sql = "SELECT r.*, ru.nama as ruangan_nama FROM reservasi r LEFT JOIN ruangan ru ON r.ruangan_id = ru.id WHERE r.id = ? AND r.user_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$res = mysqli_fetch_assoc($result);

if (!$res) {
    // Not found or not permitted
    header('Location: ' . base_path('/user/reservasi_history.php'));
    exit;
}

$title = "Detail Reservasi";
require __DIR__ . '/../templates/header.php';
?>

<section class="container" style="margin-top:24px;max-width:920px">
  <h1 style="margin-bottom:12px">📋 Detail Reservasi</h1>

  <div class="reservation-detail-card" style="background:var(--card-bg);padding:20px;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 6px 20px rgba(15,23,42,0.04)">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
      <div style="flex:1;min-width:240px">
        <h2 style="margin:0 0 6px;font-size:20px;">🚪 <?= e($res['ruangan_nama'] ?? 'Ruangan') ?></h2>
        <div style="color:var(--muted);font-size:13px;">Lokasi: <?= e($res['lokasi'] ?? '-') ?></div>
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
            <a href="<?= base_path('/user/reservasi_add.php?id=') ?><?= (int)$res['id'] ?>" class="btn btn-primary">✏️ Edit</a>
            <a href="<?= base_path('/user/reservasi_delete.php?id=') ?><?= (int)$res['id'] ?>" class="btn btn-danger" data-confirm="Batalkan reservasi ini?">✖ Batalkan</a>
          <?php else: ?>
            <a href="<?= base_path('/user/reservasi_history.php') ?>" class="btn btn-secondary">← Kembali</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (!empty($res['catatan'])): ?>
      <div style="margin-top:16px;padding:12px;background:#fbfdff;border-left:4px solid var(--primary);border-radius:8px;color:#334155">
        <strong>Catatan Pemesan:</strong>
        <div style="margin-top:8px;white-space:pre-wrap"><?= e($res['catatan']) ?></div>
      </div>
    <?php endif; ?>

      <?php if ($res['status'] === 'rejected' && !empty($res['reject_reason'])): ?>
        <div style="margin-top:12px;padding:12px;background:#fff5f5;border-left:4px solid #ef4444;border-radius:8px;color:#7f1d1d">
          <strong>Alasan Penolakan:</strong>
          <div style="margin-top:8px;white-space:pre-wrap"><?= e($res['reject_reason']) ?></div>

          <?php
            // provide link to room list (no detailed page available) and admin contact
            $roomName = urlencode($res['ruangan_nama'] ?? '');
            $roomLink = base_path('/user/ruangan_list.php');
            // find first admin contact
            $admin_contact = mysqli_fetch_assoc(mysqli_query($conn, "SELECT fullname, email FROM users WHERE role='admin' LIMIT 1"));
            $admin_email = $admin_contact['email'] ?? '';
          ?>

          <div style="margin-top:10px;font-size:13px;color:#7f1d1d">
            <div>• Lihat daftar ruangan: <a href="<?= $roomLink ?>">Daftar Ruangan</a></div>
            <?php if (!empty($admin_email) && filter_var($admin_email, FILTER_VALIDATE_EMAIL)): ?>
              <div style="margin-top:6px">• Hubungi admin: <a href="mailto:<?= e($admin_email) ?>"><?= e($admin_email) ?></a></div>
            <?php else: ?>
              <div style="margin-top:6px">• Hubungi admin untuk informasi lebih lanjut.</div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/../templates/footer.php'; ?>

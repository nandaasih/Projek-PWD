<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = (int)$_SESSION['user_id'];

// Fetch all reservations for user with filters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$where = "r.user_id=$userId";
if ($filter !== 'all') {
    $where .= " AND r.status='" . mysqli_real_escape_string($conn, $filter) . "'";
}
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $where .= " AND (ru.nama LIKE '%$search_escaped%' OR r.tanggal LIKE '%$search_escaped%')";
}

$list = mysqli_query($conn,
   "SELECT r.*, ru.nama AS ruangan
    FROM reservasi r
    LEFT JOIN ruangan ru ON ru.id=r.ruangan_id
    WHERE $where
    ORDER BY r.tanggal DESC, r.waktu_mulai DESC"
 );

if ($list === false) {
    error_log('DB error (reservasi_list): ' . mysqli_error($conn));
    $list = [];
}

$title = "Reservasi Saya";

// Start output buffering to capture HTML content
ob_start();
?>

<div class="user-reservations-wrapper">
    <div class="reservasi-header">
        <div>
            <h2 class="section-title">📋 Reservasi Saya</h2>
        </div>
        <a href="<?= base_path('/user/reservasi_add.php') ?>" class="btn btn-primary">+ Buat Reservasi Baru</a>
    </div>

    <!-- Filter & Search -->
    <div class="reservasi-filters" style="margin-bottom: 20px; background: white; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb;">
        <div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
            <a href="<?= base_path('/user/reservasi_list.php?filter=all') ?>" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>" style="padding: 8px 16px;">Semua</a>
            <a href="<?= base_path('/user/reservasi_list.php?filter=pending') ?>" class="btn btn-sm <?= $filter === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>" style="padding: 8px 16px;">⏳ Menunggu</a>
            <a href="<?= base_path('/user/reservasi_list.php?filter=approved') ?>" class="btn btn-sm <?= $filter === 'approved' ? 'btn-success' : 'btn-outline-success' ?>" style="padding: 8px 16px;">✅ Disetujui</a>
            <a href="<?= base_path('/user/reservasi_list.php?filter=rejected') ?>" class="btn btn-sm <?= $filter === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>" style="padding: 8px 16px;">❌ Ditolak</a>
        </div>

        <form method="GET" style="display: flex; gap: 8px; flex-wrap: wrap;">
            <input type="text" name="search" placeholder="Cari ruangan atau tanggal..." value="<?= e($search) ?>" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; flex: 1; min-width: 200px;">
            <button type="submit" class="btn btn-sm btn-info" style="padding: 8px 16px;">🔍 Cari</button>
            <a href="<?= base_path('/user/reservasi_list.php') ?>" class="btn btn-sm btn-secondary" style="padding: 8px 16px;">Reset</a>
        </form>
    </div>

    <!-- Reservations List -->
    <div class="user-reservations-list">
        <?php 
        $count = 0;
        while($r = mysqli_fetch_assoc($list)): 
            $count++;
            $status = $r['status'];
            $statusClass = '';
            $statusText = '';
            
            if ($status === 'pending') {
                $statusClass = 'status-pending';
                $statusText = '⏳ Menunggu Persetujuan';
            } elseif ($status === 'approved') {
                $statusClass = 'status-approved';
                $statusText = '✅ Disetujui';
            } elseif ($status === 'rejected') {
                $statusClass = 'status-rejected';
                $statusText = '❌ Ditolak';
            } else {
                $statusClass = 'status-default';
                $statusText = ucfirst($status);
            }
        ?>
            <div class="reservation-item" style="padding: 16px; background: white; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 12px; display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: start;">
                <div class="reservation-details">
                    <div style="font-weight: 600; font-size: 16px; margin-bottom: 8px;">
                        🏛️ <?= e($r['ruangan'] ?? 'N/A') ?>
                    </div>
                    <div style="display: flex; gap: 16px; color: #6b7280; font-size: 14px; margin-bottom: 12px;">
                        <span>📅 <?= date('d M Y', strtotime($r['tanggal'])) ?></span>
                        <span>⏰ <?= e($r['waktu_mulai']) ?> - <?= e($r['waktu_selesai']) ?></span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 8px; align-items: center; white-space: nowrap;">
                    <span class="badge <?= $statusClass ?>" style="padding: 6px 12px; border-radius: 20px; font-weight: 600;">
                        <?= $statusText ?>
                    </span>
                    
                    <div style="display: flex; gap: 6px;">
                        <a href="<?= base_path('/user/reservasi_view.php?id=') . (int)$r['id'] ?>" class="btn btn-sm btn-info" style="padding: 6px 12px; font-size: 13px;">👁️ Lihat</a>
                        <?php if ($status === 'approved'): ?>
                            <a href="<?= base_path('/user/reservasi_delete.php?id=') . (int)$r['id'] ?>" class="btn btn-sm btn-danger" style="padding: 6px 12px; font-size: 13px;" onclick="return confirm('Batalkan reservasi ini?');">🗑️ Batalkan</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <?php if ($count === 0): ?>
            <div style="text-align: center; padding: 40px 20px; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
                <p style="font-size: 18px; color: #6b7280; margin: 0;">📭 Tidak ada reservasi</p>
                <p style="font-size: 14px; color: #9ca3af; margin-top: 8px;">Mulai dengan membuat reservasi ruangan baru</p>
                <a href="<?= base_path('/user/reservasi_add.php') ?>" class="btn btn-primary" style="margin-top: 12px; padding: 10px 20px;">+ Buat Reservasi</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Capture the HTML content
$page_content = ob_get_clean();

// Load the user layout template with admin style
require __DIR__ . '/../templates/layout-user-admin.php';
?>

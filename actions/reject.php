<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf_token($_POST['csrf_token'] ?? '');

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');
if ($id <= 0) redirect('/admin/reservasi_list.php');

// Ensure column exists
$col = mysqli_query($conn, "SHOW COLUMNS FROM reservasi LIKE 'reject_reason'");
if (!$col || mysqli_num_rows($col) === 0) {
	@mysqli_query($conn, "ALTER TABLE reservasi ADD COLUMN reject_reason TEXT NULL");
}

// Update status and reason
$stmt = mysqli_prepare($conn, "UPDATE reservasi SET status = 'rejected', reject_reason = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $reason, $id);
mysqli_stmt_execute($stmt);

// Log action with reason
$detail = $reason ?: '';
$audit = mysqli_prepare($conn, "INSERT INTO audit_log (admin_id, action, reservasi_id, detail) VALUES (?, 'reject', ?, ?)");
mysqli_stmt_bind_param($audit, "iis", $_SESSION['user_id'], $id, $detail);
@mysqli_stmt_execute($audit);

// Send email notification to user (if email available)
$q = mysqli_prepare($conn, "SELECT u.id AS user_id, u.email, u.fullname, ru.nama as ruangan, r.tanggal FROM reservasi r JOIN users u ON u.id=r.user_id JOIN ruangan ru ON ru.id=r.ruangan_id WHERE r.id = ? LIMIT 1");
if ($q) {
	mysqli_stmt_bind_param($q, "i", $id);
	mysqli_stmt_execute($q);
	$res = mysqli_stmt_get_result($q);
	if ($res && $row = mysqli_fetch_assoc($res)) {
		$to = $row['email'];
		$userName = $row['fullname'];
		$room = $row['ruangan'];
		$date = $row['tanggal'];
		if (!empty($to) && filter_var($to, FILTER_VALIDATE_EMAIL)) {
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
			$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
			$viewUrl = $protocol . '://' . $host . base_path('/user/reservasi_view.php?id=' . $id);
				$roomListUrl = $protocol . '://' . $host . base_path('/user/ruangan_list.php');
				// find admin contact if available
				$admin_contact_q = mysqli_query($conn, "SELECT fullname, email FROM users WHERE role='admin' LIMIT 1");
				$admin_contact = $admin_contact_q ? mysqli_fetch_assoc($admin_contact_q) : null;
				$admin_email = $admin_contact['email'] ?? '';
			$subject = "Reservasi Anda ditolak - " . $room . " (" . date('d M Y', strtotime($date)) . ")";
			$body = "Halo " . $userName . ",\n\n";
			$body .= "Reservasi Anda untuk ruangan '" . $room . "' pada " . date('d M Y', strtotime($date)) . " telah ditolak oleh admin." . "\n\n";
			if (!empty($reason)) {
				$body .= "Alasan penolakan:\n" . $reason . "\n\n";
			}
				$body .= "Anda dapat melihat detail reservasi di: " . $viewUrl . "\n\n";
				$body .= "Lihat daftar ruangan: " . $roomListUrl . "\n\n";
				if (!empty($admin_email) && filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
					$body .= "Kontak admin: " . $admin_email . "\n\n";
				} else {
					$body .= "Jika Anda memerlukan bantuan lebih lanjut, silakan hubungi administrator." . "\n\n";
				}
				$body .= "Salam,\nTim Reservasi";

			require_once __DIR__ . '/../includes/mailer.php';
			send_mail($to, $subject, $body, false);
		}
	}
}

	// Insert notification for the user
	if (!empty($row['user_id'] ?? null)) {
		$notif_ins = mysqli_prepare($conn, "INSERT INTO notifikasi (user_id, title, message) VALUES (?, ?, ?)");
		if ($notif_ins) {
			$uid = (int)$row['user_id'];
			$title = 'Reservasi Ditolak';
			$msg = 'Reservasi Anda untuk ' . ($row['ruangan'] ?? '') . ' pada ' . date('d M Y', strtotime($row['tanggal'] ?? '')) . ' telah ditolak.';
			if (!empty($reason)) $msg .= ' Alasan: ' . $reason;
			mysqli_stmt_bind_param($notif_ins, "iss", $uid, $title, $msg);
			mysqli_stmt_execute($notif_ins);
			mysqli_stmt_close($notif_ins);
		}
	}

redirect('/admin/reservasi_list.php');

<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

// Ambil parameter
$ruangan_id = isset($_GET['ruangan_id']) ? (int)$_GET['ruangan_id'] : 0;
$tanggal = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';

// Validasi
if (!$ruangan_id || !$tanggal) {
    die(json_encode(['error' => 'Parameter tidak lengkap']));
}

// Validasi format tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    die(json_encode(['error' => 'Format tanggal tidak valid']));
}

// Jangan biarkan tanggal di masa lalu
if ($tanggal < date('Y-m-d')) {
    die(json_encode(['error' => 'Tanggal tidak boleh di masa lalu']));
}

// Ambil semua reservasi untuk ruangan dan tanggal tersebut (approved & pending)
$query = "SELECT waktu_mulai, waktu_selesai FROM reservasi 
          WHERE ruangan_id = $ruangan_id 
          AND tanggal = '$tanggal' 
          AND status IN ('approved', 'pending')
          ORDER BY waktu_mulai ASC";

$result = mysqli_query($conn, $query);
if (!$result) {
    die(json_encode(['error' => 'Database error: ' . mysqli_error($conn)]));
}

$booked_times = [];
while ($row = mysqli_fetch_assoc($result)) {
    $booked_times[] = [
        'start' => $row['waktu_mulai'],
        'end' => $row['waktu_selesai']
    ];
}

// Generate jam kerja (7:00 - 19:00, step 1 jam)
$available_hours = [];
$blocked_hours = [];

for ($hour = 7; $hour < 19; $hour++) {
    $hour_str = sprintf("%02d:00", $hour);
    $hour_time = strtotime($hour_str);
    
    $is_blocked = false;
    
    // Cek apakah jam ini bentrok dengan reservasi yang ada
    foreach ($booked_times as $booking) {
        $booking_start = strtotime($booking['start']);
        $booking_end = strtotime($booking['end']);
        
        if ($hour_time >= $booking_start && $hour_time < $booking_end) {
            $is_blocked = true;
            break;
        }
    }
    
    if ($is_blocked) {
        $blocked_hours[] = $hour_str;
    } else {
        $available_hours[] = $hour_str;
    }
}

echo json_encode([
    'success' => true,
    'booked_times' => $booked_times,
    'available_hours' => $available_hours,
    'blocked_hours' => $blocked_hours,
    'total_available' => count($available_hours),
    'total_blocked' => count($blocked_hours)
]);
?>

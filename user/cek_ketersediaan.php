<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = (int)$_SESSION['user_id'];

// Ambil parameter tanggal dari URL atau form
$selected_date = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : date('Y-m-d');

// Validasi format tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    $selected_date = date('Y-m-d');
}

// Pastikan tanggal tidak di masa lalu
if ($selected_date < date('Y-m-d')) {
    $selected_date = date('Y-m-d');
}

$title = "Cek Ketersediaan Ruangan";
require __DIR__ . '/../templates/header.php';

// Ambil semua ruangan aktif
$ruangan_query = mysqli_query($conn, "SELECT id, nama, lokasi, kapasitas, fasilitas FROM ruangan WHERE status='aktif' ORDER BY nama");
$ruangan_list = mysqli_fetch_all($ruangan_query, MYSQLI_ASSOC);
?>

<section class="ketersediaan-section">
  <div class="ketersediaan-wrapper">
    
    <!-- Back Button -->
    <a href="<?= base_path('/user/dashboard.php') ?>" class="back-link">
      <span>← Kembali ke Dashboard</span>
    </a>

    <!-- Page Header -->
    <div class="ketersediaan-header">
      <h1>📅 Cek Ketersediaan Ruangan</h1>
      <p>Lihat slot ruangan yang tersedia untuk tanggal pilihan Anda</p>
    </div>

    <!-- Date Picker -->
    <div class="date-picker-container">
      <form method="GET" class="date-form">
        <div class="form-group">
          <label for="tanggal">Pilih Tanggal:</label>
          <input 
            type="date" 
            id="tanggal" 
            name="tanggal" 
            value="<?= $selected_date ?>"
            min="<?= date('Y-m-d') ?>"
            onchange="this.form.submit()"
            class="date-input"
          >
        </div>
      </form>
    </div>

    <!-- Date Display -->
    <div class="selected-date-info">
      <p>Menampilkan ketersediaan untuk: <strong><?= date('d F Y', strtotime($selected_date)) ?></strong></p>
    </div>

    <!-- Ketersediaan Grid -->
    <div class="ketersediaan-grid">
      <?php if (empty($ruangan_list)): ?>
        <div class="no-data">
          <p>📭 Tidak ada ruangan tersedia</p>
        </div>
      <?php else: ?>
        <?php foreach ($ruangan_list as $room): ?>
          <?php
            // Hitung slot yang terpakai pada tanggal terpilih
            $room_id = (int)$room['id'];
            $booked_slots = mysqli_fetch_all(
              mysqli_query($conn, 
                "SELECT waktu_mulai, waktu_selesai FROM reservasi 
                WHERE ruangan_id=$room_id AND tanggal='$selected_date' AND status IN ('approved', 'pending')
                ORDER BY waktu_mulai ASC"
              ),
              MYSQLI_ASSOC
            );

            // Generate jam kerja (8:00 - 18:00)
            $work_hours = [];
            for ($hour = 8; $hour < 18; $hour++) {
              $work_hours[] = sprintf("%02d:00", $hour);
            }

            // Tentukan slot tersedia dan terpakai
            $available_slots = [];
            $occupied_slots = [];

            foreach ($work_hours as $time) {
              $is_booked = false;
              foreach ($booked_slots as $booking) {
                $start = strtotime($booking['waktu_mulai']);
                $end = strtotime($booking['waktu_selesai']);
                $current = strtotime($time);
                
                if ($current >= $start && $current < $end) {
                  $is_booked = true;
                  break;
                }
              }
              
              if ($is_booked) {
                $occupied_slots[] = $time;
              } else {
                $available_slots[] = $time;
              }
            }

            $availability_percent = count($available_slots) > 0 ? (count($available_slots) / count($work_hours)) * 100 : 0;
          ?>
          
          <div class="ketersediaan-card">
            <div class="card-header">
              <div class="room-info">
                <h3 class="room-name"><?= e($room['nama']) ?></h3>
                <p class="room-location">📍 <?= e($room['lokasi']) ?></p>
              </div>
              <div class="availability-badge">
                <span class="availability-percent"><?= round($availability_percent) ?>%</span>
                <p class="availability-label">Tersedia</p>
              </div>
            </div>

            <div class="card-details">
              <div class="detail-item">
                <span class="detail-label">Kapasitas:</span>
                <span class="detail-value"><?= (int)$room['kapasitas'] ?> orang</span>
              </div>
              <?php if (!empty($room['fasilitas'])): ?>
                <div class="detail-item">
                  <span class="detail-label">Fasilitas:</span>
                  <span class="detail-value"><?= e($room['fasilitas']) ?></span>
                </div>
              <?php endif; ?>
            </div>

            <!-- Availability Timeline -->
            <div class="availability-timeline">
              <p class="timeline-label">📊 Jadwal Harian:</p>
              <div class="time-slots">
                <?php foreach ($work_hours as $time): ?>
                  <div class="time-slot <?= in_array($time, $available_slots) ? 'available' : 'occupied' ?>" title="<?= $time ?>">
                    <span class="slot-time"><?= substr($time, 0, 2) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
              <p class="timeline-note">
                <span class="slot-available">■</span> Tersedia 
                <span class="slot-occupied">■</span> Terpakai
              </p>
            </div>

            <!-- Available Slots List -->
            <div class="slots-list">
              <p class="slots-label">Slot Tersedia:</p>
              <?php if (count($available_slots) > 0): ?>
                <div class="available-slots">
                  <?php foreach ($available_slots as $slot): ?>
                    <span class="slot-badge available"><?= $slot ?></span>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="no-slots">❌ Tidak ada slot tersedia untuk hari ini</p>
              <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="card-actions">
              <a href="<?= base_path('/user/reservasi_add.php?ruangan_id=' . (int)$room['id'] . '&tanggal=' . urlencode($selected_date)) ?>" 
                 class="btn btn-primary <?= count($available_slots) === 0 ? 'disabled' : '' ?>"
                 <?= count($available_slots) === 0 ? 'onclick="return false;"' : '' ?>>
                📅 Reservasi Sekarang
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<style>
.ketersediaan-section {
  padding: 30px 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.ketersediaan-wrapper {
  background: #fff;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.back-link {
  display: inline-flex;
  align-items: center;
  color: #0097a7;
  text-decoration: none;
  margin-bottom: 20px;
  font-weight: 500;
  transition: gap 0.3s;
}

.back-link:hover {
  gap: 8px;
}

.ketersediaan-header {
  margin-bottom: 30px;
  border-bottom: 2px solid #e0e0e0;
  padding-bottom: 20px;
}

.ketersediaan-header h1 {
  font-size: 28px;
  color: #333;
  margin: 0 0 10px 0;
}

.ketersediaan-header p {
  color: #666;
  margin: 0;
}

.date-picker-container {
  margin-bottom: 30px;
  background: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
}

.date-form {
  display: flex;
  gap: 15px;
  align-items: flex-end;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.date-input {
  padding: 10px 15px;
  border: 2px solid #0097a7;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
  min-width: 200px;
}

.selected-date-info {
  background: #e1f5fe;
  padding: 15px;
  border-left: 4px solid #0097a7;
  border-radius: 4px;
  margin-bottom: 30px;
  color: #0097a7;
  font-weight: 500;
}

.ketersediaan-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.ketersediaan-card {
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  padding: 20px;
  background: #fafafa;
  transition: all 0.3s ease;
}

.ketersediaan-card:hover {
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
  gap: 10px;
}

.room-info h3 {
  font-size: 18px;
  color: #333;
  margin: 0 0 5px 0;
}

.room-location {
  color: #666;
  font-size: 14px;
  margin: 0;
}

.availability-badge {
  background: linear-gradient(135deg, #0097a7, #00838f);
  color: white;
  padding: 12px;
  border-radius: 8px;
  text-align: center;
  min-width: 80px;
}

.availability-percent {
  font-size: 24px;
  font-weight: 700;
  display: block;
}

.availability-label {
  font-size: 12px;
  margin: 4px 0 0 0;
}

.card-details {
  background: white;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 15px;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
}

.detail-item:last-child {
  border-bottom: none;
}

.detail-label {
  color: #666;
  font-weight: 500;
  font-size: 14px;
}

.detail-value {
  color: #333;
  font-weight: 600;
  font-size: 14px;
}

.availability-timeline {
  background: white;
  padding: 15px;
  border-radius: 6px;
  margin-bottom: 15px;
}

.timeline-label {
  font-size: 13px;
  font-weight: 600;
  color: #333;
  margin: 0 0 10px 0;
}

.time-slots {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 5px;
  margin-bottom: 10px;
}

.time-slot {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: 2px solid transparent;
}

.time-slot.available {
  background: #4caf50;
  color: white;
}

.time-slot.available:hover {
  border-color: #45a049;
  transform: scale(1.05);
}

.time-slot.occupied {
  background: #f44336;
  color: white;
  opacity: 0.6;
}

.slot-time {
  display: block;
}

.timeline-note {
  font-size: 12px;
  color: #666;
  margin: 0;
  display: flex;
  gap: 15px;
}

.slot-available,
.slot-occupied {
  font-size: 16px;
  color: #333;
}

.slots-list {
  background: white;
  padding: 15px;
  border-radius: 6px;
  margin-bottom: 15px;
}

.slots-label {
  font-size: 13px;
  font-weight: 600;
  color: #333;
  margin: 0 0 10px 0;
}

.available-slots {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.slot-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.slot-badge.available {
  background: #c8e6c9;
  color: #2e7d32;
}

.no-slots {
  color: #d32f2f;
  font-size: 13px;
  margin: 0;
  font-weight: 500;
}

  .card-actions {
  display: flex;
  gap: 10px;
}

.no-data {
  grid-column: 1 / -1;
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

@media (max-width: 768px) {
  .ketersediaan-grid {
    grid-template-columns: 1fr;
  }

  .date-form {
    flex-direction: column;
    align-items: stretch;
  }

  .date-input {
    min-width: unset;
  }

  .time-slots {
    grid-template-columns: repeat(4, 1fr);
  }
}
</style>

<?php require __DIR__ . '/../templates/footer.php'; ?>

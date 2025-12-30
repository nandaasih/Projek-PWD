<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ambil daftar ruangan aktif dengan prepared statement
$stmt = mysqli_prepare($conn, "SELECT id, nama, kapasitas, lokasi FROM ruangan WHERE status=? ORDER BY nama");
$status = 'aktif';
mysqli_stmt_bind_param($stmt, "s", $status);
mysqli_stmt_execute($stmt);
$rooms = mysqli_stmt_get_result($stmt);

if (!$rooms) {
    die('Error: ' . mysqli_error($conn));
}

$ruangan_list = mysqli_fetch_all($rooms, MYSQLI_ASSOC);

$title = "Buat Reservasi Baru";
require __DIR__ . '/../templates/header.php';
$err = $_GET['error'] ?? '';

// Get today date
$today = date('Y-m-d');
$min_date = date('Y-m-d', strtotime('+1 day'));
?>

<section class="reservasi-add-section">
  <div class="add-wrapper">
    
    <!-- Back Button -->
    <a href="<?= base_path('/user/dashboard.php') ?>" class="back-link">
      <span>← Kembali ke Dashboard</span>
    </a>

    <!-- Page Header -->
    <div class="add-header">
      <h1>📅 Buat Reservasi Baru</h1>
      <p>Pesan ruangan untuk kebutuhan Anda</p>
    </div>

    <!-- Error Message -->
    <?php if ($err): ?>
      <div class="alert alert-error">
        <span class="alert-icon">!</span>
        <?= e($err) ?>
      </div>
    <?php endif; ?>

    <!-- Reservation Form -->
    <form method="POST" action="<?= base_path('/user/reservasi_create.php') ?>" class="reservation-form">
      <?= csrf_field() ?>
      
      <!-- Ruangan Card -->
      <div class="form-card">
        <h2 class="card-title">🚪 Pilih Ruangan</h2>
        <div class="form-group">
          <label for="ruangan_id" class="form-label">Ruangan <span class="required">*</span></label>
          <div class="select-wrapper">
            <span class="select-icon">🏢</span>
            <select id="ruangan_id" name="ruangan_id" class="form-select" required>
              <option value="">-- Pilih Ruangan --</option>
              <?php foreach ($ruangan_list as $room): ?>
                <option value="<?= (int)$room['id'] ?>">
                  <?= e($room['nama']) ?> (Kapasitas: <?= (int)$room['kapasitas'] ?> orang)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <p class="form-hint">Pilih ruangan yang ingin Anda reservasi</p>
        </div>

        <!-- Room Details Info -->
        <div id="room-details" class="room-details-info" style="display: none;">
          <div class="detail-box">
            <span class="detail-label">Kapasitas</span>
            <span class="detail-value" id="capacity">-</span>
          </div>
          <div class="detail-box">
            <span class="detail-label">Lokasi</span>
            <span class="detail-value" id="location">-</span>
          </div>
        </div>
      </div>

      <!-- Date & Time Card -->
      <div class="form-card">
        <h2 class="card-title">📅 Tanggal & Waktu</h2>
        
        <!-- Tanggal & Jumlah Peserta -->
        <div class="form-row">
          <div class="form-group">
            <label for="tanggal" class="form-label">Tanggal Reservasi <span class="required">*</span></label>
            <div class="input-wrapper">
              <input type="date" id="tanggal" name="tanggal" class="form-input" min="<?= $min_date ?>" required>
            </div>
            <p class="form-hint">Minimal 1 hari sebelumnya</p>
          </div>

          <div class="form-group">
            <label for="jumlah_peserta" class="form-label">Jumlah Peserta <span class="required">*</span></label>
            <div class="input-wrapper">
              <input type="number" id="jumlah_peserta" name="jumlah_peserta" class="form-input" min="1" value="1" required>
            </div>
            <p class="form-hint">Jumlah peserta yang akan hadir</p>
          </div>
        </div>

        <!-- Waktu Mulai & Selesai -->
        <div class="time-section">
          <h3 class="time-section-title">Waktu Reservasi</h3>
          
          <div class="form-row time-row">
            <!-- Waktu Mulai -->
            <div class="form-group time-group">
              <label class="form-label">Waktu Mulai <span class="required">*</span></label>
              <div class="time-input-wrapper">
                <div class="time-input-box">
                  <div class="time-input-inner">
                    <label class="time-label">Jam</label>
                    <select id="mulai_hour" name="mulai_hour" class="time-select" required>
                      <option value="">--</option>
                      <?php for ($h = 7; $h <= 18; $h++): ?>
                        <option value="<?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($h, 2, '0', STR_PAD_LEFT) ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
                <span class="time-separator">:</span>
                <div class="time-input-box">
                  <div class="time-input-inner">
                    <label class="time-label">Menit</label>
                    <select id="mulai_minute" name="mulai_minute" class="time-select" required>
                      <option value="">--</option>
                      <?php for ($m = 0; $m < 60; $m += 15): ?>
                        <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
              </div>
              <input type="hidden" id="mulai" name="mulai" required>
              <p class="form-hint">07:00 - 18:00</p>
            </div>

            <!-- Waktu Selesai -->
            <div class="form-group time-group">
              <label class="form-label">Waktu Selesai <span class="required">*</span></label>
              <div class="time-input-wrapper">
                <div class="time-input-box">
                  <div class="time-input-inner">
                    <label class="time-label">Jam</label>
                    <select id="selesai_hour" name="selesai_hour" class="time-select" required>
                      <option value="">--</option>
                      <?php for ($h = 7; $h <= 19; $h++): ?>
                        <option value="<?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($h, 2, '0', STR_PAD_LEFT) ?></option>
                      <?php endfor; ?>
                      </select>
                  </div>
                </div>
                <span class="time-separator">:</span>
                <div class="time-input-box">
                  <div class="time-input-inner">
                    <label class="time-label">Menit</label>
                    <select id="selesai_minute" name="selesai_minute" class="time-select" required>
                      <option value="">--</option>
                      <?php for ($m = 0; $m < 60; $m += 15): ?>
                        <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
              </div>
              <input type="hidden" id="selesai" name="selesai" required>
              <p class="form-hint">Harus setelah waktu mulai</p>
            </div>
          </div>
        </div>

        <!-- Availability Display -->
        <div id="availability-section" class="availability-section" style="display: none; margin-top: 30px;">
          <h3 class="availability-title">📅 Ketersediaan Slot Jam</h3>
          
          <!-- Status Info -->
          <div id="availability-status" class="availability-status"></div>
          
          <!-- Hour Blocks -->
          <div id="availability-blocks" class="availability-blocks"></div>
          
          <!-- Booked Times Info -->
          <div id="booked-times-info" class="booked-times-info"></div>
        </div>        <!-- Duration Display -->
        <div class="duration-info" style="margin-top: 20px;">
          <span class="duration-icon">⏱️</span>
          <div class="duration-content">
            <strong>Durasi Reservasi: <span id="duration-display">-</span></strong>
            <p id="duration-warning" class="duration-warning" style="display: none;"></p>
          </div>
        </div>
      </div>

      <!-- Additional Info Card -->
      <div class="form-card">
        <h2 class="card-title">📝 Informasi Tambahan</h2>
        
        <div class="form-group">
          <label for="catatan" class="form-label">Catatan / Keterangan</label>
          <div class="textarea-wrapper">
            <span class="textarea-icon">💬</span>
            <textarea id="catatan" name="catatan" class="form-textarea" placeholder="Masukkan catatan atau keterangan (opsional)"></textarea>
          </div>
          <p class="form-hint">Jelaskan keperluan atau kebutuhan khusus</p>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-large">
          <span>✓</span> Kirim Reservasi
        </button>
        <a href="<?= base_path('/user/dashboard.php') ?>" class="btn btn-secondary btn-large">
          <span>✕</span> Batal
        </a>
      </div>

      <!-- Info Box -->
      <div class="info-box-bottom">
        <span class="info-icon">ℹ️</span>
        <div class="info-content">
          <strong>Status Reservasi</strong>
          <p>Reservasi Anda akan berstatus <strong>Pending</strong> dan menunggu persetujuan dari admin. Anda akan menerima notifikasi ketika reservasi disetujui atau ditolak.</p>
        </div>
      </div>
    </form>

  </div>
</section>

<style>
.reservasi-add-section {
  background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
  min-height: 100vh;
  padding: 24px 0 40px 0;
}

.add-wrapper {
  max-width: 700px;
  margin: 0 auto;
  padding: 0 18px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #0070f3;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 24px;
  transition: color 0.2s;
}

.back-link:hover {
  color: #0051cc;
  text-decoration: underline;
}

.add-header {
  margin-bottom: 32px;
}

.add-header h1 {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 700;
  color: #0f172a;
}

.add-header p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
}

/* Alert */
.alert {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 24px;
  font-size: 14px;
  font-weight: 500;
  border: 1px solid;
}

.alert-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.alert-error {
  background: #fee2e2;
  border-color: #ef4444;
  color: #991b1b;
}

/* Form */
.reservation-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-card {
  background: #fff;
  border-radius: 14px;
  padding: 28px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  border: 1px solid #e5e7eb;
  transition: all 0.2s;
}

.form-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border-color: #0070f3;
}

.card-title {
  margin: 0 0 20px 0;
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-label {
  font-size: 14px;
  font-weight: 600;
  color: #0f172a;
}

.required {
  color: #ef4444;
}

.form-hint {
  margin: 0;
  font-size: 12px;
  color: #94a3b8;
}

/* Select */
.select-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 14px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  background: #f9fafb;
  transition: all 0.2s;
}

.select-wrapper:focus-within {
  border-color: #0070f3;
  background: #f0f4ff;
  box-shadow: 0 0 0 3px rgba(0, 112, 243, 0.1);
}

.select-icon {
  font-size: 18px;
  flex-shrink: 0;
}

.form-select {
  flex: 1;
  padding: 12px 0;
  border: none;
  background: transparent;
  color: #0f172a;
  font-size: 14px;
  outline: none;
  cursor: pointer;
}

.form-select option {
  background: #fff;
  color: #0f172a;
}

/* Input */
.input-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 14px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  background: #f9fafb;
  transition: all 0.2s;
}

.input-wrapper:focus-within {
  border-color: #0070f3;
  background: #f0f4ff;
  box-shadow: 0 0 0 3px rgba(0, 112, 243, 0.1);
}

.input-icon {
  font-size: 18px;
  flex-shrink: 0;
}

.form-input {
  flex: 1;
  padding: 12px 0;
  border: none;
  background: transparent;
  color: #0f172a;
  font-size: 14px;
  outline: none;
}

.form-input::placeholder {
  color: #cbd5e1;
}

/* Textarea */
.textarea-wrapper {
  display: flex;
  gap: 12px;
  padding: 14px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  background: #f9fafb;
  transition: all 0.2s;
  align-items: flex-start;
}

.textarea-wrapper:focus-within {
  border-color: #0070f3;
  background: #f0f4ff;
  box-shadow: 0 0 0 3px rgba(0, 112, 243, 0.1);
}

.textarea-icon {
  font-size: 18px;
  flex-shrink: 0;
  margin-top: 4px;
}

.form-textarea {
  flex: 1;
  border: none;
  background: transparent;
  color: #0f172a;
  font-size: 14px;
  outline: none;
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
}

.form-textarea::placeholder {
  color: #cbd5e1;
}

/* Time Picker */
.time-section {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 2px solid #e5e7eb;
}

.time-section-title {
  margin: 0 0 16px 0;
  font-size: 14px;
  font-weight: 700;
  color: #0070f3;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.time-row {
  gap: 24px !important;
}

.time-group {
  flex: 1;
}

.time-picker-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.time-input-wrapper {
  display: flex;
  align-items: center;
  gap: 0;
  padding: 12px;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: linear-gradient(135deg, #f9fafb 0%, #f0f2f5 100%);
  transition: all 0.2s;
}

.time-input-wrapper:focus-within {
  border-color: #0070f3;
  background: #f0f4ff;
  box-shadow: 0 0 0 3px rgba(0, 112, 243, 0.1);
}

.time-input-box {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 0 8px;
}

.time-input-inner {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.time-label {
  font-size: 10px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.time-select {
  width: 100%;
  padding: 10px 6px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  background: #fff;
  color: #0f172a;
  font-size: 20px;
  font-weight: 700;
  text-align: center;
  cursor: pointer;
  outline: none;
  transition: all 0.2s;
}

.time-select:hover {
  border-color: #0070f3;
  background: #f0f4ff;
}

.time-select:focus {
  border-color: #0070f3;
  box-shadow: 0 0 0 2px rgba(0, 112, 243, 0.1);
}

.time-separator {
  font-size: 28px;
  font-weight: 800;
  color: #0070f3;
  height: 44px;
  display: flex;
  align-items: center;
  padding: 0 4px;
  margin: 0 2px;
}

/* Duration Info */
.duration-info {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  padding: 16px;
  background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%);
  border: 2px solid #fcd34d;
  border-radius: 10px;
  margin-top: 12px;
}

.duration-icon {
  font-size: 28px;
  flex-shrink: 0;
}

.duration-content {
  flex: 1;
}

.duration-content strong {
  color: #92400e;
  display: block;
  margin-bottom: 4px;
  font-size: 15px;
}

#duration-display {
  color: #0070f3;
  font-weight: 800;
  font-size: 18px;
}

.duration-warning {
  margin: 6px 0 0 0;
  font-size: 12px;
  color: #dc2626;
  font-weight: 600;
}

/* Room Details */
.room-details-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  padding: 16px 0;
  border-top: 1px solid #e5e7eb;
  margin-top: 16px;
}

.detail-box {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 10px;
  background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
  border-radius: 8px;
}

.detail-label {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 500;
}

.detail-value {
  font-size: 14px;
  color: #0f172a;
  font-weight: 700;
}

/* Form Actions */
.form-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  padding: 28px 0 0 0;
  margin-top: 24px;
  border-top: 2px solid #e5e7eb;
}

/* Info Box */
.info-box-bottom {
  background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
  border: 1px solid #93c5fd;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.info-icon {
  font-size: 24px;
  flex-shrink: 0;
  margin-top: 2px;
}

.info-content {
  flex: 1;
}

.info-content strong {
  color: #1e40af;
  display: block;
  margin-bottom: 4px;
}

.info-content p {
  margin: 0;
  font-size: 13px;
  color: #1e3a8a;
  line-height: 1.5;
}

/* Availability Section */
.availability-section {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border: 1px solid #7dd3fc;
  border-radius: 12px;
  padding: 20px;
  margin-top: 30px;
}

.availability-title {
  font-size: 16px;
  font-weight: 700;
  color: #0c4a6e;
  margin: 0 0 15px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.availability-status {
  margin-bottom: 20px;
}

.status-card {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
}

.status-badge {
  padding: 8px 14px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.status-badge.available {
  background: #86efac;
  color: #166534;
}

.status-badge.blocked {
  background: #fca5a5;
  color: #7f1d1d;
}

.status-badge.percent {
  background: #fbbf24;
  color: #92400e;
}

.availability-blocks {
  margin: 20px 0;
}

.hour-blocks {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(45px, 1fr));
  gap: 8px;
  margin-bottom: 15px;
}

.hour-block {
  aspect-ratio: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: default;
  border: 2px solid transparent;
  transition: all 0.3s;
  position: relative;
}

.hour-block.available {
  background: #dcfce7;
  color: #166534;
  border-color: #86efac;
}

.hour-block.available:hover {
  transform: scale(1.08);
  box-shadow: 0 4px 12px rgba(134, 239, 172, 0.4);
}

.hour-block.blocked {
  background: #fee2e2;
  color: #991b1b;
  border-color: #fca5a5;
  opacity: 0.8;
}

.hour-label {
  font-size: 14px;
  font-weight: 700;
}

.block-icon {
  font-size: 16px;
  margin-top: 2px;
}

.booked-times-info {
  background: white;
  border-radius: 8px;
  padding: 12px;
  border-left: 4px solid #f97316;
}

.booked-times-list {
  margin: 0;
}

.list-title {
  font-size: 13px;
  font-weight: 700;
  color: #0c4a6e;
  margin: 0 0 8px 0;
}

.booked-times-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.booked-times-list li {
  background: #fee2e2;
  color: #991b1b;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  border: 1px solid #fca5a5;
}

/* Responsive */
@media (max-width: 768px) {
  .add-wrapper {
    padding: 0 12px;
  }

  .form-row {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .time-row {
    gap: 12px !important;
  }

  .form-actions {
    grid-template-columns: 1fr;
    gap: 8px;
  }

  .add-header h1 {
    font-size: 22px;
  }

  .form-card {
    padding: 20px;
  }

  .room-details-info {
    grid-template-columns: 1fr;
  }

  .time-input-wrapper {
    padding: 10px;
    gap: 4px;
  }

  .time-select {
    font-size: 18px;
    padding: 8px 4px;
  }

  .time-separator {
    font-size: 24px;
    padding: 0 2px;
  }
}

@media (max-width: 480px) {
  .reservasi-add-section {
    padding: 12px 0 32px 0;
  }

  .add-wrapper {
    padding: 0 12px;
  }

  .add-header h1 {
    font-size: 20px;
  }

  .form-card {
    padding: 16px;
  }

  .btn {
    padding: 12px 16px;
    font-size: 14px;
  }

  .time-input-wrapper {
    padding: 8px;
  }

  .time-select {
    font-size: 16px;
    padding: 8px 2px;
  }

  .time-separator {
    font-size: 20px;
    padding: 0;
    margin: 0 -2px;
  }

  .time-section {
    margin-top: 16px;
    padding-top: 16px;
  }

  .time-row {
    gap: 12px !important;
  }

  .duration-info {
    padding: 12px;
  }

  .duration-icon {
    font-size: 24px;
  }

  .duration-content strong {
    font-size: 14px;
  }

  #duration-display {
    font-size: 16px;
  }
}
</style>

<script>
// Update room details when selection changes
const ruanganSelect = document.getElementById('ruangan_id');
const roomDetails = document.getElementById('room-details');

const ruanganData = {
  <?php foreach ($ruangan_list as $room): ?>
    <?= (int)$room['id'] ?>: {
      nama: "<?= e($room['nama']) ?>",
      kapasitas: "<?= (int)$room['kapasitas'] ?> orang",
      lokasi: "<?= e($room['lokasi'] ?? '-') ?>"
    },
  <?php endforeach; ?>
};

ruanganSelect.addEventListener('change', function() {
  if (this.value && ruanganData[this.value]) {
    document.getElementById('capacity').textContent = ruanganData[this.value].kapasitas;
    document.getElementById('location').textContent = ruanganData[this.value].lokasi;
    roomDetails.style.display = 'grid';
    
    // Check availability ketika ruangan berubah
    checkAvailability();
  } else {
    roomDetails.style.display = 'none';
    document.getElementById('availability-section').style.display = 'none';
  }
});

// Time picker handlers
const mulaiHourSelect = document.getElementById('mulai_hour');
const mulaiMinuteSelect = document.getElementById('mulai_minute');
const selesaiHourSelect = document.getElementById('selesai_hour');
const selesaiMinuteSelect = document.getElementById('selesai_minute');
const mulaiInput = document.getElementById('mulai');
const selesaiInput = document.getElementById('selesai');
const durationDisplay = document.getElementById('duration-display');
const durationWarning = document.getElementById('duration-warning');

function updateHiddenTimeInputs() {
  // Update mulai
  if (mulaiHourSelect.value && mulaiMinuteSelect.value) {
    mulaiInput.value = `${mulaiHourSelect.value}:${mulaiMinuteSelect.value}`;
  }
  
  // Update selesai
  if (selesaiHourSelect.value && selesaiMinuteSelect.value) {
    selesaiInput.value = `${selesaiHourSelect.value}:${selesaiMinuteSelect.value}`;
  }
  
  updateDuration();
}

function updateDuration() {
  if (mulaiInput.value && selesaiInput.value) {
    const [startH, startM] = mulaiInput.value.split(':').map(Number);
    const [endH, endM] = selesaiInput.value.split(':').map(Number);
    
    const startMinutes = startH * 60 + startM;
    const endMinutes = endH * 60 + endM;
    
    if (endMinutes <= startMinutes) {
      durationDisplay.textContent = 'Invalid';
      durationDisplay.style.color = '#dc2626';
      durationWarning.textContent = '⚠️ Waktu selesai harus setelah waktu mulai!';
      durationWarning.style.display = 'block';
      selesaiInput.setCustomValidity('Waktu selesai harus setelah waktu mulai');
    } else {
      const diffMinutes = endMinutes - startMinutes;
      const hours = Math.floor(diffMinutes / 60);
      const minutes = diffMinutes % 60;
      
      let durationText = '';
      if (hours > 0) {
        durationText += `${hours} jam `;
      }
      if (minutes > 0) {
        durationText += `${minutes} menit`;
      }
      
      durationDisplay.textContent = durationText;
      durationDisplay.style.color = '#0070f3';
      durationWarning.style.display = 'none';
      selesaiInput.setCustomValidity('');
    }
  } else {
    durationDisplay.textContent = '-';
    durationWarning.style.display = 'none';
  }
}

// Event listeners
mulaiHourSelect.addEventListener('change', updateHiddenTimeInputs);
mulaiMinuteSelect.addEventListener('change', updateHiddenTimeInputs);
selesaiHourSelect.addEventListener('change', updateHiddenTimeInputs);
selesaiMinuteSelect.addEventListener('change', updateHiddenTimeInputs);

// Cek availability ketika tanggal berubah
const tanggalInput = document.getElementById('tanggal');
tanggalInput.addEventListener('change', checkAvailability);

// Function untuk cek ketersediaan slot
async function checkAvailability() {
  const ruanganId = document.getElementById('ruangan_id').value;
  const tanggal = document.getElementById('tanggal').value;
  
  if (!ruanganId || !tanggal) {
    document.getElementById('availability-section').style.display = 'none';
    return;
  }
  
  try {
    const response = await fetch(`<?= base_path('/user/check_availability.php') ?>?ruangan_id=${ruanganId}&tanggal=${tanggal}`);
    const data = await response.json();
    
    if (data.success) {
      displayAvailability(data);
      blockUnavailableHours(data.blocked_hours);
    }
  } catch (error) {
    console.error('Error checking availability:', error);
  }
}

function displayAvailability(data) {
  const section = document.getElementById('availability-section');
  const statusDiv = document.getElementById('availability-status');
  const blocksDiv = document.getElementById('availability-blocks');
  const bookedDiv = document.getElementById('booked-times-info');
  
  section.style.display = 'block';
  
  // Status
  const percent = Math.round((data.total_available / (data.total_available + data.total_blocked)) * 100);
  statusDiv.innerHTML = `
    <div class="status-card">
      <span class="status-badge available">${data.total_available} Jam Tersedia</span>
      <span class="status-badge blocked">${data.total_blocked} Jam Terpakai</span>
      <span class="status-badge percent">${percent}% Tersedia</span>
    </div>
  `;
  
  // Hour blocks
  let blocksHTML = '<div class="hour-blocks">';
  
  for (let hour = 7; hour < 19; hour++) {
    const hourStr = String(hour).padStart(2, '0') + ':00';
    const isBlocked = data.blocked_hours.includes(hourStr);
    
    blocksHTML += `
      <div class="hour-block ${isBlocked ? 'blocked' : 'available'}" title="${hourStr}">
        <span class="hour-label">${hour}</span>
        ${isBlocked ? '<span class="block-icon">🚫</span>' : '<span class="block-icon">✓</span>'}
      </div>
    `;
  }
  
  blocksHTML += '</div>';
  blocksDiv.innerHTML = blocksHTML;
  
  // Booked times info
  if (data.booked_times.length > 0) {
    let bookedHTML = '<div class="booked-times-list"><p class="list-title">⏱️ Jadwal Terpakai:</p><ul>';
    
    data.booked_times.forEach(booking => {
      const start = booking.start.substring(0, 5);
      const end = booking.end.substring(0, 5);
      bookedHTML += `<li>${start} - ${end}</li>`;
    });
    
    bookedHTML += '</ul></div>';
    bookedDiv.innerHTML = bookedHTML;
  } else {
    bookedDiv.innerHTML = '<div class="booked-times-list"><p class="list-title">✅ Semua slot tersedia untuk hari ini!</p></div>';
  }
}

function blockUnavailableHours(blockedHours) {
  // Disable jam yang terpakai di select start
  const startHourOptions = document.getElementById('mulai_hour').options;
  for (let i = 0; i < startHourOptions.length; i++) {
    const hour = startHourOptions[i].value;
    if (hour) {
      const hourStr = hour + ':00';
      if (blockedHours.includes(hourStr)) {
        startHourOptions[i].disabled = true;
        startHourOptions[i].textContent = hour + ' (Terpakai)';
      } else {
        startHourOptions[i].disabled = false;
        startHourOptions[i].textContent = hour;
      }
    }
  }
  
  // Disable jam yang terpakai di select end
  const endHourOptions = document.getElementById('selesai_hour').options;
  for (let i = 0; i < endHourOptions.length; i++) {
    const hour = endHourOptions[i].value;
    if (hour) {
      const hourStr = hour + ':00';
      if (blockedHours.includes(hourStr)) {
        endHourOptions[i].disabled = true;
        endHourOptions[i].textContent = hour + ' (Terpakai)';
      } else {
        endHourOptions[i].disabled = false;
        endHourOptions[i].textContent = hour;
      }
    }
  }
}

// Conflict checking on form submission
const form = document.querySelector('.reservation-form');
form.addEventListener('submit', async function(e) {
  const ruanganId = document.getElementById('ruangan_id').value;
  const tanggal = document.getElementById('tanggal').value;
  const mulai = document.getElementById('mulai').value;
  const selesai = document.getElementById('selesai').value;

  if (!ruanganId || !tanggal || !mulai || !selesai) {
    alert('Mohon lengkapi semua field yang diperlukan');
    e.preventDefault();
    return;
  }

  try {
    const checkUrl = `<?= base_path('/actions/check_bentrok.php') ?>?ruangan_id=${ruanganId}&tanggal=${tanggal}&mulai=${mulai}&selesai=${selesai}`;
    const response = await fetch(checkUrl);
    const data = await response.json();
    
    if (!data.ok) {
      alert('⚠️ Konflik jadwal terdeteksi! Waktu ini sudah ada reservasi lain di ruangan yang sama. Silakan pilih waktu lain.');
      e.preventDefault();
      return;
    }
  } catch (error) {
    console.error('Error checking conflict:', error);
    // Allow submission even if check fails (server will validate)
  }
});
</script>

<?php require __DIR__ . '/../templates/footer.php'; ?>


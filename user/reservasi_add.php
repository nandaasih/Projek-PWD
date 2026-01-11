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

// Start output buffering to capture HTML content
ob_start();
$err = $_GET['error'] ?? '';

// Get today date
$today = date('Y-m-d');
$min_date = date('Y-m-d', strtotime('+1 day'));
?>

<div class="reservasi-add-wrapper">
    <!-- Page Header -->
    <div style="margin-bottom: 24px;">
      <h2 class="section-title">📅 Buat Reservasi Baru</h2>
      <p style="color: #6b7280; margin: 8px 0 0 0;">Pesan ruangan untuk kebutuhan Anda</p>
    </div>

    <!-- Error Message -->
    <?php if ($err): ?>
      <div style="padding: 12px 16px; background: #fee2e2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 16px;">
        ✗ <?= e($err) ?>
      </div>
    <?php endif; ?>

    <!-- Reservation Form -->
    <form method="POST" action="<?= base_path('/user/reservasi_create.php') ?>" class="reservation-form" style="display: flex; flex-direction: column; gap: 24px;">
      <?= csrf_field() ?>
      
      <!-- Ruangan Card -->
      <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px 0; color: #1f2937;">🚪 Pilih Ruangan</h3>
        
        <div style="margin-bottom: 16px;">
          <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Ruangan <span style="color: #dc2626;">*</span></label>
          <select id="ruangan_id" name="ruangan_id" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
            <option value="">-- Pilih Ruangan --</option>
            <?php foreach ($ruangan_list as $room): ?>
              <option value="<?= (int)$room['id'] ?>" data-capacity="<?= (int)$room['kapasitas'] ?>" data-location="<?= e($room['lokasi'] ?? '') ?>">
                <?= e($room['nama']) ?> (Kapasitas: <?= (int)$room['kapasitas'] ?> orang)
              </option>
            <?php endforeach; ?>
          </select>
          <p style="color: #6b7280; font-size: 12px; margin: 4px 0 0 0;">Pilih ruangan yang ingin Anda reservasi</p>
        </div>

        <!-- Room Details Info -->
        <div id="room-details" style="display: none; padding-top: 16px; border-top: 1px solid #e5e7eb;">
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
            <div style="padding: 12px; background: #f9fafb; border-radius: 6px;">
              <span style="display: block; color: #9ca3af; font-size: 12px; font-weight: 500; margin-bottom: 4px;">Kapasitas</span>
              <span style="display: block; color: #1f2937; font-weight: 600;" id="capacity">-</span>
            </div>
            <div style="padding: 12px; background: #f9fafb; border-radius: 6px;">
              <span style="display: block; color: #9ca3af; font-size: 12px; font-weight: 500; margin-bottom: 4px;">Lokasi</span>
              <span style="display: block; color: #1f2937; font-weight: 600;" id="location">-</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Date & Time Card -->
      <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px 0; color: #1f2937;">📅 Tanggal & Waktu</h3>
        
        <!-- Tanggal & Jumlah Peserta -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
          <div>
            <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Tanggal Reservasi <span style="color: #dc2626;">*</span></label>
            <input type="date" id="tanggal" name="tanggal" min="<?= $min_date ?>" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
            <p style="color: #6b7280; font-size: 12px; margin: 4px 0 0 0;">Minimal 1 hari sebelumnya</p>
          </div>

          <div>
            <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Jumlah Peserta <span style="color: #dc2626;">*</span></label>
            <input type="number" id="jumlah_peserta" name="jumlah_peserta" min="1" value="1" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
            <p style="color: #6b7280; font-size: 12px; margin: 4px 0 0 0;">Jumlah peserta yang akan hadir</p>
          </div>
        </div>

        <!-- Waktu Mulai & Selesai -->
        <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;">
          <h4 style="font-size: 14px; font-weight: 600; color: #1f2937; margin: 0 0 12px 0;">⏰ Waktu Reservasi</h4>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <!-- Waktu Mulai -->
            <div>
              <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Waktu Mulai <span style="color: #dc2626;">*</span></label>
              <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 8px; align-items: center;">
                <select id="mulai_hour" name="mulai_hour" required style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                  <option value="">--</option>
                  <?php for ($h = 7; $h <= 18; $h++): ?>
                    <option value="<?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($h, 2, '0', STR_PAD_LEFT) ?></option>
                  <?php endfor; ?>
                </select>
                <span style="font-weight: 600; color: #6b7280;">:</span>
                <select id="mulai_minute" name="mulai_minute" required style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                  <option value="">--</option>
                  <?php for ($m = 0; $m < 60; $m += 15): ?>
                    <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <input type="hidden" id="mulai" name="mulai" required>
              <p style="color: #6b7280; font-size: 12px; margin: 4px 0 0 0;">07:00 - 18:00</p>
            </div>

            <!-- Waktu Selesai -->
            <div>
              <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Waktu Selesai <span style="color: #dc2626;">*</span></label>
              <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 8px; align-items: center;">
                <select id="selesai_hour" name="selesai_hour" required style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                  <option value="">--</option>
                  <?php for ($h = 8; $h <= 19; $h++): ?>
                    <option value="<?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($h, 2, '0', STR_PAD_LEFT) ?></option>
                  <?php endfor; ?>
                </select>
                <span style="font-weight: 600; color: #6b7280;">:</span>
                <select id="selesai_minute" name="selesai_minute" required style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                  <option value="">--</option>
                  <?php for ($m = 0; $m < 60; $m += 15): ?>
                    <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <input type="hidden" id="selesai" name="selesai" required>
              <p style="color: #6b7280; font-size: 12px; margin: 4px 0 0 0;">Harus lebih besar dari waktu mulai</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Catatan Card -->
      <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px 0; color: #1f2937;">📝 Catatan Tambahan</h3>
        
        <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Keterangan</label>
        <textarea name="keterangan" placeholder="Tuliskan keperluan atau catatan khusus tentang reservasi Anda..." style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; min-height: 100px; resize: vertical;"></textarea>
        <p style="color: #6b7280; font-size: 12px; margin: 4px 0 0 0;">Opsional - Berikan informasi tambahan jika diperlukan</p>
      </div>

      <!-- Form Actions -->
      <div style="display: flex; gap: 12px; margin-top: 8px;">
        <button type="submit" style="padding: 12px 24px; background: #0066cc; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px;">
          ✓ Pesan Ruangan
        </button>
        <a href="<?= base_path('/user/dashboard.php') ?>" style="padding: 12px 24px; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block;">
          ❌ Batal
        </a>
      </div>
    </form>
</div>

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

<?php
// Capture the HTML content
$page_content = ob_get_clean();

// Load the user layout template with admin style
require __DIR__ . '/../templates/layout-user-admin.php';
?>


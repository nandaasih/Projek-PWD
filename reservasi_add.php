<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include 'includes/database.php';
?>

<h1>Reservasi Ruangan</h1>

<form id="reservasiForm">
    <select name="ruangan_id" required>
        <option value="1">Ruang A</option>
        <option value="2">Ruang B</option>
    </select>
    <input type="date" name="tanggal" required>
    <input type="time" name="jam_mulai" required>
    <input type="time" name="jam_selesai" required>
    <button type="submit">Submit</button>
</form>

<div id="errorMessage"></div>

<script>
// Fungsi untuk mengirim email
function kirimEmail($to, $subject, $message) {
    $headers = "From: no-reply@domain.com\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    
    mail($to, $subject, $message, $headers);
}

// Panggil fungsi untuk mengirim email setelah reservasi berhasil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = $_SESSION['user_email'];  // Pastikan user email disimpan di sesi
    $subject = "Konfirmasi Reservasi Ruangan";
    $message = "Reservasi Anda telah berhasil. Berikut rincian reservasi:<br>";
    $message .= "Ruangan: " . $_POST['ruangan_id'] . "<br>";
    $message .= "Tanggal: " . $_POST['tanggal'] . "<br>";
    $message .= "Jam Mulai: " . $_POST['jam_mulai'] . "<br>";
    $message .= "Jam Selesai: " . $_POST['jam_selesai'] . "<br>";

    kirimEmail($user_email, $subject, $message);
}


document.getElementById('reservasiForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    
    fetch('/actions/check_bentrok.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Reservasi berhasil");
        } else {
            document.getElementById('errorMessage').innerHTML = data.message;
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>



<?php
include '../config/database.php';
include '../includes/auth.php';
include '../includes/functions.php';

$auth = new Auth($conn);

// Check if not logged in
if (!$auth->isLoggedIn('siswa')) {
    header('Location: ../index.php');
    exit;
}

$siswa_id = $_SESSION['siswa_id'];
$today_absensi = getTodayAbsensi($conn, $siswa_id);

if (!$today_absensi) {
    header('Location: absen-user.php');
    exit;
}

// Check if after 12:00
$current_time = strtotime(date('H:i'));
$jam_12 = strtotime('12:00');

if ($current_time >= $jam_12) {
    header('Location: backhome-absen-user.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Sudah Tercatat - Sistem Absensi QR Code</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="info-box">
            <div class="info-icon">ℹ️</div>
            <h1>Absensi Sudah Tercatat</h1>
            
            <div class="info-message">
                <p>Anda sudah melakukan absensi hari ini.</p>
                <p>Silakan kembali nanti untuk absensi pulang setelah pukul 12:00.</p>
            </div>
            
            <div class="info-footer">
                <form action="../index.php" method="GET" style="display: inline;">
                    <button type="submit" class="btn btn-primary">Kembali ke Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

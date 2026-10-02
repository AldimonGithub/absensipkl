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
$siswa = getSiswaData($conn, $siswa_id);
$today_pulang = getTodayPulang($conn, $siswa_id);

// Check if already recorded exit
if ($today_pulang) {
    header('Location: success-pulang.php');
    exit;
}

// Check if current time >= 12:00
$current_hour = (int)date('H:i');
$jam_minimum = strtotime('12:00');

if (strtotime($current_hour) < $jam_minimum) {
    // Not yet noon, redirect back to login
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jam_keluar = date('H:i:s');
    $status_pulang = getStatusPulang($jam_keluar);
    $tanggal = date('Y-m-d');
    
    if (!$status_pulang) {
        $error = 'Waktu belum sesuai untuk melakukan checkout!';
    } else {
        // Save exit time to database
        $stmt = $conn->prepare("
            INSERT INTO absensi_keluar (siswa_id, tanggal, jam_keluar, status_pulang) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->bind_param("isss", $siswa_id, $tanggal, $jam_keluar, $status_pulang);
        
        if ($stmt->execute()) {
            $_SESSION['pulang_status'] = $status_pulang;
            $_SESSION['jam_keluar'] = $jam_keluar;
            header('Location: success-pulang.php');
            exit;
        } else {
            $error = 'Gagal menyimpan data pulang!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Pulang - Sistem Absensi QR Code</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="absen-box">
            <div class="absen-header">
                <h1>Absensi Pulang</h1>
                <p class="siswa-info">
                    <strong><?php echo htmlspecialchars($siswa['nama']); ?></strong> | 
                    Kelas: <?php echo htmlspecialchars($siswa['kelas']); ?>
                </p>
            </div>
            
            <div class="time-display">
                <h2 id="jam-terkini">--:--:--</h2>
                <p id="tanggal-terkini"></p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="pulang-info">
                <h3>Status Pulang</h3>
                <div class="pulang-status">
                    <p id="pulang-status-text">Menghitung...</p>
                    <div id="pulang-status-badge" class="badge"></div>
                </div>
            </div>
            
            <div class="pulang-schedule">
                <h3>Jadwal Pulang</h3>
                <ul>
                    <li><span class="badge pulang-cepat">Pulang Cepat</span> 12:00 - 15:40</li>
                    <li><span class="badge tepat-waktu">Tepat Waktu</span> 15:41 - 16:10</li>
                    <li><span class="badge lembur">Lembur</span> 16:11 - 22:00</li>
                </ul>
            </div>
            
            <form method="POST" class="absen-form">
                <button type="submit" class="btn btn-primary btn-large">Konfirmasi Pulang</button>
            </form>
            
            <div class="absen-footer">
                <form action="../index.php" method="GET" style="display: inline;">
                    <button type="submit" class="btn btn-secondary">Logout</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/pulang.js"></script>
</body>
</html>

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
$status_pulang = $_SESSION['pulang_status'] ?? 'Tidak Diketahui';
$jam_keluar = $_SESSION['jam_keluar'] ?? '--:--:--';

unset($_SESSION['pulang_status']);
unset($_SESSION['jam_keluar']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulang Terkirim - Sistem Absensi QR Code</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="success-box">
            <div class="success-icon">✓</div>
            <h1>Pulang Terkirim!</h1>
            
            <div class="success-details">
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($siswa['nama']); ?></p>
                <p><strong>Jam Pulang:</strong> <?php echo htmlspecialchars($jam_keluar); ?></p>
                <p><strong>Status:</strong> <span class="badge <?php echo strtolower(str_replace(' ', '-', $status_pulang)); ?>"><?php echo htmlspecialchars($status_pulang); ?></span></p>
            </div>
            
            <div class="success-message">
                <p>Data pulang Anda telah berhasil dikirim ke data center.</p>
                <p>Sampai jumpa besok!</p>
            </div>
            
            <div class="success-footer">
                <form action="../index.php" method="GET" style="display: inline;">
                    <button type="submit" class="btn btn-primary">Kembali ke Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

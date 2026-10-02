<?php
include '../config/database.php';
include '../includes/auth.php';
include '../includes/functions.php';

$auth = new Auth($conn);

// Check if not logged in or QR not verified
if (!$auth->isLoggedIn('siswa')) {
    header('Location: ../index.php');
    exit;
}

$siswa_id = $_SESSION['siswa_id'];
$siswa = getSiswaData($conn, $siswa_id);
$today_absensi = getTodayAbsensi($conn, $siswa_id);

if ($today_absensi) {
    $_SESSION['already_absen'] = true;
    header('Location: download-absen.php');
    exit;
}

$error = '';
$success = '';
$current_hour = (int)date('H');

// Check if time < 06:00
$current_time = strtotime(date('H:i'));
$jam_minimum = strtotime('06:00');

$can_attend = $current_time >= $jam_minimum;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $jam_masuk = date('H:i:s');
    
    // Validate status
    if (!in_array($status, ['Hadir', 'Sakit', 'Izin', 'Absen'])) {
        $error = 'Status tidak valid!';
    }
    
    // Check if trying to mark "Hadir" before 06:00
    if ($status === 'Hadir' && !$can_attend) {
        $error = 'Anda tidak bisa melakukan absen sebelum pukul 06:00!';
    }
    
    if (!$error && empty($status)) {
        $error = 'Pilih status terlebih dahulu!';
    }
    
    if (!$error) {
        // Handle file upload if provided
        $bukti_file = null;
        if (isset($_FILES['bukti_file']) && $_FILES['bukti_file']['size'] > 0) {
            $upload_result = uploadFile($_FILES['bukti_file']);
            if ($upload_result['success']) {
                $bukti_file = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        if (!$error) {
            // Determine status_waktu
            $status_waktu = null;
            if ($status === 'Hadir') {
                $status_waktu = getStatusWaktu($jam_masuk);
            }
            
            // Save to database
            $tanggal = date('Y-m-d');
            $stmt = $conn->prepare("
                INSERT INTO absensi_masuk 
                (siswa_id, tanggal, jam_masuk, status, status_waktu, keterangan, bukti_file) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param("issssss", $siswa_id, $tanggal, $jam_masuk, $status, $status_waktu, $keterangan, $bukti_file);
            
            if ($stmt->execute()) {
                $_SESSION['absensi_status'] = $status_waktu ?? $status;
                $_SESSION['jam_masuk'] = $jam_masuk;
                header('Location: success-absen.php');
                exit;
            } else {
                $error = 'Gagal menyimpan data absensi!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Masuk - Sistem Absensi QR Code</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="absen-box">
            <div class="absen-header">
                <h1>Absensi Masuk</h1>
                <p class="siswa-info">
                    <strong><?php echo htmlspecialchars($siswa['nama']); ?></strong> | 
                    Kelas: <?php echo htmlspecialchars($siswa['kelas']); ?> | 
                    Bidang: <?php echo htmlspecialchars($siswa['bidang']); ?>
                </p>
            </div>
            
            <div class="time-display">
                <h2 id="jam-terkini">--:--:--</h2>
                <p id="tanggal-terkini"></p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!$can_attend): ?>
                <div class="alert alert-warning">
                    ⏰ Absensi dimulai pukul 06:00. Anda tidak bisa melakukan absen sebelum jam tersebut.
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="absen-form">
                <!-- Status Selection -->
                <div class="status-selection">
                    <h3>Pilih Status</h3>
                    <div class="status-options">
                        <label class="status-option">
                            <input type="radio" name="status" value="Hadir" <?php echo !$can_attend ? 'disabled' : ''; ?> required>
                            <span class="status-badge hadir">Hadir</span>
                        </label>
                        <label class="status-option">
                            <input type="radio" name="status" value="Sakit" required>
                            <span class="status-badge sakit">Sakit</span>
                        </label>
                        <label class="status-option">
                            <input type="radio" name="status" value="Izin" required>
                            <span class="status-badge izin">Izin</span>
                        </label>
                        <label class="status-option">
                            <input type="radio" name="status" value="Absen" required>
                            <span class="status-badge absen">Absen</span>
                        </label>
                    </div>
                </div>
                
                <!-- Keterangan Field -->
                <div class="form-group">
                    <label for="keterangan">Keterangan <span id="keterangan-required">(opsional)</span></label>
                    <textarea id="keterangan" name="keterangan" placeholder="Masukkan keterangan..." rows="3"></textarea>
                </div>
                
                <!-- File Upload -->
                <div class="form-group">
                    <label for="bukti_file">Upload Bukti Foto/Video <span id="file-required">(opsional)</span></label>
                    <input type="file" id="bukti_file" name="bukti_file" accept="image/*,video/*">
                    <small>Format: JPG, PNG, MP4, AVI. Maksimal 10MB</small>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-large">Kirim Absensi</button>
            </form>
            
            <div class="absen-footer">
                <form action="../index.php" method="GET" style="display: inline;">
                    <button type="submit" class="btn btn-secondary">Logout</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/absen.js"></script>
</body>
</html>

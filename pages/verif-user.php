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

$error = '';
$success = '';

// Generate CAPTCHA
if (!isset($_SESSION['captcha_code'])) {
    $_SESSION['captcha_code'] = generateCaptcha();
    $_SESSION['captcha_time'] = time();
}

// Handle CAPTCHA verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_captcha') {
    $inputCaptcha = $_POST['captcha_input'] ?? '';
    
    if (strtoupper($inputCaptcha) === $_SESSION['captcha_code']) {
        $_SESSION['captcha_verified'] = true;
        unset($_SESSION['captcha_code']);
        unset($_SESSION['captcha_time']);
    } else {
        $error = 'CAPTCHA salah, silakan coba lagi!';
        $_SESSION['captcha_code'] = generateCaptcha();
    }
}

// Handle QR Code verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_qr') {
    if (!isset($_SESSION['captcha_verified']) || !$_SESSION['captcha_verified']) {
        $error = 'Verifikasi CAPTCHA terlebih dahulu!';
    } else {
        $qr_code = $_POST['qr_code'] ?? '';
        $siswa_id = $_SESSION['siswa_id'];
        
        // Verify QR code
        $stmt = $conn->prepare("SELECT qr_code FROM siswa WHERE id = ?");
        $stmt->bind_param("i", $siswa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $siswa = $result->fetch_assoc();
        
        if ($siswa && $siswa['qr_code'] === $qr_code) {
            $_SESSION['qr_verified'] = true;
            header('Location: absen-user.php');
            exit;
        } else {
            $error = 'QR Code tidak sesuai dengan data siswa!';
        }
    }
}

$captcha_verified = isset($_SESSION['captcha_verified']) && $_SESSION['captcha_verified'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pengguna - Sistem Absensi QR Code</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>
</head>
<body>
    <div class="container">
        <div class="verif-box">
            <div class="verif-header">
                <h1>Verifikasi Pengguna</h1>
                <p>Halo, <?php echo htmlspecialchars($_SESSION['siswa_nama']); ?></p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- CAPTCHA Section -->
            <?php if (!$captcha_verified): ?>
                <div class="captcha-section">
                    <h3>Verifikasi CAPTCHA</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="verify_captcha">
                        <div class="captcha-container">
                            <canvas id="captchaCanvas" width="150" height="50"></canvas>
                            <button type="button" class="btn-refresh" onclick="refreshCaptcha()">Muat Ulang</button>
                        </div>
                        <div class="form-group">
                            <label for="captcha_input">Masukkan CAPTCHA</label>
                            <input type="text" id="captcha_input" name="captcha_input" placeholder="Masukkan kode" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Verifikasi</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- QR Code Section -->
                <div class="qr-section">
                    <h3>Scan QR Code</h3>
                    <div class="qr-container">
                        <video id="qrVideo" width="100%" height="auto" style="max-width: 400px; border: 2px solid #007bff;"></video>
                    </div>
                    
                    <form method="POST" id="qrForm">
                        <input type="hidden" name="action" value="verify_qr">
                        <input type="hidden" id="qrCodeInput" name="qr_code">
                    </form>
                    
                    <p class="text-center">Arahkan kamera ke QR Code untuk memindainya</p>
                </div>
            <?php endif; ?>
            
            <div class="verif-footer">
                <form action="../index.php" method="GET" style="display: inline;">
                    <button type="submit" class="btn btn-secondary">Logout</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/qr-scanner.js"></script>
</body>
</html>

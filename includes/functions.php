<?php
// Utility Functions

function generateCaptcha() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}

function createCaptchaImage($code) {
    $image = imagecreatetruecolor(150, 50);
    $bgColor = imagecolorallocate($image, 255, 255, 255);
    $textColor = imagecolorallocate($image, 0, 0, 0);
    $lineColor = imagecolorallocate($image, 200, 200, 200);
    
    imagefill($image, 0, 0, $bgColor);
    
    // Add noise lines
    for ($i = 0; $i < 5; $i++) {
        imageline($image, rand(0, 150), rand(0, 50), rand(0, 150), rand(0, 50), $lineColor);
    }
    
    // Add text
    imagestring($image, 5, 45, 18, $code, $textColor);
    
    // Add dots
    for ($i = 0; $i < 50; $i++) {
        imagesetpixel($image, rand(0, 150), rand(0, 50), $lineColor);
    }
    
    return $image;
}

function getStatusWaktu($jam_masuk) {
    $jam = strtotime($jam_masuk);
    $batas_tepat = strtotime('08:00');
    $batas_toleransi = strtotime('08:30');
    
    if ($jam <= $batas_tepat) {
        return 'Tepat waktu';
    } elseif ($jam <= $batas_toleransi) {
        return 'Toleransi';
    } else {
        return 'Terlambat';
    }
}

function getStatusPulang($jam_keluar) {
    $jam = strtotime($jam_keluar);
    $batas_cepat = strtotime('15:40');
    $batas_tepat = strtotime('16:10');
    
    if ($jam <= strtotime('12:00')) {
        return null;
    } elseif ($jam <= $batas_cepat) {
        return 'Pulang cepat';
    } elseif ($jam <= $batas_tepat) {
        return 'Tepat waktu';
    } else {
        return 'Lembur';
    }
}

function uploadFile($file) {
    $upload_dir = __DIR__ . '/../uploads/';
    $allowed = ['jpg', 'jpeg', 'png', 'mp4', 'avi'];
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed)) {
        return ['success' => false, 'message' => 'Format file tidak diperbolehkan'];
    }
    
    if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        return ['success' => false, 'message' => 'Ukuran file terlalu besar'];
    }
    
    $new_filename = 'bukti_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
    $upload_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $new_filename];
    }
    
    return ['success' => false, 'message' => 'Gagal upload file'];
}

function getSiswaData($conn, $siswa_id) {
    $stmt = $conn->prepare("SELECT * FROM siswa WHERE id = ?");
    $stmt->bind_param("i", $siswa_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getTodayAbsensi($conn, $siswa_id) {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT * FROM absensi_masuk WHERE siswa_id = ? AND tanggal = ?");
    $stmt->bind_param("ii", $siswa_id, $today);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getTodayPulang($conn, $siswa_id) {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT * FROM absensi_keluar WHERE siswa_id = ? AND tanggal = ?");
    $stmt->bind_param("ii", $siswa_id, $today);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>

<?php
// This script helps generate QR codes for new students
// Usage: Copy this to your server and run it

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This is a simple utility script
    $siswa_id = $_POST['siswa_id'] ?? '';
    
    // Generate QR code value
    $qr_code = 'SISWA-' . str_pad($siswa_id, 3, '0', STR_PAD_LEFT) . '-' . uniqid();
    
    echo "QR Code untuk siswa ID {$siswa_id}: {$qr_code}\n";
    echo "Gunakan nilai ini untuk update database siswa.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>QR Code Generator</title>
</head>
<body>
    <h1>QR Code Generator untuk Siswa Baru</h1>
    <form method="POST">
        <label>ID Siswa:</label>
        <input type="number" name="siswa_id" required>
        <button type="submit">Generate</button>
    </form>
    <p><strong>Catatan:</strong> Script ini hanya untuk development. Untuk production, generate QR code menggunakan library QR code generation.</p>
</body>
</html>

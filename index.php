<?php
include 'config/database.php';
include 'includes/auth.php';
include 'includes/functions.php';

$auth = new Auth($conn);

// Check if already logged in
if ($auth->isLoggedIn('siswa')) {
    header('Location: pages/verif-user.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_hp = $_POST['nomor_hp'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->loginSiswa($nomor_hp, $password)) {
        header('Location: pages/verif-user.php');
        exit;
    } else {
        $error = 'Nomor HP atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Siswa - Sistem Absensi QR Code</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="login-header">
                <h1>Login Siswa</h1>
                <p>Sistem Absensi QR Code</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="nomor_hp">Nomor HP</label>
                    <input type="tel" id="nomor_hp" name="nomor_hp" placeholder="08xxxxxxxxxx" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="login-footer">
                <p>Apakah Anda Admin? <a href="pages/login-admin.php">Login Admin</a></p>
            </div>
        </div>
    </div>
</body>
</html>

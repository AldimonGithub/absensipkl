<?php
include '../config/database.php';
include '../includes/auth.php';
include '../includes/functions.php';

$auth = new Auth($conn);

// Check if already logged in
if ($auth->isLoggedIn('admin')) {
    header('Location: admin-interface.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->loginAdmin($username, $password)) {
        header('Location: admin-interface.php');
        exit;
    } else {
        $error = 'Username atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Absensi QR Code</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="login-header">
                <h1>Login Admin</h1>
                <p>Sistem Absensi QR Code</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="login-footer">
                <p>Apakah Anda Siswa? <a href="../index.php">Login Siswa</a></p>
            </div>
        </div>
    </div>
</body>
</html>

<?php
session_start();

class Auth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Login Siswa
    public function loginSiswa($nomor_hp, $password) {
        $stmt = $this->conn->prepare("SELECT id, nama, password FROM siswa WHERE nomor_hp = ? AND aktif = TRUE");
        $stmt->bind_param("s", $nomor_hp);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['siswa_id'] = $user['id'];
                $_SESSION['siswa_nama'] = $user['nama'];
                $_SESSION['user_type'] = 'siswa';
                return true;
            }
        }
        return false;
    }
    
    // Login Admin
    public function loginAdmin($username, $password) {
        $stmt = $this->conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['user_type'] = 'admin';
                return true;
            }
        }
        return false;
    }
    
    // Check if user is logged in
    public function isLoggedIn($type = null) {
        if ($type === 'siswa') {
            return isset($_SESSION['siswa_id']);
        } elseif ($type === 'admin') {
            return isset($_SESSION['admin_id']);
        }
        return isset($_SESSION['user_type']);
    }
    
    // Logout
    public function logout() {
        session_destroy();
        return true;
    }
    
    // Get current user ID
    public function getUserId() {
        if ($_SESSION['user_type'] === 'siswa') {
            return $_SESSION['siswa_id'] ?? null;
        } elseif ($_SESSION['user_type'] === 'admin') {
            return $_SESSION['admin_id'] ?? null;
        }
        return null;
    }
}
?>

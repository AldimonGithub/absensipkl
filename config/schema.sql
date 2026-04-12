-- Create Database
CREATE DATABASE IF NOT EXISTS absensi_qrcode;
USE absensi_qrcode;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Siswa
CREATE TABLE IF NOT EXISTS siswa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_hp VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    kelas VARCHAR(50) NOT NULL,
    bidang VARCHAR(100),
    qr_code VARCHAR(255) UNIQUE NOT NULL,
    aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Absensi Masuk (Pagi)
CREATE TABLE IF NOT EXISTS absensi_masuk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    siswa_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME,
    status ENUM('Hadir', 'Sakit', 'Izin', 'Absen') NOT NULL,
    status_waktu ENUM('Tepat waktu', 'Toleransi', 'Terlambat') DEFAULT NULL,
    keterangan TEXT,
    bukti_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id),
    UNIQUE KEY unique_absen_masuk (siswa_id, tanggal)
);

-- Tabel Absensi Keluar (Sore)
CREATE TABLE IF NOT EXISTS absensi_keluar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    siswa_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_keluar TIME,
    status_pulang ENUM('Pulang cepat', 'Tepat waktu', 'Lembur') DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id),
    UNIQUE KEY unique_absen_keluar (siswa_id, tanggal)
);

-- Tabel Session CAPTCHA
CREATE TABLE IF NOT EXISTS captcha_session (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP
);

-- Insert Default Admin
INSERT INTO admin (username, password) VALUES 
('admin', '$2y$10$YIjlrBxTKPM/pDzrwI7bPOx5Hpqe1Hx8T7Kq6L2K5T8M9Z3J4P1Q2R3');

-- Insert Sample Siswa
INSERT INTO siswa (nomor_hp, password, nama, kelas, bidang, qr_code) VALUES 
('08123456789', '$2y$10$YIjlrBxTKPM/pDzrwI7bPOx5Hpqe1Hx8T7Kq6L2K5T8M9Z3J4P1Q2R3', 'Budi Santoso', '10-A', 'RPL', 'SISWA-001-QRCODE'),
('08987654321', '$2y$10$YIjlrBxTKPM/pDzrwI7bPOx5Hpqe1Hx8T7Kq6L2K5T8M9Z3J4P1Q2R3', 'Siti Nurhaliza', '10-B', 'TKJ', 'SISWA-002-QRCODE');

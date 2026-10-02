# API Documentation - Sistem Absensi QR Code

## Overview

Dokumentasi lengkap endpoint dan fungsi yang tersedia dalam sistem absensi QR Code.

## Authentication Functions (`includes/auth.php`)

### `loginSiswa($nomor_hp, $password)`
Login untuk siswa menggunakan nomor HP dan password.

**Parameters:**
- `$nomor_hp` (string): Nomor HP siswa
- `$password` (string): Password siswa

**Returns:**
- `true` jika login berhasil
- `false` jika login gagal

**Example:**
```php
$auth = new Auth($conn);
if ($auth->loginSiswa('08123456789', 'password123')) {
    // Login berhasil
} else {
    // Login gagal
}
```

### `loginAdmin($username, $password)`
Login untuk admin menggunakan username dan password.

**Parameters:**
- `$username` (string): Username admin
- `$password` (string): Password admin

**Returns:**
- `true` jika login berhasil
- `false` jika login gagal

### `isLoggedIn($type = null)`
Mengecek apakah user sudah login.

**Parameters:**
- `$type` (string, optional): 'siswa', 'admin', atau null untuk semua

**Returns:**
- `true` jika user login
- `false` jika user belum login

### `logout()`
Logout user dan destroy session.

**Returns:**
- `true` jika logout berhasil

## Utility Functions (`includes/functions.php`)

### `generateCaptcha()`
Generate kode CAPTCHA 6 karakter.

**Returns:**
- String dengan 6 karakter random

**Example:**
```php
$code = generateCaptcha();
// Output: "Ab3F9x"
```

### `createCaptchaImage($code)`
Buat image CAPTCHA dari kode.

**Parameters:**
- `$code` (string): Kode CAPTCHA

**Returns:**
- Image resource

### `getStatusWaktu($jam_masuk)`
Menentukan status waktu absensi masuk.

**Parameters:**
- `$jam_masuk` (string): Format "HH:MM:SS"

**Returns:**
- 'Tepat waktu' (06:00 - 08:00)
- 'Toleransi' (08:01 - 08:30)
- 'Terlambat' (> 08:30)

```php
$status = getStatusWaktu('07:30:00'); // 'Tepat waktu'
$status = getStatusWaktu('08:15:00'); // 'Toleransi'
$status = getStatusWaktu('09:00:00'); // 'Terlambat'
```

### `getStatusPulang($jam_keluar)`
Menentukan status waktu absensi pulang.

**Parameters:**
- `$jam_keluar` (string): Format "HH:MM:SS"

**Returns:**
- 'Pulang cepat' (12:00 - 15:40)
- 'Tepat waktu' (15:41 - 16:10)
- 'Lembur' (16:11 - 22:00)

```php
$status = getStatusPulang('15:00:00'); // 'Pulang cepat'
$status = getStatusPulang('16:00:00'); // 'Tepat waktu'
$status = getStatusPulang('18:30:00'); // 'Lembur'
```

### `uploadFile($file)`
Upload file bukti foto/video.

**Parameters:**
- `$file` (array): `$_FILES['field_name']`

**Returns:**
```php
[
    'success' => true,
    'filename' => 'bukti_1712345678_1234.jpg'
]
// atau
[
    'success' => false,
    'message' => 'Format file tidak diperbolehkan'
]
```

**Allowed Formats:** jpg, jpeg, png, mp4, avi
**Max Size:** 10MB

### `getSiswaData($conn, $siswa_id)`
Get data lengkap siswa.

**Parameters:**
- `$conn` (mysqli): Database connection
- `$siswa_id` (int): ID siswa

**Returns:**
- Array dengan data siswa atau null jika tidak ditemukan

```php
$siswa = getSiswaData($conn, 1);
// Returns:
// [
//     'id' => 1,
//     'nomor_hp' => '08123456789',
//     'nama' => 'Budi Santoso',
//     'kelas' => '10-A',
//     'bidang' => 'RPL',
//     'qr_code' => 'SISWA-001-QRCODE',
//     ...
// ]
```

### `getTodayAbsensi($conn, $siswa_id)`
Get data absensi masuk hari ini.

**Parameters:**
- `$conn` (mysqli): Database connection
- `$siswa_id` (int): ID siswa

**Returns:**
- Array dengan data absensi atau null

### `getTodayPulang($conn, $siswa_id)`
Get data absensi pulang hari ini.

**Parameters:**
- `$conn` (mysqli): Database connection
- `$siswa_id` (int): ID siswa

**Returns:**
- Array dengan data pulang atau null

## Database Schema

### Table: `siswa`
```sql
CREATE TABLE siswa (
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
```

### Table: `absensi_masuk`
```sql
CREATE TABLE absensi_masuk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    siswa_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME,
    status ENUM('Hadir', 'Sakit', 'Izin', 'Absen') NOT NULL,
    status_waktu ENUM('Tepat waktu', 'Toleransi', 'Terlambat'),
    keterangan TEXT,
    bukti_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id),
    UNIQUE KEY unique_absen_masuk (siswa_id, tanggal)
);
```

### Table: `absensi_keluar`
```sql
CREATE TABLE absensi_keluar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    siswa_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_keluar TIME,
    status_pulang ENUM('Pulang cepat', 'Tepat waktu', 'Lembur'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id),
    UNIQUE KEY unique_absen_keluar (siswa_id, tanggal)
);
```

## Pages Endpoints

### Login Siswa (`index.php`)
- **Method**: GET, POST
- **POST Parameters**: `nomor_hp`, `password`
- **Redirect**: `/pages/verif-user.php` (jika login sukses)

### Login Admin (`pages/login-admin.php`)
- **Method**: GET, POST
- **POST Parameters**: `username`, `password`
- **Redirect**: `/pages/admin-interface.php` (jika login sukses)

### Verifikasi Pengguna (`pages/verif-user.php`)
- **Method**: GET, POST
- **POST (Verify CAPTCHA)**: `action=verify_captcha`, `captcha_input`
- **POST (Verify QR)**: `action=verify_qr`, `qr_code`
- **Session Required**: `siswa_id`, `siswa_nama`
- **Redirect**: `/pages/absen-user.php` (jika QR valid)

### Absensi Masuk (`pages/absen-user.php`)
- **Method**: GET, POST
- **POST Parameters**:
  - `status` (required): 'Hadir', 'Sakit', 'Izin', 'Absen'
  - `keterangan` (optional): Teks keterangan
  - `bukti_file` (optional): File upload
- **Session Required**: `siswa_id`
- **Redirect**: `/pages/success-absen.php` (jika sukses)

### Absensi Pulang (`pages/backhome-absen-user.php`)
- **Method**: GET, POST
- **Minimum Time**: 12:00 (noon)
- **Session Required**: `siswa_id`
- **Redirect**: `/pages/success-pulang.php` (jika sukses)

### Admin Interface (`pages/admin-interface.php`)
- **Method**: GET
- **Query Parameters**:
  - `search`: Cari nama atau nomor HP
  - `filter_status`: Filter status (Hadir, Sakit, Izin, Absen)
  - `filter_date`: Filter tanggal (YYYY-MM-DD)
  - `page`: Halaman pagination
- **Session Required**: `admin_id`

## Timezone & Datetime

Sistem menggunakan timezone server. Untuk mengubah timezone, edit PHP code:

```php
date_default_timezone_set('Asia/Jakarta');
```

## Security Best Practices

1. **Password Hashing**: Gunakan `password_hash()` dan `password_verify()`
2. **SQL Injection**: Selalu gunakan prepared statements
3. **File Upload**: Validasi format, ukuran, dan scan jika memungkinkan
4. **Session**: Jangan share session data di URL
5. **HTTPS**: Gunakan HTTPS di production

## Error Codes

| Code | Message | Meaning |
|------|---------|---------|
| 401 | Unauthorized | Login gagal atau session expired |
| 403 | Forbidden | User tidak memiliki akses |
| 404 | Not Found | Page atau resource tidak ditemukan |
| 500 | Internal Server Error | Database atau server error |

---

**Last Updated**: April 2026

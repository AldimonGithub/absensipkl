# Panduan Setup & Troubleshooting

## 🔧 Setup Awal

### 1. Komposisi Sistem Minimum

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Browser modern (Chrome, Firefox, Safari, Edge)
- Server dengan akses ke folder system

### 2. Langkah-Langkah Setup

#### Step 1: Database Setup
```bash
# Login ke MySQL
mysql -u root -p

# Buat database
CREATE DATABASE absensi_qrcode;
CREATE USER 'absensi_user'@'localhost' IDENTIFIED BY 'password123';
GRANT ALL PRIVILEGES ON absensi_qrcode.* TO 'absensi_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u absensi_user -p absensi_qrcode < config/schema.sql
```

#### Step 2: Konfigurasi File
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'absensi_user');
define('DB_PASS', 'password123');
define('DB_NAME', 'absensi_qrcode');
```

#### Step 3: Permission Folder
```bash
chmod 755 uploads/
chmod 644 config/database.php
```

#### Step 4: Test Connection
Buka di browser: `http://localhost/Absensi/`

Panduan ini berlaku untuk deployment PHP/MySQL. Jika situs dijalankan di GitHub Pages, gunakan [SUPABASE_SETUP.md](SUPABASE_SETUP.md) karena GitHub Pages tidak menjalankan PHP.

### 3. Menambah Siswa Baru

SQL Query untuk menambah siswa baru:
```sql
INSERT INTO siswa (nomor_hp, password, nama, kelas, bidang, qr_code)
VALUES (
    '0812345xxxx',
    '$2y$10$YOUR_HASHED_PASSWORD_HERE',
    'Nama Siswa',
    '10-C',
    'RPL',
    'SISWA-003-QRCODE'
);
```

Atau gunakan PHP untuk hash password:
```php
$hashed = password_hash('password123', PASSWORD_BCRYPT);
echo $hashed;
```

### 4. Menambah Admin Baru

```sql
INSERT INTO admin (username, password)
VALUES (
    'admin2',
    '$2y$10$YOUR_HASHED_PASSWORD_HERE'
);
```

## 🐛 Troubleshooting

### Masalah: "Access Denied" saat koneksi database

**Solusi:**
1. Pastikan MySQL running
2. Verifikasi username dan password di `config/database.php`
3. Pastikan user database punya privilege :
```sql
GRANT ALL PRIVILEGES ON absensi_qrcode.* TO 'absensi_user'@'localhost';
FLUSH PRIVILEGES;
```

### Masalah: QR Code scanner tidak berfungsi

**Solusi:**
1. Gunakan HTTPS atau localhost (requirement untuk akses kamera)
2. Izinkan akses kamera di pengaturan browser
3. Gunakan browser terbaru (Chrome versi 56+, Firefox versi 55+, Safari 11+)
4. Cek console browser untuk error message (F12)

### Masalah: File upload tidak bekerja

**Solusi:**
1. Pastikan folder `uploads/` punya permission write:
```bash
chmod 777 uploads/
```

2. Cek setting di `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

3. Pastikan format file diperbolehkan (JPG, PNG, MP4, AVI)

### Masalah: Session expire terlalu cepat

**Solusi:**
1. Edit `php.ini`:
```ini
session.gc_maxlifetime = 3600  ; 1 jam
session.cookie_lifetime = 0     ; sampai browser ditutup
```

2. Atau di awal PHP file:
```php
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
session_start();
```

### Masalah: Password login salah

**Solusi untuk reset password:**

1. Generate hash password baru:
```php
<?php
echo password_hash('password_baru', PASSWORD_BCRYPT);
?>
```

2. Update di database:
```sql
UPDATE siswa SET password = 'hash_yang_baru_tadi' WHERE nomor_hp = '08123456789';
UPDATE admin SET password = 'hash_yang_baru_tadi' WHERE username = 'admin';
```

### Masalah: CAPTCHA tidak muncul

**Solusi:**
1. Pastikan GD library di-enable di PHP:
```bash
php -m | grep GD
```

2. Jika belum, enable di `php.ini`:
```ini
extension=gd
```

3. Restart Apache/PHP

### Masalah: Timezone tidak sesuai

**Solusi:**
1. Edit top of `index.php`:
```php
date_default_timezone_set('Asia/Jakarta');
```

2. Atau edit `php.ini`:
```ini
date.timezone = "Asia/Jakarta"
```

## 📊 Database Maintenance

### Backup Database
```bash
mysqldump -u absensi_user -p absensi_qrcode > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restore Database
```bash
mysql -u absensi_user -p absensi_qrcode < backup_20260412_120000.sql
```

### Clean Old Files
```bash
# Hapus file lebih dari 30 hari
find uploads/ -type f -mtime +30 -delete
```

### Database Optimization
```sql
OPTIMIZE TABLE siswa;
OPTIMIZE TABLE absensi_masuk;
OPTIMIZE TABLE absensi_keluar;
OPTIMIZE TABLE admin;
OPTIMIZE TABLE captcha_session;
```

## 🔐 Security Checklist

- [ ] Ubah password default admin
- [ ] Ubah password default siswa sample
- [ ] Setup HTTPS dengan SSL certificate
- [ ] Hapus atau rename file development (`qr-generator.php`)
- [ ] Set proper file permissions (644 untuk file, 755 untuk folder)
- [ ] Disable PHP error display di production (edit `php.ini`)
- [ ] Setup firewall dan rate limiting untuk prevent brute force
- [ ] Regular backup database dan file
- [ ] Update PHP dan MySQL ke versi terbaru

## 📈 Performance Tips

1. **Database Indexing**
   Sudah ada di schema, pastikan indexes terpakai dengan baik

2. **Caching**
   Tambahkan HTTP caching header untuk asset statis

3. **Compression**
   Enable gzip compression di Apache:
   ```apache
   mod_deflate
   ```

4. **Database Query Optimization**
   Gunakan EXPLAIN untuk analisis query

5. **Asset Minification**
   Minify CSS dan JavaScript file

## 📞 Support / Help

Untuk bantuan lebih lanjut, hubungi:
- Tim IT Sekolah
- Administrator Sistem

---

**Last Updated**: April 2026

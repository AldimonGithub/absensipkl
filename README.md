# Sistem Aplikasi Absensi QR Code

Sistem aplikasi absensi berbasis QR Code untuk mengelola kehadiran siswa dengan mudah dan efisien.

## 📋 Fitur Utama

### 1. **Login Siswa & Admin**
- Login siswa menggunakan nomor HP dan password
- Login admin dengan username dan password
- Session management yang aman

### 2. **Verifikasi Pengguna**
- Verifikasi CAPTCHA untuk keamanan
- Scan QR Code untuk konfirmasi identitas siswa
- QR Code harus sesuai dengan data siswa

### 3. **Absensi Masuk (Pagi)**
- Pilih status: Hadir, Sakit, Izin, Absen
- Otomatis menghitung status waktu:
  - **Tepat Waktu**: 06:00 - 08:00
  - **Toleransi**: 08:01 - 08:30
  - **Terlambat**: > 08:30
- Field keterangan dan bukti foto/video hanya aktif untuk status Sakit/Izin/Absen
- Minimum waktu absensi: 06:00 (sebelumnya tidak bisa pilih Hadir)

### 4. **Absensi Pulang (Sore)**
- Otomatis tampil setelah pukul 12:00
- Kategori status pulang:
  - **Pulang Cepat**: 12:00 - 15:40
  - **Tepat Waktu**: 15:41 - 16:10
  - **Lembur**: 16:11 - 22:00

### 5. **Admin Interface**
- Dashboard untuk melihat semua data absensi siswa
- Filter berdasarkan nama, status, atau tanggal
- Kolom: Nama, Kelas, Bidang, Status Masuk, Status Waktu, Keterangan, Bukti File, Status Pulang
- Pagination untuk management data yang banyak

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **QR Code Scanning**: jsQR Library
- **Session Management**: PHP Session

## 📦 Struktur Folder

```
Absensi/
├── config/
│   ├── database.php          # Konfigurasi database
│   └── schema.sql            # Schema database
├── includes/
│   ├── auth.php              # Authentication logic
│   └── functions.php         # Utility functions
├── pages/
│   ├── login-admin.php       # Login admin
│   ├── verif-user.php        # Verifikasi pengguna (CAPTCHA & QR)
│   ├── absen-user.php        # Form absensi masuk
│   ├── backhome-absen-user.php # Form absensi pulang
│   ├── success-absen.php     # Konfirmasi absensi masuk
│   ├── success-pulang.php    # Konfirmasi absensi pulang
│   ├── download-absen.php    # Halaman sudah absen
│   └── admin-interface.php   # Dashboard admin
├── assets/
│   ├── css/
│   │   ├── style.css         # Styles utama
│   │   └── admin.css         # Styles admin dashboard
│   └── js/
│       ├── qr-scanner.js     # QR code scanner
│       ├── absen.js          # Absensi form handler
│       └── pulang.js         # Pulang form handler
├── uploads/                  # Folder untuk bukti file (foto/video)
├── index.php                 # Halaman login siswa
└── README.md                 # File dokumentasi ini
```

## ⚙️ Instalasi & Setup

### 1. **Persiapan Database**

1. Buka **phpMyAdmin** atau terminal MySQL
2. Buat database baru dengan nama `absensi_qrcode`
3. Import file `config/schema.sql`:

```bash
mysql -u root -p absensi_qrcode < config/schema.sql
```

Atau gunakan phpMyAdmin:
- Klik "New" → "Database"
- Masukkan nama: `absensi_qrcode`
- Klik "Create"
- Buka tab "Import"
- Pilih file `config/schema.sql`
- Klik "Import"

### 2. **Konfigurasi Database**

Edit file `config/database.php`:

```php
define('DB_HOST', 'localhost');      // Host database
define('DB_USER', 'root');           // Username database
define('DB_PASS', '');               // Password database (kosong jika tidak ada)
define('DB_NAME', 'absensi_qrcode'); // Nama database
```

### 3. **Setup Server**

**Menggunakan XAMPP:**
1. Copy folder `Absensi` ke direktori `htdocs` XAMPP
2. Jalankan Apache dan MySQL dari XAMPP Control Panel
3. Buka browser: `http://localhost/Absensi`

**Menggunakan PHP Built-in Server:**
```bash
cd /path/to/Absensi
php -S localhost:8000
```
Akses di: `http://localhost:8000`

### 4. **Konfigurasi Folder Upload**

Pastikan folder `uploads/` memiliki permission untuk write:

```bash
chmod 755 uploads/
```

## 👥 Akun Default

Setelah import database, ada 2 akun default yang dapat digunakan:

### **Admin**
- **Username**: `admin`
- **Password**: `admin` (hashed dengan bcrypt)

### **Siswa**
1. **Nomor HP**: `08123456789`
   - **Password**: `admin` (hashed dengan bcrypt)
   - **Nama**: Budi Santoso
   - **Kelas**: 10-A
   - **Bidang**: RPL
   - **QR Code**: `SISWA-001-QRCODE`

2. **Nomor HP**: `08987654321`
   - **Password**: `admin` (hashed dengan bcrypt)
   - **Nama**: Siti Nurhaliza
   - **Kelas**: 10-B
   - **Bidang**: TKJ
   - **QR Code**: `SISWA-002-QRCODE`

## 🔐 Keamanan

- Password di-hash menggunakan **bcrypt** (`password_hash()`)
- Session handling untuk authenticasi
- Input validation dan sanitization
- File upload validation (format, ukuran)
- Prepared statements untuk mencegah SQL injection

## 📱 Alur Penggunaan

### **Untuk Siswa:**

1. **Login** → Input Nomor HP & Password
2. **Verifikasi CAPTCHA** → Masukkan kode CAPTCHA yang ditampilkan
3. **Scan QR Code** → Arahkan kamera ke QR Code
4. **Absensi Masuk** → Pilih status, keterangan (jika diperlukan), bukti file (jika diperlukan)
5. **Konfirmasi** → Lihat status waktu (Tepat/Toleransi/Terlambat)
6. **Setelah Jam 12:00** → Bisa melakukan Absensi Pulang
7. **Absensi Pulang** → Konfirmasi pulang dengan status (Pulang Cepat/Tepat Waktu/Lembur)

### **Untuk Admin:**

1. **Login Admin** → Input Username & Password
2. **Dashboard** → Lihat semua data absensi
3. **Filter** → Cari berdasarkan nama, status, atau tanggal
4. **Export Data** (opsional) → Lihat bukti file dari siswa

## 🎨 Customization

### Mengubah Warna:

Edit file `assets/css/style.css`:
```css
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### Mengubah Jam Kerja:

Edit file `includes/functions.php`:
```php
function getStatusWaktu($jam_masuk) {
    $jam = strtotime($jam_masuk);
    $batas_tepat = strtotime('08:00');      // Ubah jam batas
    $batas_toleransi = strtotime('08:30');   // Ubah jam toleransi
    // ...
}
```

### Mengubah Jadwal Pulang:

Edit file `includes/functions.php`:
```php
function getStatusPulang($jam_keluar) {
    $jam = strtotime($jam_keluar);
    $batas_cepat = strtotime('15:40');   // Ubah jam pulang cepat
    $batas_tepat = strtotime('16:10');   // Ubah jam pulang tepat
    // ...
}
```

## 🐛 Troubleshooting

### Database Connection Error
- Pastikan MySQL running
- Periksa username, password, dan nama database di `config/database.php`
- Pastikan database sudah dibuat

### QR Code Scanner Tidak Berfungsi
- Pastikan browser mendukung WebRTC (Chrome, Firefox, Safari, Edge)
- Berikan permission akses kamera ketika browser meminta
- Gunakan HTTPS atau localhost untuk production

### File Upload Gagal
- Pastikan folder `uploads/` writable: `chmod 755 uploads/`
- Periksa file size (maksimal 10MB)
- Periksa format file yang diperbolehkan

### Session Expired
- Bersihkan browser cookies dan cache
- Restart server
- Ubah `session.gc_maxlifetime` di `php.ini` jika perlu

## 📧 Support

Untuk pertanyaan atau laporan bug, silakan hubungi tim IT.

## 📄 Lisensi

Sistem ini dikembangkan untuk kebutuhan internal institusi.

---

**Versi**: 1.0
**Terakhir Update**: April 2026

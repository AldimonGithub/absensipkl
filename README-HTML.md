# Sistem Absensi QR Code - Versi HTML/CSS/JavaScript

Versi HTML/CSS/JavaScript murni dari Sistem Absensi berbasis QR Code. Tidak memerlukan PHP atau database server.

## 📋 Fitur Utama

### ✅ Sudah Diimplementasikan

1. **Login Siswa & Admin**
   - Login siswa dengan nomor HP dan password
   - Login admin dengan username dan password
   - Session management menggunakan localStorage

2. **Verifikasi QR Code**
   - Scan QR Code menggunakan kamera (jsQR library)
   - Manual input QR Code
   - Verifikasi CAPTCHA 6 digit

3. **Absensi Masuk (Pagi)**
   - Status: Hadir, Sakit, Izin, Absen
   - Automatic time status detection:
     - Tepat Waktu: sebelum jam 08:00
     - Toleransi: 08:01 - 08:30
     - Terlambat: setelah 08:30
   - Konditional fields untuk Sakit/Izin/Absen
   - Upload bukti foto/video (simulasi)
   - Hanya bisa absensi mulai jam 06:00

4. **Absensi Pulang (Sore)**
   - Status: Pulang Cepat, Tepat Waktu, Lembur
   - Hanya tersedia setelah jam 12:00
   - Automatic status calculation

5. **Dashboard Siswa**
   - Ringkasan absensi hari ini
   - Riwayat absensi lengkap
   - Export data ke CSV

6. **Interface Admin**
   - Dashboard dengan statistik
   - Tabel semua data absensi siswa
   - Filter berdasarkan nama, status, dan tanggal
   - Pagination
   - Export ke CSV (hari ini atau semua data)

## 🚀 Cara Menggunakan

### Membuka Aplikasi

1. Buka file `index.html` di browser modern (Chrome, Firefox, Safari, Edge)
2. Atau buka dengan Live Server di VS Code

### Demo Accounts

**Siswa:**
- No HP: `08123456789`
- Password: `password123`

**Alternatif Siswa:**
- No HP: `08987654321`
- Password: `password123`

**Admin:**
- Username: `admin`
- Password: `admin123`

## 📁 Struktur File

```
Absensi/
├── index.html                 # Halaman login
├── verif-user.html           # Verifikasi QR Code dan CAPTCHA
├── absen-user.html           # Absensi masuk
├── pulang.html               # Absensi pulang
├── download-absen.html       # Dashboard siswa
├── admin.html                # Dashboard admin
├── data.js                   # Database management (localStorage)
├── assets/
│   ├── css/
│   │   ├── main.css         # CSS utama
│   │   └── style.css        # Style lama (tidak digunakan)
│   └── js/
│       ├── utils.js         # Utility functions
│       ├── qr-scanner.js    # QR scanner (lama)
│       └── ...
└── README.md                 # File ini
```

## 💾 Data Storage

Semua data disimpan di **localStorage** browser:
- `initialized`: Status inisialisasi
- `session`: Data session login
- `admins`: Data admin
- `siswa`: Data siswa
- `absensi_masuk`: Riwayat absensi masuk
- `absensi_keluar`: Riwayat absensi pulang

⚠️ **Catatan:** Data akan hilang jika browser cache dihapus. Untuk production, gunakan database nyata (MySQL, etc).

## 🔐 Keamanan

Untuk development/demo saja. Untuk production:
- ❌ Jangan gunakan localStorage untuk sensitif data
- ✅ Gunakan backend dengan PHP, Node.js, atau framework lain
- ✅ Implement proper authentication (JWT, OAuth, dll)
- ✅ Gunakan HTTPS
- ✅ Hash passwords dengan bcrypt atau library serupa
- ✅ Sanitize semua input

## 🔧 Customization

### Mengubah Jam Minimal Absen

Edit di `absen-user.html`:
```javascript
// Lines ~25-30
if (now.getHours() < 6) { // Ubah 6 untuk jam lain
```

### Mengubah Batas Waktu

Edit di `assets/js/utils.js`:
```javascript
// getStatusWaktu() untuk pagi
// getStatusPulang() untuk sore
```

### Menambah Siswa Baru

Edit di `data.js`:
```javascript
DB.setSiswa([
    // Tambahkan di sini
    { 
        id: 4, 
        nomor_hp: '081xxx', 
        password: btoa('password123'), 
        nama: 'Nama Siswa',
        kelas: '12-A', 
        bidang: 'RPL', 
        qr_code: 'SISWA-004-QRCODE',
        aktif: true 
    }
]);
```

## 📱 Kompatibilitas

- ✅ Chrome/Chromium
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- ✅ Mobile browsers
- ⚠️ IE 11 (mungkin tidak berfungsi sepenuhnya)

## 🎯 Dependencies

- **jsQR** - QR Code scanning (loaded from CDN)
- Tidak ada dependency external lainnya

## 📊 Fitur Laporan

### CSV Export

Data dapat diexport ke format CSV yang kompatibel dengan:
- Microsoft Excel
- Google Sheets
- LibreOffice Calc
- Software lain yang mendukung CSV

## ⚙️ Troubleshooting

### Kamera tidak bekerja
- Pastikan browser memiliki permission akses kamera
- Coba refresh halaman
- Gunakan HTTPS (localhost OK untuk development)

### CAPTCHA tidak muncul
- Canvas tidak support di browser terlalu lama
- Coba browser yang lebih baru

### QR Code tidak terdeteksi
- Pastikan pencahayaan cukup
- Arahkan kamera langsung ke QR Code
- Gunakan manual input sebagai alternatif

### Data hilang setelah refresh
- Ini wajar dengan localStorage
- Untuk production, gunakan backend database

## 📝 License

MIT License - Bebas digunakan untuk tujuan apapun

## 🤝 Kontribusi

Silahkan fork dan submit pull request untuk improvement

## 📧 Support

Untuk pertanyaan atau issue, silahkan buat issue di repository

---

**Versi:** 2.0 (HTML/CSS/JavaScript)  
**Update:** April 2024  
**Status:** Production Ready untuk Demo/Development

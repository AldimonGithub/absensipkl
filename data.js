// Data Management using localStorage

const DB = {
    // Initialize data
    init() {
        if (!localStorage.getItem('initialized')) {
            localStorage.setItem('initialized', 'true');
            
            // Initialize global verification QR
            this.setGlobalQR({
                qr_code: 'ABSENSI-VERIFICATION-001',
                created_at: new Date().toISOString(),
                description: 'QR Code Verifikasi Global Sistem Absensi'
            });
            
            // Initialize admin accounts
            const polgankaPassword = btoa('polganka1');
            const siswaPassword = btoa('password123');
            
            this.setAdmins([
                { id: 1, username: 'polganacid', password: polgankaPassword, nama: 'Polgana' }
            ]);
            
            // Initialize students - empty
            this.setSiswa([]);
            
            // Initialize empty attendance records
            this.setAbsensiMasuk([]);
            this.setAbsensiKeluar([]);
        }
    },
    
    // Session Management
    setSession(user) {
        const session = {
            logged_in: true,
            user_type: user.type, // 'siswa' or 'admin'
            user_id: user.id,
            user_name: user.nama || user.username,
            login_time: new Date().toISOString()
        };
        localStorage.setItem('session', JSON.stringify(session));
    },
    
    getSession() {
        const session = localStorage.getItem('session');
        return session ? JSON.parse(session) : null;
    },
    
    clearSession() {
        localStorage.removeItem('session');
    },
    
    // Global QR Verification Methods
    getGlobalQR() {
        const qr = localStorage.getItem('global_qr');
        return qr ? JSON.parse(qr) : null;
    },
    
    setGlobalQR(data) {
        localStorage.setItem('global_qr', JSON.stringify(data));
    },
    
    generateNewGlobalQR() {
        const timestamp = Date.now();
        const newQR = `ABSENSI-VERIFICATION-${timestamp}`;
        this.setGlobalQR({
            qr_code: newQR,
            created_at: new Date().toISOString(),
            description: 'QR Code Verifikasi Global Sistem Absensi'
        });
        return newQR;
    },
    
    // Admin Methods
    getAdmins() {
        return JSON.parse(localStorage.getItem('admins') || '[]');
    },
    
    setAdmins(data) {
        localStorage.setItem('admins', JSON.stringify(data));
    },
    
    loginAdmin(username, password) {
        const admins = this.getAdmins();
        const admin = admins.find(a => a.username === username);
        if (admin && admin.password === btoa(password)) {
            return admin;
        }
        return null;
    },
    
    // Student Methods
    getSiswa() {
        return JSON.parse(localStorage.getItem('siswa') || '[]');
    },
    
    setSiswa(data) {
        localStorage.setItem('siswa', JSON.stringify(data));
    },
    
    loginSiswa(nomor_hp, password) {
        const siswa = this.getSiswa();
        const student = siswa.find(s => s.nomor_hp === nomor_hp && s.aktif);
        if (student && student.password === btoa(password)) {
            return student;
        }
        return null;
    },
    
    getSiswaById(id) {
        const siswa = this.getSiswa();
        return siswa.find(s => s.id == id);
    },
    
    addSiswa(siswaData) {
        const siswa = this.getSiswa();
        const newId = Math.max(...siswa.map(s => s.id || 0), 0) + 1;
        const newSiswa = {
            id: newId,
            nomor_hp: siswaData.nomor_hp,
            password: btoa(siswaData.password),
            nama: siswaData.nama,
            kelas: siswaData.kelas,
            sekolah: siswaData.sekolah || '',
            bidang: siswaData.bidang,
            qr_code: `SISWA-${newId.toString().padStart(3, '0')}-QRCODE`,
            qr_verified: false,
            aktif: true
        };
        siswa.push(newSiswa);
        this.setSiswa(siswa);
        return newSiswa;
    },
    
    updateSiswa(id, updates) {
        const siswa = this.getSiswa();
        const index = siswa.findIndex(s => s.id == id);
        if (index >= 0) {
            siswa[index] = { ...siswa[index], ...updates };
            if (updates.password) {
                siswa[index].password = btoa(updates.password);
            }
            this.setSiswa(siswa);
            return siswa[index];
        }
        return null;
    },
    
    // Attendance Methods
    getAbsensiMasuk() {
        return JSON.parse(localStorage.getItem('absensi_masuk') || '[]');
    },
    
    setAbsensiMasuk(data) {
        localStorage.setItem('absensi_masuk', JSON.stringify(data));
    },
    
    getAbsensiKeluar() {
        return JSON.parse(localStorage.getItem('absensi_keluar') || '[]');
    },
    
    setAbsensiKeluar(data) {
        localStorage.setItem('absensi_keluar', JSON.stringify(data));
    },
    
    getTodayAbsensi(siswa_id) {
        const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
        const absensi = this.getAbsensiMasuk();
        return absensi.find(a => a.siswa_id == siswa_id && a.tanggal === today);
    },
    
    getTodayPulang(siswa_id) {
        const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
        const pulang = this.getAbsensiKeluar();
        return pulang.find(a => a.siswa_id == siswa_id && a.tanggal === today);
    },
    
    saveAbsensiMasuk(data) {
        const absensi = this.getAbsensiMasuk();
        const today = new Date().toISOString().split('T')[0];
        
        // Check if already exists
        const existingIndex = absensi.findIndex(a => 
            a.siswa_id == data.siswa_id && a.tanggal === today
        );
        
        if (existingIndex >= 0) {
            absensi[existingIndex] = { ...absensi[existingIndex], ...data };
        } else {
            data.id = Math.max(...absensi.map(a => a.id || 0), 0) + 1;
            data.tanggal = today;
            absensi.push(data);
        }
        
        this.setAbsensiMasuk(absensi);
        return data;
    },
    
    saveAbsensiKeluar(data) {
        const pulang = this.getAbsensiKeluar();
        const today = new Date().toISOString().split('T')[0];
        
        // Check if already exists
        const existingIndex = pulang.findIndex(a => 
            a.siswa_id == data.siswa_id && a.tanggal === today
        );
        
        if (existingIndex >= 0) {
            pulang[existingIndex] = { ...pulang[existingIndex], ...data };
        } else {
            data.id = Math.max(...pulang.map(a => a.id || 0), 0) + 1;
            data.tanggal = today;
            pulang.push(data);
        }
        
        this.setAbsensiKeluar(pulang);
        return data;
    },
    
    getAllAbsensi(siswa_id) {
        const masuk = this.getAbsensiMasuk().filter(a => a.siswa_id == siswa_id);
        const keluar = this.getAbsensiKeluar().filter(a => a.siswa_id == siswa_id);
        return { masuk, keluar };
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    DB.init();
});

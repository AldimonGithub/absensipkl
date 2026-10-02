window.ABSENSI_SUPABASE_URL = 'https://YOUR-PROJECT.supabase.co';
window.ABSENSI_SUPABASE_ANON_KEY = 'YOUR_SUPABASE_ANON_KEY';

const supabaseConfigured =
    window.ABSENSI_SUPABASE_URL.startsWith('https://') &&
    !window.ABSENSI_SUPABASE_URL.includes('YOUR-PROJECT') &&
    window.ABSENSI_SUPABASE_ANON_KEY !== 'YOUR_SUPABASE_ANON_KEY';

window.absensiSupabase = {
    client: supabaseConfigured && window.supabase
        ? window.supabase.createClient(window.ABSENSI_SUPABASE_URL, window.ABSENSI_SUPABASE_ANON_KEY)
        : null,

    normalizePhone(value) {
        let phone = value.replace(/[^\d+]/g, '');
        if (phone.startsWith('08')) phone = '+628' + phone.slice(2);
        else if (phone.startsWith('8')) phone = '+62' + phone;
        else if (phone.startsWith('62')) phone = '+' + phone;
        return phone;
    },

    emailForPhone(value) {
        return `${this.normalizePhone(value).replace(/\D/g, '')}@login.absensi.invalid`;
    },

    async getStudentProfile(userId) {
        const { data, error } = await this.client
            .from('profiles')
            .select('id, user_id, nomor_hp, nama, jenis_pengguna, kelas, bidang, qr_code, aktif')
            .eq('user_id', userId)
            .single();

        if (error) throw error;
        return data;
    },

    cacheStudentProfile(profile) {
        const siswa = DB.getSiswa().filter(student => String(student.id) !== String(profile.id));
        siswa.push({
            id: Number(profile.id),
            user_id: profile.user_id,
            nomor_hp: profile.nomor_hp,
            password: '',
            nama: profile.nama,
            jenis_pengguna: profile.jenis_pengguna,
            kelas: profile.kelas || '',
            sekolah: '',
            bidang: profile.bidang || '',
            qr_code: profile.qr_code,
            qr_verified: false,
            aktif: profile.aktif
        });
        DB.setSiswa(siswa);
    }
};
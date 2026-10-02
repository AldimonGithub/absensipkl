import { createClient } from 'https://esm.sh/@supabase/supabase-js@2';

const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
    'Access-Control-Allow-Methods': 'POST, OPTIONS'
};

function jsonResponse(body: Record<string, unknown>, status = 200) {
    return new Response(JSON.stringify(body), {
        status,
        headers: { ...corsHeaders, 'Content-Type': 'application/json' }
    });
}

function normalizePhone(value: string) {
    let phone = value.replace(/[^\d+]/g, '');
    if (phone.startsWith('08')) phone = '+628' + phone.slice(2);
    else if (phone.startsWith('8')) phone = '+62' + phone;
    else if (phone.startsWith('62')) phone = '+' + phone;
    return phone;
}

Deno.serve(async request => {
    if (request.method === 'OPTIONS') return new Response('ok', { headers: corsHeaders });
    if (request.method !== 'POST') return jsonResponse({ error: 'Method tidak diizinkan.' }, 405);

    const authorization = request.headers.get('Authorization');
    if (!authorization) return jsonResponse({ error: 'Sesi Admin diperlukan.' }, 401);

    const supabaseUrl = Deno.env.get('SUPABASE_URL')!;
    const anonKey = Deno.env.get('SUPABASE_ANON_KEY')!;
    const serviceRoleKey = Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!;
    const userClient = createClient(supabaseUrl, anonKey, {
        global: { headers: { Authorization: authorization } }
    });
    const serviceClient = createClient(supabaseUrl, serviceRoleKey);
    const { data: { user }, error: authError } = await userClient.auth.getUser();

    if (authError || !user || user.app_metadata?.role !== 'admin') {
        return jsonResponse({ error: 'Akses hanya untuk Admin.' }, 403);
    }

    let body: Record<string, string>;
    try {
        body = await request.json();
    } catch {
        return jsonResponse({ error: 'Data permintaan tidak valid.' }, 400);
    }

    if (body.action === 'delete') {
        if (!body.user_id || body.user_id === user.id) {
            return jsonResponse({ error: 'Akun tidak valid untuk dihapus.' }, 400);
        }

        const { error } = await serviceClient.auth.admin.deleteUser(body.user_id);
        if (error) return jsonResponse({ error: error.message }, 400);
        return jsonResponse({ success: true });
    }

    const nama = (body.nama || '').trim();
    const nomorHp = normalizePhone(body.nomor_hp || '');
    const jenisPengguna = body.jenis_pengguna;
    const password = body.password || '';

    if (!nama || nama.length > 100 || !/^\+62\d{8,13}$/.test(nomorHp)) {
        return jsonResponse({ error: 'Nama atau nomor HP tidak valid.' }, 400);
    }
    if (!['siswa', 'mahasiswa'].includes(jenisPengguna)) {
        return jsonResponse({ error: 'Jenis pengguna tidak valid.' }, 400);
    }
    if (password.length < 8) {
        return jsonResponse({ error: 'Password minimal 8 karakter.' }, 400);
    }

    const qrCode = `PKL-${crypto.randomUUID().toUpperCase()}`;
    const email = `${nomorHp.replace(/\D/g, '')}@login.absensi.invalid`;
    const { data: created, error: createError } = await serviceClient.auth.admin.createUser({
        email,
        password,
        email_confirm: true,
        app_metadata: { role: 'siswa' },
        user_metadata: { nama, jenis_pengguna: jenisPengguna }
    });

    if (createError || !created.user) {
        return jsonResponse({ error: createError?.message || 'Akun gagal dibuat.' }, 400);
    }

    const profile = {
        user_id: created.user.id,
        nomor_hp: nomorHp,
        nama,
        jenis_pengguna: jenisPengguna,
        kelas: (body.kelas || '').trim(),
        bidang: (body.bidang || '').trim(),
        qr_code: qrCode
    };
    const { data: savedProfile, error: profileError } = await serviceClient
        .from('profiles')
        .insert(profile)
        .select('id, user_id, nomor_hp, nama, jenis_pengguna, kelas, bidang, qr_code, aktif')
        .single();

    if (profileError) {
        await serviceClient.auth.admin.deleteUser(created.user.id);
        return jsonResponse({ error: profileError.message }, 400);
    }

    return jsonResponse({ profile: savedProfile });
});
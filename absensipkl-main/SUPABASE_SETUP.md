# Setup Supabase untuk GitHub Pages

GitHub Pages hanya menyajikan file statis. Supabase menyediakan autentikasi dan database bersama yang dipakai situs ini.

## 1. Buat project dan konfigurasi frontend

1. Buat project di Supabase.
2. Buka **Project Settings > API** dan salin Project URL serta anon/public key.
3. Isi `assets/js/supabase-config.js` pada `ABSENSI_SUPABASE_URL` dan `ABSENSI_SUPABASE_ANON_KEY`.
4. Jangan pernah menaruh `service_role` key di file frontend atau repository.

Anon key memang terlihat di browser. Keamanan data diatur oleh RLS dan Edge Function.

## 2. Siapkan database dan autentikasi

1. Buka **SQL Editor** di Supabase dan jalankan isi `supabase/schema.sql` satu kali.
2. Pastikan provider Email aktif. Nomor HP yang dimasukkan pengguna dipetakan secara internal menjadi identitas email terkonfirmasi; pengguna tetap login dengan nomor HP dan password, tanpa pengiriman email atau SMS.
3. Buat akun Admin awal di **Authentication > Users** menggunakan email dan password.
4. Jadikan akun itu Admin melalui SQL Editor. Ganti email sesuai akun yang dibuat:

```sql
update auth.users
set raw_app_meta_data = coalesce(raw_app_meta_data, '{}'::jsonb) || '{"role":"admin"}'::jsonb
where email = 'admin@example.com';
```

Admin login menggunakan email yang dibuat di Supabase. Siswa/Mahasiswa dibuat dari tab **Kelola Siswa** dan login menggunakan nomor HP serta password.

## 3. Deploy fungsi pengelolaan akun

Install Supabase CLI, lalu dari folder repository jalankan:

```sh
supabase login
supabase link --project-ref PROJECT_REF
supabase functions deploy admin-manage-users
```

Fungsi memakai environment rahasia yang disediakan Supabase. Jangan menambahkan service-role key ke GitHub.

## 4. Deploy situs

Push perubahan ke repository dan aktifkan GitHub Pages untuk branch/folder situs. Masukkan URL GitHub Pages ke **Authentication > URL Configuration > Redirect URLs**.

Akun yang tersimpan sebelumnya di `localStorage` hanya ada pada browser pembuatnya dan tidak dapat otomatis ditemukan oleh Supabase. Buat ulang akun dari dashboard Admin. Data absensi halaman HTML lama juga masih tersimpan lokal; sinkronisasi data absensi lintas perangkat memerlukan migrasi terpisah ke tabel Supabase.
# Checklist Rilis Produksi PPDB

Dokumen ini menjadi checklist minimum sebelum PPDB dibuka untuk calon peserta didik.

## Konfigurasi wajib

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` dibuat khusus untuk instalasi produksi dan disimpan sebagai secret.
- `APP_URL` menggunakan HTTPS resmi sekolah.
- `APP_TIMEZONE=Asia/Makassar` dan jadwal server tersinkron melalui NTP.
- `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, dan `SESSION_SAME_SITE=lax`.
- `MAIL_MAILER` menggunakan SMTP/relay resmi; `MAIL_MAILER=log` hanya untuk development.
- `QUEUE_CONNECTION=database` atau Redis dan worker dijalankan sebagai service.
- `FILESYSTEM_DISK=local` untuk berkas privat, dengan storage dan database masuk jadwal backup.
- Bukti pembayaran SPP disimpan pada disk privat dan hanya diakses melalui endpoint terotorisasi.
- Role `Admin Sekolah` tidak boleh memberikan role `Super Admin`; perubahan role administratif dilakukan oleh Super Admin.
- Ujian guru hanya dapat dibuat untuk kombinasi mata pelajaran dan rombel yang tercatat pada jadwal mengajar guru.
- Migrasi idempotensi tagihan harus berhasil dan tidak boleh dilewati sebelum membuka modul keuangan.

## Pemeriksaan rilis

1. Jalankan `php artisan migrate --force`.
2. Jalankan `php artisan storage:link` hanya bila fitur publik memang membutuhkan disk publik; berkas PPDB tetap disimpan pada disk privat.
3. Jalankan `php artisan optimize` setelah `.env` produksi benar.
4. Pastikan scheduler aktif dengan `php artisan schedule:work` atau cron Laravel.
5. Pastikan worker antrean aktif dan tidak menjalankan job dengan `APP_DEBUG=true`.
6. Uji pendaftaran online, pendaftaran offline, upload dokumen, upload pembayaran, verifikasi, pengumuman, daftar ulang, dan konversi pada staging.
7. Pastikan backup database dan `storage/app/private` dapat dipulihkan sebelum membuka periode.
8. Setelah go-live, pantau log, queue failures, ruang disk, serta audit log PPDB.
9. Pastikan response web memiliki header keamanan minimum dan uji akses dokumen menggunakan akun sekolah lain.
10. Lakukan uji beban, uji penetrasi, uji aksesibilitas keyboard/screen reader, serta simulasi pemulihan backup sebelum rilis resmi.

# Checklist Kesiapan Produksi SIAKAD

Dokumen ini melengkapi checklist PPDB untuk rilis aplikasi sekolah secara resmi.

## Keamanan dan akses

- Super Admin menjadi satu-satunya role yang dapat mengelola role Super Admin.
- Bukti pembayaran, dokumen PPDB, dan berkas administratif berada pada storage privat.
- Endpoint download selalu memeriksa sekolah aktif, relasi data, dan permission pengguna.
- MFA untuk Super Admin dan Admin Sekolah diaktifkan sebelum go-live atau ditetapkan sebagai kontrol kompensasi sementara.
- Security header minimum diuji pada staging: X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, dan HSTS pada HTTPS.
- Password, access code, token, dan kredensial sementara tidak ditulis ke log aplikasi.

## Performa dan memory

- Database produksi menggunakan MySQL/PostgreSQL, bukan SQLite.
- Queue worker dan scheduler berjalan sebagai service yang dipantau.
- Daftar besar memakai pagination atau cursor; export tidak membangun seluruh isi file di memory.
- Backup dan cleanup file diproses terjadwal serta tidak berjalan bersamaan.
- Uji beban dilakukan pada skenario login, PPDB, upload dokumen, pembayaran, presensi, nilai, dan laporan.

## Operasional

- APP_ENV=production, APP_DEBUG=false, HTTPS, cookie secure, SMTP resmi, dan konfigurasi dicache.
- Tersedia backup database dan storage privat dengan retensi yang jelas.
- Restore backup diuji pada lingkungan terpisah.
- Log rotation, pemantauan queue failure, ruang disk, scheduler, dan error rate aktif.
- Tersedia prosedur rollback dan penanggung jawab insiden.

## Validasi sebelum go-live

- UAT semua role: Super Admin, Admin Sekolah, Kepala Sekolah, panitia PPDB, TU, guru, siswa, dan orang tua.
- Uji IDOR lintas sekolah dan lintas akun.
- Uji keamanan upload untuk tipe file, ukuran, nama file, dan konten berbahaya.
- Uji aksesibilitas keyboard, fokus, label field, kontras, dan responsif mobile.
- Composer audit dijalankan dari CI dengan akses registry yang berhasil.

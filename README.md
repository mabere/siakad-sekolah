# SIAKAD Sekolah

**Sistem Informasi Akademik Sekolah Terintegrasi (Smart School ERP)** berbasis Laravel 12 & Livewire 3 yang dirancang untuk mendukung operasional akademik sekolah jenjang SD, SMP, SMA, dan SMK di Indonesia sesuai standar **Kurikulum Merdeka** dan **K13**.

---

## 🌟 Fitur Utama

### 1. Multi-Role & Manajemen Hak Akses
- **Super Admin & Admin Sekolah**: Pengaturan profil sekolah, tahun ajaran aktif, kurikulum, manajemen user & generator akun otomatis, master data (rombel, mapel, guru, siswa).
- **Kepala Sekolah & Wakasek (Kurikulum/Kesiswaan/Sarpras/Humas)**: Dashboard monitoring, persetujuan perangkat pembelajaran, evaluasi ketercapaian akademik.
- **Guru Pengampu & Wali Kelas**:
  - Input & pembobotan nilai (Formatif/Tugas, UTS, UAS) dengan skala predikat huruf kustomisasi (KKTP) dan penguncian nilai.
  - Asisten Perangkat Pembelajaran AI (Google Gemini) untuk pembuatan Modul Ajar RPP+, ATP Silabus, Prota/Prosem, Bahan Ajar, LKPD 3 Tingkat (Scaffolding/Reguler/HOTS), Modul P5, Asesmen & KKTP, Remedial & Pengayaan, serta Diferensiasi Pembelajaran.
  - Jurnal Mengajar KBM & Presensi Mapel Real-time.
  - Bank Soal CBT & Koreksi Ujian Online (Pilihan Ganda & Essay Berbobot).
  - Bimbingan Konseling (BK) & Catatan Pelanggaran/Kedisiplinan Siswa.
  - Pembinaan Ekstrakurikuler & Pencatatan Prestasi Siswa.
- **Tata Usaha & Keuangan (SPP)**: Manajemen pos keuangan, tagihan SPP bulanan, pencatatan transaksi, dan cetak kuitansi.
- **PPDB Online (Penerimaan Peserta Didik Baru)**: Formulir pendaftaran calon murid baru, verifikasi berkas, seleksi, konversi ke siswa aktif, dan pemulihan akses akun.
- **Portal Siswa & Orang Tua**: Monitoring jadwal pelajaran, presensi harian, nilai & rapor digital, pengerjaan ujian CBT online, dan riwayat pembayaran SPP.

---

## 🛠️ Stack Teknologi

- **Backend**: PHP 8.2+ / Laravel 12
- **Frontend / Interaktivitas**: Livewire 3, Alpine.js, Tailwind CSS
- **AI Engine**: Google Gemini API (Structured Outputs / JSON Schema Decoding)
- **Database**: MySQL / SQLite
- **Export & Cetak**: Cetak Resmi Ber-Kop Surat PDF & Unduh Dokumen Microsoft Word (.doc)

---

## 🚀 Panduan Instalasi Lokal

1. **Clone repositori**:
   ```bash
   git clone https://github.com/mabere/siakad-sekolah.git
   cd siakad-sekolah
   ```

2. **Instal dependensi backend & frontend**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi Database & Seeder**:
   ```bash
   php artisan migrate --seed
   ```

5. **Build Asset & Jalankan Server**:
   ```bash
   npm run build
   php artisan serve
   ```

---

## 🧪 Menjalankan Pengujian (Automated Tests)

```bash
php artisan test
```

---

## 📄 Lisensi

Sistem Informasi Akademik Sekolah dilisensikan di bawah [MIT License](LICENSE).

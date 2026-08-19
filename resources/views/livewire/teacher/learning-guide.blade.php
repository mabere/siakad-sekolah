<div>
    <x-slot name="title">Panduan Penggunaan Ekosistem Perangkat Pembelajaran AI</x-slot>

    {{-- HERO HEADER --}}
    <div class="mb-6 rounded-2xl bg-gradient-to-r from-teal-800 via-teal-900 to-slate-900 p-6 text-white shadow-md sm:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-teal-400/20 border border-teal-300/30 px-3 py-0.5 text-xs font-bold text-teal-200">
                        ✨ Panduan Lengkap Kurikulum Merdeka
                    </span>
                    <span class="rounded-full bg-purple-400/20 border border-purple-300/30 px-3 py-0.5 text-xs font-bold text-purple-200">
                        Powered by Google Gemini AI
                    </span>
                </div>
                <h1 class="text-2xl font-black tracking-tight sm:text-3xl">Panduan Penggunaan Perangkat Pembelajaran AI</h1>
                <p class="max-w-3xl text-sm text-teal-100/90 leading-relaxed">
                    Panduan operasional terstruktur untuk setiap peran pengguna (Guru, Wakasek Kurikulum, Kepala Sekolah, dan Administrator) dalam mengoptimalkan pembuatan modul ajar, diferensiasi, program remedial-pengayaan, dan supervisi akademik resmi.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('guru.learning-assistant') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-teal-500 px-4 py-2.5 text-xs font-bold text-slate-950 shadow hover:bg-teal-400 transition">
                    <span>🚀 Buka Generator AI</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ROLE TAB SWITCHER / ROLE BADGE --}}
    @if (count($allowedTabs) > 1)
        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-2 shadow-xs">
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @if (isset($allowedTabs['guru']))
                    <button type="button" wire:click="setRoleTab('guru')" class="flex items-center justify-center gap-2 rounded-lg py-2.5 px-3 text-xs font-bold transition {{ $activeRoleTab === 'guru' ? 'bg-teal-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                        <span>👨‍🏫</span> {{ $allowedTabs['guru'] }}
                    </button>
                @endif
                @if (isset($allowedTabs['wakasek']))
                    <button type="button" wire:click="setRoleTab('wakasek')" class="flex items-center justify-center gap-2 rounded-lg py-2.5 px-3 text-xs font-bold transition {{ $activeRoleTab === 'wakasek' ? 'bg-indigo-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                        <span>🎓</span> {{ $allowedTabs['wakasek'] }}
                    </button>
                @endif
                @if (isset($allowedTabs['kepsek']))
                    <button type="button" wire:click="setRoleTab('kepsek')" class="flex items-center justify-center gap-2 rounded-lg py-2.5 px-3 text-xs font-bold transition {{ $activeRoleTab === 'kepsek' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                        <span>👔</span> {{ $allowedTabs['kepsek'] }}
                    </button>
                @endif
                @if (isset($allowedTabs['admin']))
                    <button type="button" wire:click="setRoleTab('admin')" class="flex items-center justify-center gap-2 rounded-lg py-2.5 px-3 text-xs font-bold transition {{ $activeRoleTab === 'admin' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                        <span>⚙️</span> {{ $allowedTabs['admin'] }}
                    </button>
                @endif
            </div>
        </div>
    @else
        <div class="mb-6 flex items-center justify-between rounded-xl border border-teal-200 bg-teal-50/70 px-5 py-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-700 text-white text-base font-bold shadow-xs">
                    {{ $activeRoleTab === 'guru' ? '👨‍🏫' : ($activeRoleTab === 'wakasek' ? '🎓' : ($activeRoleTab === 'kepsek' ? '👔' : '⚙️')) }}
                </span>
                <div>
                    <p class="text-2xs font-bold uppercase tracking-wider text-teal-700">Panduan Khusus Peran Anda</p>
                    <h2 class="text-sm font-bold text-teal-950">{{ $allowedTabs[$activeRoleTab] ?? 'Panduan Pengguna' }}</h2>
                </div>
            </div>
            <span class="inline-flex items-center rounded-full border border-teal-200 bg-white px-3 py-1 text-2xs font-semibold text-teal-800 shadow-2xs">
                Peran Aktif: {{ session('active_role', auth()->user()?->roles->first()?->name ?? 'Guru') }}
            </span>
        </div>
    @endif

    {{-- KONTEN PANDUAN: 1. ROLE GURU & WALI KELAS --}}
    @if ($activeRoleTab === 'guru' && isset($allowedTabs['guru']))
        <div class="space-y-6">
            {{-- Quick Summary Cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-teal-200 bg-teal-50/60 p-4 space-y-1">
                    <div class="flex items-center gap-2 text-teal-800 font-bold text-xs uppercase">
                        <span>📄</span> 7 Dokumen Kurikulum
                    </div>
                    <p class="text-xs text-slate-700">Modul Ajar, ATP, Prota/Prosem, Bahan Ajar, LKPD Bertingkat, Modul P5, Asesmen KKTP.</p>
                </div>
                <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 p-4 space-y-1">
                    <div class="flex items-center gap-2 text-indigo-800 font-bold text-xs uppercase">
                        <span>💡</span> Diferensiasi & Analisis Kelas
                    </div>
                    <p class="text-xs text-slate-700">Analisis empiris nilai, 3 dimensi diferensiasi, pengelompokan 3-tier, dan 1-click bridge.</p>
                </div>
                <div class="rounded-xl border border-purple-200 bg-purple-50/60 p-4 space-y-1">
                    <div class="flex items-center gap-2 text-purple-800 font-bold text-xs uppercase">
                        <span>🎯</span> Remedial & Pengayaan AI
                    </div>
                    <p class="text-xs text-slate-700">Analisis CBT, re-teaching, worked example, tantangan HOTS, dan ekspor soal ke CBT.</p>
                </div>
            </div>

            {{-- STEP BY STEP GUIDE FOR GURU --}}
            <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span>📘</span> Panduan Langkah Praktis untuk Guru Pengampu
                </h2>

                <div class="space-y-6 pt-2">
                    {{-- Langkah 1 --}}
                    <div class="flex gap-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-600 text-white font-black text-sm">
                            1
                        </div>
                        <div class="space-y-2 flex-1">
                            <h3 class="text-sm font-bold text-slate-900">Membuat Perangkat Pembelajaran Otomatis (7 Jenis Dokumen)</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Buka menu <strong>Pembelajaran & KBM → Perangkat Pembelajaran AI</strong> (<a href="{{ route('guru.learning-assistant') }}" class="text-teal-600 font-semibold underline">buka di sini</a>).
                            </p>
                            <ul class="list-disc pl-5 text-xs text-slate-700 space-y-1">
                                <li>Pilih <strong>Jadwal Kelas Aktif</strong> yang Anda ampu (sistem otomatis mendeteksi Fase Kurikulum E/F, kebutuhan belajar rombel, dan sarana yang tersedia).</li>
                                <li>Pilih <strong>Jenis Dokumen</strong> yang ingin dibuat (Modul Ajar, ATP, Prota-Prosem, Bahan Ajar, LKPD 3 Tingkat, Modul P5, atau Asesmen KKTP).</li>
                                <li>Gunakan fitur <strong>Bank CP & ATP Resmi Kemendikdasmen</strong> untuk memilih bab/topik standar sehingga Tujuan Pembelajaran terisi otomatis secara presisi.</li>
                                <li>Klik <strong>🚀 Generate Perangkat Ajar AI</strong>. Sistem Gemini AI akan menghasilkan dokumen lengkap dalam hitungan detik.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Langkah 2 --}}
                    <div class="flex gap-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-600 text-white font-black text-sm">
                            2
                        </div>
                        <div class="space-y-2 flex-1">
                            <h3 class="text-sm font-bold text-slate-900">Menganalisis Kesiapan Belajar & Strategi Diferensiasi (3 Dimensi)</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Buka menu <strong>Pembelajaran & KBM → Rekomendasi Diferensiasi AI</strong> (<a href="{{ route('guru.differentiation') }}" class="text-teal-600 font-semibold underline">buka di sini</a>).
                            </p>
                            <ul class="list-disc pl-5 text-xs text-slate-700 space-y-1">
                                <li>Pilih jadwal rombel Anda. Sistem akan memuat <strong>Data Empiris Kelas</strong> (rata-rata nilai tugas/UTS/UAS, rasio kehadiran, dan sebaran kemampuan).</li>
                                <li>Klik <strong>💡 Analisis & Rekomendasi Diferensiasi AI</strong> untuk memetakan:
                                    <ul class="list-circle pl-5 mt-1 space-y-0.5 text-2xs text-slate-600">
                                        <li><strong>Diferensiasi Konten:</strong> Materi bertingkat dari fondasi visual hingga materi pengayaan ilmiah.</li>
                                        <li><strong>Diferensiasi Proses:</strong> Skenario scaffolding guru, tutor sebaya, dan investigasi mandiri.</li>
                                        <li><strong>Diferensiasi Produk:</strong> Pilihan unjuk kerja (poster infografis, laporan analitis, atau karya nyata).</li>
                                        <li><strong>Pengelompokan 3-Tier:</strong> Kelompok Fondasi/Scaffolding (&lt;75), Reguler (75-84), dan Pengayaan/HOTS (&ge;85).</li>
                                    </ul>
                                </li>
                                <li>Klik <strong>🚀 Terapkan ke Generator Modul Ajar (1-Click)</strong> untuk menyalin seluruh strategi diferensiasi langsung ke form pembuatan perangkat ajar.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Langkah 3 --}}
                    <div class="flex gap-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-600 text-white font-black text-sm">
                            3
                        </div>
                        <div class="space-y-2 flex-1">
                            <h3 class="text-sm font-bold text-slate-900">Menyusun Paket Pembelajaran Remedial & Pengayaan AI</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Buka menu <strong>Penilaian & Asesmen → Remedial & Pengayaan AI</strong> (<a href="{{ route('guru.remedial-enrichment') }}" class="text-teal-600 font-semibold underline">buka di sini</a>).
                            </p>
                            <ul class="list-disc pl-5 text-xs text-slate-700 space-y-1">
                                <li>Pilih rombel atau pilih <strong>Hasil Ujian CBT</strong> tertentu untuk auto-analisis.</li>
                                <li>Sistem memetakan otomatis siswa yang memerlukan remedial (&lt;75) dan siswa yang siap pengayaan (&ge;85).</li>
                                <li>Klik <strong>🚀 Generate Paket Remedial & Pengayaan AI</strong>. Sistem menyusun:
                                    <ul class="list-circle pl-5 mt-1 space-y-0.5 text-2xs text-slate-600">
                                        <li><strong>Paket Remedial:</strong> Rangkuman konsep pelurus miskonsepsi, worked example terurai, 5 butir soal latihan berpemandu (hints), kunci jawaban, dan panduan guru.</li>
                                        <li><strong>Paket Pengayaan:</strong> Wacana studi kasus kontekstual nyata, soal tantangan HOTS C4–C6, ide mini projek mandiri, dan rubrik asesmen.</li>
                                        <li><strong>Diagnosis Miskonsepsi:</strong> Analisis akar masalah kendala belajar siswa.</li>
                                    </ul>
                                </li>
                                <li>Gunakan tombol <strong>⚡ Ekspor ke CBT</strong> untuk langsung mengonversi soal remedial menjadi Bank Soal CBT baru.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Langkah 4 --}}
                    <div class="flex gap-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-600 text-white font-black text-sm">
                            4
                        </div>
                        <div class="space-y-2 flex-1">
                            <h3 class="text-sm font-bold text-slate-900">Sinkronisasi Jurnal KBM, Duplikasi Kelas Paralel & Ekspor Resmi</h3>
                            <ul class="list-disc pl-5 text-xs text-slate-700 space-y-1">
                                <li><strong>1-Click Sinkronisasi ke Jurnal Mengajar:</strong> Mengisi otomatis aktivitas harian, alokasi jam, dan catatan KBM ke Jurnal Mengajar Anda.</li>
                                <li><strong>Duplikasi ke Kelas Paralel:</strong> Menggandakan perangkat pembelajaran yang sama ke rombel paralel yang Anda ajar hanya dengan sekali klik tanpa input ulang.</li>
                                <li><strong>Cetak PDF Resmi & Ekspor Word:</strong> Menghasilkan format cetak ber-kop resmi sekolah dan berkas Microsoft Word (.doc) yang siap diedit atau diajukan ke Kepala Sekolah.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    {{-- KONTEN PANDUAN: 2. ROLE WAKASEK KURIKULUM --}}
    @elseif ($activeRoleTab === 'wakasek' && isset($allowedTabs['wakasek']))
        <div class="space-y-6">
            <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 p-5 space-y-2">
                <div class="flex items-center gap-2 text-indigo-900 font-bold text-sm">
                    <span>🎓</span> Peran Strategis Wakasek Kurikulum
                </div>
                <p class="text-xs text-slate-700 leading-relaxed">
                    Wakasek Kurikulum bertanggung jawab atas standarisasi dokumen Kurikulum Satuan Pendidikan (KSP), pengelolaan Bank Kurikulum Merdeka (Capaian Pembelajaran, Elemen, dan Alur Tujuan Pembelajaran), pembagian beban jam mengajar (JP), serta supervisi administrasi akademik guru.
                </p>
            </div>

            <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span>📋</span> Panduan Operasional Wakasek Kurikulum
                </h2>

                <div class="space-y-6 pt-2">
                    <div class="border-l-4 border-indigo-600 pl-4 space-y-1.5">
                        <h3 class="text-sm font-bold text-slate-900">1. Manajemen Bank Kurikulum Merdeka Terpadu (CP / TP)</h3>
                        <p class="text-xs text-slate-600">
                            Akses menu <strong>Data Akademik → Bank Kurikulum (CP/TP)</strong>.
                        </p>
                        <ul class="list-disc pl-5 text-xs text-slate-700 space-y-0.5">
                            <li>Memeriksa dan memperbarui Capaian Pembelajaran (CP) Fase E (Kelas 10) dan Fase F (Kelas 11–12).</li>
                            <li>Menetapkan Tujuan Pembelajaran (TP) baku dan Kriteria Ketercapaian Tujuan Pembelajaran (KKTP) sebagai pedoman seluruh guru mapel.</li>
                            <li>Memastikan elemen dan sub-elemen sesuai dengan standar Kepka BSKAP No. 032/H/KR/2024.</li>
                        </ul>
                    </div>

                    <div class="border-l-4 border-indigo-600 pl-4 space-y-1.5">
                        <h3 class="text-sm font-bold text-slate-900">2. Menetapkan Struktur Rombel, Jadwal & Kalender Pekan Efektif</h3>
                        <p class="text-xs text-slate-600">
                            Akses menu <strong>Data Akademik → Rombongan Belajar & Jadwal Pelajaran</strong>.
                        </p>
                        <ul class="list-disc pl-5 text-xs text-slate-700 space-y-0.5">
                            <li>Menentukan jumlah alokasi pekan efektif semester ganjil dan genap (misal: 18 pekan efektif).</li>
                            <li>Mengisi profil kebutuhan belajar dan fasilitas di menu Rombongan Belajar agar asisten AI guru dapat menghasilkan modul yang kontekstual.</li>
                        </ul>
                    </div>

                    <div class="border-l-4 border-indigo-600 pl-4 space-y-1.5">
                        <h3 class="text-sm font-bold text-slate-900">3. Monitoring & Supervisi Administrasi Guru</h3>
                        <p class="text-xs text-slate-600">
                            Memantau keterisian perangkat ajar guru secara berkala.
                        </p>
                        <ul class="list-disc pl-5 text-xs text-slate-700 space-y-0.5">
                            <li>Memverifikasi kelengkapan 7 dokumen kurikulum (Modul Ajar, ATP, Prota-Prosem, LKPD, Bahan Ajar, Modul P5, Asesmen KKTP) pada setiap awal semester.</li>
                            <li>Memastikan guru telah menerapkan strategi pembelajaran terdiferensiasi serta melaksanakan program remedial dan pengayaan bagi siswa yang membutuhkan.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    {{-- KONTEN PANDUAN: 3. ROLE KEPALA SEKOLAH --}}
    @elseif ($activeRoleTab === 'kepsek' && isset($allowedTabs['kepsek']))
        <div class="space-y-6">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-5 space-y-2">
                <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                    <span>👔</span> Peran Pengawasan & Pengesahan Kepala Sekolah
                </div>
                <p class="text-xs text-slate-700 leading-relaxed">
                    Kepala Sekolah memegang fungsi pembinaan supervisi akademik manajerial, persetujuan resmi (approval) dokumen kurikulum guru, evaluasi capaian asesmen berbasis rapor pendidikan, dan penandatanganan dokumen legal sekolah.
                </p>
            </div>

            <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span>🖋️</span> Panduan Supervisi & Pengesahan Dokumen
                </h2>

                <div class="space-y-6 pt-2">
                    <div class="border-l-4 border-emerald-600 pl-4 space-y-1.5">
                        <h3 class="text-sm font-bold text-slate-900">1. Penelaahan & Persetujuan (Approval) Perangkat Ajar Guru</h3>
                        <ul class="list-disc pl-5 text-xs text-slate-700 space-y-0.5">
                            <li>Meninjau dokumen Modul Ajar dan perangkat ajar yang diajukan oleh guru pengampu.</li>
                            <li>Memeriksa keselarasan Tujuan Pembelajaran dengan Visi, Misi, dan Karakteristik Satuan Pendidikan.</li>
                            <li>Mengecek kelayakan rubrik asesmen KKTP dan integrasi 6 Dimensi Profil Pelajar Pancasila (P5).</li>
                        </ul>
                    </div>

                    <div class="border-l-4 border-emerald-600 pl-4 space-y-1.5">
                        <h3 class="text-sm font-bold text-slate-900">2. Pengesahan Dokumen Cetak Ber-Kop Resmi</h3>
                        <ul class="list-disc pl-5 text-xs text-slate-700 space-y-0.5">
                            <li>Setiap lembar kerja (Modul Ajar, Lembar Remedial, Lembar Pengayaan) yang dicetak oleh guru secara otomatis mencantumkan nama dan NIP Kepala Sekolah untuk pengesahan tanda tangan resmi.</li>
                        </ul>
                    </div>

                    <div class="border-l-4 border-emerald-600 pl-4 space-y-1.5">
                        <h3 class="text-sm font-bold text-slate-900">3. Evaluasi Capaian Pembelajaran & Intervensi Mutu</h3>
                        <ul class="list-disc pl-5 text-xs text-slate-700 space-y-0.5">
                            <li>Memantau rekapitulasi Ledger Nilai, Rapor Digital, dan hasil evaluasi Projek P5 melalui menu <strong>Data Akademik → Penilaian & Rapor</strong>.</li>
                            <li>Mendorong guru memanfaatkan modul diferensiasi dan program remedial AI jika ditemukan rombel dengan tingkat ketidaktuntasan tinggi.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    {{-- KONTEN PANDUAN: 4. ROLE ADMINISTRATOR SEKOLAH --}}
    @elseif ($activeRoleTab === 'admin' && isset($allowedTabs['admin']))
        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-slate-100 p-5 space-y-2">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-sm">
                    <span>⚙️</span> Konfigurasi Sistem & Provider Gemini AI
                </div>
                <p class="text-xs text-slate-700 leading-relaxed">
                    Administrator Sekolah dan Super Admin mengontrol ketersediaan API AI, master data akademik, tahun ajaran aktif, dan hak akses pengguna.
                </p>
            </div>

            <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span>🛠️</span> Panduan Konfigurasi Administrator
                </h2>

                <div class="space-y-6 pt-2">
                    <div class="border-l-4 border-slate-700 pl-4 space-y-1.5">
                        <h3 class="text-sm font-bold text-slate-900">1. Konfigurasi Google Gemini AI Provider</h3>
                        <p class="text-xs text-slate-600">
                            Pastikan berkas <code>.env</code> atau pengaturan sistem memuat kredensial Gemini API:
                        </p>
                        <div class="rounded-lg bg-slate-900 p-3 text-2xs font-mono text-emerald-400">
                            GEMINI_API_KEY=AIzaSy...<br>
                            GEMINI_MODEL=gemini-2.5-flash<br>
                            GEMINI_ENABLED=true
                        </div>
                    </div>

                    <div class="border-l-4 border-slate-700 pl-4 space-y-1.5">
                        <h3 class="text-sm font-bold text-slate-900">2. Master Data & Penjadwalan Pembelajaran</h3>
                        <ul class="list-disc pl-5 text-xs text-slate-700 space-y-0.5">
                            <li>Memastikan Tahun Ajaran aktif telah diatur dengan tipe kurikulum <strong>MERDEKA</strong>.</li>
                            <li>Memastikan data Guru memiliki akun pengguna aktif dan terhubung ke jadwal mengajar yang valid.</li>
                            <li>Mengisi Visi, Misi, dan Muatan Lokal pada Pengaturan Sekolah agar diintegrasikan secara otomatis oleh AI ke dalam dokumen KBM.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- FAQ SECTION --}}
    <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
            <span>❓</span> Pertanyaan yang Sering Diajukan (FAQ)
        </h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 text-xs">
            <div class="rounded-lg bg-slate-50 border border-slate-200 p-4 space-y-1.5">
                <strong class="text-slate-900 block font-bold">Apakah dokumen AI sudah sesuai regulasi resmi Kemendikdasmen?</strong>
                <p class="text-slate-600 leading-relaxed">
                    Ya, seluruh prompt dan skema JSON telah diselaraskan dengan <strong>Panduan Pembelajaran dan Asesmen (PPA 2024)</strong> dan <strong>Kepka BSKAP No. 032/H/KR/2024</strong> tentang Capaian Pembelajaran Kurikulum Merdeka.
                </p>
            </div>
            <div class="rounded-lg bg-slate-50 border border-slate-200 p-4 space-y-1.5">
                <strong class="text-slate-900 block font-bold">Apakah hasil generate AI dapat diedit secara manual oleh guru?</strong>
                <p class="text-slate-600 leading-relaxed">
                    Sangat bisa. Guru memiliki kendali penuh untuk mengedit langsung setiap butir aktivitas, pertanyaan pemantik, rubrik penilaian, atau mengunduhnya dalam format Microsoft Word (.doc) untuk penyuntingan offline.
                </p>
            </div>
            <div class="rounded-lg bg-slate-50 border border-slate-200 p-4 space-y-1.5">
                <strong class="text-slate-900 block font-bold">Bagaimana keamanan data pribadi siswa?</strong>
                <p class="text-slate-600 leading-relaxed">
                    Sistem hanya mengirimkan agregat data statistik (rata-rata nilai, persentase kehadiran, dan karakteristik kebutuhan belajar) ke AI, tanpa pernah mengekspos data pribadi sensitif (NIK, alamat, kontak) siswa.
                </p>
            </div>
            <div class="rounded-lg bg-slate-50 border border-slate-200 p-4 space-y-1.5">
                <strong class="text-slate-900 block font-bold">Bagaimana jika guru mengajar beberapa kelas paralel pada mata pelajaran yang sama?</strong>
                <p class="text-slate-600 leading-relaxed">
                    Guru cukup membuat 1 dokumen acuan, kemudian menggunakan fitur <strong>Duplikasi ke Kelas Paralel</strong> untuk menyalinnya ke seluruh kelas paralel hanya dalam satu kali klik.
                </p>
            </div>
        </div>
    </div>
</div>

<div>
    <x-slot name="title">Rapor Siswa - {{ $student->name }}</x-slot>

    <!-- Style khusus untuk mencetak -->
    <style>
        @media print {

            /* Sembunyikan elemen UI sistem */
            aside,
            header,
            .print-hide,
            .toast {
                display: none !important;
            }

            /* Hilangkan background abu-abu dan padding dari main container layout */
            body,
            main,
            html {
                background-color: white !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: 100% !important;
            }

            /* Sesuaikan kontainer rapor untuk kertas */
            .rapor-container {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            /* Pastikan tabel tidak terpotong aneh */
            table {
                page-break-inside: auto
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto
            }

            @page {
                size: A4;
                margin: 1.5cm;
            }
        }
    </style>

    <!-- Tombol Aksi (Sembunyi saat dicetak) -->
    <div class="mb-6 flex justify-between items-center print-hide">
        <a href="{{ route('admin.academic.report-cards') }}"
            class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Kembali ke Daftar Kelas
        </a>

        <button onclick="window.print()"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                </path>
            </svg>
            Cetak Rapor (PDF / Printer)
        </button>
    </div>

    @if (!$activeYear)
        <div class="mb-4 bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 rounded shadow-sm print-hide">
            Tidak ada Tahun Ajaran yang aktif.
        </div>
    @else
        <!-- Kertas Rapor -->
        <div
            class="rapor-container bg-white shadow-xl border border-slate-200 rounded-sm mx-auto max-w-4xl p-10 text-slate-900 font-serif relative">

            <!-- Kop Surat Resmi -->
            <div class="border-b-4 border-slate-800 pb-4 mb-6 flex items-center justify-between">
                <div class="w-24 h-24 flex items-center justify-center shrink-0">
                    @if ($school && $school->logo)
                        <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo"
                            class="max-w-full max-h-full object-contain">
                    @else
                        <div
                            class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center border-2 border-slate-300">
                            <span class="text-xs font-bold text-slate-400">LOGO</span>
                        </div>
                    @endif
                </div>
                <div class="flex-1 text-center px-4">
                    <h1 class="text-2xl font-bold uppercase tracking-wide text-slate-800">
                        {{ $school->name ?? 'NAMA SEKOLAH' }}</h1>
                    <p class="text-sm mt-1">{{ $school->address ?? 'Alamat Sekolah Belum Diisi, Kota, Provinsi' }}</p>
                    <p class="text-sm">Telepon: {{ $school->phone ?? '-' }} | Email: {{ $school->email ?? '-' }}</p>
                </div>
                <div class="w-24 shrink-0"></div> <!-- Spacer for centering -->
            </div>

            <!-- Judul Dokumen -->
            <div class="text-center mb-8">
                <h2 class="text-xl font-bold uppercase underline">Laporan Hasil Belajar Siswa</h2>
                <p class="text-sm mt-1">Nomor: 421.3 / {{ date('Ym') }} / {{ $student->id }} / {{ $activeYear->id }}
                </p>
            </div>

            <!-- Identitas Siswa -->
            <div class="flex justify-between mb-6 text-sm">
                <table class="w-1/2">
                    <tr>
                        <td class="py-1 w-32 font-semibold">Nama Siswa</td>
                        <td class="py-1 w-2">:</td>
                        <td class="py-1 font-bold">{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold">NIS / NISN</td>
                        <td>:</td>
                        <td class="py-1">{{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold">Kelas</td>
                        <td>:</td>
                        <td class="py-1">{{ $student->classroom->grade_level ?? '-' }}
                            {{ $student->classroom->name ?? '-' }}</td>
                    </tr>
                </table>
                <table class="w-1/3">
                    <tr>
                        <td class="py-1 w-30 font-semibold">Tahun Ajaran</td>
                        <td class="py-1 w-2">:</td>
                        <td class="py-1">{{ $activeYear->name }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold">Semester</td>
                        <td>:</td>
                        <td class="py-1">
                            {{ str_contains(strtolower($activeYear->name), 'genap') ? 'Genap' : 'Ganjil' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Tabel Nilai Akademik -->
            <div class="mb-8">
                <h3 class="font-bold text-slate-800 mb-2 uppercase text-sm border-b-2 border-slate-300 pb-1">A. Nilai
                    Akademik</h3>
                <table class="w-full border-collapse border border-slate-800 text-sm">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-800 py-2 px-3 text-center w-12">No</th>
                            <th class="border border-slate-800 py-2 px-3 text-left w-1/5">Mata Pelajaran</th>
                            <th class="border border-slate-800 py-2 px-3 text-center w-20 font-bold">Nilai Akhir</th>
                            <th class="border border-slate-800 py-2 px-3 text-left">Capaian Kompetensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($grades) > 0)
                            @foreach ($grades as $index => $grade)
                                <tr>
                                    <td class="border border-slate-800 py-2 px-3 text-center align-top">{{ $index + 1 }}</td>
                                    <td class="border border-slate-800 py-2 px-3 align-top font-semibold">{{ $grade->subject->name ?? '-' }}</td>
                                    <td class="border border-slate-800 py-2 px-3 text-center align-top font-bold text-base">
                                        {{ $grade->final_score }}
                                    </td>
                                    <td class="border border-slate-800 py-2 px-3 align-top text-xs leading-relaxed text-justify">
                                        {{ $grade->notes ?: 'Siswa belum memiliki deskripsi capaian kompetensi.' }}
                                    </td>
                                </tr>
                            @endforeach
                            <!-- Rata-rata -->
                            <tr class="bg-slate-50 font-bold">
                                <td colspan="2" class="border border-slate-800 py-2 px-3 text-right">Rata-Rata Nilai:</td>
                                <td class="border border-slate-800 py-2 px-3 text-center text-lg">{{ $averageScore }}</td>
                                <td class="border border-slate-800 py-2 px-3"></td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="7"
                                    class="border border-slate-800 py-6 px-3 text-center text-slate-500 italic">Belum
                                    ada data nilai pada semester ini.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Tabel Presensi -->
            <div class="mb-12">
                <h3 class="font-bold text-slate-800 mb-2 uppercase text-sm border-b-2 border-slate-300 pb-1">B.
                    Ketidakhadiran</h3>
                <table class="w-1/2 border-collapse border border-slate-800 text-sm">
                    <tr>
                        <td class="border border-slate-800 py-2 px-3 w-48">Sakit</td>
                        <td class="border border-slate-800 py-2 px-3 text-center w-24">
                            {{ $attendanceSummary['Sakit'] }} hari</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-800 py-2 px-3">Izin</td>
                        <td class="border border-slate-800 py-2 px-3 text-center">{{ $attendanceSummary['Izin'] }} hari
                        </td>
                    </tr>
                    <tr>
                        <td class="border border-slate-800 py-2 px-3">Tanpa Keterangan (Alpa)</td>
                        <td class="border border-slate-800 py-2 px-3 text-center">{{ $attendanceSummary['Alpa'] }} hari
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Tanda Tangan -->
            <div class="flex justify-end">{{ $city ? $city . ', ' : '' }}{{ date('d F Y') }}</div>
            <div class="flex justify-between text-sm">
                <div class="text-center w-64 flex flex-col items-center">
                    <div class="h-12 mb-4 flex flex-col justify-end">
                        <p>Mengetahui,</p>
                        <p><b>Kepala Sekolah</b></p>
                    </div>

                    @php
                        $qrDataHeadmaster = "Dokumen resmi SIAKAD. Sah ditandatangani secara elektronik oleh Kepala Sekolah: {$headmasterName} (NIP: {$headmasterNip})";
                    @endphp

                    @if ($headmasterName !== '_________________________')
                        <div class="mb-2">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->generate($qrDataHeadmaster) !!}
                        </div>
                    @else
                        <div class="mb-20"></div>
                    @endif

                    <p class="font-bold underline">{{ $headmasterName }}</p>
                    <p>NIP. {{ $headmasterNip }}</p>
                </div>

                <div class="text-center w-64 flex flex-col items-center">
                    <div class="h-12 mb-4 flex flex-col justify-end">
                        <p></p>
                        <p><b>Wali Kelas</b></p>
                    </div>

                    @php
                        $waliKelas = $student->classroom->teacher->name ?? 'Wali Kelas';
                        $nip = $student->classroom->teacher->nip ?? '-';
                        $qrData =
                            "Dokumen resmi SIAKAD. Sah ditandatangani secara elektronik oleh Wali Kelas: {$waliKelas} (NIP: {$nip}) pada tanggal " .
                            date('d M Y');
                    @endphp

                    <div class="mb-2">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->generate($qrData) !!}
                    </div>

                    <p class="font-bold underline">{{ $waliKelas }}</p>
                    <p>NIP. {{ $nip }}</p>
                </div>
            </div>

        </div>
    @endif
</div>

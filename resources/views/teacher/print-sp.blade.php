<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Panggilan SP {{ $spLevel }} - {{ $student->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { font-size: 12pt; color: #000; }
            .no-print { display: none !important; }
            @page { margin: 2cm; }
        }
        body { font-family: 'Times New Roman', Times, serif; line-height: 1.5; color: #333; background: #f3f4f6; }
        .print-container { max-width: 21cm; margin: 2rem auto; background: white; padding: 2cm; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .kop-surat { border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; }
        .kop-surat img { width: 80px; height: 80px; object-fit: contain; }
        .kop-teks { flex: 1; text-align: center; }
        .judul-surat { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="no-print p-4 bg-slate-800 text-white flex justify-between items-center fixed w-full top-0 left-0">
        <div>Preview Surat Panggilan Orang Tua (SP {{ $spLevel }})</div>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded font-bold shadow">🖨️ Cetak Surat</button>
    </div>

    <div class="print-container mt-16 md:mt-8">
        <div class="kop-surat">
            @if($school->logo_url)
                <img src="{{ $school->logo_url }}" alt="Logo">
            @else
                <div style="width: 80px; height: 80px;"></div>
            @endif
            <div class="kop-teks">
                <h1 class="text-xl font-bold uppercase tracking-wider">{{ $school->name }}</h1>
                <p class="text-sm">Alamat: {{ $school->address ?? 'Jln. Pendidikan No.1, Kota Cerdas' }}</p>
                <p class="text-sm">Telepon: {{ $school->phone ?? '(021) 1234567' }} | Email: {{ $school->email ?? 'info@sekolah.sch.id' }}</p>
            </div>
            <div style="width: 80px; height: 80px;"></div>
        </div>

        <div class="flex justify-between text-sm mb-6">
            <div>
                <p>Nomor: ... / SP.{{ $spLevel }} / BK / {{ date('Y') }}</p>
                <p>Lampiran: 1 (satu) lembar</p>
                <p>Hal: <strong>Panggilan Orang Tua / Wali Murid</strong></p>
            </div>
            <div class="text-right">
                <p>{{ $school->city ?? 'Kota Cerdas' }}, {{ $date }}</p>
            </div>
        </div>

        <div class="mb-6">
            <p>Kepada Yth,</p>
            <p><strong>Bapak/Ibu Orang Tua / Wali Murid dari:</strong></p>
            <p>{{ $student->name }}</p>
            <p>di Tempat</p>
        </div>

        <div class="mb-4 text-justify">
            <p>Dengan hormat,</p>
            <p>Puji syukur kita panjatkan ke hadirat Tuhan Yang Maha Esa atas segala rahmat-Nya. Sehubungan dengan proses bimbingan dan pembinaan kedisiplinan siswa di {{ $school->name }}, kami memohon kehadiran Bapak/Ibu Orang Tua/Wali Murid dari:</p>
            
            <table class="my-4 ml-8 text-sm">
                <tr><td class="w-32 py-1">Nama Siswa</td><td class="px-2">:</td><td class="font-bold">{{ $student->name }}</td></tr>
                <tr><td class="py-1">NIS / NISN</td><td class="px-2">:</td><td>{{ $student->nis ?? '-' }} / {{ $student->nisn ?? '-' }}</td></tr>
                <tr><td class="py-1">Kelas</td><td class="px-2">:</td><td>{{ $student->classroom->name ?? '-' }}</td></tr>
                <tr><td class="py-1">Wali Kelas</td><td class="px-2">:</td><td>{{ $student->classroom->teacher->name ?? '-' }}</td></tr>
                <tr><td class="py-1">Total Poin</td><td class="px-2">:</td><td><span class="font-bold">{{ $totalPoints }}</span> (Mencapai batas Surat Peringatan {{ $spLevel }})</td></tr>
            </table>

            <p class="mb-2">Untuk menghadiri pertemuan yang akan dilaksanakan pada:</p>
            <table class="my-4 ml-8 text-sm">
                <tr><td class="w-32 py-1">Hari, Tanggal</td><td class="px-2">:</td><td>.................................................................</td></tr>
                <tr><td class="py-1">Waktu</td><td class="px-2">:</td><td>.................................................................</td></tr>
                <tr><td class="py-1">Tempat</td><td class="px-2">:</td><td>Ruang Bimbingan Konseling (BK)</td></tr>
                <tr><td class="py-1">Menemui</td><td class="px-2">:</td><td>Guru BK / Wali Kelas</td></tr>
            </table>

            <p>Mengingat pentingnya pertemuan ini demi kebaikan dan perkembangan putra/putri Bapak/Ibu, kami sangat mengharapkan kehadiran Bapak/Ibu <strong>tepat pada waktunya dan tidak dapat diwakilkan</strong>.</p>
            <p class="mt-4">Demikian surat panggilan ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.</p>
        </div>

        <div class="flex justify-between mt-12 text-center text-sm">
            <div>
                <p>Mengetahui,</p>
                <p><strong>Kepala Sekolah</strong></p>
                <div class="h-20"></div>
                <p class="underline font-bold">.......................................</p>
                <p>NIP. ..............................</p>
            </div>
            <div>
                <p>&nbsp;</p>
                <p><strong>Guru Bimbingan Konseling</strong></p>
                <div class="h-20"></div>
                <p class="underline font-bold">{{ auth()->user()->name }}</p>
                <p>NIP. ..............................</p>
            </div>
        </div>
        
        <div class="mt-20 border-t border-dashed border-gray-400 pt-8" style="page-break-before: always;">
            <h2 class="text-lg font-bold text-center mb-6 underline uppercase">Lampiran Riwayat Pelanggaran Siswa</h2>
            
            <table class="my-4 text-sm w-full">
                <tr><td class="w-32 py-1">Nama Siswa</td><td class="px-2">:</td><td class="font-bold">{{ $student->name }}</td></tr>
                <tr><td class="py-1">Kelas</td><td class="px-2">:</td><td>{{ $student->classroom->name ?? '-' }}</td></tr>
                <tr><td class="py-1">Total Poin</td><td class="px-2">:</td><td class="font-bold text-red-600">{{ $totalPoints }}</td></tr>
            </table>

            <table class="w-full border-collapse border border-slate-300 text-sm mt-4">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-slate-300 px-3 py-2 text-left w-10">No</th>
                        <th class="border border-slate-300 px-3 py-2 text-left w-24">Tanggal</th>
                        <th class="border border-slate-300 px-3 py-2 text-left">Jenis Pelanggaran</th>
                        <th class="border border-slate-300 px-3 py-2 text-center w-16">Poin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($student->violations as $idx => $vio)
                    <tr>
                        <td class="border border-slate-300 px-3 py-2 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-slate-300 px-3 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($vio->event_date)->format('d/m/Y') }}</td>
                        <td class="border border-slate-300 px-3 py-2">
                            {{ $vio->violationMaster->name ?? 'Lainnya' }}
                            @if($vio->notes)
                            <br><span class="text-xs text-gray-500 italic">Catatan: {{ $vio->notes }}</span>
                            @endif
                        </td>
                        <td class="border border-slate-300 px-3 py-2 text-center font-bold">{{ $vio->points }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="border border-slate-300 px-3 py-4 text-center italic text-gray-500">Belum ada catatan pelanggaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<div>
    <x-slot name="title">Dashboard Guru</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">Ringkasan kegiatan mengajar Anda hari ini.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Jadwal hari ini</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $todaySchedulesCount }}</p>
            <a href="{{ route('guru.schedules') }}" class="mt-4 inline-block text-sm font-medium text-teal-700 hover:text-teal-900">
                Lihat jadwal
            </a>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Kelas yang diajar</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $totalClassesCount }}</p>
            <a href="{{ route('guru.attendances') }}" class="mt-4 inline-block text-sm font-medium text-teal-700 hover:text-teal-900">
                Buka rekap kehadiran
            </a>
        </div>
    </div>

    {{-- HUB PERANGKAT PEMBELAJARAN AI & PANDUAN --}}
    <div class="mt-6 rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50/90 via-emerald-50/50 to-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-teal-100 pb-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-teal-600 px-2.5 py-0.5 text-2xs font-bold text-white uppercase tracking-wider">
                        ✨ Asisten AI Kurikulum Merdeka
                    </span>
                    <span class="text-xs font-semibold text-teal-800">
                        BSKAP 032/2024 & PPA 2024
                    </span>
                </div>
                <h2 class="text-lg font-bold text-slate-900">Pusat Otomasi Administrasi & Diferensiasi KBM</h2>
                <p class="text-xs text-slate-600">Akses cepat pembuatan 7 dokumen perangkat ajar, rekomendasi diferensiasi, paket remedial-pengayaan, dan panduan lengkap.</p>
            </div>
            <a href="{{ route('guru.learning-guide') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-teal-300 bg-white px-3.5 py-2 text-xs font-bold text-teal-800 shadow-xs hover:bg-teal-50 transition">
                <span>📖 Buka Panduan Lengkap</span>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 pt-4">
            <a href="{{ route('guru.learning-assistant') }}" class="group rounded-xl border border-slate-200 bg-white p-4 transition hover:border-teal-500 hover:shadow-md">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-700 text-lg font-bold group-hover:bg-teal-600 group-hover:text-white transition">
                        📄
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 group-hover:text-teal-700 transition">Generator 7 Dokumen AI</h3>
                        <p class="text-[11px] text-slate-500">Modul Ajar, ATP, Prota-Prosem, LKPD, Bahan Ajar, P5 & KKTP</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('guru.differentiation') }}" class="group rounded-xl border border-slate-200 bg-white p-4 transition hover:border-indigo-500 hover:shadow-md">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 text-lg font-bold group-hover:bg-indigo-600 group-hover:text-white transition">
                        💡
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 group-hover:text-indigo-700 transition">Rekomendasi Diferensiasi</h3>
                        <p class="text-[11px] text-slate-500">Analisis empiris nilai, 3 dimensi diferensiasi & grouping</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('guru.remedial-enrichment') }}" class="group rounded-xl border border-slate-200 bg-white p-4 transition hover:border-purple-500 hover:shadow-md">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-700 text-lg font-bold group-hover:bg-purple-600 group-hover:text-white transition">
                        🎯
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 group-hover:text-purple-700 transition">Remedial & Pengayaan</h3>
                        <p class="text-[11px] text-slate-500">Analisis CBT, scaffolding, HOTS challenges & ekspor CBT</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    @if($isEwsEnabled)
        <div class="mt-8 mb-4">
            <h2 class="text-xl font-bold text-slate-800">Early Warning System (EWS) Kedisiplinan</h2>
            <p class="text-sm text-slate-500">Ringkasan kondisi kedisiplinan dan jadwal konseling siswa.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Red Zone -->
            <div class="bg-white rounded-xl shadow-sm border border-rose-200 overflow-hidden">
                <div class="bg-rose-50 p-4 border-b border-rose-200">
                    <h3 class="font-bold text-rose-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Zona Merah (SP2)
                    </h3>
                    <p class="text-xs text-rose-600 mt-1">Siswa dengan poin >= 50</p>
                </div>
                <div class="p-4 divide-y divide-slate-100">
                    @forelse($redZoneStudents as $st)
                        <div class="py-2 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $st['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $st['nisn'] }}</p>
                            </div>
                            <span class="px-2.5 py-1 bg-rose-100 text-rose-800 text-xs font-black rounded-lg">
                                {{ $st['total_points'] }} Poin
                            </span>
                        </div>
                    @empty
                        <div class="py-4 text-center text-sm text-slate-500">
                            Tidak ada siswa di zona merah.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Yellow Zone -->
            <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
                <div class="bg-amber-50 p-4 border-b border-amber-200">
                    <h3 class="font-bold text-amber-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Zona Kuning (SP1)
                    </h3>
                    <p class="text-xs text-amber-600 mt-1">Siswa dengan poin 20 - 49</p>
                </div>
                <div class="p-4 divide-y divide-slate-100">
                    @forelse($yellowZoneStudents as $st)
                        <div class="py-2 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $st['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $st['nisn'] }}</p>
                            </div>
                            <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-black rounded-lg">
                                {{ $st['total_points'] }} Poin
                            </span>
                        </div>
                    @empty
                        <div class="py-4 text-center text-sm text-slate-500">
                            Tidak ada siswa di zona kuning.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Upcoming Counseling -->
            <div class="bg-white rounded-xl shadow-sm border border-indigo-200 overflow-hidden">
                <div class="bg-indigo-50 p-4 border-b border-indigo-200">
                    <h3 class="font-bold text-indigo-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Jadwal Konseling Mendatang
                    </h3>
                    <p class="text-xs text-indigo-600 mt-1">Sesi bimbingan yang dijadwalkan</p>
                </div>
                <div class="p-4 divide-y divide-slate-100">
                    @forelse($upcomingCounselings as $cr)
                        <div class="py-2">
                            <div class="flex justify-between items-start mb-1">
                                <p class="text-sm font-bold text-slate-800">{{ $cr['student']['name'] }}</p>
                                <span class="text-[10px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded">
                                    {{ \Carbon\Carbon::parse($cr['counseling_date'])->translatedFormat('d M') }} 
                                    {{ $cr['counseling_time'] ? \Carbon\Carbon::parse($cr['counseling_time'])->format('H:i') : '' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 line-clamp-1">{{ $cr['counseling_type'] }}</p>
                        </div>
                    @empty
                        <div class="py-4 text-center text-sm text-slate-500">
                            Belum ada jadwal bimbingan konseling mendatang.
                        </div>
                    @endforelse
                    
                    <div class="pt-3 text-center">
                        <a href="{{ route('guru.counseling') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                            Lihat Semua Jurnal BK &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- EWS Charts Row -->
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden p-5">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                Top 5 Pelanggaran Bulan Ini
            </h3>
            
            @if(count($topViolationsData['data'] ?? []) > 0)
                <div class="h-64 w-full">
                    <canvas id="violationsChart"></canvas>
                </div>
            @else
                <div class="py-12 text-center text-sm text-slate-500 bg-slate-50 rounded-lg border border-dashed border-slate-200">
                    Belum ada data pelanggaran bulan ini.
                </div>
            @endif
        </div>
    @endif
</div>

@if($isEwsEnabled && count($topViolationsData['data'] ?? []) > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        const ctx = document.getElementById('violationsChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topViolationsData['labels']) !!},
                    datasets: [{
                        label: 'Jumlah Kasus',
                        data: {!! json_encode($topViolationsData['data']) !!},
                        backgroundColor: '#6366f1',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        }
    });
</script>
@endpush
@endif


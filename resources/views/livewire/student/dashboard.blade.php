<div>
    <x-slot name="title">Dashboard Siswa</x-slot>

    <!-- Student Digital Identity Banner -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-indigo-950 rounded-2xl p-6 sm:p-8 text-white shadow-xl mb-8 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <svg class="w-72 h-72 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-2xl font-black text-white shadow-inner flex-shrink-0">
                    {{ substr(auth()->user()->name ?? 'S', 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">
                            NISN: {{ $student?->nisn ?? '-' }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-500/30 text-emerald-200 border border-emerald-400/30">
                            Siswa {{ $student?->status ?? 'Aktif' }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-white mt-1">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    
                    <p class="text-xs sm:text-sm text-indigo-200 mt-1 flex items-center gap-3 flex-wrap">
                        <span>🏫 <strong>{{ $classroomName }}</strong></span>
                        <span>•</span>
                        <span>👨‍🏫 Wali Kelas: <strong>{{ $homeroomTeacherName }}</strong></span>
                        <span>•</span>
                        <span>🗓️ TA: <strong>{{ $activeYearName }}</strong></span>
                    </p>
                </div>
            </div>

            <!-- Digital Student Card QR Badge -->
            <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-xl text-center self-stretch md:self-auto flex flex-row md:flex-col items-center justify-between gap-3">
                <div class="p-2 bg-white rounded-lg shadow-sm">
                    <svg class="w-10 h-10 text-slate-900" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v2h-3v-2zm-3 0h2v3h-2v-3zm3 3h3v5h-3v-5zm-6 2h3v3h-3v-3zm3 3h3v2h-3v-2z"/>
                    </svg>
                </div>
                <div class="text-left md:text-center">
                    <span class="text-[11px] font-bold text-indigo-200 block uppercase tracking-wider">Kartu Pelajar</span>
                    <span class="text-xs font-black text-white">ID: {{ $student?->nisn ?? '10001' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Indicators & Key Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Kehadiran Kumulatif -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between transition-all hover:shadow-md">
            <div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Kehadiran Kumulatif</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-800 mb-2">{{ $attendancePercentage }}%</div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mb-2">
                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ $attendancePercentage }}%;"></div>
                </div>
            </div>
            <a href="{{ route('siswa.attendances') }}" class="text-xs font-bold text-teal-700 hover:text-teal-900 mt-2 flex items-center gap-1">
                Lihat Rekap Presensi →
            </a>
        </div>

        <!-- Poin Kedisiplinan -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between transition-all hover:shadow-md">
            <div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Poin Kedisiplinan</span>
                    <div class="w-9 h-9 rounded-xl {{ $totalDemeritPoints > 20 ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600' }} flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                </div>
                <div class="text-3xl font-black {{ $totalDemeritPoints > 20 ? 'text-rose-600' : 'text-slate-800' }} mb-1">
                    {{ $totalDemeritPoints }} <span class="text-xs font-bold text-slate-400">Poin</span>
                </div>
                <div class="text-xs text-slate-500">
                    {{ $totalViolationsCount }} Pelanggaran Tercatat
                </div>
            </div>
            <span class="text-xs font-bold {{ $totalDemeritPoints == 0 ? 'text-emerald-600' : 'text-amber-600' }} mt-2">
                {{ $totalDemeritPoints == 0 ? '✓ Status Disiplin Baik' : '⚠️ Perhatikan Tata Tertib' }}
            </span>
        </div>

        <!-- Prestasi Diukir -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between transition-all hover:shadow-md">
            <div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Prestasi Diukir</span>
                    <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-800 mb-1">
                    {{ $totalAchievementsCount }} <span class="text-xs font-bold text-slate-400">Penghargaan</span>
                </div>
                <div class="text-xs text-slate-500">Akademik & Non-Akademik</div>
            </div>
            <span class="text-xs font-bold text-indigo-600 mt-2">🏆 Kejuaraan Terdaftar</span>
        </div>

        <!-- Ujian CBT Aktif -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between transition-all hover:shadow-md">
            <div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Ujian CBT Aktif</span>
                    <div class="w-9 h-9 rounded-xl bg-teal-100 flex items-center justify-center text-teal-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-800 mb-1">
                    {{ $activeExamsCount }} <span class="text-xs font-bold text-slate-400">Ujian</span>
                </div>
                <div class="text-xs text-slate-500">Dapat Diikuti Sekarang</div>
            </div>
            <span class="text-xs font-bold text-teal-600 mt-2">⏱️ Asesmen CBT Online</span>
        </div>
    </div>

    <!-- Main Grid: Today's Schedule & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Today's Schedule (2 Columns) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Jadwal Pelajaran Hari Ini</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Hari {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <a href="{{ route('siswa.schedules') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors">
                        Lihat Semua Hari →
                    </a>
                </div>

                @if(empty($todaySchedules))
                    <div class="text-center py-8 text-slate-500">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                            ☕
                        </div>
                        <p class="text-sm font-semibold">Tidak ada jadwal pelajaran untuk hari ini.</p>
                        <p class="text-xs text-slate-400 mt-1">Gunakan waktu luang untuk belajar mandiri atau mengerjakan tugas.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($todaySchedules as $sched)
                            @php
                                $now = \Carbon\Carbon::now()->format('H:i:s');
                                $isLive = ($now >= $sched['start_time'] && $now <= $sched['end_time']);
                            @endphp
                            <div class="p-4 rounded-xl border transition-all {{ $isLive ? 'bg-indigo-50/70 border-indigo-300 shadow-sm' : 'bg-slate-50/60 border-slate-200' }} flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl {{ $isLive ? 'bg-indigo-600 text-white font-black' : 'bg-white text-slate-700 font-bold border border-slate-200' }} flex items-center justify-center text-xs flex-shrink-0">
                                        Mapel
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-base font-bold text-slate-900">{{ $sched['subject']['name'] ?? 'Mata Pelajaran' }}</h4>
                                            @if($isLive)
                                                <span class="px-2 py-0.5 bg-indigo-600 text-white text-[10px] font-black rounded-full animate-pulse">
                                                    LIVE NOW
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-600 mt-0.5">
                                            👨‍🏫 {{ $sched['teacher']['name'] ?? 'Guru Pengampu' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="text-left sm:text-right bg-white sm:bg-transparent px-3 py-1.5 rounded-lg border sm:border-0 border-slate-200 w-full sm:w-auto">
                                    <div class="text-xs font-black text-slate-800">
                                        ⏰ {{ substr($sched['start_time'], 0, 5) }} - {{ substr($sched['end_time'], 0, 5) }}
                                    </div>
                                    <div class="text-[11px] font-semibold text-slate-500 mt-0.5">
                                        {{ $classroomName }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side: Quick Actions & Announcement -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">Akses Cepat Akademik</h3>
                
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('siswa.schedules') }}" class="p-3.5 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 rounded-xl transition-all flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0">
                            📅
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-800">Jadwal Pelajaran Mingguan</div>
                            <div class="text-xs text-slate-500">Lihat seluruh mata pelajaran minggu ini</div>
                        </div>
                    </a>

                    <a href="{{ route('siswa.attendances') }}" class="p-3.5 bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-200 rounded-xl transition-all flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                            📋
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-800">Rekapitulasi Kehadiran</div>
                            <div class="text-xs text-slate-500">Cek persentase absensi harian & mapel</div>
                        </div>
                    </a>

                    <a href="{{ route('siswa.grades') }}" class="p-3.5 bg-slate-50 hover:bg-purple-50 border border-slate-200 hover:border-purple-200 rounded-xl transition-all flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center flex-shrink-0">
                            🎓
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-800">Kartu Hasil Studi & Rapor</div>
                            <div class="text-xs text-slate-500">Lihat nilai capaian pembelajaran & cetak rapor</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- School Announcement Card -->
            <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl p-6 shadow-md border border-slate-800">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-lg">📢</span>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">Pengumuman Sekolah</h4>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Pastikan selalu menjaga kedisiplinan dan hadir tepat waktu. Selalu cek jadwal ujian CBT serta tugas pembelajaran secara berkala melalui Portal Siswa ini.
                </p>
            </div>
        </div>
    </div>
</div>

<div>
    <x-slot name="title">Dasbor Eksekutif</x-slot>

    <!-- Welcome Section -->
    <div class="relative bg-gradient-to-br from-slate-900 to-indigo-950 rounded-3xl shadow-2xl p-8 lg:p-10 mb-8 overflow-hidden text-white border border-slate-800">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 rounded-full bg-indigo-500 opacity-20 blur-3xl"></div>
        <div class="absolute bottom-0 right-40 -mb-20 w-60 h-60 rounded-full bg-blue-500 opacity-10 blur-2xl"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 text-indigo-200 text-xs font-bold tracking-widest uppercase mb-4 border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span> Kepala Sekolah
                </div>
                <h2 class="text-3xl md:text-4xl font-black mb-3 tracking-tight">Selamat Datang, {{ auth()->user()->name ?? 'Bapak/Ibu Kepala' }}!</h2>
                <p class="text-indigo-200 text-lg max-w-2xl font-light">Ringkasan aktivitas akademik, kedisiplinan, dan prestasi sekolah terintegrasi secara *real-time*.</p>
            </div>
            
            <!-- Quick Date & Semester Badge -->
            <div class="hidden lg:flex flex-col items-end gap-2 text-right bg-white/5 backdrop-blur-sm p-4 rounded-2xl border border-white/10">
                <span class="text-2xl font-bold">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                <span class="text-indigo-300 font-medium">TA. {{ $activeYear ? $activeYear->name.' ('.$activeYear->semester.')' : 'Belum Ada' }}</span>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Siswa Aktif -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <span class="px-2 py-1 bg-green-50 text-green-700 text-xs font-bold rounded-lg border border-green-100">Aktif</span>
            </div>
            <div>
                <h3 class="text-3xl font-black text-slate-800 mb-1">{{ number_format($totalStudents) }}</h3>
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-widest">Total Siswa</p>
            </div>
        </div>

        <!-- Total Guru -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-black text-slate-800 mb-1">{{ number_format($totalTeachers) }}</h3>
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-widest">Total Guru</p>
            </div>
        </div>

        <!-- Total Prestasi -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                </div>
                <span class="px-2 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg border border-amber-100">Semester Ini</span>
            </div>
            <div>
                <h3 class="text-3xl font-black text-slate-800 mb-1">{{ number_format($totalAchievements ?? 0) }}</h3>
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-widest">Prestasi Siswa</p>
            </div>
        </div>

        <!-- Total Pelanggaran -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <span class="px-2 py-1 bg-rose-50 text-rose-700 text-xs font-bold rounded-lg border border-rose-100">Semester Ini</span>
            </div>
            <div>
                <h3 class="text-3xl font-black text-slate-800 mb-1">{{ number_format($totalViolations ?? 0) }}</h3>
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-widest">Kasus Pelanggaran</p>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Kolom Kiri: KBM & Presensi -->
        <div class="xl:col-span-2 space-y-8">
            
            <!-- Monitoring KBM Hari Ini -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Monitoring KBM Berjalan</h3>
                            <p class="text-xs text-slate-500 font-medium">Jurnal Mengajar Guru Hari Ini</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    @if(isset($todayJournals) && count($todayJournals) > 0)
                        <div class="space-y-4">
                            @foreach($todayJournals as $journal)
                                <div class="flex flex-col sm:flex-row sm:items-center p-4 bg-slate-50 rounded-2xl hover:bg-indigo-50 border border-slate-100 transition-colors gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-extrabold px-2.5 py-1 bg-indigo-100 text-indigo-700 rounded-lg">{{ $journal->classroom->grade_level }} {{ $journal->classroom->name }}</span>
                                            <h4 class="font-bold text-slate-800 text-base">{{ $journal->subject->name }}</h4>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-600 mb-2">{{ $journal->teacher->name }}</p>
                                        <p class="text-sm text-slate-500 line-clamp-2 italic border-l-2 border-indigo-200 pl-3">"{{ $journal->topic_summary }}"</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Waktu Entri</div>
                                        <div class="font-bold text-slate-700">{{ $journal->created_at->format('H:i') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-slate-500 font-medium text-lg">Belum ada jurnal mengajar yang dientri hari ini.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Rekap Presensi Semester -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Tren Kehadiran Siswa</h3>
                        <p class="text-xs text-slate-500 font-medium">Semester Aktif</p>
                    </div>
                </div>

                @if($attendanceStats['recorded_students'] > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50">
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Total Terekam</span>
                            <span class="text-2xl font-black text-slate-800">{{ number_format($attendanceStats['recorded_students']) }}</span>
                        </div>
                        <div class="p-4 rounded-2xl border border-amber-100 bg-amber-50">
                            <span class="block text-xs font-bold text-amber-600 uppercase tracking-widest mb-1">Sakit</span>
                            <span class="text-2xl font-black text-amber-700">{{ number_format($attendanceStats['sick']) }}</span>
                        </div>
                        <div class="p-4 rounded-2xl border border-blue-100 bg-blue-50">
                            <span class="block text-xs font-bold text-blue-600 uppercase tracking-widest mb-1">Izin</span>
                            <span class="text-2xl font-black text-blue-700">{{ number_format($attendanceStats['permission']) }}</span>
                        </div>
                        <div class="p-4 rounded-2xl border border-rose-100 bg-rose-50">
                            <span class="block text-xs font-bold text-rose-600 uppercase tracking-widest mb-1">Alpa</span>
                            <span class="text-2xl font-black text-rose-700">{{ number_format($attendanceStats['absent']) }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-8 bg-slate-50 border border-slate-200 border-dashed rounded-2xl text-center">
                        <p class="text-slate-500 font-medium">Belum ada rekap presensi untuk semester aktif.</p>
                    </div>
                @endif
            </div>

        </div>

        <!-- Kolom Kanan: Kedisiplinan & Prestasi -->
        <div class="space-y-8">
            
            <!-- Kasus Pelanggaran Terbaru -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-rose-100 bg-rose-50/30 flex items-center justify-between">
                    <h3 class="text-base font-bold text-rose-900 flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                        Deteksi Dini Kasus
                    </h3>
                </div>
                
                <div class="p-6">
                    @if(isset($recentViolations) && count($recentViolations) > 0)
                        <div class="space-y-4">
                            @foreach($recentViolations as $violation)
                                <div class="pb-4 border-b border-slate-100 last:border-0 last:pb-0">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1 pr-3">
                                            <p class="font-bold text-slate-800 text-sm leading-tight">{{ $violation->student->name }}</p>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $violation->category == 'Berat' ? 'bg-rose-100 text-rose-700' : ($violation->category == 'Sedang' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                            {{ $violation->category }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-100">{{ $violation->violationMaster->name ?? 'Catatan Khusus' }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold mt-2 uppercase tracking-widest text-right">{{ \Carbon\Carbon::parse($violation->event_date)->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-slate-500">
                            <p class="text-sm font-medium">Tidak ada catatan pelanggaran terbaru.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Prestasi Terbaru -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-amber-100 bg-amber-50/30">
                    <h3 class="text-base font-bold text-amber-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                        Prestasi Terbaru
                    </h3>
                </div>
                
                <div class="p-6">
                    @if(isset($recentAchievements) && count($recentAchievements) > 0)
                        <div class="space-y-4">
                            @foreach($recentAchievements as $achievement)
                                <div class="pb-4 border-b border-slate-100 last:border-0 last:pb-0">
                                    <div class="mb-2">
                                        <p class="font-bold text-slate-800 text-sm leading-tight">{{ $achievement->student->name }}</p>
                                    </div>
                                    <h4 class="text-sm font-semibold text-amber-700">{{ $achievement->achievement_name }}</h4>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-xs text-slate-600 bg-slate-100 px-2 py-0.5 rounded font-medium">{{ $achievement->level }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ \Carbon\Carbon::parse($achievement->date)->format('d M Y') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-slate-500">
                            <p class="text-sm font-medium">Belum ada data prestasi terbaru.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<div>
    <x-slot name="title">Dasbor Akademik</x-slot>

    <!-- Welcome Section -->
    <div class="relative bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-xl p-8 mb-8 overflow-hidden text-white">
        <!-- Decorative background shapes -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white opacity-10 blur-3xl"></div>
        <div class="absolute bottom-0 right-32 -mb-16 w-48 h-48 rounded-full bg-white opacity-10 blur-2xl"></div>
        
        <div class="relative z-10">
            <h2 class="text-3xl font-bold mb-2">Selamat Datang, {{ auth()->user()->name ?? 'Admin' }}! 👋</h2>
            <p class="text-indigo-100 text-lg max-w-2xl">Pantau dan kelola seluruh aktivitas akademik hari ini melalui dasbor pintar terintegrasi SIAKAD.</p>
        </div>
    </div>

    <!-- Quick Stats Cards (Colorful) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Siswa -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-in-out"></div>
            <div class="relative z-10 flex-1">
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1">Siswa Aktif</p>
                <h3 class="text-3xl font-extrabold text-slate-800">{{ number_format($totalStudents) }}</h3>
            </div>
            <div class="relative z-10 w-14 h-14 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-200">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
        </div>
        
        <!-- Card 2: Guru -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-in-out"></div>
            <div class="relative z-10 flex-1">
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Guru</p>
                <h3 class="text-3xl font-extrabold text-slate-800">{{ number_format($totalTeachers) }}</h3>
            </div>
            <div class="relative z-10 w-14 h-14 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-200">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
        </div>

        <!-- Card 3: Kelas -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-in-out"></div>
            <div class="relative z-10 flex-1">
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1">Rombel / Kelas</p>
                <h3 class="text-3xl font-extrabold text-slate-800">{{ number_format($totalClassrooms) }}</h3>
            </div>
            <div class="relative z-10 w-14 h-14 rounded-full bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-200">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
        </div>

        <!-- Card 4: Mata Pelajaran -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-rose-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-in-out"></div>
            <div class="relative z-10 flex-1">
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1">Mata Pelajaran</p>
                <h3 class="text-3xl font-extrabold text-slate-800">{{ number_format($totalSubjects) }}</h3>
            </div>
            <div class="relative z-10 w-14 h-14 rounded-full bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center text-white shadow-lg shadow-rose-200">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
        </div>
    </div>

    <!-- Dua Kolom Bawah -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Kiri: Jadwal Hari Ini -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Jadwal Pelajaran Terdekat</h3>
                    <p class="text-sm text-slate-500">Hari ini: {{ $todayName }}</p>
                </div>
                <a href="{{ route('admin.academic.schedules') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                    Lihat Semua <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>

            @if(count($todaySchedules) > 0)
                <div class="space-y-4">
                    @foreach($todaySchedules as $schedule)
                        <div class="flex items-center p-4 bg-slate-50 rounded-xl hover:bg-indigo-50 border border-slate-100 transition-colors">
                            <div class="bg-white border border-indigo-100 text-indigo-700 rounded-lg p-2 text-center w-24 mr-4 shrink-0 shadow-sm">
                                <div class="font-extrabold text-sm">{{ date('H:i', strtotime($schedule->start_time)) }}</div>
                                <div class="text-[10px] uppercase tracking-widest text-indigo-400">Sampai</div>
                                <div class="font-extrabold text-sm">{{ date('H:i', strtotime($schedule->end_time)) }}</div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-slate-800 text-base">{{ $schedule->subject->name }}</h4>
                                <div class="flex items-center mt-1 text-sm">
                                    <span class="flex items-center text-slate-600 mr-4">
                                        <svg class="w-4 h-4 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        {{ $schedule->classroom->grade_level }} {{ $schedule->classroom->name }}
                                    </span>
                                    <span class="flex items-center text-slate-600">
                                        <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        {{ $schedule->teacher->name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-slate-50 border border-slate-100 border-dashed rounded-xl p-8 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-slate-500 font-medium">Tidak ada jadwal tersisa untuk hari ini.</p>
                </div>
            @endif
        </div>

        <!-- Kanan: Rekap presensi semester -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-slate-800">Rekap Presensi Semester</h3>
                <p class="text-sm text-slate-500">{{ $activeYear ? $activeYear->name.' / '.$activeYear->semester : 'Belum ada tahun ajaran aktif' }}</p>
            </div>

            @if($attendanceStats['recorded_students'] > 0)
                <div class="mb-5 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Siswa sudah direkap</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $attendanceStats['recorded_students'] }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-auto">
                    <div class="p-3 rounded-lg border border-amber-200 bg-amber-50">
                        <span class="block text-xs font-medium text-amber-800">Sakit</span>
                        <span class="text-xl font-semibold text-amber-700">{{ $attendanceStats['sick'] }}</span>
                    </div>
                    <div class="p-3 rounded-lg border border-blue-200 bg-blue-50">
                        <span class="block text-xs font-medium text-blue-800">Izin</span>
                        <span class="text-xl font-semibold text-blue-700">{{ $attendanceStats['permission'] }}</span>
                    </div>
                    <div class="p-3 rounded-lg border border-red-200 bg-red-50">
                        <span class="block text-xs font-medium text-red-800">Alpa</span>
                        <span class="text-xl font-semibold text-red-700">{{ $attendanceStats['absent'] }}</span>
                    </div>
                </div>
            @else
                <div class="flex-1 p-5 bg-slate-50 border border-slate-200 border-dashed rounded-lg">
                    <p class="text-slate-600 text-sm font-medium">Belum ada rekap presensi untuk semester aktif.</p>
                    <p class="mt-1 text-xs text-slate-500">Pilih kelas lalu masukkan jumlah sakit, izin, dan alpa.</p>
                    <a href="{{ route('admin.academic.attendances') }}" class="mt-4 inline-flex px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Buka Presensi
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

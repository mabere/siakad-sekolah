<div class="p-6">
    <!-- Header Area -->
    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Dasbor Kesiswaan</h1>
        <p class="text-slate-500 font-medium mt-1">Pemantauan pembinaan, pelanggaran, dan prestasi siswa.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Students -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalStudents) }}</h3>
                <p class="text-sm font-semibold text-slate-500 mt-1">Total Siswa Aktif</p>
            </div>
        </div>

        <!-- Violations -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-rose-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalViolations) }}</h3>
                <p class="text-sm font-semibold text-slate-500 mt-1">Kasus Pelanggaran (Semester Ini)</p>
            </div>
        </div>

        <!-- Achievements -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalAchievements) }}</h3>
                <p class="text-sm font-semibold text-slate-500 mt-1">Total Prestasi Siswa</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Violations -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Pelanggaran Terbaru</h2>
                        <p class="text-xs text-slate-500 font-medium">Early Warning System Kesiswaan</p>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentViolations as $violation)
                    <div class="p-6 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-slate-900">{{ $violation->student->name }}</h4>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full 
                                {{ $violation->violationMaster->points >= 50 ? 'bg-rose-100 text-rose-700' : ($violation->violationMaster->points >= 20 ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ $violation->violationMaster->points }} Poin
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 mb-2">{{ $violation->violationMaster->name }}</p>
                        <p class="text-xs text-slate-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ \Carbon\Carbon::parse($violation->event_date)->format('d M Y') }}
                        </p>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 font-medium">Belum ada data pelanggaran terbaru.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Achievements -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Prestasi Siswa Terbaru</h2>
                        <p class="text-xs text-slate-500 font-medium">Kebanggaan sekolah kita</p>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentAchievements as $achievement)
                    <div class="p-6 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-slate-900">{{ $achievement->student->name }}</h4>
                            <span class="text-xs font-bold text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full">{{ $achievement->level }}</span>
                        </div>
                        <p class="text-sm text-slate-600 font-semibold">{{ $achievement->name }}</p>
                        <p class="text-sm text-slate-500 mb-2">Peringkat: {{ $achievement->rank }}</p>
                        <p class="text-xs text-slate-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ \Carbon\Carbon::parse($achievement->date)->format('d M Y') }}
                        </p>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 font-medium">Belum ada data prestasi terbaru.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="p-6">
    <!-- Header Area -->
    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Dasbor Kurikulum</h1>
        <p class="text-slate-500 font-medium mt-1">Pemantauan aktivitas KBM dan penjadwalan akademik.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Teachers -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <span class="text-xs font-bold text-blue-600 bg-blue-100/50 px-2.5 py-1 rounded-full">Aktif</span>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalTeachers) }}</h3>
                <p class="text-sm font-semibold text-slate-500 mt-1">Total Guru</p>
            </div>
        </div>

        <!-- Subjects -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalSubjects) }}</h3>
                <p class="text-sm font-semibold text-slate-500 mt-1">Mata Pelajaran</p>
            </div>
        </div>

        <!-- Classrooms -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-teal-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center text-teal-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalClassrooms) }}</h3>
                <p class="text-sm font-semibold text-slate-500 mt-1">Total Kelas</p>
            </div>
        </div>

        <!-- Schedules Today -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <span class="text-xs font-bold text-amber-600 bg-amber-100/50 px-2.5 py-1 rounded-full">Hari Ini</span>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800">{{ $todaySchedules->count() }}</h3>
                <p class="text-sm font-semibold text-slate-500 mt-1">Jadwal Aktif</p>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Teaching Journals -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Jurnal Mengajar Hari Ini</h2>
                    <p class="text-xs text-slate-500 font-medium">Pemantauan KBM secara real-time</p>
                </div>
            </div>
            <a href="#" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Lihat Semua &rarr;</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($todayJournals as $journal)
                <div class="p-6 hover:bg-slate-50 transition-colors flex items-start gap-4">
                    <img src="{{ $journal->teacher->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($journal->teacher->name) }}" class="w-12 h-12 rounded-xl object-cover ring-2 ring-slate-100 shadow-sm" alt="Teacher">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-sm font-bold text-slate-900 truncate">{{ $journal->teacher->name }}</h4>
                            <span class="text-xs font-bold text-slate-500">{{ \Carbon\Carbon::parse($journal->created_at)->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-slate-600 font-semibold mb-2">{{ $journal->subject->name }} &bull; <span class="text-indigo-600">{{ $journal->classroom->name }}</span></p>
                        <p class="text-sm text-slate-500">{{ $journal->topic }}</p>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <p class="text-slate-500 font-medium">Belum ada jurnal KBM yang masuk hari ini.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

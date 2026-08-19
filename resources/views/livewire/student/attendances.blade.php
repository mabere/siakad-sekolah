<div>
    <x-slot name="title">Rekap Kehadiran Siswa</x-slot>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Rekapitulasi Kehadiran & Presensi Siswa</h2>
            <p class="text-sm text-slate-500 mt-1">Tahun Ajaran: {{ $activeYear ? $activeYear->name : '-' }}</p>
        </div>
    </div>

    <!-- Summary Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm text-center">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Persentase</span>
            <div class="text-2xl font-black text-emerald-600">{{ $dailyPercentage }}%</div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm text-center">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Hadir (Sesi/Mapel)</span>
            <div class="text-2xl font-black text-slate-800">{{ $dailyHadir }} <span class="text-xs text-slate-400">Kali</span></div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm text-center">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Sakit</span>
            <div class="text-2xl font-black text-amber-600">{{ $dailySakit }} <span class="text-xs text-slate-400">Hari</span></div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm text-center">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Izin</span>
            <div class="text-2xl font-black text-blue-600">{{ $dailyIzin }} <span class="text-xs text-slate-400">Hari</span></div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm text-center col-span-2 sm:col-span-1">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Alpa / Tanpa Ket</span>
            <div class="text-2xl font-black text-rose-600">{{ $dailyAlpa }} <span class="text-xs text-slate-400">Hari</span></div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="switchTab('daily')" type="button"
                class="{{ $activeTab === 'daily' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Rekap Kehadiran Harian (Wali Kelas)
            </button>

            <button wire:click="switchTab('subject')" type="button"
                class="{{ $activeTab === 'subject' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Presensi Per Pertemuan Mata Pelajaran
            </button>
        </nav>
    </div>

    <!-- TAB 1: Presensi Harian -->
    @if($activeTab === 'daily')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tahun Ajaran & Kelas</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Sakit</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Izin</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Alpa</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Catatan Wali Kelas</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($dailyLogs as $log)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">
                                    🏫 {{ $log->classroom?->grade_level }} {{ $log->classroom?->name }} ({{ $log->academicYear?->name }})
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-bold text-amber-600">
                                    {{ $log->sick ?? 0 }} Hari
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-bold text-blue-600">
                                    {{ $log->permission ?? 0 }} Hari
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-bold text-rose-600">
                                    {{ $log->absent ?? 0 }} Hari
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    {{ $log->notes ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                    Belum ada rekap presensi harian dari Wali Kelas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(is_object($dailyLogs) && method_exists($dailyLogs, 'links'))
                <div class="p-4 border-t border-slate-200">
                    {{ $dailyLogs->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- TAB 2: Presensi Mata Pelajaran -->
    @if($activeTab === 'subject')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal & Pertemuan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Mata Pelajaran & Guru</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status Presensi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Catatan Guru Mapel</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($subjectLogs as $slog)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-800">{{ \Carbon\Carbon::parse($slog->date)->translatedFormat('d M Y') }}</div>
                                    <div class="text-xs text-indigo-700 font-extrabold">Pertemuan Ke-{{ $slog->meeting_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900">{{ $slog->subject?->name }}</div>
                                    <div class="text-xs text-slate-500">👨‍🏫 {{ $slog->teacher?->name ?? 'Guru Mapel' }}</div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    @php
                                        $stStyles = [
                                            'Hadir' => 'bg-emerald-100 text-emerald-800 font-bold',
                                            'Sakit' => 'bg-amber-100 text-amber-800 font-bold',
                                            'Izin' => 'bg-blue-100 text-blue-800 font-bold',
                                            'Alpa' => 'bg-rose-100 text-rose-800 font-black',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 text-xs rounded-full {{ $stStyles[$slog->status] ?? 'bg-slate-100' }}">
                                        {{ $slog->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    {{ $slog->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                    Belum ada presensi mata pelajaran per pertemuan yang dicatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(is_object($subjectLogs) && method_exists($subjectLogs, 'links'))
                <div class="p-4 border-t border-slate-200">
                    {{ $subjectLogs->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

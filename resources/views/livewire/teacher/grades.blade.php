<div>
    <x-slot name="title">Input Nilai & Evaluasi Siswa</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Input Nilai & Evaluasi Pembelajaran</h2>
            <p class="text-sm text-slate-500 mt-1">Tahun Ajaran: <span class="font-semibold text-slate-700">{{ $activeYear ? $activeYear->name . ' (' . $activeYear->semester . ')' : '-' }}</span></p>
        </div>
        @if($selectedSchedule)
            <div class="flex items-center gap-2">
                <button type="button" 
                        wire:click="toggleLockGrades" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl border transition-colors shadow-xs {{ $isLocked ? 'bg-amber-50 border-amber-300 text-amber-800 hover:bg-amber-100' : 'bg-slate-100 border-slate-300 text-slate-700 hover:bg-slate-200' }}">
                    <span>{{ $isLocked ? '🔓 Buka Kunci Nilai' : '🔒 Kunci Nilai Kelas' }}</span>
                </button>
                <button type="button" 
                        wire:click="saveAllGrades" 
                        wire:loading.attr="disabled"
                        @if($isLocked) disabled @endif
                        class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveAllGrades">💾 Simpan Semua Nilai</span>
                    <span wire:loading wire:target="saveAllGrades" class="flex items-center gap-1.5">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        @endif
    </div>

    @if (session()->has('grade_success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-xs">
            <span class="flex items-center gap-2">
                <span>{{ session('grade_success') }}</span>
            </span>
        </div>
    @endif

    @if (session()->has('grade_error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-xs">
            <span>⚠️ {{ session('grade_error') }}</span>
        </div>
    @endif

    @if(!$activeYear)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-xl mb-6 shadow-sm">
            <p class="text-sm text-amber-700">Saat ini tidak ada Tahun Ajaran yang berstatus aktif.</p>
        </div>
    @elseif(empty($schedules))
        <div class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
            <div class="mx-auto w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z" transform="translate(0 6)"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak Ada Kelas Diajar</h3>
            <p class="text-slate-500">Anda tidak terdaftar sebagai pengajar di jadwal manapun pada tahun ajaran aktif ini.</p>
        </div>
    @else
        <!-- Filter Jadwal & Rombel -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="w-full md:max-w-md">
                    <label for="schedule" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Pilih Mata Pelajaran & Kelas</label>
                    <select id="schedule" wire:model.live="selectedScheduleId" class="block w-full pl-3.5 pr-10 py-2.5 text-sm border-slate-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 rounded-lg shadow-xs bg-white font-medium text-slate-800">
                        <option value="">-- Pilih Mata Pelajaran & Rombel --</option>
                        @foreach($schedules as $sched)
                            <option value="{{ $sched['id'] }}">
                                {{ $sched['subject']['name'] }} — Kelas {{ $sched['classroom']['grade_level'] }} {{ $sched['classroom']['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($selectedSchedule)
                    <div class="flex flex-wrap items-center gap-3 pt-2 md:pt-0">
                        <div class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <span class="text-2xs uppercase text-slate-400 font-bold block">Total Siswa</span>
                            <span class="text-sm font-bold text-slate-800">{{ $students->count() }} Siswa</span>
                        </div>
                        <div class="px-3.5 py-2 rounded-xl bg-teal-50 border border-teal-200 text-center">
                            <span class="text-2xs uppercase text-teal-600 font-bold block">Rata-Rata Kelas</span>
                            <span class="text-sm font-black text-teal-800">{{ $averageScore }}</span>
                        </div>
                        <div class="px-3.5 py-2 rounded-xl border {{ $isLocked ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }} text-center">
                            <span class="text-2xs uppercase font-bold block {{ $isLocked ? 'text-amber-600' : 'text-emerald-600' }}">Status Nilai</span>
                            <span class="text-xs font-bold">{{ $isLocked ? '🔒 Terkunci (Aman)' : '🔓 Mode Edit Terbuka' }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($selectedSchedule)
        <div>
            <!-- Banner Ringkasan Bobot & Skala Predikat Huruf (Pixel-Perfect High-End Design) -->
            <div class="bg-white/95 backdrop-blur-sm border border-teal-200/90 rounded-2xl p-4 sm:p-5 mb-6 shadow-xs hover:shadow-sm transition-all flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-teal-500 to-teal-700 text-white flex items-center justify-center font-bold text-lg shadow-sm ring-4 ring-teal-50 flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Ketentuan Pembobotan & Skala Skor Predikat Huruf</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-2xs font-bold bg-teal-100/90 text-teal-800 border border-teal-200">Kustomisasi Sekolah</span>
                        </div>
                        <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs">
                            <div class="flex items-center gap-1.5 text-slate-600">
                                <span class="text-slate-400 font-medium">Bobot:</span>
                                <span class="font-bold text-slate-800">Tugas {{ $weightTugas }}%</span>
                                <span class="text-slate-300">•</span>
                                <span class="font-bold text-slate-800">UTS {{ $weightUts }}%</span>
                                <span class="text-slate-300">•</span>
                                <span class="font-bold text-slate-800">UAS {{ $weightUas }}%</span>
                            </div>
                            <div class="hidden sm:inline text-slate-300">|</div>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-slate-400 font-medium">Skor Huruf:</span>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-extrabold text-2xs border border-emerald-200 shadow-2xs">A ≥ {{ $minScoreA }}</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 font-extrabold text-2xs border border-blue-200 shadow-2xs">B ≥ {{ $minScoreB }}</span>
                                <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-extrabold text-2xs border border-amber-200 shadow-2xs">C ≥ {{ $minScoreC }}</span>
                                <span class="px-2 py-0.5 rounded-md bg-orange-100 text-orange-800 font-extrabold text-2xs border border-orange-200 shadow-2xs">D ≥ {{ $minScoreD }}</span>
                                <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-extrabold text-2xs border border-rose-200 shadow-2xs">E &lt; {{ $minScoreD }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <button type="button" 
                            @click="$dispatch('open-weight-modal')" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full border-2 border-teal-500 hover:border-teal-600 bg-white hover:bg-teal-50 text-teal-800 hover:text-teal-900 text-xs font-bold shadow-xs hover:shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Atur Bobot & Predikat Huruf</span>
                    </button>
                </div>
            </div>

            @if (session()->has('weight_success'))
                <div class="mb-4 p-3.5 bg-emerald-50 text-emerald-800 text-xs font-semibold rounded-xl border border-emerald-200 shadow-xs flex items-center justify-between">
                    <span>{{ session('weight_success') }}</span>
                </div>
            @endif

            <!-- Tabel Input Nilai Siswa -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-10">No</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider min-w-[13rem]">Nama Siswa</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Tugas</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">UTS</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">UAS</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-xs font-bold text-teal-700 uppercase tracking-wider bg-teal-50/70">Nilai Akhir</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-xs font-bold text-teal-700 uppercase tracking-wider bg-teal-50/70">Predikat</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider min-w-[11rem]">TP Tertinggi</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider min-w-[11rem]">TP Terendah</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider min-w-[14rem]">Deskripsi Capaian</th>
                                <th scope="col" class="px-4 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($students as $idx => $student)
                                @php
                                    $sData = $gradeData[$student->id] ?? [];
                                    $finalScore = $sData['final_score'] ?? 0;
                                    $letter = $sData['grade_letter'] ?? 'E';
                                    
                                    $letterColor = match($letter) {
                                        'A' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'B' => 'bg-blue-100 text-blue-800 border-blue-300',
                                        'C' => 'bg-amber-100 text-amber-800 border-amber-300',
                                        'D' => 'bg-orange-100 text-orange-800 border-orange-300',
                                        default => 'bg-rose-100 text-rose-800 border-rose-300',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors {{ $isLocked ? 'bg-slate-50/40' : '' }}">
                                    <td class="px-4 py-3.5 whitespace-nowrap text-xs font-semibold text-slate-400">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <div class="text-sm font-bold text-slate-900">{{ $student->name }}</div>
                                        <div class="text-xs text-slate-500">NISN: {{ $student->nisn }}</div>
                                    </td>
                                    <td class="px-3 py-3.5 whitespace-nowrap text-center">
                                        <input type="number" 
                                               min="0" 
                                               max="100" 
                                               wire:model.live.debounce.400ms="gradeData.{{ $student->id }}.tugas" 
                                               @if($isLocked) disabled @endif
                                               class="w-16 p-2 rounded-lg border-slate-300 text-center font-semibold text-slate-800 shadow-xs focus:border-teal-500 focus:ring-teal-500 sm:text-xs disabled:bg-slate-100 disabled:text-slate-500">
                                    </td>
                                    <td class="px-3 py-3.5 whitespace-nowrap text-center">
                                        <input type="number" 
                                               min="0" 
                                               max="100" 
                                               wire:model.live.debounce.400ms="gradeData.{{ $student->id }}.uts" 
                                               @if($isLocked) disabled @endif
                                               class="w-16 p-2 rounded-lg border-slate-300 text-center font-semibold text-slate-800 shadow-xs focus:border-teal-500 focus:ring-teal-500 sm:text-xs disabled:bg-slate-100 disabled:text-slate-500">
                                    </td>
                                    <td class="px-3 py-3.5 whitespace-nowrap text-center">
                                        <input type="number" 
                                               min="0" 
                                               max="100" 
                                               wire:model.live.debounce.400ms="gradeData.{{ $student->id }}.uas" 
                                               @if($isLocked) disabled @endif
                                               class="w-16 p-2 rounded-lg border-slate-300 text-center font-semibold text-slate-800 shadow-xs focus:border-teal-500 focus:ring-teal-500 sm:text-xs disabled:bg-slate-100 disabled:text-slate-500">
                                    </td>
                                    <td class="px-3 py-3.5 whitespace-nowrap text-center bg-teal-50/40">
                                        <span class="px-2.5 py-1 rounded-lg bg-teal-100 text-teal-900 font-black text-xs border border-teal-200 block w-fit mx-auto">
                                            {{ number_format($finalScore, 1) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 whitespace-nowrap text-center bg-teal-50/40">
                                        <span class="px-2.5 py-0.5 rounded-full font-black text-xs border {{ $letterColor }} block w-fit mx-auto">
                                            {{ $letter }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <input type="text" 
                                               wire:model.live.debounce.1000ms="gradeData.{{ $student->id }}.tp_highest" 
                                               @if($isLocked) disabled @endif
                                               placeholder="Cth: struktur virus & vaksin" 
                                               class="w-full min-w-[11rem] p-2 rounded-lg border-slate-300 shadow-xs focus:border-teal-500 focus:ring-teal-500 sm:text-xs disabled:bg-slate-100">
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <input type="text" 
                                               wire:model.live.debounce.1000ms="gradeData.{{ $student->id }}.tp_lowest" 
                                               @if($isLocked) disabled @endif
                                               placeholder="Cth: daur biogeokimia" 
                                               class="w-full min-w-[11rem] p-2 rounded-lg border-slate-300 shadow-xs focus:border-teal-500 focus:ring-teal-500 sm:text-xs disabled:bg-slate-100">
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <textarea wire:model="gradeData.{{ $student->id }}.notes" 
                                                  rows="2" 
                                                  @if($isLocked) disabled @endif
                                                  placeholder="Deskripsi capaian rapor otomatis..." 
                                                  class="w-full min-w-[14rem] p-2 rounded-lg border-slate-300 shadow-xs focus:border-teal-500 focus:ring-teal-500 sm:text-2xs leading-tight disabled:bg-slate-100"></textarea>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-right text-xs">
                                        <button wire:click="saveGrade({{ $student->id }})" 
                                                type="button"
                                                @if($isLocked) disabled @endif
                                                class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-lg transition-colors shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">
                                            Simpan
                                        </button>
                                        @if(session()->has('success_'.$student->id))
                                            <span class="text-2xs text-emerald-600 font-bold block mt-1">✓ {{ session('success_'.$student->id) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-8 whitespace-nowrap text-center text-sm text-slate-500">
                                        Tidak ada data siswa pada rombel ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($students->isNotEmpty())
                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="text-xs text-slate-500 font-medium">
                            Menampilkan <span class="font-bold text-slate-700">{{ $students->count() }}</span> data siswa pada rombel ini.
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    wire:click="saveAllGrades" 
                                    wire:loading.attr="disabled"
                                    @if($isLocked) disabled @endif
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="saveAllGrades">💾 Simpan Semua Nilai ({{ $students->count() }} Siswa)</span>
                                <span wire:loading wire:target="saveAllGrades" class="flex items-center gap-1.5">
                                    <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Menyimpan Data Permanen...
                                </span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif
    @endif

    <!-- MODAL LENGKAP: PENGATURAN BOBOT & SKALA NILAI HURUF -->
    <div x-data="{ open: false }" 
         x-on:open-weight-modal.window="open = true" 
         x-on:keydown.escape.window="open = false"
         x-show="open" 
         style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" style="background-color: rgba(15, 23, 42, 0.6);" @click="open = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full z-10 border border-slate-100">
                <div class="bg-white p-6 sm:p-7">
                    <div class="flex items-start justify-between border-b border-slate-100 pb-4 mb-5">
                        <div class="flex items-start gap-3.5">
                            <div class="flex-shrink-0 flex items-center justify-center h-11 w-11 rounded-2xl bg-teal-100 text-teal-700 shadow-2xs">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900" id="modal-title">Pengaturan Bobot & Skala Skor Nilai Huruf</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Sesuaikan persentase perhitungan nilai akhir dan rentang skor predikat (KKTP/Kriteria Sekolah).</p>
                            </div>
                        </div>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    @if (session()->has('weight_error'))
                        <div class="mb-4 p-3 bg-red-50 text-red-700 text-xs font-semibold rounded-xl border border-red-200">
                            ⚠️ {{ session('weight_error') }}
                        </div>
                    @endif

                    <div class="space-y-6">
                        <!-- BAGIAN 1: BOBOT PERSENTASE -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">1. Bobot Komponen Nilai (%)</label>
                                <span class="text-xs font-bold {{ ($weightTugas + $weightUts + $weightUas) === 100 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    Total: {{ $weightTugas + $weightUts + $weightUas }}% {{ ($weightTugas + $weightUts + $weightUas) === 100 ? '✓ (Valid)' : '(Harus 100%)' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="bg-slate-50/80 p-3 rounded-2xl border border-slate-200/80">
                                    <label class="block text-2xs font-bold text-slate-600 mb-1">Tugas / Formatif</label>
                                    <div class="relative">
                                        <input type="number" min="0" max="100" wire:model.live="weightTugas" class="w-full pr-7 rounded-xl border-slate-300 shadow-xs focus:border-teal-500 focus:ring-teal-500 text-sm font-bold text-slate-800 py-1.5 px-3">
                                        <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                                    </div>
                                </div>
                                <div class="bg-slate-50/80 p-3 rounded-2xl border border-slate-200/80">
                                    <label class="block text-2xs font-bold text-slate-600 mb-1">UTS / Sumatif Tengah</label>
                                    <div class="relative">
                                        <input type="number" min="0" max="100" wire:model.live="weightUts" class="w-full pr-7 rounded-xl border-slate-300 shadow-xs focus:border-teal-500 focus:ring-teal-500 text-sm font-bold text-slate-800 py-1.5 px-3">
                                        <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                                    </div>
                                </div>
                                <div class="bg-slate-50/80 p-3 rounded-2xl border border-slate-200/80">
                                    <label class="block text-2xs font-bold text-slate-600 mb-1">UAS / Sumatif Akhir</label>
                                    <div class="relative">
                                        <input type="number" min="0" max="100" wire:model.live="weightUas" class="w-full pr-7 rounded-xl border-slate-300 shadow-xs focus:border-teal-500 focus:ring-teal-500 text-sm font-bold text-slate-800 py-1.5 px-3">
                                        <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BAGIAN 2: PRESET SKALA CEPAT -->
                        <div>
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">2. Pilihan Cepat Preset Skala KKTP Sekolah</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <button type="button" 
                                        wire:click="applyGradeScalePreset('standard')" 
                                        class="p-2.5 text-xs font-bold rounded-xl border text-center transition-all shadow-2xs {{ $minScoreA === 90 && $minScoreB === 80 && $minScoreC === 70 && $minScoreD === 60 ? 'bg-teal-50 border-teal-500 text-teal-900 ring-2 ring-teal-500/20' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                                    Standar (90/80/70/60)
                                </button>
                                <button type="button" 
                                        wire:click="applyGradeScalePreset('kktp_75')" 
                                        class="p-2.5 text-xs font-bold rounded-xl border text-center transition-all shadow-2xs {{ $minScoreA === 92 && $minScoreB === 83 && $minScoreC === 75 && $minScoreD === 65 ? 'bg-teal-50 border-teal-500 text-teal-900 ring-2 ring-teal-500/20' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                                    KKTP 75 (92/83/75/65)
                                </button>
                                <button type="button" 
                                        wire:click="applyGradeScalePreset('kktp_70')" 
                                        class="p-2.5 text-xs font-bold rounded-xl border text-center transition-all shadow-2xs {{ $minScoreA === 90 && $minScoreB === 80 && $minScoreC === 70 && $minScoreD === 60 ? 'bg-teal-50 border-teal-500 text-teal-900' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                                    KKTP 70 (90/80/70/60)
                                </button>
                                <button type="button" 
                                        wire:click="applyGradeScalePreset('kktp_65')" 
                                        class="p-2.5 text-xs font-bold rounded-xl border text-center transition-all shadow-2xs {{ $minScoreA === 88 && $minScoreB === 77 && $minScoreC === 65 && $minScoreD === 55 ? 'bg-teal-50 border-teal-500 text-teal-900 ring-2 ring-teal-500/20' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                                    KKTP 65 (88/77/65/55)
                                </button>
                            </div>
                        </div>

                        <!-- BAGIAN 3: SKOR MINIMAL PREDIKAT (CUSTOM) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">3. Batas Minimal Nilai Huruf (Kustom)</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <!-- Predikat A -->
                                <div class="p-3 rounded-2xl bg-emerald-50/70 border border-emerald-200 shadow-2xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-black text-emerald-800">Predikat A</span>
                                        <span class="text-2xs text-emerald-600 font-bold">Sangat Baik</span>
                                    </div>
                                    <div class="text-2xs text-slate-500 mb-1">Nilai Min:</div>
                                    <input type="number" min="1" max="100" wire:model.live="minScoreA" class="w-full rounded-xl border-emerald-300 shadow-xs focus:border-emerald-500 focus:ring-emerald-500 text-sm font-black text-emerald-900 py-1.5 px-2.5 bg-white">
                                    <div class="text-2xs text-emerald-700 font-semibold mt-1">Rentang: {{ $minScoreA }} - 100</div>
                                </div>

                                <!-- Predikat B -->
                                <div class="p-3 rounded-2xl bg-blue-50/70 border border-blue-200 shadow-2xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-black text-blue-800">Predikat B</span>
                                        <span class="text-2xs text-blue-600 font-bold">Baik</span>
                                    </div>
                                    <div class="text-2xs text-slate-500 mb-1">Nilai Min:</div>
                                    <input type="number" min="1" max="99" wire:model.live="minScoreB" class="w-full rounded-xl border-blue-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 text-sm font-black text-blue-900 py-1.5 px-2.5 bg-white">
                                    <div class="text-2xs text-blue-700 font-semibold mt-1">Rentang: {{ $minScoreB }} - {{ max(0, $minScoreA - 1) }}</div>
                                </div>

                                <!-- Predikat C -->
                                <div class="p-3 rounded-2xl bg-amber-50/70 border border-amber-200 shadow-2xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-black text-amber-800">Predikat C</span>
                                        <span class="text-2xs text-amber-600 font-bold">Cukup</span>
                                    </div>
                                    <div class="text-2xs text-slate-500 mb-1">Nilai Min:</div>
                                    <input type="number" min="1" max="98" wire:model.live="minScoreC" class="w-full rounded-xl border-amber-300 shadow-xs focus:border-amber-500 focus:ring-amber-500 text-sm font-black text-amber-900 py-1.5 px-2.5 bg-white">
                                    <div class="text-2xs text-amber-700 font-semibold mt-1">Rentang: {{ $minScoreC }} - {{ max(0, $minScoreB - 1) }}</div>
                                </div>

                                <!-- Predikat D -->
                                <div class="p-3 rounded-2xl bg-orange-50/70 border border-orange-200 shadow-2xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-black text-orange-800">Predikat D</span>
                                        <span class="text-2xs text-orange-600 font-bold">Kurang</span>
                                    </div>
                                    <div class="text-2xs text-slate-500 mb-1">Nilai Min:</div>
                                    <input type="number" min="1" max="97" wire:model.live="minScoreD" class="w-full rounded-xl border-orange-300 shadow-xs focus:border-orange-500 focus:ring-orange-500 text-sm font-black text-orange-900 py-1.5 px-2.5 bg-white">
                                    <div class="text-2xs text-orange-700 font-semibold mt-1">Rentang: {{ $minScoreD }} - {{ max(0, $minScoreC - 1) }}</div>
                                </div>
                            </div>

                            <div class="mt-3 p-3 bg-slate-50 rounded-2xl border border-slate-200 text-2xs text-slate-600 flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-rose-700">Predikat E (Sangat Kurang):</span> Otomatis berlaku untuk nilai di bawah <span class="font-bold text-slate-800">{{ $minScoreD }}</span> (Skor 0 s.d. {{ max(0, $minScoreD - 1) }}).
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                    <button @click="$wire.saveWeights().then(() => open = false)" type="button" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-xs px-5 py-2.5 bg-teal-600 text-sm font-bold text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Simpan Bobot & Skala Predikat
                    </button>
                    <button @click="open = false" type="button" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-xs px-5 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

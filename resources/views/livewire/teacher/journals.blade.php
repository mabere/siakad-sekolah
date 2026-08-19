<div>
    <x-slot name="title">Jurnal Mengajar KBM</x-slot>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Jurnal Mengajar / Agenda KBM</h2>
            <p class="text-sm text-slate-500 mt-1">Tahun Ajaran: {{ $activeYear ? $activeYear->name : '-' }}</p>
        </div>
        <div>
            <button wire:click="openFormModal" type="button" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Buat Jurnal KBM Baru
            </button>
        </div>
    </div>

    @if(!$activeYear)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-md mb-6 shadow-sm">
            <p class="text-sm text-amber-700">Saat ini tidak ada Tahun Ajaran yang berstatus aktif.</p>
        </div>
    @elseif(empty($schedules))
        <div class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
            <div class="mx-auto w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C20.832 18.477 19.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak Ada Kelas Diajar</h3>
            <p class="text-slate-500">Anda tidak terdaftar sebagai pengajar di jadwal manapun pada tahun ajaran aktif ini.</p>
        </div>
    @else
        <!-- Filter Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="filter-schedule" class="block text-sm font-medium text-slate-700 mb-2">Filter Mata Pelajaran & Kelas</label>
                    <select id="filter-schedule" wire:model.live="selectedScheduleId" class="block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm rounded-md shadow-sm">
                        <option value="">-- Semua Mata Pelajaran & Kelas --</option>
                        @foreach($schedules as $sched)
                            <option value="{{ $sched['id'] }}">
                                {{ $sched['subject']['name'] }} - Kelas {{ $sched['classroom']['grade_level'] }} {{ $sched['classroom']['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-date" class="block text-sm font-medium text-slate-700 mb-2">Filter Tanggal KBM</label>
                    <input type="date" id="filter-date" wire:model.live="dateFilter" class="block w-full px-3 py-2 border-slate-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm rounded-md shadow-sm">
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('journal_success'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 text-sm rounded-xl border border-green-200 shadow-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('journal_success') }}</span>
            </div>
        @endif

        @if (session()->has('journal_error'))
            <div class="mb-4 p-4 bg-red-50 text-red-700 text-sm rounded-xl border border-red-200 shadow-sm">
                {{ session('journal_error') }}
            </div>
        @endif

        <!-- List of Journals -->
        <div class="space-y-4 mb-6">
            @forelse($journals as $journal)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 transition-all hover:shadow-md">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 border-b border-slate-100 pb-4 mb-4">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="bg-teal-100 text-teal-800 text-xs font-bold px-2.5 py-1 rounded-md">
                                    Pertemuan Ke-{{ $journal->meeting_number }}
                                </span>
                                <span class="bg-slate-100 text-slate-700 text-xs font-semibold px-2.5 py-1 rounded-md">
                                    {{ \Carbon\Carbon::parse($journal->date)->translatedFormat('l, d F Y') }}
                                </span>
                                <span class="bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-semibold px-2.5 py-1 rounded-md">
                                    {{ $journal->learning_method }}
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800 mt-2">
                                {{ $journal->subject?->name }} — Kelas {{ $journal->classroom?->grade_level }} {{ $journal->classroom?->name }}
                            </h3>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2">
                            <button wire:click="openFormModal({{ $journal->id }})" type="button" class="px-3 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Edit
                            </button>
                            <button wire:click="deleteJournal({{ $journal->id }})" wire:confirm="Apakah Anda yakin ingin menghapus entri jurnal ini?" type="button" class="px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Hapus
                            </button>
                        </div>
                    </div>

                    <!-- Journal Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <!-- Left Column: Topic & Activities -->
                        <div class="space-y-3">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Materi / Capaian Pembelajaran (TP)</h4>
                                <p class="text-slate-800 bg-slate-50 p-3 rounded-lg border border-slate-100 leading-relaxed font-medium">
                                    {{ $journal->topic_summary }}
                                </p>
                            </div>

                            @if($journal->activities)
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Ringkasan Aktivitas KBM</h4>
                                    <p class="text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-100 leading-relaxed">
                                        {{ $journal->activities }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Right Column: Student Behavior & Attendance Widget -->
                        <div class="space-y-3">
                            <!-- Presensi Ringkas Widget -->
                            @php
                                $attSum = $attendanceSummaries[$journal->id] ?? ['Hadir' => 0, 'Sakit' => 0, 'Izin' => 0, 'Alpa' => 0];
                            @endphp
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Ringkasan Kehadiran Pertemuan</h4>
                                <div class="flex items-center gap-2 flex-wrap bg-slate-50 p-2.5 rounded-lg border border-slate-100 text-xs font-bold">
                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-800 rounded">Hadir: {{ $attSum['Hadir'] }}</span>
                                    <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded">Sakit: {{ $attSum['Sakit'] }}</span>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">Izin: {{ $attSum['Izin'] }}</span>
                                    <span class="px-2 py-1 bg-rose-100 text-rose-800 rounded">Alpa: {{ $attSum['Alpa'] }}</span>
                                </div>
                            </div>

                            @if($journal->student_notes)
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 mb-1">Catatan Sikap / Kejadian Siswa</h4>
                                    <p class="text-amber-900 bg-amber-50 p-3 rounded-lg border border-amber-200 leading-relaxed">
                                        {{ $journal->student_notes }}
                                    </p>
                                </div>
                            @endif

                            @if($journal->obstacles_and_solutions)
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Kendala & Tindak Lanjut</h4>
                                    <p class="text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-100 leading-relaxed">
                                        {{ $journal->obstacles_and_solutions }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
                    <p class="text-slate-500">Belum ada jurnal mengajar yang dicatat untuk filter ini.</p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $journals->links() }}
            </div>
        </div>
    @endif

    <!-- Modal Form Input Jurnal KBM -->
    <div x-data="{ open: @entangle('showFormModal') }" 
         x-show="open" 
         x-on:keydown.escape.window="open = false"
         style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" style="background-color: rgba(15, 23, 42, 0.5);" @click="open = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-10">
                <div class="bg-white p-6 sm:p-8">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800" id="modal-title">
                                {{ $editingJournalId ? 'Edit Jurnal Mengajar KBM' : 'Tambah Jurnal Mengajar KBM Baru' }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Catat agenda pembelajaran dan perkembangan KBM di kelas.</p>
                        </div>
                        <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="space-y-5 text-sm max-h-[70vh] overflow-y-auto pr-1">
                        <!-- Form Schedule -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Mata Pelajaran & Kelas <span class="text-rose-500">*</span></label>
                            <select wire:model.live="formScheduleId" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                <option value="">-- Pilih Mata Pelajaran & Kelas --</option>
                                @foreach($schedules as $sched)
                                    <option value="{{ $sched['id'] }}">
                                        {{ $sched['subject']['name'] }} - Kelas {{ $sched['classroom']['grade_level'] }} {{ $sched['classroom']['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('formScheduleId') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Tanggal KBM <span class="text-rose-500">*</span></label>
                                <input type="date" wire:model="date" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                @error('date') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Pertemuan Ke- <span class="text-rose-500">*</span></label>
                                <input type="number" min="1" max="200" wire:model="meetingNumber" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                @error('meetingNumber') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Metode KBM <span class="text-rose-500">*</span></label>
                                <select wire:model="learningMethod" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                    <option value="Tatap Muka (Luring)">Tatap Muka (Luring)</option>
                                    <option value="Daring">Daring</option>
                                    <option value="Praktikum/Laboratorium">Praktikum / Lab</option>
                                    <option value="Studi Lapangan">Studi Lapangan</option>
                                </select>
                                @error('learningMethod') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Topic Summary -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Materi Pembahasan / Capaian Pembelajaran (TP) <span class="text-rose-500">*</span></label>
                            <textarea wire:model="topicSummary" rows="3" placeholder="Tuliskan pokok materi / TP yang diajarkan pada pertemuan ini..." class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5"></textarea>
                            @error('topicSummary') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <!-- Activities -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Ringkasan Aktivitas KBM (Opsional)</label>
                            <textarea wire:model="activities" rows="2" placeholder="Contoh: Diskusi kelompok, presentasi materi bab 2, latihan soal..." class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5"></textarea>
                        </div>

                        <!-- Student Notes -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Catatan Kejadian / Sikap Siswa di Kelas (Opsional)</label>
                            <textarea wire:model="studentNotes" rows="2" placeholder="Contoh: Siswa B aktif memimpin diskusi, Siswa X tidak membawa modul..." class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5"></textarea>
                        </div>

                        <!-- Obstacles and Solutions -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Kendala & Tindak Lanjut (Opsional)</label>
                            <textarea wire:model="obstaclesAndSolutions" rows="2" placeholder="Contoh: Proyektor kelas mati, pembelajaran dialihkan ke papan tulis..." class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                    <button wire:click="saveJournal" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-5 py-2.5 bg-teal-600 text-sm font-bold text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Simpan Jurnal KBM
                    </button>
                    <button @click="open = false" type="button" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-5 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

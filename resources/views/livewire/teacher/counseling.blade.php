<div>
    <x-slot name="title">Bimbingan Konseling & Kedisiplinan</x-slot>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Bimbingan Konseling (BK) & Poin Kedisiplinan Siswa</h2>
            <p class="text-sm text-slate-500 mt-1">Tahun Ajaran: {{ $activeYear ? $activeYear->name : '-' }}</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="switchTab('violations')" type="button"
                class="{{ $activeTab === 'violations' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Poin Kedisiplinan & Pelanggaran
            </button>

            <button wire:click="switchTab('counseling_journals')" type="button"
                class="{{ $activeTab === 'counseling_journals' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                Jurnal Bimbingan Konseling (BK)
            </button>

            <button wire:click="switchTab('student_points_recap')" type="button"
                class="{{ $activeTab === 'student_points_recap' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Rekap Akumulasi Poin Siswa
            </button>
        </nav>
    </div>

    <!-- Flash Notifications -->
    @if (session()->has('violation_success'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 text-sm rounded-xl border border-green-200 shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('violation_success') }}</span>
        </div>
    @endif

    @if (session()->has('counseling_success'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 text-sm rounded-xl border border-green-200 shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('counseling_success') }}</span>
        </div>
    @endif

    <!-- TAB 1: Poin Kedisiplinan & Pelanggaran -->
    @if($activeTab === 'violations')
        <!-- Action & Filter Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <select wire:model.live="violationCategoryFilter" class="p-2 rounded-lg border-slate-300 text-xs font-semibold shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    <option value="">Semua Kategori Pelanggaran</option>
                    <option value="Ringan">Pelanggaran Ringan</option>
                    <option value="Sedang">Pelanggaran Sedang</option>
                    <option value="Berat">Pelanggaran Berat</option>
                </select>
            </div>

            <button wire:click="openViolationModal" type="button" class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors gap-2">
                + Catat Pelanggaran Siswa
            </button>
        </div>

        <!-- Table Violations -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Siswa</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Pelanggaran</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Poin Pelanggaran</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tindak Lanjut & Pelapor</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($violations as $v)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">
                                    {{ \Carbon\Carbon::parse($v->event_date)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900">{{ $v->student->name }}</div>
                                    <div class="text-xs text-slate-500">NISN: {{ $v->student->nisn }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900">{{ $v->violationMaster->name ?? 'Lainnya' }}</div>
                                    @if($v->notes)
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $v->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <span class="px-2.5 py-1 text-xs rounded-full border bg-slate-100 font-bold">
                                        {{ $v->category }} (+{{ $v->points }} Poin)
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <div class="font-bold text-slate-800">{{ $v->action_taken ?: 'Teguran' }}</div>
                                    <div class="text-slate-500 mt-0.5">Pelapor: {{ $v->reporterTeacher?->name ?? 'Sistem' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="deleteViolation({{ $v->id }})" class="text-rose-600 hover:text-rose-900 font-bold" onclick="return confirm('Hapus pelanggaran ini?')">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    Belum ada catatan pelanggaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $violations->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 2: Jurnal Bimbingan Konseling (BK) -->
    @if($activeTab === 'counseling_journals')
        <!-- Action & Filter Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex flex-wrap gap-3">
                <select wire:model.live="counselingTypeFilter" class="p-2 rounded-lg border-slate-300 text-xs font-semibold shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    <option value="">Semua Jenis Bimbingan</option>
                    <option value="Bimbingan Pribadi">Bimbingan Pribadi</option>
                    <option value="Bimbingan Belajar">Bimbingan Belajar</option>
                    <option value="Bimbingan Sosial">Bimbingan Sosial</option>
                    <option value="Bimbingan Karir">Bimbingan Karir</option>
                </select>

                <select wire:model.live="counselingStatusFilter" class="p-2 rounded-lg border-slate-300 text-xs font-semibold shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    <option value="">Semua Status Penanganan</option>
                    <option value="Proses">Dalam Proses</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Rujukan">Rujukan / Referral</option>
                </select>
            </div>

            <button wire:click="openCounselingModal" type="button" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors gap-2">
                + Tambah Sesi Konseling Baru
            </button>
        </div>

        <!-- Table Counseling -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Siswa & Guru Konselor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Permasalahan & Solusi</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Jenis Layanan BK</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($counselings as $c)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-800">{{ $c->student->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $c->counselorTeacher?->name ?? 'BK' }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-700 leading-relaxed max-w-xs">
                                    <div class="font-bold text-slate-900 mb-0.5">Masalah:</div>
                                    {{ $c->problem_description }}
                                    
                                    @if($c->solution_plan)
                                        <div class="mt-2 font-bold text-slate-900 mb-0.5">Solusi:</div>
                                        {{ $c->solution_plan }}
                                    @endif
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center text-xs">
                                    <div class="font-bold text-slate-800 mb-1">{{ \Carbon\Carbon::parse($c->counseling_date)->translatedFormat('d M Y') }}</div>
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-extrabold text-[11px] rounded border border-indigo-200">
                                        {{ $c->counseling_type }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 text-xs rounded-full border bg-slate-100 font-bold">
                                        {{ $c->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="deleteCounseling({{ $c->id }})" class="text-rose-600 hover:text-rose-900 font-bold" onclick="return confirm('Hapus bimbingan ini?')">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    Belum ada data bimbingan konseling.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $counselings->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 3: Rekap Akumulasi Poin Siswa -->
    @if($activeTab === 'student_points_recap')
        <!-- Search & Filter Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <input type="text" wire:model.live.debounce.300ms="recapSearch" placeholder="Cari nama atau NISN siswa..." class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-teal-500 focus:ring-teal-500 min-w-[240px] py-2 px-3">
                <select wire:model.live="recapClassroomId" class="rounded-lg border-slate-300 text-xs font-semibold shadow-sm focus:border-teal-500 focus:ring-teal-500 py-2 px-3">
                    <option value="">Semua Kelas</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}">Kelas {{ $cls->grade_level }} {{ $cls->name }}</option>
                    @endforeach
                </select>
                <button wire:click="exportCsv" type="button" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors gap-2 justify-center">
                    <span>📊</span> Ekspor CSV
                </button>
            </div>

            <div class="text-xs text-slate-500 font-medium">
                Siswa dengan poin > 25 memerlukan perhatian khusus dari Guru BK & Wali Kelas.
            </div>
        </div>

        <!-- Table Recap -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Siswa & NISN</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pelanggaran</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Total Poin Kedisiplinan</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sesi BK</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status Pemantauan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($studentRecap as $st)
                            @php
                                $pts = $st->total_points ?? 0;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900">{{ $st->name }}</div>
                                    <div class="text-xs text-slate-500">NISN: {{ $st->nisn }}</div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center text-xs font-bold text-slate-800">
                                    {{ $st->total_violations }} Kasus
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 text-xs font-black rounded-lg border {{ $pts >= 50 ? 'bg-rose-100 text-rose-900 border-rose-300' : ($pts >= 20 ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-slate-100 text-slate-700 border-slate-200') }}">
                                        {{ $pts }} Poin
                                    </span>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center text-xs font-bold text-teal-800">
                                    {{ $st->total_counselings }} Sesi BK
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($pts >= 50)
                                        <div class="flex flex-col items-center justify-center gap-1.5">
                                            <span class="px-3 py-1 text-[11px] font-extrabold rounded-full bg-rose-600 text-white shadow-sm w-full">
                                                ?? Panggilan Ortu (SP)
                                            </span>
                                            <a href="{{ route('guru.counseling.sp', $st->id) }}" target="_blank" class="px-3 py-1 text-[11px] font-bold rounded border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 shadow-sm transition-colors w-full flex justify-center items-center gap-1" title="Cetak Surat Panggilan">
                                                <span>???</span> Cetak SP
                                            </a>
                                        </div>
                                    @elseif($pts >= 20)
                                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-500 text-white shadow-sm block">
                                            ?? Peringatan 1
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                            ? Aman
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    Belum ada data rekapitulasi poin.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $studentRecap->links() }}
            </div>
        </div>
    @endif

    <!-- MODAL 1: Form Input Pelanggaran Siswa -->
    <div x-data="{ open: @entangle('showViolationModal') }" 
         x-show="open" 
         x-on:keydown.escape.window="open = false"
         style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" style="background-color: rgba(15, 23, 42, 0.5);" @click="open = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full z-10">
                <div class="bg-white p-6 sm:p-8">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 border-b pb-4">
                        {{ $editingViolationId ? 'Edit Catatan Pelanggaran' : 'Catat Pelanggaran Siswa Baru' }}
                    </h3>

                    <div class="space-y-5 text-sm">
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Pilih Siswa <span class="text-rose-500">*</span></label>
                            <select wire:model="violationStudentId" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                <option value="">-- Pilih Siswa --</option>
                                @foreach(App\Models\Student::where('school_id', app(App\Support\CurrentSchool::class)->id())->where('status', 'Aktif')->orderBy('name')->get() as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }} (NISN: {{ $st->nisn }})</option>
                                @endforeach
                            </select>
                            @error('violationStudentId') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Nama Pelanggaran / Kasus <span class="text-rose-500">*</span></label>
                            <select wire:model.live="violationMasterId" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                <option value="">-- Pilih Jenis Pelanggaran --</option>
                                @foreach($violationMasters as $vm)
                                    <option value="{{ $vm->id }}">{{ $vm->code ? $vm->code.' - ' : '' }}{{ $vm->name }}</option>
                                @endforeach
                            </select>
                            @error('violationMasterId') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Kategori <span class="text-rose-500">*</span></label>
                                <select wire:model.live="violationCategory" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                    <option value="Ringan">Ringan (+5 Poin)</option>
                                    <option value="Sedang">Sedang (+15 Poin)</option>
                                    <option value="Berat">Berat (+35 Poin)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Bobot Poin <span class="text-rose-500">*</span></label>
                                <input type="number" min="1" max="500" wire:model="violationPoints" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5 font-bold text-rose-700" {{ $violationMasterId ? 'readonly' : '' }}>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Tanggal Kejadian</label>
                                <input type="date" wire:model="violationEventDate" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Tindak Lanjut</label>
                                <input type="text" wire:model="violationActionTaken" placeholder="Misal: Teguran Lisan / SP1" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Catatan Tambahan (Opsional)</label>
                            <textarea wire:model="violationNotes" rows="2" placeholder="Detail kronologi singkat kejadian..." class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                    <button wire:click="saveViolation" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-5 py-2.5 bg-rose-600 text-sm font-bold text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 sm:w-auto">
                        Simpan Pelanggaran
                    </button>
                    <button @click="open = false" type="button" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-5 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Form Input Jurnal Bimbingan Konseling -->
    <div x-data="{ open: @entangle('showCounselingModal') }" 
         x-show="open" 
         x-on:keydown.escape.window="open = false"
         style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" style="background-color: rgba(15, 23, 42, 0.5);" @click="open = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-10">
                <div class="bg-white p-6 sm:p-8">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 border-b pb-4">
                        {{ $editingCounselingId ? 'Edit Jurnal BK' : 'Tambah Sesi Bimbingan Konseling Baru' }}
                    </h3>

                    <div class="space-y-5 text-sm max-h-[70vh] overflow-y-auto pr-1">
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Pilih Siswa <span class="text-rose-500">*</span></label>
                            <select wire:model="counselingStudentId" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                <option value="">-- Pilih Siswa --</option>
                                @foreach(App\Models\Student::where('school_id', app(App\Support\CurrentSchool::class)->id())->where('status', 'Aktif')->orderBy('name')->get() as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }} (NISN: {{ $st->nisn }})</option>
                                @endforeach
                            </select>
                            @error('counselingStudentId') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Jenis Layanan BK <span class="text-rose-500">*</span></label>
                                <select wire:model="counselingType" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                    <option value="Bimbingan Pribadi">Bimbingan Pribadi</option>
                                    <option value="Bimbingan Belajar">Bimbingan Belajar</option>
                                    <option value="Bimbingan Sosial">Bimbingan Sosial</option>
                                    <option value="Bimbingan Karir">Bimbingan Karir</option>
                                </select>
                            </div>

                            <div class="col-span-1 sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-2">Tanggal Sesi BK</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" wire:model="counselingDate" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                    <input type="time" wire:model="counselingTime" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                </div>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Status Penanganan <span class="text-rose-500">*</span></label>
                                <select wire:model="counselingStatus" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5 font-bold">
                                    <option value="Proses">Dalam Proses</option>
                                    <option value="Selesai">Selesai</option>
                                    <option value="Rujukan">Rujukan / Referral</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Deskripsi Permasalahan / Keluhan <span class="text-rose-500">*</span></label>
                            <textarea wire:model="counselingProblem" rows="3" placeholder="Tuliskan pokok permasalahan atau kendala siswa..." class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5"></textarea>
                            @error('counselingProblem') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Solusi & Rencana Tindak Lanjut</label>
                            <textarea wire:model="counselingSolution" rows="3" placeholder="Tuliskan langkah solusi, bimbingan, atau tindak lanjut..." class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                    <button wire:click="saveCounseling" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-5 py-2.5 bg-teal-600 text-sm font-bold text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Simpan Jurnal BK
                    </button>
                    <button @click="open = false" type="button" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-5 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


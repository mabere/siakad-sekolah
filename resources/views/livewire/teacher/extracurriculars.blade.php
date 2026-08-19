<div>
    <x-slot name="title">Ekskul & Prestasi Siswa</x-slot>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Pembina Ekstrakurikuler & Prestasi Siswa</h2>
            <p class="text-sm text-slate-500 mt-1">Tahun Ajaran: {{ $activeYear ? $activeYear->name : '-' }}</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="switchTab('ekskul_grading')" type="button"
                class="{{ $activeTab === 'ekskul_grading' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Penilaian Ekstrakurikuler
            </button>

            <button wire:click="switchTab('achievements')" type="button"
                class="{{ $activeTab === 'achievements' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                Pencatatan Prestasi Siswa
            </button>

            <button wire:click="switchTab('master_ekskul')" type="button"
                class="{{ $activeTab === 'master_ekskul' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Kelola Master Ekskul
            </button>
        </nav>
    </div>

    <!-- Flash Notifications -->
    @if (session()->has('ekskul_success'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 text-sm rounded-xl border border-green-200 shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('ekskul_success') }}</span>
        </div>
    @endif

    @if (session()->has('ekskul_error'))
        <div class="mb-4 p-4 bg-red-50 text-red-700 text-sm rounded-xl border border-red-200 shadow-sm">
            {{ session('ekskul_error') }}
        </div>
    @endif

    @if (session()->has('achievement_success'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 text-sm rounded-xl border border-green-200 shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('achievement_success') }}</span>
        </div>
    @endif

    <!-- TAB 1: Penilaian Ekstrakurikuler -->
    @if($activeTab === 'ekskul_grading')
        @if(!$activeYear)
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-md mb-6 shadow-sm">
                <p class="text-sm text-amber-700">Saat ini tidak ada Tahun Ajaran yang berstatus aktif.</p>
            </div>
        @elseif($extracurriculars->isEmpty())
            <div class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
                <div class="mx-auto w-20 h-20 bg-teal-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Ekstrakurikuler Terdaftar</h3>
                <p class="text-slate-500 max-w-md mx-auto mb-6 text-sm">Tambahkan kegiatan ekstrakurikuler terlebih dahulu pada tab "Kelola Master Ekskul".</p>
                <button wire:click="switchTab('master_ekskul')" type="button" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors gap-2">
                    + Kelola Master Ekskul
                </button>
            </div>
        @else
            <!-- Filter Bar & Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="w-full sm:w-auto min-w-[280px]">
                        <label for="select-ekskul" class="block text-sm font-medium text-slate-700 mb-2">Pilih Kegiatan Ekstrakurikuler</label>
                        <select id="select-ekskul" wire:model.live="selectedEkskulId" class="block w-full pl-3 pr-10 py-2.5 text-base border-slate-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm rounded-md shadow-sm font-bold text-slate-800">
                            @foreach($extracurriculars as $eks)
                                <option value="{{ $eks->id }}">
                                    {{ $eks->name }} ({{ $eks->category }}) — Pembina: {{ $eks->teacher?->name ?? 'Belum Ditentukan' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($selectedEkskul)
                        <div>
                            <button wire:click="openAddMemberModal" type="button" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors gap-2">
                                + Tambah Anggota Siswa
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            @if($selectedEkskul)
                <!-- Members Table -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Daftar Anggota {{ $selectedEkskul->name }} ({{ $members->count() }} Siswa)
                        </span>
                        <span class="text-xs text-slate-500">Nilai predikat akan langsung dicetak pada Rapor Siswa</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Predikat Nilai</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Catatan Deskripsi Keaktifan</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @forelse($members as $mem)
                                    @php
                                        $currentGrade = $memberData[$mem->id]['grade'] ?? 'A';
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-slate-900">{{ $mem->student?->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $mem->student?->nisn }}</div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="inline-flex rounded-md shadow-sm" role="group">
                                                <!-- A (Sangat Baik) -->
                                                <button type="button"
                                                    wire:click="$set('memberData.{{ $mem->id }}.grade', 'A')"
                                                    class="px-3 py-1.5 text-xs font-bold transition-all border rounded-l-lg {{ $currentGrade === 'A' ? 'bg-emerald-600 text-white border-emerald-600 shadow-inner font-black' : 'bg-white text-emerald-700 hover:bg-emerald-50 border-slate-300' }}">
                                                    A (Sangat Baik)
                                                </button>
                                                <!-- B (Baik) -->
                                                <button type="button"
                                                    wire:click="$set('memberData.{{ $mem->id }}.grade', 'B')"
                                                    class="px-3 py-1.5 text-xs font-bold transition-all border-t border-b border-r {{ $currentGrade === 'B' ? 'bg-blue-600 text-white border-blue-600 shadow-inner font-black' : 'bg-white text-blue-700 hover:bg-blue-50 border-slate-300' }}">
                                                    B (Baik)
                                                </button>
                                                <!-- C (Cukup) -->
                                                <button type="button"
                                                    wire:click="$set('memberData.{{ $mem->id }}.grade', 'C')"
                                                    class="px-3 py-1.5 text-xs font-bold transition-all border-t border-b border-r {{ $currentGrade === 'C' ? 'bg-amber-500 text-white border-amber-500 shadow-inner font-black' : 'bg-white text-amber-700 hover:bg-amber-50 border-slate-300' }}">
                                                    C (Cukup)
                                                </button>
                                                <!-- D (Kurang) -->
                                                <button type="button"
                                                    wire:click="$set('memberData.{{ $mem->id }}.grade', 'D')"
                                                    class="px-3 py-1.5 text-xs font-bold transition-all border-t border-b border-r rounded-r-lg {{ $currentGrade === 'D' ? 'bg-rose-600 text-white border-rose-600 shadow-inner font-black' : 'bg-white text-rose-700 hover:bg-rose-50 border-slate-300' }}">
                                                    D (Kurang)
                                                </button>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="text" wire:model="memberData.{{ $mem->id }}.description" placeholder="Deskripsi keaktifan anggota..." class="w-full p-2 rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="removeMemberFromEkskul({{ $mem->id }})" wire:confirm="Keluarkan siswa dari ekstrakurikuler ini?" type="button" class="text-rose-600 hover:text-rose-900 font-semibold text-xs">
                                                Keluarkan
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                            Belum ada anggota siswa di kegiatan ekstrakurikuler ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(!$members->isEmpty())
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                            <button wire:click="saveEkskulGrades" type="button" class="inline-flex items-center px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Simpan Nilai & Deskripsi Ekskul
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        @endif
    @endif

    <!-- TAB 2: Pencatatan Prestasi Siswa -->
    @if($activeTab === 'achievements')
        <!-- Action & Filter Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex flex-wrap gap-3 w-full md:w-auto">
                <div>
                    <select wire:model.live="achievementCategoryFilter" class="p-2 rounded-md border-slate-300 text-xs font-semibold shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Semua Kategori (Akademik & Non)</option>
                        <option value="Akademik">Akademik</option>
                        <option value="Non-Akademik">Non-Akademik</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="achievementLevelFilter" class="p-2 rounded-md border-slate-300 text-xs font-semibold shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Semua Tingkat Kejuaraan</option>
                        <option value="Kecamatan">Tingkat Kecamatan</option>
                        <option value="Kabupaten/Kota">Tingkat Kabupaten/Kota</option>
                        <option value="Provinsi">Tingkat Provinsi</option>
                        <option value="Nasional">Tingkat Nasional</option>
                        <option value="Internasional">Tingkat Internasional</option>
                    </select>
                </div>
            </div>

            <button wire:click="openAchievementModal" type="button" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors gap-2">
                + Tambah Prestasi Siswa Baru
            </button>
        </div>

        <!-- Table Achievements -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Siswa & Kelas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Kejuaraan / Event</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori & Tingkat</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Peringkat / Hasil</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Penyelenggara & Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($achievements as $ach)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $ach->student?->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $ach->student?->nisn }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-800">{{ $ach->event_name }}</div>
                                    @if($ach->notes)
                                        <div class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $ach->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $ach->category === 'Akademik' ? 'bg-indigo-100 text-indigo-800' : 'bg-teal-100 text-teal-800' }}">
                                            {{ $ach->category }}
                                        </span>
                                        @php
                                            $lvlStyles = [
                                                'Kecamatan' => 'bg-slate-100 text-slate-700',
                                                'Kabupaten/Kota' => 'bg-amber-100 text-amber-800',
                                                'Provinsi' => 'bg-blue-100 text-blue-800',
                                                'Nasional' => 'bg-emerald-100 text-emerald-800',
                                                'Internasional' => 'bg-purple-100 text-purple-800',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded {{ $lvlStyles[$ach->level] ?? 'bg-slate-100' }}">
                                            {{ $ach->level }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 bg-amber-50 text-amber-900 border border-amber-200 font-extrabold text-xs rounded-lg">
                                        🏆 {{ $ach->rank }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                    <div>{{ $ach->organizer ?? '-' }}</div>
                                    <div class="text-slate-400 mt-0.5">{{ $ach->event_date ? \Carbon\Carbon::parse($ach->event_date)->translatedFormat('d M Y') : '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="openAchievementModal({{ $ach->id }})" type="button" class="text-amber-600 hover:text-amber-900 font-semibold text-xs mr-3">
                                        Edit
                                    </button>
                                    <button wire:click="deleteAchievement({{ $ach->id }})" wire:confirm="Hapus data prestasi siswa ini?" type="button" class="text-rose-600 hover:text-rose-900 font-semibold text-xs">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                    Belum ada data prestasi siswa yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $achievements->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 3: Kelola Master Ekskul -->
    @if($activeTab === 'master_ekskul')
        <div class="mb-6 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Daftar Kegiatan Ekstrakurikuler Sekolah</h3>
            <button wire:click="openEkskulModal" type="button" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors gap-2">
                + Tambah Kegiatan Ekskul Baru
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            @forelse($extracurriculars as $eks)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between transition-all hover:shadow-md">
                    <div>
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="text-lg font-bold text-slate-800">{{ $eks->name }}</h4>
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $eks->category === 'Wajib' ? 'bg-amber-100 text-amber-800' : 'bg-teal-100 text-teal-800' }}">
                                {{ $eks->category }}
                            </span>
                        </div>

                        <div class="text-xs font-semibold text-slate-600 mb-3 flex items-center gap-1">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Pembina: <span class="text-slate-800">{{ $eks->teacher?->name ?? 'Belum Ditentukan' }}</span>
                        </div>

                        @if($eks->description)
                            <p class="text-slate-600 text-xs mb-4 line-clamp-2 leading-relaxed">{{ $eks->description }}</p>
                        @endif
                    </div>

                    <div class="border-t border-slate-100 pt-3 flex justify-between items-center">
                        <span class="text-xs font-bold text-teal-700 bg-teal-50 px-2 py-1 rounded">
                            {{ $eks->members_count }} Anggota Siswa
                        </span>
                        <div class="flex gap-2">
                            <button wire:click="openEkskulModal({{ $eks->id }})" type="button" class="text-amber-600 hover:text-amber-900 font-bold text-xs">
                                Edit
                            </button>
                            <button wire:click="deleteEkskul({{ $eks->id }})" wire:confirm="Hapus kegiatan ekstrakurikuler ini?" type="button" class="text-rose-600 hover:text-rose-900 font-bold text-xs">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
                    <p class="text-slate-500">Belum ada master kegiatan ekstrakurikuler yang ditambahkan.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- MODAL 1: Tambah Anggota Ekskul -->
    <div x-data="{ open: @entangle('showAddMemberModal') }" 
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

            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform sm:my-8 sm:align-middle sm:max-w-md sm:w-full z-10">
                <div class="bg-white p-6 sm:p-8">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 border-b pb-4">Tambah Anggota Siswa ke Ekskul</h3>

                    <div class="space-y-5 text-sm">
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Filter Kelas Siswa</label>
                            <select wire:model.live="addMemberClassroomId" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}">Kelas {{ $cls->grade_level }} {{ $cls->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Pilih Siswa <span class="text-rose-500">*</span></label>
                            <select wire:model="addMemberStudentId" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($availableStudents as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }} (NISN: {{ $st->nisn }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                    <button wire:click="addMemberToEkskul" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-5 py-2.5 bg-teal-600 text-sm font-bold text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Tambahkan Anggota
                    </button>
                    <button @click="open = false" type="button" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-5 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Input / Edit Prestasi Siswa -->
    <div x-data="{ open: @entangle('showAchievementModal') }" 
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
                        {{ $editingAchievementId ? 'Edit Data Prestasi Siswa' : 'Tambah Data Prestasi Siswa Baru' }}
                    </h3>

                    <div class="space-y-5 text-sm max-h-[70vh] overflow-y-auto pr-1">
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Pilih Siswa Berprestasi <span class="text-rose-500">*</span></label>
                            <select wire:model="achievementStudentId" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                <option value="">-- Pilih Siswa --</option>
                                @foreach(App\Models\Student::where('school_id', app(App\Support\CurrentSchool::class)->id())->where('status', 'Aktif')->orderBy('name')->get() as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }} (NISN: {{ $st->nisn }})</option>
                                @endforeach
                            </select>
                            @error('achievementStudentId') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Nama Kejuaraan / Event Lomba <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="achievementEventName" placeholder="Contoh: Olimpiade Sains Nasional (OSN) Matematika" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                            @error('achievementEventName') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Kategori Prestasi <span class="text-rose-500">*</span></label>
                                <select wire:model="achievementCategory" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                    <option value="Akademik">Akademik</option>
                                    <option value="Non-Akademik">Non-Akademik</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Tingkat Kejuaraan <span class="text-rose-500">*</span></label>
                                <select wire:model="achievementLevel" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                    <option value="Kecamatan">Kecamatan</option>
                                    <option value="Kabupaten/Kota">Kabupaten / Kota</option>
                                    <option value="Provinsi">Provinsi</option>
                                    <option value="Nasional">Nasional</option>
                                    <option value="Internasional">Internasional</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Peringkat / Hasil <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="achievementRank" placeholder="Misal: Juara 1 / Medali Emas" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                @error('achievementRank') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Tanggal Kejuaraan</label>
                                <input type="date" wire:model="achievementEventDate" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Penyelenggara Event</label>
                            <input type="text" wire:model="achievementOrganizer" placeholder="Contoh: Kemendikbudristek / Dinas Pendidikan" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Catatan Tambahan (Opsional)</label>
                            <textarea wire:model="achievementNotes" rows="2" placeholder="Catatan atau keterangan piagam/sertifikat..." class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                    <button wire:click="saveAchievement" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-5 py-2.5 bg-teal-600 text-sm font-bold text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Simpan Prestasi
                    </button>
                    <button @click="open = false" type="button" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-5 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: Master Ekskul Form -->
    <div x-data="{ open: @entangle('showEkskulModal') }" 
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
                        {{ $editingEkskulId ? 'Edit Master Ekskul' : 'Tambah Kegiatan Ekstrakurikuler Baru' }}
                    </h3>

                    <div class="space-y-5 text-sm">
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Nama Kegiatan Ekskul <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="ekskulName" placeholder="Contoh: Pramuka / Futsal / Paskibra" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                            @error('ekskulName') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Kategori <span class="text-rose-500">*</span></label>
                                <select wire:model="ekskulCategory" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                    <option value="Wajib">Wajib</option>
                                    <option value="Pilihan">Pilihan</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Guru Pembina</label>
                                <select wire:model="ekskulTeacherId" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                    <option value="">-- Pilih Guru Pembina --</option>
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Deskripsi Ringkas Kegiatan</label>
                            <textarea wire:model="ekskulDescription" rows="2" placeholder="Deskripsi singkat kegiatan ekstrakurikuler..." class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                    <button wire:click="saveEkskul" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-5 py-2.5 bg-teal-600 text-sm font-bold text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Simpan Ekskul
                    </button>
                    <button @click="open = false" type="button" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-5 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

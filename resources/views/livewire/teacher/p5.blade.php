<div>
    <x-slot name="title">Evaluasi P5</x-slot>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Evaluasi P5 (Profil Pelajar Pancasila)</h2>
            <p class="text-sm text-slate-500 mt-1">Kurikulum Merdeka — Tahun Ajaran: {{ $activeYear ? $activeYear->name : '-' }}</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="switchTab('assessment')" type="button"
                class="{{ $activeTab === 'assessment' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Evaluasi Karakter Siswa
            </button>

            <button wire:click="switchTab('manage_projects')" type="button"
                class="{{ $activeTab === 'manage_projects' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Kelola Projek & Dimensi P5
                @if(!$projects->isEmpty())
                    <span class="bg-teal-100 text-teal-800 text-xs px-2 py-0.5 rounded-full font-semibold">{{ $projects->count() }} Projek</span>
                @endif
            </button>
        </nav>
    </div>

    <!-- Flash Notifications -->
    @if (session()->has('p5_success'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 text-sm rounded-xl border border-green-200 shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('p5_success') }}</span>
        </div>
    @endif

    @if (session()->has('p5_error'))
        <div class="mb-4 p-4 bg-red-50 text-red-700 text-sm rounded-xl border border-red-200 shadow-sm">
            {{ session('p5_error') }}
        </div>
    @endif

    <!-- TAB 1: Evaluasi Karakter Siswa -->
    @if($activeTab === 'assessment')
        @if(!$activeYear)
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-md mb-6 shadow-sm">
                <p class="text-sm text-amber-700">Saat ini tidak ada Tahun Ajaran yang berstatus aktif.</p>
            </div>
        @elseif($projects->isEmpty())
            <div class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
                <div class="mx-auto w-20 h-20 bg-teal-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C20.832 18.477 19.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Projek P5 Terdaftar</h3>
                <p class="text-slate-500 max-w-md mx-auto mb-6 text-sm">Buat projek P5 terlebih dahulu untuk memetakan tema, kelas, dan dimensi target sebelum menginput evaluasi kualitatif siswa.</p>
                <button wire:click="openProjectModal" type="button" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors gap-2">
                    + Buat Projek P5 Pertama
                </button>
            </div>
        @else
            <!-- Select Project Filter -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                <label for="select-p5-project" class="block text-sm font-medium text-slate-700 mb-2">Pilih Projek P5</label>
                <select id="select-p5-project" wire:model.live="selectedProjectId" class="block w-full pl-3 pr-10 py-2.5 text-base border-slate-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm rounded-md shadow-sm font-semibold text-slate-800">
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}">
                            {{ $proj->title }} — Tema: {{ $proj->theme }} (Kelas {{ $proj->classroom?->grade_level }} {{ $proj->classroom?->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            @if($selectedProject)
                <!-- Project Detail Card & Legend -->
                <div class="bg-teal-900 text-white rounded-xl shadow-sm p-6 mb-6 relative overflow-hidden">
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <span class="bg-teal-700 text-teal-100 text-xs font-bold px-2.5 py-1 rounded-md border border-teal-600">
                                    {{ $selectedProject->theme }}
                                </span>
                                <span class="bg-teal-800 text-teal-200 text-xs font-semibold px-2.5 py-1 rounded-md">
                                    {{ $selectedProject->phase }}
                                </span>
                                <span class="bg-teal-800 text-teal-200 text-xs font-semibold px-2.5 py-1 rounded-md">
                                    Kelas {{ $selectedProject->classroom?->grade_level }} {{ $selectedProject->classroom?->name }}
                                </span>
                            </div>
                            <h3 class="text-xl font-black text-white">{{ $selectedProject->title }}</h3>
                            @if($selectedProject->description)
                                <p class="text-teal-200 text-sm mt-1 max-w-3xl leading-relaxed">{{ $selectedProject->description }}</p>
                            @endif
                        </div>

                        <!-- Legend Guide -->
                        <div class="bg-teal-800/80 p-3 rounded-lg border border-teal-700 text-xs text-teal-100 space-y-1 self-stretch md:self-auto">
                            <div class="font-bold text-white mb-1">Skala Kualitatif P5 Kemendikbud:</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 bg-amber-500 rounded-full inline-block"></span> <strong>BB</strong>: Belum Berkembang</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 bg-blue-500 rounded-full inline-block"></span> <strong>MB</strong>: Mulai Berkembang</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 bg-emerald-500 rounded-full inline-block"></span> <strong>BSH</strong>: Berkembang Sesuai Harapan (Target)</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 bg-indigo-500 rounded-full inline-block"></span> <strong>SB</strong>: Sangat Berkembang</div>
                        </div>
                    </div>
                </div>

                <!-- Matrix Assessment Table -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider min-w-[200px]">Nama Siswa</th>
                                    @foreach($selectedProject->dimensions as $dim)
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-slate-700 uppercase tracking-wider min-w-[240px]">
                                            <div class="text-teal-800 font-extrabold mb-0.5">{{ $dim->dimension_name }}</div>
                                            <div class="text-slate-500 font-normal text-[11px] leading-tight">{{ $dim->sub_element }}</div>
                                            <!-- Mass action button for this dimension -->
                                            <button wire:click="setAllDimensionScore({{ $dim->id }}, 'BSH')" type="button" class="mt-2 px-2 py-0.5 bg-emerald-100 text-emerald-800 hover:bg-emerald-200 text-[10px] font-bold rounded transition-colors">
                                                ✓ Set Semua BSH
                                            </button>
                                        </th>
                                    @endforeach
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider min-w-[280px]">Catatan Narasi Proses Projek</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @forelse($students as $student)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-slate-900">{{ $student->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $student->nisn }}</div>
                                        </td>

                                        <!-- Dimensions Scores -->
                                        @foreach($selectedProject->dimensions as $dim)
                                            @php
                                                $score = $assessmentData[$student->id][$dim->id] ?? 'BSH';
                                            @endphp
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                <div class="inline-flex rounded-md shadow-sm" role="group">
                                                    <!-- BB -->
                                                    <button type="button"
                                                        wire:click="$set('assessmentData.{{ $student->id }}.{{ $dim->id }}', 'BB')"
                                                        class="px-2.5 py-1.5 text-xs font-bold border rounded-l-lg transition-all {{ $score === 'BB' ? 'bg-amber-500 text-white border-amber-500 shadow-inner font-black' : 'bg-white text-amber-700 hover:bg-amber-50 border-slate-300' }}">
                                                        BB
                                                    </button>
                                                    <!-- MB -->
                                                    <button type="button"
                                                        wire:click="$set('assessmentData.{{ $student->id }}.{{ $dim->id }}', 'MB')"
                                                        class="px-2.5 py-1.5 text-xs font-bold border-t border-b border-r transition-all {{ $score === 'MB' ? 'bg-blue-600 text-white border-blue-600 shadow-inner font-black' : 'bg-white text-blue-700 hover:bg-blue-50 border-slate-300' }}">
                                                        MB
                                                    </button>
                                                    <!-- BSH -->
                                                    <button type="button"
                                                        wire:click="$set('assessmentData.{{ $student->id }}.{{ $dim->id }}', 'BSH')"
                                                        class="px-2.5 py-1.5 text-xs font-bold border-t border-b border-r transition-all {{ $score === 'BSH' ? 'bg-emerald-600 text-white border-emerald-600 shadow-inner font-black' : 'bg-white text-emerald-700 hover:bg-emerald-50 border-slate-300' }}">
                                                        BSH
                                                    </button>
                                                    <!-- SB -->
                                                    <button type="button"
                                                        wire:click="$set('assessmentData.{{ $student->id }}.{{ $dim->id }}', 'SB')"
                                                        class="px-2.5 py-1.5 text-xs font-bold border-t border-b border-r rounded-r-lg transition-all {{ $score === 'SB' ? 'bg-indigo-600 text-white border-indigo-600 shadow-inner font-black' : 'bg-white text-indigo-700 hover:bg-indigo-50 border-slate-300' }}">
                                                        SB
                                                    </button>
                                                </div>
                                            </td>
                                        @endforeach

                                        <!-- Process Notes -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="text" wire:model="processNotesData.{{ $student->id }}" placeholder="Catatan perkembangan karakter..." class="w-full p-2 rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($selectedProject->dimensions) + 2 }}" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                            Tidak ada siswa aktif di kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(!$students->isEmpty())
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                            <button wire:click="saveAssessments" type="button" class="inline-flex items-center px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Simpan Evaluasi Karakter P5
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        @endif
    @endif

    <!-- TAB 2: Kelola Projek & Dimensi P5 -->
    @if($activeTab === 'manage_projects')
        <div class="mb-6 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Daftar Projek P5 Terdaftar</h3>
            <button wire:click="openProjectModal" type="button" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors gap-2">
                + Buat Projek P5 Baru
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            @forelse($projects as $proj)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between transition-all hover:shadow-md">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap mb-2">
                            <span class="bg-teal-100 text-teal-800 text-xs font-bold px-2.5 py-1 rounded-md">
                                {{ $proj->theme }}
                            </span>
                            <span class="bg-slate-100 text-slate-700 text-xs font-semibold px-2.5 py-1 rounded-md">
                                {{ $proj->phase }}
                            </span>
                            <span class="bg-slate-100 text-slate-700 text-xs font-semibold px-2.5 py-1 rounded-md">
                                Kelas {{ $proj->classroom?->grade_level }} {{ $proj->classroom?->name }}
                            </span>
                        </div>

                        <h4 class="text-xl font-bold text-slate-800 mb-2">{{ $proj->title }}</h4>

                        @if($proj->description)
                            <p class="text-slate-600 text-sm mb-4 leading-relaxed line-clamp-2">{{ $proj->description }}</p>
                        @endif

                        <div class="border-t border-slate-100 pt-3 mt-3">
                            <h5 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Dimensi & Subelemen Sasaran ({{ $proj->dimensions->count() }}):</h5>
                            <ul class="space-y-1.5 text-xs text-slate-700">
                                @foreach($proj->dimensions as $d)
                                    <li class="flex items-start gap-1.5">
                                        <span class="text-teal-600 font-bold">•</span>
                                        <span><strong>{{ $d->dimension_name }}</strong>: {{ $d->sub_element }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-4 mt-6 flex justify-end gap-2">
                        <button wire:click="openProjectModal({{ $proj->id }})" type="button" class="px-3 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                            Edit Projek
                        </button>
                        <button wire:click="deleteProject({{ $proj->id }})" wire:confirm="Apakah Anda yakin ingin menghapus projek P5 ini?" type="button" class="px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                            Hapus
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
                    <p class="text-slate-500">Belum ada projek P5 yang didaftarkan.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Modal Form Buat / Edit Projek P5 -->
    <div x-data="{ open: @entangle('showProjectModal') }" 
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
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full z-10">
                <div class="bg-white p-6 sm:p-8">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800" id="modal-title">
                                {{ $editingProjectId ? 'Edit Projek P5' : 'Buat Projek P5 Baru' }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Petakan tema, fase, kelas, dan dimensi Profil Pelajar Pancasila target.</p>
                        </div>
                        <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="space-y-5 text-sm max-h-[70vh] overflow-y-auto pr-2">
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Judul / Nama Projek P5 <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="projectTitle" placeholder="Contoh: Pengelolaan Sampah Mandiri Sekolah" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                            @error('projectTitle') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Tema P5 Kemendikbud <span class="text-rose-500">*</span></label>
                                <select wire:model="projectTheme" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                    @foreach(App\Livewire\Teacher\P5::$themes as $thm)
                                        <option value="{{ $thm }}">{{ $thm }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Fase P5 <span class="text-rose-500">*</span></label>
                                <select wire:model="projectPhase" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                    <option value="Fase A">Fase A (SD Kelas 1-2)</option>
                                    <option value="Fase B">Fase B (SD Kelas 3-4)</option>
                                    <option value="Fase C">Fase C (SD Kelas 5-6)</option>
                                    <option value="Fase D">Fase D (SMP Kelas 7-9)</option>
                                    <option value="Fase E">Fase E (SMA/SMK Kelas 10)</option>
                                    <option value="Fase F">Fase F (SMA/SMK Kelas 11-12)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Target Kelas <span class="text-rose-500">*</span></label>
                                <select wire:model="projectClassroomId" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5">
                                    @foreach($classrooms as $cls)
                                        <option value="{{ $cls->id }}">Kelas {{ $cls->grade_level }} {{ $cls->name }}</option>
                                    @endforeach
                                </select>
                                @error('projectClassroomId') <span class="text-xs text-rose-600 mt-1 block font-semibold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Deskripsi Singkat Projek</label>
                            <textarea wire:model="projectDescription" rows="2" placeholder="Tuliskan tujuan dan deskripsi singkat kegiatan projek..." class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5 px-3.5"></textarea>
                        </div>

                        <!-- Repeater: Dimensi & Subelemen -->
                        <div class="border-t border-slate-200 pt-5 mt-5">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm">Pemetaan Dimensi & Subelemen P5 Target <span class="text-rose-500">*</span></h4>
                                    <p class="text-xs text-slate-500">Sub-elemen ini akan dinilai secara kualitatif pada matriks evaluasi siswa.</p>
                                </div>
                                <button wire:click="addDimensionRow" type="button" class="text-xs font-bold text-teal-700 hover:text-teal-800 bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-lg border border-teal-200 transition-colors">
                                    + Tambah Dimensi
                                </button>
                            </div>

                            <div class="space-y-4">
                                @foreach($projectDimensionsInput as $idx => $dimRow)
                                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 relative">
                                        @if(count($projectDimensionsInput) > 1)
                                            <button wire:click="removeDimensionRow({{ $idx }})" type="button" class="absolute top-3 right-3 text-rose-500 hover:text-rose-700 text-xs font-bold bg-white px-2 py-1 rounded border border-rose-200 shadow-sm">
                                                ✕ Hapus
                                            </button>
                                        @endif
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 mb-1">Dimensi Pancasila</label>
                                                <select wire:model="projectDimensionsInput.{{ $idx }}.dimension_name" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-xs py-2 px-3">
                                                    @foreach(App\Livewire\Teacher\P5::$dimensions as $dName)
                                                        <option value="{{ $dName }}">{{ $dName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 mb-1">Elemen</label>
                                                <input type="text" wire:model="projectDimensionsInput.{{ $idx }}.element_name" placeholder="Misal: Kolaborasi" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-xs py-2 px-3">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1">Subelemen / Target Capaian Sasaran</label>
                                            <input type="text" wire:model="projectDimensionsInput.{{ $idx }}.sub_element" placeholder="Misal: Kerjasama kelompok dalam merencanakan & mengeksekusi projek" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-xs py-2 px-3">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                    <button wire:click="saveProject" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-5 py-2.5 bg-teal-600 text-sm font-bold text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Simpan Projek P5
                    </button>
                    <button @click="open = false" type="button" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-5 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

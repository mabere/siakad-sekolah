<div>
    <x-slot name="title">Rekomendasi Diferensiasi Pengajaran AI</x-slot>

    <div class="mb-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Rekomendasi Diferensiasi Pengajaran AI</h1>
                <p class="mt-1 max-w-3xl text-sm text-slate-500">
                    Analisis karakteristik empiris kelas (riwayat nilai, presensi, sarana & kebutuhan belajar) untuk rekomendasi strategi diferensiasi Kurikulum Merdeka berbasis Gemini AI.
                </p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700">
                ✨ Gemini AI Pedagogical Advisor
            </span>
        </div>
    </div>

    @if (! $isConfigured)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Fitur AI belum aktif.</p>
            <p class="mt-1">
                Administrator perlu mengatur credential Gemini dan mengaktifkan fitur ini.
            </p>
        </div>
    @endif

    @if (session()->has('generation_success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 flex items-center gap-2">
            <span>✅</span> {{ session('generation_success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
        {{-- KOLOM KIRI: PILIH JADWAL & STATISTIK EMPIRIS KELAS --}}
        <section class="xl:col-span-2 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-4">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span>🏫</span> Pilih Rombel & Jadwal Mengajar
                </h2>

                <div>
                    <label for="selectedScheduleId" class="mb-2 block text-sm font-semibold text-slate-700">Jadwal Kelas Aktif</label>
                    <select id="selectedScheduleId" wire:model.live="selectedScheduleId" class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 bg-white">
                        <option value="">-- Pilih Mata Pelajaran & Kelas --</option>
                        @foreach ($schedules as $sched)
                            <option value="{{ $sched['id'] }}">
                                {{ $sched['subject'] }} — Kelas {{ $sched['classroom'] }} ({{ $sched['time'] }})
                            </option>
                        @endforeach
                    </select>
                    @if (count($schedules) === 0)
                        <p class="mt-1.5 text-xs text-slate-500">Belum ada jadwal mengajar aktif yang terhubung ke akun Anda.</p>
                    @endif
                    @error('selectedScheduleId') <span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
                </div>

                {{-- DASHBOARD DATA EMPIRIS KELAS --}}
                @if ($classStats)
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">📊 Data Empiris Kelas</span>
                            <span class="text-2xs bg-teal-100 text-teal-800 font-bold px-2 py-0.5 rounded-full">
                                {{ $classStats['total_students'] }} Siswa Terdaftar
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div class="rounded-lg bg-white border border-slate-200 p-2.5">
                                <span class="text-2xs text-slate-500">Rata-Rata Nilai</span>
                                <p class="text-lg font-black text-slate-900">{{ $classStats['avg_final_score'] }}</p>
                                <div class="text-[10px] text-slate-400">Tugas: {{ $classStats['avg_tugas'] }} | UTS: {{ $classStats['avg_uts'] }}</div>
                            </div>
                            <div class="rounded-lg bg-white border border-slate-200 p-2.5">
                                <span class="text-2xs text-slate-500">Tingkat Kehadiran</span>
                                <p class="text-lg font-black text-emerald-700">{{ $classStats['attendance_rate'] }}%</p>
                                <div class="text-[10px] text-slate-400">Hadir: {{ $classStats['hadir_count'] }} | Sakit/Izin: {{ $classStats['sakit_count'] + $classStats['izin_count'] }}</div>
                            </div>
                        </div>

                        <div class="space-y-1.5 text-xs text-slate-700 pt-1">
                            <div class="flex justify-between items-center py-1 border-b border-slate-200">
                                <span>Perlu Bimbingan (&lt;75):</span>
                                <span class="font-bold text-rose-700">{{ $classStats['need_support_count'] }} Siswa</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-200">
                                <span>Level Reguler (75–84):</span>
                                <span class="font-bold text-blue-700">{{ $classStats['regular_count'] }} Siswa</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span>Level Mahir (&ge;85):</span>
                                <span class="font-bold text-emerald-700">{{ $classStats['high_achiever_count'] }} Siswa</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200 space-y-1.5 text-xs">
                            <div>
                                <span class="font-semibold text-slate-700">Kebutuhan Belajar:</span>
                                <p class="text-slate-600 text-2xs">{{ $classStats['student_needs'] }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-slate-700">Sarana Pendukung:</span>
                                <p class="text-slate-600 text-2xs">{{ $classStats['available_facilities'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <label for="additionalContext" class="mb-2 block text-sm font-semibold text-slate-700">Catatan Khusus Pengamatan Guru <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="additionalContext" wire:model="additionalContext" rows="3" placeholder="Contoh: Pada materi sebelumnya beberapa siswa masih kesulitan dalam operasi aljabar dasar, namun sangat antusias saat praktik kelompok..." class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-xs shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                </div>

                <button type="button" wire:click="generateRecommendation" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-teal-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <span wire:loading.remove wire:target="generateRecommendation">💡 Analisis & Rekomendasi Diferensiasi AI</span>
                    <span wire:loading wire:target="generateRecommendation" class="flex items-center gap-2">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menganalisis Data Kelas dengan Gemini...
                    </span>
                </button>
                @error('generation') <span class="block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
            </div>
        </section>

        {{-- KOLOM KANAN: HASIL REKOMENDASI DIFERENSIASI AI --}}
        <section class="xl:col-span-3 space-y-6">
            @if ($recommendation)
                <div class="space-y-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-bold text-teal-800">
                                    Kurikulum Merdeka
                                </span>
                                <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-800">
                                    Diferensiasi Pembelajaran
                                </span>
                            </div>
                            <h2 class="mt-2 text-xl font-bold text-slate-900">Strategi Diferensiasi Kelas</h2>
                        </div>
                        <button type="button" wire:click="applyToLearningAssistant" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-teal-700 transition">
                            <span>🚀 Terapkan ke Generator Modul Ajar (1-Click)</span>
                        </button>
                    </div>

                    {{-- 1. Ringkasan Kesiapan Kelas --}}
                    <div class="rounded-xl border border-teal-200 bg-teal-50/50 p-4 space-y-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-teal-900">📋 Analisis Kesiapan Belajar Kelas</h3>
                        <p class="text-xs text-slate-700 leading-relaxed">{{ data_get($recommendation, 'classroom_summary') }}</p>

                        @php $dist = data_get($recommendation, 'readiness_level_distribution', []); @endphp
                        @if ($dist)
                            <div class="grid grid-cols-3 gap-2 pt-2 text-center text-xs font-semibold">
                                <div class="rounded bg-white p-2 border border-emerald-200 text-emerald-800">
                                    <div class="text-2xs text-slate-500">Perlu Bimbingan</div>
                                    <div class="text-base font-bold">{{ data_get($dist, 'scaffolding_percentage', '-') }}</div>
                                </div>
                                <div class="rounded bg-white p-2 border border-blue-200 text-blue-800">
                                    <div class="text-2xs text-slate-500">Reguler / Cakap</div>
                                    <div class="text-base font-bold">{{ data_get($dist, 'regular_percentage', '-') }}</div>
                                </div>
                                <div class="rounded bg-white p-2 border border-purple-200 text-purple-800">
                                    <div class="text-2xs text-slate-500">Mahir / Pengayaan</div>
                                    <div class="text-base font-bold">{{ data_get($dist, 'advanced_percentage', '-') }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Model Pembelajaran Rekomendasi --}}
                    @php $models = data_get($recommendation, 'recommended_learning_models', []); @endphp
                    @if (is_array($models) && count($models) > 0)
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">💡 Rekomendasi Model Pembelajaran</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($models as $m)
                                    <span class="rounded-lg bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-bold text-blue-900">
                                        ✓ {{ $m }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 2. Matriks 3 Dimensi Diferensiasi --}}
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 mb-3">🎯 Matriks 3 Dimensi Diferensiasi</h3>
                        <div class="space-y-3">
                            {{-- Diferensiasi Konten --}}
                            <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-teal-700 text-white px-2 py-0.5 text-2xs font-bold uppercase">1. Diferensiasi Konten</span>
                                    <span class="text-xs font-semibold text-slate-800">{{ data_get($recommendation, 'differentiation_content.strategy') }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-2xs pt-1">
                                    <div class="p-2.5 rounded bg-emerald-50 border border-emerald-200">
                                        <strong class="text-emerald-900 block mb-1">Fondasi (Scaffolding):</strong>
                                        <p class="text-slate-700">{{ data_get($recommendation, 'differentiation_content.for_scaffolding') }}</p>
                                    </div>
                                    <div class="p-2.5 rounded bg-blue-50 border border-blue-200">
                                        <strong class="text-blue-900 block mb-1">Reguler (Cakap):</strong>
                                        <p class="text-slate-700">{{ data_get($recommendation, 'differentiation_content.for_regular') }}</p>
                                    </div>
                                    <div class="p-2.5 rounded bg-purple-50 border border-purple-200">
                                        <strong class="text-purple-900 block mb-1">Pengayaan (HOTS):</strong>
                                        <p class="text-slate-700">{{ data_get($recommendation, 'differentiation_content.for_advanced') }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Diferensiasi Proses --}}
                            <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-blue-700 text-white px-2 py-0.5 text-2xs font-bold uppercase">2. Diferensiasi Proses</span>
                                    <span class="text-xs font-semibold text-slate-800">{{ data_get($recommendation, 'differentiation_process.strategy') }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-2xs pt-1">
                                    <div class="p-2.5 rounded bg-emerald-50 border border-emerald-200">
                                        <strong class="text-emerald-900 block mb-1">Bimbingan Guru:</strong>
                                        <p class="text-slate-700">{{ data_get($recommendation, 'differentiation_process.for_scaffolding') }}</p>
                                    </div>
                                    <div class="p-2.5 rounded bg-blue-50 border border-blue-200">
                                        <strong class="text-blue-900 block mb-1">Mandiri & Tutor Sebaya:</strong>
                                        <p class="text-slate-700">{{ data_get($recommendation, 'differentiation_process.for_regular') }}</p>
                                    </div>
                                    <div class="p-2.5 rounded bg-purple-50 border border-purple-200">
                                        <strong class="text-purple-900 block mb-1">Eksplorasi Terbuka:</strong>
                                        <p class="text-slate-700">{{ data_get($recommendation, 'differentiation_process.for_advanced') }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Diferensiasi Produk --}}
                            <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-purple-700 text-white px-2 py-0.5 text-2xs font-bold uppercase">3. Diferensiasi Produk</span>
                                    <span class="text-xs font-semibold text-slate-800">{{ data_get($recommendation, 'differentiation_product.strategy') }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-2xs pt-1">
                                    @foreach (data_get($recommendation, 'differentiation_product.options', []) as $opt)
                                        <div class="p-2.5 rounded bg-slate-50 border border-slate-200">
                                            <strong class="text-indigo-900 block mb-0.5">{{ data_get($opt, 'product_type') }}</strong>
                                            <div class="text-[11px] font-semibold text-teal-700 mb-1">Target: {{ data_get($opt, 'target_group') }}</div>
                                            <p class="text-slate-600">{{ data_get($opt, 'description') }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Pengelompokan Kesiapan Belajar Siswa --}}
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 mb-3">👥 Strategi Pengelompokan Kesiapan Belajar (3 Tier)</h3>
                        <div class="space-y-3">
                            {{-- Kelompok 1 --}}
                            <div class="rounded-xl border border-emerald-300 bg-emerald-50/40 p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="rounded bg-emerald-600 px-2 py-0.5 text-2xs font-bold text-white uppercase">{{ data_get($recommendation, 'student_grouping.scaffolding_group.title', 'Kelompok 1: Perlu Bimbingan') }}</span>
                                </div>
                                <p class="text-xs text-emerald-950"><strong>Karakteristik:</strong> {{ data_get($recommendation, 'student_grouping.scaffolding_group.characteristics') }}</p>
                                <p class="text-xs text-emerald-900 font-semibold">Intervensi Guru: {{ data_get($recommendation, 'student_grouping.scaffolding_group.teacher_intervention') }}</p>
                                <ul class="list-disc pl-5 text-2xs text-slate-700 space-y-0.5">
                                    @foreach (data_get($recommendation, 'student_grouping.scaffolding_group.sample_tasks', []) as $task)
                                        <li>{{ $task }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- Kelompok 2 --}}
                            <div class="rounded-xl border border-blue-300 bg-blue-50/40 p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="rounded bg-blue-600 px-2 py-0.5 text-2xs font-bold text-white uppercase">{{ data_get($recommendation, 'student_grouping.regular_group.title', 'Kelompok 2: Reguler (Cakap)') }}</span>
                                </div>
                                <p class="text-xs text-blue-950"><strong>Karakteristik:</strong> {{ data_get($recommendation, 'student_grouping.regular_group.characteristics') }}</p>
                                <p class="text-xs text-blue-900 font-semibold">Penguatan Guru: {{ data_get($recommendation, 'student_grouping.regular_group.teacher_intervention') }}</p>
                                <ul class="list-disc pl-5 text-2xs text-slate-700 space-y-0.5">
                                    @foreach (data_get($recommendation, 'student_grouping.regular_group.sample_tasks', []) as $task)
                                        <li>{{ $task }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- Kelompok 3 --}}
                            <div class="rounded-xl border border-purple-300 bg-purple-50/40 p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="rounded bg-purple-600 px-2 py-0.5 text-2xs font-bold text-white uppercase">{{ data_get($recommendation, 'student_grouping.advanced_group.title', 'Kelompok 3: Pengayaan (Mahir)') }}</span>
                                </div>
                                <p class="text-xs text-purple-950"><strong>Karakteristik:</strong> {{ data_get($recommendation, 'student_grouping.advanced_group.characteristics') }}</p>
                                <p class="text-xs text-purple-900 font-semibold">Fasilitasi Guru: {{ data_get($recommendation, 'student_grouping.advanced_group.teacher_intervention') }}</p>
                                <ul class="list-disc pl-5 text-2xs text-slate-700 space-y-0.5">
                                    @foreach (data_get($recommendation, 'student_grouping.advanced_group.sample_tasks', []) as $task)
                                        <li>{{ $task }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Rencana Aksi Pedagogis Guru --}}
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 mb-2">📋 Rencana Aksi Pedagogis Guru</h3>
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                                <thead class="bg-slate-50 font-bold text-slate-700">
                                    <tr>
                                        <th class="px-3 py-2">Langkah</th>
                                        <th class="px-3 py-2">Tindakan Guru</th>
                                        <th class="px-3 py-2">Hasil yang Diharapkan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach (data_get($recommendation, 'pedagogical_action_plan', []) as $act)
                                        <tr>
                                            <td class="px-3 py-2.5 font-bold text-teal-800 align-top">
                                                Langkah {{ data_get($act, 'step_number') }}:<br>
                                                <span class="font-normal text-slate-900">{{ data_get($act, 'action_title') }}</span>
                                            </td>
                                            <td class="px-3 py-2.5 text-slate-700 align-top">{{ data_get($act, 'teacher_action') }}</td>
                                            <td class="px-3 py-2.5 text-emerald-800 font-medium align-top">{{ data_get($act, 'expected_outcome') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50/60 p-8 text-center text-slate-500">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-600 text-xl font-bold">
                        💡
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800">Belum Ada Rekomendasi Diferensiasi AI</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                        Pilih jadwal mengajar di kolom kiri dan klik <strong>Analisis & Rekomendasi Diferensiasi AI</strong> untuk menganalisis data empiris kelas dan menghasilkan strategi diferensiasi Kurikulum Merdeka.
                    </p>
                </div>
            @endif
        </section>
    </div>
</div>

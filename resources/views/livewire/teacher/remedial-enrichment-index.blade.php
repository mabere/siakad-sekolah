<div>
    <x-slot name="title">Generator Remedial & Pengayaan AI</x-slot>

    <div class="mb-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Generator Lembar Kerja Remedial & Pengayaan AI</h1>
                <p class="mt-1 max-w-3xl text-sm text-slate-500">
                    Otomasi penyusunan paket pembelajaran remedial (scaffolding & re-teaching) dan pengayaan (tantangan HOTS & studi kasus nyata) berbasis analisis hasil asesmen/CBT Kurikulum Merdeka.
                </p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-700">
                ✨ Gemini AI Diagnostic & Intervention
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

    @if (session()->has('cbt_export_success'))
        <div class="mb-6 rounded-xl border border-teal-200 bg-teal-50 p-4 text-sm font-semibold text-teal-800 flex items-center gap-2">
            <span>🚀</span> {{ session('cbt_export_success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
        {{-- KOLOM KIRI: PILIH KELAS/UJIAN & FORM PARAMETER --}}
        <section class="xl:col-span-2 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-4">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span>🎯</span> Sumber Data Asesmen & Kelas
                </h2>

                <div>
                    <label for="selectedScheduleId" class="mb-2 block text-sm font-semibold text-slate-700">Jadwal Kelas / Rombel</label>
                    <select id="selectedScheduleId" wire:model.live="selectedScheduleId" class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500 bg-white">
                        <option value="">-- Pilih Rombel & Mata Pelajaran --</option>
                        @foreach ($schedules as $sched)
                            <option value="{{ $sched['id'] }}">
                                {{ $sched['subject'] }} — Kelas {{ $sched['classroom'] }} ({{ $sched['time'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="selectedExamId" class="mb-2 block text-sm font-semibold text-slate-700">
                        Pilih Hasil Ujian CBT <span class="font-normal text-slate-400">(opsional - auto-analisis)</span>
                    </label>
                    <select id="selectedExamId" wire:model.live="selectedExamId" class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500 bg-white">
                        <option value="">-- Analisis Berdasarkan Rekap Nilai Kelas (Default) --</option>
                        @foreach ($availableExams as $ex)
                            <option value="{{ $ex['id'] }}">
                                {{ $ex['title'] }} ({{ $ex['subject'] }} - {{ $ex['classroom'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- STATISTIK & PEMETAAN SISWA --}}
                @if (count($remedialStudents) > 0 || count($enrichmentStudents) > 0 || $examAnalysis)
                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">📊 Hasil Pemetaan Asesmen</span>
                            @if ($examAnalysis)
                                <span class="text-2xs bg-purple-100 text-purple-800 font-bold px-2 py-0.5 rounded-full">
                                    Rata-rata CBT: {{ $examAnalysis['avg_score'] }} ({{ $examAnalysis['pass_rate'] }}% Tuntas)
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-center text-xs">
                            <div class="rounded-lg bg-rose-50 border border-rose-200 p-2.5">
                                <span class="text-2xs text-rose-600 font-semibold uppercase">Perlu Remedial (&lt;75)</span>
                                <p class="text-xl font-black text-rose-700">{{ count($remedialStudents) }} Siswa</p>
                            </div>
                            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-2.5">
                                <span class="text-2xs text-emerald-600 font-semibold uppercase">Siap Pengayaan (&ge;85)</span>
                                <p class="text-xl font-black text-emerald-700">{{ count($enrichmentStudents) }} Siswa</p>
                            </div>
                        </div>

                        {{-- DAFTAR NAMA SISWA REMEDIAL --}}
                        @if (count($remedialStudents) > 0)
                            <div class="space-y-1 pt-1">
                                <span class="text-2xs font-bold text-rose-900 block">Siswa Sasaran Remedial:</span>
                                <div class="max-h-24 overflow-y-auto space-y-1 text-2xs">
                                    @foreach ($remedialStudents as $rs)
                                        <div class="flex justify-between items-center bg-white px-2 py-1 rounded border border-rose-100">
                                            <span class="font-medium text-slate-800">{{ $rs['name'] }}</span>
                                            <span class="font-bold text-rose-600">{{ $rs['score'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- DAFTAR NAMA SISWA PENGAYAAN --}}
                        @if (count($enrichmentStudents) > 0)
                            <div class="space-y-1 pt-1">
                                <span class="text-2xs font-bold text-emerald-900 block">Siswa Sasaran Pengayaan:</span>
                                <div class="max-h-24 overflow-y-auto space-y-1 text-2xs">
                                    @foreach ($enrichmentStudents as $es)
                                        <div class="flex justify-between items-center bg-white px-2 py-1 rounded border border-emerald-100">
                                            <span class="font-medium text-slate-800">{{ $es['name'] }}</span>
                                            <span class="font-bold text-emerald-600">{{ $es['score'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <div>
                    <label for="topic" class="mb-2 block text-sm font-semibold text-slate-700">Topik / Materi Asesmen <span class="text-rose-500">*</span></label>
                    <input type="text" id="topic" wire:model="topic" placeholder="Contoh: Sistem Persamaan Linear Tiga Variabel (SPLTV)" class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500" />
                    @error('topic') <span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="targetCompetency" class="mb-2 block text-sm font-semibold text-slate-700">Indikator / TP yang Belum Tuntas <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="targetCompetency" wire:model="targetCompetency" rows="2" placeholder="Contoh: Siswa kesulitan mengubah wacana cerita ke dalam model matematika eliminasi-substitusi.." class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-xs shadow-sm focus:border-purple-500 focus:ring-purple-500"></textarea>
                </div>

                <div>
                    <label for="teacherNotes" class="mb-2 block text-sm font-semibold text-slate-700">Catatan Khusus Pengamatan Guru <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="teacherNotes" wire:model="teacherNotes" rows="2" placeholder="Instruksi tambahan untuk asisten AI.." class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-xs shadow-sm focus:border-purple-500 focus:ring-purple-500"></textarea>
                </div>

                <button type="button" wire:click="generatePackage" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-purple-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <span wire:loading.remove wire:target="generatePackage">🚀 Generate Paket Remedial & Pengayaan AI</span>
                    <span wire:loading wire:target="generatePackage" class="flex items-center gap-2">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyusun Paket Pembelajaran Intervensi...
                    </span>
                </button>
                @error('generation') <span class="block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
            </div>
        </section>

        {{-- KOLOM KANAN: HASIL GENERATOR PAKET REMEDIAL & PENGAYAAN --}}
        <section class="xl:col-span-3 space-y-6">
            @if ($package)
                <div class="space-y-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    {{-- TAB NAVIGATION & ACTION BAR --}}
                    <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="$set('activeTab', 'remedial')" class="rounded-lg px-3.5 py-2 text-xs font-bold transition {{ $activeTab === 'remedial' ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                📘 Lembar Remedial (Scaffolding)
                            </button>
                            <button type="button" wire:click="$set('activeTab', 'enrichment')" class="rounded-lg px-3.5 py-2 text-xs font-bold transition {{ $activeTab === 'enrichment' ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                🌟 Lembar Pengayaan (HOTS)
                            </button>
                            <button type="button" wire:click="$set('activeTab', 'analysis')" class="rounded-lg px-3.5 py-2 text-xs font-bold transition {{ $activeTab === 'analysis' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                💡 Diagnosis Miskonsepsi
                            </button>
                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('guru.remedial-enrichment.print', ['type' => $activeTab === 'enrichment' ? 'enrichment' : 'remedial']) }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                                <span>🖨️ Cetak PDF</span>
                            </a>
                            <a href="{{ route('guru.remedial-enrichment.export-word', ['type' => $activeTab === 'enrichment' ? 'enrichment' : 'remedial']) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                                <span>📥 Ekspor Word</span>
                            </a>
                            @if ($activeTab === 'remedial')
                                <button type="button" wire:click="exportRemedialToCbt" class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-teal-700 transition">
                                    <span>⚡ Ekspor ke CBT</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- TAB 1: PAKET REMEDIAL --}}
                    @if ($activeTab === 'remedial')
                        @php $rem = data_get($package, 'remedial_package'); @endphp
                        <div class="space-y-4">
                            <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-rose-600 text-white px-2 py-0.5 text-2xs font-bold uppercase">Lembar Kerja Remedial</span>
                                    <span class="text-xs font-bold text-rose-900">{{ data_get($rem, 'title') }}</span>
                                </div>
                                <p class="text-xs text-slate-700"><strong>Sasaran TP:</strong> {{ data_get($rem, 'target_competency') }}</p>
                            </div>

                            {{-- Rangkuman Konsep & Re-teaching --}}
                            <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">📖 Rangkuman Konsep Kunci (Re-Teaching)</h3>
                                <p class="text-xs text-slate-700 leading-relaxed">{{ data_get($rem, 'concept_recap') }}</p>
                            </div>

                            {{-- Worked Example --}}
                            <div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4 space-y-2">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900">💡 Contoh Soal Terurai Langkah Demi Langkah (Worked Example)</h3>
                                <div class="rounded bg-white p-3 border border-amber-200 text-xs font-semibold text-slate-900">
                                    {{ data_get($rem, 'worked_example.problem_statement') }}
                                </div>
                                <ol class="list-decimal pl-5 text-xs text-slate-700 space-y-1">
                                    @foreach(data_get($rem, 'worked_example.step_by_step_solution', []) as $step)
                                        <li>{{ $step }}</li>
                                    @endforeach
                                </ol>
                                @if(data_get($rem, 'worked_example.key_takeaway'))
                                    <p class="text-2xs text-amber-900 font-bold bg-amber-100 p-2 rounded">
                                        📌 Kunci Ingat: {{ data_get($rem, 'worked_example.key_takeaway') }}
                                    </p>
                                @endif
                            </div>

                            {{-- Butir Latihan Remedial --}}
                            <div class="space-y-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">📝 5 Butir Latihan Remedial Berpemandu</h3>
                                @foreach(data_get($rem, 'practice_items', []) as $item)
                                    <div class="rounded-lg border border-slate-200 bg-white p-3.5 space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-bold text-slate-900">Soal #{{ data_get($item, 'item_number') }}</span>
                                            <span class="text-2xs font-semibold uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                                {{ data_get($item, 'type') === 'essay' ? 'Uraian' : 'Pilihan Ganda' }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-800 leading-relaxed">{{ data_get($item, 'question_text') }}</p>

                                        @if(is_array(data_get($item, 'options')) && count(data_get($item, 'options')) > 0)
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pl-2 text-xs text-slate-700 pt-1">
                                                @foreach(data_get($item, 'options') as $opt)
                                                    <div class="rounded bg-slate-50 p-1.5 border border-slate-100">{{ $opt }}</div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="rounded bg-emerald-50 border border-emerald-200 p-2 text-2xs space-y-1">
                                            <p class="text-emerald-900 font-bold">💡 Petunjuk Bantu: <span class="font-normal">{{ data_get($item, 'hint') }}</span></p>
                                            <p class="text-emerald-950 font-bold">🔑 Kunci Jawaban: <span class="font-semibold text-emerald-800">{{ data_get($item, 'answer_key') }}</span> — {{ data_get($item, 'explanation') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Panduan Guru --}}
                            @if(data_get($rem, 'teacher_scaffolding_guide'))
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700">
                                    <strong class="text-slate-900 block mb-1">👩‍🏫 Panduan Intervensi Guru di Kelas:</strong>
                                    {{ data_get($rem, 'teacher_scaffolding_guide') }}
                                </div>
                            @endif
                        </div>

                    {{-- TAB 2: PAKET PENGAYAAN --}}
                    @elseif ($activeTab === 'enrichment')
                        @php $enr = data_get($package, 'enrichment_package'); @endphp
                        <div class="space-y-4">
                            <div class="rounded-xl border border-purple-200 bg-purple-50/50 p-4 space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-purple-600 text-white px-2 py-0.5 text-2xs font-bold uppercase">Lembar Kerja Pengayaan HOTS</span>
                                    <span class="text-xs font-bold text-purple-900">{{ data_get($enr, 'title') }}</span>
                                </div>
                                <p class="text-xs text-slate-700"><strong>Sasaran TP Pengayaan:</strong> {{ data_get($enr, 'target_competency') }}</p>
                            </div>

                            {{-- Studi Kasus Nyata --}}
                            <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-purple-900">🌐 Wacana Stimulus & Studi Kasus Nyata</h3>
                                <div class="rounded bg-purple-50/40 p-3.5 border border-purple-100 text-xs text-slate-800 leading-relaxed">
                                    {{ data_get($enr, 'real_world_case') }}
                                </div>
                            </div>

                            {{-- Soal Tantangan HOTS --}}
                            <div class="space-y-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">🔥 Butir Tantangan HOTS (C4–C6)</h3>
                                @foreach(data_get($enr, 'hots_items', []) as $hItem)
                                    <div class="rounded-lg border border-slate-200 bg-white p-3.5 space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-bold text-slate-900">Tantangan #{{ data_get($hItem, 'item_number') }}</span>
                                            <span class="text-2xs font-bold px-2 py-0.5 rounded bg-purple-100 text-purple-800">
                                                {{ data_get($hItem, 'cognitive_level') }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-800 leading-relaxed">{{ data_get($hItem, 'question_text') }}</p>
                                        <div class="rounded bg-slate-50 border border-slate-200 p-2 text-2xs">
                                            <strong class="text-slate-700">Panduan Jawaban / Eksplorasi:</strong>
                                            <p class="text-slate-600 mt-0.5">{{ data_get($hItem, 'expected_response_guide') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Mini Project Prompt --}}
                            @if(data_get($enr, 'mini_project_prompt'))
                                <div class="rounded-xl border border-indigo-200 bg-indigo-50/40 p-4 space-y-2">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-900">🚀 Ide Mini-Projek / Investigasi Mandiri</h3>
                                    <p class="text-xs font-bold text-slate-900">{{ data_get($enr, 'mini_project_prompt.project_title') }}</p>
                                    <p class="text-xs text-slate-700">{{ data_get($enr, 'mini_project_prompt.instructions') }}</p>
                                    <div class="flex flex-wrap gap-2 text-2xs pt-1">
                                        <span class="rounded bg-white px-2 py-1 border border-indigo-200 text-indigo-800 font-semibold">
                                            ⏱ Durasi: {{ data_get($enr, 'mini_project_prompt.estimated_duration', '1-2 Minggu') }}
                                        </span>
                                        <span class="rounded bg-white px-2 py-1 border border-indigo-200 text-indigo-800 font-semibold">
                                            📦 Target Produk: {{ data_get($enr, 'mini_project_prompt.deliverable_product') }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            {{-- Rubrik Penilaian Pengayaan --}}
                            @if(count(data_get($enr, 'scoring_rubric', [])) > 0)
                                <div class="space-y-2">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">📋 Rubrik Penilaian Pengayaan</h3>
                                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                                        <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                                            <thead class="bg-slate-50 font-bold text-slate-700">
                                                <tr>
                                                    <th class="px-3 py-2">Kriteria</th>
                                                    <th class="px-3 py-2">Indikator Ketercapaian</th>
                                                    <th class="px-3 py-2">Rentang Skor</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 bg-white">
                                                @foreach(data_get($enr, 'scoring_rubric', []) as $rub)
                                                    <tr>
                                                        <td class="px-3 py-2 font-bold text-purple-900">{{ data_get($rub, 'criteria') }}</td>
                                                        <td class="px-3 py-2 text-slate-700">{{ data_get($rub, 'indicator') }}</td>
                                                        <td class="px-3 py-2 font-semibold text-emerald-800">{{ data_get($rub, 'score_range') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>

                    {{-- TAB 3: DIAGNOSIS & MISKONSEPSI --}}
                    @elseif ($activeTab === 'analysis')
                        @php $ana = data_get($package, 'analysis_summary'); @endphp
                        <div class="space-y-4">
                            <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-4 space-y-2">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-blue-900">🔍 Analisis Akar Masalah (Root Cause)</h3>
                                <p class="text-xs text-slate-700 leading-relaxed">{{ data_get($ana, 'root_cause_analysis') }}</p>
                            </div>

                            <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">⚠️ Identifikasi Miskonsepsi yang Ditemukan</h3>
                                <ul class="list-disc pl-5 text-xs text-slate-700 space-y-1">
                                    @foreach(data_get($ana, 'misconceptions_identified', []) as $misc)
                                        <li>{{ $misc }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">🎯 Rekomendasi Strategi Intervensi</h3>
                                <p class="text-xs text-slate-700 leading-relaxed">{{ data_get($ana, 'intervention_strategy') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50/60 p-8 text-center text-slate-500">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 text-purple-600 text-xl font-bold">
                        💡
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800">Belum Ada Paket Remedial & Pengayaan Aktif</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                        Pilih rombel atau hasil CBT di kolom kiri, sesuaikan parameter materi, lalu klik <strong>Generate Paket Remedial & Pengayaan AI</strong>.
                    </p>
                </div>
            @endif
        </section>
    </div>
</div>

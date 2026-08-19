<div>
    <x-slot name="title">Kartu Hasil Studi & Rapor Digital</x-slot>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Kartu Hasil Studi & Rapor Digital</h2>
            <p class="text-sm text-slate-500 mt-1">
                Siswa: <strong>{{ auth()->user()->name }}</strong> | 
                Tahun Ajaran: <strong>{{ $activeYear ? $activeYear->name : '-' }}</strong>
            </p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="switchTab('academic')" type="button"
                class="{{ $activeTab === 'academic' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Rapor Akademik / KHS
            </button>

            <button wire:click="switchTab('p5')" type="button"
                class="{{ $activeTab === 'p5' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                Rapor Evaluasi P5 (Profil Pancasila)
            </button>
        </nav>
    </div>

    <!-- TAB 1: Rapor Akademik -->
    @if($activeTab === 'academic')
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-r from-indigo-900 to-indigo-800 rounded-2xl p-6 text-white shadow-md">
                <span class="text-xs font-bold text-indigo-200 uppercase tracking-wider block mb-1">Rata-rata Nilai Akhir (Rapor)</span>
                <div class="text-3xl font-black text-white">{{ $averageFinalScore }}</div>
                <div class="text-xs text-indigo-300 mt-1">Capaian Komulatif Pembelajaran</div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Mata Pelajaran Dinilai</span>
                <div class="text-3xl font-black text-slate-800">{{ $gradesList->count() }} <span class="text-xs text-slate-400">Mapel</span></div>
                <span class="text-xs font-semibold text-emerald-600">Terdaftar di KHS</span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Status Rapor Digital</span>
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full inline-block mt-1">
                        ✓ Rapor Resmi Terbit
                    </span>
                </div>
                <button onclick="window.print()" type="button" class="p-3 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-xl transition-colors font-bold text-xs flex items-center gap-1.5 border border-indigo-200">
                    🖨️ Cetak KHS
                </button>
            </div>
        </div>

        <!-- Grades Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Mata Pelajaran</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Tugas</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">UTS</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">UAS</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Nilai Akhir & Predikat</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Capaian Pembelajaran (Deskripsi)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($gradesList as $g)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900">{{ $g->subject?->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $g->subject?->code }}</div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-semibold text-slate-700">
                                        {{ $g->tugas ?? '-' }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-semibold text-slate-700">
                                        {{ $g->uts ?? '-' }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-semibold text-slate-700">
                                        {{ $g->uas ?? '-' }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="text-base font-black text-slate-900">{{ $g->calculated_final }}</span>
                                        @php
                                            $predStyles = [
                                                'A' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                                'B' => 'bg-blue-100 text-blue-800 border-blue-300',
                                                'C' => 'bg-amber-100 text-amber-800 border-amber-300',
                                                'D' => 'bg-rose-100 text-rose-800 border-rose-300',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs font-black rounded-md border {{ $predStyles[$g->calculated_predicate] ?? 'bg-slate-100' }}">
                                            {{ $g->calculated_predicate }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 leading-relaxed text-justify">
                                    {{ $g->notes ?: 'Belum ada deskripsi capaian kompetensi untuk mata pelajaran ini.' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                    Belum ada nilai akademik yang diinput oleh guru mata pelajaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 2: Rapor Evaluasi P5 -->
    @if($activeTab === 'p5')
        @if($p5Projects->isEmpty())
            <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm">
                <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-4 text-amber-500 text-2xl font-bold">
                    ⭐
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Projek P5 Terdaftar</h3>
                <p class="text-slate-500 text-sm">Fasilitator P5 belum merilis evaluasi projek Profil Pelajar Pancasila untuk kelas Anda.</p>
            </div>
        @else
            <div class="space-y-8 mb-6">
                @foreach($p5Projects as $proj)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <div class="border-b border-slate-200 pb-4 mb-6">
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <span class="px-2.5 py-0.5 bg-teal-100 text-teal-800 text-xs font-bold rounded-md">
                                    {{ $proj->theme }}
                                </span>
                                <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-800 text-xs font-bold rounded-md">
                                    {{ $proj->phase }}
                                </span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800">{{ $proj->title }}</h3>
                            @if($proj->description)
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $proj->description }}</p>
                            @endif
                        </div>

                        <!-- Matrix Table -->
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-slate-200 border border-slate-200 rounded-xl overflow-hidden">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Dimensi & Subelemen Pancasila</th>
                                        <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">BB</th>
                                        <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">MB</th>
                                        <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">BSH</th>
                                        <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">SB</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    @foreach($proj->dimensions as $dim)
                                        @php
                                            $pred = $p5AssessmentsMap[$dim->id] ?? 'BSH';
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-3 text-xs text-slate-800">
                                                <div class="font-bold text-teal-800 mb-0.5">{{ $dim->dimension_name }}</div>
                                                <div class="text-slate-600"><strong>{{ $dim->element_name }}</strong>: {{ $dim->sub_element }}</div>
                                            </td>
                                            <!-- BB -->
                                            <td class="px-3 py-3 text-center">
                                                @if($pred === 'BB')
                                                    <span class="w-7 h-7 rounded-full bg-amber-500 text-white font-black text-xs inline-flex items-center justify-center shadow-xs">✓</span>
                                                @else
                                                    <span class="text-slate-300">•</span>
                                                @endif
                                            </td>
                                            <!-- MB -->
                                            <td class="px-3 py-3 text-center">
                                                @if($pred === 'MB')
                                                    <span class="w-7 h-7 rounded-full bg-blue-500 text-white font-black text-xs inline-flex items-center justify-center shadow-xs">✓</span>
                                                @else
                                                    <span class="text-slate-300">•</span>
                                                @endif
                                            </td>
                                            <!-- BSH -->
                                            <td class="px-3 py-3 text-center">
                                                @if($pred === 'BSH')
                                                    <span class="w-7 h-7 rounded-full bg-emerald-600 text-white font-black text-xs inline-flex items-center justify-center shadow-xs">✓</span>
                                                @else
                                                    <span class="text-slate-300">•</span>
                                                @endif
                                            </td>
                                            <!-- SB -->
                                            <td class="px-3 py-3 text-center">
                                                @if($pred === 'SB')
                                                    <span class="w-7 h-7 rounded-full bg-indigo-600 text-white font-black text-xs inline-flex items-center justify-center shadow-xs">✓</span>
                                                @else
                                                    <span class="text-slate-300">•</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Process Notes -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Catatan Perkembangan Proses Projek P5:</h4>
                            <p class="text-xs text-slate-700 leading-relaxed font-medium">
                                {{ $p5NotesMap[$proj->id] ?? 'Siswa menunjukkan antusiasme dan keaktifan yang sangat baik selama pelaksanaan kegiatan projek P5.' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>

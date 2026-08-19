<div>
    <x-slot name="title">Lembar Penilaian Siswa</x-slot>

    <!-- Header Actions -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.academic.grades') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Daftar Penilaian
            </a>
            <h2 class="text-2xl font-bold text-slate-800">
                {{ $subject->name }}
            </h2>
            <div class="flex items-center text-sm text-slate-500 mt-1">
                <span class="inline-flex items-center mr-4">
                    <svg class="w-4 h-4 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Kelas: {{ $classroom->grade_level }} {{ $classroom->name }}
                </span>
                <span class="inline-flex items-center">
                    <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Tahun Ajaran: {{ $activeYear ? $activeYear->name : '-' }}
                </span>
            </div>
        </div>
        <button wire:click="saveGrades" @if(count($students) == 0) disabled @endif class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-slate-300 disabled:cursor-not-allowed">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
            Simpan Nilai
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Grade Table -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        @if(count($students) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-12">No</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider w-24">Tugas (30%)</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider w-24">UTS (30%)</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider w-24">UAS (40%)</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider w-24 bg-indigo-50">Nilai Akhir</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-indigo-600 uppercase tracking-wider w-24 bg-indigo-50">Huruf</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-48">TP Tertinggi</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-48">TP Terendah</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-64">Deskripsi Capaian</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($students as $index => $student)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-500 text-center">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $student->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $student->nis ?: '-' }}</div>
                                </td>
                                <!-- Input Components -->
                                <td class="px-4 py-3">
                                    <input type="number" min="0" max="100" wire:model.live.debounce.500ms="grades.{{ $student->id }}.tugas" wire:change="calculatePreview({{ $student->id }})" class="block w-full text-center rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-2 py-1 border text-slate-700">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" min="0" max="100" wire:model.live.debounce.500ms="grades.{{ $student->id }}.uts" wire:change="calculatePreview({{ $student->id }})" class="block w-full text-center rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-2 py-1 border text-slate-700">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" min="0" max="100" wire:model.live.debounce.500ms="grades.{{ $student->id }}.uas" wire:change="calculatePreview({{ $student->id }})" class="block w-full text-center rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-2 py-1 border text-slate-700">
                                </td>
                                
                                <!-- Calculated Columns -->
                                <td class="px-4 py-3 text-center bg-indigo-50/50">
                                    <span class="text-sm font-bold text-slate-800">{{ $calculatedFinals[$student->id] ?? 0 }}</span>
                                </td>
                                <td class="px-4 py-3 text-center bg-indigo-50/50">
                                    @php
                                        $letter = $calculatedLetters[$student->id] ?? 'E';
                                        $color = match($letter) {
                                            'A' => 'text-emerald-600 bg-emerald-100',
                                            'B' => 'text-blue-600 bg-blue-100',
                                            'C' => 'text-amber-600 bg-amber-100',
                                            'D' => 'text-orange-600 bg-orange-100',
                                            default => 'text-red-600 bg-red-100',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $color }}">
                                        {{ $letter }}
                                    </span>
                                </td>
                                
                                <!-- TP Inputs & Auto-generated Notes -->
                                <td class="px-4 py-3">
                                    <input type="text" wire:model.live.debounce.1000ms="tpHighest.{{ $student->id }}" placeholder="Cth: memahami Aljabar" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-2 py-1 border text-slate-700">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" wire:model.live.debounce.1000ms="tpLowest.{{ $student->id }}" placeholder="Cth: konsep Peluang" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-2 py-1 border text-slate-700">
                                </td>
                                <td class="px-4 py-3">
                                    <textarea wire:model="notes.{{ $student->id }}" rows="2" placeholder="Deskripsi di-generate otomatis..." class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs px-2 py-1 border text-slate-700 leading-tight"></textarea>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center">
                <div class="text-sm text-slate-500 flex items-center">
                    <svg class="w-5 h-5 mr-1 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Rumus Nilai Akhir: (30% Tugas) + (30% UTS) + (40% UAS). Disimpan untuk Semester/Tahun Ajaran aktif.
                </div>
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900">Kelas Kosong</h3>
                <p class="mt-1 text-sm text-slate-500">Tidak ada siswa yang terdaftar aktif di kelas ini.</p>
            </div>
        @endif
    </div>
</div>

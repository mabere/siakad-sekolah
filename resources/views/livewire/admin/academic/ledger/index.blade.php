<div>
    <x-slot name="title">Ledger Nilai Kelas</x-slot>

    @if(!$activeYear)
        <div class="mb-4 bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 rounded shadow-sm">
            Tidak ada Tahun Ajaran yang aktif. Silakan set di menu Master Data.
        </div>
    @endif

    <!-- Control Panel -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6 print:hidden">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Kelas</label>
                <select wire:model.live="filterClassroom" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border bg-slate-50 hover:bg-white transition-colors cursor-pointer">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}">{{ $cls->grade_level }} {{ $cls->name }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-slate-500">Pilih rombongan belajar untuk melihat dan mencetak rekapitulasi nilai seluruh siswa.</p>
            </div>
            <div class="flex items-start sm:justify-end mt-7">
                <button onclick="window.print()" @if(count($students) == 0) disabled @endif class="inline-flex justify-center items-center py-2.5 px-6 shadow-md text-sm font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all disabled:bg-slate-300 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak Ledger (PDF)
                </button>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden print:border-none print:shadow-none print:bg-transparent">
        @if(count($students) > 0)
            <div class="p-6 hidden print:block text-center mb-6">
                <h2 class="text-xl font-bold uppercase">Ledger Nilai Siswa</h2>
                <p class="text-md font-semibold">Tahun Ajaran: {{ $activeYear->name }} - Semester {{ $activeYear->semester }}</p>
                @php
                    $activeClass = $classrooms->firstWhere('id', $filterClassroom);
                @endphp
                <p class="text-md">Kelas: {{ $activeClass->grade_level }} {{ $activeClass->name }}</p>
            </div>
            
            <div class="overflow-x-auto print:overflow-visible">
                <table class="min-w-full divide-y divide-slate-200 print:divide-slate-800 print:border print:border-slate-800 text-sm">
                    <thead class="bg-slate-50 print:bg-white">
                        <tr>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-700 uppercase tracking-wider w-12 print:border print:border-slate-800 print:text-black">No</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider min-w-[200px] print:border print:border-slate-800 print:text-black">Nama Siswa</th>
                            
                            @foreach($subjects as $subject)
                            <th scope="col" class="px-2 py-3 text-center text-xs font-bold text-slate-700 uppercase tracking-wider w-16 print:border print:border-slate-800 print:text-black" title="{{ $subject->name }}">
                                <div class="writing-vertical-rl transform rotate-180 h-32 mx-auto whitespace-nowrap">{{ $subject->name }}</div>
                            </th>
                            @endforeach
                            
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-700 uppercase tracking-wider w-20 print:border print:border-slate-800 print:text-black">Jumlah<br>Nilai</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-700 uppercase tracking-wider w-20 print:border print:border-slate-800 print:text-black">Rata-<br>Rata</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200 print:divide-slate-800">
                        @foreach($students as $index => $student)
                            @php
                                $studentGrades = $grades->get($student->id, collect());
                                $totalScore = 0;
                                $scoreCount = 0;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors print:break-inside-avoid">
                                <td class="px-3 py-2 whitespace-nowrap text-slate-700 text-center font-medium print:border print:border-slate-800 print:text-black">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap print:border print:border-slate-800 print:text-black">
                                    <div class="font-bold text-slate-900 print:text-black">{{ $student->name }}</div>
                                    <div class="text-xs text-slate-500 print:text-slate-700">{{ $student->nis ?: '-' }}</div>
                                </td>
                                
                                @foreach($subjects as $subject)
                                    @php
                                        $grade = $studentGrades->firstWhere('subject_id', $subject->id);
                                        $score = $grade ? $grade->final_score : null;
                                        if ($score !== null) {
                                            $totalScore += $score;
                                            $scoreCount++;
                                        }
                                    @endphp
                                    <td class="px-2 py-2 whitespace-nowrap text-center font-medium print:border print:border-slate-800 print:text-black @if($score !== null && $score < 75) text-red-600 print:text-red-600 @else text-slate-700 @endif">
                                        {{ $score !== null ? $score : '-' }}
                                    </td>
                                @endforeach
                                
                                <td class="px-3 py-2 whitespace-nowrap text-center font-bold text-slate-800 print:border print:border-slate-800 print:text-black bg-slate-50 print:bg-white">
                                    {{ $totalScore }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-center font-bold text-indigo-700 print:border print:border-slate-800 print:text-black bg-indigo-50/30 print:bg-white">
                                    {{ $scoreCount > 0 ? number_format($totalScore / $scoreCount, 2) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="hidden print:flex justify-end mt-12 w-full text-sm">
                <div class="text-center w-64">
                    @php
                        $citySetting = \App\Models\SystemSetting::where('school_id', 1)->where('key', 'city')->first();
                        $city = $citySetting ? $citySetting->value : '';
                    @endphp
                    <p>{{ $city ? $city . ', ' : '' }}{{ date('d F Y') }}</p>
                    <p class="mb-20">Wali Kelas,</p>
                    <p class="font-bold underline">{{ $activeClass->teacher->name ?? 'Wali Kelas' }}</p>
                    <p>NIP. {{ $activeClass->teacher->nip ?? '-' }}</p>
                </div>
            </div>
            
        @else
            @if(!$filterClassroom)
                <div class="p-12 text-center print:hidden">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <h3 class="mt-2 text-sm font-semibold text-slate-900">Belum Ada Kelas Terpilih</h3>
                    <p class="mt-1 text-sm text-slate-500">Pilih kelas di panel atas untuk melihat ledger nilai.</p>
                </div>
            @else
                <div class="p-12 text-center print:hidden">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <h3 class="mt-2 text-sm font-semibold text-slate-900">Kelas Kosong</h3>
                    <p class="mt-1 text-sm text-slate-500">Tidak ada siswa yang terdaftar aktif di kelas ini.</p>
                </div>
            @endif
        @endif
    </div>
    
    <style>
        .writing-vertical-rl {
            writing-mode: vertical-rl;
        }
        @media print {
            @page { size: landscape; margin: 1cm; }
            body { background: white; }
            .print\:hidden { display: none !important; }
            .print\:block { display: block !important; }
            .print\:flex { display: flex !important; }
            .print\:border { border-width: 1px !important; }
            .print\:border-slate-800 { border-color: #1e293b !important; }
            .print\:text-black { color: black !important; }
            .print\:bg-white { background-color: white !important; }
            .print\:bg-transparent { background-color: transparent !important; }
            .print\:shadow-none { box-shadow: none !important; }
            .print\:overflow-visible { overflow: visible !important; }
            .print\:break-inside-avoid { break-inside: avoid; }
        }
    </style>
</div>

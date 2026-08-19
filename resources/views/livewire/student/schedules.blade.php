<div>
    <x-slot name="title">Jadwal Pelajaran Siswa</x-slot>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Jadwal Pelajaran Mingguan Siswa</h2>
            <p class="text-sm text-slate-500 mt-1">
                Kelas: <strong>{{ $student && $student->classroom ? 'Kelas '.$student->classroom->grade_level.' '.$student->classroom->name : '-' }}</strong> | 
                Tahun Ajaran: <strong>{{ $activeYear ? $activeYear->name : '-' }}</strong>
            </p>
        </div>
    </div>

    <!-- Day Filter Selector -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
            <button wire:click="selectDay('Semua')" type="button"
                class="px-4 py-2 text-xs font-bold rounded-xl transition-all whitespace-nowrap {{ $selectedDay === 'Semua' ? 'bg-indigo-600 text-white shadow-sm font-black' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Hari
            </button>
            @foreach($daysList as $day)
                @php
                    $isToday = strtolower($todayDayName) === strtolower($day);
                @endphp
                <button wire:click="selectDay('{{ $day }}')" type="button"
                    class="px-4 py-2 text-xs font-bold rounded-xl transition-all whitespace-nowrap flex items-center gap-1.5 {{ $selectedDay === $day ? 'bg-indigo-600 text-white shadow-sm font-black' : ($isToday ? 'bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200') }}">
                    <span>{{ $day }}</span>
                    @if($isToday)
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <!-- Schedules Grid -->
    @if(empty($schedulesByDay))
        <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm">
            <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-500 text-2xl font-bold">
                📅
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Jadwal Pelajaran</h3>
            <p class="text-slate-500 text-sm">Jadwal pelajaran untuk kelas Anda belum dikonfigurasi oleh pihak kurikulum sekolah.</p>
        </div>
    @else
        <div class="space-y-8">
            @foreach($daysList as $dayName)
                @if(($selectedDay === 'Semua' || $selectedDay === $dayName) && isset($schedulesByDay[$dayName]))
                    @php
                        $isToday = strtolower($todayDayName) === strtolower($dayName);
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl {{ $isToday ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-white' }} font-black flex items-center justify-center text-sm shadow-sm">
                                    {{ substr($dayName, 0, 3) }}
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-800">Hari {{ $dayName }}</h3>
                                    <p class="text-xs text-slate-500">{{ count($schedulesByDay[$dayName]) }} Jam Pelajaran Terdaftar</p>
                                </div>
                            </div>

                            @if($isToday)
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full flex items-center gap-1.5 border border-emerald-200">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> HARI INI
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($schedulesByDay[$dayName] as $sched)
                                @php
                                    $now = \Carbon\Carbon::now()->format('H:i:s');
                                    $isLive = ($isToday && $now >= $sched['start_time'] && $now <= $sched['end_time']);
                                @endphp
                                <div class="p-5 rounded-xl border transition-all {{ $isLive ? 'bg-indigo-50/80 border-indigo-300 shadow-sm' : 'bg-slate-50/70 border-slate-200 hover:border-slate-300' }} flex justify-between items-start gap-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded bg-slate-200 text-slate-700">
                                                {{ $sched['subject']['code'] ?? 'MAPEL' }}
                                            </span>
                                            @if($isLive)
                                                <span class="px-2 py-0.5 bg-indigo-600 text-white text-[10px] font-black rounded-full animate-pulse">
                                                    LIVE NOW
                                                </span>
                                            @endif
                                        </div>

                                        <h4 class="text-base font-bold text-slate-900 mb-1">{{ $sched['subject']['name'] ?? 'Mata Pelajaran' }}</h4>
                                        <div class="text-xs text-slate-600 flex items-center gap-1.5">
                                            <span>👨‍🏫 {{ $sched['teacher']['name'] ?? 'Guru Pengampu' }}</span>
                                        </div>
                                    </div>

                                    <div class="text-right flex-shrink-0">
                                        <div class="px-3 py-1.5 bg-white rounded-lg border border-slate-200 shadow-2xs inline-block">
                                            <div class="text-xs font-black text-slate-800">
                                                ⏰ {{ substr($sched['start_time'], 0, 5) }} - {{ substr($sched['end_time'], 0, 5) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>

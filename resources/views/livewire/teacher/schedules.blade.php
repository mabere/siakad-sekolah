<div>
    <x-slot name="title">Jadwal Mengajar</x-slot>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Jadwal Mengajar</h2>
            <p class="text-sm text-slate-500 mt-1">Tahun Ajaran: {{ $activeYear ? $activeYear->name : 'Tidak ada tahun ajaran aktif' }}</p>
        </div>
    </div>

    @if(!$activeYear)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-md mb-6 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">Saat ini tidak ada Tahun Ajaran yang berstatus aktif. Hubungi Administrator.</p>
                </div>
            </div>
        </div>
    @elseif(empty($schedules))
        <div class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
            <div class="mx-auto w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Jadwal</h3>
            <p class="text-slate-500">Anda belum memiliki jadwal mengajar pada tahun ajaran ini.</p>
        </div>
    @else
        @php $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']; @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($days as $day)
                @if(isset($schedules[$day]))
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-teal-50 border-b border-teal-100 p-4">
                            <h3 class="font-bold text-teal-800 text-lg flex items-center">
                                <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $day }}
                            </h3>
                        </div>
                        <div class="p-0">
                            <ul class="divide-y divide-slate-100">
                                @foreach($schedules[$day] as $schedule)
                                    <li class="p-4 hover:bg-slate-50 transition-colors">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ substr($schedule['start_time'], 0, 5) }} - {{ substr($schedule['end_time'], 0, 5) }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                Kelas {{ $schedule['classroom']['grade_level'] }} {{ $schedule['classroom']['name'] }}
                                            </span>
                                        </div>
                                        <div class="font-bold text-slate-800 text-lg">{{ $schedule['subject']['name'] }}</div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>

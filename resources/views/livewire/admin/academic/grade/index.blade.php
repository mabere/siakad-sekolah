<div>
    <x-slot name="title">Daftar Penilaian Siswa</x-slot>

    @if(!$activeYear)
        <div class="mb-4 bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 rounded shadow-sm">
            Tidak ada Tahun Ajaran yang aktif. Silakan set di menu Master Data.
        </div>
    @else
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Tahun Ajaran: {{ $activeYear->name }}</h2>
                <p class="text-sm text-slate-500 mt-1">Daftar kelas dan mata pelajaran yang tersedia berdasarkan Jadwal Pelajaran.</p>
            </div>
        </div>

        @if(count($assignments) > 0)
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-12">No</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Mata Pelajaran</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kelas</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Guru Pengampu</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider w-32">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($assignments as $index => $assignment)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-slate-800">{{ $assignment->subject->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="bg-indigo-50 text-indigo-700 rounded text-xs font-bold px-2 py-1 uppercase tracking-wider border border-indigo-100">
                                            {{ $assignment->classroom->grade_level }} {{ $assignment->classroom->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-600">{{ $assignment->teacher->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200">
                                            Monitoring
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900">Belum Ada Jadwal Pelajaran</h3>
                <p class="mt-1 text-sm text-slate-500">Daftar tugas penilaian akan otomatis muncul berdasarkan jadwal pelajaran yang telah dibuat.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.academic.schedules') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                        Buat Jadwal Pelajaran
                    </a>
                </div>
            </div>
        @endif
    @endif
</div>

<div>
    <x-slot name="title">Pusat Cetak Rapor</x-slot>

    @if(!$activeYear)
        <div class="mb-4 bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 rounded shadow-sm">
            Tidak ada Tahun Ajaran yang aktif. Silakan set di menu Master Data.
        </div>
    @endif

    <!-- Control Panel -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Cetak Rapor Siswa</h2>
            <p class="text-sm text-slate-500">Tahun Ajaran: <span class="font-semibold">{{ $activeYear ? $activeYear->name : '-' }}</span></p>
        </div>
        <div class="w-full md:w-72">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Pilih Kelas</label>
            <select wire:model.live="filterClassroom" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                <option value="">-- Pilih Kelas --</option>
                @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}">{{ $cls->grade_level }} {{ $cls->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Student List Table -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        @if(count($students) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-12">No</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">NIS / NISN</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($students as $index => $student)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-800">{{ $student->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-600">{{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('admin.academic.report-cards.show', $student->id) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Lihat Rapor
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            @if(!$filterClassroom)
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">Belum Ada Kelas Terpilih</h3>
                    <p class="mt-1 text-sm text-slate-500">Pilih kelas di atas untuk melihat daftar siswa.</p>
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">Kelas Kosong</h3>
                    <p class="mt-1 text-sm text-slate-500">Tidak ada siswa yang terdaftar aktif di kelas ini.</p>
                </div>
            @endif
        @endif
    </div>
</div>

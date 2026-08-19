<div>
    <x-slot name="title">Rekap Presensi Semester</x-slot>

    <div class="mb-4 bg-slate-100 border-l-4 border-slate-400 text-slate-700 p-4 rounded shadow-sm">
        Mode monitoring: pengisian dan perubahan presensi dilakukan oleh guru melalui portal Guru.
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

    @if(!$activeYear)
        <div class="mb-4 bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 rounded shadow-sm">
            Tidak ada Tahun Ajaran yang aktif. Silakan set di menu Master Data.
        </div>
    @endif

    <!-- Control Panel -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Kelas</label>
                <select wire:model.live="filterClassroom" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border bg-slate-50 hover:bg-white transition-colors cursor-pointer">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}">{{ $cls->grade_level }} {{ $cls->name }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-slate-500">Pilih rombongan belajar untuk melihat rekap presensi (Sakit, Izin, Alpa) selama semester aktif.</p>
            </div>
            <div class="flex items-start sm:justify-end mt-7">
                <span class="text-sm font-semibold text-slate-500">Monitoring saja</span>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        @if(count($students) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-12 text-center">No</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Sakit</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Izin</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Alpa</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-64">Catatan (Opsional)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($students as $index => $student)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center font-medium">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900">{{ $student->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $student->nis ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="block text-center font-semibold text-slate-700">{{ $sickCounts[$student->id] ?? 0 }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="block text-center font-semibold text-slate-700">{{ $permissionCounts[$student->id] ?? 0 }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="block text-center font-semibold text-slate-700">{{ $absentCounts[$student->id] ?? 0 }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="block text-slate-600">{{ $notes[$student->id] ?: '-' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-slate-200 bg-slate-50 flex justify-end items-center gap-4">
                <span class="text-sm text-slate-500">Data ini bersifat read-only untuk kebutuhan monitoring.</span>
            </div>
        @else
            @if(!$filterClassroom)
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <h3 class="mt-2 text-sm font-semibold text-slate-900">Belum Ada Kelas Terpilih</h3>
                    <p class="mt-1 text-sm text-slate-500">Pilih kelas di panel atas untuk melihat daftar siswa.</p>
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <h3 class="mt-2 text-sm font-semibold text-slate-900">Kelas Kosong</h3>
                    <p class="mt-1 text-sm text-slate-500">Tidak ada siswa yang terdaftar aktif di kelas ini.</p>
                </div>
            @endif
        @endif
    </div>
</div>

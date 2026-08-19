<div>
    <x-slot name="title">Jadwal Pelajaran</x-slot>

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
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto flex-1">
            <div class="w-full sm:w-64">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tahun Ajaran</label>
                <select wire:model.live="filterAcademicYear" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full sm:w-64">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Filter Kelas</label>
                <select wire:model.live="filterClassroom" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}">{{ $cls->grade_level }} {{ $cls->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @if(!$isReadOnly)
        <button wire:click="create" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Jadwal
        </button>
        @endif
    </div>

    @if($isReadOnly)
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded shadow-sm flex items-start">
            <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="font-bold">Mode Riwayat (Hanya Baca)</p>
                <p class="text-sm">Anda sedang melihat jadwal dari tahun ajaran masa lalu. Penambahan, pengubahan, maupun penghapusan jadwal dinonaktifkan untuk menjaga integritas data historis.</p>
            </div>
        </div>
    @endif

    <div class="w-full">
        <!-- Form Panel -->
        @if ($isFormOpen)
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
                <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                    <h2 class="text-lg font-semibold text-slate-800">{{ $isEdit ? 'Edit Jadwal' : 'Tambah Jadwal Baru' }}</h2>
                    <button wire:click="resetForm" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit.prevent="save" class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Mata Pelajaran</label>
                            <select wire:model="subject_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($subjects as $subj)
                                    <option value="{{ $subj->id }}">{{ $subj->name }} ({{ $subj->code }})</option>
                                @endforeach
                            </select>
                            @error('subject_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Guru Pengajar</label>
                            <select wire:model="teacher_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Hari</label>
                            <select wire:model="day_of_week" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                                <option value="">-- Pilih Hari --</option>
                                @foreach($days as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                            @error('day_of_week') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Jam Mulai</label>
                                <input type="time" wire:model="start_time" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                                @error('start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Jam Selesai</label>
                                <input type="time" wire:model="end_time" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                                @error('end_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="button" wire:click="resetForm" class="mr-2 bg-white py-2 px-4 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Batal
                        </button>
                        <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Schedules Grid -->
        @if($filterClassroom && $filterAcademicYear)
            <div class="space-y-6">
                @foreach($days as $day)
                    @php $daySchedules = $schedules[$day] ?? []; @endphp
                    @if(count($daySchedules) > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 px-6 py-3 border-b border-slate-200 flex justify-between items-center">
                            <h3 class="font-bold text-slate-800 text-lg">{{ $day }}</h3>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jam Pelajaran</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Mata Pelajaran</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Guru Pengampu</th>
                                        @if(!$isReadOnly)
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider w-24">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    @foreach($daySchedules as $schedule)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-slate-700 bg-slate-100 inline-block px-2 py-1 rounded">
                                                    {{ date('H:i', strtotime($schedule->start_time)) }} - {{ date('H:i', strtotime($schedule->end_time)) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-semibold text-indigo-600">{{ $schedule->subject->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold mr-3">
                                                        {{ substr($schedule->teacher->name, 0, 1) }}
                                                    </div>
                                                    <div class="text-sm text-slate-900">{{ $schedule->teacher->name }}</div>
                                                </div>
                                            </td>
                                            @if(!$isReadOnly)
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button wire:click="edit({{ $schedule->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>
                                                <button wire:click="delete({{ $schedule->id }})" wire:confirm="Yakin ingin menghapus jadwal ini?" class="text-red-600 hover:text-red-900" title="Hapus">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <h3 class="text-lg font-medium text-slate-800">Pilih Parameter Terlebih Dahulu</h3>
                <p class="text-sm text-slate-500 mt-1">Gunakan filter Tahun Ajaran dan Kelas di atas untuk melihat atau mengelola jadwal.</p>
            </div>
        @endif
    </div>
</div>

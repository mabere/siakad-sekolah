<div>
    <x-slot name="title">Presensi Siswa</x-slot>

    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Presensi & Kehadiran Siswa</h2>
            <p class="text-sm text-slate-500 mt-1">Tahun Ajaran: {{ $activeYear ? $activeYear->name : '-' }}</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="switchTab('subject')" type="button"
                class="{{ $activeTab === 'subject' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C20.832 18.477 19.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Presensi Mata Pelajaran (Per Pertemuan)
            </button>

            @if($isHomeroomTeacher)
            <button wire:click="switchTab('homeroom')" type="button"
                class="{{ $activeTab === 'homeroom' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Rekapitulasi Rapor Wali Kelas
                <span class="bg-teal-100 text-teal-800 text-xs px-2 py-0.5 rounded-full font-semibold">Wali Kelas</span>
            </button>
            @endif
        </nav>
    </div>

    <!-- Tab 1: Presensi Mata Pelajaran (Per Pertemuan) -->
    @if($activeTab === 'subject')
        @if(!$activeYear)
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-md mb-6 shadow-sm">
                <p class="text-sm text-amber-700">Saat ini tidak ada Tahun Ajaran yang berstatus aktif.</p>
            </div>
        @elseif(empty($schedules))
            <div class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
                <div class="mx-auto w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak Ada Kelas Diajar</h3>
                <p class="text-slate-500">Anda tidak terdaftar sebagai pengajar di jadwal manapun pada tahun ajaran aktif ini.</p>
            </div>
        @else
            <!-- Filter Bar -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="subject-schedule" class="block text-sm font-medium text-slate-700 mb-2">Pilih Mata Pelajaran & Kelas</label>
                        <select id="subject-schedule" wire:model.live="selectedScheduleId" class="block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm rounded-md shadow-sm">
                            @foreach($schedules as $sched)
                                <option value="{{ $sched['id'] }}">
                                    {{ $sched['subject']['name'] }} - Kelas {{ $sched['classroom']['grade_level'] }} {{ $sched['classroom']['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="attendance-date" class="block text-sm font-medium text-slate-700 mb-2">Tanggal Pertemuan</label>
                        <input type="date" id="attendance-date" wire:model.live="attendanceDate" class="block w-full px-3 py-2 border-slate-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm rounded-md shadow-sm">
                    </div>

                    <div>
                        <label for="meeting-number" class="block text-sm font-medium text-slate-700 mb-2">Pertemuan Ke-</label>
                        <input type="number" min="1" max="100" id="meeting-number" wire:model="meetingNumber" class="block w-full px-3 py-2 border-slate-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm rounded-md shadow-sm">
                    </div>
                </div>
            </div>

            <!-- Flash Notifications -->
            @if (session()->has('subject_attendance_success'))
                <div class="mb-4 p-4 bg-green-50 text-green-700 text-sm rounded-xl border border-green-200 shadow-sm flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('subject_attendance_success') }}</span>
                </div>
            @endif

            @if (session()->has('subject_attendance_error'))
                <div class="mb-4 p-4 bg-red-50 text-red-700 text-sm rounded-xl border border-red-200 shadow-sm">
                    {{ session('subject_attendance_error') }}
                </div>
            @endif

            <!-- Quick Action Bar -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi Cepat Massal:</span>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="setAllSubjectStatus('Hadir')" type="button" class="px-3 py-1.5 bg-emerald-100 text-emerald-800 hover:bg-emerald-200 text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                        ✓ Set Semua Hadir
                    </button>
                    <button wire:click="setAllSubjectStatus('Sakit')" type="button" class="px-3 py-1.5 bg-amber-100 text-amber-800 hover:bg-amber-200 text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                        S Sakit
                    </button>
                    <button wire:click="setAllSubjectStatus('Izin')" type="button" class="px-3 py-1.5 bg-blue-100 text-blue-800 hover:bg-blue-200 text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                        I Izin
                    </button>
                    <button wire:click="setAllSubjectStatus('Alpa')" type="button" class="px-3 py-1.5 bg-rose-100 text-rose-800 hover:bg-rose-200 text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                        A Alpa
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status Kehadiran</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Catatan KBM</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($subjectStudents as $student)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-slate-900">{{ $student->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $student->nisn }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $currentStatus = $subjectAttendanceData[$student->id]['status'] ?? 'Hadir';
                                        @endphp
                                        <div class="inline-flex rounded-md shadow-sm" role="group">
                                            <button type="button"
                                                wire:click="$set('subjectAttendanceData.{{ $student->id }}.status', 'Hadir')"
                                                class="px-3 py-1.5 text-xs font-bold transition-all border rounded-l-lg {{ $currentStatus === 'Hadir' ? 'bg-emerald-600 text-white border-emerald-600 shadow-inner font-black' : 'bg-white text-emerald-700 hover:bg-emerald-50 border-slate-300' }}">
                                                Hadir
                                            </button>
                                            <button type="button"
                                                wire:click="$set('subjectAttendanceData.{{ $student->id }}.status', 'Sakit')"
                                                class="px-3 py-1.5 text-xs font-bold transition-all border-t border-b border-r {{ $currentStatus === 'Sakit' ? 'bg-amber-500 text-white border-amber-500 shadow-inner font-black' : 'bg-white text-amber-700 hover:bg-amber-50 border-slate-300' }}">
                                                Sakit
                                            </button>
                                            <button type="button"
                                                wire:click="$set('subjectAttendanceData.{{ $student->id }}.status', 'Izin')"
                                                class="px-3 py-1.5 text-xs font-bold transition-all border-t border-b border-r {{ $currentStatus === 'Izin' ? 'bg-blue-600 text-white border-blue-600 shadow-inner font-black' : 'bg-white text-blue-700 hover:bg-blue-50 border-slate-300' }}">
                                                Izin
                                            </button>
                                            <button type="button"
                                                wire:click="$set('subjectAttendanceData.{{ $student->id }}.status', 'Alpa')"
                                                class="px-3 py-1.5 text-xs font-bold transition-all border-t border-b border-r rounded-r-lg {{ $currentStatus === 'Alpa' ? 'bg-rose-600 text-white border-rose-600 shadow-inner font-black' : 'bg-white text-rose-700 hover:bg-rose-50 border-slate-300' }}">
                                                Alpa
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" wire:model="subjectAttendanceData.{{ $student->id }}.notes" placeholder="Opsional (misal: Terlambat 10 menit)" class="w-full p-2 rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                        Tidak ada siswa aktif di kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if(!$subjectStudents->isEmpty())
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                        <button wire:click="saveSubjectAttendance" type="button" class="inline-flex items-center px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Presensi Pertemuan Ini
                        </button>
                    </div>
                @endif
            </div>
        @endif
    @endif

    <!-- Tab 2: Rekapitulasi Rapor Wali Kelas -->
    @if($activeTab === 'homeroom')
        @if(!$isHomeroomTeacher)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-8 text-center shadow-sm">
                <div class="mx-auto w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-amber-900 mb-1">Bukan Wali Kelas</h3>
                <p class="text-sm text-amber-700 max-w-md mx-auto">Anda tidak terdaftar sebagai Wali Kelas pada tahun ajaran aktif ini. Tab ini khusus digunakan oleh Wali Kelas untuk menginput rekapitulasi kumulatif kehadiran rapor.</p>
            </div>
        @else
            <div class="mb-4 bg-teal-50 border border-teal-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-teal-100 text-teal-700 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h5m-5 0v-4m0 4h5m-5 0v-4"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-teal-900 text-sm">Rekapitulasi Kehadiran Semester Rapor</h4>
                        <p class="text-xs text-teal-700">Wali Kelas: {{ $homeroomClass ? 'Kelas '.$homeroomClass->grade_level.' '.$homeroomClass->name : '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Table Wali Kelas -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Sakit (Hari)</th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Izin (Hari)</th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Tanpa Keterangan (Hari)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Catatan</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($homeroomStudents as $student)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-slate-900">{{ $student->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $student->nisn }}</div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center">
                                        <input type="number" min="0" max="366" wire:model="attendanceData.{{ $student->id }}.sick" class="w-20 p-2 rounded-md border-slate-300 text-center shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center">
                                        <input type="number" min="0" max="366" wire:model="attendanceData.{{ $student->id }}.permission" class="w-20 p-2 rounded-md border-slate-300 text-center shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center">
                                        <input type="number" min="0" max="366" wire:model="attendanceData.{{ $student->id }}.absent" class="w-20 p-2 rounded-md border-slate-300 text-center shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" wire:model="attendanceData.{{ $student->id }}.notes" placeholder="Opsional" class="w-full p-2 rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="saveAttendance({{ $student->id }})" class="text-white bg-teal-600 hover:bg-teal-700 focus:ring-4 focus:ring-teal-300 font-medium rounded-lg text-xs px-3 py-1.5 transition-colors">
                                            Simpan
                                        </button>
                                        @if(session()->has('success_'.$student->id))
                                            <div class="text-xs font-bold text-teal-600 mt-1 block">
                                                {{ session('success_'.$student->id) }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                        Tidak ada data siswa pada kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>

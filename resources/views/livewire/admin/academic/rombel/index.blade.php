<div>
    <x-slot name="title">Penempatan Siswa (Rombel)</x-slot>

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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Kolom Kiri: Siswa Tersedia -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 flex flex-col h-[800px]">
            <div class="p-4 border-b border-slate-200 bg-slate-50 rounded-t-lg">
                <h2 class="text-lg font-semibold text-slate-800">Daftar Siswa Tersedia</h2>
                <p class="text-sm text-slate-500 mt-1">Pilih sumber data siswa yang ingin dimasukkan ke kelas</p>
                
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Sumber Siswa</label>
                        <select wire:model.live="sourceFilter" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                            <option value="unassigned">Belum Punya Kelas (Baru/Keluar)</option>
                            <optgroup label="Dari Kelas Tertentu">
                                @foreach($classrooms as $cls)
                                    @if($cls->id != $targetClassroom)
                                        <option value="{{ $cls->id }}">{{ $cls->grade_level }} {{ $cls->name }}</option>
                                    @endif
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Cari Siswa</label>
                        <input type="text" wire:model.live.debounce.300ms="searchAvailable" placeholder="Nama / NISN..." class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    </div>
                </div>
            </div>
            
            <!-- List Panel Kiri -->
            <div class="flex-1 overflow-y-auto p-4 bg-slate-50/50">
                @if(count($availableStudents) > 0)
                    <div class="space-y-2">
                        @foreach($availableStudents as $student)
                            <label class="flex items-center p-3 bg-white border border-slate-200 rounded-lg hover:bg-indigo-50 cursor-pointer transition-colors shadow-sm">
                                <input type="checkbox" wire:model.live="selectedAvailableStudents" value="{{ $student->id }}" class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-slate-800">{{ $student->name }}</div>
                                    <div class="text-xs text-slate-500">NIS/NISN: {{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }} • {{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        {{ $availableStudents->links() }}
                    </div>
                @else
                    <div class="text-center py-12 text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <p class="mt-2 text-sm font-medium">Tidak ada siswa yang tersedia</p>
                    </div>
                @endif
            </div>

            <!-- Action Kiri -->
            <div class="p-4 border-t border-slate-200 bg-white rounded-b-lg flex justify-between items-center">
                <span class="text-sm font-medium text-slate-600">
                    Terpilih: <strong class="text-indigo-600">{{ count($selectedAvailableStudents) }}</strong>
                </span>
                <button 
                    wire:click="assignStudents" 
                    wire:loading.attr="disabled"
                    wire:target="assignStudents"
                    wire:confirm="Masukkan {{ count($selectedAvailableStudents) }} siswa ke Kelas Tujuan?"
                    @if(count($selectedAvailableStudents) == 0 || !$targetClassroom) disabled @endif
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-slate-300 disabled:cursor-not-allowed">
                    Masukkan ke Kelas 
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </div>


        <!-- Kolom Kanan: Kelas Tujuan -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 flex flex-col h-[800px]">
            <div class="p-4 border-b border-slate-200 bg-indigo-50 rounded-t-lg">
                <h2 class="text-lg font-semibold text-slate-800">Kelas Tujuan (Rombel)</h2>
                <p class="text-sm text-slate-500 mt-1">Pilih kelas dan atur anggota siswanya</p>
                
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Kelas Tujuan</label>
                        <select wire:model.live="targetClassroom" class="block w-full rounded-md border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white font-medium text-indigo-700">
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            @foreach($classrooms as $cls)
                                @if($cls->id != $sourceFilter)
                                    <option value="{{ $cls->id }}">{{ $cls->grade_level }} {{ $cls->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Cari Siswa Terdaftar</label>
                        <input type="text" wire:model.live.debounce.300ms="searchAssigned" placeholder="Nama / NISN..." class="block w-full rounded-md border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    </div>
                </div>
            </div>
            
            <!-- List Panel Kanan -->
            <div class="flex-1 overflow-y-auto p-4 bg-slate-50/50">
                @if(!$targetClassroom)
                    <div class="text-center py-12 text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <p class="mt-2 text-sm font-medium">Silakan pilih Kelas Tujuan</p>
                    </div>
                @elseif(count($assignedStudents) > 0)
                    <div class="mb-3 text-sm font-medium text-slate-600 bg-white p-2 rounded border border-slate-200 text-center shadow-sm">
                        Total Terdaftar: <strong class="text-indigo-600">{{ $assignedStudents->total() }} Siswa</strong>
                    </div>
                    <div class="space-y-2">
                        @foreach($assignedStudents as $student)
                            <label class="flex items-center p-3 bg-white border border-slate-200 rounded-lg hover:bg-red-50 cursor-pointer transition-colors shadow-sm">
                                <input type="checkbox" wire:model.live="selectedAssignedStudents" value="{{ $student->id }}" class="w-5 h-5 rounded border-slate-300 text-red-600 focus:ring-red-500">
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-slate-800">{{ $student->name }}</div>
                                    <div class="text-xs text-slate-500">NIS/NISN: {{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }} • {{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        {{ $assignedStudents->links() }}
                    </div>
                @else
                    <div class="text-center py-12 text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <p class="mt-2 text-sm font-medium">Belum ada siswa di kelas ini</p>
                    </div>
                @endif
            </div>

            <!-- Action Kanan -->
            <div class="p-4 border-t border-slate-200 bg-white rounded-b-lg flex justify-between items-center">
                <button 
                    wire:click="removeStudents" 
                    wire:loading.attr="disabled"
                    wire:target="removeStudents"
                    wire:confirm="Keluarkan {{ count($selectedAssignedStudents) }} siswa dari kelas ini?"
                    @if(count($selectedAssignedStudents) == 0) disabled @endif
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:bg-slate-300 disabled:cursor-not-allowed">
                    <svg class="mr-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Keluarkan dari Kelas
                </button>
                <span class="text-sm font-medium text-slate-600">
                    Terpilih: <strong class="text-red-600">{{ count($selectedAssignedStudents) }}</strong>
                </span>
            </div>
        </div>

    </div>
</div>

<div>
    <x-slot name="title">Kenaikan Kelas & Kelulusan</x-slot>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Kenaikan & Kelulusan Kelas</h2>
            <p class="text-sm text-slate-500 mt-1">Proses perpindahan tingkat, mutasi, dan kelulusan siswa secara masal.</p>
        </div>
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

    @if(!$isPromotionUnlocked)
    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-md mb-6 shadow-sm flex items-start">
        <svg class="h-6 w-6 text-amber-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <h3 class="text-sm font-bold text-amber-800">Mode Akhir Tahun Akademik Belum Diaktifkan</h3>
            <div class="mt-1 text-sm text-amber-700">
                <p>Saat ini Anda hanya dapat melakukan <b>Pindah Kelas (Mutasi Internal)</b>. Untuk memproses Naik Kelas, Tinggal Kelas, atau Lulus Sekolah, silakan aktifkan mode akhir tahun melalui menu <a href="{{ route('admin.settings') }}" class="font-bold underline hover:text-amber-900">Pengaturan Sistem</a>.</p>
            </div>
        </div>
    </div>
    @else
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md mb-6 shadow-sm">
        <div class="flex items-start">
            <svg class="h-6 w-6 text-blue-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <h3 class="text-sm font-bold text-blue-800">Mode Akhir Tahun Akademik Aktif</h3>
                <div class="mt-1 text-sm text-blue-700">
                    <p>Seluruh fitur eksekusi (Naik Kelas, Pindah Kelas, Tinggal Kelas, dan Lulus Sekolah) telah terbuka. Pastikan Anda berhati-hati dalam memproses data siswa.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Panel Kiri: Sumber -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-200 bg-slate-50">
                        <h3 class="font-bold text-slate-800">1. Pilih Sumber Kelas & Siswa</h3>
                        <div class="mt-4">
                            <select wire:model.live="sourceClassroomId" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border bg-white">
                                <option value="">-- Pilih Kelas Asal --</option>
                                @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}">{{ $cls->grade_level }} {{ $cls->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    @if($sourceClassroomId)
                        @if(count($students) > 0)
                            <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
                                <table class="min-w-full divide-y divide-slate-200 relative">
                                    <thead class="bg-white sticky top-0 shadow-sm z-10">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-12">
                                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-16">No</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Siswa / NIS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-200">
                                        @foreach($students as $index => $student)
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="px-6 py-3 whitespace-nowrap text-center">
                                                    <input type="checkbox" wire:model.live="selectedStudents" value="{{ $student->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-500 font-medium">
                                                    {{ $index + 1 }}
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap">
                                                    <div class="text-sm font-bold text-slate-900">{{ $student->name }}</div>
                                                    <div class="text-xs text-slate-500">{{ $student->nis ?: '-' }}</div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-4 bg-slate-50 border-t border-slate-200 text-sm text-slate-600 font-medium">
                                <span class="text-indigo-600 font-bold">{{ count($selectedStudents) }}</span> siswa dipilih dari total {{ count($students) }} siswa.
                            </div>
                        @else
                            <div class="p-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <h3 class="mt-2 text-sm font-semibold text-slate-900">Kelas Kosong</h3>
                                <p class="mt-1 text-sm text-slate-500">Tidak ada siswa yang terdaftar aktif di kelas ini.</p>
                            </div>
                        @endif
                    @else
                        <div class="p-12 text-center">
                            <h3 class="mt-2 text-sm font-semibold text-slate-900">Belum Memilih Kelas Asal</h3>
                            <p class="mt-1 text-sm text-slate-500">Silakan pilih kelas asal terlebih dahulu.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Panel Kanan: Aksi & Target -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
                    <div class="p-6 border-b border-slate-200 bg-indigo-50">
                        <h3 class="font-bold text-indigo-900">2. Tentukan Aksi</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-3">Pilih Aksi</label>
                            <div class="space-y-3">
                                <label class="flex items-start p-3 border rounded-lg transition-colors {{ $actionType === 'promote' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 hover:bg-slate-50' }} {{ ($sourceClassroomId && $isFinalGrade) || !$isPromotionUnlocked ? 'opacity-60 cursor-not-allowed bg-slate-50 hover:bg-slate-50' : 'cursor-pointer' }}">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="radio" wire:model.live="actionType" value="promote" class="form-radio h-4 w-4 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50" @if(($sourceClassroomId && $isFinalGrade) || !$isPromotionUnlocked) disabled @endif>
                                    </div>
                                    <div class="ml-3">
                                        <span class="block font-medium {{ ($sourceClassroomId && $isFinalGrade) || !$isPromotionUnlocked ? 'text-slate-500' : 'text-slate-900' }}">Naik Kelas (Ke Tingkat Selanjutnya)</span>
                                        @if(!$isPromotionUnlocked)
                                            <span class="block text-xs text-red-500 mt-1">Akses belum dibuka.</span>
                                        @elseif($sourceClassroomId && $isFinalGrade)
                                            <span class="block text-xs text-red-500 mt-1">Siswa sudah berada di tingkat akhir.</span>
                                        @endif
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $actionType === 'transfer' ? 'border-amber-500 bg-amber-50' : 'border-slate-200 hover:bg-slate-50' }}">
                                    <input type="radio" wire:model.live="actionType" value="transfer" class="form-radio h-4 w-4 text-amber-600 focus:ring-amber-500">
                                    <span class="ml-3 font-medium text-slate-900">Pindah Kelas (Tingkat Sama)</span>
                                </label>
                                <label class="flex items-start p-3 border rounded-lg transition-colors {{ $actionType === 'stay' ? 'border-orange-500 bg-orange-50' : 'border-slate-200 hover:bg-slate-50' }} {{ !$isPromotionUnlocked ? 'opacity-60 cursor-not-allowed bg-slate-50 hover:bg-slate-50' : 'cursor-pointer' }}">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="radio" wire:model.live="actionType" value="stay" class="form-radio h-4 w-4 text-orange-600 focus:ring-orange-500 disabled:opacity-50" @if(!$isPromotionUnlocked) disabled @endif>
                                    </div>
                                    <div class="ml-3">
                                        <span class="block font-medium {{ !$isPromotionUnlocked ? 'text-slate-500' : 'text-slate-900' }}">Tinggal Kelas (Mengulang Tingkat)</span>
                                        @if(!$isPromotionUnlocked)
                                            <span class="block text-xs text-red-500 mt-1">Akses belum dibuka.</span>
                                        @endif
                                    </div>
                                </label>
                                <label class="flex items-start p-3 border rounded-lg transition-colors {{ $actionType === 'graduate' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 hover:bg-slate-50' }} {{ !$sourceClassroomId || !$isFinalGrade || !$isPromotionUnlocked ? 'opacity-60 cursor-not-allowed bg-slate-50 hover:bg-slate-50' : 'cursor-pointer' }}">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="radio" wire:model.live="actionType" value="graduate" class="form-radio h-4 w-4 text-emerald-600 focus:ring-emerald-500 disabled:opacity-50" @if(!$sourceClassroomId || !$isFinalGrade || !$isPromotionUnlocked) disabled @endif>
                                    </div>
                                    <div class="ml-3">
                                        <span class="block font-medium {{ !$sourceClassroomId || !$isFinalGrade || !$isPromotionUnlocked ? 'text-slate-500' : 'text-slate-900' }}">Lulus Sekolah</span>
                                        @if(!$isPromotionUnlocked)
                                            <span class="block text-xs text-red-500 mt-1">Akses belum dibuka.</span>
                                        @elseif(!$sourceClassroomId)
                                            <span class="block text-xs text-red-500 mt-1">Pilih kelas asal terlebih dahulu.</span>
                                        @elseif(!$isFinalGrade)
                                            <span class="block text-xs text-red-500 mt-1">Siswa belum berada di tingkat akhir.</span>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        </div>

                        @if($actionType === 'promote' || $actionType === 'transfer' || $actionType === 'stay')
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Kelas Tujuan</label>
                                @if(count($targetClassrooms) > 0)
                                    <select wire:model="targetClassroomId" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border bg-white">
                                        <option value="">-- Pilih Kelas Tujuan --</option>
                                        @foreach($targetClassrooms as $cls)
                                            <option value="{{ $cls->id }}">{{ $cls->grade_level }} {{ $cls->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-2 text-xs text-slate-500">
                                        @if($actionType === 'promote')
                                            Hanya menampilkan rombel di tingkat atasnya.
                                        @elseif($actionType === 'stay')
                                            Menampilkan rombel di tingkat yang sama (bisa kembali ke kelas semula).
                                        @else
                                            Hanya menampilkan rombel di tingkat yang sama.
                                        @endif
                                    </p>
                                @else
                                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 text-center">
                                        @if($actionType === 'promote')
                                            Tidak ada kelas yang lebih tinggi. Pastikan rombel di tingkat atas sudah dibuat.
                                        @else
                                            Tidak ada kelas yang tersedia di tingkat ini.
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        <div class="pt-4 border-t border-slate-200">
                            <button wire:click="processPromotion" @if(empty($selectedStudents) || (($actionType === 'promote' || $actionType === 'transfer' || $actionType === 'stay') && empty($targetClassrooms))) disabled @endif onclick="confirm('Apakah Anda yakin ingin memproses {{ count($selectedStudents) }} siswa ini?') || event.stopImmediatePropagation()" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors disabled:bg-slate-300 disabled:cursor-not-allowed">
                                Proses {{ count($selectedStudents) }} Siswa
                            </button>
                            @if(empty($selectedStudents))
                                <p class="text-xs text-center mt-2 text-slate-500">Pilih minimal 1 siswa terlebih dahulu</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

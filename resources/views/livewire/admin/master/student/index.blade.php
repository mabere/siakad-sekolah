<div>
    <x-slot name="title">Data Siswa</x-slot>

    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <!-- Form Panel -->
    @if ($isFormOpen)
    <div x-data x-init="window.scrollTo({top: 0, behavior: 'smooth'})" class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-800">{{ $isEdit ? 'Edit Data Siswa' : 'Tambah Data Siswa' }}</h2>
            <button wire:click="resetForm" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form wire:submit.prevent="save" class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700">Nama Lengkap Siswa</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">NISN (Opsional)</label>
                    <input type="text" wire:model="nisn" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('nisn') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">NIS (Opsional)</label>
                    <input type="text" wire:model="nis" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('nis') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Jenis Kelamin</label>
                    <select wire:model="gender" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                    @error('gender') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tempat Lahir</label>
                    <input type="text" wire:model="birth_place" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('birth_place') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Lahir</label>
                    <input type="date" wire:model="birth_date" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('birth_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Agama</label>
                    <select wire:model="religion" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Katolik">Katolik</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Buddha">Buddha</option>
                        <option value="Konghucu">Konghucu</option>
                    </select>
                    @error('religion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700">Alamat</label>
                    <textarea wire:model="address" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"></textarea>
                    @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Telepon Orang Tua / Wali</label>
                    <input type="text" wire:model="parent_phone" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('parent_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Penempatan Kelas</label>
                    <select wire:model="classroom_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                        <option value="">-- Belum ada kelas --</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->grade_level }} {{ $cls->name }}</option>
                        @endforeach
                    </select>
                    @error('classroom_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Status Akademik</label>
                    <select wire:model="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                        <option value="Aktif">Aktif</option>
                        <option value="Lulus">Lulus</option>
                        <option value="Pindah">Pindah</option>
                        <option value="Keluar">Keluar</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="button" wire:click="resetForm" class="mr-2 bg-white py-2 px-4 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Batal
                </button>
                <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Simpan
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
        <div class="p-4 border-b border-slate-200 flex flex-col md:flex-row md:justify-between md:items-center bg-slate-50 rounded-t-lg gap-4">
            <h2 class="text-lg font-semibold text-slate-800">Daftar Siswa</h2>
            
            <div class="flex flex-col sm:flex-row gap-2">
                <!-- Filter Kelas -->
                <select wire:model.live="filter_classroom" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border w-full sm:w-auto bg-white">
                    <option value="">Semua Kelas</option>
                    <option value="unassigned">Belum Ada Kelas</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}">{{ $cls->grade_level }} {{ $cls->name }}</option>
                    @endforeach
                </select>
                
                <!-- Search -->
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, NISN, NIS..." class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border w-full sm:w-64">
                
                @if (!$isFormOpen)
                <button wire:click="create" class="inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                    <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Siswa
                </button>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Siswa & Kelas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Identitas Induk</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($students as $student)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $student->name }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : '-') }} 
                                    • Kelas: 
                                    @if($student->classroom)
                                        <span class="font-medium text-indigo-600">{{ $student->classroom->grade_level }} {{ $student->classroom->name }}</span>
                                    @else
                                        <span class="text-orange-500 italic">Belum diset</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">NISN: {{ $student->nisn ?: '-' }}</div>
                                <div class="text-xs text-slate-500">NIS: {{ $student->nis ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                @if($student->status === 'Aktif')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                @elseif($student->status === 'Lulus')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Lulus</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-800">{{ $student->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit({{ $student->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                <button wire:click="delete({{ $student->id }})" wire:confirm="Apakah Anda yakin ingin menghapus data siswa ini?" class="text-red-600 hover:text-red-900">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                @if($search || $filter_classroom)
                                    Pencarian atau filter tidak menemukan hasil.
                                @else
                                    Belum ada data siswa.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $students->links() }}
        </div>
    </div>
</div>

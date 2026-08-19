<div>
    <x-slot name="title">Manajemen Kelas</x-slot>

    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Form Panel -->
    @if ($isFormOpen)
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-800">{{ $isEdit ? 'Edit Kelas' : 'Tambah Kelas Baru' }}</h2>
            <button wire:click="resetForm" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form wire:submit.prevent="save" class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tahun Ajaran</label>
                    <select wire:model="academic_year_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->name }} (Semester {{ $ay->semester }})</option>
                        @endforeach
                    </select>
                    @error('academic_year_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tingkat Kelas</label>
                    <select wire:model="grade_level" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                        <option value="">-- Pilih Tingkat --</option>
                        @if($schoolLevel === 'SD')
                            <option value="1">Kelas 1</option>
                            <option value="2">Kelas 2</option>
                            <option value="3">Kelas 3</option>
                            <option value="4">Kelas 4</option>
                            <option value="5">Kelas 5</option>
                            <option value="6">Kelas 6</option>
                        @elseif($schoolLevel === 'SMP')
                            <option value="7">Kelas 7</option>
                            <option value="8">Kelas 8</option>
                            <option value="9">Kelas 9</option>
                        @elseif(in_array($schoolLevel, ['SMA', 'SMK']))
                            <option value="10">Kelas 10</option>
                            <option value="11">Kelas 11</option>
                            <option value="12">Kelas 12</option>
                        @else
                            <option value="1">Kelas 1</option>
                            <option value="2">Kelas 2</option>
                            <option value="3">Kelas 3</option>
                            <option value="4">Kelas 4</option>
                            <option value="5">Kelas 5</option>
                            <option value="6">Kelas 6</option>
                            <option value="7">Kelas 7</option>
                            <option value="8">Kelas 8</option>
                            <option value="9">Kelas 9</option>
                            <option value="10">Kelas 10</option>
                            <option value="11">Kelas 11</option>
                            <option value="12">Kelas 12</option>
                        @endif
                    </select>
                    @error('grade_level') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                @if(!in_array($schoolLevel, ['SD', 'SMP']))
                <div>
                    <label class="block text-sm font-medium text-slate-700">
                        Jurusan
                        @if(in_array((string)$grade_level, ['11', '12']))
                            <span class="text-red-500">*</span>
                        @else
                            <span class="text-slate-400 font-normal text-xs">(Opsional — kelas 10 belum berjurusan)</span>
                        @endif
                    </label>
                    <select wire:model="major_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                        <option value="">-- Belum ada jurusan --</option>
                        @foreach($majors as $major)
                            <option value="{{ $major->id }}">{{ $major->name }}</option>
                        @endforeach
                    </select>
                    @if(in_array((string)$grade_level, ['11', '12']))
                        <p class="text-xs text-amber-600 mt-1">⚠ Kelas 11 dan 12 wajib memiliki jurusan.</p>
                    @else
                        <p class="text-xs text-slate-500 mt-1">Siswa kelas 10 akan memilih jurusan di awal kelas 11. Biarkan kosong jika belum ditentukan.</p>
                    @endif
                    @error('major_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama Kelas (Contoh: IPA 1 / A)</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Wali Kelas (Opsional)</label>
                    <select wire:model="teacher_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                        <option value="">-- Tidak ada Wali Kelas --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    @error('teacher_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 mt-2 border-t border-slate-100 pt-4">
                    <h3 class="text-sm font-bold text-slate-800">Profil Pembelajaran Kelas</h3>
                    <p class="mt-1 text-xs text-slate-500">Informasi ini menjadi konteks diferensiasi pembelajaran dan bantuan AI. Hindari menuliskan nama atau data pribadi siswa.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Kebutuhan Belajar Kelas</label>
                    <textarea wire:model="student_needs" rows="3" maxlength="2000" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" placeholder="Contoh: kemampuan awal beragam, perlu penguatan literasi numerasi."></textarea>
                    @error('student_needs') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Fasilitas Tersedia</label>
                    <textarea wire:model="available_facilities" rows="3" maxlength="1500" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" placeholder="Contoh: papan tulis, proyektor, perpustakaan, internet."></textarea>
                    @error('available_facilities') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Lingkungan Belajar</label>
                    <input type="text" wire:model="learning_environment" maxlength="255" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" placeholder="Contoh: ruang kelas, perpustakaan, lapangan, atau kebun sekolah.">
                    @error('learning_environment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="mt-4 flex justify-end">
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
        <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-lg">
            <h2 class="text-lg font-semibold text-slate-800">Daftar Kelas</h2>
            @if (!$isFormOpen)
            <button wire:click="create" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Kelas
            </button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Identitas Kelas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tahun Ajaran</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Wali Kelas</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($classrooms as $classroom)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">
                                    {{ $classroom->grade_level }} {{ $classroom->name }}
                                </div>
                                @if(!$isSmp && $classroom->major)
                                    <div class="text-xs text-slate-500">{{ $classroom->major->name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                @if($classroom->academicYear)
                                    {{ $classroom->academicYear->name }} ({{ $classroom->academicYear->semester }})
                                @else
                                    <span class="text-red-500">Tidak ada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ $classroom->teacher ? $classroom->teacher->name : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit({{ $classroom->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                <button wire:click="delete({{ $classroom->id }})" wire:confirm="Apakah Anda yakin ingin menghapus kelas ini?" class="text-red-600 hover:text-red-900">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                Belum ada data kelas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

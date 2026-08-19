<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800 leading-tight">Data Ekstrakurikuler</h2>
                <p class="text-sm text-slate-500 mt-1">Manajemen ekstrakurikuler sekolah dan penugasan guru pembina.</p>
            </div>
        </div>
    </x-slot>

    @if($activeYear)
    <div class="mb-6 flex justify-end">
        <button wire:click="openModal" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Ekskul
        </button>
    </div>
    @endif

    @if(!$activeYear)
    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <h3 class="font-bold">Tidak ada Tahun Ajaran Aktif</h3>
            <p class="text-sm mt-1">Anda tidak dapat mengelola ekstrakurikuler karena tidak ada tahun ajaran yang sedang aktif. Silakan atur tahun ajaran di menu Master Data.</p>
        </div>
    </div>
    @else
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <!-- Toolbar -->
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div class="relative max-w-sm w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors" placeholder="Cari nama ekstrakurikuler...">
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Ekstrakurikuler</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Pembina</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Anggota</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($extracurriculars as $ekskul)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-900">{{ $ekskul->name }}</div>
                            <div class="text-xs text-slate-500 truncate max-w-xs">{{ $ekskul->description ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $ekskul->category === 'Wajib' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-800' }}">
                                {{ $ekskul->category }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <img class="h-8 w-8 rounded-full object-cover border border-slate-200" src="{{ $ekskul->teacher->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($ekskul->teacher->name) }}" alt="">
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-slate-900">{{ $ekskul->teacher->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            <div class="flex items-center gap-1.5 font-bold">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                {{ $ekskul->members_count }} Siswa
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <button wire:click="editMode({{ $ekskul->id }})" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit Data">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button onclick="confirm('Apakah Anda yakin ingin menghapus ekskul {{ addslashes($ekskul->name) }}? Pastikan tidak ada anggota yang terdaftar di dalamnya.') || event.stopImmediatePropagation()" wire:click="delete({{ $ekskul->id }})" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Data">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400 border border-slate-100">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-slate-500 font-medium">Belum ada data ekstrakurikuler.</p>
                            @if($search)
                            <p class="text-sm text-slate-400 mt-1">Pencarian "{{ $search }}" tidak ditemukan.</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Form Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" style="background-color: rgba(15, 23, 42, 0.5);" wire:click="closeModal" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform sm:my-8 sm:align-middle sm:max-w-md sm:w-full z-10">
                <form wire:submit="save" class="p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-xl font-bold text-slate-800">{{ $isEdit ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler' }}</h2>
                        <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Ekstrakurikuler <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" class="block w-full p-2 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: Pramuka">
                            @error('name') <span class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                            <select wire:model="category" class="block w-full p-2 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="Wajib">Wajib</option>
                                <option value="Pilihan">Pilihan</option>
                            </select>
                            @error('category') <span class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Guru Pembina <span class="text-rose-500">*</span></label>
                            <select wire:model="teacher_id" class="block w-full p-2 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">-- Pilih Guru Pembina --</option>
                                @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <span class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi (Opsional)</label>
                            <textarea wire:model="description" rows="3" class="block w-full p-2 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Deskripsi singkat kegiatan ekstrakurikuler..."></textarea>
                            @error('description') <span class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl shadow-sm hover:bg-slate-50 transition-colors focus:ring-2 focus:ring-slate-200 focus:ring-offset-2">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-xl shadow-sm hover:bg-indigo-700 transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

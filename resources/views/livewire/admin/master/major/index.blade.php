<div>
    <x-slot name="title">Jurusan (Program Keahlian)</x-slot>

    @if ($isSmp)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Fitur <strong>Jurusan / Program Keahlian</strong> tidak digunakan karena profil tingkat sekolah Anda disetel ke <strong>SMP</strong>. Pengaturan ini dapat diubah pada menu Pengaturan Sekolah.
                    </p>
                </div>
            </div>
        </div>
    @else

        @if (session()->has('message'))
            <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
                {{ session('message') }}
            </div>
        @endif

        <!-- Form Panel -->
        @if ($isFormOpen)
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-slate-800">{{ $isEdit ? 'Edit Jurusan' : 'Tambah Jurusan' }}</h2>
                <button wire:click="resetForm" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form wire:submit.prevent="save" class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Nama Jurusan (Contoh: Ilmu Pengetahuan Alam)</label>
                        <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Kode Jurusan (Contoh: IPA)</label>
                        <input type="text" wire:model="code" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                        @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Keterangan / Deskripsi (Opsional)</label>
                        <textarea wire:model="description" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"></textarea>
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                <h2 class="text-lg font-semibold text-slate-800">Daftar Jurusan</h2>
                @if (!$isFormOpen)
                <button wire:click="create" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                    <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah
                </button>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Jurusan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kode</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse ($majors as $major)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $major->name }}</div>
                                    @if($major->description)
                                    <div class="text-xs text-slate-500 truncate max-w-xs">{{ $major->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded bg-slate-100 text-slate-800">
                                        {{ $major->code ?: '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $major->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    <button wire:click="delete({{ $major->id }})" wire:confirm="Apakah Anda yakin ingin menghapus jurusan ini?" class="text-red-600 hover:text-red-900">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                    Belum ada data jurusan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<div>
    <x-slot name="title">Data Guru</x-slot>

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
            <h2 class="text-lg font-semibold text-slate-800">{{ $isEdit ? 'Edit Data Guru' : 'Tambah Data Guru' }}</h2>
            <button wire:click="resetForm" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form wire:submit.prevent="save" class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Nama Lengkap (beserta gelar)</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">NIP / NIY (Opsional)</label>
                    <input type="text" wire:model="nip" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('nip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                    <label class="block text-sm font-medium text-slate-700">Nomor Telepon (Opsional)</label>
                    <input type="text" wire:model="phone" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2 mt-2">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-slate-600">Guru Aktif (mengajar saat ini)</span>
                    </label>
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
        <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:justify-between sm:items-center bg-slate-50 rounded-t-lg gap-4">
            <h2 class="text-lg font-semibold text-slate-800">Daftar Guru</h2>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau NIP..." class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border w-full sm:w-64">
                @if (!$isFormOpen)
                <button wire:click="create" class="inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                    <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Guru
                </button>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Profil Guru</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kontak & Gender</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($teachers as $teacher)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $teacher->name }}</div>
                                <div class="text-xs text-slate-500">NIP/NIY: {{ $teacher->nip ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ $teacher->gender === 'L' ? 'Laki-laki' : ($teacher->gender === 'P' ? 'Perempuan' : '-') }}</div>
                                <div class="text-xs text-slate-500">{{ $teacher->phone ?: 'Tidak ada telepon' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                @if($teacher->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-800">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit({{ $teacher->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                <button wire:click="delete({{ $teacher->id }})" wire:confirm="Apakah Anda yakin ingin menghapus data guru ini?" class="text-red-600 hover:text-red-900">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                @if($search)
                                    Pencarian tidak menemukan hasil.
                                @else
                                    Belum ada data guru.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $teachers->links() }}
        </div>
    </div>
</div>

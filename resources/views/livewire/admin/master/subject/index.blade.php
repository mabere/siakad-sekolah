<div>
    <x-slot name="title">Mata Pelajaran</x-slot>

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

    <!-- Modal Dialog (Tambah / Edit) -->
    @if ($showModal || $isFormOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-indigo-100 text-indigo-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C20.832 18.477 19.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ $isEdit ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran Baru' }}</h3>
                        <p class="text-xs text-slate-500">Kelola master data mata pelajaran sekolah</p>
                    </div>
                </div>
                <button type="button" wire:click="closeModal" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">✕</button>
            </div>

            <form wire:submit.prevent="save" class="py-4 space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="name" placeholder="Contoh: Matematika" class="w-full rounded-lg border-slate-300 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name') <span class="text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kode Singkat</label>
                        <input type="text" wire:model="code" placeholder="Contoh: MTK" class="w-full rounded-lg border-slate-300 py-2 text-xs uppercase focus:border-indigo-500 focus:ring-indigo-500">
                        @error('code') <span class="text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tipe Pelajaran <span class="text-rose-500">*</span></label>
                        <select wire:model="type" class="w-full rounded-lg border-slate-300 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                            <option value="Wajib">Wajib</option>
                            <option value="Peminatan">Peminatan</option>
                            <option value="Muatan Lokal">Muatan Lokal</option>
                        </select>
                        @error('type') <span class="text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" wire:click="closeModal" class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-bold text-slate-700 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 font-bold text-white shadow-sm hover:bg-indigo-700 transition">
                        {{ $isEdit ? 'Perbarui Pelajaran' : 'Simpan Pelajaran' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
        <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-slate-50 rounded-t-lg">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-slate-800">Daftar Mata Pelajaran</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                    {{ $subjects->total() }} Total
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-56">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama / kode..." class="w-full rounded-md border-slate-300 shadow-sm text-xs px-3 py-1.5 pl-8 border focus:border-indigo-500 focus:ring-indigo-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <select wire:model.live="selectedType" class="rounded-md border-slate-300 shadow-sm text-xs px-3 py-1.5 border focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                    <option value="">Semua Tipe</option>
                    <option value="Wajib">Wajib</option>
                    <option value="Peminatan">Peminatan</option>
                    <option value="Muatan Lokal">Muatan Lokal</option>
                </select>
                @if (!$isFormOpen)
                <button wire:click="create" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                    <svg class="-ml-1 mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah
                </button>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Mata Pelajaran</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tipe</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($subjects as $subject)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $subject->name }}</div>
                                <div class="text-xs text-slate-500">Kode: {{ $subject->code ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                @if($subject->type === 'Wajib')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded bg-blue-100 text-blue-800">Wajib</span>
                                @elseif($subject->type === 'Peminatan')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded bg-purple-100 text-purple-800">Peminatan</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded bg-orange-100 text-orange-800">Muatan Lokal</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit({{ $subject->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                <button wire:click="delete({{ $subject->id }})" wire:confirm="Apakah Anda yakin ingin menghapus mata pelajaran ini?" class="text-red-600 hover:text-red-900">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 whitespace-nowrap text-sm text-slate-500 text-center">
                                Tidak ada data mata pelajaran yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($subjects->hasPages())
            <div class="border-t border-slate-200 bg-slate-50/50 p-4">
                {{ $subjects->links() }}
            </div>
        @endif
    </div>
</div>

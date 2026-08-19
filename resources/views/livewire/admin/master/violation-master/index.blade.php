<div>
    <x-slot name="title">Master Data Pelanggaran</x-slot>

    <!-- Header & Flash Messages -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Master Data Pelanggaran</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola daftar pelanggaran, kategori, dan bobot poin standar.</p>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Data Table Panel -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <!-- Toolbar -->
        <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50 rounded-t-xl">
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau kode..." class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500 min-w-[200px] py-2 px-3">
                <select wire:model.live="categoryFilter" class="rounded-lg border-slate-300 text-xs font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                    <option value="">Semua Kategori</option>
                    <option value="Ringan">Ringan</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Berat">Berat</option>
                </select>
            </div>
            
            <button wire:click="openModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                + Tambah Data
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/4">Kode & Nama Pelanggaran</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Bobot Poin</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($violations as $v)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-900">{{ $v->name }}</div>
                                <div class="text-xs text-slate-500 font-mono mt-1">{{ $v->code ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $catStyles = [
                                        'Ringan' => 'bg-amber-100 text-amber-800',
                                        'Sedang' => 'bg-orange-100 text-orange-800 font-bold',
                                        'Berat' => 'bg-rose-100 text-rose-800 font-black',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $catStyles[$v->category] ?? 'bg-slate-100' }}">
                                    {{ $v->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm font-black text-rose-700">+{{ $v->default_points }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="openModal({{ $v->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3 font-semibold">Edit</button>
                                <button wire:click="delete({{ $v->id }})" wire:confirm="Hapus data ini? Semua entri pelanggaran siswa yang menggunakan master ini akan bernilai NULL pada kolom master-nya." class="text-rose-600 hover:text-rose-900 font-semibold">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 whitespace-nowrap text-sm text-slate-500 text-center">
                                Belum ada data Master Pelanggaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-slate-200 bg-slate-50 rounded-b-xl">
            {{ $violations->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    <!-- Modal -->
    <div x-data="{ open: @entangle('showModal') }"
         x-show="open"
         style="display: none;"
         x-on:keydown.escape.window="open = false"
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true"
         x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="open = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full z-10 relative">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-slate-900 border-b pb-3 mb-4" id="modal-title">
                                {{ $editingId ? 'Edit Data Pelanggaran' : 'Tambah Data Pelanggaran' }}
                            </h3>
                            <div class="mt-2 space-y-4 text-left">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">Kode (Opsional)</label>
                                    <input type="text" wire:model="code" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                                    @error('code') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">Nama Pelanggaran</label>
                                    <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                                    @error('name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700">Kategori</label>
                                        <select wire:model.live="category" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                                            <option value="Ringan">Ringan</option>
                                            <option value="Sedang">Sedang</option>
                                            <option value="Berat">Berat</option>
                                        </select>
                                        @error('category') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700">Bobot Poin</label>
                                        <input type="number" wire:model="default_points" min="1" max="500" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border font-bold text-rose-700">
                                        @error('default_points') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200">
                    <button wire:click="save" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan
                    </button>
                    <button type="button" @click="open = false; $wire.closeModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

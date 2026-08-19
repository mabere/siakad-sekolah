<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Manajemen Kategori Berita
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session()->has('message'))
                <div class="mb-4 bg-teal-100 border border-teal-400 text-teal-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-slate-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-slate-800">Daftar Kategori</h3>
                        <button wire:click="openModal"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            Tambah Kategori
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        No</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Nama Kategori</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Slug</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @forelse($categories as $index => $cat)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                            {{ $cat->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ $cat->slug }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                                            <button wire:click="openModal({{ $cat->id }})"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                            <button wire:click="delete({{ $cat->id }})"
                                                wire:confirm="Yakin ingin menghapus kategori ini?"
                                                class="text-rose-600 hover:text-rose-900">Hapus</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">Belum
                                            ada kategori.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-data="{ open: @entangle('isModalOpen') }" x-show="open" style="display: none;" x-on:keydown.escape.window="open = false"
        class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
        x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                @click="open = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full z-10 relative">
                <form wire:submit.prevent="save">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="mb-4">
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">
                                {{ $categoryId ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                            </h3>
                        </div>
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-slate-700">Nama Kategori</label>
                            <input type="text" wire:model="name" id="name"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 p-2 block w-full shadow-sm sm:text-sm border-slate-300 rounded-md">
                            @error('name')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <button type="button" @click="open = false; $wire.closeModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

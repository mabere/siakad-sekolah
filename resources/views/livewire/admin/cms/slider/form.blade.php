<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.cms.sliders') }}" class="text-slate-500 hover:text-slate-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ $sliderId ? 'Edit Slider' : 'Tambah Slider Baru' }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-slate-200">
                    <form wire:submit.prevent="save">

                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-slate-700 mb-1">Judul Heading</label>
                            <input type="text" wire:model="title" id="title" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-lg border-slate-300 rounded-md py-2 px-3 font-semibold" placeholder="Contoh: PPDB Telah Dibuka">
                            @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi Singkat (Opsional)</label>
                            <textarea wire:model="description" id="description" rows="3" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-md py-2 px-3" placeholder="Teks kecil di bawah judul..."></textarea>
                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-6 border border-slate-200 rounded-lg p-4 bg-slate-50">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Gambar Background Slider</label>

                            @if ($existing_image_path)
                                <div class="mb-4">
                                    <p class="text-xs text-slate-500 mb-1">Gambar saat ini:</p>
                                    <img src="{{ asset('storage/' . $existing_image_path) }}" class="h-32 object-cover rounded-md border border-slate-300 shadow-sm">
                                </div>
                            @endif

                            <input type="file" wire:model="image_path" id="image_path" class="block w-full p-2 text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200">
                            <p class="text-xs text-slate-500 mt-2">Format: JPG, PNG. Rekomendasi ukuran: 1920x800 pixel (Landscape lebar).</p>

                            <div wire:loading wire:target="image_path" class="text-xs font-bold text-indigo-600 mt-2">Mengunggah gambar...</div>
                            @if ($image_path)
                                <div class="mt-2 text-xs font-bold text-green-600">Gambar baru siap disimpan.</div>
                            @endif
                            @error('image_path') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="button_text" class="block text-sm font-medium text-slate-700 mb-1">Teks Tombol (Opsional)</label>
                                <input type="text" wire:model="button_text" id="button_text" class="focus:ring-indigo-500 p-2 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-md" placeholder="Contoh: Daftar Sekarang">
                                @error('button_text') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="button_url" class="block text-sm font-medium text-slate-700 mb-1">Link Tombol (Opsional)</label>
                                <input type="text" wire:model="button_url" id="button_url" class="focus:ring-indigo-500 p-2 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-md" placeholder="Contoh: /ppdb atau https://...">
                                @error('button_url') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="order" class="block text-sm font-medium text-slate-700 mb-1">Urutan Tampil</label>
                                <input type="number" wire:model="order" id="order" class="focus:ring-indigo-500 p-2 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-md">
                            </div>
                            <div class="flex items-center pt-6">
                                <input id="is_active" wire:model="is_active" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-slate-900 font-medium">
                                    Tampilkan di Beranda Publik
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                            <a href="{{ route('admin.cms.sliders') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-md font-semibold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Simpan Slider
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.cms.posts') }}" class="text-slate-500 hover:text-slate-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ $postId ? 'Edit Artikel' : 'Tulis Artikel Baru' }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-slate-200">
                    <form wire:submit.prevent="save">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div class="md:col-span-2">
                                <label for="title" class="block text-sm font-medium text-slate-700 mb-1">Judul
                                    Artikel</label>
                                <input type="text" wire:model="title" id="title"
                                    class="focus:ring-indigo-500 focus:border-indigo-500 p-2 block w-full shadow-sm sm:text-lg border-slate-300 rounded-md py-2 px-3 font-semibold"
                                    placeholder="Masukkan judul artikel...">
                                @error('title')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="featured_image" class="block text-sm font-medium text-slate-700 mb-1">Gambar
                                    Utama</label>
                                <input type="file" wire:model="featured_image" id="featured_image"
                                    class="p-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-300 rounded-md">
                                @error('featured_image')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                                <div wire:loading wire:target="featured_image" class="text-xs text-slate-500 mt-1">
                                    Mengunggah...</div>
                                @if ($featured_image)
                                    <div class="mt-2 text-xs text-green-600">Gambar siap disimpan.</div>
                                @elseif($existing_featured_image)
                                    <div class="mt-2 text-xs text-slate-500">Gambar saat ini telah disetel.</div>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="post_category_id"
                                    class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                                <select wire:model="post_category_id" id="post_category_id"
                                    class="p-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-md">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('post_category_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status
                                    Publikasi</label>
                                <select wire:model="status" id="status"
                                    class="p-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-md">
                                    <option value="Draft">Draft (Simpan sementara)</option>
                                    <option value="Published">Published (Tampilkan ke Publik)</option>
                                </select>
                                @error('status')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-6" wire:key="article-editor-{{ $postId ?? 'new' }}">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Isi Artikel</label>
                            <div
                                class="overflow-hidden rounded-md border border-slate-300 shadow-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                                <x-rich-text::input
                                    id="content"
                                    name="content"
                                    wire:model="content"
                                    :value="\App\Support\SafeHtml::clean($content)"
                                    data-trix-upload-url="{{ route('admin.cms.posts.attachments.store') }}"
                                    class="rich-text-editor"
                                />
                            </div>
                            @error('content')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-slate-500 mt-2">Gunakan toolbar untuk memformat artikel. Konten akan
                                dibersihkan otomatis sebelum dipublikasikan.</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                            <a href="{{ route('admin.cms.posts') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-md font-semibold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Simpan Artikel
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

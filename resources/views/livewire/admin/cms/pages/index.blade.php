<div>
    <x-slot name="title">Halaman (Pages)</x-slot>

    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    @if ($isFormOpen)
        <div x-data x-init="$el.scrollIntoView({ behavior: 'smooth' })" class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6 ring-2 ring-indigo-500/20">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-indigo-50/50 rounded-t-lg">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span>{{ $isEdit ? '✏️ Edit Halaman:' : '➕ Tambah Halaman Baru' }}</span>
                    @if($isEdit)
                        <span class="text-indigo-600 font-extrabold">{{ $title }}</span>
                    @endif
                </h2>
                <button wire:click="closeForm" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="save" class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Judul Halaman <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="title" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Slug (URL) <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="slug" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm bg-slate-50 sm:text-sm px-3 py-2 border">
                    @error('slug') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    <p class="text-xs text-slate-500 mt-1">URL Publik: {{ url('/p') }}/<span class="font-bold text-indigo-600">{{ $slug ?: '...' }}</span></p>
                </div>
                
                <div
                    wire:key="page-editor-{{ $editingId ?? 'new' }}"
                    x-data="{
                        editor: null,
                        initialContent: @js(\App\Support\SafeHtml::clean($content)),
                        init() {
                            this.editor = this.$refs.editor;
                            this.editor.innerHTML = this.initialContent || '';
                        },
                        sync() {
                            this.$refs.content.value = this.editor?.innerHTML || '';
                            this.$refs.content.dispatchEvent(new Event('input', { bubbles: true }));
                        },
                        format(command, value = null) {
                            this.editor?.focus();
                            document.execCommand(command, false, value);
                            this.sync();
                        },
                        addLink() {
                            const url = window.prompt('Masukkan URL tautan:', 'https://');
                            if (url?.trim()) {
                                this.format('createLink', url.trim());
                            }
                        },
                    }"
                    x-init="init()"
                >
                    <label class="block text-sm font-semibold text-slate-700 mb-2" for="page-content-editor">Konten Halaman</label>
                    <div class="overflow-hidden rounded-md border border-slate-300 shadow-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                        <div class="flex flex-wrap items-center gap-1 border-b border-slate-200 bg-slate-50 p-2" role="toolbar" aria-label="Format konten halaman">
                            <button type="button" @mousedown.prevent="format('formatBlock', 'h2')" class="rounded px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-white hover:text-indigo-600" title="Heading 2">H2</button>
                            <button type="button" @mousedown.prevent="format('formatBlock', 'h3')" class="rounded px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-white hover:text-indigo-600" title="Heading 3">H3</button>
                            <span class="mx-1 h-5 border-l border-slate-300" aria-hidden="true"></span>
                            <button type="button" @mousedown.prevent="format('bold')" class="rounded px-2.5 py-1.5 text-sm font-bold text-slate-700 hover:bg-white hover:text-indigo-600" title="Tebal" aria-label="Tebal">B</button>
                            <button type="button" @mousedown.prevent="format('italic')" class="rounded px-2.5 py-1.5 text-sm italic text-slate-700 hover:bg-white hover:text-indigo-600" title="Miring" aria-label="Miring">I</button>
                            <button type="button" @mousedown.prevent="format('underline')" class="rounded px-2.5 py-1.5 text-sm underline text-slate-700 hover:bg-white hover:text-indigo-600" title="Garis bawah" aria-label="Garis bawah">U</button>
                            <span class="mx-1 h-5 border-l border-slate-300" aria-hidden="true"></span>
                            <button type="button" @mousedown.prevent="format('insertUnorderedList')" class="rounded px-2.5 py-1.5 text-sm text-slate-700 hover:bg-white hover:text-indigo-600" title="Daftar berpoin" aria-label="Daftar berpoin">&bull; List</button>
                            <button type="button" @mousedown.prevent="format('insertOrderedList')" class="rounded px-2.5 py-1.5 text-sm text-slate-700 hover:bg-white hover:text-indigo-600" title="Daftar bernomor" aria-label="Daftar bernomor">1. List</button>
                            <button type="button" @mousedown.prevent="addLink()" class="rounded px-2.5 py-1.5 text-sm text-slate-700 hover:bg-white hover:text-indigo-600" title="Tambahkan tautan" aria-label="Tambahkan tautan">Tautan</button>
                            <button type="button" @mousedown.prevent="format('removeFormat')" class="ml-auto rounded px-2.5 py-1.5 text-xs text-slate-500 hover:bg-white hover:text-indigo-600" title="Hapus format" aria-label="Hapus format">Hapus format</button>
                        </div>

                        <div
                            x-ref="editor"
                            id="page-content-editor"
                            contenteditable="true"
                            role="textbox"
                            aria-multiline="true"
                            aria-label="Isi halaman"
                            spellcheck="true"
                            @input="sync()"
                            @keydown.ctrl.b.prevent="format('bold')"
                            @keydown.meta.b.prevent="format('bold')"
                            class="min-h-64 w-full bg-white px-4 py-3 text-sm leading-7 text-slate-800 outline-none empty:before:pointer-events-none empty:before:text-slate-400 empty:before:content-[attr(data-placeholder)] [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:my-1"
                            data-placeholder="Tulis isi halaman di sini..."
                        ></div>
                    </div>
                    <textarea x-ref="content" wire:model="content" class="hidden" tabindex="-1" aria-hidden="true"></textarea>
                    @error('content') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    <p class="mt-2 text-xs text-slate-500">Gunakan toolbar untuk memformat teks. Format HTML yang tidak diizinkan akan dibersihkan otomatis.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Status Terbit</label>
                    <select wire:model="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                        <option value="Draft">Draft (Konsep)</option>
                        <option value="Published">Published (Publikasikan)</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mt-6 flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="closeForm" class="bg-white py-2 px-4 border border-slate-300 rounded-md shadow-sm text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="bg-indigo-600 py-2 px-5 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white hover:bg-indigo-700">
                        {{ $isEdit ? 'Perbarui Halaman' : 'Simpan Halaman' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-lg">
            <h2 class="text-lg font-semibold text-slate-800">Daftar Halaman Statis</h2>
            @if (!$isFormOpen)
                <button wire:click="openForm" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Halaman
                </button>
            @endif
        </div>

        <div class="p-4">
            <div class="mb-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari halaman..." class="w-full sm:w-1/3 rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">URL Slug</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tgl Dibuat</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($pages as $page)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-900">{{ $page->title }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    /p/{{ $page->slug }}
                                    @if($page->status === 'Published')
                                        <a href="/p/{{ $page->slug }}" target="_blank" class="ml-2 text-indigo-600 hover:text-indigo-900 inline-flex items-center" title="Buka di tab baru">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($page->status === 'Published')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Published</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-800">Draft</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    {{ $page->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="openForm({{ $page->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    <button wire:click="delete({{ $page->id }})" wire:confirm="Yakin ingin menghapus halaman ini?" class="text-red-600 hover:text-red-900">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-center text-slate-500">
                                    Belum ada halaman yang dibuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $pages->links() }}
            </div>
        </div>
    </div>
</div>

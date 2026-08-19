<div>
    <x-slot name="title">Menu Navigasi</x-slot>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm flex items-center justify-between">
            <span>{{ session('message') }}</span>
            <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 ml-4">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ═══════════════════════════════════════
             PANEL KIRI: Daftar Menu Group
        ════════════════════════════════════════ --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-lg">
                    <h3 class="font-semibold text-slate-800">Daftar Menu</h3>
                    <button wire:click="openMenuForm()" class="text-xs font-bold px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        + Buat Menu
                    </button>
                </div>

                {{-- Form Buat / Edit Menu Group --}}
                @if($showMenuForm)
                    <form wire:submit.prevent="saveMenu" class="p-4 border-b border-slate-200 space-y-3 {{ $editingMenuId ? 'bg-amber-50' : 'bg-indigo-50' }}">
                        <h4 class="text-sm font-bold {{ $editingMenuId ? 'text-amber-800' : 'text-indigo-800' }}">
                            {{ $editingMenuId ? '✏️ Edit Menu' : '+ Buat Menu Baru' }}
                        </h4>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Menu *</label>
                            <input type="text" wire:model="menuName" placeholder="Misal: Menu Utama"
                                   class="w-full text-sm rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                            @error('menuName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi</label>
                            <select wire:model="menuLocation" class="w-full text-sm rounded-md border-slate-300 bg-white px-3 py-2 border">
                                <option value="">-- Pilih Lokasi --</option>
                                <option value="header">Header (Menu Atas)</option>
                                <option value="footer">Footer (Menu Bawah)</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-semibold text-white rounded-md {{ $editingMenuId ? 'bg-amber-600 hover:bg-amber-700' : 'bg-indigo-600 hover:bg-indigo-700' }}">
                                {{ $editingMenuId ? 'Perbarui' : 'Simpan' }}
                            </button>
                            <button type="button" wire:click="$set('showMenuForm', false)"
                                    class="px-4 py-2 text-sm bg-white text-slate-600 rounded-md hover:bg-slate-100 border border-slate-300 font-semibold">
                                Batal
                            </button>
                        </div>
                    </form>
                @endif

                {{-- List Menu Groups --}}
                <ul class="divide-y divide-slate-100">
                    @forelse($menus as $menu)
                        <li class="flex items-center justify-between px-4 py-3 hover:bg-slate-50 cursor-pointer
                                   {{ $selectedMenuId == $menu['id'] ? 'bg-indigo-50 border-l-4 border-indigo-500' : '' }}"
                            wire:click="selectMenu({{ $menu['id'] }})">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $menu['name'] }}</p>
                                @if($menu['location'])
                                    <span class="text-xs text-slate-500 capitalize">📍 {{ $menu['location'] }}</span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ada lokasi</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 ml-2" wire:click.stop>
                                <button wire:click="openMenuForm({{ $menu['id'] }})"
                                        title="Edit menu"
                                        class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button wire:click="deleteMenu({{ $menu['id'] }})"
                                        wire:confirm="Yakin hapus menu ini beserta semua isinya?"
                                        title="Hapus menu"
                                        class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-8 text-center text-sm text-slate-400">
                            Belum ada menu. Klik <strong>"+ Buat Menu"</strong> untuk memulai.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
             PANEL KANAN: Menu Item Builder
        ════════════════════════════════════════ --}}
        <div class="lg:col-span-2">
            @if($selectedMenu)
                <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                    <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-lg">
                        <div>
                            <h3 class="font-semibold text-slate-800">{{ $selectedMenu['name'] }}</h3>
                            @if($selectedMenu['location'])
                                <p class="text-xs text-slate-500 capitalize">Lokasi: {{ $selectedMenu['location'] }}</p>
                            @endif
                        </div>
                        @if(!$showItemForm)
                            <button wire:click="openItemForm()"
                                    class="text-xs font-bold px-3 py-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                + Tambah Item
                            </button>
                        @endif
                    </div>

                    {{-- ── Form Tambah / Edit Item ── --}}
                    @if($showItemForm)
                        <form wire:submit.prevent="saveItem"
                              class="p-4 border-b border-slate-200 space-y-3 {{ $editingItemId ? 'bg-amber-50' : 'bg-emerald-50' }}">
                            <div class="flex justify-between items-center">
                                <h4 class="font-semibold text-sm {{ $editingItemId ? 'text-amber-800' : 'text-emerald-800' }}">
                                    {{ $editingItemId ? '✏️ Edit Item Menu' : '+ Tambah Item Menu' }}
                                </h4>
                                <button type="button" wire:click="closeItemForm"
                                        class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Tipe Item (hanya saat tambah baru) --}}
                            @if(!$editingItemId)
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Item</label>
                                    <select wire:model.live="itemType"
                                            class="w-full text-sm rounded-md border-slate-300 bg-white px-3 py-2 border">
                                        <option value="custom">Tautan Khusus (Custom URL)</option>
                                        <option value="page">Halaman Statis (Pages)</option>
                                    </select>
                                </div>
                                @if($itemType === 'page')
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Halaman</label>
                                        <select wire:model.live="selectedPageId"
                                                class="w-full text-sm rounded-md border-slate-300 bg-white px-3 py-2 border">
                                            <option value="">-- Pilih Halaman --</option>
                                            @foreach($pages as $page)
                                                <option value="{{ $page->id }}">{{ $page->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            @endif

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Label Menu *</label>
                                    <input type="text" wire:model="itemTitle" placeholder="Teks yang tampil di navbar"
                                           class="w-full text-sm rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                                    @error('itemTitle') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">URL</label>
                                    <input type="text" wire:model="itemUrl" placeholder="https://... atau #"
                                           class="w-full text-sm rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                                    @error('itemUrl') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Buka Di</label>
                                    <select wire:model="itemTarget"
                                            class="w-full text-sm rounded-md border-slate-300 bg-white px-3 py-2 border">
                                        <option value="_self">Halaman Sama</option>
                                        <option value="_blank">Tab Baru ↗</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sub-menu dari</label>
                                    <select wire:model="itemParentId"
                                            class="w-full text-sm rounded-md border-slate-300 bg-white px-3 py-2 border">
                                        <option value="">-- Tidak ada (Top Level) --</option>
                                        @if($selectedMenu && isset($selectedMenu['parent_items']))
                                            @foreach($selectedMenu['parent_items'] as $parent)
                                                {{-- Jangan izinkan item menjadi sub-menu dari dirinya sendiri --}}
                                                @if(!$editingItemId || $parent['id'] != $editingItemId)
                                                    <option value="{{ $parent['id'] }}">{{ $parent['title'] }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="flex gap-2 pt-1">
                                <button type="submit"
                                        class="px-4 py-2 text-sm font-semibold text-white rounded-md {{ $editingItemId ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                                    {{ $editingItemId ? 'Perbarui Item' : 'Tambahkan ke Menu' }}
                                </button>
                                <button type="button" wire:click="closeItemForm"
                                        class="px-4 py-2 text-sm bg-white text-slate-600 rounded-md hover:bg-slate-100 border border-slate-300 font-semibold">
                                    Batal
                                </button>
                            </div>
                        </form>
                    @endif

                    {{-- ── Daftar Item Menu ── --}}
                    <div class="p-4">
                        @if(!empty($selectedMenu['parent_items']))
                            <ul class="space-y-2">
                                @foreach($selectedMenu['parent_items'] as $item)
                                    {{-- Parent Item --}}
                                    <li class="border border-slate-200 rounded-lg bg-slate-50 {{ ($editingItemId == $item['id']) ? 'ring-2 ring-amber-400' : '' }}">
                                        <div class="flex items-center justify-between px-4 py-3">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                                </svg>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $item['title'] }}</p>
                                                    <p class="text-xs text-slate-400 truncate">
                                                        {{ $item['url'] }}
                                                        @if($item['target'] === '_blank')
                                                            <span class="text-indigo-400">↗</span>
                                                        @endif
                                                        @if(!empty($item['children']))
                                                            <span class="ml-1 bg-indigo-100 text-indigo-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                                                {{ count($item['children']) }} sub-menu
                                                            </span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-0.5 flex-shrink-0 ml-2">
                                                <button wire:click="moveUp({{ $item['id'] }})"
                                                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200 rounded transition-colors" title="Naik">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                    </svg>
                                                </button>
                                                <button wire:click="moveDown({{ $item['id'] }})"
                                                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200 rounded transition-colors" title="Turun">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                                <button wire:click="openItemForm({{ $item['id'] }})"
                                                        class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded transition-colors" title="Edit item">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <button wire:click="deleteItem({{ $item['id'] }})"
                                                        wire:confirm="Hapus item '{{ $item['title'] }}'?"
                                                        class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded transition-colors" title="Hapus item">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Children / Sub-menu Items --}}
                                        @if(!empty($item['children']))
                                            <ul class="border-t border-slate-200 divide-y divide-slate-100">
                                                @foreach($item['children'] as $child)
                                                    <li class="flex items-center justify-between pl-10 pr-3 py-2.5 bg-white {{ ($editingItemId == $child['id']) ? 'ring-2 ring-inset ring-amber-400' : '' }}">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <span class="text-slate-300 flex-shrink-0">└</span>
                                                            <div class="min-w-0">
                                                                <p class="text-sm text-slate-700 truncate">{{ $child['title'] }}</p>
                                                                <p class="text-xs text-slate-400 truncate">
                                                                    {{ $child['url'] }}
                                                                    @if($child['target'] === '_blank')
                                                                        <span class="text-indigo-400">↗</span>
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-0.5 flex-shrink-0 ml-2">
                                                            <button wire:click="moveUp({{ $child['id'] }})"
                                                                    class="p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded" title="Naik">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                                </svg>
                                                            </button>
                                                            <button wire:click="moveDown({{ $child['id'] }})"
                                                                    class="p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded" title="Turun">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                </svg>
                                                            </button>
                                                            <button wire:click="openItemForm({{ $child['id'] }})"
                                                                    class="p-1 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded" title="Edit sub-menu">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                            </button>
                                                            <button wire:click="deleteItem({{ $child['id'] }})"
                                                                    wire:confirm="Hapus sub-menu '{{ $child['title'] }}'?"
                                                                    class="p-1 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded" title="Hapus">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center py-10 text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                <p class="text-sm font-semibold">Menu ini masih kosong.</p>
                                <p class="text-xs mt-1">Klik <strong class="text-emerald-600">"+ Tambah Item"</strong> untuk mulai mengisi.</p>
                            </div>
                        @endif
                    </div>
                </div>

            @else
                {{-- Belum pilih menu --}}
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 h-full flex items-center justify-center py-24">
                    <div class="text-center text-slate-400">
                        <svg class="w-16 h-16 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        <p class="text-lg font-semibold">Pilih menu dari panel kiri</p>
                        <p class="text-sm mt-1">atau buat menu baru untuk mulai menyusun navigasi publik.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

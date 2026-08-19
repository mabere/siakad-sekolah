<div>
    <x-slot name="title">Kategori & Pos Pembayaran Keuangan</x-slot>

    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-lg">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Pos & Kategori Pembayaran</h2>
                <p class="text-xs text-slate-500">Atur komponen tagihan seperti SPP Bulanan, Uang Gedung, Seragam, dll.</p>
            </div>
            @if(!$isFormOpen)
                <button wire:click="openForm()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg transition">
                    + Tambah Pos Tagihan
                </button>
            @endif
        </div>

        @if($isFormOpen)
            <form wire:submit.prevent="save" class="p-4 bg-indigo-50/50 border-b border-slate-200 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Tagihan *</label>
                        <input type="text" wire:model="name" placeholder="Misal: SPP Bulanan 2026" class="w-full text-sm rounded-md border-slate-300 px-3 py-2 border">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Pembayaran *</label>
                        <select wire:model="type" class="w-full text-sm rounded-md border-slate-300 bg-white px-3 py-2 border">
                            <option value="monthly_spp">SPP Bulanan (Rutin tiap bulan)</option>
                            <option value="one_time">Sekali Bayar (Misal: Uang Pangkal / Gedung)</option>
                            <option value="optional">Opsional / Sukarela (Misal: Kegiatan Ekskul)</option>
                        </select>
                        @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Standar (Rp) *</label>
                        <input type="number" wire:model="default_amount" class="w-full text-sm rounded-md border-slate-300 px-3 py-2 border">
                        @error('default_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span>Aktifkan Kategori Tagihan Ini</span>
                    </label>

                    <div class="ml-auto flex gap-2">
                        <button type="button" wire:click="closeForm()" class="px-4 py-2 bg-white text-slate-600 border border-slate-300 text-xs font-semibold rounded-lg hover:bg-slate-50">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700">Simpan</button>
                    </div>
                </div>
            </form>
        @endif

        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">Nama Tagihan</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Nominal Standar</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $cat->name }}</td>
                                <td class="px-4 py-3">
                                    @if($cat->type === 'monthly_spp')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">SPP Bulanan</span>
                                    @elseif($cat->type === 'one_time')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Sekali Bayar</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Opsional</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono font-semibold">Rp {{ number_format($cat->default_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <button wire:click="toggleStatus({{ $cat->id }})" class="cursor-pointer">
                                        @if($cat->is_active)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Non-Aktif</span>
                                        @endif
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <button wire:click="openForm({{ $cat->id }})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900">Edit</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada pos tagihan. Klik "+ Tambah Pos Tagihan" untuk membuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

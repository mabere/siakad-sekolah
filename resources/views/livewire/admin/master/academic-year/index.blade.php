<div>
    <x-slot name="title">Tahun Ajaran</x-slot>

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
            <h2 class="text-lg font-semibold text-slate-800">{{ $isEdit ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran' }}</h2>
            <button wire:click="resetForm" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form wire:submit.prevent="save" class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama (Contoh: 2025/2026)</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Semester</label>
                    <select wire:model="semester" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                    @error('semester') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Mulai (Opsional)</label>
                    <input type="date" wire:model="start_date" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Selesai (Opsional)</label>
                    <input type="date" wire:model="end_date" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 mt-2 border-t border-slate-100 pt-4">
                    <h3 class="text-sm font-bold text-slate-800">Kurikulum & Kalender Tahun Ajaran</h3>
                    <p class="mt-1 text-xs text-slate-500">Data ini berlaku untuk tahun ajaran yang sedang disunting, bukan identitas permanen sekolah.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Kurikulum</label>
                    <select wire:model="curriculum_type" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                        <option value="MERDEKA">Kurikulum Merdeka</option>
                        <option value="K13">Kurikulum 2013 (K13)</option>
                    </select>
                    @error('curriculum_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Minggu Efektif</label>
                    <input type="number" min="1" max="52" wire:model="effective_weeks" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" placeholder="Contoh: 18">
                    @error('effective_weeks') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Muatan Lokal</label>
                    <textarea wire:model="local_content" rows="2" maxlength="1000" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" placeholder="Contoh: Bahasa daerah, keterampilan daerah, atau konteks lokal."></textarea>
                    @error('local_content') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Fokus Dimensi P5</label>
                    <textarea wire:model="p5_focus" rows="2" maxlength="1000" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" placeholder="Contoh: Bernalar kritis dan gotong royong."></textarea>
                    @error('p5_focus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Catatan Kalender Akademik</label>
                    <textarea wire:model="calendar_notes" rows="2" maxlength="2000" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border" placeholder="Catatan libur, asesmen, atau kegiatan khusus tahun ajaran ini."></textarea>
                    @error('calendar_notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 mt-2">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-slate-600">Jadikan sebagai Tahun Ajaran Aktif (Default)</span>
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
        <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-lg">
            <h2 class="text-lg font-semibold text-slate-800">Daftar Tahun Ajaran</h2>
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama & Semester</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Periode</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($academicYears as $ay)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $ay->name }}</div>
                                <div class="text-sm text-slate-500">Semester {{ $ay->semester }}</div>
                                <div class="text-xs text-indigo-600">{{ $ay->curriculum_type ?: 'Kurikulum belum diatur' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ $ay->start_date ? $ay->start_date->format('d/m/Y') : '-' }} s/d {{ $ay->end_date ? $ay->end_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($ay->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                @else
                                    <button wire:click="setAsActive({{ $ay->id }})" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer transition">
                                        Set Aktif
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit({{ $ay->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                <button wire:click="delete({{ $ay->id }})" wire:confirm="Apakah Anda yakin ingin menghapus Tahun Ajaran ini?" class="text-red-600 hover:text-red-900">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                Belum ada data tahun ajaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

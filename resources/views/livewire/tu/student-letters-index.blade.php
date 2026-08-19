<div>
    <x-slot name="title">Layanan Surat Keterangan Siswa</x-slot>

    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-lg">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Administrasi Surat Menyurat Siswa</h2>
                <p class="text-xs text-slate-500">Pencetakan Surat Keterangan Aktif, Berkelakuan Baik, atau Pindah Sekolah.</p>
            </div>
            <button wire:click="openCreateModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg transition">
                + Terbitkan Surat Baru
            </button>
        </div>

        <div class="p-4">
            <div class="mb-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama siswa atau nomor surat..." class="w-full sm:w-1/3 text-sm rounded-md border-slate-300 px-3 py-2 border">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">Nomor Surat</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">Jenis Surat</th>
                            <th class="px-4 py-3">Keperluan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($letters as $l)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-mono font-bold text-indigo-600 text-xs">{{ $l->letter_number ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ $l->student->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $l->student->classroom->full_name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ $l->type_name }}</td>
                                <td class="px-4 py-3 text-xs text-slate-600">{{ $l->purpose ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($l->status === 'approved')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Disetujui</span>
                                    @elseif($l->status === 'pending')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Pending</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Ditolak</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    @if($l->status === 'pending')
                                        <button wire:click="updateStatus({{ $l->id }}, 'approved')" class="px-2.5 py-1 bg-emerald-600 text-white text-xs rounded hover:bg-emerald-700">Setujui</button>
                                        <button wire:click="updateStatus({{ $l->id }}, 'rejected')" class="px-2.5 py-1 bg-rose-600 text-white text-xs rounded hover:bg-rose-700">Tolak</button>
                                    @elseif($l->status === 'approved')
                                        <button onclick="window.print()" class="px-3 py-1 bg-slate-800 text-white text-xs font-semibold rounded hover:bg-slate-900">🖨️ Cetak Surat</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada riwayat permohonan/penerbitan surat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $letters->links() }}</div>
        </div>
    </div>

    {{-- Modal Create Surat --}}
    @if($showCreateModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                <h3 class="text-lg font-bold text-slate-900 border-b pb-2">Terbitkan Surat Keterangan</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pilih Siswa *</label>
                        <select wire:model="studentId" class="w-full rounded-md border-slate-300 px-3 py-2 border">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($students as $st)
                                <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->classroom->full_name ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jenis Surat *</label>
                        <select wire:model="letterType" class="w-full rounded-md border-slate-300 px-3 py-2 border">
                            <option value="surat_keterangan_aktif">Surat Keterangan Siswa Aktif</option>
                            <option value="surat_berkelakuan_baik">Surat Keterangan Berkelakuan Baik</option>
                            <option value="surat_pindah_sekolah">Surat Keterangan Pindah Sekolah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Keperluan</label>
                        <textarea wire:model="purpose" placeholder="Misal: Persyaratan beasiswa / pengurusan BPJS..." class="w-full rounded-md border-slate-300 px-3 py-2 border"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button wire:click="$set('showCreateModal', false)" class="px-4 py-2 bg-slate-100 text-slate-600 font-semibold text-xs rounded-lg">Batal</button>
                    <button wire:click="createLetter()" class="px-4 py-2 bg-indigo-600 text-white font-semibold text-xs rounded-lg hover:bg-indigo-700">Terbitkan Surat</button>
                </div>
            </div>
        </div>
    @endif
</div>

<div>
    <x-slot name="title">Riwayat & Pembayaran SPP Anak</x-slot>

    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    {{-- Selector Anak --}}
    @if($relations->count() > 1)
        <div class="mb-6 bg-slate-50 border border-slate-200 p-4 rounded-xl flex items-center justify-between">
            <span class="text-sm font-bold text-slate-800">Tagihan Untuk Anak:</span>
            <div class="flex gap-2">
                @foreach($relations as $rel)
                    <button wire:click="selectStudent({{ $rel->student_id }})"
                            class="px-4 py-2 rounded-lg text-xs font-bold transition
                            {{ $selectedStudentId == $rel->student_id ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 border border-slate-300' }}">
                        👨‍🎓 {{ $rel->student->name }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
        <div class="p-4 border-b border-slate-200 bg-slate-50 rounded-t-lg">
            <h2 class="text-lg font-bold text-slate-800">Daftar Tagihan & Riwayat Pembayaran SPP</h2>
            <p class="text-xs text-slate-500">Anda dapat melakukan pembayaran via transfer bank lalu mengupload foto bukti transfer di bawah ini.</p>
        </div>

        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">Pos Tagihan</th>
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3">Nominal Tagihan</th>
                            <th class="px-4 py-3">Sudah Dibayar</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi / Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payments as $p)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-bold text-slate-900">{{ $p->category->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $p->month_name != '-' ? $p->month_name : '' }} {{ $p->academicYear->name ?? '' }}
                                </td>
                                <td class="px-4 py-3 font-mono font-semibold">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 font-mono text-emerald-600 font-semibold">Rp {{ number_format($p->paid_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    @if($p->status === 'paid')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">LUNAS</span>
                                    @elseif($p->status === 'pending_confirmation')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 animate-pulse">Verifikasi TU</span>
                                    @elseif($p->status === 'partial')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Dicicil</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Belum Bayar</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($p->status === 'unpaid' || $p->status === 'partial')
                                        <button wire:click="openUploadModal({{ $p->id }})" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm">
                                            📤 Upload Bukti Transfer
                                        </button>
                                    @elseif($p->status === 'pending_confirmation')
                                        <span class="text-xs text-amber-700 font-semibold">Sedang Diproses Staf TU</span>
                                    @else
                                        <span class="text-xs text-emerald-600 font-semibold">✓ Pembayaran Diterima</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada riwayat tagihan untuk anak ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Upload Bukti Transfer --}}
    @if($showUploadModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                <h3 class="text-lg font-bold text-slate-900 border-b pb-2">Upload Bukti Pembayaran Transfer</h3>
                
                {{-- Rekening Info --}}
                <div class="bg-indigo-50 border border-indigo-200 p-3 rounded-lg text-xs space-y-1 text-indigo-900">
                    <p class="font-bold">Transfer Pembayaran ke Rekening Resmi Sekolah:</p>
                    <p>Bank Mandiri: <span class="font-mono font-bold">137-00-1234567-8</span> (a.n SMA SIAKAD)</p>
                    <p>Bank BCA: <span class="font-mono font-bold">883-0987654</span> (a.n SMA SIAKAD)</p>
                </div>

                <form wire:submit.prevent="uploadProof" class="space-y-3 text-sm">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Foto / Scan Bukti Transfer *</label>
                        <input type="file" wire:model="proofFile" class="w-full p-2 text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('proofFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                        <textarea wire:model="paymentNotes" placeholder="Misal: Nama pemilik rekening pengirim..." class="w-full rounded-md border-slate-300 px-3 py-2 border text-xs"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" wire:click="$set('showUploadModal', false)" class="px-4 py-2 bg-slate-100 text-slate-600 font-semibold text-xs rounded-lg">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white font-semibold text-xs rounded-lg hover:bg-emerald-700">Kirim Bukti Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

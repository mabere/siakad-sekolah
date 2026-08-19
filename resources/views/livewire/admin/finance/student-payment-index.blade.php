<div>
    @php($financePrefix = explode('.', request()->route()->getName())[0] ?? 'admin')
    <x-slot name="title">Kelola Tagihan & Kasir SPP Siswa</x-slot>

    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
        <div class="p-4 border-b border-slate-200 flex flex-wrap justify-between items-center bg-slate-50 rounded-t-lg gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Manajemen Tagihan & Kasir Pembayaran</h2>
                <p class="text-xs text-slate-500">Penerbitan tagihan bulanan dan transaksi pembayaran tunai/transfer.</p>
            </div>
            <button wire:click="openGenerateModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg transition">
                ⚡ Terbitkan Tagihan Rombel
            </button>
        </div>

        {{-- Filter Section --}}
        <div class="p-4 bg-slate-50/50 border-b border-slate-200 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama siswa / NISN..." class="text-sm rounded-md border-slate-300 px-3 py-2 border">
            <select wire:model.live="selectedClassroomId" class="text-sm rounded-md border-slate-300 bg-white px-3 py-2 border">
                <option value="">-- Semua Kelas / Rombel --</option>
                @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}">{{ $cls->full_name }}</option>
                @endforeach
            </select>
            <select wire:model.live="selectedCategoryId" class="text-sm rounded-md border-slate-300 bg-white px-3 py-2 border">
                <option value="">-- Semua Pos Tagihan --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="selectedStatus" class="text-sm rounded-md border-slate-300 bg-white px-3 py-2 border">
                <option value="">-- Semua Status --</option>
                <option value="unpaid">Belum Bayar</option>
                <option value="pending_confirmation">Menunggu Verifikasi Transfer</option>
                <option value="partial">Sebagian (Cicilan)</option>
                <option value="paid">Lunas</option>
            </select>
        </div>

        {{-- Table --}}
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">Siswa & Rombel</th>
                            <th class="px-4 py-3">Pos Tagihan</th>
                            <th class="px-4 py-3">Bulan / TA</th>
                            <th class="px-4 py-3">Tagihan</th>
                            <th class="px-4 py-3">Terbayar</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payments as $p)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ $p->student->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $p->student->classroom->full_name ?? '-' }} • NISN: {{ $p->student->nisn ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ $p->category->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $p->month_name != '-' ? $p->month_name : '' }} {{ $p->academicYear->name ?? '' }}
                                </td>
                                <td class="px-4 py-3 font-mono font-semibold">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 font-mono text-emerald-600 font-semibold">Rp {{ number_format($p->paid_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    @if($p->status === 'paid')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">LUNAS</span>
                                    @elseif($p->status === 'pending_confirmation')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 animate-pulse">Perlu Verifikasi</span>
                                    @elseif($p->status === 'partial')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Dicicil</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Belum Bayar</span>
                                    @endif
                                </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                    @if($p->proof_file)
                                        <a href="{{ route($financePrefix.'.finance.payments.proof', $p->id) }}" class="inline-flex px-3 py-1 border border-slate-300 text-slate-700 text-xs font-semibold rounded-md hover:bg-slate-50">Lihat bukti</a>
                                    @endif
                                    @if($p->status === 'pending_confirmation')
                                        <button wire:click="confirmPayment({{ $p->id }})" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-md">Verifikasi</button>
                                    @elseif($p->status !== 'paid')
                                        <button wire:click="openPaymentModal({{ $p->id }})" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-md">Bayar Kasir</button>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400">Tidak ada tagihan pembayaran ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $payments->links() }}</div>
        </div>
    </div>

    {{-- Modal Generate Bulk --}}
    @if($showGenerateModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                <h3 class="text-lg font-bold text-slate-900 border-b pb-2">Terbitkan Tagihan Massal</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Target Kelas / Rombel *</label>
                        <select wire:model="genClassroomId" class="w-full rounded-md border-slate-300 px-3 py-2 border">
                            <option value="">-- Pilih Rombel --</option>
                            @foreach($classrooms as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pos / Kategori Tagihan *</label>
                        <select wire:model="genCategoryId" class="w-full rounded-md border-slate-300 px-3 py-2 border">
                            <option value="">-- Pilih Pos Tagihan --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }} (Rp {{ number_format($cat->default_amount, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tahun Ajaran *</label>
                            <select wire:model="genAcademicYearId" class="w-full rounded-md border-slate-300 px-3 py-2 border">
                                @foreach($academicYears as $ay)
                                    <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Bulan SPP</label>
                            <select wire:model="genMonth" class="w-full rounded-md border-slate-300 px-3 py-2 border">
                                @foreach([1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'] as $num => $mName)
                                    <option value="{{ $num }}">{{ $mName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button wire:click="$set('showGenerateModal', false)" class="px-4 py-2 bg-slate-100 text-slate-600 font-semibold text-xs rounded-lg">Batal</button>
                    <button wire:click="generateBulkPayments()" class="px-4 py-2 bg-emerald-600 text-white font-semibold text-xs rounded-lg hover:bg-emerald-700">Terbitkan Sekarang</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Payment Kasir --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                <h3 class="text-lg font-bold text-slate-900 border-b pb-2">Transaksi Pembayaran Kasir TU</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jumlah Yang Dibayar (Rp) *</label>
                        <input type="number" wire:model="payAmount" class="w-full rounded-md border-slate-300 px-3 py-2 border text-lg font-bold font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Metode Pembayaran</label>
                        <select wire:model="payMethod" class="w-full rounded-md border-slate-300 px-3 py-2 border">
                            <option value="cash">Tunai / Kasir TU</option>
                            <option value="bank_transfer">Transfer Bank / EDC</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan Transaksi</label>
                        <textarea wire:model="payNotes" rows="2" placeholder="Nomor kuitansi / catatan tambahan..." class="w-full rounded-md border-slate-300 px-3 py-2 border"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button wire:click="$set('showPaymentModal', false)" class="px-4 py-2 bg-slate-100 text-slate-600 font-semibold text-xs rounded-lg">Batal</button>
                    <button wire:click="processPayment()" class="px-4 py-2 bg-indigo-600 text-white font-semibold text-xs rounded-lg hover:bg-indigo-700">Simpan Transaksi</button>
                </div>
            </div>
        </div>
    @endif
</div>

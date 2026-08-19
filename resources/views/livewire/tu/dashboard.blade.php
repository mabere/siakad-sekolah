<div>
    <x-slot name="title">Dashboard Staf Tata Usaha</x-slot>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Pemasukan Hari Ini</p>
            <h3 class="text-2xl font-extrabold text-emerald-600 mt-1 font-mono">Rp {{ number_format($todayIncome, 0, ',', '.') }}</h3>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Verifikasi Transfer</p>
            <h3 class="text-2xl font-extrabold text-amber-600 mt-1">{{ $pendingVerifications }} <span class="text-xs font-normal text-slate-400">Tagihan</span></h3>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Permohonan Surat</p>
            <h3 class="text-2xl font-extrabold text-indigo-600 mt-1">{{ $pendingLetters }} <span class="text-xs font-normal text-slate-400">Pending</span></h3>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Total Siswa Aktif</p>
            <h3 class="text-2xl font-extrabold text-slate-800 mt-1">{{ $totalStudents }} <span class="text-xs font-normal text-slate-400">Siswa</span></h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pembayaran Terbaru --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-slate-800">Pembayaran SPP Masuk</h3>
                <a href="{{ route('admin.finance.payments') }}" class="text-xs font-bold text-indigo-600 hover:underline">Lihat Semua Kasir ↗</a>
            </div>
            <div class="space-y-3">
                @forelse($recentPayments as $rp)
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $rp->student->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">{{ $rp->category->name ?? '-' }} • {{ $rp->paid_at ? $rp->paid_at->format('d M H:i') : '-' }}</p>
                        </div>
                        <span class="font-mono font-bold text-emerald-600 text-sm">+ Rp {{ number_format($rp->paid_amount, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">Belum ada transaksi pembayaran masuk hari ini.</p>
                @endforelse
            </div>
        </div>

        {{-- Surat Terbaru --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-slate-800">Permohonan Surat Keterangan</h3>
                <a href="{{ route('tu.letters') }}" class="text-xs font-bold text-indigo-600 hover:underline">Kelola Surat ↗</a>
            </div>
            <div class="space-y-3">
                @forelse($recentLetters as $rl)
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $rl->student->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">{{ $rl->type_name }}</p>
                        </div>
                        @if($rl->status === 'pending')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                        @elseif($rl->status === 'approved')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Disetujui</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Ditolak</span>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">Belum ada permohonan surat terbaru.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

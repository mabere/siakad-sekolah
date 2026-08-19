<div>
    <x-slot name="title">Portal Orang Tua / Wali Siswa</x-slot>

    {{-- Pemilihan Anak (Multi-anak support) --}}
    @if($relations->count() > 1)
        <div class="mb-6 bg-indigo-50 border border-indigo-200 p-4 rounded-xl flex items-center justify-between">
            <span class="text-sm font-bold text-indigo-900">Pilih Anak yang Dipantau:</span>
            <div class="flex gap-2">
                @foreach($relations as $rel)
                    <button wire:click="selectStudent({{ $rel->student_id }})"
                            class="px-4 py-2 rounded-lg text-xs font-bold transition
                            {{ $selectedStudentId == $rel->student_id ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 border border-slate-300' }}">
                        👨‍🎓 {{ $rel->student->name }} ({{ $rel->student->classroom->full_name ?? '-' }})
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @if($student)
        {{-- Profile Banner Anak --}}
        <div class="bg-gradient-to-r from-slate-900 to-indigo-900 text-white p-6 rounded-2xl shadow-md mb-6 flex flex-wrap justify-between items-center gap-4">
            <div>
                <span class="bg-indigo-500/30 text-indigo-200 text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wider">Profil Anak Didik</span>
                <h2 class="text-2xl font-extrabold mt-1">{{ $student->name }}</h2>
                <p class="text-xs text-slate-300 mt-0.5">NISN: {{ $student->nisn ?? '-' }} • Kelas: {{ $student->classroom->full_name ?? '-' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('parent.payments') }}" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl shadow transition">
                    💳 Pembayaran SPP
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Kehadiran Terakhir --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="font-bold text-slate-800 border-b pb-3 mb-4 flex justify-between items-center">
                    <span>📅 Kehadiran Terakhir</span>
                    <span class="text-xs text-slate-400 font-normal">5 Rekaman Terbaru</span>
                </h3>
                <div class="space-y-3">
                    @forelse($attendances as $att)
                        <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $att->date ? \Carbon\Carbon::parse($att->date)->format('d F Y') : '-' }}</p>
                                <p class="text-xs text-slate-500">Jam Masuk: {{ $att->check_in_time ?? '-' }}</p>
                            </div>
                            @if($att->status === 'hadir')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Hadir</span>
                            @elseif($att->status === 'terlambat')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Terlambat</span>
                            @elseif($att->status === 'sakit' || $att->status === 'izin')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">{{ ucfirst($att->status) }}</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Alpa</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada data presensi siswa.</p>
                    @endforelse
                </div>
            </div>

            {{-- Ringkasan Tagihan Aktif --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="font-bold text-slate-800 border-b pb-3 mb-4 flex justify-between items-center">
                    <span>💳 Tagihan SPP & Sekolah</span>
                    <a href="{{ route('parent.payments') }}" class="text-xs font-bold text-indigo-600 hover:underline">Lihat Rincian ↗</a>
                </h3>
                <div class="space-y-3">
                    @forelse($unpaidPayments as $up)
                        <div class="flex justify-between items-center p-3 bg-rose-50/50 border border-rose-100 rounded-lg">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $up->category->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $up->month_name != '-' ? $up->month_name : '' }} {{ $up->academicYear->name ?? '' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-mono font-bold text-rose-700 text-sm">Rp {{ number_format($up->amount - $up->paid_amount, 0, ',', '.') }}</p>
                                @if($up->status === 'pending_confirmation')
                                    <span class="text-[10px] font-bold text-amber-600">Menunggu Verifikasi</span>
                                @else
                                    <a href="{{ route('parent.payments') }}" class="text-[10px] font-bold text-indigo-600 underline">Bayar / Transfer</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-emerald-600 bg-emerald-50 rounded-xl border border-emerald-100">
                            <span class="text-2xl">🎉</span>
                            <p class="text-xs font-bold mt-1">Tidak Ada Tagihan Aktif!</p>
                            <p class="text-[11px] text-emerald-700">Seluruh kewajiban pembayaran SPP anak sudah lunas.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <div class="bg-white p-8 rounded-xl border border-slate-200 text-center text-slate-400">
            <p class="text-sm">Akun Orang Tua ini belum terhubung dengan siswa. Silakan hubungi Tata Usaha Sekolah.</p>
        </div>
    @endif
</div>

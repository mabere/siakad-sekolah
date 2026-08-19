<div class="min-h-screen bg-slate-50 pt-24">
    <div class="mx-auto max-w-xl px-4 py-10 sm:px-6">
        <a href="{{ route('public.ppdb.status') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Kembali ke cek status</a>
        <div class="mt-5 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Akses PPDB</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Lupa kode akses?</h1>
            <p class="mt-2 text-sm text-slate-600">Gunakan nomor pendaftaran dan email yang tercatat. PIN baru hanya dapat dibuat setelah OTP terverifikasi.</p>

            @if ($message)
                <div class="mt-4 rounded-md border border-indigo-200 bg-indigo-50 p-3 text-sm text-indigo-800">{{ $message }}</div>
            @endif

            @if ($newAccessCode)
                <div class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 p-5 text-center">
                    <p class="text-sm font-medium text-emerald-800">PIN baru Anda</p>
                    <p class="mt-2 font-mono text-3xl font-bold tracking-[0.35em] text-emerald-900">{{ $newAccessCode }}</p>
                    <p class="mt-3 text-xs text-emerald-700">Simpan PIN ini. Demi keamanan, PIN tidak akan ditampilkan lagi setelah halaman dimuat ulang.</p>
                </div>
                <a href="{{ route('public.ppdb.status') }}" class="mt-5 block rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-700">Lanjut ke cek status</a>
            @else
                <form wire:submit="requestOtp" class="mt-6 space-y-4">
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Nomor pendaftaran</label><input type="text" wire:model="applicationNumber" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm uppercase">@error('applicationNumber')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Email kontak</label><input type="email" wire:model="contactEmail" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">@error('contactEmail')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                    <button type="submit" wire:loading.attr="disabled" class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">Kirim OTP</button>
                </form>

                @if ($otpSent)
                    <form wire:submit="resetAccessCode" class="mt-5 space-y-4 border-t border-slate-200 pt-5">
                        <div><label class="mb-1 block text-sm font-medium text-slate-700">Kode OTP</label><input type="text" inputmode="numeric" maxlength="6" wire:model="otp" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-center font-mono text-lg tracking-[0.35em]">@error('otp')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                        <button type="submit" wire:loading.attr="disabled" class="w-full rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">Verifikasi OTP dan buat PIN baru</button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>

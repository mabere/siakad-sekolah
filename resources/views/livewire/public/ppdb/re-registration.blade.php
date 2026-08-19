<div class="min-h-screen bg-slate-50 pt-24">
    <div class="mx-auto max-w-xl px-4 py-10 sm:px-6">
        <a href="{{ route('public.ppdb.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Kembali ke PPDB</a>
        <div class="mt-5 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">Daftar ulang PPDB</h1>
            <p class="mt-2 text-sm text-slate-600">Masukkan nomor pendaftaran dan kode akses untuk mengonfirmasi kesediaan masuk sekolah.</p>

            @if (! $application)
                <form wire:submit="check" class="mt-6 space-y-4">
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Nomor pendaftaran</label><input type="text" wire:model="applicationNumber" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm uppercase">@error('applicationNumber')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Kode akses</label><input type="password" inputmode="numeric" wire:model="accessCode" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">@error('accessCode')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Lanjutkan</button>
                </form>
            @else
                <div class="mt-6 rounded-md bg-slate-50 p-4"><p class="text-xs text-slate-500">Calon peserta didik</p><p class="font-semibold text-slate-900">{{ $application->candidate?->name }}</p><p class="mt-2 text-xs text-slate-500">Jalur {{ $application->pathway?->name }} · {{ $application->application_number }}</p></div>
                @if ($confirmed || $application->reregistration_status === 'confirmed')
                    <div class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">Konfirmasi daftar ulang sudah diterima. Selanjutnya panitia akan memverifikasi daftar ulang.</div>
                @else
                    <p class="mt-5 text-sm text-slate-600">Dengan menekan tombol berikut, Anda menyatakan menerima hasil seleksi dan bersedia mengikuti proses daftar ulang.</p>
                    <button type="button" wire:click="confirm" class="mt-4 w-full rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white">Konfirmasi daftar ulang</button>
                @endif
                <button type="button" wire:click="resetSearch" class="mt-5 text-sm font-semibold text-indigo-600">Kembali</button>
            @endif
        </div>
    </div>
</div>

<div class="min-h-screen bg-slate-50 pt-24">
    <div class="mx-auto max-w-xl px-4 py-10 sm:px-6">
        <a href="{{ route('public.ppdb.status') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Kembali ke cek status</a>

        <div class="mt-5 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Aktivasi portal siswa</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Buat password baru</h1>

            @if ($isValid)
                <p class="mt-2 text-sm leading-6 text-slate-600">Akun portal untuk <strong>{{ $studentName }}</strong> sudah tersedia. Buat password baru untuk melanjutkan ke portal siswa.</p>
                <div class="mt-5 rounded-md bg-slate-50 p-4 text-sm">
                    <p class="text-xs text-slate-500">Username portal siswa</p>
                    <p class="mt-1 font-mono font-semibold text-slate-800">{{ $username }}</p>
                </div>

                <form wire:submit="activate" class="mt-6 space-y-4">
                    <div>
                        <label for="student-activation-password" class="mb-1 block text-sm font-medium text-slate-700">Password baru</label>
                        <input id="student-activation-password" type="password" wire:model="password" autocomplete="new-password" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
                        <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter, dengan huruf besar, huruf kecil, angka, dan simbol.</p>
                        @error('password') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="student-activation-password-confirmation" class="mb-1 block text-sm font-medium text-slate-700">Ulangi password baru</label>
                        <input id="student-activation-password-confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
                    </div>
                    <button type="submit" wire:loading.attr="disabled" class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">Simpan password dan masuk ke portal</button>
                </form>
            @else
                <div class="mt-5 rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <p class="font-semibold">Tautan aktivasi tidak tersedia</p>
                    <p class="mt-1 leading-6">Tautan ini mungkin sudah digunakan atau kedaluwarsa. Buka kembali halaman cek status PPDB untuk membuat tautan aktivasi baru.</p>
                </div>
                <a href="{{ route('public.ppdb.status') }}" class="mt-5 inline-flex rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Buka cek status PPDB</a>
            @endif
        </div>
    </div>
</div>

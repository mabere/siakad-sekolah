<div class="min-h-screen bg-slate-50 pt-24">
    <div class="mx-auto max-w-xl px-4 py-10 sm:px-6">
        <a href="{{ route('public.ppdb.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Kembali ke PPDB</a>
        <div class="mt-5 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">Cek status pendaftaran</h1>
            <p class="mt-2 text-sm text-slate-600">Gunakan nomor pendaftaran dan kode akses yang diterima setelah mengirim formulir.</p>

            @if (! $application)
                <form wire:submit="check" class="mt-6 space-y-4">
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Nomor pendaftaran</label><input type="text" wire:model="applicationNumber" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm uppercase">@error('applicationNumber') <span class="text-xs text-red-600">{{ $message }}</span> @enderror</div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Kode akses</label><input type="password" inputmode="numeric" wire:model="accessCode" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">@error('accessCode') <span class="text-xs text-red-600">{{ $message }}</span> @enderror</div>
                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Tampilkan status</button>
                    <a href="{{ route('public.ppdb.access-recovery') }}" class="block text-center text-sm font-semibold text-indigo-600 hover:text-indigo-800">Lupa kode akses?</a>
                </form>
            @else
                 <div class="mt-6 rounded-md bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Calon peserta didik</p>
                    <p class="font-semibold text-slate-900">{{ $application->candidate?->name }}</p>
                    <p class="mt-3 text-xs text-slate-500">Nomor pendaftaran</p>
                     <p class="font-mono font-semibold text-slate-900">{{ $application->application_number }}</p>
                 </div>
                 @if ($receiptUrl)<a href="{{ $receiptUrl }}" target="_blank" rel="noopener" class="mt-4 inline-flex rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Unduh bukti pendaftaran PDF</a>@endif
                <dl class="mt-5 divide-y divide-slate-100 text-sm">
                    @php($registrationPayment = $application->payments->firstWhere('type', 'registration'))
                    @if ($registrationPayment)<div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Invoice pembayaran</dt><dd class="font-mono font-semibold text-slate-800">{{ $registrationPayment->invoice_number ?: '-' }}</dd></div>@endif
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Jalur</dt><dd class="font-semibold text-slate-800">{{ $application->pathway?->name }}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Verifikasi dokumen</dt><dd class="font-semibold text-slate-800">{{ ucfirst($application->verification_status) }}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Pembayaran</dt><dd class="font-semibold text-slate-800">{{ str_replace('_', ' ', ucfirst($application->payment_status)) }}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Seleksi</dt><dd class="font-semibold text-slate-800">{{ $application->period?->selection_finalized_at ? ucfirst($application->selection_status) : 'Menunggu finalisasi' }}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Daftar ulang</dt><dd class="font-semibold text-slate-800">{{ str_replace('_', ' ', ucfirst($application->reregistration_status)) }}</dd></div>
                </dl>
                @if ($application->conversion_status === 'converted')
                    <div class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-emerald-900">
                        <p class="text-sm font-semibold">Status akademik: sudah menjadi siswa</p>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-4"><dt class="text-emerald-700">NIS</dt><dd class="font-mono font-semibold">{{ $application->convertedStudent?->nis ?? '-' }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-emerald-700">Kelas</dt><dd class="font-semibold">{{ $application->convertedStudent?->classroom ? 'Kelas '.$application->convertedStudent->classroom->grade_level.' '.$application->convertedStudent->classroom->name : 'Sedang diproses' }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-emerald-700">Akun portal</dt><dd class="font-semibold">{{ $studentAccountActivated ? 'Sudah diaktifkan' : 'Belum diaktifkan' }}</dd></div>
                        </dl>
                        @if ($studentAccountActivated)
                            <p class="mt-3 text-xs text-emerald-800">Username: <span class="font-mono font-semibold">{{ $studentActivationUsername }}</span></p>
                            <a href="{{ route('login') }}" class="mt-4 inline-flex rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Masuk ke portal siswa</a>
                        @elseif ($studentActivationUrl)
                            <p class="mt-3 text-xs text-emerald-800">Gunakan tautan ini untuk membuat password baru. Tautan berlaku 30 menit dan hanya dapat digunakan sekali.</p>
                            <a href="{{ $studentActivationUrl }}" class="mt-4 inline-flex rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Aktivasi akun dan buat password</a>
                        @endif
                    </div>
                @endif
                @if ($application->payment_status === 'pending' || $application->payment_status === 'rejected')
                    <div class="mt-5 rounded-md border border-amber-200 bg-amber-50 p-4">
                        <p class="text-sm font-semibold text-amber-900">Upload bukti pembayaran</p>
                        @if ($application->period && (data_get($application->period->settings, 'payment_bank') || data_get($application->period->settings, 'payment_account_number')))
                            <p class="mt-2 text-xs text-amber-800"><strong>{{ data_get($application->period->settings, 'payment_bank', 'Metode pembayaran') }}</strong> · {{ data_get($application->period->settings, 'payment_account_number', '-') }} · {{ data_get($application->period->settings, 'payment_account_name', '-') }}</p>
                            @if (data_get($application->period->settings, 'payment_instructions'))<p class="mt-2 whitespace-pre-line text-xs text-amber-800">{{ data_get($application->period->settings, 'payment_instructions') }}</p>@endif
                        @endif
                        <p class="mt-1 text-xs text-amber-800">Gunakan PDF/JPG/PNG maksimal 10 MB.</p>
                        <form wire:submit="uploadPaymentProof" class="mt-3 space-y-3">
                            <input type="file" wire:model="paymentProof" accept=".pdf,.jpg,.jpeg,.png" class="block w-full rounded-md border border-slate-300 bg-white p-2 text-sm text-slate-600">
                            <textarea wire:model="paymentNotes" rows="2" placeholder="Catatan pembayaran (opsional)" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('paymentProof') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            <button type="submit" class="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white">Kirim bukti pembayaran</button>
                        </form>
                    </div>
                @endif
                <button type="button" wire:click="resetSearch" class="mt-5 text-sm font-semibold text-indigo-600 hover:text-indigo-800">Cek nomor lain</button>
            @endif
        </div>
    </div>
</div>

@php
    $ppdbPrefix = $this->ppdbRoutePrefix();
@endphp
<div>
    <x-slot name="title">Pendaftar PPDB</x-slot>

    @if (session()->has('message'))
        <div class="mb-4 rounded-md border-l-4 border-emerald-500 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 rounded-md border-l-4 border-red-500 bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @if ($isOfflineFormOpen)
        <div class="mb-6 rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 p-4"><div><h2 class="font-semibold text-slate-900">Input pendaftaran offline</h2><p class="text-xs text-slate-500">Data dibuat oleh panitia dan mengikuti alur verifikasi yang sama.</p></div><button type="button" wire:click="$set('isOfflineFormOpen', false)" class="text-sm font-semibold text-slate-500">Tutup</button></div>
            <form wire:submit="saveOffline" class="space-y-4 p-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Periode *</label><select wire:model.live="offlinePeriodId" class="w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm"><option value="">Pilih periode</option>@foreach ($offlinePeriods as $period)<option value="{{ $period->id }}">{{ $period->name }}</option>@endforeach</select>@error('offlinePeriodId')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Jalur *</label><select wire:model="offlinePathwayId" class="w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm"><option value="">Pilih jalur</option>@foreach ($offlinePathways as $pathway)<option value="{{ $pathway->id }}">{{ $pathway->name }}</option>@endforeach</select>@error('offlinePathwayId')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                    <x-ppdb-field label="Nama calon siswa *" model="offlineCandidateName" />
                    <x-ppdb-field label="NIK calon siswa" model="offlineCandidateNik" />
                    <x-ppdb-field label="NISN calon siswa" model="offlineCandidateNisn" />
                    <div><label class="mb-1 block text-sm font-medium text-slate-700">Jenis kelamin *</label><select wire:model="offlineGender" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm"><option value="">Pilih</option><option value="L">Laki-laki</option><option value="P">Perempuan</option></select>@error('offlineGender')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                    <x-ppdb-field label="Tempat lahir *" model="offlineBirthPlace" />
                    <x-ppdb-field label="Tanggal lahir *" model="offlineBirthDate" type="date" />
                    <x-ppdb-field label="Asal sekolah *" model="offlinePreviousSchool" />
                    <x-ppdb-field label="Nama orang tua/wali *" model="offlineGuardianName" />
                    <x-ppdb-field label="Nomor telepon wali *" model="offlineGuardianPhone" />
                    <x-ppdb-field label="Nomor kontak pendaftaran *" model="offlineContactPhone" />
                    <x-ppdb-field label="Email kontak" model="offlineContactEmail" type="email" />
                    <div class="md:col-span-2"><label class="mb-1 block text-sm font-medium text-slate-700">Alamat *</label><textarea wire:model="offlineAddress" rows="2" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm"></textarea>@error('offlineAddress')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                </div>
                <div class="flex justify-end"><button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Simpan pendaftar offline</button></div>
            </form>
        </div>
    @endif

    <div class="mb-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"><div><h2 class="font-semibold text-slate-900">Daftar pendaftar</h2><p class="text-xs text-slate-500">Verifikasi dokumen, pembayaran, hasil seleksi, dan daftar ulang.</p></div><div class="flex flex-wrap gap-2"><button type="button" wire:click="exportApplications" wire:loading.attr="disabled" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50">Ekspor CSV</button><button type="button" wire:click="openOfflineForm" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Input offline</button></div></div>
        <div class="mt-4 grid gap-3 md:grid-cols-4"><input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nomor atau nama..." class="rounded-md border-slate-300 px-3 py-2 text-sm"><select wire:model.live="periodFilter" class="rounded-md border-slate-300 bg-white px-3 py-2 text-sm"><option value="">Semua periode</option>@foreach ($periods as $period)<option value="{{ $period->id }}">{{ $period->name }}</option>@endforeach</select><select wire:model.live="verificationFilter" class="rounded-md border-slate-300 bg-white px-3 py-2 text-sm"><option value="">Semua verifikasi</option><option value="submitted">Menunggu verifikasi</option><option value="revision">Perlu perbaikan</option><option value="verified">Terverifikasi</option><option value="rejected">Ditolak</option></select><select wire:model.live="selectionFilter" class="rounded-md border-slate-300 bg-white px-3 py-2 text-sm"><option value="">Semua seleksi</option><option value="pending">Belum dinilai</option><option value="accepted">Diterima</option><option value="waitlisted">Cadangan</option><option value="rejected">Tidak diterima</option></select></div>
    </div>

    @if ($selectedApplication)
        @if ($selectedApplication->period?->allowsVerification())
            <div class="mb-4 rounded-md border border-indigo-200 bg-indigo-50 p-3 text-sm text-indigo-800">
                <p class="font-semibold">{{ $selectedApplication->period->verificationStageLabel() }}</p>
                <p class="mt-1 text-xs text-indigo-700">Panitia dapat memeriksa dokumen dan pembayaran tanpa menunggu pendaftaran ditutup.</p>
            </div>
        @else
            <div class="mb-4 rounded-md border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                <p class="font-semibold">{{ $selectedApplication->period?->verificationStageLabel() }}</p>
                <p class="mt-1 text-xs text-slate-600">Perubahan verifikasi dikunci karena periode sudah masuk tahap lanjutan.</p>
            </div>
        @endif
        <div class="mb-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Scoring seleksi detail</h2>
                    <p class="mt-1 text-xs text-slate-500">Nilai per kriteria menjadi bahan pertimbangan panitia. Keputusan akhir tetap ditetapkan melalui status seleksi.</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-500">Total / rata-rata</p>
                    <p class="font-semibold text-slate-900">{{ number_format($selectedApplication->selectionScores->sum(fn ($score) => (float) $score->score), 2, ',', '.') }} / {{ $selectedApplication->selectionScores->count() > 0 ? number_format($selectedApplication->selectionScores->avg('score'), 2, ',', '.') : '0,00' }}</p>
                </div>
            </div>
            <form wire:submit="saveSelectionScore" class="mt-4 grid gap-3 md:grid-cols-[1.2fr_0.6fr_1.5fr_auto] md:items-end">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Kriteria *</label>
                    <input type="text" wire:model="scoreCriterion" placeholder="Contoh: Nilai rapor" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                    @error('scoreCriterion') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Skor (0-100) *</label>
                    <input type="number" min="0" max="100" step="0.01" wire:model="scoreValue" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                    @error('scoreValue') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Catatan</label>
                    <input type="text" wire:model="scoreNotes" placeholder="Opsional" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                    @error('scoreNotes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="rounded-md bg-slate-800 px-3 py-2 text-xs font-semibold text-white">Simpan skor</button>
            </form>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-xs">
                    <thead class="border-b border-slate-200 text-slate-500"><tr><th class="py-2 pr-4">Kriteria</th><th class="py-2 pr-4">Skor</th><th class="py-2 pr-4">Catatan</th><th class="py-2 text-right">Aksi</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($selectedApplication->selectionScores as $score)
                            <tr><td class="py-2 pr-4 font-medium text-slate-800">{{ $score->criterion }}</td><td class="py-2 pr-4 font-semibold text-slate-800">{{ number_format((float) $score->score, 2, ',', '.') }}</td><td class="py-2 pr-4 text-slate-500">{{ $score->notes ?: '-' }}</td><td class="py-2 text-right"><button type="button" wire:click="removeSelectionScore({{ $score->id }})" class="font-semibold text-red-600">Hapus</button></td></tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-slate-500">Belum ada skor seleksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-6 rounded-lg border border-indigo-200 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 p-4"><div><h2 class="font-semibold text-slate-900">{{ $selectedApplication->application_number }} · {{ $selectedApplication->candidate?->name }}</h2><p class="text-xs text-slate-500">{{ ucfirst($selectedApplication->source) }} · {{ $selectedApplication->pathway?->name }}</p></div><button type="button" wire:click="closeApplication" class="text-sm font-semibold text-slate-500">Tutup</button></div>
            @if ($rotatedAccessCode)
                <div class="mx-4 mb-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    <p class="font-semibold">PIN baru berhasil dibuat</p>
                    <p class="mt-1">Sampaikan PIN ini kepada calon setelah verifikasi identitas:</p>
                    <p class="mt-1 font-mono text-lg font-bold">{{ $rotatedAccessCode }}</p>
                </div>
            @endif
            @if ($conversionCredentials)
                <div class="mx-4 mb-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">
                    <p class="font-semibold">Akun portal dibuat — simpan kredensial ini sekarang</p>
                    <p class="mt-1 text-xs text-emerald-800">Password tidak disimpan dalam bentuk terbaca dan tidak akan ditampilkan lagi setelah detail ditutup atau dimuat ulang.</p>
                    <div class="mt-3 space-y-2">@foreach ($conversionCredentials as $credential)<div class="rounded border border-emerald-200 bg-white p-2"><p class="text-xs font-semibold text-emerald-800">{{ $credential['role'] }} · {{ $credential['delivery'] }}</p><p class="mt-1 font-mono text-xs text-slate-800">Username: {{ $credential['username'] }}<br>Password: {{ $credential['password'] }}</p></div>@endforeach</div>
                </div>
            @endif
            <div class="mx-4 mb-4 rounded-md border border-slate-200 bg-slate-50 p-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Akses pendaftar</p>
                        <p class="mt-1 text-xs text-slate-500">Reset PIN hanya dilakukan setelah identitas calon diverifikasi.</p>
                    </div>
                    <button type="button" wire:click="resetAccessCode({{ $selectedApplication->id }})" wire:confirm="Reset PIN pendaftar ini? PIN lama akan langsung tidak berlaku." class="rounded-md border border-amber-300 bg-white px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-50">Reset PIN</button>
                </div>
            </div>
            <div class="grid gap-5 p-4 lg:grid-cols-2 {{ $selectedApplication->source === 'offline' ? 'ppdb-offline-detail' : 'ppdb-online-detail' }}">
                <div class="lg:col-span-2 rounded-md border border-slate-200 bg-slate-50 p-3"><p class="text-xs font-semibold text-slate-600">Akses berkas privat</p><div class="mt-2 flex flex-wrap gap-3">@foreach ($selectedApplication->documents as $document)<a href="{{ route($ppdbPrefix.'.ppdb.documents.download', $document->id) }}" class="text-xs font-semibold text-indigo-700">Unduh {{ $document->requirement?->name }}</a>@endforeach @foreach ($selectedApplication->payments->whereNotNull('proof_file') as $payment)<a href="{{ route($ppdbPrefix.'.ppdb.payments.proof', $payment->id) }}" class="text-xs font-semibold text-indigo-700">Unduh bukti pembayaran</a>@endforeach</div></div>
                <div><h3 class="text-sm font-semibold text-slate-800">Dokumen</h3><div class="mt-2 space-y-2">@forelse ($selectedApplication->documents as $document)<div class="flex items-center justify-between gap-3 rounded-md border border-slate-200 p-3"><div><p class="text-sm font-medium text-slate-800">{{ $document->requirement?->name }}</p><p class="text-xs text-slate-500">{{ $document->original_name }} · {{ ucfirst($document->status) }}</p></div><div class="flex gap-2">@if ($document->status !== 'verified')<button type="button" wire:click="setDocumentStatus({{ $document->id }}, 'verified')" class="text-xs font-semibold text-emerald-700">Terima</button>@endif@if ($document->status !== 'rejected')<button type="button" wire:click="setDocumentStatus({{ $document->id }}, 'rejected')" class="text-xs font-semibold text-red-700">Tolak</button>@endif</div></div>@empty<div class="rounded-md bg-amber-50 p-3 text-xs text-amber-700">Belum ada dokumen. Panitia dapat melengkapi dokumen melalui formulir di bawah.</div>@endforelse</div><form wire:submit="uploadDocument" class="mt-4 space-y-2 rounded-md border border-dashed border-slate-300 p-3"><p class="text-xs font-semibold text-slate-600">Upload/lengkapi dokumen</p><select wire:model="offlineRequirementId" class="w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm"><option value="">Pilih persyaratan</option>@foreach ($selectedApplication->pathway?->requirements ?? [] as $requirement)<option value="{{ $requirement->id }}">{{ $requirement->name }}</option>@endforeach</select><input type="file" wire:model="offlineDocument" accept=".pdf,.jpg,.jpeg,.png" class="block w-full p-2 rounded-md border border-slate-300 bg-white text-sm"><button type="submit" class="rounded-md bg-slate-700 px-3 py-2 text-xs font-semibold text-white">Simpan dokumen</button>@error('offlineRequirementId')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror @error('offlineDocument')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror</form></div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Pembayaran</h3>
                    <div class="mt-2 space-y-3">
                        @foreach ($selectedApplication->payments as $payment)
                            @php
                                $paymentLabel = match ($payment->status) {
                                    'verified' => 'Terverifikasi',
                                    'submitted' => 'Menunggu verifikasi',
                                    'rejected' => 'Ditolak',
                                    'not_required' => 'Tidak diperlukan',
                                    default => 'Belum dibayar',
                                };
                                $paymentBadge = match ($payment->status) {
                                    'verified' => 'bg-emerald-100 text-emerald-700',
                                    'submitted' => 'bg-amber-100 text-amber-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <div class="rounded-md border border-slate-200 p-3">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-slate-800">{{ ucfirst($payment->type) }}</p>
                                        <p class="mt-1 font-mono text-xs text-slate-500">{{ $payment->invoice_number ?: 'Tanpa invoice' }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                                    </div>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $paymentBadge }}">{{ $paymentLabel }}</span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if ($payment->proof_file)
                                        <a href="{{ route($ppdbPrefix.'.ppdb.payments.proof', $payment->id) }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Unduh bukti pembayaran</a>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-slate-50 px-3 py-1.5 text-xs text-slate-500">Bukti belum diunggah</span>
                                    @endif
                                    @if (in_array($payment->status, ['pending', 'submitted'], true))
                                        <button type="button" wire:click="verifyPayment({{ $payment->id }})" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Verifikasi pembayaran</button>
                                        <button type="button" wire:click="rejectPayment({{ $payment->id }})" wire:confirm="Tolak pembayaran ini? Pastikan catatan keputusan sudah diisi." class="inline-flex items-center rounded-md border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">Tolak pembayaran</button>
                                    @endif
                                </div>
                                @if ($payment->notes)
                                    <p class="mt-2 text-xs text-slate-500">Catatan: {{ $payment->notes }}</p>
                                @endif
                                @if ($payment->histories->isNotEmpty())
                                    <details class="mt-3 text-xs text-slate-500"><summary class="cursor-pointer font-semibold text-slate-600">Riwayat pembayaran ({{ $payment->histories->count() }})</summary><div class="mt-2 space-y-1">@foreach ($payment->histories as $history)<p>{{ $history->created_at?->format('d/m/Y H:i') }} · {{ ucfirst($history->to_status) }}@if ($history->actor) · {{ $history->actor->name }}@endif</p>@endforeach</div></details>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-200 bg-slate-50 p-4"><label class="mb-1 block text-xs font-semibold text-slate-600">Catatan keputusan</label><textarea wire:model="decisionNote" rows="2" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm" placeholder="Isi alasan bila meminta perbaikan atau menolak"></textarea><div class="mt-3 flex flex-wrap gap-2"><button type="button" wire:click="setVerificationStatus({{ $selectedApplication->id }}, 'verified')" class="rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Tandai terverifikasi</button><button type="button" wire:click="setVerificationStatus({{ $selectedApplication->id }}, 'revision')" class="rounded-md bg-amber-500 px-3 py-2 text-xs font-semibold text-white">Minta perbaikan</button><button type="button" wire:click="setVerificationStatus({{ $selectedApplication->id }}, 'rejected')" class="rounded-md bg-red-600 px-3 py-2 text-xs font-semibold text-white">Tolak verifikasi</button>@if ($selectedApplication->verification_status === 'verified')<button type="button" wire:click="setSelectionStatus({{ $selectedApplication->id }}, 'accepted')" class="rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white">Tetapkan diterima</button><button type="button" wire:click="setSelectionStatus({{ $selectedApplication->id }}, 'waitlisted')" class="rounded-md border border-indigo-300 px-3 py-2 text-xs font-semibold text-indigo-700">Cadangan</button><button type="button" wire:click="setSelectionStatus({{ $selectedApplication->id }}, 'rejected')" class="rounded-md border border-red-300 px-3 py-2 text-xs font-semibold text-red-700">Tidak diterima</button>@endif@if ($selectedApplication->selection_status === 'accepted')<button type="button" wire:click="openReregistration({{ $selectedApplication->id }})" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Buka daftar ulang</button>@endif@if ($selectedApplication->reregistration_status === 'confirmed')<button type="button" wire:click="verifyReregistration({{ $selectedApplication->id }})" class="rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Verifikasi daftar ulang</button>@endif@if ($selectedApplication->reregistration_status === 'verified' && $selectedApplication->conversion_status !== 'converted')<button type="button" wire:click="convertToStudent({{ $selectedApplication->id }})" class="rounded-md bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Konversi menjadi siswa</button>@endif</div></div>
        </div>
    @endif

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Pendaftar</th><th class="px-4 py-3">Jalur</th><th class="px-4 py-3">Sumber</th><th class="px-4 py-3">Verifikasi</th><th class="px-4 py-3">Seleksi</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse ($applications as $application)<tr><td class="px-4 py-3"><p class="font-semibold text-slate-900">{{ $application->candidate?->name }}</p><p class="font-mono text-xs text-slate-500">{{ $application->application_number }}</p></td><td class="px-4 py-3 text-slate-600">{{ $application->pathway?->name }}</td><td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">{{ ucfirst($application->source) }}</span></td><td class="px-4 py-3 text-xs font-semibold text-slate-700">{{ ucfirst($application->verification_status) }}</td><td class="px-4 py-3 text-xs font-semibold text-slate-700">{{ ucfirst($application->selection_status) }}</td><td class="px-4 py-3 text-right"><button type="button" wire:click="showApplication({{ $application->id }})" class="text-xs font-semibold text-indigo-600">Detail</button></td></tr>@empty<tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">Belum ada pendaftar.</td></tr>@endforelse</tbody></table></div><div class="border-t border-slate-200 p-4">{{ $applications->links() }}</div></div>
</div>

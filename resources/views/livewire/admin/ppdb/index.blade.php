<div>
    <x-slot name="title">Manajemen PPDB</x-slot>

    @if (session()->has('message'))
        <div class="mb-4 rounded-md border-l-4 border-emerald-500 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 rounded-md border-l-4 border-red-500 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-900">
        <p class="font-semibold">PPDB level {{ $schoolLevel }}</p>
        <p class="mt-1 text-indigo-700">Jalur Umum, Prestasi, dan Pindahan dibuat otomatis. Jalur Zonasi dan Afirmasi dapat ditambahkan pada tahap konfigurasi lanjutan.</p>
    </div>

    @if ($reopenVerificationPeriodId)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-amber-900">Buka kembali verifikasi</h2>
                    <p class="mt-1 text-sm text-amber-800">Periode: {{ $reopenVerificationPeriodName }}</p>
                    <p class="mt-1 text-xs leading-5 text-amber-700">Gunakan hanya untuk koreksi teknis atau penyelesaian antrean yang tertunda. Tindakan ini dicatat dalam audit log.</p>
                </div>
                <button type="button" wire:click="cancelReopenVerification" class="text-sm font-semibold text-amber-800 hover:text-amber-950">Batal</button>
            </div>
            <form wire:submit="reopenVerification" class="mt-4 space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-amber-900">Alasan pembukaan kembali *</label>
                    <textarea wire:model="reopenVerificationReason" rows="3" maxlength="1000" placeholder="Contoh: Verifikasi pembayaran beberapa pendaftar tertunda karena gangguan layanan upload." class="w-full rounded-md border-amber-300 bg-white px-3 py-2 text-sm"></textarea>
                    @error('reopenVerificationReason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <button type="submit" wire:confirm="Buka kembali periode ini ke tahap verifikasi? Tindakan akan dicatat dalam audit log." class="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">Konfirmasi buka kembali</button>
            </form>
        </div>
    @endif

    @if ($cancelFinalizationPeriodId)
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-red-900">Batalkan finalisasi hasil seleksi</h2>
                    <p class="mt-1 text-sm text-red-800">Periode: {{ $cancelFinalizationPeriodName }}</p>
                    <p class="mt-1 text-xs leading-5 text-red-700">Periode akan kembali ke tahap verifikasi. Snapshot hasil lama tidak dihapus, tetapi ditandai tidak berlaku dan disimpan sebagai riwayat. Pengumuman, daftar ulang, dan konversi siswa harus belum dimulai.</p>
                </div>
                <button type="button" wire:click="closeCancelFinalization" class="text-sm font-semibold text-red-800 hover:text-red-950">Batal</button>
            </div>
            <form wire:submit="cancelFinalization" class="mt-4 space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-red-900">Alasan pembatalan finalisasi *</label>
                    <textarea wire:model="cancelFinalizationReason" rows="3" maxlength="1000" placeholder="Contoh: Terdapat kesalahan teknis pada hasil pemeringkatan yang perlu diperiksa ulang." class="w-full rounded-md border-red-300 bg-white px-3 py-2 text-sm"></textarea>
                    @error('cancelFinalizationReason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <button type="submit" wire:confirm="Batalkan finalisasi hasil seleksi dan kembalikan periode ke verifikasi? Snapshot lama akan ditandai tidak berlaku dan tindakan ini dicatat." class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Konfirmasi pembatalan finalisasi</button>
            </form>
        </div>
    @endif

    @if ($isFormOpen)
        <div class="mb-6 rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 p-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">{{ $isEdit ? 'Edit Periode PPDB' : 'Tambah Periode PPDB' }}</h2>
                    <p class="text-xs text-slate-500">Level sekolah diambil otomatis dari pengaturan sekolah.</p>
                </div>
                <button type="button" wire:click="closeForm" class="text-sm font-semibold text-slate-500 hover:text-slate-800">Tutup</button>
            </div>

            <form wire:submit="save" class="space-y-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama periode *</label>
                        <input type="text" wire:model="name" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kode periode *</label>
                        <input type="text" wire:model="code" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm uppercase">
                        @error('code') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tahun ajaran *</label>
                        <select wire:model="academic_year_id" class="w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm">
                            <option value="">Pilih tahun ajaran</option>
                            @foreach ($academicYears as $academicYear)
                                <option value="{{ $academicYear->id }}">{{ $academicYear->name }} - {{ $academicYear->semester }}</option>
                            @endforeach
                        </select>
                        @error('academic_year_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Status *</label>
                        <select wire:model="status" class="w-full rounded-md border-slate-300 bg-white px-3 py-2 text-sm">
                            @foreach (\App\Models\PpdbPeriod::STATUSES as $periodStatus)
                                <option value="{{ $periodStatus }}">{{ str_replace('_', ' ', ucfirst($periodStatus)) }}</option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Mulai pendaftaran *</label>
                        <input type="datetime-local" wire:model="registration_starts_at" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                        @error('registration_starts_at') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Akhir pendaftaran *</label>
                        <input type="datetime-local" wire:model="registration_ends_at" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                        @error('registration_ends_at') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Batas verifikasi</label>
                        <input type="datetime-local" wire:model="verification_ends_at" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                        @error('verification_ends_at') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Waktu pengumuman</label>
                        <input type="datetime-local" wire:model="announcement_at" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                        @error('announcement_at') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Batas daftar ulang</label>
                        <input type="datetime-local" wire:model="re_registration_ends_at" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                        @error('re_registration_ends_at') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Biaya pendaftaran standar</label>
                        <input type="number" min="0" step="0.01" wire:model="default_registration_fee" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                        @error('default_registration_fee') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Bank/metode pembayaran</label>
                        <input type="text" wire:model="payment_bank" placeholder="Contoh: BRI" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                        @error('payment_bank') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama pemilik rekening</label>
                        <input type="text" wire:model="payment_account_name" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                        @error('payment_account_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nomor rekening</label>
                        <input type="text" wire:model="payment_account_number" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm">
                        @error('payment_account_number') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Instruksi pembayaran</label>
                        <textarea wire:model="payment_instructions" rows="3" placeholder="Tuliskan langkah pembayaran dan keterangan yang perlu dicantumkan." class="w-full rounded-md border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('payment_instructions') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="payment_required" class="rounded border-slate-300 text-indigo-600">
                    Pembayaran pendaftaran diperlukan
                </label>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" wire:click="closeForm" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Batal</button>
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan periode</button>
                </div>
            </form>
        </div>
    @endif

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 p-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Periode PPDB</h2>
                <p class="text-xs text-slate-500">Kelola periode, status, dan jalur penerimaan.</p>
            </div>
            @if (! $isFormOpen && $canManagePeriods)
                <button type="button" wire:click="create" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Tambah periode</button>
            @endif
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($periods as $period)
                <div class="p-4">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-slate-900">{{ $period->name }}</h3>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $period->code }}</span>
                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ ucfirst($period->status) }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">{{ $period->academicYear?->name }} - {{ $period->academicYear?->semester }} · {{ $period->level }} · {{ $period->applications_count }} pendaftar</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $period->registration_starts_at->format('d/m/Y H:i') }} s/d {{ $period->registration_ends_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if ($canManagePeriods)<button type="button" wire:click="edit({{ $period->id }})" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Edit</button>@endif
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
                        @foreach ($period->pathways as $pathway)
                            <div class="rounded-md border border-slate-200 px-3 py-2">
                                <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $pathway->name }}</p>
                                </div>
                                @if ($canManagePeriods)<button type="button" wire:click="togglePathway({{ $pathway->id }})" class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $pathway->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">@else<span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $pathway->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">@endif
                                    {{ $pathway->is_active ? 'Aktif' : 'Nonaktif' }}
                                @if ($canManagePeriods)</button>@else</span>@endif
                                </div>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <label class="text-[11px] text-slate-500">Kuota<input type="number" min="0" @if ($canManagePeriods) wire:change="updatePathwaySettings({{ $pathway->id }}, $event.target.value, {{ $pathway->registration_fee }})" @else disabled @endif value="{{ $pathway->quota }}" class="mt-1 w-full rounded border-slate-300 px-2 py-1 text-xs"></label>
                                    <label class="text-[11px] text-slate-500">Biaya<input type="number" min="0" step="0.01" @if ($canManagePeriods) wire:change="updatePathwaySettings({{ $pathway->id }}, {{ $pathway->quota }}, $event.target.value)" @else disabled @endif value="{{ $pathway->registration_fee }}" class="mt-1 w-full rounded border-slate-300 px-2 py-1 text-xs"></label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if ($canManagePeriods)<div class="mt-3 flex flex-wrap gap-2">
                        @if (! $period->pathways->contains('code', 'zonasi'))
                            <button type="button" wire:click="addOptionalPathway({{ $period->id }}, 'zonasi')" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Aktifkan Zonasi</button>
                        @endif
                        @if (! $period->pathways->contains('code', 'afirmasi'))
                            <button type="button" wire:click="addOptionalPathway({{ $period->id }}, 'afirmasi')" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Aktifkan Afirmasi</button>
                        @endif
                    </div>@endif
                    @if ($canCancelFinalization && $period->selection_finalized_at && in_array($period->status, [\App\Models\PpdbPeriod::STATUS_SELECTION, \App\Models\PpdbPeriod::STATUS_CLOSED], true))
                        <button type="button" wire:click="openCancelFinalization({{ $period->id }})" class="mt-3 rounded-md border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-100">Batalkan finalisasi hasil</button>
                    @elseif ($canReopenVerification && $period->status === \App\Models\PpdbPeriod::STATUS_CLOSED)
                        <button type="button" wire:click="openReopenVerification({{ $period->id }})" class="mt-3 rounded-md border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">Buka kembali verifikasi</button>
                    @endif
                    @if ($period->status === \App\Models\PpdbPeriod::STATUS_SELECTION && ! $period->selection_finalized_at && in_array(session('active_role'), ['Super Admin', 'Admin Sekolah', 'Kepala Sekolah'], true))
                        <button type="button" wire:click="finalizeSelection({{ $period->id }})" wire:confirm="Hasil seleksi akan dikunci dan menjadi dasar pengumuman. Lanjutkan?" class="mt-3 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Finalisasi hasil seleksi</button>
                    @elseif ($period->selection_finalized_at)
                        <p class="mt-3 text-xs font-semibold text-emerald-700">Hasil seleksi terkunci pada {{ $period->selection_finalized_at->format('d/m/Y H:i') }}.</p>
                    @endif
                </div>
            @empty
                <div class="p-10 text-center text-sm text-slate-500">Belum ada periode PPDB.</div>
            @endforelse
        </div>
    </div>
</div>

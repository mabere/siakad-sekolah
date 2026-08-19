<div class="min-h-screen bg-slate-50 pt-24">
    @php
        $steps = [
            1 => 'Data calon',
            2 => 'Orang tua/wali',
            3 => 'Dokumen',
            4 => 'Review & kirim',
            5 => 'Pembayaran',
        ];
        $requiresPayment = $period->payment_required && (float) ($selectedPathway?->registration_fee ?? 0) > 0;
    @endphp

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('public.ppdb.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Kembali ke daftar periode</a>
        <div class="mt-5">
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Formulir PPDB</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $period->name }}</h1>
            <p class="mt-2 text-sm text-slate-600">Isi formulir secara bertahap. Pastikan data dan dokumen yang dikirim sudah benar.</p>
        </div>

        <nav class="mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white p-2 shadow-sm" aria-label="Tahapan pendaftaran">
            <ol class="flex min-w-[650px] items-center gap-1">
                @foreach ($steps as $step => $label)
                    <li class="flex-1">
                        @if ($step < $currentStep && $currentStep < 5)
                            <button type="button" wire:click="goToStep({{ $step }})" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm text-slate-600 hover:bg-slate-50">
                        @else
                            <div class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm {{ $step === $currentStep ? 'bg-indigo-50 font-semibold text-indigo-700' : ($step < $currentStep ? 'text-emerald-700' : 'text-slate-400') }}">
                        @endif
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border text-xs font-bold {{ $step === $currentStep ? 'border-indigo-600 bg-indigo-600 text-white' : ($step < $currentStep ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300') }}">{{ $step }}</span>
                            <span>{{ $label }}</span>
                        @if ($step < $currentStep && $currentStep < 5)
                            </button>
                        @else
                            </div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        @if ($currentStep === 5)
            <section class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-6 text-emerald-900">
                <h2 class="text-xl font-bold">Pendaftaran berhasil dikirim</h2>
                <p class="mt-2 text-sm">Unduh atau cetak bukti pendaftaran sebelum menutup halaman ini.</p>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-md bg-white/70 p-3"><dt class="text-xs text-emerald-700">Nomor pendaftaran</dt><dd class="mt-1 font-mono text-lg font-bold">{{ $applicationNumber }}</dd></div>
                    <div class="rounded-md bg-white/70 p-3"><dt class="text-xs text-emerald-700">PIN akses</dt><dd class="mt-1 font-mono text-lg font-bold">{{ $accessCode }}</dd></div>
                </dl>
                <div class="mt-5 flex flex-wrap gap-2">
                    <button type="button" wire:click="downloadReceipt" wire:loading.attr="disabled" class="inline-flex rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800 disabled:opacity-60">Unduh bukti PDF</button>
                    <button type="button" onclick="window.print()" class="inline-flex rounded-md border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">Cetak</button>
                    <button type="button" onclick="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from($applicationNumber.' | PIN: '.$accessCode) }})" class="inline-flex rounded-md border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">Salin nomor & PIN</button>
                </div>
                <p class="mt-3 text-xs text-emerald-800">Jika PIN hilang, hubungi panitia PPDB untuk reset setelah verifikasi identitas.</p>
            </section>

            @if ($requiresPayment)
                <section class="mt-6 rounded-lg border border-amber-200 bg-white p-6 shadow-sm">
                    <h2 class="font-semibold text-slate-900">Langkah pembayaran</h2>
                    <p class="mt-1 text-sm text-slate-600">Biaya pendaftaran jalur {{ $selectedPathway?->name }}: <strong>Rp {{ number_format((float) $selectedPathway->registration_fee, 0, ',', '.') }}</strong>.</p>
                    @if (data_get($period->settings, 'payment_bank') || data_get($period->settings, 'payment_account_number'))
                        <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                            <p><strong>{{ data_get($period->settings, 'payment_bank', 'Metode pembayaran') }}</strong></p>
                            <p class="mt-1">{{ data_get($period->settings, 'payment_account_number', '-') }} · {{ data_get($period->settings, 'payment_account_name', '-') }}</p>
                            @if (data_get($period->settings, 'payment_instructions'))<p class="mt-2 whitespace-pre-line text-xs text-amber-800">{{ data_get($period->settings, 'payment_instructions') }}</p>@endif
                        </div>
                    @else
                        <p class="mt-1 text-xs text-slate-500">Lakukan pembayaran sesuai informasi resmi panitia, lalu unggah buktinya di bawah.</p>
                    @endif

                    @if ($paymentStatus === 'pending' || $paymentStatus === 'rejected')
                        <form wire:submit="uploadPaymentProof" class="ppdb-form mt-4 space-y-3">
                            @if ($paymentStatus === 'rejected')
                                <div class="rounded-md bg-red-50 p-3 text-xs text-red-700">Bukti pembayaran sebelumnya ditolak. Silakan unggah bukti yang lebih jelas.</div>
                            @endif
                            <input type="file" wire:model="paymentProof" accept=".pdf,.jpg,.jpeg,.png" class="block w-full p-2 rounded-md border border-slate-300 bg-white text-sm text-slate-600 shadow-sm file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-slate-700">
                            <textarea wire:model="paymentNotes" rows="2" placeholder="Catatan pembayaran (opsional)" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></textarea>
                            @error('paymentProof') <p class="text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                            <button type="submit" wire:loading.attr="disabled" class="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-60">Kirim bukti pembayaran</button>
                        </form>
                    @elseif ($paymentStatus === 'submitted')
                        <p class="mt-4 rounded-md bg-amber-50 p-3 text-sm text-amber-800">Bukti pembayaran sudah dikirim dan menunggu verifikasi panitia.</p>
                    @elseif ($paymentStatus === 'verified')
                        <p class="mt-4 rounded-md bg-emerald-50 p-3 text-sm text-emerald-800">Pembayaran sudah terverifikasi.</p>
                    @endif
                </section>
            @else
                <section class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="font-semibold text-slate-900">Pendaftaran selesai</h2>
                    <p class="mt-1 text-sm text-slate-600">Periode dan jalur ini tidak mewajibkan pembayaran pendaftaran.</p>
                </section>
            @endif

            <a href="{{ route('public.ppdb.status') }}" class="mt-5 inline-flex rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Cek status pendaftaran</a>
        @else
            <form wire:submit="submit" class="ppdb-form mt-6 space-y-6">
                @if ($currentStep === 1)
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-slate-900">Pilih jalur pendaftaran</h2>
                        <div class="mt-3 grid gap-3 md:grid-cols-3">
                            @foreach ($pathways as $pathway)
                                <label class="cursor-pointer rounded-md border p-3 {{ (string) $pathwayId === (string) $pathway->id ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200' }}">
                                    <input type="radio" wire:model.live="pathwayId" value="{{ $pathway->id }}" class="text-indigo-600">
                                    <span class="ml-2 text-sm font-semibold text-slate-800">{{ $pathway->name }}</span>
                                    <span class="mt-1 block text-xs text-slate-500">{{ $pathway->description }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('pathwayId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </section>

                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-slate-900">Data calon peserta didik</h2>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <x-ppdb-field label="Nama lengkap *" model="candidateName" />
                            <x-ppdb-field label="NIK" model="candidateNik" />
                            <x-ppdb-field label="NISN" model="candidateNisn" />
                            <div><label for="ppdb-candidate-gender" class="mb-1.5 block text-sm font-medium text-slate-700">Jenis kelamin *</label><select id="ppdb-candidate-gender" wire:model="candidateGender" class="h-11 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"><option value="">Pilih</option><option value="L">Laki-laki</option><option value="P">Perempuan</option></select>@error('candidateGender') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror</div>
                            <x-ppdb-field label="Tempat lahir *" model="birthPlace" />
                            <x-ppdb-field label="Tanggal lahir *" model="birthDate" type="date" />
                            <x-ppdb-field label="Asal sekolah *" model="previousSchool" />
                            <x-ppdb-field label="Kode pos" model="postalCode" />
                            <div class="md:col-span-2"><label for="ppdb-address" class="mb-1.5 block text-sm font-medium text-slate-700">Alamat *</label><textarea id="ppdb-address" wire:model="address" rows="3" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></textarea>@error('address') <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror</div>
                            <x-ppdb-field label="Desa/Kelurahan" model="village" />
                            <x-ppdb-field label="Kecamatan" model="district" />
                            <x-ppdb-field label="Kabupaten/Kota" model="regency" />
                            <x-ppdb-field label="Provinsi" model="province" />
                        </div>
                    </section>
                @elseif ($currentStep === 2)
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-slate-900">Data orang tua/wali</h2>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div><label for="ppdb-guardian-relationship" class="mb-1.5 block text-sm font-medium text-slate-700">Hubungan *</label><select id="ppdb-guardian-relationship" wire:model="guardianRelationship" class="h-11 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"><option value="ayah">Ayah</option><option value="ibu">Ibu</option><option value="wali">Wali</option></select></div>
                            <x-ppdb-field label="Nama orang tua/wali *" model="guardianName" />
                            <x-ppdb-field label="NIK orang tua/wali" model="guardianNik" />
                            <x-ppdb-field label="Nomor telepon *" model="guardianPhone" />
                            <x-ppdb-field label="Email" model="guardianEmail" type="email" />
                            <x-ppdb-field label="Pekerjaan" model="guardianOccupation" />
                            <div class="md:col-span-2"><label for="ppdb-guardian-address" class="mb-1.5 block text-sm font-medium text-slate-700">Alamat orang tua/wali</label><textarea id="ppdb-guardian-address" wire:model="guardianAddress" rows="2" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></textarea></div>
                            <x-ppdb-field label="Email kontak pendaftaran" model="contactEmail" type="email" />
                            <x-ppdb-field label="Telepon kontak pendaftaran *" model="contactPhone" />
                        </div>
                    </section>
                @elseif ($currentStep === 3)
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-slate-900">Dokumen persyaratan</h2>
                        <div class="mt-4 space-y-4">
                            @foreach ($requirements as $requirement)
                                <div><label class="mb-1 block text-sm font-medium text-slate-700">{{ $requirement->name }} @if ($requirement->is_required)<span class="text-red-600">*</span>@else<span class="text-xs font-normal text-slate-500">(opsional)</span>@endif</label><input type="file" wire:model="documents.{{ $requirement->id }}" accept=".pdf,.jpg,.jpeg,.png" class="block w-full rounded-md border border-slate-300 bg-white p-2 text-sm text-slate-600 file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2">@error('documents.'.$requirement->id) <span class="text-xs text-red-600">{{ $message }}</span> @enderror</div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs text-slate-500">Format PDF/JPG/PNG, maksimal 10 MB per file.</p>
                    </section>
                @else
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-slate-900">Periksa kembali data pendaftaran</h2>
                        <p class="mt-1 text-sm text-slate-600">Pastikan data utama sudah benar sebelum formulir dikirim.</p>
                        <dl class="mt-4 divide-y divide-slate-100 text-sm">
                            <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Jalur</dt><dd class="font-semibold text-slate-800">{{ $selectedPathway?->name ?: '-' }}</dd></div>
                            <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Nama calon</dt><dd class="font-semibold text-slate-800">{{ $candidateName ?: '-' }}</dd></div>
                            <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Jenis kelamin</dt><dd class="font-semibold text-slate-800">{{ $candidateGender === 'L' ? 'Laki-laki' : ($candidateGender === 'P' ? 'Perempuan' : '-') }}</dd></div>
                            <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Orang tua/wali</dt><dd class="font-semibold text-slate-800">{{ $guardianName ?: '-' }}</dd></div>
                            <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Kontak</dt><dd class="font-semibold text-slate-800">{{ $contactPhone ?: '-' }}</dd></div>
                            <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500">Dokumen</dt><dd class="font-semibold text-slate-800">{{ count(array_filter($documents)) }} berkas dipilih</dd></div>
                        </dl>
                    </section>

                    @if ($requiresPayment)
                        <section class="rounded-lg border border-amber-200 bg-amber-50 p-5">
                            <h2 class="font-semibold text-amber-900">Informasi pembayaran</h2>
                    <p class="mt-1 text-sm text-amber-800">Biaya pendaftaran jalur ini adalah <strong>Rp {{ number_format((float) $selectedPathway->registration_fee, 0, ',', '.') }}</strong>.</p>
                            @if (data_get($period->settings, 'payment_bank') || data_get($period->settings, 'payment_account_number'))
                                <p class="mt-2 text-xs text-amber-700"><strong>{{ data_get($period->settings, 'payment_bank', 'Metode pembayaran') }}</strong> · {{ data_get($period->settings, 'payment_account_number', '-') }} · {{ data_get($period->settings, 'payment_account_name', '-') }}</p>
                                @if (data_get($period->settings, 'payment_instructions'))<p class="mt-2 whitespace-pre-line text-xs text-amber-700">{{ data_get($period->settings, 'payment_instructions') }}</p>@endif
                            @else
                                <p class="mt-2 text-xs text-amber-700">Setelah formulir berhasil dikirim, tahap pembayaran akan terbuka untuk mengunggah bukti pembayaran. Metode pembayaran mengikuti informasi resmi panitia.</p>
                            @endif
                        </section>
                    @else
                        <section class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                            <h2 class="font-semibold text-slate-900">Informasi pembayaran</h2>
                            <p class="mt-1 text-sm text-slate-600">Pendaftaran ini tidak memerlukan pembayaran.</p>
                        </section>
                    @endif
                @endif

                <div class="flex flex-wrap justify-between gap-3">
                    @if ($currentStep > 1)
                        <button type="button" wire:click="previousStep" class="rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kembali</button>
                    @else
                        <span></span>
                    @endif
                    @if ($currentStep < 4)
                        <button type="button" wire:click="nextStep" wire:loading.attr="disabled" class="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">Lanjut</button>
                    @else
                        <button type="submit" wire:loading.attr="disabled" class="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">Kirim pendaftaran</button>
                    @endif
                </div>
            </form>
        @endif
    </div>
</div>

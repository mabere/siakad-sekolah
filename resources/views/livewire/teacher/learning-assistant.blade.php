<div>
    <x-slot name="title">Perangkat Pembelajaran AI</x-slot>

    <div class="mb-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Perangkat Pembelajaran AI</h1>
                <p class="mt-1 max-w-3xl text-sm text-slate-500">
                    Generator perangkat pembelajaran terpadu berstandar Kurikulum Merdeka (PPA 2024 & BSKAP 032/2024) berbasis Gemini AI.
                </p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700">
                ✨ Powered by Gemini AI
            </span>
        </div>
    </div>

    @if (! $isConfigured)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Fitur AI belum aktif.</p>
            <p class="mt-1">
                Administrator perlu mengatur credential Gemini dan mengaktifkan fitur ini. Modul akademik lain tetap berjalan seperti biasa.
            </p>
        </div>
    @endif

    @if (session()->has('generation_success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 flex items-center gap-2">
            <span>✅</span> {{ session('generation_success') }}
        </div>
    @endif

    @if (session()->has('duplicate_success'))
        <div class="mb-6 rounded-xl border border-purple-200 bg-purple-50 p-4 text-sm font-semibold text-purple-900 flex items-center gap-2">
            <span>✨</span> {{ session('duplicate_success') }}
        </div>
    @endif

    @if (str_contains($additionalContext, '[Rekomendasi Diferensiasi AI]'))
        <div class="mb-6 rounded-xl border border-indigo-200 bg-indigo-50/80 p-4 text-xs font-semibold text-indigo-900 flex items-center justify-between">
            <span class="flex items-center gap-2">
                <span>💡</span> Rekomendasi Diferensiasi AI Berhasil Diterapkan ke Konteks Pembelajaran
            </span>
            <span class="text-2xs bg-white text-indigo-800 border border-indigo-200 px-2 py-0.5 rounded-full font-bold">
                Terpersonalisasi
            </span>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
        <section class="xl:col-span-2">
            <form wire:submit.prevent="generate" class="space-y-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Konteks Pembuatan Perangkat Ajar</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                        Pilih jenis dokumen dan sesuaikan parameter kurikulum. Data privasi siswa tetap aman dan terlindungi.
                    </p>
                </div>

                <div>
                    <label for="documentType" class="mb-2 block text-sm font-semibold text-slate-700">Jenis Dokumen Kurikulum Merdeka</label>
                    <select id="documentType" wire:model.live="documentType" class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 bg-white font-medium">
                        @foreach ($documentTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('documentType') <span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
                </div>

                {{-- Parameter Spesifik Berdasarkan Jenis Dokumen --}}
                @if ($documentType === 'modul_p5')
                    <div class="rounded-xl border border-purple-200 bg-purple-50/60 p-3.5 space-y-2">
                        <label for="selectedP5Theme" class="block text-xs font-bold text-purple-900 uppercase">🌟 Tema Resmi Projek P5</label>
                        <select id="selectedP5Theme" wire:model="selectedP5Theme" class="w-full rounded-lg border-purple-300 bg-white px-3 py-2 text-xs font-semibold text-purple-900 shadow-2xs focus:border-purple-500">
                            @foreach ($availableP5Themes as $theme)
                                <option value="{{ $theme }}">{{ $theme }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif ($documentType === 'prota_prosem')
                    <div class="rounded-xl border border-blue-200 bg-blue-50/60 p-3.5 space-y-2">
                        <label class="block text-xs font-bold text-blue-900 uppercase">📅 Estimasi Pekan Efektif Kalender Pendidikan</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-2xs text-slate-600">Semester Ganjil:</span>
                                <input type="number" min="1" max="30" wire:model="effectiveWeeksOdd" class="w-full rounded border-blue-300 bg-white px-2.5 py-1 text-xs text-center font-bold">
                            </div>
                            <div>
                                <span class="text-2xs text-slate-600">Semester Genap:</span>
                                <input type="number" min="1" max="30" wire:model="effectiveWeeksEven" class="w-full rounded border-blue-300 bg-white px-2.5 py-1 text-xs text-center font-bold">
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <label for="selectedScheduleId" class="mb-2 block text-sm font-semibold text-slate-700">Jadwal Mengajar</label>
                    <select id="selectedScheduleId" wire:model.live="selectedScheduleId" class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 bg-white">
                        <option value="">-- Pilih mata pelajaran dan kelas --</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule['id'] }}">
                                {{ $schedule['subject'] }} — Kelas {{ $schedule['classroom'] }} ({{ $schedule['time'] }})
                            </option>
                        @endforeach
                    </select>
                    @if ($detectedFase)
                        <div class="mt-2 inline-flex items-center gap-1.5 rounded-md bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 border border-teal-200">
                            🎯 Terdeteksi: <strong>{{ $detectedFase }}</strong>
                        </div>
                    @endif
                    @if (count($schedules) === 0)
                        <p class="mt-1 text-xs text-slate-500">Belum ada jadwal aktif yang terhubung ke akun guru ini.</p>
                    @endif
                    @error('selectedScheduleId') <span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
                </div>

                @if ($documentType !== 'modul_p5')
                <div>
                    <label for="selectedLearningModel" class="mb-2 block text-sm font-semibold text-slate-700">Model Pembelajaran <span class="font-normal text-slate-400">(opsional)</span></label>
                    <select id="selectedLearningModel" wire:model="selectedLearningModel" class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 bg-white">
                        <option value="">-- Rekomendasikan Otomatis oleh AI --</option>
                        @foreach ($availableLearningModels as $modelOption)
                            <option value="{{ $modelOption }}">{{ $modelOption }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Dimensi Profil Pelajar Pancasila (P5) <span class="font-normal text-slate-400">(opsional)</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        @foreach ($availableP5Dimensions as $dim)
                            <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-slate-100 cursor-pointer">
                                <input type="checkbox" wire:model="selectedP5Dimensions" value="{{ $dim }}" class="rounded text-teal-600 focus:ring-teal-500">
                                <span class="font-medium text-slate-700">{{ $dim }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- CP & ATP Kemendikdasmen Bank Selector --}}
                @if (count($availableBankTopics) > 0)
                    <div class="rounded-xl border border-teal-200 bg-gradient-to-r from-teal-50/70 to-emerald-50/70 p-4 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label for="selectedBankTopicId" class="text-xs font-bold uppercase tracking-wider text-teal-900 flex items-center gap-1.5">
                                <span>📚</span> Bank CP & ATP Resmi Kemendikdasmen
                            </label>
                            <span class="text-2xs font-semibold text-teal-700 bg-white border border-teal-200 px-2 py-0.5 rounded-full">
                                BSKAP No. 032/H/KR/2024
                            </span>
                        </div>
                        <p class="text-xs text-teal-700">Pilih topik materi pokok untuk mengisi otomatis Tujuan Pembelajaran (TP), Model Pembelajaran, dan Dimensi P5.</p>
                        <select id="selectedBankTopicId" wire:change="selectBankTopic($event.target.value)" class="w-full rounded-lg border-teal-300 bg-white px-3 py-2 text-xs font-medium shadow-xs focus:border-teal-500 focus:ring-teal-500">
                            <option value="">-- Pilih Topik / Bab dari Bank Resmi --</option>
                            @foreach ($availableBankTopics as $bId => $bData)
                                <option value="{{ $bId }}" @selected($selectedBankTopicId === $bId)>
                                    {{ $bData['chapter_title'] }}
                                </option>
                            @endforeach
                        </select>
                        @if (session('bank_selected_info'))
                            <div class="rounded-md bg-emerald-100/80 border border-emerald-300 p-2 text-xs font-medium text-emerald-900 flex items-center gap-1.5">
                                <span>✅</span> {{ session('bank_selected_info') }}
                            </div>
                        @endif
                    </div>
                @endif

                <div>
                    <label for="topic" class="mb-2 block text-sm font-semibold text-slate-700">Topik / Materi Utama <span class="text-rose-500">*</span></label>
                    <textarea id="topic" wire:model="topic" rows="3" maxlength="1500" placeholder="Contoh: Mengungkap Fakta Alam Secara Objektif (Teks LHO)" class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                    @error('topic') <span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="learningObjectives" class="mb-2 block text-sm font-semibold text-slate-700">Tujuan Pembelajaran (TP) / Capaian <span class="text-rose-500">*</span></label>
                    <textarea id="learningObjectives" wire:model="learningObjectives" rows="4" maxlength="2000" placeholder="Contoh: Peserta didik mampu mengevaluasi informasi akurat dan menulis teks LHO berdasarkan observasi lingkungan nyata..." class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                    @error('learningObjectives') <span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="schoolVisionMission" class="mb-2 block text-sm font-semibold text-slate-700">Visi dan Misi Sekolah <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="schoolVisionMission" wire:model="schoolVisionMission" rows="2" maxlength="2000" placeholder="Isi jika ingin menjadi konteks draf..." class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                </div>

                <div>
                    <label for="localContent" class="mb-2 block text-sm font-semibold text-slate-700">Muatan Lokal & Kearifan Daerah <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="localContent" wire:model="localContent" rows="2" maxlength="1000" placeholder="Contoh: Bahasa daerah atau kearifan lokal lingkungan sekitar..." class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                </div>

                <div>
                    <label for="studentNeeds" class="mb-2 block text-sm font-semibold text-slate-700">Kebutuhan Belajar Kelas <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="studentNeeds" wire:model="studentNeeds" rows="2" maxlength="2000" placeholder="Contoh: 5 siswa memerlukan bimbingan visual, 10 siswa siap materi pengayaan..." class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                </div>

                <div>
                    <label for="availableFacilities" class="mb-2 block text-sm font-semibold text-slate-700">Fasilitas / Sarana Pendukung <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="availableFacilities" wire:model="availableFacilities" rows="2" maxlength="1500" placeholder="Contoh: Proyektor LCD, laboratorium komputer, buku paket Kemendikdasmen..." class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                </div>

                <div>
                    <label for="additionalContext" class="mb-2 block text-sm font-semibold text-slate-700">Catatan Tambahan Guru <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="additionalContext" wire:model="additionalContext" rows="2" maxlength="2500" placeholder="Instruksi khusus lainnya untuk asisten AI..." class="w-full rounded-lg border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-teal-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                        <span wire:loading.remove>🚀 Generate Perangkat Ajar AI</span>
                        <span wire:loading class="flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Menghubungkan ke Gemini AI...
                        </span>
                    </button>
                    @error('generation') <span class="mt-2 block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
                </div>
            </form>
        </section>

        <section class="xl:col-span-3 space-y-6">
            @if ($draft)
                {{-- PREVIEW HASIL GENERASI DRAF AI --}}
                @php
                    $docType = (string) data_get($draft, 'document_type', $documentType);
                    $p5Dims = data_get($draft, 'p5_dimensions', []);
                    $learningModel = data_get($draft, 'learning_model', '');
                    $warnings = data_get($draft, 'warnings', []);
                    $references = data_get($draft, 'references', []);
                @endphp

                <div class="space-y-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-bold text-teal-800">
                                    {{ $this->documentTypes()[$docType] ?? 'Perangkat Pembelajaran' }}
                                </span>
                                @if ($learningModel)
                                    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-800">💡 {{ $learningModel }}</span>
                                @endif
                            </div>
                            <h2 class="mt-2 text-xl font-bold text-slate-900">{{ data_get($draft, 'title', 'Draf Perangkat Pembelajaran') }}</h2>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ data_get($draft, 'summary', '') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                            @if ($savedDraftId)
                                <button type="button" wire:click="openDuplicateModal" class="inline-flex items-center gap-1.5 rounded-lg bg-purple-50 border border-purple-300 px-3 py-1.5 text-xs font-bold text-purple-800 shadow-2xs hover:bg-purple-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                                    Duplikasi ke Kelas Paralel
                                </button>
                                <a href="{{ route('guru.learning-assistant.print', $savedDraftId) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-50 border border-teal-300 px-3 py-1.5 text-xs font-bold text-teal-800 shadow-2xs hover:bg-teal-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    Cetak PDF Resmi
                                </a>
                                <a href="{{ route('guru.learning-assistant.export-word', $savedDraftId) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 border border-indigo-300 px-3 py-1.5 text-xs font-bold text-indigo-800 shadow-2xs hover:bg-indigo-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Unduh Word
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Dimensi P5 --}}
                    @if (is_array($p5Dims) && count($p5Dims) > 0)
                        <div class="rounded-lg border border-teal-100 bg-teal-50/50 p-3.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-teal-900 mb-2">🇮🇩 Profil Pelajar Pancasila (P5) Terintegrasi</h3>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($p5Dims as $p5)
                                    <span class="rounded-md bg-white border border-teal-200 px-2.5 py-1 text-xs font-semibold text-teal-800 shadow-2xs">
                                        ✓ {{ $p5 }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Status Banner --}}
                    <div class="flex flex-col gap-3 rounded-lg border border-teal-100 bg-teal-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-teal-900">
                            @if ($savedDraftId && $savedDraftStatus === 'approved')
                                <p class="font-semibold text-emerald-800">✅ Draf ini sudah disetujui resmi.</p>
                                <p class="mt-0.5 text-xs text-emerald-700">Draf siap dicetak ber-Kop Surat, diekspor ke Word, atau disinkronkan ke Jurnal & CBT.</p>
                            @elseif ($savedDraftId)
                                <p class="font-semibold text-teal-900">💾 Draf tersimpan sebagai versi {{ collect($recentDrafts)->firstWhere('id', $savedDraftId)['version'] ?? '' }}.</p>
                                <p class="mt-0.5 text-xs text-teal-700">Periksa kembali atau beri persetujuan resmi.</p>
                            @else
                                <p class="font-semibold text-amber-900">⚠️ Draf baru hasil AI (belum tersimpan).</p>
                                <p class="mt-0.5 text-xs text-amber-700">Simpan draf untuk mengaktifkan fitur cetak ber-Kop resmi dan ekspor Word.</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if (! $savedDraftId)
                                <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft" class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60">
                                    <span wire:loading.remove wire:target="saveDraft">Simpan draf</span>
                                    <span wire:loading wire:target="saveDraft">Menyimpan...</span>
                                </button>
                            @elseif ($savedDraftStatus === 'draft')
                                <button type="button" wire:click="approveDraft" wire:loading.attr="disabled" wire:target="approveDraft" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                                    <span wire:loading.remove wire:target="approveDraft">Setujui draf</span>
                                    <span wire:loading wire:target="approveDraft">Menyetujui...</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- 1-Click Synchronization Panel --}}
                    <div class="rounded-xl border border-indigo-100 bg-gradient-to-r from-indigo-50/60 to-purple-50/60 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-900 flex items-center gap-1.5">
                                    <span>⚡</span> 1-Click Sinkronisasi Akademik
                                </h3>
                                <p class="text-xs text-indigo-700 mt-0.5">Otomatiskan pengisian agenda jurnal mengajar dan pembuatan bank soal CBT dari perangkat ajar ini.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" wire:click="syncToTeachingJournal" wire:loading.attr="disabled" wire:target="syncToTeachingJournal" class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-indigo-200 px-3 py-1.5 text-xs font-bold text-indigo-800 shadow-2xs hover:bg-indigo-50 hover:border-indigo-300 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    <span wire:loading.remove wire:target="syncToTeachingJournal">Sinkron ke Jurnal KBM</span>
                                    <span wire:loading wire:target="syncToTeachingJournal">Menyinkronkan...</span>
                                </button>
                                <button type="button" wire:click="syncToQuestionBank" wire:loading.attr="disabled" wire:target="syncToQuestionBank" class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-purple-200 px-3 py-1.5 text-xs font-bold text-purple-800 shadow-2xs hover:bg-purple-50 hover:border-purple-300 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    <span wire:loading.remove wire:target="syncToQuestionBank">Ekspor ke CBT Bank Soal</span>
                                    <span wire:loading wire:target="syncToQuestionBank">Mengekspor...</span>
                                </button>
                            </div>
                        </div>
                        @if (session('sync_journal_success'))
                            <div class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 p-2.5 text-xs text-emerald-800 flex items-center justify-between">
                                <span>✅ {{ session('sync_journal_success') }}</span>
                                <a href="{{ route('guru.journals') }}" class="font-bold underline hover:text-emerald-950">Lihat Jurnal Mengajar →</a>
                            </div>
                        @endif
                        @if (session('sync_cbt_success'))
                            <div class="mt-3 rounded-lg border border-purple-200 bg-purple-50 p-2.5 text-xs text-purple-800 flex items-center justify-between">
                                <span>🎯 {{ session('sync_cbt_success') }}</span>
                                <a href="{{ route('guru.exams') }}" class="font-bold underline hover:text-purple-950">Buka CBT Bank Soal →</a>
                            </div>
                        @endif
                    </div>

                    {{-- DOKUMEN 1: MODUL AJAR (RPP+) --}}
                    @if ($docType === 'modul_ajar')
                        @php
                            $meaningful = data_get($draft, 'meaningful_understanding');
                            $inquiries = data_get($draft, 'inquiry_questions', []);
                            $objectives = data_get($draft, 'learning_objectives', []);
                            $activities = data_get($draft, 'activities', []);
                            $worksheet = data_get($draft, 'student_worksheet', []);
                            $assessment = data_get($draft, 'assessment', []);
                            $rubrics = data_get($draft, 'assessment_rubric', []);
                        @endphp

                        @if ($meaningful || (is_array($inquiries) && count($inquiries) > 0))
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if ($meaningful)
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                        <h3 class="mb-2 text-sm font-bold text-slate-800">💡 Pemahaman Bermakna</h3>
                                        <p class="text-sm leading-relaxed text-slate-700">{!! \App\Support\SafeHtml::formatHumanText($meaningful) !!}</p>
                                    </div>
                                @endif
                                @if (is_array($inquiries) && count($inquiries) > 0)
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                        <h3 class="mb-2 text-sm font-bold text-slate-800">❓ Pertanyaan Pemantik</h3>
                                        <ul class="list-disc space-y-1 pl-5 text-sm text-slate-700">
                                            @foreach ($inquiries as $inquiry)
                                                <li>{!! \App\Support\SafeHtml::formatHumanText($inquiry) !!}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div>
                            <h3 class="mb-2 text-sm font-bold text-slate-800">Tujuan Pembelajaran (TP)</h3>
                            <ul class="list-disc space-y-1 pl-5 text-sm text-slate-700">
                                @forelse (is_array($objectives) ? $objectives : [] as $objective)
                                    <li>{!! \App\Support\SafeHtml::formatHumanText($objective) !!}</li>
                                @empty
                                    <li class="text-slate-500">[Belum ditentukan]</li>
                                @endforelse
                            </ul>
                        </div>

                        <div>
                            <h3 class="mb-2 text-sm font-bold text-slate-800">Rangkaian Aktivitas KBM</h3>
                            <div class="overflow-x-auto rounded-lg border border-slate-200">
                                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2 font-semibold">Tahap</th>
                                            <th class="px-3 py-2 font-semibold">Waktu</th>
                                            <th class="px-3 py-2 font-semibold">Aktivitas & Peran</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @forelse (is_array($activities) ? $activities : [] as $activity)
                                            <tr class="align-top">
                                                <td class="px-3 py-3 font-semibold text-slate-800">{{ data_get($activity, 'stage', 'Kegiatan') }}</td>
                                                <td class="whitespace-nowrap px-3 py-3 text-slate-600">{{ data_get($activity, 'duration_minutes', 0) }} menit</td>
                                                <td class="px-3 py-3 text-slate-700">
                                                    <p>{!! \App\Support\SafeHtml::formatHumanText(data_get($activity, 'activity', '')) !!}</p>
                                                    @if(data_get($activity, 'teacher_role') || data_get($activity, 'student_role'))
                                                    <div class="mt-1.5 text-xs text-slate-500 grid grid-cols-1 sm:grid-cols-2 gap-1 bg-slate-50 p-2 rounded">
                                                        <div><span class="font-semibold text-slate-700">Guru:</span> {!! \App\Support\SafeHtml::formatHumanText(data_get($activity, 'teacher_role', '-')) !!}</div>
                                                        <div><span class="font-semibold text-slate-700">Murid:</span> {!! \App\Support\SafeHtml::formatHumanText(data_get($activity, 'student_role', '-')) !!}</div>
                                                    </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="px-3 py-4 text-center text-slate-500">Belum ada aktivitas.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- LKPD Preview --}}
                        @if (!empty($worksheet))
                            <div class="rounded-lg border border-teal-200 bg-teal-50/40 p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="rounded bg-teal-600 px-2 py-0.5 text-[11px] font-bold text-white uppercase">LKPD</span>
                                    <h3 class="text-sm font-bold text-slate-900">{{ data_get($worksheet, 'title', 'Lembar Kerja Peserta Didik') }}</h3>
                                </div>
                                <p class="text-xs text-slate-600 mb-3 italic">Petunjuk: {!! \App\Support\SafeHtml::formatHumanText(data_get($worksheet, 'instructions', '-')) !!}</p>
                                <ol class="list-decimal space-y-1.5 pl-5 text-sm text-slate-800">
                                    @foreach (data_get($worksheet, 'tasks', []) as $task)
                                        <li>{!! \App\Support\SafeHtml::formatHumanText($task) !!}</li>
                                    @endforeach
                                </ol>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <h3 class="mb-2 text-sm font-bold text-slate-800">Instrumen Asesmen</h3>
                                <dl class="space-y-2 text-sm">
                                    <div><dt class="font-semibold text-slate-600">Diagnostik</dt><dd class="text-slate-700">{!! \App\Support\SafeHtml::formatHumanText(data_get($assessment, 'diagnostic', '-')) !!}</dd></div>
                                    <div><dt class="font-semibold text-slate-600">Formatif</dt><dd class="text-slate-700">{!! \App\Support\SafeHtml::formatHumanText(data_get($assessment, 'formative', '-')) !!}</dd></div>
                                    <div><dt class="font-semibold text-slate-600">Sumatif</dt><dd class="text-slate-700">{!! \App\Support\SafeHtml::formatHumanText(data_get($assessment, 'summative', '-')) !!}</dd></div>
                                </dl>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <h3 class="mb-2 text-sm font-bold text-slate-800">Diferensiasi Pembelajaran</h3>
                                <p class="text-sm leading-relaxed text-slate-700">{!! \App\Support\SafeHtml::formatHumanText(data_get($draft, 'differentiation', '-')) !!}</p>
                            </div>
                        </div>

                    {{-- DOKUMEN 2: ALUR TUJUAN PEMBELAJARAN (ATP) --}}
                    @elseif ($docType === 'atp')
                        <div class="space-y-4">
                            <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-4">
                                <h3 class="text-sm font-bold text-blue-900 mb-1">🎯 Capaian Pembelajaran (CP) Umum</h3>
                                <p class="text-xs text-slate-700 leading-relaxed">{!! \App\Support\SafeHtml::formatHumanText(data_get($draft, 'cp_general', '-')) !!}</p>
                            </div>

                            @php $cpElements = data_get($draft, 'cp_elements', []); @endphp
                            @if(is_array($cpElements) && count($cpElements) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($cpElements as $elem)
                                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                            <span class="text-xs font-bold text-teal-800">Elemen: {{ data_get($elem, 'element_name') }}</span>
                                            <p class="text-xs text-slate-600 mt-1">{!! \App\Support\SafeHtml::formatHumanText(data_get($elem, 'cp_statement')) !!}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div>
                                <h3 class="text-sm font-bold text-slate-800 mb-2">Matriks Alur Tujuan Pembelajaran (ATP Flow)</h3>
                                <div class="overflow-x-auto rounded-lg border border-slate-200">
                                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                                        <thead class="bg-slate-50 font-bold text-slate-700 uppercase">
                                            <tr>
                                                <th class="px-3 py-2.5">No</th>
                                                <th class="px-3 py-2.5">Bab / Topik</th>
                                                <th class="px-3 py-2.5">Tujuan Pembelajaran</th>
                                                <th class="px-3 py-2.5">Indikator</th>
                                                <th class="px-3 py-2.5">JP</th>
                                                <th class="px-3 py-2.5">Teknik Asesmen</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            @foreach(data_get($draft, 'atp_flow', []) as $flow)
                                                <tr>
                                                    <td class="px-3 py-2.5 font-bold text-teal-700">{{ data_get($flow, 'sequence_number') }}</td>
                                                    <td class="px-3 py-2.5 font-semibold text-slate-900">{{ data_get($flow, 'chapter') }}<div class="text-2xs text-slate-500">{{ data_get($flow, 'topic') }}</div></td>
                                                    <td class="px-3 py-2.5 text-slate-700">{!! \App\Support\SafeHtml::formatHumanText(data_get($flow, 'learning_objectives')) !!}</td>
                                                    <td class="px-3 py-2.5 text-slate-600">{!! \App\Support\SafeHtml::formatHumanText(data_get($flow, 'indicators')) !!}</td>
                                                    <td class="px-3 py-2.5 font-bold text-slate-800 whitespace-nowrap">{{ data_get($flow, 'suggested_duration_jp') }}</td>
                                                    <td class="px-3 py-2.5 text-slate-600">{{ data_get($flow, 'assessment_technique', '-') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    {{-- DOKUMEN 3: PROGRAM TAHUNAN & SEMESTER (PROTA & PROSEM) --}}
                    @elseif ($docType === 'prota_prosem')
                        <div class="space-y-4">
                            <div class="grid grid-cols-3 gap-3">
                                <div class="rounded-lg bg-teal-50 border border-teal-200 p-3 text-center">
                                    <span class="text-2xs font-bold text-teal-700 uppercase">Pekan Ganjil</span>
                                    <p class="text-lg font-black text-teal-900">{{ data_get($draft, 'total_effective_weeks_odd', 18) }} Pekan</p>
                                </div>
                                <div class="rounded-lg bg-blue-50 border border-blue-200 p-3 text-center">
                                    <span class="text-2xs font-bold text-blue-700 uppercase">Pekan Genap</span>
                                    <p class="text-lg font-black text-blue-900">{{ data_get($draft, 'total_effective_weeks_even', 16) }} Pekan</p>
                                </div>
                                <div class="rounded-lg bg-purple-50 border border-purple-200 p-3 text-center">
                                    <span class="text-2xs font-bold text-purple-700 uppercase">Total JP Setahun</span>
                                    <p class="text-lg font-black text-purple-900">{{ data_get($draft, 'total_jp_year', '-') }}</p>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-slate-800 mb-2">Distribusi Program Tahunan (Prota)</h3>
                                <div class="overflow-x-auto rounded-lg border border-slate-200">
                                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                                        <thead class="bg-slate-50 font-bold text-slate-700">
                                            <tr>
                                                <th class="px-3 py-2">Bab</th>
                                                <th class="px-3 py-2">Materi / Capaian</th>
                                                <th class="px-3 py-2">Semester</th>
                                                <th class="px-3 py-2 text-right">Alokasi JP</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            @foreach(data_get($draft, 'prota_distribution', []) as $prota)
                                                <tr>
                                                    <td class="px-3 py-2 font-bold text-slate-800">{{ data_get($prota, 'chapter_number') }}</td>
                                                    <td class="px-3 py-2 font-semibold text-slate-900">{{ data_get($prota, 'chapter_title') }}<div class="text-2xs text-slate-500">{!! \App\Support\SafeHtml::formatHumanText(data_get($prota, 'learning_objectives')) !!}</div></td>
                                                    <td class="px-3 py-2 font-bold text-teal-700">{{ data_get($prota, 'semester') }}</td>
                                                    <td class="px-3 py-2 text-right font-bold text-slate-900">{{ data_get($prota, 'allocated_jp') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    {{-- DOKUMEN 4: BAHAN AJAR & MATERI KONSEP --}}
                    @elseif ($docType === 'bahan_ajar')
                        <div class="space-y-4">
                            <div class="rounded-lg border border-teal-200 bg-teal-50/60 p-4">
                                <h3 class="text-sm font-bold text-teal-900 mb-1">📖 Ringkasan Konsep Esensial</h3>
                                <p class="text-xs text-slate-700 leading-relaxed">{!! \App\Support\SafeHtml::formatHumanText(data_get($draft, 'concept_summary', '-')) !!}</p>
                            </div>

                            @if (data_get($draft, 'conceptual_analogy'))
                                <div class="rounded-lg border border-amber-200 bg-amber-50/60 p-3.5">
                                    <h4 class="text-xs font-bold text-amber-900 uppercase">💡 Skema / Analogi Memori</h4>
                                    <p class="text-xs text-slate-700 mt-1">{!! \App\Support\SafeHtml::formatHumanText(data_get($draft, 'conceptual_analogy')) !!}</p>
                                </div>
                            @endif

                            <div class="space-y-3">
                                <h3 class="text-sm font-bold text-slate-800">Uraian Materi Pembelajaran</h3>
                                @foreach(data_get($draft, 'key_sections', []) as $sec)
                                    <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                        <h4 class="text-xs font-bold text-indigo-900">{{ data_get($sec, 'subtitle') }}</h4>
                                        <p class="text-xs text-slate-700 leading-relaxed">{!! \App\Support\SafeHtml::formatHumanText(data_get($sec, 'content')) !!}</p>
                                        <div class="rounded bg-slate-50 border border-slate-200 p-2.5 text-2xs space-y-1">
                                            <div class="font-bold text-emerald-800">📌 Poin Kunci: {!! \App\Support\SafeHtml::formatHumanText(data_get($sec, 'key_takeaway')) !!}</div>
                                            @if(data_get($sec, 'practical_example'))
                                                <div class="text-slate-600">🔍 Contoh Nyata: {!! \App\Support\SafeHtml::formatHumanText(data_get($sec, 'practical_example')) !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    {{-- DOKUMEN 5: LKPD 3 TINGKAT BERDIFERENSIASI --}}
                    @elseif ($docType === 'lkpd_bertingkat')
                        <div class="space-y-4">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3.5">
                                <h3 class="text-xs font-bold text-slate-800 uppercase">Petunjuk Umum LKPD</h3>
                                <p class="text-xs text-slate-600 mt-1">{!! \App\Support\SafeHtml::formatHumanText(data_get($draft, 'general_instructions')) !!}</p>
                            </div>

                            {{-- 3 Level Cards --}}
                            <div class="space-y-3">
                                {{-- Level 1 --}}
                                <div class="rounded-xl border border-emerald-300 bg-emerald-50/40 p-4 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="rounded bg-emerald-600 px-2 py-0.5 text-2xs font-bold text-white uppercase">Level 1: Perlu Bimbingan (Scaffolding)</span>
                                        <span class="text-2xs text-emerald-800 font-semibold">{{ data_get($draft, 'level_1_scaffolding.target_group') }}</span>
                                    </div>
                                    <p class="text-xs text-emerald-950 font-medium">Panduan: {!! \App\Support\SafeHtml::formatHumanText(data_get($draft, 'level_1_scaffolding.guidance_steps')) !!}</p>
                                    <ol class="list-decimal pl-5 text-xs text-slate-800 space-y-1">
                                        @foreach(data_get($draft, 'level_1_scaffolding.tasks', []) as $task)
                                            <li>{!! \App\Support\SafeHtml::formatHumanText($task) !!}</li>
                                        @endforeach
                                    </ol>
                                    @if(data_get($draft, 'level_1_scaffolding.hints'))
                                        <p class="text-2xs text-slate-500 italic bg-white p-2 rounded border border-emerald-200">💡 Petunjuk Bantu: {!! \App\Support\SafeHtml::formatHumanText(data_get($draft, 'level_1_scaffolding.hints')) !!}</p>
                                    @endif
                                </div>

                                {{-- Level 2 --}}
                                <div class="rounded-xl border border-blue-300 bg-blue-50/40 p-4 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="rounded bg-blue-600 px-2 py-0.5 text-2xs font-bold text-white uppercase">Level 2: Reguler (Cakap)</span>
                                        <span class="text-2xs text-blue-800 font-semibold">{{ data_get($draft, 'level_2_regular.target_group') }}</span>
                                    </div>
                                    <p class="text-xs text-blue-950 font-medium">Instruksi: {!! \App\Support\SafeHtml::formatHumanText(data_get($draft, 'level_2_regular.instructions')) !!}</p>
                                    <ol class="list-decimal pl-5 text-xs text-slate-800 space-y-1">
                                        @foreach(data_get($draft, 'level_2_regular.core_tasks', []) as $task)
                                            <li>{!! \App\Support\SafeHtml::formatHumanText($task) !!}</li>
                                        @endforeach
                                    </ol>
                                </div>

                                {{-- Level 3 --}}
                                <div class="rounded-xl border border-purple-300 bg-purple-50/40 p-4 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="rounded bg-purple-600 px-2 py-0.5 text-2xs font-bold text-white uppercase">Level 3: Pengayaan / HOTS (Mahir)</span>
                                        <span class="text-2xs text-purple-800 font-semibold">{{ data_get($draft, 'level_3_advanced.target_group') }}</span>
                                    </div>
                                    <p class="text-xs text-purple-950 font-medium">Tantangan: {!! \App\Support\SafeHtml::formatHumanText(data_get($draft, 'level_3_advanced.challenge_case')) !!}</p>
                                    <ol class="list-decimal pl-5 text-xs text-slate-800 space-y-1">
                                        @foreach(data_get($draft, 'level_3_advanced.hots_tasks', []) as $task)
                                            <li>{!! \App\Support\SafeHtml::formatHumanText($task) !!}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            </div>
                        </div>

                    {{-- DOKUMEN 6: MODUL PROJEK P5 --}}
                    @elseif ($docType === 'modul_p5')
                        <div class="space-y-4">
                            <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-purple-600 px-2.5 py-0.5 text-2xs font-bold text-white uppercase">🌟 Tema: {{ data_get($draft, 'p5_theme') }}</span>
                                    <span class="text-xs font-bold text-purple-900">Topik: {{ data_get($draft, 'project_topic') }}</span>
                                </div>
                                <p class="text-xs text-slate-700 mt-2 leading-relaxed">{!! \App\Support\SafeHtml::formatHumanText(data_get($draft, 'project_background')) !!}</p>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-slate-800 mb-2">4 Tahapan Alur Aktivitas Projek</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach(data_get($draft, 'project_stages', []) as $stg)
                                        <div class="rounded-lg border border-slate-200 bg-white p-3.5 space-y-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-bold text-indigo-900">{{ data_get($stg, 'stage_name') }}</span>
                                                <span class="text-2xs font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded">{{ data_get($stg, 'duration_jp') }}</span>
                                            </div>
                                            <p class="text-xs text-slate-700">{!! \App\Support\SafeHtml::formatHumanText(data_get($stg, 'activities')) !!}</p>
                                            <div class="text-2xs font-semibold text-emerald-700 pt-1">Artifact: {!! \App\Support\SafeHtml::formatHumanText(data_get($stg, 'output_artifact')) !!}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    {{-- DOKUMEN 7: INSTRUMEN ASESMEN & RUBRIK KKTP --}}
                    @elseif ($docType === 'asesmen_kktp')
                        <div class="space-y-4">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3.5">
                                <h3 class="text-xs font-bold text-slate-800 uppercase">Target Kompetensi Asesmen</h3>
                                <p class="text-xs text-slate-700 mt-1">{!! \App\Support\SafeHtml::formatHumanText(data_get($draft, 'target_competency')) !!}</p>
                            </div>

                            {{-- Soal Sumatif AKM --}}
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 mb-2">Naskah Butir Soal Sumatif (AKM / HOTS)</h3>
                                <div class="space-y-3">
                                    @foreach(data_get($draft, 'summative_assessment.questions', []) as $q)
                                        <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-teal-800">Soal No. {{ data_get($q, 'number') }} ({{ data_get($q, 'question_type') }})</span>
                                                <span class="text-2xs font-bold bg-slate-100 px-2 py-0.5 rounded text-slate-700">Bobot: {{ data_get($q, 'scoring_points') }} Poin</span>
                                            </div>
                                            @if(data_get($q, 'stimulus_text'))
                                                <div class="text-2xs text-slate-600 bg-slate-50 p-2.5 rounded border border-slate-200 italic">
                                                    {!! \App\Support\SafeHtml::formatHumanText(data_get($q, 'stimulus_text')) !!}
                                                </div>
                                            @endif
                                            <p class="text-xs font-medium text-slate-900">{!! \App\Support\SafeHtml::formatHumanText(data_get($q, 'question_text')) !!}</p>
                                            @if(is_array(data_get($q, 'options')) && count(data_get($q, 'options')) > 0)
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pl-2 text-xs text-slate-700">
                                                    @foreach(data_get($q, 'options') as $opt)
                                                        <div>• {!! \App\Support\SafeHtml::formatHumanText($opt) !!}</div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div class="rounded bg-emerald-50 border border-emerald-200 p-2 text-2xs font-medium text-emerald-900">
                                                <strong>Kunci/Pedoman:</strong> {!! \App\Support\SafeHtml::formatHumanText(data_get($q, 'correct_answer')) !!} — <em>{!! \App\Support\SafeHtml::formatHumanText(data_get($q, 'explanation')) !!}</em>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Matriks KKTP --}}
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 mb-2">Rubrik Kriteria Ketercapaian Tujuan Pembelajaran (KKTP)</h3>
                                <div class="overflow-x-auto rounded-lg border border-slate-200">
                                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                                        <thead class="bg-slate-50 font-bold text-slate-700">
                                            <tr>
                                                <th class="px-3 py-2">Aspek</th>
                                                <th class="px-3 py-2 text-rose-700">Perlu Bimbingan</th>
                                                <th class="px-3 py-2 text-amber-700">Cukup</th>
                                                <th class="px-3 py-2 text-blue-700">Baik</th>
                                                <th class="px-3 py-2 text-emerald-700">Sangat Baik</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            @foreach(data_get($draft, 'kktp_rubric', []) as $kktp)
                                                <tr>
                                                    <td class="px-3 py-2 font-bold text-slate-900">{{ data_get($kktp, 'aspect') }}</td>
                                                    <td class="px-3 py-2 text-rose-800">{!! \App\Support\SafeHtml::formatHumanText(data_get($kktp, 'perlu_bimbingan')) !!}</td>
                                                    <td class="px-3 py-2 text-amber-800">{!! \App\Support\SafeHtml::formatHumanText(data_get($kktp, 'cukup')) !!}</td>
                                                    <td class="px-3 py-2 text-blue-800">{!! \App\Support\SafeHtml::formatHumanText(data_get($kktp, 'baik')) !!}</td>
                                                    <td class="px-3 py-2 text-emerald-800">{!! \App\Support\SafeHtml::formatHumanText(data_get($kktp, 'sangat_baik')) !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Catatan & Referensi Bersama --}}
                    @if (is_array($warnings) && count($warnings) > 0)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                            <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-amber-900">⚠️ Hal yang Perlu Dikonfirmasi Guru</h3>
                            <ul class="list-disc space-y-1 pl-5 text-xs text-amber-800">
                                @foreach ($warnings as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (is_array($references) && count($references) > 0)
                        <div>
                            <h3 class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">📚 Referensi & Sumber Acuan</h3>
                            <ul class="list-disc space-y-1 pl-5 text-xs text-slate-600">
                                @foreach ($references as $reference)
                                    <li>{{ $reference }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50/60 p-8 text-center text-slate-500">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-600 text-xl font-bold">
                        AI
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800">Belum Ada Draf Perangkat Ajar Aktif</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                        Pilih jenis dokumen di formulir kiri dan klik <strong>Generate Perangkat Ajar AI</strong> untuk menghasilkan draf otomatis berbasis Gemini AI.
                    </p>
                </div>
            @endif

            {{-- Riwayat Draf Tersimpan --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-3">🕒 Riwayat Draf Perangkat Ajar Terakhir</h3>
                @if (count($recentDrafts) > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach ($recentDrafts as $rd)
                            <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                                <div>
                                    <span class="font-semibold text-slate-900">{{ $rd['title'] }}</span>
                                    <div class="text-2xs text-slate-500">
                                        {{ $this->documentTypes()[$rd['document_type']] ?? $rd['document_type'] }} • v{{ $rd['version'] }} • {{ $rd['created_at'] }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($rd['status'] === 'approved')
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-2xs">Disetujui</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-2xs">Draf</span>
                                    @endif
                                    <button type="button" wire:click="loadSavedDraft({{ $rd['id'] }})" class="font-bold text-teal-600 hover:text-teal-800">
                                        Buka →
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500">Belum ada riwayat draf tersimpan.</p>
                @endif
            </div>
        </section>
    </div>

    {{-- MODAL DUPLIKASI KE KELAS PARALEL --}}
    @if ($showDuplicateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-purple-100 text-purple-700 font-bold">✨</div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Duplikasi ke Kelas Paralel</h3>
                            <p class="text-xs text-slate-500">Salin perangkat pembelajaran ini ke rombel paralel yang diajar</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDuplicateModal" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">✕</button>
                </div>

                <div class="py-4 space-y-3 text-xs">
                    <p class="text-slate-600">Pilih kelas paralel target pengajaran Anda:</p>
                    @if (count($parallelClassSchedules) > 0)
                        <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                            @foreach ($parallelClassSchedules as $ps)
                                <label class="flex items-center gap-2.5 p-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-purple-50/50 cursor-pointer">
                                    <input type="checkbox" wire:model="selectedTargetScheduleIds" value="{{ $ps['id'] }}" class="rounded text-purple-600 focus:ring-purple-500">
                                    <div class="flex-1">
                                        <div class="font-bold text-slate-800">{{ $ps['classroom_name'] }}</div>
                                        <div class="text-2xs text-slate-500">{{ $ps['day_time'] }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-amber-800">
                            Tidak ditemukan jadwal kelas paralel lain untuk mata pelajaran yang sama pada tahun ajaran ini.
                        </div>
                    @endif
                    @error('duplicate') <span class="text-rose-600 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" wire:click="closeDuplicateModal" class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-bold text-slate-700 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    @if (count($parallelClassSchedules) > 0)
                        <button type="button" wire:click="duplicateToParallelClasses" wire:loading.attr="disabled" class="rounded-lg bg-purple-600 px-5 py-2 font-bold text-white shadow-sm hover:bg-purple-700 transition">
                            Duplikasikan Sekarang
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

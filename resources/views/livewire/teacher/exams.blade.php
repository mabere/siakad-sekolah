<div x-data="{ openBank: @entangle('showBankModal'), openQuestion: @entangle('showQuestionModal'), openExam: @entangle('showExamModal'), openCorrection: @entangle('showCorrectionModal') }">
    <x-slot name="title">Bank Soal & Asesmen Ujian CBT</x-slot>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Bank Soal & Asesmen Ujian CBT</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola paket soal, butir pertanyaan, penjadwalan ujian online, serta koreksi esai.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 mb-6">
        <nav class="-mb-px flex space-x-8 overflow-x-auto scrollbar-none" aria-label="Tabs">
            <button wire:click="switchTab('question_banks')" type="button"
                class="{{ $activeTab === 'question_banks' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                1. Paket Bank Soal
            </button>

            <button wire:click="switchTab('questions_editor')" type="button"
                class="{{ $activeTab === 'questions_editor' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                2. Editor & Butir Soal {{ $selectedBank ? '('.$selectedBank->title.')' : '' }}
            </button>

            <button wire:click="switchTab('exams_list')" type="button"
                class="{{ $activeTab === 'exams_list' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                3. Jadwal & Pengaturan Ujian CBT
            </button>

            <button wire:click="switchTab('submissions')" type="button"
                class="{{ $activeTab === 'submissions' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                4. Koreksi & Penilaian CBT Siswa
            </button>
        </nav>
    </div>

    <!-- TAB 1: BANK SOAL -->
    @if($activeTab === 'question_banks')
        @if(session()->has('bank_success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
                <span>✓ {{ session('bank_success') }}</span>
            </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-bold text-slate-800">Daftar Paket Bank Soal Guru</h2>
            <button wire:click="openBankModal" type="button" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                <span>+ Buat Paket Bank Soal Baru</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            @forelse($questionBanks as $bank)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between hover:shadow-md transition-all">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-[11px] font-extrabold rounded uppercase">
                                {{ $bank->subject?->code ?? 'MAPEL' }}
                            </span>
                            <span class="px-2.5 py-0.5 bg-teal-50 text-teal-800 border border-teal-200 text-xs font-bold rounded-full">
                                Kelas {{ $bank->grade_level }}
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 mb-1 leading-snug">{{ $bank->title }}</h3>
                        <p class="text-xs text-slate-500 mb-4">Kode: <span class="font-mono font-bold text-slate-700">{{ $bank->code ?? '-' }}</span></p>

                        <div class="flex items-center gap-4 text-xs text-slate-600 mb-4 bg-slate-50 p-3 rounded-xl">
                            <div>
                                <span class="font-bold text-slate-900 block text-base">{{ $bank->questions->count() }}</span>
                                <span class="text-[11px] text-slate-500">Butir Soal</span>
                            </div>
                            <div class="border-l border-slate-200 pl-4">
                                <span class="font-bold text-slate-900 block text-base">{{ $bank->subject?->name }}</span>
                                <span class="text-[11px] text-slate-500">Mata Pelajaran</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                        <button wire:click="selectBankForQuestions({{ $bank->id }})" type="button" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition-colors">
                            ✏️ Kelola Soal
                        </button>
                        <div class="flex items-center gap-1">
                            <button wire:click="openBankModal({{ $bank->id }})" type="button" class="p-2 text-slate-500 hover:text-teal-600 rounded-lg hover:bg-slate-100">
                                ⚙️
                            </button>
                            <button wire:click="deleteBank({{ $bank->id }})" wire:confirm="Hapus Paket Bank Soal ini beserta seluruh butir soal di dalamnya?" type="button" class="p-2 text-slate-500 hover:text-rose-600 rounded-lg hover:bg-slate-100">
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm">
                    <p class="text-slate-500 text-sm font-semibold">Belum ada paket bank soal. Klik tombol di atas untuk membuat paket baru.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- TAB 2: QUESTIONS EDITOR -->
    @if($activeTab === 'questions_editor')
        @if(!$selectedBank)
            <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm mb-6">
                <p class="text-slate-600 text-sm font-semibold mb-3">Silakan pilih Paket Bank Soal terlebih dahulu pada Tab 1.</p>
                <button wire:click="switchTab('question_banks')" type="button" class="px-4 py-2 bg-teal-600 text-white text-xs font-bold rounded-xl">
                    ← Kembali ke Daftar Bank Soal
                </button>
            </div>
        @else
            @if(session()->has('question_success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
                    <span>✓ {{ session('question_success') }}</span>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <span class="px-2.5 py-0.5 bg-teal-100 text-teal-800 text-xs font-bold rounded">
                            {{ $selectedBank->subject?->name }} (Kelas {{ $selectedBank->grade_level }})
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $selectedBank->title }}</h2>
                    </div>

                    <button wire:click="openQuestionModal" type="button" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                        <span>+ Tambah Butir Soal Baru</span>
                    </button>
                </div>

                <div class="space-y-4">
                    @forelse($questions as $idx => $q)
                        <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-slate-50 transition-all flex flex-col md:flex-row justify-between items-start gap-4">
                            <div class="space-y-2 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 bg-indigo-600 text-white font-black text-xs rounded-md">
                                        Soal #{{ $idx + 1 }}
                                    </span>
                                    <span class="px-2.5 py-0.5 text-xs font-bold rounded uppercase {{ $q->type === 'pg' ? 'bg-teal-100 text-teal-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ strtoupper($q->type) }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-semibold">
                                        {{ $q->type === 'essay' ? 'Bobot Esai: '.$q->score_weight.' Poin' : 'Bobot Terdistribusi Otomatis' }}
                                    </span>
                                </div>

                                <div class="text-sm font-semibold text-slate-900 leading-relaxed">
                                    {!! nl2br(e($q->question_text)) !!}
                                </div>

                                @if($q->type === 'pg' && !empty($q->options))
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 text-xs">
                                        @foreach($q->options as $optKey => $optVal)
                                            @php
                                                $isCorrect = strtolower($q->correct_answer) === strtolower($optKey);
                                            @endphp
                                            <div class="p-2 rounded-lg border {{ $isCorrect ? 'bg-emerald-100 text-emerald-900 border-emerald-300 font-bold' : 'bg-white text-slate-700 border-slate-200' }}">
                                                <span class="font-black uppercase mr-1">{{ $optKey }}.</span> {{ $optVal }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 self-end md:self-start">
                                <button wire:click="openQuestionModal({{ $q->id }})" type="button" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-lg transition-colors">
                                    Edit
                                </button>
                                <button wire:click="deleteQuestion({{ $q->id }})" wire:confirm="Hapus butir soal ini?" type="button" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold rounded-lg transition-colors border border-rose-200">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-500 text-sm">
                            Belum ada butir soal dalam paket ini. Klik tombol di atas untuk menambah soal.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    @endif

    <!-- TAB 3: EXAMS LIST -->
    @if($activeTab === 'exams_list')
        @if(session()->has('exam_success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
                <span>✓ {{ session('exam_success') }}</span>
            </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-bold text-slate-800">Daftar Jadwal Ujian CBT Online</h2>
            <button wire:click="openExamModal" type="button" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                <span>+ Buat Jadwal Ujian CBT</span>
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Ujian</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Kelas & Mapel</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Durasi & Paket Soal</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($exams as $ex)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900">{{ $ex->title }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-slate-800">Kelas {{ $ex->classroom?->grade_level }} {{ $ex->classroom?->name }}</div>
                                    <div class="text-xs text-slate-500">📚 {{ $ex->subject?->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-bold text-slate-800">⏱️ {{ $ex->duration_minutes }} Menit</div>
                                    <div class="text-xs text-indigo-700 font-semibold">📦 {{ $ex->questionBank?->title }}</div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    @php
                                        $stBadge = [
                                            'Aktif' => 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold',
                                            'Draft' => 'bg-amber-100 text-amber-800 border-amber-300',
                                            'Selesai' => 'bg-slate-100 text-slate-600 border-slate-300',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 text-xs rounded-full border {{ $stBadge[$ex->status] ?? 'bg-slate-100' }}">
                                        {{ $ex->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                    <button wire:click="openExamModal({{ $ex->id }})" type="button" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg mr-1 transition-colors">
                                        Edit
                                    </button>
                                    <button wire:click="deleteExam({{ $ex->id }})" wire:confirm="Hapus jadwal ujian CBT ini?" type="button" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold rounded-lg transition-colors border border-rose-200">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                    Belum ada jadwal ujian CBT yang dikonfigurasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(is_object($exams) && method_exists($exams, 'links'))
                <div class="p-4 border-t border-slate-200">
                    {{ $exams->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- TAB 4: KOREKSI & PENILAIAN CBT SISWA -->
    @if($activeTab === 'submissions')
        @if(session()->has('correction_success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
                <span>✓ {{ session('correction_success') }}</span>
            </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Daftar Lembar Jawaban Ujian Siswa (Submissions)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Periksa pengerjaan siswa, koreksi soal esai per butir, dan update nilai akhir otomatis.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Siswa & Kelas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Ujian & Mapel</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Waktu Submit</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Nilai Ujian Akhir</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi Koreksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($submissions as $sub)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900">{{ $sub->student?->name }}</div>
                                    <div class="text-xs text-slate-500">Kelas {{ $sub->student?->classroom?->grade_level }} {{ $sub->student?->classroom?->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-800">{{ $sub->exam?->title }}</div>
                                    <div class="text-xs text-slate-500">📚 {{ $sub->exam?->subject?->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                    🗓️ {{ \Carbon\Carbon::parse($sub->submitted_at)->translatedFormat('d M Y, H:i') }} WIB
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <span class="px-3.5 py-1.5 bg-indigo-100 text-indigo-900 font-black text-sm rounded-xl border border-indigo-300">
                                        {{ $sub->score }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button wire:click="openCorrectionModal({{ $sub->id }})" type="button" class="px-3.5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl transition-colors shadow-xs">
                                        📝 Periksa & Koreksi Esai
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                    Belum ada siswa yang mengumpulkan pengerjaan ujian CBT.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(is_object($submissions) && method_exists($submissions, 'links'))
                <div class="p-4 border-t border-slate-200">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- MODAL 1: BUAT / EDIT PAKET BANK SOAL -->
    <div x-show="openBank" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="openBank" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openBank = false"></div>

            <div x-show="openBank" x-transition.scale class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full z-10">
                <form wire:submit.prevent="saveBank">
                    <div class="bg-white p-6 sm:p-8 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-lg font-bold text-slate-900">{{ $editingBankId ? 'Edit Paket Bank Soal' : 'Buat Paket Bank Soal Baru' }}</h3>
                            <button type="button" @click="openBank = false" class="text-slate-400 hover:text-slate-600">✖</button>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran *</label>
                             <select wire:model="bankSubjectId" class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code }})</option>
                                @endforeach
                            </select>
                            @error('bankSubjectId') <span class="text-xs text-rose-600 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Judul Paket Bank Soal *</label>
                             <input type="text" wire:model="bankTitle" placeholder="Misal: Bank Soal UH 1 Fisika Dasar" class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            @error('bankTitle') <span class="text-xs text-rose-600 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kode Paket</label>
                                 <input type="text" wire:model="bankCode" placeholder="BS-101" class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500 uppercase font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat Kelas *</label>
                                 <select wire:model="bankGradeLevel" class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                                    <option value="10">Kelas 10</option>
                                    <option value="11">Kelas 11</option>
                                    <option value="12">Kelas 12</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi / Catatan Paket</label>
                             <textarea wire:model="bankDescription" rows="3" placeholder="Catatan standar kompetensi atau kisi-kisi soal..." class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-sm transition-colors">
                            💾 Simpan Paket Bank Soal
                        </button>
                        <button type="button" @click="openBank = false" class="w-full sm:w-auto mt-3 sm:mt-0 px-4 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs rounded-xl transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: BUAT / EDIT BUTIR SOAL -->
    <div x-show="openQuestion" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="openQuestion" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openQuestion = false"></div>

            <div x-show="openQuestion" x-transition.scale class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-10">
                <form wire:submit.prevent="saveQuestion">
                    <div class="bg-white p-6 sm:p-8 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-lg font-bold text-slate-900">{{ $editingQuestionId ? 'Edit Butir Pertanyaan' : 'Tambah Butir Pertanyaan Baru' }}</h3>
                            <button type="button" @click="openQuestion = false" class="text-slate-400 hover:text-slate-600">✖</button>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Soal *</label>
                                 <select wire:model.live="questionType" class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500 font-bold">
                                    <option value="pg">Pilihan Ganda (PG)</option>
                                    <option value="essay">Uraian / Esai</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    {{ $questionType === 'essay' ? 'Bobot Poin Esai (0 - 100) *' : 'Bobot Terdistribusi Otomatis' }}
                                </label>
                                 <input type="number" min="1" max="100" wire:model="scoreWeight" @disabled($questionType === 'pg') class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500 font-bold disabled:bg-slate-100">
                                @if($questionType === 'pg')
                                    <span class="text-[11px] text-slate-500 font-semibold block mt-0.5">Bobot PG dihitung otomatis dari sisa porsi 100 poin.</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Teks Pertanyaan / Soal *</label>
                             <textarea wire:model="questionText" rows="4" placeholder="Tuliskan butir soal lengkap di sini..." class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                            @error('questionText') <span class="text-xs text-rose-600 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <!-- Option Input Fields for PG -->
                        @if($questionType === 'pg')
                            <div class="space-y-3 pt-2 border-t border-slate-100">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Opsi Pilihan Jawaban PG:</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Opsi A *</label>
                                         <input type="text" wire:model="optionA" class="w-full p-2 rounded-lg border-slate-300 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Opsi B *</label>
                                         <input type="text" wire:model="optionB" class="w-full p-2 rounded-lg border-slate-300 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Opsi C *</label>
                                         <input type="text" wire:model="optionC" class="w-full p-2 rounded-lg border-slate-300 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Opsi D *</label>
                                         <input type="text" wire:model="optionD" class="w-full p-2 rounded-lg border-slate-300 text-xs">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Opsi E (Opsional)</label>
                                         <input type="text" wire:model="optionE" class="w-full p-2 rounded-lg border-slate-300 text-xs">
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <label class="block text-xs font-bold text-emerald-800 mb-1">Kunci Jawaban Resmi PG *</label>
                                     <select wire:model="correctAnswer" class="w-full sm:w-44 p-2 rounded-xl border-emerald-400 text-sm font-black text-emerald-900 bg-emerald-50">
                                        <option value="a">Opsi A</option>
                                        <option value="b">Opsi B</option>
                                        <option value="c">Opsi C</option>
                                        <option value="d">Opsi D</option>
                                        <option value="e">Opsi E</option>
                                    </select>
                                </div>
                            </div>
                        @else
                            <div class="pt-2 border-t border-slate-100">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kunci Jawaban Referensi / Solusi Singkat (Guru)</label>
                                 <textarea wire:model="correctAnswer" rows="2" placeholder="Referensi kunci / kata kunci jawaban esai..." class="w-full p-2 rounded-xl border-slate-300 text-xs"></textarea>
                            </div>
                        @endif
                    </div>

                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-sm transition-colors">
                            💾 Simpan Butir Soal
                        </button>
                        <button type="button" @click="openQuestion = false" class="w-full sm:w-auto mt-3 sm:mt-0 px-4 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs rounded-xl transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 3: BUAT / EDIT JADWAL UJIAN CBT -->
    <div x-show="openExam" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="openExam" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openExam = false"></div>

            <div x-show="openExam" x-transition.scale class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full z-10">
                <form wire:submit.prevent="saveExam">
                    <div class="bg-white p-6 sm:p-8 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-lg font-bold text-slate-900">{{ $editingExamId ? 'Edit Pengaturan Ujian CBT' : 'Buat Jadwal Ujian CBT Baru' }}</h3>
                            <button type="button" @click="openExam = false" class="text-slate-400 hover:text-slate-600">✖</button>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Judul Ujian / Asesmen *</label>
                             <input type="text" wire:model="examTitle" placeholder="Misal: Ujian Akhir Semester (UAS) Fisika" class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            @error('examTitle') <span class="text-xs text-rose-600 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran *</label>
                                 <select wire:model="examSubjectId" class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                                    @foreach($subjects as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Target Kelas *</label>
                                 <select wire:model="examClassroomId" class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                                    @foreach($classrooms as $c)
                                        <option value="{{ $c->id }}">Kelas {{ $c->grade_level }} {{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Paket Bank Soal *</label>
                                 <select wire:model="examBankId" class="w-full p-2 rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500 font-semibold text-indigo-900">
                                    @foreach($questionBanks as $qb)
                                        <option value="{{ $qb->id }}">{{ $qb->title }} ({{ $qb->questions_count }} Soal)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Durasi Pengerjaan *</label>
                                <div class="flex items-center gap-2">
                                     <input type="number" min="5" max="300" wire:model="examDurationMinutes" class="w-full p-2 rounded-xl border-slate-300 text-sm font-bold">
                                    <span class="text-xs text-slate-500 font-bold">Menit</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Waktu Mulai Ujian</label>
                                 <input type="datetime-local" wire:model="examStartTime" class="w-full p-2 rounded-xl border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Waktu Selesai Ujian</label>
                                 <input type="datetime-local" wire:model="examEndTime" class="w-full p-2 rounded-xl border-slate-300 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 items-center pt-2">
                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="examRandomize" class="rounded text-teal-600 focus:ring-teal-500">
                                    <span class="text-xs font-bold text-slate-700">Acak Urutan Soal CBT</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Akses Ujian *</label>
                                 <select wire:model="examStatus" class="w-full p-2 rounded-xl border-slate-300 text-xs font-bold">
                                    <option value="Draft">Draft (Belum Tampil)</option>
                                    <option value="Aktif">Aktif (Dapat Dikerjakan Siswa)</option>
                                    <option value="Selesai">Selesai (Ditutup)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-sm transition-colors">
                            💾 Simpan Jadwal Ujian
                        </button>
                        <button type="button" @click="openExam = false" class="w-full sm:w-auto mt-3 sm:mt-0 px-4 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs rounded-xl transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 4: FORM KOREKSI PER BUTIR ESAI & PENILAIAN KOMBINASI CBT SISWA -->
    <div x-show="openCorrection" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="openCorrection" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openCorrection = false"></div>

            <div x-show="openCorrection" x-transition.scale class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-10">
                @if($editingSubmission)
                    <form wire:submit.prevent="saveCorrection">
                        <div class="bg-white p-6 sm:p-8">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                                <div>
                                    <span class="px-2.5 py-0.5 bg-teal-100 text-teal-800 text-[11px] font-bold rounded">
                                        Pemeriksaan & Koreksi CBT (Standar Total 100 Poin)
                                    </span>
                                    <h3 class="text-xl font-bold text-slate-900 mt-1">{{ $editingSubmission->student?->name }} (Kelas {{ $editingSubmission->student?->classroom?->name }})</h3>
                                    <p class="text-xs text-slate-500">Ujian: {{ $editingSubmission->exam?->title }}</p>
                                </div>
                                <button type="button" @click="openCorrection = false" class="text-slate-400 hover:text-slate-600">
                                    ✖
                                </button>
                            </div>

                            <!-- Review & Scoring Per Question -->
                            <div class="space-y-4 max-h-96 overflow-y-auto pr-1 mb-6 border-b border-slate-100 pb-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600">Lembar Jawaban & Koreksi Per Butir Soal:</h4>
                                @php
                                    $subAns = $editingSubmission->answers ?? [];
                                @endphp
                                @if($editingSubmission->exam && $editingSubmission->exam->questionBank)
                                    @foreach($editingSubmission->exam->questionBank->questions as $idx => $q)
                                        @php
                                            $userKey = strtolower($subAns[$q->id] ?? '-');
                                            $correctKey = strtolower($q->correct_answer ?? '-');
                                            $isPg = in_array(strtolower($q->type ?? 'pg'), ['pg', 'pilihan_ganda']);
                                            $isRight = $isPg && ($userKey === $correctKey);
                                        @endphp
                                        <div class="p-4 rounded-xl border {{ $isPg ? ($isRight ? 'bg-emerald-50/70 border-emerald-200' : 'bg-rose-50/70 border-rose-200') : 'bg-purple-50/70 border-purple-200' }}">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-slate-800">Soal #{{ $idx + 1 }} ({{ strtoupper($q->type ?? 'PG') }})</span>
                                                    <span class="text-[11px] text-slate-500 font-semibold">
                                                        {{ $isPg ? 'Bobot PG: '.$calculatedPgWeightPerQ.' Poin' : 'Bobot Maks Esai: '.$q->score_weight.' Poin' }}
                                                    </span>
                                                </div>
                                                @if($isPg)
                                                    <span class="px-2 py-0.5 text-[10px] font-black rounded {{ $isRight ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                        {{ $isRight ? '✓ BENAR ('.$calculatedPgWeightPerQ.' Poin)' : '❌ SALAH (0 Poin)' }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 text-[10px] font-black rounded bg-purple-600 text-white">
                                                        📝 PENILAIAN ESAI
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="text-xs font-semibold text-slate-900 mb-2">{!! nl2br(e($q->question_text)) !!}</p>
                                            
                                            <div class="text-xs space-y-1 mb-3">
                                                <p class="text-slate-800"><strong>Jawaban Siswa:</strong> <span class="font-medium text-indigo-900 bg-white px-2 py-1 rounded border border-slate-200 block mt-1 leading-relaxed">{{ $userKey }}</span></p>
                                                <p class="text-slate-600 mt-1"><strong>Referensi Kunci Guru:</strong> {{ $correctKey }}</p>
                                            </div>

                                            <!-- Manual Score Input for Essay Question -->
                                            @if(! $isPg)
                                                <div class="mt-3 bg-white p-3 rounded-lg border border-purple-200">
                                                    <label class="block text-xs font-bold text-purple-900 mb-1">
                                                        Input Skor Perolehan Esai (0 s.d. {{ $q->score_weight }} Poin):
                                                    </label>
                                                    <input type="number" step="0.5" min="0" max="{{ $q->score_weight }}"
                                                        wire:model.live="essayScores.{{ $q->id }}"
                                                        class="w-full sm:w-44 rounded-lg border-slate-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm p-2 font-bold text-purple-900">
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <!-- Combined Score Calculation Summary -->
                            <div class="bg-gradient-to-r from-indigo-900 to-indigo-950 text-white rounded-xl p-5 shadow-inner space-y-4">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                    <div class="space-y-1 text-xs text-indigo-200">
                                        <p>• Porsi Pilihan Ganda: <strong class="text-white">{{ $calculatedPgPortionTotal }} Poin</strong> (@ {{ $calculatedPgWeightPerQ }} Poin/Soal)</p>
                                        <p>• Porsi Esai (Set Guru): <strong class="text-white">{{ $calculatedEssayPortionTotal }} Poin</strong></p>
                                        <p>• Skor PG Diperoleh (Otomatis): <strong class="text-emerald-300 font-bold">{{ $calculatedPgEarned }} Poin</strong></p>
                                        <p>• Skor Esai Diperoleh (Input Guru): <strong class="text-purple-300 font-bold">{{ $calculatedEssayEarned }} Poin</strong></p>
                                    </div>

                                    <div class="w-full sm:w-auto text-center sm:text-right bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl border border-white/20">
                                        <label class="text-[11px] font-bold text-indigo-200 block uppercase tracking-wider mb-1">Nilai Akhir Terkalkulasi</label>
                                        <input type="number" step="0.1" min="0" max="100" wire:model="correctedScore"
                                            class="w-32 text-center text-2xl font-black text-amber-300 bg-slate-900/60 border border-white/30 rounded-lg p-1">
                                        <span class="text-[10px] text-indigo-200 block font-semibold mt-0.5">(Skala 0 - 100 Poin)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-sm transition-colors">
                                💾 Simpan Nilai Akhir Ujian
                            </button>
                            <button type="button" @click="openCorrection = false" class="w-full sm:w-auto mt-3 sm:mt-0 px-4 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs rounded-xl transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

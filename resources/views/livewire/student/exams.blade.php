<div x-data="{ openDetail: @entangle('showDetailModal') }">
    <x-slot name="title">Ujian Online CBT Siswa</x-slot>

    @if(session()->has('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between">
            <span>⚠️ {{ session('error') }}</span>
        </div>
    @endif

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Ruang Asesmen & Hasil Ujian CBT</h2>
            <p class="text-sm text-slate-500 mt-1">
                Siswa: <strong>{{ auth()->user()->name }}</strong> | 
                Kelas: <strong>{{ $student && $student->classroom ? 'Kelas '.$student->classroom->grade_level.' '.$student->classroom->name : '-' }}</strong>
            </p>
        </div>
    </div>

    <!-- Navigation Tabs (Only shown when not taking exam and not finished) -->
    @if(!$isExamActive && !$isFinished)
        <div class="border-b border-slate-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="switchTab('available')" type="button"
                    class="{{ $activeTab === 'available' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Daftar Ujian CBT Aktif
                </button>

                <button wire:click="switchTab('history')" type="button"
                    class="{{ $activeTab === 'history' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Riwayat & Hasil Ujian (Scorecard)
                </button>
            </nav>
        </div>
    @endif

    <!-- MODE 1: LIST UJIAN AKTIF (TAB AVAILABLE) -->
    @if(!$isExamActive && !$isFinished && $activeTab === 'available')
        @if($availableExams->isEmpty())
            <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm">
                <div class="w-16 h-16 bg-teal-50 rounded-full flex items-center justify-center mx-auto mb-4 text-teal-600 text-2xl font-bold">
                    📝
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Ujian CBT Aktif</h3>
                <p class="text-slate-500 text-sm">Tidak ada jadwal ujian CBT yang aktif untuk kelas Anda saat ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($availableExams as $exam)
                    @php
                        $isSubmitted = in_array($exam->id, $submittedExamIds);
                        $isInProgress = in_array($exam->id, $inProgressExamIds);
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between hover:shadow-md transition-all">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-[11px] font-extrabold rounded uppercase">
                                    {{ $exam->subject?->code ?? 'CBT' }}
                                </span>
                                @if($isSubmitted)
                                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full border border-emerald-300">
                                        ✓ Sudah Dikumpulkan
                                    </span>
                                @elseif($isInProgress)
                                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 text-xs font-bold rounded-full border border-amber-300">
                                        Sedang Dikerjakan
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full border border-emerald-300">
                                        ● Aktif
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-lg font-bold text-slate-900 mb-1 leading-snug">{{ $exam->title }}</h3>
                            <div class="text-xs text-slate-600 space-y-1 mb-4">
                                <p>📚 <strong>{{ $exam->subject?->name }}</strong></p>
                                <p>👨‍🏫 Guru: <strong>{{ $exam->teacher?->name ?? 'Pengampu' }}</strong></p>
                                <p>⏱️ Durasi: <strong>{{ $exam->duration_minutes }} Menit</strong></p>
                                @if($exam->questionBank)
                                    <p>❓ Butir Soal: <strong>{{ $exam->questionBank->questions()->count() }} Soal</strong></p>
                                @endif
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            @if($isSubmitted)
                                @php
                                    $subItem = $submissionsList->firstWhere('exam_id', $exam->id);
                                @endphp
                                <button wire:click="viewSubmissionDetail({{ $subItem?->id }})" type="button" class="w-full py-2.5 px-4 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold rounded-xl transition-colors shadow-xs flex items-center justify-center gap-2">
                                    <span>📊 Lihat Hasil (Skor: {{ $subItem?->score }})</span>
                                </button>
                            @elseif($isInProgress)
                                <button wire:click="startExam({{ $exam->id }})" type="button" class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                                    <span>Lanjutkan Pengerjaan</span>
                                </button>
                            @else
                                <button wire:click="startExam({{ $exam->id }})" type="button" class="w-full py-2.5 px-4 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                                    <span>🚀 Mulai Pengerjaan Ujian</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    <!-- MODE 2: TAB RIWAYAT & HASIL UJIAN (SCORECARD) -->
    @if(!$isExamActive && !$isFinished && $activeTab === 'history')
        @if($submissionsList->isEmpty())
            <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm">
                <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-600 text-2xl font-bold">
                    📊
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Riwayat Ujian</h3>
                <p class="text-slate-500 text-sm">Anda belum pernah menyelesaikan ujian CBT online.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Ujian & Mapel</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Waktu Dikumpulkan</th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Jawaban Benar</th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Nilai CBT</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($submissionsList as $sub)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-slate-900">{{ $sub->exam?->title }}</div>
                                        <div class="text-xs text-slate-500">📚 {{ $sub->exam?->subject?->name }} • 👨‍🏫 {{ $sub->exam?->teacher?->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                        🗓️ {{ \Carbon\Carbon::parse($sub->submitted_at)->translatedFormat('d M Y, H:i') }} WIB
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-semibold text-slate-700">
                                        {{ $sub->total_correct }} / {{ $sub->total_questions }} Soal
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 bg-indigo-100 text-indigo-900 font-black text-sm rounded-lg border border-indigo-300">
                                            {{ $sub->score }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button wire:click="viewSubmissionDetail({{ $sub->id }})" type="button" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 transition-colors">
                                            📊 Detail & Review
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    <!-- MODE 3: RUANG PENGERJAAN UJIAN CBT -->
    @if($isExamActive && !empty($questionsList))
        @php
            $currentQ = $questionsList[$currentIndex];
            $currentQId = $currentQ['id'];
            $qType = strtolower($currentQ['type'] ?? 'pg');
        @endphp

        <!-- Top Header CBT Bar -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-4 sm:p-6 text-white shadow-xl mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="px-2.5 py-0.5 bg-teal-500/30 text-teal-200 border border-teal-400/30 rounded-full text-xs font-bold uppercase tracking-wider">
                    {{ $activeExam?->subject?->name ?? 'Ujian CBT' }}
                </span>
                <h2 class="text-xl font-black text-white mt-1">{{ $activeExam?->title }}</h2>
            </div>

            <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl border border-white/20">
                <span class="text-xs text-indigo-200 font-bold uppercase tracking-wider">Sisa Waktu:</span>
                <span class="text-xl font-mono font-black text-amber-300">
                    {{ sprintf('%02d:%02d', floor($remainingSeconds / 60), $remainingSeconds % 60) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Question Content -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <!-- Question Header Bar -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-900 font-black text-xs rounded-lg">
                                Soal No. {{ $currentIndex + 1 }} / {{ count($questionsList) }}
                            </span>
                            <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-xs font-bold rounded uppercase">
                                Tipe: {{ strtoupper($qType) }}
                            </span>
                        </div>

                        <button wire:click="toggleDoubtful({{ $currentQId }})" type="button"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 {{ ($doubtfuls[$currentQId] ?? false) ? 'bg-amber-500 text-white shadow-sm font-black' : 'bg-slate-100 text-slate-600 hover:bg-amber-100 hover:text-amber-800' }}">
                            <span>🟨 Ragu-Ragu</span>
                        </button>
                    </div>

                    <!-- Question Text -->
                    <div class="text-slate-900 text-base font-semibold leading-relaxed mb-6">
                        {!! nl2br(e($currentQ['question_text'])) !!}
                    </div>

                    <!-- Answer Options (PG vs Essay) -->
                    @if($qType === 'pg' || $qType === 'pilihan_ganda')
                        @php
                            $opts = $currentQ['options'] ?? [];
                            if (is_string($opts)) {
                                $opts = json_decode($opts, true) ?? [];
                            }
                        @endphp

                        <div class="space-y-3 mb-8">
                            @if(!empty($opts) && is_array($opts))
                                @foreach($opts as $key => $val)
                                    @php
                                        $optLabel = strtoupper($key);
                                        $isSelected = strtolower($answers[$currentQId] ?? '') === strtolower($key);
                                    @endphp

                                    <button type="button" wire:click="selectAnswer({{ $currentQId }}, '{{ $key }}')"
                                        class="w-full text-left p-4 rounded-xl border transition-all flex items-start gap-4 {{ $isSelected ? 'bg-indigo-50/90 border-indigo-600 text-indigo-950 font-bold shadow-sm ring-1 ring-indigo-500' : 'bg-slate-50/60 border-slate-200 hover:bg-slate-100' }}">
                                        <div class="w-7 h-7 rounded-lg {{ $isSelected ? 'bg-indigo-600 text-white font-black' : 'bg-white text-slate-700 font-bold border border-slate-300' }} flex items-center justify-center text-xs flex-shrink-0 mt-0.5 shadow-xs">
                                            {{ $optLabel }}
                                        </div>
                                        <div class="text-sm leading-snug pt-0.5">
                                            {{ $val }}
                                        </div>
                                    </button>
                                @endforeach
                            @endif
                        </div>
                    @else
                        <!-- Essay Question Textarea -->
                        <div class="mb-8">
                            <label class="block text-xs font-bold text-slate-700 mb-2">Jawaban Uraian / Esai Anda:</label>
                            <textarea wire:model.live="answers.{{ $currentQId }}" rows="5"
                                class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-4"
                                placeholder="Tuliskan jawaban lengkap Anda di sini..."></textarea>
                        </div>
                    @endif

                    <!-- Bottom Nav Controls -->
                    <div class="flex items-center justify-between border-t border-slate-100 pt-6">
                        <button wire:click="prevQuestion" type="button" @disabled($currentIndex == 0)
                            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 disabled:opacity-40 text-slate-700 text-xs font-bold rounded-xl transition-colors">
                            ← Soal Sebelumnya
                        </button>

                        @if($currentIndex < count($questionsList) - 1)
                            <button wire:click="nextQuestion" type="button"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm">
                                Soal Selanjutnya →
                            </button>
                        @else
                            <button wire:click="submitExam" type="button"
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl transition-colors shadow-md">
                                🛑 Selesai & Kumpulkan
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Side: Question Grid Navigator -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-base font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">Navigasi Soal CBT</h3>

                    <div class="grid grid-cols-5 gap-2.5 mb-6">
                        @foreach($questionsList as $idx => $q)
                            @php
                                $qid = $q['id'];
                                $isAns = isset($answers[$qid]) && !empty($answers[$qid]);
                                $isDoubt = $doubtfuls[$qid] ?? false;
                                $isCurr = $currentIndex === $idx;

                                $gridBg = 'bg-slate-100 text-slate-700 border-slate-200';
                                if ($isDoubt) {
                                    $gridBg = 'bg-amber-500 text-white font-black border-amber-600';
                                } elseif ($isAns) {
                                    $gridBg = 'bg-emerald-600 text-white font-black border-emerald-700';
                                }
                            @endphp

                            <button wire:click="goToQuestion({{ $idx }})" type="button"
                                class="w-full h-10 rounded-xl border text-xs transition-all flex items-center justify-center relative {{ $gridBg }} {{ $isCurr ? 'ring-2 ring-offset-2 ring-indigo-600' : '' }}">
                                {{ $idx + 1 }}
                            </button>
                        @endforeach
                    </div>

                    <button wire:click="submitExam" type="button"
                        class="w-full mt-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-md transition-colors flex items-center justify-center gap-2">
                        <span>🛑 Kumpulkan Ujian Sekarang</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODE 4: SCREEN HASIL SETELAH SUBMIT -->
    @if($isFinished)
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg border border-slate-200 p-8 text-center my-8">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-600 text-4xl shadow-inner">
                🎉
            </div>
            <h2 class="text-2xl font-black text-slate-900 mb-1">Ujian CBT Selesai Dikumpulkan!</h2>
            <p class="text-xs text-slate-500 mb-6">Terima kasih telah mengerjakan ujian tepat waktu.</p>

            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 mb-6">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Nilai Ujian CBT Anda (Objektif PG)</span>
                <div class="text-5xl font-black text-indigo-700 mb-2">{{ $finalScore }}</div>
                <div class="text-xs font-semibold text-slate-600 space-y-1">
                    <p>✓ Jawaban Benar: <strong>{{ $totalCorrect }}</strong> dari <strong>{{ $totalPgCount }}</strong> Soal Pilihan Ganda</p>
                    @if($totalEssayCount > 0)
                        <p class="text-amber-700 font-bold">📝 Terdapat {{ $totalEssayCount }} Soal Esai (Menunggu Penilaian Guru)</p>
                    @endif
                </div>
            </div>

            <button wire:click="$set('isFinished', false)" type="button"
                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition-colors shadow-md">
                ← Kembali ke Ruang Ujian
            </button>
        </div>
    @endif

    <!-- MODAL DETAIL HASIL & REVIEW SOAL UJIAN -->
    <div x-show="openDetail" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="openDetail" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openDetail = false"></div>

            <div x-show="openDetail" x-transition.scale class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-10">
                @if($selectedSubmission)
                    <div class="bg-white p-6 sm:p-8">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                            <div>
                                <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-800 text-[11px] font-bold rounded">
                                    {{ $selectedSubmission->exam?->subject?->name }}
                                </span>
                                <h3 class="text-xl font-bold text-slate-900 mt-1">{{ $selectedSubmission->exam?->title }}</h3>
                            </div>
                            <button type="button" wire:click="closeDetailModal" class="text-slate-400 hover:text-slate-600">
                                ✖
                            </button>
                        </div>

                        <!-- Score Banner -->
                        <div class="bg-gradient-to-r from-indigo-900 to-indigo-800 text-white rounded-xl p-5 mb-6 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-indigo-200 uppercase tracking-wider block">Nilai Ujian CBT</span>
                                <div class="text-3xl font-black text-white mt-0.5">{{ $selectedSubmission->score }}</div>
                            </div>
                            <div class="text-right text-xs text-indigo-200">
                                <p>Jawaban Benar: <strong>{{ $selectedSubmission->total_correct }}</strong> / {{ $selectedSubmission->total_questions }} Soal</p>
                                <p class="mt-0.5">Dikumpulkan: {{ \Carbon\Carbon::parse($selectedSubmission->submitted_at)->translatedFormat('d M Y, H:i') }} WIB</p>
                            </div>
                        </div>

                        <!-- Questions Review List -->
                        @if($selectedSubmission->exam && $selectedSubmission->exam->questionBank)
                            <div class="space-y-4 max-h-96 overflow-y-auto pr-1">
                                <h4 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">Review Lembar Jawaban & Kunci Jawaban:</h4>
                                @php
                                    $subAns = $selectedSubmission->answers ?? [];
                                @endphp
                                @foreach($selectedSubmission->exam->questionBank->questions as $idx => $q)
                                    @php
                                        $userKey = strtolower($subAns[$q->id] ?? '-');
                                        $correctKey = strtolower($q->correct_answer ?? '-');
                                        $isPg = in_array(strtolower($q->type ?? 'pg'), ['pg', 'pilihan_ganda']);
                                        $isRight = $isPg && ($userKey === $correctKey);
                                    @endphp
                                    <div class="p-4 rounded-xl border {{ $isPg ? ($isRight ? 'bg-emerald-50/70 border-emerald-200' : 'bg-rose-50/70 border-rose-200') : 'bg-slate-50 border-slate-200' }}">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-bold text-slate-800">Soal {{ $idx + 1 }} ({{ strtoupper($q->type ?? 'PG') }})</span>
                                            @if($isPg)
                                                <span class="px-2 py-0.5 text-[10px] font-black rounded {{ $isRight ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                                    {{ $isRight ? '✓ BENAR' : '❌ SALAH' }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs font-semibold text-slate-900 mb-2">{!! nl2br(e($q->question_text)) !!}</p>
                                        <div class="text-xs space-y-1">
                                            <p class="text-slate-700"><strong>Jawaban Anda:</strong> {{ strtoupper($userKey) }}</p>
                                            @if($isPg)
                                                <p class="text-emerald-700"><strong>Kunci Jawaban:</strong> {{ strtoupper($correctKey) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 text-right">
                        <button type="button" wire:click="closeDetailModal" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-xl transition-colors">
                            Tutup Review
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

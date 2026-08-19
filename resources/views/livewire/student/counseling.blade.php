<div x-data="{ openModal: @entangle('showRequestModal') }">
    <x-slot name="title">Kedisiplinan & Bimbingan Konseling</x-slot>

    @if(session()->has('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-xs">
            <span>✓ {{ session('success') }}</span>
        </div>
    @endif

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Poin Kedisiplinan & Bimbingan Konseling (BK)</h2>
            <p class="text-sm text-slate-500 mt-1">
                Siswa: <strong>{{ auth()->user()->name }}</strong> | 
                Tahun Ajaran: <strong>{{ $activeYear ? $activeYear->name : '-' }}</strong>
            </p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="switchTab('discipline')" type="button"
                class="{{ $activeTab === 'discipline' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Poin Kedisiplinan & Pelanggaran
            </button>

            <button wire:click="switchTab('counseling')" type="button"
                class="{{ $activeTab === 'counseling' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                Jurnal & Permohonan Konseling BK
            </button>
        </nav>
    </div>

    <!-- TAB 1: Poin Kedisiplinan & Pelanggaran -->
    @if($activeTab === 'discipline')
        <!-- Summary Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Total Accumulation Points</span>
                <div class="text-4xl font-black {{ $totalDemeritPoints > 20 ? 'text-rose-600' : 'text-slate-800' }}">
                    {{ $totalDemeritPoints }} <span class="text-xs text-slate-400 font-bold">Poin Minus</span>
                </div>
                <div class="text-xs text-slate-500 mt-1">Status Poin Pelanggaran Kumulatif</div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Total Pelanggaran</span>
                <div class="text-4xl font-black text-slate-800">{{ $totalViolationsCount }} <span class="text-xs text-slate-400 font-bold">Kejadian</span></div>
                <div class="text-xs text-slate-500 mt-1">Kasus Pelanggaran Tata Tertib</div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Tingkat Pemantauan Disiplin</span>
                <div>
                    @if($totalDemeritPoints >= 50)
                        <span class="px-3.5 py-1.5 bg-rose-100 text-rose-800 text-xs font-black rounded-full inline-block border border-rose-300">
                            🚨 SP 2 / Panggilan Ortub High Risk
                        </span>
                    @elseif($totalDemeritPoints >= 20)
                        <span class="px-3.5 py-1.5 bg-amber-100 text-amber-800 text-xs font-black rounded-full inline-block border border-amber-300">
                            ⚠️ Perhatian / Peringatan 1
                        </span>
                    @else
                        <span class="px-3.5 py-1.5 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full inline-block border border-emerald-300">
                            ✓ Baik (Disiplin Terjaga)
                        </span>
                    @endif
                </div>
                <div class="text-xs text-slate-500 mt-2">Kategori Evaluasi Tata Tertib</div>
            </div>
        </div>

        <!-- Violations Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal Kejadian</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Jenis Pelanggaran</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori & Poin</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tindak Lanjut & Guru Pelapor</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($violationsList as $v)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">
                                    🗓️ {{ \Carbon\Carbon::parse($v->event_date)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900">{{ $v->violation_name }}</div>
                                    @if($v->notes)
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $v->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    @php
                                        $catStyles = [
                                            'Ringan' => 'bg-amber-100 text-amber-800 border-amber-300 font-bold',
                                            'Sedang' => 'bg-orange-100 text-orange-800 border-orange-300 font-bold',
                                            'Berat' => 'bg-rose-100 text-rose-800 border-rose-300 font-black',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs rounded-full border {{ $catStyles[$v->category] ?? 'bg-slate-100' }}">
                                        {{ $v->category }} (+{{ $v->points }} Poin)
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-bold text-slate-800">{{ $v->action_taken ?: 'Teguran' }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">👨‍🏫 Pelapor: {{ $v->reporterTeacher?->name ?? 'Guru Piket/Kesiswaan' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                    Sangat baik! Tidak ada catatan pelanggaran tata tertib yang dilaporkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 2: Jurnal & Permohonan BK -->
    @if($activeTab === 'counseling')
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Sesi Bimbingan & Konseling (BK)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Rekam jejak konseling akademik, pribadi, sosial, maupun karir.</p>
            </div>
            <button wire:click="openRequestModal" type="button" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                <span>💬 Ajukan Konseling Mandiri ke Guru BK</span>
            </button>
        </div>

        <!-- Counseling Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal & Jenis Layanan BK</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Deskripsi Masalah / Topik</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Rencana Solusi & Guru BK</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status Proses</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($counselingsList as $c)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-800">{{ \Carbon\Carbon::parse($c->counseling_date)->translatedFormat('d M Y') }}</div>
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-extrabold text-[11px] rounded mt-1 inline-block border border-indigo-200">
                                        {{ $c->counseling_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-700 leading-relaxed max-w-xs">
                                    {{ $c->problem_description }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 leading-relaxed">
                                    <div class="font-semibold text-slate-800 mb-0.5">{{ $c->solution_plan ?: '-' }}</div>
                                    <div class="text-[11px] text-slate-500">👨‍🏫 Guru BK: {{ $c->counselorTeacher?->name ?? 'Belum Ditunjuk' }}</div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    @php
                                        $cstBadge = [
                                            'Proses' => 'bg-amber-100 text-amber-800 border-amber-300 font-bold',
                                            'Selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold',
                                            'Rujukan' => 'bg-indigo-100 text-indigo-800 border-indigo-300 font-bold',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 text-xs rounded-full border {{ $cstBadge[$c->status] ?? 'bg-slate-100' }}">
                                        {{ $c->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500">
                                    Belum ada sesi bimbingan konseling yang tercatat. Klik tombol di atas untuk mengajukan konseling mandiri.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- MODAL FORM: AJUKAN KONSELING MANDIRI SISWA -->
    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="openModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openModal = false"></div>

            <div x-show="openModal" x-transition.scale class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full z-10">
                <form wire:submit.prevent="submitRequest">
                    <div class="bg-white p-6 sm:p-8">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-slate-800">Ajukan Sesi Konseling BK</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Permohonan Anda akan ditangani secara rahasia oleh Guru BK.</p>
                            </div>
                            <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-600">
                                ✖
                            </button>
                        </div>

                        <div class="space-y-5 text-sm">
                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Jenis Layanan Bimbingan BK</label>
                                <select wire:model="counseling_type" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-3.5">
                                    <option value="Bimbingan Pribadi">Bimbingan Pribadi</option>
                                    <option value="Bimbingan Belajar">Bimbingan Belajar / Akademik</option>
                                    <option value="Bimbingan Sosial">Bimbingan Sosial & Pertemanan</option>
                                    <option value="Bimbingan Karir">Bimbingan Karir & Studi Lanjut</option>
                                </select>
                                @error('counseling_type') <span class="text-xs text-rose-600 font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-2">Deskripsi Masalah / Hal yang Ingin Didiskusikan</label>
                                <textarea wire:model="problem_description" rows="4" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3.5"
                                    placeholder="Ceritakan secara singkat masalah atau hal yang ingin Anda konsultasikan dengan Guru BK..."></textarea>
                                @error('problem_description') <span class="text-xs text-rose-600 font-semibold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 sm:flex sm:flex-row-reverse gap-3">
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition-colors">
                            💬 Kirim Permohonan Konseling
                        </button>
                        <button type="button" @click="openModal = false" class="w-full sm:w-auto mt-3 sm:mt-0 px-4 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs rounded-xl transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

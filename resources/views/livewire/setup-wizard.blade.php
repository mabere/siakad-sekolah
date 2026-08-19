<div
    class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-100 to-indigo-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-xl">
        <div class="text-center mb-8 mt-12">
            <div
                class="mx-auto w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg transform rotate-3">
                <svg class="w-10 h-10 text-white transform -rotate-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C20.832 18.477 19.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                    </path>
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-slate-900 tracking-tight">SIAKAD Onboarding</h2>
            <p class="mt-2 text-sm text-slate-600">Selesaikan pengaturan dasar sebelum menggunakan aplikasi.</p>
        </div>

        <div class="bg-white py-8 px-4 shadow-2xl sm:rounded-3xl sm:px-10 border border-slate-100">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm {{ $step >= 1 ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-200 text-slate-500' }}">
                            1</div>
                        <span
                            class="text-xs font-semibold mt-2 {{ $step >= 1 ? 'text-indigo-600' : 'text-slate-500' }}">Profil
                            Sekolah</span>
                    </div>
                    <div class="flex-1 h-1 mx-2 {{ $step >= 2 ? 'bg-indigo-600' : 'bg-slate-200' }} rounded-full"></div>
                    <div class="flex flex-col items-center">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm {{ $step >= 2 ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-200 text-slate-500' }}">
                            2</div>
                        <span
                            class="text-xs font-semibold mt-2 {{ $step >= 2 ? 'text-indigo-600' : 'text-slate-500' }}">Tahun
                            Ajaran</span>
                    </div>
                    <div class="flex-1 h-1 mx-2 {{ $step >= 3 ? 'bg-indigo-600' : 'bg-slate-200' }} rounded-full"></div>
                    <div class="flex flex-col items-center">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm {{ $step >= 3 ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-200 text-slate-500' }}">
                            3</div>
                        <span
                            class="text-xs font-semibold mt-2 {{ $step >= 3 ? 'text-indigo-600' : 'text-slate-500' }}">Selesai</span>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            @if ($step === 1)
                <div class="space-y-5">
                    <h3 class="text-lg font-bold text-slate-800 border-b pb-2">Identitas Sekolah</h3>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Sekolah <span
                                class="text-rose-500">*</span></label>
                        <input type="text" wire:model="schoolName"
                            class="w-full rounded-xl p-2 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Misal: SMA Negeri 1 Siakad">
                        @error('schoolName')
                            <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tingkat Pendidikan (Level) <span
                                class="text-rose-500">*</span></label>
                        <select wire:model="level"
                            class="w-full p-2 rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="SD">SD (Sekolah Dasar)</option>
                            <option value="SMP">SMP (Sekolah Menengah Pertama)</option>
                            <option value="SMA">SMA (Sekolah Menengah Atas)</option>
                            <option value="SMK">SMK (Sekolah Menengah Kejuruan)</option>
                            <option value="TERPADU">Terpadu (SD - SMA)</option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Penting: Penentuan level ini akan mempengaruhi data
                            master kelas yang bisa dibuat.</p>
                        @error('level')
                            <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">NPSN</label>
                            <input type="text" wire:model="npsn"
                                class="w-full p-2 rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Alamat Sekolah</label>
                        <textarea wire:model="address" rows="2"
                            class="w-full p-2 rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button wire:click="nextStep"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition-colors">
                            Selanjutnya &rarr;
                        </button>
                    </div>
                </div>
            @elseif ($step === 2)
                <div class="space-y-5">
                    <h3 class="text-lg font-bold text-slate-800 border-b pb-2">Tahun Ajaran Pertama</h3>
                    <p class="text-sm text-slate-600 mb-4">Tahun ajaran ini akan menjadi tahun ajaran aktif pertama di
                        sistem.</p>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Tahun Ajaran <span
                                class="text-rose-500">*</span></label>
                        <input type="text" wire:model="academicYearName"
                            class="w-full p-2 rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Misal: 2026/2027">
                        @error('academicYearName')
                            <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Semester <span
                                class="text-rose-500">*</span></label>
                        <select wire:model="semester"
                            class="w-full p-2 rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                        @error('semester')
                            <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pt-4 flex justify-between">
                        <button wire:click="previousStep"
                            class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors">
                            &larr; Kembali
                        </button>
                        <button wire:click="nextStep"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition-colors">
                            Selanjutnya &rarr;
                        </button>
                    </div>
                </div>
            @elseif ($step === 3)
                <div class="space-y-5 text-center py-6">
                    <div
                        class="mx-auto w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800">Siap Digunakan!</h3>
                    <p class="text-slate-600">Pengaturan dasar telah selesai. Anda siap menggunakan sistem SIAKAD.</p>

                    <div class="pt-6">
                        <button wire:click="completeSetup"
                            class="w-full px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg transition-colors flex justify-center items-center gap-2 text-lg">
                            Mulai Gunakan Aplikasi
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="mt-4 flex justify-center">
                        <button wire:click="previousStep"
                            class="text-sm text-slate-500 hover:text-indigo-600 font-semibold underline">
                            Cek kembali pengaturan
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

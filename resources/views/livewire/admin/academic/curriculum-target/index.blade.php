<div>
    <x-slot name="title">Bank Kurikulum (CP & TP)</x-slot>

    <!-- Header Section -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-indigo-600/10 text-indigo-600 border border-indigo-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Bank Kurikulum & Capaian Pembelajaran</h1>
                    <p class="text-xs text-slate-500">Kelola master Capaian Pembelajaran (CP), Tujuan Pembelajaran (TP), dan Alur (ATP) standar Kemendikdasmen.</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" wire:click="loadNationalPresets" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 rounded-xl border border-teal-300 bg-teal-50 px-3.5 py-2 text-xs font-bold text-teal-800 shadow-2xs hover:bg-teal-100 transition disabled:opacity-60" title="Muat preset kurikulum resmi Kemendikdasmen">
                <span wire:loading.remove wire:target="loadNationalPresets">✨ Muat Preset Nasional</span>
                <span wire:loading wire:target="loadNationalPresets">Memuat Preset...</span>
            </button>
            <button type="button" wire:click="openCreateModal" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Bab / TP Baru
            </button>
        </div>
    </div>

    <!-- Flash Alert -->
    @if (session()->has('message'))
        <div class="mb-5 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50/90 p-4 text-xs font-semibold text-emerald-900 shadow-xs">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('message') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900">✕</button>
        </div>
    @endif

    <!-- Filters Bar -->
    <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div class="md:col-span-2">
                <label class="block text-2xs font-bold uppercase tracking-wider text-slate-500 mb-1">Pencarian Materi</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari bab, topik, elemen CP..." class="w-full rounded-lg border-slate-300 pl-9 pr-3 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            <div>
                <label class="block text-2xs font-bold uppercase tracking-wider text-slate-500 mb-1">Fase Kurikulum</label>
                <select wire:model.live="selectedPhase" class="w-full rounded-lg border-slate-300 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Fase</option>
                    @foreach ($phases as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-2xs font-bold uppercase tracking-wider text-slate-500 mb-1">Mata Pelajaran</label>
                <select wire:model.live="selectedSubjectName" class="w-full rounded-lg border-slate-300 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Mapel</option>
                    @foreach ($subjects as $s)
                        <option value="{{ $s->name }}">{{ $s->name }}</option>
                    @endforeach
                    <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                    <option value="Matematika">Matematika</option>
                    <option value="Pendidikan Pancasila">Pendidikan Pancasila</option>
                    <option value="Informatika">Informatika</option>
                    <option value="Bahasa Inggris">Bahasa Inggris</option>
                </select>
            </div>
            <div>
                <label class="block text-2xs font-bold uppercase tracking-wider text-slate-500 mb-1">Semester</label>
                <select wire:model.live="selectedSemester" class="w-full rounded-lg border-slate-300 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Semester</option>
                    <option value="1">Semester 1 (Ganjil)</option>
                    <option value="2">Semester 2 (Genap)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50/80 font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-4 py-3.5">Fase & Mapel</th>
                        <th class="px-4 py-3.5">Bab & Materi Pokok</th>
                        <th class="px-4 py-3.5">Elemen CP & Model KBM</th>
                        <th class="px-4 py-3.5">Profil Pancasila (P5)</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($targets as $target)
                        <tr wire:key="curriculum-target-{{ $target->id }}" class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md bg-indigo-50 border border-indigo-200 px-2 py-0.5 text-2xs font-bold text-indigo-700">
                                    {{ $target->phase }}
                                </span>
                                <p class="mt-1 font-bold text-slate-900">{{ $target->subject_name }}</p>
                                <p class="text-2xs text-slate-500">Kelas {{ $target->grade_level }} · Sem {{ $target->semester }}</p>
                            </td>
                            <td class="px-4 py-3.5 align-top max-w-sm">
                                <p class="font-bold text-slate-900">{{ $target->chapter_title }}</p>
                                <p class="text-2xs text-slate-600 font-medium mt-0.5">Topik: <span class="text-slate-800">{{ $target->topic }}</span></p>
                                <div class="mt-1.5 p-2 rounded-lg bg-slate-50 border border-slate-200/80 text-2xs text-slate-700 line-clamp-2" title="{{ $target->learning_objectives }}">
                                    {{ $target->learning_objectives }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 align-top">
                                @if ($target->element)
                                    <span class="inline-block rounded-md bg-teal-50 border border-teal-200 px-2 py-0.5 text-2xs font-semibold text-teal-800">
                                        📌 {{ $target->element }}
                                    </span>
                                @endif
                                <p class="text-2xs text-slate-600 font-medium mt-1">
                                    Model: <span class="font-semibold text-slate-800">{{ $target->learning_model ?: 'PBL' }}</span>
                                </p>
                                <p class="text-2xs text-slate-400 mt-0.5">Alokasi: {{ $target->suggested_duration_jp ?: '6 JP' }}</p>
                            </td>
                            <td class="px-4 py-3.5 align-top max-w-xs">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ((array) ($target->p5_dimensions ?? []) as $p5)
                                        <span class="inline-flex items-center rounded-md bg-slate-100 border border-slate-200 px-1.5 py-0.5 text-2xs font-medium text-slate-700">
                                            ✓ {{ $p5 }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3.5 align-top text-center whitespace-nowrap">
                                <button type="button" wire:click="toggleStatus({{ $target->id }})" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-2xs font-bold transition {{ $target->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $target->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $target->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-4 py-3.5 align-top text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" wire:click="editTarget({{ $target->id }})" class="p-1.5 rounded-lg border border-slate-300 bg-white text-slate-600 hover:border-indigo-400 hover:text-indigo-600 transition" title="Edit Data">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button type="button" wire:click="confirmDelete({{ $target->id }})" class="p-1.5 rounded-lg border border-slate-300 bg-white text-rose-600 hover:border-rose-400 hover:bg-rose-50 transition" title="Hapus Data">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                <div class="mx-auto max-w-sm space-y-2">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-800">Belum ada data target CP & TP</p>
                                    <p class="text-xs text-slate-500">Klik tombol "Muat Preset Nasional" untuk mengisi otomatis seluruh materi standar Kemendikdasmen.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($targets->hasPages())
            <div class="border-t border-slate-200 bg-slate-50/50 p-4">
                {{ $targets->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form (Tambah / Edit) -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-lg bg-indigo-100 text-indigo-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ $isEditing ? 'Edit Target CP & TP Kurikulum' : 'Tambah Target CP & TP Baru' }}</h3>
                            <p class="text-xs text-slate-500">Standar Kurikulum Merdeka Kemendikdasmen (BSKAP 032/2024)</p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">✕</button>
                </div>

                <form wire:submit.prevent="save" class="py-4 space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="subject_name" placeholder="Misal: Bahasa Indonesia" class="w-full rounded-lg border-slate-300 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            @error('subject_name') <span class="text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Fase Perkembangan <span class="text-rose-500">*</span></label>
                            <select wire:model="phase" class="w-full rounded-lg border-slate-300 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($phases as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('phase') <span class="text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tingkat Kelas & Semester</label>
                            <div class="flex gap-2">
                                <input type="number" wire:model="grade_level" min="1" max="12" class="w-1/2 rounded-lg border-slate-300 py-2 text-xs" placeholder="Kelas">
                                <select wire:model="semester" class="w-1/2 rounded-lg border-slate-300 py-2 text-xs">
                                    <option value="1">Sem 1</option>
                                    <option value="2">Sem 2</option>
                                    <option value="1 & 2">Sem 1 & 2</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Bab <span class="text-rose-500">*</span></label>
                            <input type="number" wire:model="chapter_number" min="1" class="w-full rounded-lg border-slate-300 py-2 text-xs">
                            @error('chapter_number') <span class="text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block font-bold text-slate-700 mb-1">Judul Bab Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="chapter_title" placeholder="Contoh: Bab 1: Mengungkap Fakta Alam Secara Objektif" class="w-full rounded-lg border-slate-300 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            @error('chapter_title') <span class="text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Elemen Capaian Pembelajaran (CP)</label>
                            <input type="text" wire:model="element" placeholder="Contoh: Membaca dan Memirsa, Menulis" class="w-full rounded-lg border-slate-300 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Topik / Materi Pokok <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="topic" placeholder="Contoh: Teks Laporan Hasil Observasi (LHO)" class="w-full rounded-lg border-slate-300 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            @error('topic') <span class="text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Rumusan Tujuan Pembelajaran (TP Operasional) <span class="text-rose-500">*</span></label>
                        <textarea wire:model="learning_objectives" rows="4" placeholder="Tuliskan butir-butir TP secara terperinci..." class="w-full rounded-lg border-slate-300 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        @error('learning_objectives') <span class="text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Rekomendasi Model Pembelajaran</label>
                            <select wire:model="learning_model" class="w-full rounded-lg border-slate-300 py-2 text-xs">
                                @foreach ($learningModels as $lm)
                                    <option value="{{ $lm }}">{{ $lm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Estimasi Alokasi Waktu</label>
                            <input type="text" wire:model="suggested_duration_jp" placeholder="Contoh: 6 JP (3 Pertemuan)" class="w-full rounded-lg border-slate-300 py-2 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Dimensi Profil Pelajar Pancasila (P5) Relevan</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-2xs">
                            @foreach ($p5DimensionsList as $p5Dim)
                                <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-slate-100 cursor-pointer">
                                    <input type="checkbox" wire:model="p5_dimensions" value="{{ $p5Dim }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="font-medium text-slate-700">{{ $p5Dim }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Esensi Pemahaman Bermakna (Meaningful Understanding)</label>
                        <textarea wire:model="meaningful_understanding" rows="2" placeholder="Nilai kebermaknaan materi bagi kehidupan nyata peserta didik..." class="w-full rounded-lg border-slate-300 py-2 text-xs"></textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Contoh Pertanyaan Pemantik (1 baris per pertanyaan)</label>
                        <textarea wire:model="inquiry_questions_text" rows="2" placeholder="Bagaimana cara membedakan fakta ilmiah dengan opini?&#10;Mengapa klasifikasi penting dalam laporan?" class="w-full rounded-lg border-slate-300 py-2 text-xs"></textarea>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span class="font-bold text-slate-700">Aktifkan Bab & TP Ini</span>
                        </label>

                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-bold text-slate-700 hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 font-bold text-white shadow-sm hover:bg-indigo-700">
                                Simpan Target
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-rose-100 text-rose-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Konfirmasi Hapus</h3>
                        <p class="text-xs text-slate-500">Apakah Anda yakin ingin menghapus target kurikulum ini?</p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteTarget" class="rounded-lg bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-rose-700">
                        Hapus Target
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

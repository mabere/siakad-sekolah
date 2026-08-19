<div>
    <x-slot name="title">Pengaturan Profil & Foto</x-slot>

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pengaturan Profil & Keamanan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola identitas diri, foto profil, dan kata sandi akun Anda.</p>
        </div>
    </div>

    @if(session()->has('profile_success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
            <span>✓ {{ session('profile_success') }}</span>
        </div>
    @endif

    @if(session()->has('password_success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
            <span>✓ {{ session('password_success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Side: Profile Photo & Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <h2 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Informasi Profil & Foto
                </h2>

                <form wire:submit.prevent="saveProfile" class="space-y-6">
                    <!-- Photo Upload Section -->
                    <div class="flex flex-col sm:flex-row items-center gap-6 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="relative group">
                            @if($photo)
                                <img src="{{ $photo->temporaryUrl() }}" alt="Preview Avatar" class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover shadow-md ring-4 ring-teal-500/30">
                            @else
                                <img src="{{ $currentPhotoUrl }}" alt="Avatar" class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover shadow-md ring-4 ring-teal-500/30">
                            @endif

                            <div wire:loading wire:target="photo" class="absolute inset-0 bg-slate-900/60 rounded-full flex items-center justify-center text-white text-xs font-bold backdrop-blur-xs">
                                Uploading...
                            </div>
                        </div>

                        <div class="space-y-3 text-center sm:text-left flex-1">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Foto Profil</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Format JPG, JPEG, atau PNG. Ukuran maksimal 2 MB.</p>
                            </div>

                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                <label class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm cursor-pointer inline-flex items-center gap-2">
                                    <span>📷 Pilih Foto Baru</span>
                                    <input type="file" wire:model="photo" class="hidden" accept="image/png, image/jpeg, image/jpg">
                                </label>

                                @if($user->getRawOriginal('avatar_url'))
                                    <button type="button" wire:click="deletePhoto" wire:confirm="Hapus foto profil ini?" class="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold rounded-xl transition-colors border border-rose-200">
                                        🗑️ Hapus Foto
                                    </button>
                                @endif
                            </div>

                            @error('photo') <span class="text-xs text-rose-600 font-semibold block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Input Fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                            <input type="text" wire:model="name" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm font-semibold p-3">
                            @error('name') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email *</label>
                            <input type="email" wire:model="email" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm font-semibold p-3">
                            @error('email') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Peran / Hak Akses</label>
                            <span class="px-3 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl block border border-slate-200">
                                {{ $user->roles->pluck('name')->first() ?? 'Pengguna' }}
                            </span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Sekolah</label>
                            <span class="px-3 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl block border border-slate-200 truncate">
                                {{ $user->school?->name ?? 'SIAKAD Sekolah' }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-right">
                        <button type="submit" class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-md transition-colors inline-flex items-center gap-2">
                            <span>💾 Simpan Perubahan Profil</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side: Change Password -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <h2 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Ubah Kata Sandi
                </h2>

                <form wire:submit.prevent="updatePassword" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Saat Ini *</label>
                        <input type="password" wire:model="current_password" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3">
                        @error('current_password') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Baru *</label>
                        <input type="password" wire:model="password" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3">
                        @error('password') <span class="text-xs text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Kata Sandi Baru *</label>
                        <input type="password" wire:model="password_confirmation" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3">
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition-colors flex items-center justify-center gap-2">
                            <span>🔒 Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div>
    <x-slot name="title">Pengaturan Sistem</x-slot>

    @if (session()->has('message'))
        <div class="mb-8 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm flex items-center">
            <svg class="w-5 h-5 text-emerald-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <p class="text-emerald-700 font-medium">{{ session('message') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="save" x-data="{ activeTab: 1 }" class="space-y-8 pb-20">
        
        <!-- Tab Navigation -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <nav class="flex overflow-x-auto hide-scrollbar" aria-label="Tabs">
                <button type="button" @click="activeTab = 1" 
                    :class="{'border-indigo-500 text-indigo-600 bg-indigo-50/50': activeTab === 1, 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50': activeTab !== 1}" 
                    class="flex-1 whitespace-nowrap py-4 px-6 border-b-2 font-semibold text-sm transition-colors text-center focus:outline-none flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Profil Institusi
                </button>
                <button type="button" @click="activeTab = 2" 
                    :class="{'border-indigo-500 text-indigo-600 bg-indigo-50/50': activeTab === 2, 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50': activeTab !== 2}" 
                    class="flex-1 whitespace-nowrap py-4 px-6 border-b-2 font-semibold text-sm transition-colors text-center focus:outline-none flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Kontak & Lokasi
                </button>
                <button type="button" @click="activeTab = 3" 
                    :class="{'border-indigo-500 text-indigo-600 bg-indigo-50/50': activeTab === 3, 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50': activeTab !== 3}" 
                    class="flex-1 whitespace-nowrap py-4 px-6 border-b-2 font-semibold text-sm transition-colors text-center focus:outline-none flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Kepala Sekolah
                </button>
                <button type="button" @click="activeTab = 4" 
                    :class="{'border-indigo-500 text-indigo-600 bg-indigo-50/50': activeTab === 4, 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50': activeTab !== 4}" 
                    class="flex-1 whitespace-nowrap py-4 px-6 border-b-2 font-semibold text-sm transition-colors text-center focus:outline-none flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Akademik & Sistem
                </button>
            </nav>
        </div>
        
        <!-- Tab Contents -->
        <div class="relative">
            
            <!-- 1. Profil Sekolah -->
            <div x-show="activeTab === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-1">
                        <h3 class="text-lg font-bold text-slate-800">Profil Institusi</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">Informasi dasar instansi yang akan digunakan sebagai identitas utama dalam seluruh dokumen resmi, laporan, dan antarmuka sistem SIAKAD.</p>
                    </div>
                    <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Sekolah <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="name" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border" required placeholder="Contoh: SMP Negeri 1 Nusantara">
                                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">NPSN</label>
                                <input type="text" wire:model="npsn" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border" placeholder="Nomor Pokok Sekolah Nasional">
                                @error('npsn') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
        
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Status Sekolah <span class="text-red-500">*</span></label>
                                <select wire:model="status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border bg-white">
                                    <option value="NEGERI">Negeri</option>
                                    <option value="SWASTA">Swasta</option>
                                </select>
                                @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="sm:col-span-2 border-t border-slate-100 pt-6 mt-2">
                                <div class="mb-5">
                                    <h4 class="text-base font-bold text-slate-800">Arah Strategis Sekolah</h4>
                                    <p class="mt-1 text-sm text-slate-500">Visi dan misi dapat digunakan sebagai konteks dokumen resmi serta bantuan AI dalam menyusun perangkat pembelajaran.</p>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Visi Sekolah</label>
                                        <textarea wire:model="vision" rows="3" maxlength="5000" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border" placeholder="Tuliskan visi resmi sekolah."></textarea>
                                        <p class="mt-1.5 text-xs text-slate-500">Pernyataan cita-cita atau kondisi ideal yang ingin diwujudkan sekolah.</p>
                                        @error('vision') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Misi Sekolah</label>
                                        <textarea wire:model="mission" rows="5" maxlength="5000" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border" placeholder="Tuliskan misi resmi sekolah, misalnya satu butir per baris."></textarea>
                                        <p class="mt-1.5 text-xs text-slate-500">Langkah atau komitmen utama sekolah untuk mewujudkan visi.</p>
                                        @error('mission') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
        
                            <div class="sm:col-span-2 border-t border-slate-100 pt-6 mt-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-4">Logo Sekolah</label>
                                <div class="flex items-start gap-6">
                                    <!-- Preview Logo -->
                                    <div class="shrink-0 relative">
                                        @if ($logo)
                                            <div class="h-28 w-28 rounded-xl border-2 border-indigo-200 p-2 bg-indigo-50 flex items-center justify-center overflow-hidden">
                                                <img src="{{ $logo->temporaryUrl() }}" class="max-h-full max-w-full object-contain">
                                            </div>
                                            <span class="absolute -top-2 -right-2 bg-indigo-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">Baru</span>
                                        @elseif ($existingLogo)
                                            <div class="h-28 w-28 rounded-xl border border-slate-200 p-2 bg-slate-50 flex items-center justify-center overflow-hidden shadow-inner">
                                                <img src="{{ asset('storage/' . $existingLogo) }}" class="max-h-full max-w-full object-contain">
                                            </div>
                                        @else
                                            <div class="h-28 w-28 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 flex flex-col items-center justify-center text-slate-400">
                                                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                <span class="text-xs font-medium">Kosong</span>
                                            </div>
                                        @endif
                                    </div>
                                    <!-- Input Upload -->
                                    <div class="flex-1">
                                        <input type="file" wire:model="logo" accept="image/*" class="block w-full p-2 text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-300 rounded-lg cursor-pointer bg-white transition-colors">
                                        <p class="mt-2 text-xs text-slate-500">Format yang didukung: PNG, JPG, JPEG (Maks. 2MB). Disarankan menggunakan logo dengan latar belakang transparan.</p>
                                        @error('logo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        <div wire:loading wire:target="logo" class="text-sm font-medium text-indigo-600 mt-2 flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            Memproses gambar...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
            <!-- 2. Kontak & Lokasi -->
            <div x-show="activeTab === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-1">
                        <h3 class="text-lg font-bold text-slate-800">Kontak & Lokasi</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">Alamat lengkap dan rincian kontak akan dicetak pada kop surat rapor serta dokumen resmi lainnya.</p>
                    </div>
                    <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Kota / Kabupaten</label>
                                <input type="text" wire:model="city" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border" placeholder="Contoh: Jakarta Selatan (Akan digunakan pada tempat tanggal rapor)">
                                @error('city') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
        
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                                <textarea wire:model="address" rows="3" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border" placeholder="Nama jalan, RT/RW, Kecamatan, Provinsi, Kode Pos"></textarea>
                                @error('address') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Nomor Telepon</label>
                                <div class="relative rounded-lg shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    </div>
                                    <input type="text" wire:model="phone" class="block w-full pl-10 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border" placeholder="021-XXXXXXX">
                                </div>
                                @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Email Sekolah</label>
                                <div class="relative rounded-lg shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <input type="email" wire:model="email" class="block w-full pl-10 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border" placeholder="admin@sekolah.sch.id">
                                </div>
                                @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Website</label>
                                <div class="relative rounded-lg shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                    </div>
                                    <input type="text" wire:model="website" class="block w-full pl-10 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border" placeholder="https://www.sekolah.sch.id">
                                </div>
                                @error('website') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
            <!-- 3. Kepala Sekolah -->
            <div x-show="activeTab === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-1">
                        <h3 class="text-lg font-bold text-slate-800">Kepala Sekolah</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">Pejabat berwenang yang akan menandatangani dokumen akademis secara elektronik menggunakan sistem QR Code.</p>
                    </div>
                    <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Kepala Sekolah</label>
                                <input type="text" wire:model="headmasterName" placeholder="Contoh: Budi Santoso, S.Pd., M.Pd." class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border">
                                @error('headmasterName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">NIP Kepala Sekolah</label>
                                <input type="text" wire:model="headmasterNip" placeholder="Contoh: 19801231 200501 1 001" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border">
                                @error('headmasterNip') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
            <!-- 4. Akademik & Kurikulum -->
            <div x-show="activeTab === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-1">
                        <h3 class="text-lg font-bold text-slate-800">Akademik & Sistem</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">Pengaturan default sistem. Rincian kurikulum dan kalender yang berubah setiap tahun ajaran diatur pada menu Master Data &gt; Tahun Ajaran.</p>
                    </div>
                    <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8 space-y-8">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Tingkat Pendidikan Sistem <span class="text-red-500">*</span></label>
                                <select wire:model.live="level" @if($isLevelLocked) disabled @endif class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border @if($isLevelLocked) bg-slate-100 text-slate-500 cursor-not-allowed @else bg-slate-50 text-slate-600 @endif font-medium">
                                    <option value="SMP">SMP / Sederajat</option>
                                    <option value="SMA">SMA / Sederajat</option>
                                    <option value="TERPADU">Sekolah Terpadu</option>
                                </select>
                                @if($isLevelLocked)
                                    <p class="mt-1.5 text-xs text-red-600 font-medium">Terkunci: Tingkat pendidikan tidak dapat diubah karena data kelas/siswa sudah dibuat.</p>
                                @else
                                    <p class="mt-1.5 text-xs text-amber-600">Perhatian: Mengubah jenjang dapat memengaruhi fitur yang aktif.</p>
                                @endif
                                @error('level') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
        
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Tipe Kurikulum Default</label>
                                <select wire:model="curriculumType" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2.5 border bg-white">
                                    <option value="MERDEKA">Kurikulum Merdeka</option>
                                    <option value="K13">Kurikulum 2013 (K13)</option>
                                    @if($level === 'TERPADU')
                                    <option value="MIXED">Campuran (Bisa disetel per kelas)</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="pt-6 border-t border-slate-100">
                            <div class="flex items-start bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                <div class="flex items-center h-5 mt-0.5">
                                    <input wire:model="isPromotionUnlocked" id="isPromotionUnlocked" type="checkbox" class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-gray-300 rounded cursor-pointer">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="isPromotionUnlocked" class="font-bold text-indigo-900 cursor-pointer text-base">Buka Akses Kenaikan & Kelulusan Akhir Tahun</label>
                                    <p class="text-indigo-700/80 mt-1 leading-relaxed">
                                        Saat diaktifkan, Admin dapat mengeksekusi fitur <b>Naik Kelas</b>, <b>Tinggal Kelas</b>, dan <b>Lulus Sekolah</b>. Fitur ini sebaiknya hanya diaktifkan pada masa transisi akhir tahun ajaran. 
                                        <br><i>Catatan: Fitur <b>Pindah Kelas (Mutasi Internal)</b> tetap dapat digunakan kapan saja meskipun opsi ini dimatikan.</i>
                                    </p>
                                </div>
                            </div>
                        </div>
        
                        <div class="border-t border-slate-100 pt-6">
                            @if ($level === 'SMA' || $level === 'TERPADU')
                                <div class="flex items-start bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input wire:model="useMajors" id="useMajors" type="checkbox" class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-gray-300 rounded cursor-pointer">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="useMajors" class="font-bold text-indigo-900 cursor-pointer text-base">Aktifkan Modul Penjurusan</label>
                                        <p class="text-indigo-700/80 mt-1 leading-relaxed">Mengaktifkan fitur program keahlian/penjurusan (seperti MIPA, IPS, Bahasa, atau Kejuruan) pada pendataan master siswa dan rombongan belajar.</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-start bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <svg class="w-5 h-5 text-slate-400 shrink-0 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm text-slate-500 leading-relaxed">
                                        <span class="font-semibold text-slate-700">Penjurusan Dinonaktifkan.</span> Sistem mendeteksi jenjang SMP sehingga modul program keahlian disembunyikan secara otomatis.
                                    </p>
                                </div>
                            @endif
                        </div>
        
                    </div>
                </div>
            </div>

        </div>

        <!-- Sticky Bottom Actions -->
        <div class="fixed bottom-0 left-0 right-0 lg:left-64 z-40 bg-white/80 backdrop-blur-md border-t border-slate-200 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] p-4 flex justify-end gap-3 px-6 md:px-12">
            <button type="submit" class="inline-flex justify-center items-center py-2.5 px-8 shadow-md text-sm font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                <svg wire:loading.remove wire:target="save" class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <svg wire:loading wire:target="save" class="animate-spin w-5 h-5 mr-2 -ml-1 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                
                <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>

    </form>
</div>

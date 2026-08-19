<div class="p-6">
    <!-- Header Area -->
    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Dasbor Humas</h1>
        <p class="text-slate-500 font-medium mt-1">Manajemen konten portal, berita, dan informasi publik.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Posts -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15M9 11l3 3L22 4"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalPosts) }}</h3>
                <p class="text-sm font-semibold text-slate-500 mt-1">Total Artikel/Berita</p>
            </div>
        </div>

        <!-- Categories -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-teal-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center text-teal-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalCategories) }}</h3>
                <p class="text-sm font-semibold text-slate-500 mt-1">Kategori Konten</p>
            </div>
        </div>

        <!-- Sliders -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalSliders) }}</h3>
                <p class="text-sm font-semibold text-slate-500 mt-1">Slider Banner</p>
            </div>
        </div>
    </div>

    <!-- Coming Soon Extended Features -->
    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-2xl p-8 border border-indigo-100 shadow-sm relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-indigo-600 flex-shrink-0 shadow-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Modul Humas Lanjutan Segera Hadir</h3>
                <p class="text-slate-600 font-medium">Fitur Pengumuman Massal (Broadcast), Pesan Wali Murid, dan Pengaduan Masyarakat sedang dalam tahap rancangan arsitektur dan akan dirilis segera.</p>
            </div>
        </div>
    </div>
</div>

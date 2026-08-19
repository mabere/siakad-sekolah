<div class="pt-32 pb-16 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-slate-900 mb-4">Berita & Pengumuman Sekolah</h1>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">Dapatkan informasi terbaru seputar kegiatan akademik, prestasi siswa, dan pengumuman resmi dari sekolah kami.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Main Content (Left) -->
            <div class="lg:w-2/3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($posts as $post)
                        <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-200 group flex flex-col h-full">
                            <div class="h-52 bg-slate-200 relative overflow-hidden flex-shrink-0">
                                @if($post->featured_image)
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-indigo-50 text-indigo-200">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L28 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                                @if($post->category)
                                    <button wire:click="setCategory('{{ $post->category->slug }}')" class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-indigo-600 shadow-sm hover:bg-indigo-600 hover:text-white transition-colors">
                                        {{ $post->category->name }}
                                    </button>
                                @endif
                            </div>
                            <div class="p-6 flex flex-col flex-1">
                                <div class="text-xs text-slate-500 font-medium mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span>{{ $post->published_at->format('d M Y') }}</span>
                                    <span>&bull;</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span>{{ $post->author->name ?? 'Admin' }}</span>
                                </div>
                                <h2 class="text-xl font-bold text-slate-900 mb-3 line-clamp-2 leading-snug">
                                    <a href="{{ route('public.blog.show', $post->slug) }}" class="hover:text-indigo-600 transition-colors">{{ $post->title }}</a>
                                </h2>
                                <p class="text-slate-600 text-sm mb-4 line-clamp-3 flex-1">
                                    {{ Str::limit(strip_tags($post->content), 120) }}
                                </p>
                                <a href="{{ route('public.blog.show', $post->slug) }}" class="inline-flex items-center text-sm font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                    Baca Selengkapnya
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-1 md:col-span-2 text-center py-16 bg-white rounded-2xl border border-slate-200 shadow-sm">
                            <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak ada berita ditemukan</h3>
                            <p class="text-slate-500">Coba sesuaikan kata kunci pencarian atau pilih kategori lain.</p>
                            @if($search || $categorySlug)
                                <button wire:click="$set('search', ''); $set('categorySlug', '');" class="mt-4 px-4 py-2 bg-indigo-50 text-indigo-600 font-bold rounded-lg hover:bg-indigo-100 transition-colors">
                                    Reset Pencarian
                                </button>
                            @endif
                        </div>
                    @endforelse
                </div>
                
                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            </div>

            <!-- Sidebar (Right) -->
            <div class="lg:w-1/3 space-y-8">
                
                <!-- Search Widget -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Cari Berita
                    </h3>
                    <div class="relative">
                        <input wire:model.live.debounce.500ms="search" type="text" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow text-sm" placeholder="Ketik kata kunci...">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <!-- Categories Widget -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Kategori
                    </h3>
                    <ul class="space-y-2">
                        <li>
                            <button wire:click="setCategory('')" class="w-full flex items-center justify-between py-2 px-3 rounded-lg transition-colors {{ $categorySlug === '' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
                                <span>Semua Berita</span>
                            </button>
                        </li>
                        @foreach($categories as $category)
                            <li>
                                <button wire:click="setCategory('{{ $category->slug }}')" class="w-full flex items-center justify-between py-2 px-3 rounded-lg transition-colors {{ $categorySlug === $category->slug ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
                                    <span>{{ $category->name }}</span>
                                    <span class="bg-slate-100 text-slate-500 text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $categorySlug === $category->slug ? 'bg-indigo-100 text-indigo-600' : '' }}">
                                        {{ $category->posts_count }}
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Popular Posts Widget -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Berita Terbaru
                    </h3>
                    <div class="space-y-4">
                        @foreach($popularPosts as $popPost)
                            <a href="{{ route('public.blog.show', $popPost->slug) }}" class="flex gap-4 group">
                                <div class="w-20 h-20 bg-slate-200 rounded-lg overflow-hidden flex-shrink-0">
                                    @if($popPost->featured_image)
                                        <img src="{{ asset('storage/' . $popPost->featured_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-indigo-50 text-indigo-200">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L28 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-slate-800 line-clamp-2 leading-tight group-hover:text-indigo-600 transition-colors mb-1">{{ $popPost->title }}</h4>
                                    <p class="text-xs text-slate-500 font-medium">{{ $popPost->published_at->format('d M Y') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="pt-32 pb-16 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Main Content (Left) -->
            <div class="lg:w-2/3">
                <div class="bg-white rounded-3xl overflow-hidden shadow-xl border border-slate-100">
                    
                    <!-- Featured Image -->
                    <div class="h-64 sm:h-96 bg-slate-200 relative">
                        @if($post->featured_image)
                            <img src="{{ asset('storage/' . $post->featured_image) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-indigo-50 text-indigo-200">
                                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L28 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                    </div>

                    <!-- Article Body -->
                    <div class="p-8 sm:p-12">
                        
                        <!-- Meta -->
                        <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500 font-medium mb-6">
                            @if($post->category)
                                <a href="{{ route('public.blog.index', ['category' => $post->category->slug]) }}" class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full font-bold hover:bg-indigo-100 transition-colors">
                                    {{ $post->category->name }}
                                </a>
                            @endif
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $post->published_at->format('d F Y') }}
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Oleh {{ $post->author->name ?? 'Admin' }}
                            </div>
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-8 leading-tight">
                            {{ $post->title }}
                        </h1>

                        <!-- Content Formatting Area -->
                        <div class="prose prose-slate prose-indigo max-w-none prose-img:rounded-xl
                            [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:my-1
                            [&_blockquote]:border-l-4 [&_blockquote]:border-indigo-300 [&_blockquote]:pl-4 [&_blockquote]:italic
                            [&_a]:text-indigo-600 [&_a]:underline
                            [&_table]:my-4 [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-slate-300 [&_td]:p-2
                            [&_th]:border [&_th]:border-slate-300 [&_th]:bg-slate-100 [&_th]:p-2 [&_th]:text-left
                            [&_img]:my-4 [&_img]:max-w-full [&_img]:rounded-xl">
                            {!! \App\Support\SafeHtml::clean($post->content) !!}
                        </div>
                        
                        <!-- Share Buttons (Static UI for now) -->
                        <div class="mt-12 pt-8 border-t border-slate-200">
                            <h4 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Bagikan Artikel Ini</h4>
                            <div class="flex gap-3">
                                <button class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                                </button>
                                <button class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.312h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/></svg>
                                </button>
                                <button class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-green-50 hover:text-green-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Sidebar (Right) -->
            <div class="lg:w-1/3 space-y-8">
                
                <!-- Categories Widget -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Kategori Berita
                    </h3>
                    <ul class="space-y-2">
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ route('public.blog.index', ['category' => $category->slug]) }}" class="w-full flex items-center justify-between py-2 px-3 rounded-lg transition-colors text-slate-600 hover:bg-slate-50 hover:text-indigo-600">
                                    <span>{{ $category->name }}</span>
                                    <span class="bg-slate-100 text-slate-500 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        {{ $category->posts_count }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Popular Posts Widget -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15M9 11l3 3L22 4"></path></svg>
                        Berita Lainnya
                    </h3>
                    <div class="space-y-4">
                        @forelse($popularPosts as $popPost)
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
                        @empty
                            <p class="text-sm text-slate-500">Tidak ada berita lainnya.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

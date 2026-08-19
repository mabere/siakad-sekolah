<div>
    <!-- Hero Section with W3Schools Style Slider & Quotation Text -->
    <div x-data="heroSlider()" class="relative w-full bg-slate-900 overflow-hidden group" style="height: 70vh; min-height: 500px;">
        
        <!-- Slider Container (Flex for sliding) -->
        <div class="flex w-full h-full transition-transform duration-[1500ms] ease-in-out" 
             :style="'transform: translateX(-' + (activeSlide * 100) + '%)'">
            
            <template x-for="(slider, index) in sliders" :key="index">
                <div class="w-full h-full flex-shrink-0 relative">
                    
                    <!-- Background Image -->
                    <img :src="'{{ asset('storage') }}/' + slider.image_path" class="absolute inset-0 w-full h-full object-cover">
                    
                    <!-- Dark Overlay -->
                    <div class="absolute inset-0 bg-black/50"></div>
                    
                    <!-- Number Text (1 / 3) -->
                    <div class="absolute top-6 left-6 z-20 text-white/90 text-sm font-bold tracking-widest bg-black/40 px-3 py-1 rounded">
                        <span x-text="(index + 1)"></span> / <span x-text="sliders.length"></span>
                    </div>

                    <!-- Quotation Content -->
                    <div class="absolute inset-0 flex items-center justify-center z-20 p-6">
                        <div class="max-w-3xl w-full text-center">
                            <!-- Quotation Mark Icon -->
                            <svg class="w-12 h-12 text-white/40 mb-6 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                            </svg>
                            
                            <h2 class="text-3xl md:text-5xl lg:text-6xl font-serif font-bold text-white mb-6 italic leading-snug drop-shadow-lg" x-text="`&quot;${slider.title}&quot;`"></h2>
                            
                            <p class="text-lg md:text-xl text-slate-200 mb-10 max-w-2xl mx-auto drop-shadow-md font-medium" x-text="slider.description"></p>
                            
                            <!-- Action Button -->
                            <template x-if="slider.button_text">
                                <a :href="slider.button_url || '#'" class="inline-block px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition-colors shadow-lg">
                                    <span x-text="slider.button_text"></span>
                                </a>
                            </template>
                            
                            <!-- Fallback Button if empty -->
                            <template x-if="!slider.button_text">
                                <div class="inline-block">
                                    @auth
                                        <a href="{{ route('admin.dashboard') }}" class="inline-block px-8 py-3 bg-white hover:bg-slate-100 text-indigo-900 font-bold rounded-lg transition-colors shadow-lg">Ke Dashboard</a>
                                    @else
                                        <a href="{{ route('login') }}" class="inline-block px-8 py-3 bg-white hover:bg-slate-100 text-indigo-900 font-bold rounded-lg transition-colors shadow-lg">Masuk Portal</a>
                                    @endauth
                                </div>
                            </template>
                        </div>
                    </div>
                    
                </div>
            </template>
        </div>

        <!-- Next / Prev Buttons -->
        <button @click="prev()" class="absolute left-0 top-1/2 -translate-y-1/2 z-30 px-4 py-8 text-white/50 hover:text-white hover:bg-black/40 transition-all text-4xl font-light rounded-r-lg">
            &#10094;
        </button>
        <button @click="next()" class="absolute right-0 top-1/2 -translate-y-1/2 z-30 px-4 py-8 text-white/50 hover:text-white hover:bg-black/40 transition-all text-4xl font-light rounded-l-lg">
            &#10095;
        </button>

        <!-- Dots Indicator -->
        <div class="absolute bottom-6 inset-x-0 z-30 flex justify-center gap-2">
            <template x-for="(slider, index) in sliders" :key="'dot-'+index">
                <button @click="goTo(index)" 
                        class="w-3.5 h-3.5 rounded-full transition-colors duration-300 border-2 border-transparent"
                        :class="activeSlide === index ? 'bg-white border-white/50 shadow-lg' : 'bg-white/40 hover:bg-white/70'">
                </button>
            </template>
        </div>
    </div>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('heroSlider', () => ({
                activeSlide: 0,
                sliders: @json($sliders),
                timer: null,
                
                init() {
                    if (this.sliders.length > 1) {
                        this.startAutoPlay();
                    }
                },
                
                startAutoPlay() {
                    this.timer = setInterval(() => {
                        this.next();
                    }, 8000); // 8 seconds per slide
                },
                
                resetAutoPlay() {
                    clearInterval(this.timer);
                    this.startAutoPlay();
                },
                
                next() {
                    this.activeSlide = (this.activeSlide + 1) % this.sliders.length;
                    this.resetAutoPlay();
                },
                
                prev() {
                    this.activeSlide = (this.activeSlide - 1 + this.sliders.length) % this.sliders.length;
                    this.resetAutoPlay();
                },
                
                goTo(index) {
                    this.activeSlide = index;
                    this.resetAutoPlay();
                }
            }));
        });
    </script>

    <!-- Animated School Stats Section -->
    <div class="py-20 bg-white relative z-20 -mt-8 rounded-t-[3rem] shadow-[0_-10px_40px_rgba(0,0,0,0.1)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                
                <div class="text-center group" x-data="{ count: 0, target: {{ $stats['students'] }} }" x-init="
                    let observer = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting) {
                            let i = setInterval(() => { 
                                count += Math.ceil(target/50); 
                                if(count >= target) { count = target; clearInterval(i); } 
                            }, 20);
                            observer.disconnect();
                        }
                    });
                    observer.observe($el);
                ">
                    <div class="w-16 h-16 mx-auto bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div class="text-4xl font-black text-slate-900 mb-1"><span x-text="count">0</span>+</div>
                    <div class="text-slate-500 font-semibold text-sm tracking-wide">Siswa Aktif</div>
                </div>

                <div class="text-center group" x-data="{ count: 0, target: {{ $stats['teachers'] }} }" x-init="
                    let observer = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting) {
                            let i = setInterval(() => { 
                                count += Math.ceil(target/50); 
                                if(count >= target) { count = target; clearInterval(i); } 
                            }, 20);
                            observer.disconnect();
                        }
                    });
                    observer.observe($el);
                ">
                    <div class="w-16 h-16 mx-auto bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="text-4xl font-black text-slate-900 mb-1"><span x-text="count">0</span>+</div>
                    <div class="text-slate-500 font-semibold text-sm tracking-wide">Tenaga Pendidik</div>
                </div>

                <div class="text-center group" x-data="{ count: 0, target: {{ $stats['classrooms'] }} }" x-init="
                    let observer = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting) {
                            let i = setInterval(() => { 
                                count += Math.ceil(target/50); 
                                if(count >= target) { count = target; clearInterval(i); } 
                            }, 20);
                            observer.disconnect();
                        }
                    });
                    observer.observe($el);
                ">
                    <div class="w-16 h-16 mx-auto bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div class="text-4xl font-black text-slate-900 mb-1"><span x-text="count">0</span></div>
                    <div class="text-slate-500 font-semibold text-sm tracking-wide">Rombongan Belajar</div>
                </div>

                <div class="text-center group" x-data="{ count: 0, target: {{ $stats['achievements'] }} }" x-init="
                    let observer = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting) {
                            let i = setInterval(() => { 
                                count += Math.ceil(target/50); 
                                if(count >= target) { count = target; clearInterval(i); } 
                            }, 20);
                            observer.disconnect();
                        }
                    });
                    observer.observe($el);
                ">
                    <div class="w-16 h-16 mx-auto bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    </div>
                    <div class="text-4xl font-black text-slate-900 mb-1"><span x-text="count">0</span>+</div>
                    <div class="text-slate-500 font-semibold text-sm tracking-wide">Prestasi Siswa</div>
                </div>

            </div>
        </div>
    </div>

    <!-- Fitur Unggulan (New Section) -->
    <div id="fitur" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-indigo-600 font-bold tracking-wide uppercase text-sm mb-2">Platform Terintegrasi</h2>
                <h3 class="text-3xl md:text-4xl font-black text-slate-900 mb-4">Kemudahan Dalam Genggaman</h3>
                <p class="text-slate-600 text-lg">Portal SIAKAD kami dirancang dengan antarmuka modern yang memudahkan seluruh warga sekolah.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 transition-colors duration-300">
                        <svg class="w-7 h-7 text-indigo-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-3">Presensi Real-time</h4>
                    <p class="text-slate-600">Pantau kehadiran siswa dengan mudah dan cepat melalui sistem scan QR atau manual presensi yang terhubung ke portal orang tua.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-teal-100 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-teal-600 transition-colors duration-300">
                        <svg class="w-7 h-7 text-teal-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-3">E-Rapor Kurikulum Merdeka</h4>
                    <p class="text-slate-600">Manajemen penilaian Capaian Pembelajaran (CP) dan Tujuan Pembelajaran (TP) yang terstruktur hingga pencetakan rapor digital.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-rose-100 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-rose-600 transition-colors duration-300">
                        <svg class="w-7 h-7 text-rose-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-3">Portal Orang Tua & Siswa</h4>
                    <p class="text-slate-600">Transparansi nilai, pelanggaran (EWS), absensi, dan informasi akademik lainnya yang dapat dipantau langsung oleh wali murid.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest News Section -->
    <div id="berita" class="py-24 bg-white relative">
        <!-- Decorative Background -->
        <div class="absolute top-0 right-0 w-1/3 h-full bg-slate-50/50 rounded-l-[100px] z-0 hidden lg:block"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col sm:flex-row justify-between items-end mb-16 gap-6">
                <div class="max-w-2xl">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-4">Berita & Informasi</h2>
                    <p class="text-slate-600 text-lg">Ikuti perkembangan aktivitas akademik, prestasi, dan pengumuman terbaru dari sekolah kami.</p>
                </div>
                <a href="{{ route('public.blog.index') }}" class="group inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 font-bold px-6 py-3 rounded-full hover:bg-indigo-600 hover:text-white transition-all duration-300">
                    Lihat Semua
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @forelse($latestPosts as $index => $post)
                    <div class="bg-white rounded-[2rem] overflow-hidden border border-slate-100 hover:shadow-2xl hover:border-indigo-100 transition-all duration-500 group {{ $index === 1 ? 'md:-translate-y-8' : '' }}">
                        <div class="h-56 bg-slate-100 relative overflow-hidden">
                            @if($post->featured_image)
                                <img src="{{ asset('storage/' . $post->featured_image) }}" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-50 text-indigo-300">
                                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L28 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            @if($post->category)
                                <div class="absolute top-4 left-4 bg-white/95 backdrop-blur-md px-4 py-1.5 rounded-full text-xs font-black tracking-wider uppercase text-indigo-700 shadow-lg">
                                    {{ $post->category->name }}
                                </div>
                            @endif
                        </div>
                        <div class="p-8">
                            <div class="text-sm font-semibold text-slate-500 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $post->published_at->format('d F Y') }}
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-4 line-clamp-2 leading-snug group-hover:text-indigo-600 transition-colors">
                                <a href="{{ route('public.blog.show', $post->slug) }}">{{ $post->title }}</a>
                            </h3>
                            <p class="text-slate-600 line-clamp-3 text-sm mb-6 leading-relaxed">
                                {{ strip_tags($post->content) }}
                            </p>
                            <a href="{{ route('public.blog.show', $post->slug) }}" class="inline-flex items-center font-bold text-indigo-600 group-hover:text-indigo-800 transition-colors">
                                Baca Selengkapnya
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-20 bg-slate-50 rounded-[3rem] border border-slate-200 border-dashed">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15M9 11l3 3L22 4"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-800 mb-2">Belum ada publikasi</h4>
                        <p class="text-slate-500">Berita dan informasi terbaru akan segera hadir di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Alpine JS Setup for Intersect (if Alpine Intersection observer plugin is missing, fallback) -->
    <!-- The x-intersect is part of alpinejs/intersect which might not be installed. 
         To ensure it works without the plugin, I'll update the stats to animate immediately or use simple scroll event. 
         But assuming Alpine standard or just a basic timer for stats. 
         Wait, I used x-intersect which requires `@alpinejs/intersect`. If it's not present, they won't animate.
         Let me change `x-intersect.once` to just simple `x-init` with a timeout, or a simple scroll listener on window. -->
</div>

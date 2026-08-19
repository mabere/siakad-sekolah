<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SIAKAD Sekolah - Portal Digital Akademik' }}</title>
    <meta name="description" content="Sistem Informasi Akademik dan Berita Sekolah.">
    <meta name="theme-color" content="#1e3a8a">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-[Inter] antialiased bg-slate-50 text-slate-800 selection:bg-indigo-500 selection:text-white flex flex-col min-h-screen">
    @php
        $school = \App\Models\School::first();
        $schoolName = $school?->name ?? 'SIAKAD';
        $schoolLogo = $school?->logo_url;

        // Load dynamic menus
        $headerMenu = $school
            ? \App\Models\Menu::where('school_id', $school->id)
                ->where('location', 'header')
                ->with(['parentItems.children'])
                ->first()
            : null;

        $footerMenu = $school
            ? \App\Models\Menu::where('school_id', $school->id)
                ->where('location', 'footer')
                ->with(['parentItems.children'])
                ->first()
            : null;

        $headerItems = $headerMenu?->parentItems ?? collect();
        $footerItems = $footerMenu?->parentItems ?? collect();
    @endphp

    <!-- Navbar -->
    <nav x-data="{ mobileMenuOpen: false }" class="fixed w-full z-50 transition-all duration-300 bg-white/90 backdrop-blur-md border-b border-slate-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    @if($schoolLogo)
                        <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}" class="w-10 h-10 rounded-xl object-contain bg-white p-1 ring-2 ring-indigo-500/30 flex-shrink-0 shadow-md">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-blue-500 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-500/30">
                            {{ substr($schoolName, 0, 1) }}
                        </div>
                    @endif
                    <span class="font-extrabold text-2xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-slate-900 to-slate-700">{{ $schoolName }}</span>
                </a>
                
                <!-- Desktop Menu (Dynamic) -->
                <div class="hidden lg:flex space-x-1 items-center">
                    @if($headerItems->isNotEmpty())
                        @foreach($headerItems as $item)
                            @if($item->children->isNotEmpty())
                                {{-- Menu dengan sub-menu (dropdown) --}}
                                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                                    <a href="{{ \App\Support\HostRelativeUrl::normalize($item->url) }}" target="{{ $item->target }}"
                                       class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-lg hover:bg-slate-50 transition-colors">
                                        {{ $item->title }}
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </a>
                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 translate-y-1"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 translate-y-1"
                                         style="display:none;"
                                         class="absolute left-0 top-full mt-1 w-52 bg-white rounded-xl shadow-lg border border-slate-100 py-2 z-50">
                                        @foreach($item->children as $child)
                                            <a href="{{ \App\Support\HostRelativeUrl::normalize($child->url) }}" target="{{ $child->target }}"
                                               class="block px-4 py-2 text-sm text-slate-700 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                                {{ $child->title }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                {{-- Menu item biasa --}}
                                <a href="{{ \App\Support\HostRelativeUrl::normalize($item->url) }}" target="{{ $item->target }}"
                                   class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-lg hover:bg-slate-50 transition-colors">
                                    {{ $item->title }}
                                </a>
                            @endif
                        @endforeach
                    @else
                        {{-- Fallback: link bawaan jika menu belum dikonfigurasi --}}
                        <a href="{{ route('home') }}" class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-lg hover:bg-slate-50 transition-colors">Beranda</a>
                        <a href="{{ route('public.blog.index') }}" class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-lg hover:bg-slate-50 transition-colors">Berita & Pengumuman</a>
                    @endif
                </div>
                
                <!-- Desktop Auth Button -->
                <div class="hidden lg:block">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('admin.dashboard') }}"
                                class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white transition-all bg-slate-900 rounded-full hover:bg-slate-800 hover:shadow-lg hover:-translate-y-0.5">
                                Ke Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white transition-all bg-indigo-600 rounded-full hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5">
                                Login SIAKAD
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </a>
                        @endauth
                    @endif
                </div>

                <!-- Hamburger Button (Mobile) -->
                <div class="lg:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-slate-600 hover:text-indigo-600 p-2 focus:outline-none">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!mobileMenuOpen">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="mobileMenuOpen" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="lg:hidden absolute top-20 left-0 w-full bg-white/95 backdrop-blur-md shadow-xl border-t border-slate-100 max-h-[calc(100vh-5rem)] overflow-y-auto z-50" 
             style="display: none;"
             @click.away="mobileMenuOpen = false">
            <div class="px-4 py-6 space-y-1">
                @if($headerItems->isNotEmpty())
                    @foreach($headerItems as $item)
                        @if($item->children->isNotEmpty())
                            <div x-data="{ subOpen: false }" class="border-b border-slate-100/80 last:border-none">
                                <button @click="subOpen = !subOpen"
                                        class="flex items-center justify-between w-full px-3 py-3 rounded-xl text-base font-bold text-slate-700 hover:text-indigo-600 hover:bg-slate-50 transition-colors">
                                    <span>{{ $item->title }}</span>
                                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="subOpen ? 'rotate-180 text-indigo-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="subOpen" x-collapse class="pl-4 pb-2 space-y-1">
                                    @foreach($item->children as $child)
                                        <a href="{{ \App\Support\HostRelativeUrl::normalize($child->url) }}" target="{{ $child->target }}"
                                           class="block px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50 transition-colors">
                                            ↳ {{ $child->title }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ \App\Support\HostRelativeUrl::normalize($item->url) }}" target="{{ $item->target }}"
                               class="block px-3 py-3 rounded-xl text-base font-bold text-slate-700 hover:text-indigo-600 hover:bg-slate-50 transition-colors border-b border-slate-100/80 last:border-none">
                                {{ $item->title }}
                            </a>
                        @endif
                    @endforeach
                @else
                    <a href="{{ route('home') }}" class="block px-3 py-3 rounded-xl text-base font-bold text-slate-700 hover:text-indigo-600 hover:bg-slate-50 transition-colors">Beranda</a>
                    <a href="{{ route('public.blog.index') }}" class="block px-3 py-3 rounded-xl text-base font-bold text-slate-700 hover:text-indigo-600 hover:bg-slate-50 transition-colors">Berita & Pengumuman</a>
                @endif

                <div class="pt-4 border-t border-slate-200 mt-2">
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="block w-full text-center px-4 py-3 rounded-xl shadow-sm text-base font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors">Ke Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="block w-full text-center px-4 py-3 rounded-xl shadow-sm text-base font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">Login SIAKAD</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 py-12 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pb-8 border-b border-slate-700">
                {{-- Kolom 1: Identitas Sekolah --}}
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        @if($schoolLogo)
                            <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}" class="w-10 h-10 rounded-xl object-contain bg-white p-1">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-lg">
                                {{ substr($schoolName, 0, 1) }}
                            </div>
                        @endif
                        <span class="text-lg font-extrabold text-white">{{ $schoolName }}</span>
                    </div>
                    @if($school?->address)
                        <p class="text-sm text-slate-400 leading-relaxed">{{ $school->address }}</p>
                    @else
                        <p class="text-sm text-slate-500 italic">Sistem Informasi Akademik Sekolah.</p>
                    @endif
                </div>

                {{-- Kolom 2: Link Footer (Dinamis) --}}
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Navigasi</h4>
                    @if($footerItems->isNotEmpty())
                        <ul class="space-y-2">
                            @foreach($footerItems as $item)
                                <li>
                                    <a href="{{ \App\Support\HostRelativeUrl::normalize($item->url) }}" target="{{ $item->target }}"
                                       class="text-sm text-slate-400 hover:text-indigo-400 transition-colors">
                                        {{ $item->title }}
                                    </a>
                                </li>
                                @foreach($item->children as $child)
                                    <li class="pl-3">
                                        <a href="{{ \App\Support\HostRelativeUrl::normalize($child->url) }}" target="{{ $child->target }}"
                                           class="text-sm text-slate-500 hover:text-indigo-400 transition-colors">
                                            ↳ {{ $child->title }}
                                        </a>
                                    </li>
                                @endforeach
                            @endforeach
                        </ul>
                    @else
                        {{-- Fallback --}}
                        <ul class="space-y-2">
                            <li><a href="{{ route('home') }}" class="text-sm text-slate-400 hover:text-indigo-400 transition-colors">Beranda</a></li>
                            <li><a href="{{ route('public.blog.index') }}" class="text-sm text-slate-400 hover:text-indigo-400 transition-colors">Berita & Pengumuman</a></li>
                        </ul>
                    @endif
                </div>

                {{-- Kolom 3: Akses Portal --}}
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Portal</h4>
                    <ul class="space-y-2">
                        @auth
                            <li><a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-400 hover:text-indigo-400 transition-colors">Dashboard</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="text-sm text-slate-400 hover:text-indigo-400 transition-colors">Login SIAKAD</a></li>
                        @endauth
                        <li><a href="{{ route('public.blog.index') }}" class="text-sm text-slate-400 hover:text-indigo-400 transition-colors">Berita Sekolah</a></li>
                    </ul>
                </div>
            </div>

            {{-- Copyright --}}
            <div class="pt-6 flex flex-col sm:flex-row justify-between items-center gap-3">
                <p class="text-xs text-slate-500">
                    &copy; {{ date('Y') }} {{ $schoolName }}. Semua hak dilindungi.
                </p>
                <p class="text-xs text-slate-600">Powered by SIAKAD &mdash; Sistem Informasi Akademik Sekolah</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>

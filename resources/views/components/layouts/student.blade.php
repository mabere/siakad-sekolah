<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }} - Portal Siswa</title>
    <meta name="description"
        content="Portal siswa SIAKAD Sekolah untuk melihat jadwal, kehadiran, dan informasi akademik.">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#1e3a8a">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="bg-slate-50 font-sans antialiased text-slate-900">
    @php
        $currentSchool = null;
        try {
            $currentSchool = app(\App\Support\CurrentSchool::class)->get();
        } catch (\Throwable $e) {
            $currentSchool = null;
        }
        $schoolName = $currentSchool?->name ?? 'SIAKAD Sekolah';
        $schoolLogo = $currentSchool?->logo_url;
        $activeRole = session('active_role');
    @endphp

    <!-- Wrapper Utama Aplikasi -->
    <div x-data="{ sidebarOpen: true, userMenuOpen: false }" class="min-h-screen flex flex-col relative">

        <!-- Overlay Mobile saat Sidebar Terbuka -->
        <div x-show="sidebarOpen" x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs sm:hidden" @click="sidebarOpen = false"></div>

        <!-- Sidebar 100% Fixed pada Viewport Layar (Full w-64 vs Mini w-20 pada Desktop) -->
        <aside :class="sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full sm:translate-x-0 sm:w-20'"
            class="fixed inset-y-0 left-0 z-50 bg-indigo-950 text-white h-screen transition-transform duration-300 ease-in-out shadow-2xl flex flex-col justify-between">

            <!-- Area Scroll Menu Navigasi Terstruktur & Elegan -->
            <div class="flex-1 overflow-y-auto scrollbar-thin overflow-x-hidden">
                <div class="p-4 flex items-center justify-between border-b border-indigo-900/80">
                    <div class="flex items-center gap-3 min-w-0"
                        :class="!sidebarOpen ? 'sm:justify-center sm:w-full' : ''">
                        @if ($schoolLogo)
                            <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}"
                                class="w-10 h-10 rounded-xl object-contain bg-white p-1 ring-2 ring-indigo-500/30 flex-shrink-0 shadow-md">
                        @else
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-400 flex items-center justify-center font-black text-white text-base shadow-md flex-shrink-0">
                                {{ substr($schoolName, 0, 1) }}
                            </div>
                        @endif

                        <div class="min-w-0" x-show="sidebarOpen">
                            <h1 class="text-sm font-black tracking-tight text-white truncate leading-snug"
                                title="{{ $schoolName }}">{{ $schoolName }}</h1>
                            <p class="text-[11px] text-indigo-300 font-semibold truncate">Portal Siswa</p>
                        </div>
                    </div>

                    <button @click="sidebarOpen = false"
                        class="sm:hidden text-indigo-300 hover:text-white p-1 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Menu Groups Single-Open Accordion & Professional Icons -->
                <nav x-data="{
                    activeGroup: '{{ request()->routeIs('siswa.schedules') || request()->routeIs('siswa.attendances') || request()->routeIs('siswa.grades') || request()->routeIs('siswa.exams') ? 'academic' : (request()->routeIs('siswa.counseling') ? 'counseling' : 'dashboard') }}'
                }" class="mt-4 px-3 pb-6 space-y-2">

                    <!-- Group 1: Dashboard Siswa -->
                    <div class="relative group">
                        <a @click="activeGroup = 'dashboard'" href="{{ route('siswa.dashboard') }}"
                            class="flex items-center gap-3 px-3 py-2 text-[13px] font-semibold rounded-xl transition-colors {{ request()->routeIs('siswa.dashboard') ? 'text-white bg-indigo-800 shadow-md font-bold' : 'text-indigo-200 hover:bg-indigo-900 hover:text-white' }}"
                            :class="!sidebarOpen ? 'sm:justify-center sm:px-0' : ''">
                            <svg class="w-5 h-5 text-indigo-300 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
                            <span x-show="sidebarOpen" class="truncate text-left">Dashboard Siswa</span>
                        </a>

                        <!-- Tooltip Melayang saat Mini Mode -->
                        <div x-show="!sidebarOpen"
                            class="hidden sm:block absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-indigo-950 text-white text-xs font-bold rounded-lg shadow-2xl border border-indigo-800 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">
                            Dashboard Siswa
                        </div>
                    </div>

                    <!-- Group 2: Akademik & Ujian (Single-Open) -->
                    @if ($activeRole === 'Siswa')
                        <div class="relative group">
                            <button
                                @click="if(!sidebarOpen) { sidebarOpen = true; activeGroup = 'academic'; } else { activeGroup = activeGroup === 'academic' ? null : 'academic'; }"
                                type="button"
                                class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-xl text-left transition-colors"
                                :class="[activeGroup === 'academic' ? 'text-white bg-indigo-900/90' :
                                    'text-indigo-300 hover:text-white hover:bg-indigo-900/40', !sidebarOpen ?
                                    'sm:justify-center sm:px-0' : ''
                                ]">
                                <span class="flex items-center gap-2.5 min-w-0 text-left">
                                    <svg class="w-5 h-5 text-indigo-300 flex-shrink-0" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z">
                                        </path>
                                    </svg>
                                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap text-left">Akademik &
                                        Ujian</span>
                                </span>
                                <svg x-show="sidebarOpen"
                                    class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200"
                                    :class="activeGroup === 'academic' ? 'rotate-180' : ''" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Tooltip Melayang saat Mini Mode -->
                            <div x-show="!sidebarOpen"
                                class="hidden sm:block absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-indigo-950 text-white text-xs font-bold rounded-lg shadow-2xl border border-indigo-800 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">
                                Akademik & Ujian
                            </div>

                            <div x-show="activeGroup === 'academic' && sidebarOpen"
                                class="mt-1 space-y-1 pl-3 border-l-2 border-indigo-400/50 ml-4">
                                <a href="{{ route('siswa.schedules') }}"
                                    class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs('siswa.schedules') ? 'text-indigo-100 bg-indigo-900/80 font-bold' : 'text-indigo-200 hover:text-white hover:bg-indigo-900/50' }}">Jadwal
                                    Pelajaran</a>
                                <a href="{{ route('siswa.attendances') }}"
                                    class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs('siswa.attendances') ? 'text-indigo-100 bg-indigo-900/80 font-bold' : 'text-indigo-200 hover:text-white hover:bg-indigo-900/50' }}">Rekap
                                    Kehadiran</a>
                                <a href="{{ route('siswa.grades') }}"
                                    class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs('siswa.grades') ? 'text-indigo-100 bg-indigo-900/80 font-bold' : 'text-indigo-200 hover:text-white hover:bg-indigo-900/50' }}">Kartu
                                    Hasil Studi & Rapor</a>
                                <a href="{{ route('siswa.exams') }}"
                                    class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs('siswa.exams') ? 'text-indigo-100 bg-indigo-900/80 font-bold' : 'text-indigo-200 hover:text-white hover:bg-indigo-900/50' }}">Ujian
                                    Online CBT</a>
                            </div>
                        </div>
                    @endif

                    <!-- Group 3: Kedisiplinan & BK (Single-Open) -->
                    @if ($activeRole === 'Siswa')
                        <div class="relative group">
                            <button
                                @click="if(!sidebarOpen) { sidebarOpen = true; activeGroup = 'counseling'; } else { activeGroup = activeGroup === 'counseling' ? null : 'counseling'; }"
                                type="button"
                                class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-xl text-left transition-colors"
                                :class="[activeGroup === 'counseling' ? 'text-white bg-indigo-900/90' :
                                    'text-indigo-300 hover:text-white hover:bg-indigo-900/40', !sidebarOpen ?
                                    'sm:justify-center sm:px-0' : ''
                                ]">
                                <span class="flex items-center gap-2.5 min-w-0 text-left">
                                    <svg class="w-5 h-5 text-indigo-300 flex-shrink-0" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                        </path>
                                    </svg>
                                    <span x-show="sidebarOpen"
                                        class="truncate whitespace-nowrap text-left">Kedisiplinan & BK</span>
                                </span>
                                <svg x-show="sidebarOpen"
                                    class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200"
                                    :class="activeGroup === 'counseling' ? 'rotate-180' : ''" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Tooltip Melayang saat Mini Mode -->
                            <div x-show="!sidebarOpen"
                                class="hidden sm:block absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-indigo-950 text-white text-xs font-bold rounded-lg shadow-2xl border border-indigo-800 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">
                                Kedisiplinan & BK
                            </div>

                            <div x-show="activeGroup === 'counseling' && sidebarOpen"
                                class="mt-1 space-y-1 pl-3 border-l-2 border-indigo-400/50 ml-4">
                                <a href="{{ route('siswa.counseling') }}"
                                    class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs('siswa.counseling') ? 'text-indigo-100 bg-indigo-900/80 font-bold' : 'text-indigo-200 hover:text-white hover:bg-indigo-900/50' }}">Kedisiplinan
                                    & Pelanggaran</a>
                            </div>
                        </div>
                    @endif
                </nav>
            </div>

            <!-- Sticky Sidebar Footer User Profile -->
            <div class="p-3 border-t border-indigo-900 bg-indigo-950/90 flex items-center justify-between gap-2 flex-shrink-0"
                :class="!sidebarOpen ? 'sm:justify-center' : ''">
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-2.5 min-w-0 group hover:opacity-90 transition-opacity"
                    :class="!sidebarOpen ? 'sm:justify-center' : ''">
                    <img src="{{ auth()->user()?->avatar_url }}" alt="Avatar"
                        class="w-8 h-8 rounded-full object-cover ring-2 ring-indigo-400 flex-shrink-0 shadow-xs">
                    <div class="min-w-0" x-show="sidebarOpen">
                        <p
                            class="text-xs font-bold text-white truncate leading-tight group-hover:text-indigo-200 transition-colors">
                            {{ auth()->user()?->name }}</p>
                        <p class="text-[10px] text-indigo-300 truncate">Siswa Aktif</p>
                    </div>
                </a>

                <div class="flex items-center gap-1 flex-shrink-0" x-show="sidebarOpen">
                    <a href="{{ route('profile.edit') }}"
                        class="p-1.5 text-indigo-300 hover:text-white rounded-lg hover:bg-indigo-900 transition-colors"
                        title="Edit Profil Saya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="p-1.5 text-indigo-300 hover:text-rose-300 rounded-lg hover:bg-indigo-900 transition-colors"
                            title="Logout / Keluar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area 100% Statis Tanpa Animasi Pergeseran Layar saat Perpindahan Halaman -->
        <div :class="sidebarOpen ? 'sm:ml-64' : 'sm:ml-20'" class="flex-1 flex flex-col min-w-0 min-h-screen">

            <!-- Top Header Navbar -->
            <header
                class="bg-white shadow-xs h-16 flex items-center justify-between px-4 sm:px-6 border-b border-slate-200 sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="text-slate-600 hover:text-slate-900 p-2 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none"
                        title="Buka/Tutup Sidebar">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h2 class="text-lg font-bold text-slate-800 truncate">{{ $title ?? 'Dashboard Siswa' }}</h2>
                </div>

                <!-- Right User Dropdown Menu -->
                <div class="flex items-center gap-2 sm:gap-4">
                    <livewire:role-switcher />

                    <div class="relative" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" type="button"
                            class="flex items-center gap-3 p-1.5 rounded-full hover:bg-slate-100 transition-colors border border-transparent hover:border-slate-200 focus:outline-none">
                            <img src="{{ auth()->user()?->avatar_url }}" alt="Avatar"
                                class="w-9 h-9 rounded-full object-cover ring-2 ring-indigo-600 shadow-sm">
                            <div class="hidden sm:block text-left pr-1">
                                <span
                                    class="block text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()?->name }}</span>
                                <span class="block text-[10px] text-indigo-700 font-bold">Siswa Aktif</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 hidden sm:block transition-transform duration-200"
                                :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu Card -->
                        <div x-show="userMenuOpen" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-2xl border border-slate-200 py-2 z-50 text-slate-700"
                            style="display: none;">

                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()?->name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()?->email }}</p>
                            </div>

                            <div class="py-1">
                                <a href="{{ route('profile.edit') }}"
                                    class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-indigo-700 transition-colors">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                    Edit Profil Saya
                                </a>
                            </div>

                            <div class="border-t border-slate-100 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors">
                                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                            </path>
                                        </svg>
                                        Logout / Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Slot (pb-16 untuk memberikan ruang bagi fixed footer) -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-16">
                {{ $slot }}
            </main>

            <!-- Seksi Fixed Viewport Footer Aplikasi Utama (Terpaku di Dasar Layar Browser dengan Glassmorphism) -->
            <footer :class="sidebarOpen ? 'sm:ml-64' : 'sm:ml-20'"
                class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-4 sm:px-6 py-2.5 text-slate-500 text-xs flex flex-col sm:flex-row items-center justify-between gap-1.5 z-20 shadow-xs">
                <div>
                    <p class="font-semibold text-slate-700">© 2026 {{ $schoolName }}. Hak Cipta Dilindungi
                        Undang-Undang.</p>
                </div>
                <div class="flex items-center gap-3 text-[11px]">
                    <span
                        class="px-2.5 py-0.5 bg-indigo-50 text-indigo-800 border border-indigo-200 rounded-full font-bold">Kurikulum
                        Merdeka</span>
                    <span class="font-mono text-slate-400">v1.0.0</span>
                </div>
            </footer>
        </div>
    </div>

    @livewireScripts
</body>

</html>

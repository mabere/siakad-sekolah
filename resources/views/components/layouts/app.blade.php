<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>
        <meta name="description" content="Portal administrasi SIAKAD Sekolah untuk pengelolaan data dan layanan akademik.">
        <meta name="robots" content="noindex, nofollow, noarchive">
        <meta name="theme-color" content="#1e3a8a">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-rich-text::styles />

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
            
            $activePrefix = match(true) {
                in_array($activeRole, ['Super Admin', 'Admin Sekolah']) => 'admin',
                $activeRole === 'Kepala Sekolah' => 'kepsek',
                str_starts_with((string)$activeRole, 'Wakasek') => 'wakasek',
                $activeRole === 'Staf Tata Usaha' => 'tu',
                default => 'admin',
            };
        @endphp

        <!-- Wrapper Utama Aplikasi -->
        <div x-data="{ sidebarOpen: true, userMenuOpen: false }" class="min-h-screen flex flex-col relative">
            
            <!-- Overlay Mobile saat Sidebar Terbuka -->
            <div x-show="sidebarOpen" x-transition.opacity 
                 class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs sm:hidden"
                 @click="sidebarOpen = false"></div>

            <!-- Sidebar 100% Fixed pada Viewport Layar (Full w-64 vs Mini w-20 pada Desktop) -->
            <aside :class="sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full sm:translate-x-0 sm:w-20'" 
                   class="fixed inset-y-0 left-0 z-50 bg-slate-900 text-white h-screen transition-transform duration-300 ease-in-out shadow-2xl flex flex-col justify-between">
                
                <!-- Area Scroll Menu Navigasi Terstruktur & Elegan -->
                <div class="flex-1 overflow-y-auto scrollbar-thin overflow-x-hidden">
                    <div class="p-4 flex items-center justify-between border-b border-slate-800/80">
                        <div class="flex items-center gap-3 min-w-0" :class="!sidebarOpen ? 'sm:justify-center sm:w-full' : ''">
                            @if($schoolLogo)
                                <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}" class="w-10 h-10 rounded-xl object-contain bg-white p-1 ring-2 ring-indigo-500/30 flex-shrink-0 shadow-md">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center font-black text-white text-base shadow-md flex-shrink-0">
                                    {{ substr($schoolName, 0, 1) }}
                                </div>
                            @endif
                            
                            <div class="min-w-0" x-show="sidebarOpen">
                                <h1 class="text-sm font-black tracking-tight text-white truncate leading-snug" title="{{ $schoolName }}">{{ $schoolName }}</h1>
                                <p class="text-[11px] text-slate-400 font-semibold truncate">Portal Administrator</p>
                            </div>
                        </div>

                        <button @click="sidebarOpen = false" class="sm:hidden text-slate-400 hover:text-white p-1 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <nav x-data="{ 
                        activeGroup: '{{ request()->routeIs($activePrefix . '.master.*') ? 'master' : (request()->routeIs($activePrefix . '.academic.*') ? 'academic' : (request()->routeIs($activePrefix . '.ppdb.*') ? 'ppdb' : (request()->routeIs($activePrefix . '.users.*') || request()->routeIs($activePrefix . '.settings') ? 'system' : (request()->routeIs($activePrefix . '.cms.*') ? 'cms' : 'dashboard')))) }}' 
                    }" class="mt-4 px-3 pb-6 space-y-2">
                        
                        <!-- Group 1: Dashboard Utama -->
                        <div class="relative group">
                            <a @click="activeGroup = 'dashboard'" href="{{ route($activePrefix . '.dashboard') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-[13px] font-semibold rounded-xl transition-colors {{ request()->routeIs($activePrefix . '.dashboard') ? 'text-white bg-indigo-600 shadow-md font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                               :class="!sidebarOpen ? 'sm:justify-center sm:px-0' : ''">
                                <svg class="w-5 h-5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                <span x-show="sidebarOpen" class="truncate text-left">Dashboard Admin</span>
                            </a>

                            <!-- Tooltip Melayang saat Mini Mode -->
                            <div x-show="!sidebarOpen" class="hidden sm:block absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-900 text-white text-xs font-bold rounded-lg shadow-2xl border border-slate-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">
                                Dashboard Admin
                            </div>
                        </div>

                        <!-- Group 2: Master Data (Single-Open) -->
                        @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Wakasek Kurikulum', 'Wakasek Kesiswaan']))
                        <div class="relative group">
                            <button @click="if(!sidebarOpen) { sidebarOpen = true; activeGroup = 'master'; } else { activeGroup = activeGroup === 'master' ? null : 'master'; }" type="button" 
                                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-xl text-left transition-colors"
                                    :class="[activeGroup === 'master' ? 'text-white bg-slate-800/90' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40', !sidebarOpen ? 'sm:justify-center sm:px-0' : '']">
                                <span class="flex items-center gap-2.5 min-w-0 text-left">
                                    <svg class="w-5 h-5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap text-left">Master Data</span>
                                </span>
                                <svg x-show="sidebarOpen" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="activeGroup === 'master' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <!-- Tooltip Melayang saat Mini Mode -->
                            <div x-show="!sidebarOpen" class="hidden sm:block absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-900 text-white text-xs font-bold rounded-lg shadow-2xl border border-slate-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">
                                Master Data
                            </div>

                            <div x-show="activeGroup === 'master' && sidebarOpen" class="mt-1 space-y-1 pl-3 border-l-2 border-indigo-500/50 ml-4">
                                @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Wakasek Kurikulum']))
                                    <div class="px-3 pt-1 pb-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">Tahun Ajaran</div>
                                    <a href="{{ route($activePrefix . '.master.academic-years.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.master.academic-years.*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Tahun Ajaran</a>

                                    <div class="px-3 pt-2 pb-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">Master Kurikulum</div>
                                    <a href="{{ route($activePrefix . '.master.majors.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.master.majors.*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Jurusan</a>
                                    <a href="{{ route($activePrefix . '.master.subjects.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.master.subjects.*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Mata Pelajaran</a>

                                    <div class="px-3 pt-2 pb-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">Sumber Daya Pengajar</div>
                                    <a href="{{ route($activePrefix . '.master.teachers.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.master.teachers.*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Data Guru</a>

                                    <div class="px-3 pt-2 pb-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">Profil Kelas</div>
                                    <a href="{{ route($activePrefix . '.master.classrooms.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.master.classrooms.*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Data Kelas</a>
                                @endif
                                @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Wakasek Kesiswaan']))
                                    <a href="{{ route($activePrefix . '.master.students.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.master.students.*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Data Siswa</a>
                                    <a href="{{ route($activePrefix . '.master.violations.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.master.violations.*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Data Pelanggaran</a>
                                    <a href="{{ route($activePrefix . '.master.extracurriculars.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.master.extracurriculars.*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Ekstrakurikuler</a>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Group 3: Operasional Akademik (Single-Open) -->
                        @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Wakasek Kurikulum', 'Wakasek Kesiswaan', 'Staf Tata Usaha']))
                        <div class="relative group">
                            <button @click="if(!sidebarOpen) { sidebarOpen = true; activeGroup = 'academic'; } else { activeGroup = activeGroup === 'academic' ? null : 'academic'; }" type="button" 
                                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-xl text-left transition-colors"
                                    :class="[activeGroup === 'academic' ? 'text-white bg-slate-800/90' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40', !sidebarOpen ? 'sm:justify-center sm:px-0' : '']">
                                <span class="flex items-center gap-2.5 min-w-0 text-left">
                                    <svg class="w-5 h-5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap text-left">Data Akademik</span>
                                </span>
                                <svg x-show="sidebarOpen" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="activeGroup === 'academic' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <!-- Tooltip Melayang saat Mini Mode -->
                            <div x-show="!sidebarOpen" class="hidden sm:block absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-900 text-white text-xs font-bold rounded-lg shadow-2xl border border-slate-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">
                                Data Akademik
                            </div>

                            <div x-show="activeGroup === 'academic' && sidebarOpen" class="mt-1 space-y-1 pl-3 border-l-2 border-indigo-500/50 ml-4">
                                @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Wakasek Kurikulum', 'Staf Tata Usaha']))
                                    <a href="{{ route($activePrefix . '.academic.rombel') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.academic.rombel') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Rombongan Belajar</a>
                                    <a href="{{ route($activePrefix . '.academic.schedules') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.academic.schedules') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Jadwal Pelajaran</a>
                                    @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Wakasek Kurikulum']))
                                        <a href="{{ route($activePrefix . '.academic.curriculum-targets') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.academic.curriculum-targets*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Bank Kurikulum (CP/TP)</a>
                                        <a href="{{ route($activePrefix . '.academic.curriculum-guide') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.academic.curriculum-guide*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Panduan Perangkat AI</a>
                                    @endif
                                    <a href="{{ route($activePrefix . '.academic.grades') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.academic.grades') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Penilaian & Rapor</a>
                                    <a href="{{ route($activePrefix . '.academic.report-cards') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.academic.report-cards*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Cetak Rapor Digital</a>
                                    <a href="{{ route($activePrefix . '.academic.ledger') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.academic.ledger') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Ledger Nilai</a>
                                    <a href="{{ route($activePrefix . '.academic.promotion') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.academic.promotion') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Kenaikan Kelas</a>
                                @endif
                                @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Wakasek Kesiswaan']))
                                    <a href="{{ route($activePrefix . '.academic.attendances') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.academic.attendances') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Presensi Siswa</a>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Group PPDB -->
                        @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Staf Tata Usaha', 'Panitia PPDB']))
                        <div class="relative group">
                            <button @click="if(!sidebarOpen) { sidebarOpen = true; activeGroup = 'ppdb'; } else { activeGroup = activeGroup === 'ppdb' ? null : 'ppdb'; }" type="button" class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-xl text-left transition-colors" :class="[activeGroup === 'ppdb' ? 'text-white bg-slate-800/90' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40', !sidebarOpen ? 'sm:justify-center sm:px-0' : '']">
                                <span class="flex items-center gap-2.5 min-w-0 text-left"><svg class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7m-7-4v-4.5L12 14l7-3.5V17"></path></svg><span x-show="sidebarOpen" class="truncate whitespace-nowrap text-left">PPDB</span></span>
                                <svg x-show="sidebarOpen" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="activeGroup === 'ppdb' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="activeGroup === 'ppdb' && sidebarOpen" class="mt-1 space-y-1 pl-3 border-l-2 border-amber-500/50 ml-4">
                                <a href="{{ route($activePrefix . '.ppdb.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.ppdb.index') ? 'text-amber-300 bg-amber-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Periode PPDB</a>
                                <a href="{{ route($activePrefix . '.ppdb.applications') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.ppdb.applications') ? 'text-amber-300 bg-amber-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Pendaftar PPDB</a>
                            </div>
                        </div>
                        @endif

                        <!-- Group 4: Keuangan & SPP (Single-Open) -->
                        @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Staf Tata Usaha']))
                        <div class="relative group">
                            <button @click="if(!sidebarOpen) { sidebarOpen = true; activeGroup = 'finance'; } else { activeGroup = activeGroup === 'finance' ? null : 'finance'; }" type="button" 
                                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-xl text-left transition-colors"
                                    :class="[activeGroup === 'finance' ? 'text-white bg-slate-800/90' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40', !sidebarOpen ? 'sm:justify-center sm:px-0' : '']">
                                <span class="flex items-center gap-2.5 min-w-0 text-left">
                                    <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap text-left">Keuangan & SPP</span>
                                </span>
                                <svg x-show="sidebarOpen" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="activeGroup === 'finance' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div x-show="activeGroup === 'finance' && sidebarOpen" class="mt-1 space-y-1 pl-3 border-l-2 border-emerald-500/50 ml-4">
                                <a href="{{ route($activePrefix . '.finance.categories') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.finance.categories*') ? 'text-emerald-300 bg-emerald-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Pos Tagihan SPP</a>
                                <a href="{{ route($activePrefix . '.finance.payments') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.finance.payments*') ? 'text-emerald-300 bg-emerald-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Kasir & Tagihan Siswa</a>
                            </div>
                        </div>
                        @endif

                        <!-- Group Layanan Tata Usaha -->
                        @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah', 'Staf Tata Usaha']))
                        <div class="relative group">
                            <a href="{{ route('tu.letters') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-[13px] font-semibold rounded-xl transition-colors {{ request()->routeIs('tu.letters*') ? 'text-white bg-indigo-600 shadow-md font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span x-show="sidebarOpen" class="truncate text-left">Layanan Surat TU</span>
                            </a>
                        </div>
                        @endif

                        <!-- Group Portal Orang Tua -->
                        @if($activeRole === 'Orang Tua')
                        <div class="relative group space-y-1">
                            <a href="{{ route('parent.dashboard') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-[13px] font-semibold rounded-xl transition-colors {{ request()->routeIs('parent.dashboard') ? 'text-white bg-indigo-600 shadow-md font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <span x-show="sidebarOpen" class="truncate text-left">Portal Orang Tua</span>
                            </a>
                            <a href="{{ route('parent.payments') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-[13px] font-semibold rounded-xl transition-colors {{ request()->routeIs('parent.payments*') ? 'text-white bg-emerald-600 shadow-md font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span x-show="sidebarOpen" class="truncate text-left">Bayar SPP Anak</span>
                            </a>
                        </div>
                        @endif

                        <!-- Group 5: Manajemen Konten (Single-Open) -->
                        @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Wakasek Humas']))
                        <div class="relative group">
                            <button @click="if(!sidebarOpen) { sidebarOpen = true; activeGroup = 'cms'; } else { activeGroup = activeGroup === 'cms' ? null : 'cms'; }" type="button" 
                                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-xl text-left transition-colors"
                                    :class="[activeGroup === 'cms' ? 'text-white bg-slate-800/90' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40', !sidebarOpen ? 'sm:justify-center sm:px-0' : '']">
                                <span class="flex items-center gap-2.5 min-w-0 text-left">
                                    <svg class="w-5 h-5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15M9 11l3 3L22 4"></path></svg>
                                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap text-left">Manajemen Konten</span>
                                </span>
                                <svg x-show="sidebarOpen" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="activeGroup === 'cms' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <!-- Tooltip Melayang saat Mini Mode -->
                            <div x-show="!sidebarOpen" class="hidden sm:block absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-900 text-white text-xs font-bold rounded-lg shadow-2xl border border-slate-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">
                                Manajemen Konten
                            </div>

                            <div x-show="activeGroup === 'cms' && sidebarOpen" class="mt-1 space-y-1 pl-3 border-l-2 border-indigo-500/50 ml-4">
                                <a href="{{ route($activePrefix . '.cms.posts') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.cms.posts*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Artikel & Berita</a>
                                <a href="{{ route($activePrefix . '.cms.categories') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.cms.categories*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Kategori Konten</a>
                                <a href="{{ route($activePrefix . '.cms.sliders') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.cms.sliders*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Slider Beranda</a>
                                <a href="{{ route($activePrefix . '.cms.pages.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.cms.pages*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Halaman Statis</a>
                                <a href="{{ route($activePrefix . '.cms.menus.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.cms.menus*') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Menu Navigasi</a>
                            </div>
                        </div>
                        @endif

                        <!-- Group 5: Sistem & Pengguna (Single-Open) -->
                        @if(in_array($activeRole, ['Super Admin', 'Admin Sekolah']))
                        <div class="relative group">
                            <button @click="if(!sidebarOpen) { sidebarOpen = true; activeGroup = 'system'; } else { activeGroup = activeGroup === 'system' ? null : 'system'; }" type="button" 
                                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold uppercase tracking-wider rounded-xl text-left transition-colors"
                                    :class="[activeGroup === 'system' ? 'text-white bg-slate-800/90' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40', !sidebarOpen ? 'sm:justify-center sm:px-0' : '']">
                                <span class="flex items-center gap-2.5 min-w-0 text-left">
                                    <svg class="w-5 h-5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span x-show="sidebarOpen" class="truncate whitespace-nowrap text-left">Sistem & Pengguna</span>
                                </span>
                                <svg x-show="sidebarOpen" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="activeGroup === 'system' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <!-- Tooltip Melayang saat Mini Mode -->
                            <div x-show="!sidebarOpen" class="hidden sm:block absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-900 text-white text-xs font-bold rounded-lg shadow-2xl border border-slate-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">
                                Sistem & Pengguna
                            </div>

                            <div x-show="activeGroup === 'system' && sidebarOpen" class="mt-1 space-y-1 pl-3 border-l-2 border-indigo-500/50 ml-4">
                                <a href="{{ route($activePrefix . '.users.index') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.users.index') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Manajemen Pengguna</a>
                                <a href="{{ route($activePrefix . '.users.generator') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.users.generator') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Generator Akun</a>
                                <a href="{{ route($activePrefix . '.settings') }}" class="block px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors {{ request()->routeIs($activePrefix . '.settings') ? 'text-indigo-300 bg-indigo-950/60 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">Profil Sekolah & Sistem</a>
                            </div>
                        </div>
                        @endif
                    </nav>
                </div>

                <!-- Sticky Sidebar Footer User Profile -->
                <div class="p-3 border-t border-slate-800 bg-slate-950/90 flex items-center justify-between gap-2 flex-shrink-0" :class="!sidebarOpen ? 'sm:justify-center' : ''">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 min-w-0 group hover:opacity-90 transition-opacity" :class="!sidebarOpen ? 'sm:justify-center' : ''">
                        <img src="{{ auth()->user()?->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover ring-2 ring-teal-500 flex-shrink-0 shadow-xs">
                        <div class="min-w-0" x-show="sidebarOpen">
                            <p class="text-xs font-bold text-white truncate leading-tight group-hover:text-teal-300 transition-colors">{{ auth()->user()?->name }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()?->roles->pluck('name')->first() ?? 'Administrator' }}</p>
                        </div>
                    </a>

                    <div class="flex items-center gap-1 flex-shrink-0" x-show="sidebarOpen">
                        <a href="{{ route('profile.edit') }}" class="p-1.5 text-slate-400 hover:text-teal-300 rounded-lg hover:bg-slate-800 transition-colors" title="Edit Profil Saya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition-colors" title="Logout / Keluar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area 100% Statis Tanpa Animasi Pergeseran Layar saat Perpindahan Halaman -->
            <div :class="sidebarOpen ? 'sm:ml-64' : 'sm:ml-20'" 
                 class="flex-1 flex flex-col min-w-0 min-h-screen">
                
                <!-- Top Header Navbar -->
                <header class="bg-white shadow-xs h-16 flex items-center justify-between px-4 sm:px-6 border-b border-slate-200 sticky top-0 z-30">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-slate-600 hover:text-slate-900 p-2 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none" title="Buka/Tutup Sidebar">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h2 class="text-lg font-bold text-slate-800 truncate">{{ $title ?? 'Dashboard' }}</h2>
                    </div>
                    
                    <!-- Right User Dropdown Menu -->
                    <div class="flex items-center gap-2 sm:gap-4">
                        <livewire:role-switcher />
                        
                        <div class="relative" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" type="button" class="flex items-center gap-3 p-1.5 rounded-full hover:bg-slate-100 transition-colors border border-transparent hover:border-slate-200 focus:outline-none">
                            <img src="{{ auth()->user()?->avatar_url }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover ring-2 ring-teal-600 shadow-sm">
                            <div class="hidden sm:block text-left pr-1">
                                <span class="block text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()?->name }}</span>
                                <span class="block text-[10px] text-slate-500 font-semibold">{{ auth()->user()?->roles->pluck('name')->first() ?? 'Administrator' }}</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 hidden sm:block transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <!-- Dropdown Menu Card -->
                        <div x-show="userMenuOpen" 
                            x-transition:enter="transition ease-out duration-100" 
                            x-transition:enter-start="transform opacity-0 scale-95" 
                            x-transition:enter-end="transform opacity-100 scale-100" 
                            x-transition:leave="transition ease-in duration-75" 
                            x-transition:leave-start="transform opacity-100 scale-100" 
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-2xl border border-slate-200 py-2 z-50 text-slate-700" style="display: none;">
                            
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()?->name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()?->email }}</p>
                            </div>

                            <div class="py-1">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition-colors">
                                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    Edit Profil Saya
                                </a>
                            </div>

                            <div class="border-t border-slate-100 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors">
                                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
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
                    @if (isset($header))
                        <div class="mb-6">
                            {{ $header }}
                        </div>
                    @endif
                    
                    {{ $slot }}
                </main>

                <!-- Seksi Fixed Viewport Footer Aplikasi Utama (Terpaku di Dasar Layar Browser dengan Glassmorphism) -->
                <footer :class="sidebarOpen ? 'sm:ml-64' : 'sm:ml-20'"
                        class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-4 sm:px-6 py-2.5 text-slate-500 text-xs flex flex-col sm:flex-row items-center justify-between gap-1.5 z-20 shadow-xs">
                    <div>
                        <p class="font-semibold text-slate-700">© 2026 {{ $schoolName }}. Hak Cipta Dilindungi Undang-Undang.</p>
                    </div>
                    <div class="flex items-center gap-3 text-[11px]">
                        <span class="px-2.5 py-0.5 bg-teal-50 text-teal-800 border border-teal-200 rounded-full font-bold">Kurikulum Merdeka</span>
                        <span class="font-mono text-slate-400">v1.0.0</span>
                    </div>
                </footer>
            </div>
        </div>

        @livewireScripts
    </body>
</html>


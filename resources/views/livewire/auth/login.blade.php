<div class="flex items-center justify-center min-h-[calc(100vh-5rem)] pt-24 pb-12 px-4 sm:px-6 relative overflow-hidden bg-slate-50">
    <!-- Background Decoration -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-indigo-200/40 blur-3xl"></div>
        <div class="absolute bottom-0 -left-24 w-96 h-96 rounded-full bg-blue-200/40 blur-3xl"></div>
    </div>

    <!-- Login Card -->
    <div class="relative z-10 w-full max-w-md">
        <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-2xl p-8 sm:p-10 border border-white">
            
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-600 to-blue-500 shadow-xl shadow-indigo-500/20 text-white font-black text-3xl mb-6 transform -rotate-3 hover:rotate-0 transition-transform">
                    S
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Portal SIAKAD</h1>
                <p class="text-sm text-slate-500 mt-2 font-medium">Masuk untuk mengelola aktivitas akademik</p>
            </div>

            <form wire:submit="login" class="space-y-6">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input wire:model="email" type="email" id="email" 
                            class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm shadow-sm placeholder-slate-400
                            focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-medium text-slate-700" 
                            placeholder="admin@siakad.test">
                    </div>
                    @error('email') <span class="text-rose-500 text-xs font-bold mt-2 block">{{ $message }}</span> @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-bold text-slate-700 mb-2">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input wire:model="password" type="password" id="password" autocomplete="current-password" 
                            class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm shadow-sm placeholder-slate-400
                            focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-medium text-slate-700" 
                            placeholder="••••••••">
                    </div>
                    @error('password') <span class="text-rose-500 text-xs font-bold mt-2 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-between pt-1">
                    <div class="flex items-center">
                        <input wire:model="remember" id="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600 focus:ring-offset-0 bg-white cursor-pointer transition-colors">
                        <label for="remember" class="ml-2 block text-sm font-semibold text-slate-600 cursor-pointer select-none">Ingat saya</label>
                    </div>
                    <a href="#" class="text-sm font-bold text-indigo-600 hover:text-indigo-500 transition-colors">Lupa sandi?</a>
                </div>

                <div class="pt-2">
                    <button type="submit" wire:loading.attr="disabled" wire:target="login" 
                        class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-500/30 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0 group">
                        <span wire:loading.remove wire:target="login">Masuk ke Sistem</span>
                        <svg wire:loading.remove wire:target="login" class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        
                        <span wire:loading wire:target="login" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memverifikasi...
                        </span>
                    </button>
                </div>
            </form>
            
        </div>
        
    </div>
</div>

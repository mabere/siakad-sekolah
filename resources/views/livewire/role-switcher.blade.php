<div>
    @if(count($availableRoles) > 0)
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" @click.away="open = false" type="button" class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg transition-colors">
            <div class="flex flex-col items-start">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider leading-none mb-0.5">Akses Saat Ini</span>
                <span class="text-xs font-bold text-indigo-700 leading-none">{{ $activeRole }}</span>
            </div>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>

        <!-- Dropdown Menu -->
        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 overflow-hidden" style="display: none;">
            <div class="px-4 py-2 bg-slate-50 border-b border-slate-100">
                <p class="text-xs font-bold text-slate-500">Ganti Peran Ke:</p>
            </div>
            <div class="py-1">
                @foreach($availableRoles as $role)
                    <button wire:click="switchRole('{{ $role }}')" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        {{ $role }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
    @else
        <div class="flex flex-col items-start px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-lg">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider leading-none mb-0.5">Akses Saat Ini</span>
            <span class="text-xs font-bold text-slate-700 leading-none">{{ $activeRole }}</span>
        </div>
    @endif
</div>

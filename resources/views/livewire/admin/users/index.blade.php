<div>
    <x-slot name="title">Manajemen Pengguna</x-slot>

    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('temporary_password'))
        <div class="mb-4 border border-amber-300 bg-amber-50 p-4 rounded-md">
            <p class="text-sm font-medium text-amber-900">Password sementara</p>
            <code class="mt-2 inline-block select-all rounded bg-white px-3 py-2 text-sm text-slate-900 border border-amber-200">{{ session('temporary_password') }}</code>
            <p class="mt-2 text-xs text-amber-800">Salin dan sampaikan melalui kanal yang aman. Password ini hanya muncul sekali.</p>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
        <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center bg-slate-50 rounded-t-lg gap-4">
            <h2 class="text-lg font-semibold text-slate-800">Daftar Akun Pengguna</h2>
            
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <select wire:model.live="filterRole" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border bg-white">
                    <option value="">Semua Peran (Role)</option>
                    @foreach($availableRoles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau email..." class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border w-full sm:w-64">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Pengguna</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Peran (Role)</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status Akses</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-slate-900">{{ $user->name }}</div>
                                        <div class="text-sm text-slate-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @foreach($user->roles as $role)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ str_contains(strtolower($role->name), 'admin') ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ str_contains(strtolower($role->name), 'guru') ? 'bg-indigo-100 text-indigo-800' : '' }}
                                        {{ str_contains(strtolower($role->name), 'siswa') ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ (!str_contains(strtolower($role->name), 'admin') && !str_contains(strtolower($role->name), 'guru') && !str_contains(strtolower($role->name), 'siswa')) ? 'bg-slate-100 text-slate-800' : '' }}
                                    ">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                @if($user->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex flex-wrap justify-end gap-2">
                                    @if (auth()->user()->hasRole('Super Admin') || (auth()->id() !== $user->id && ! $user->hasAnyRole(['Super Admin', 'Admin Sekolah'])))
                                        <button type="button" wire:click="editRoles({{ $user->id }})" class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 transition hover:border-indigo-300 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">Edit Role</button>
                                    @endif

                                    <button type="button" wire:click="resetPassword({{ $user->id }})" wire:loading.attr="disabled" wire:confirm="Buat password sementara baru untuk {{ $user->name }}?" class="inline-flex items-center rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-50">Reset Password</button>

                                    @if(auth()->id() !== $user->id)
                                        @if($user->is_active)
                                            <button type="button" wire:click="toggleActiveStatus({{ $user->id }})" wire:confirm="Nonaktifkan akun ini? Pengguna tidak akan bisa login." class="inline-flex items-center rounded-md border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1">Nonaktifkan</button>
                                        @else
                                            <button type="button" wire:click="toggleActiveStatus({{ $user->id }})" class="inline-flex items-center rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">Aktifkan</button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-slate-200">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Edit Role Modal -->
    @if($isEditModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeEditModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">
                                    Edit Peran (Role) untuk {{ $editingUserName }}
                                </h3>
                                <div class="mt-4">
                                    <p class="text-sm text-slate-500 mb-4">
                                        Pilih peran yang ingin diberikan kepada pengguna ini. Anda bisa memilih lebih dari satu peran.
                                    </p>
                                    
                                    <div class="space-y-3">
                                        @foreach($availableRoles as $role)
                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="role-{{ $role->id }}" wire:model="selectedRoles" type="checkbox" value="{{ $role->name }}" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-slate-300 rounded">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="role-{{ $role->id }}" class="font-medium text-slate-700">{{ $role->name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="updateRoles" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Perubahan
                        </button>
                        <button type="button" wire:click="closeEditModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

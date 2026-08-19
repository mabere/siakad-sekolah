<div>
    <x-slot name="title">Generator Akun Pengguna</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-800">Pembuatan Akun Otomatis</h2>
        <p class="text-slate-500 text-sm mt-1">Setiap proses membuat maksimal 25 akun agar penggunaan memory tetap stabil.</p>
    </div>

    @if(count($generatedCredentials) > 0)
        <div class="mb-6 rounded-lg border border-amber-300 bg-amber-50 p-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-amber-900">Kredensial sementara</h3>
                    <p class="mt-1 text-xs text-amber-800">Salin sekarang dan bagikan melalui kanal aman. Daftar ini tidak disimpan sebagai teks biasa.</p>
                </div>
                <button wire:click="clearGeneratedCredentials" class="text-sm font-medium text-amber-900 hover:underline">Tutup</button>
            </div>
            <div class="mt-4 overflow-x-auto rounded border border-amber-200 bg-white">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Nama</th>
                            <th class="px-3 py-2">Email</th>
                            <th class="px-3 py-2">Password</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($generatedCredentials as $credential)
                            <tr>
                                <td class="px-3 py-2">{{ $credential['name'] }}</td>
                                <td class="px-3 py-2 select-all">{{ $credential['email'] }}</td>
                                <td class="px-3 py-2 font-mono select-all">{{ $credential['password'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card Guru -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-slate-900">Akun Guru</h3>
                        <p class="text-sm text-slate-500">Guru yang belum punya akun</p>
                    </div>
                </div>
                <div class="text-3xl font-bold text-slate-700">
                    {{ $teacherCount }}
                </div>
            </div>
            
            @if (session()->has('message_teacher'))
                <div class="mb-4 text-sm font-medium text-emerald-600 bg-emerald-50 p-3 rounded">
                    {{ session('message_teacher') }}
                </div>
            @endif

            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-500 mb-4">Email memakai NIP bila tersedia. Password acak ditampilkan satu kali setelah proses selesai.</p>
                <button 
                    wire:click="generateTeachers" 
                    wire:confirm="Apakah Anda yakin ingin men-generate akun untuk {{ $teacherCount }} Guru?"
                    wire:loading.attr="disabled"
                    @if($teacherCount == 0) disabled @endif
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-slate-300 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="generateTeachers">Generate {{ $teacherCount }} Akun Guru</span>
                    <span wire:loading wire:target="generateTeachers">Memproses...</span>
                </button>
            </div>
        </div>

        <!-- Card Siswa -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"></path><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-slate-900">Akun Siswa</h3>
                        <p class="text-sm text-slate-500">Siswa yang belum punya akun</p>
                    </div>
                </div>
                <div class="text-3xl font-bold text-slate-700">
                    {{ $studentCount }}
                </div>
            </div>
            
            @if (session()->has('message_student'))
                <div class="mb-4 text-sm font-medium text-emerald-600 bg-emerald-50 p-3 rounded">
                    {{ session('message_student') }}
                </div>
            @endif

            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-500 mb-4">Email memakai NISN atau NIS. Password acak ditampilkan satu kali setelah proses selesai.</p>
                <button 
                    wire:click="generateStudents" 
                    wire:confirm="Apakah Anda yakin ingin men-generate akun untuk {{ $studentCount }} Siswa?"
                    wire:loading.attr="disabled"
                    @if($studentCount == 0) disabled @endif
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-slate-300 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="generateStudents">Generate {{ $studentCount }} Akun Siswa</span>
                    <span wire:loading wire:target="generateStudents">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
</div>

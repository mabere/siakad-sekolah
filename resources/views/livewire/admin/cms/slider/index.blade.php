<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Slider Beranda
            </h2>
            <a href="{{ route('admin.cms.sliders.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Slider
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session()->has('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Urutan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Gambar</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Judul & Teks</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @forelse ($sliders as $index => $slider)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-medium">
                                            <div class="flex flex-col gap-1 items-center justify-center w-8">
                                                @if(!$loop->first)
                                                    <button wire:click="moveUp({{ $slider->id }})" class="text-slate-400 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg></button>
                                                @endif
                                                <span class="font-bold">{{ $slider->order }}</span>
                                                @if(!$loop->last)
                                                    <button wire:click="moveDown({{ $slider->id }})" class="text-slate-400 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></button>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="h-16 w-32 bg-slate-100 rounded overflow-hidden">
                                                <img src="{{ asset('storage/' . $slider->image_path) }}" class="h-full w-full object-cover">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-slate-900">{{ $slider->title }}</div>
                                            <div class="text-xs text-slate-500 line-clamp-2 mt-1">{{ $slider->description }}</div>
                                            @if($slider->button_text)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 mt-2">
                                                    Btn: {{ $slider->button_text }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button wire:click="toggleStatus({{ $slider->id }})" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $slider->is_active ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800' }}">
                                                {{ $slider->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.cms.sliders.edit', $slider->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            <button wire:click="delete({{ $slider->id }})" wire:confirm="Yakin ingin menghapus slider ini?" class="text-red-600 hover:text-red-900">Hapus</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                            Belum ada data slider beranda. <br> <a href="{{ route('admin.cms.sliders.create') }}" class="text-indigo-600 hover:underline mt-2 inline-block">Tambah Slider Baru</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

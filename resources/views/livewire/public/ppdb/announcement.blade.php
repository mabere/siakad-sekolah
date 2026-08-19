<div class="min-h-screen bg-slate-50 pt-24">
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('public.ppdb.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Kembali ke PPDB</a>

        <div class="mt-5 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Pengumuman PPDB</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $period->name }}</h1>
            <p class="mt-2 text-sm text-slate-600">Hasil seleksi yang diumumkan pada {{ $period->announcement_at?->format('d/m/Y H:i') }}. Untuk rincian pribadi, gunakan menu cek status dengan nomor pendaftaran dan kode akses.</p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">Diterima</span>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-700">Cadangan</span>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">Tidak diterima</span>
            </div>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-2">
            <select wire:model.live="pathwayFilter" class="rounded-md border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Semua jalur</option>
                @foreach ($period->pathways as $pathway)
                    <option value="{{ $pathway->id }}">{{ $pathway->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter" class="rounded-md border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Semua hasil</option>
                <option value="accepted">Diterima</option>
                <option value="waitlisted">Cadangan</option>
                <option value="rejected">Tidak diterima</option>
            </select>
        </div>

        <div class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Nomor pendaftaran</th><th class="px-4 py-3">Nama calon siswa</th><th class="px-4 py-3">Jalur</th><th class="px-4 py-3">Hasil seleksi</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($applications as $application)
                            @php($status = $application->selection_status)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700">{{ $application->application_number }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $application->candidate?->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $application->pathway?->name }}</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $status === 'accepted' ? 'bg-emerald-100 text-emerald-700' : ($status === 'waitlisted' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">{{ $status === 'accepted' ? 'Diterima' : ($status === 'waitlisted' ? 'Cadangan' : 'Tidak diterima') }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada hasil seleksi yang sesuai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 p-4">{{ $applications->links() }}</div>
        </div>

        <p class="mt-4 text-xs text-slate-500">Untuk rincian status pribadi, daftar ulang, atau informasi yang tidak tercantum di halaman ini, gunakan menu cek status dengan nomor pendaftaran dan kode akses.</p>
    </div>
</div>

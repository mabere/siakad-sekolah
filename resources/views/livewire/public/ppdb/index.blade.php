<div class="min-h-screen bg-slate-50 pt-24">
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Penerimaan Peserta Didik Baru</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Daftar PPDB {{ $school->name }}</h1>
            <p class="mt-3 text-slate-600">Pilih periode pendaftaran yang sedang tersedia. Pendaftaran online dan pendaftaran melalui panitia sekolah mengikuti proses verifikasi yang sama.</p>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('public.ppdb.guide') }}" class="rounded-md border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">Panduan alur PPDB</a>
            <a href="{{ route('public.ppdb.status') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cek status pendaftaran</a>
            <a href="{{ route('public.ppdb.reregistration') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Daftar ulang</a>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-2">
            @forelse ($periods as $period)
                <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-slate-900">{{ $period->name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $period->academicYear?->name }} · Level {{ $period->level }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">{{ $period->status === 'open' ? 'Dibuka' : 'Segera dibuka' }}</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Pendaftaran: {{ $period->registration_starts_at->format('d/m/Y H:i') }} s/d {{ $period->registration_ends_at->format('d/m/Y H:i') }}</p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($period->pathways as $pathway)
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $pathway->name }}</span>
                        @endforeach
                    </div>

                    @if ($period->status === 'open' && $period->is_registration_open)
                        <a href="{{ route('public.ppdb.register', $period->id) }}" class="mt-5 inline-flex rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Mulai pendaftaran</a>
                    @else
                        <span class="mt-5 inline-flex rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-500">Pendaftaran belum dibuka</span>
                    @endif
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500 md:col-span-2">Belum ada periode PPDB yang dipublikasikan.</div>
            @endforelse
        </div>

        @if ($announcementPeriods->isNotEmpty())
            <section class="mt-10">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Pengumuman hasil seleksi</h2>
                    <p class="mt-1 text-sm text-slate-600">Lihat hasil seleksi seluruh pendaftar pada periode yang telah diumumkan.</p>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach ($announcementPeriods as $period)
                        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="font-semibold text-slate-900">{{ $period->name }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $period->academicYear?->name }} · Diumumkan {{ $period->announcement_at?->format('d/m/Y H:i') }}</p>
                            <a href="{{ route('public.ppdb.announcement', $period->id) }}" class="mt-4 inline-flex rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Lihat pengumuman</a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>

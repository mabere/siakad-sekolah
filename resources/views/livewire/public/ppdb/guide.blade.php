<div class="min-h-screen bg-slate-50 pt-24">
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('public.ppdb.index') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Kembali ke PPDB</a>

        <section class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.25fr_0.75fr] lg:px-10 lg:py-10">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Panduan peserta</p>
                    <h1 class="mt-3 max-w-2xl text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Alur PPDB {{ $school->name }}</h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">Ikuti tahapan berikut agar pendaftaran, verifikasi, seleksi, sampai daftar ulang berjalan lancar. Pendaftaran tersedia secara online dan dapat dibantu panitia secara offline.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('public.ppdb.index') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Lihat periode PPDB</a>
                        <a href="{{ route('public.ppdb.status') }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cek status pendaftaran</a>
                    </div>
                </div>
                <div class="flex items-center justify-center rounded-xl bg-indigo-50 p-6">
                    <div class="w-full max-w-xs">
                        <div class="flex items-center justify-between text-xs font-semibold text-indigo-700"><span>Mulai</span><span>Selesai</span></div>
                        <div class="relative mt-3 h-2 rounded-full bg-indigo-200"><div class="absolute inset-y-0 left-0 w-full rounded-full bg-indigo-600"></div></div>
                        <div class="mt-4 grid grid-cols-3 gap-3 text-center text-xs text-slate-600"><div><span class="mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 font-bold text-white">1</span><p class="mt-2">Daftar</p></div><div><span class="mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 font-bold text-white">2</span><p class="mt-2">Verifikasi</p></div><div><span class="mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 font-bold text-white">3</span><p class="mt-2">Daftar ulang</p></div></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-8">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div><p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Alur utama</p><h2 class="mt-1 text-2xl font-bold text-slate-900">Enam langkah sampai selesai</h2></div>
                <p class="text-sm text-slate-500">Simpan nomor pendaftaran dan kode akses.</p>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['number' => '01', 'title' => 'Pilih periode', 'text' => 'Pastikan periode, jenjang, jalur, jadwal, biaya, dan persyaratan sesuai informasi sekolah.'],
                    ['number' => '02', 'title' => 'Isi pendaftaran', 'text' => 'Lengkapi data calon siswa, orang tua/wali, alamat, dan nomor kontak dengan benar.'],
                    ['number' => '03', 'title' => 'Unggah berkas', 'text' => 'Kirim dokumen sesuai jalur. Pastikan berkas terbaca dan formatnya sesuai ketentuan.'],
                    ['number' => '04', 'title' => 'Verifikasi', 'text' => 'Panitia memeriksa berkas dan pembayaran. Perbaiki data jika ada catatan revisi.'],
                    ['number' => '05', 'title' => 'Seleksi', 'text' => 'Pendaftar terverifikasi dinilai sesuai kriteria dan hasil seleksi ditetapkan panitia.'],
                    ['number' => '06', 'title' => 'Daftar ulang', 'text' => 'Calon yang diterima melakukan konfirmasi dan menunggu verifikasi daftar ulang.'],
                ] as $step)
                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start gap-4"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">{{ $step['number'] }}</span><div><h3 class="font-semibold text-slate-900">{{ $step['title'] }}</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ $step['text'] }}</p></div></div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-10 grid gap-5 lg:grid-cols-2">
            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">⌁</span><div><h2 class="text-lg font-bold text-slate-900">Pendaftaran online</h2><p class="mt-1 text-sm text-slate-500">Untuk pendaftaran mandiri dari rumah.</p></div></div>
                <ol class="mt-5 space-y-3 text-sm text-slate-700">
                    <li class="flex gap-3"><span class="font-semibold text-emerald-600">1.</span><span>Buka halaman periode PPDB yang sedang dibuka.</span></li>
                    <li class="flex gap-3"><span class="font-semibold text-emerald-600">2.</span><span>Pilih jalur dan isi formulir dengan data sesuai dokumen.</span></li>
                    <li class="flex gap-3"><span class="font-semibold text-emerald-600">3.</span><span>Unggah dokumen dan simpan nomor pendaftaran serta kode akses.</span></li>
                </ol>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-700">+</span><div><h2 class="text-lg font-bold text-slate-900">Pendaftaran offline</h2><p class="mt-1 text-sm text-slate-500">Untuk calon siswa yang membutuhkan bantuan panitia.</p></div></div>
                <ol class="mt-5 space-y-3 text-sm text-slate-700">
                    <li class="flex gap-3"><span class="font-semibold text-amber-600">1.</span><span>Datang atau hubungi panitia sesuai jadwal layanan sekolah.</span></li>
                    <li class="flex gap-3"><span class="font-semibold text-amber-600">2.</span><span>Bawa dokumen asli dan salinan sesuai jalur yang dipilih.</span></li>
                    <li class="flex gap-3"><span class="font-semibold text-amber-600">3.</span><span>Pastikan nomor pendaftaran dan kode akses diterima setelah input selesai.</span></li>
                </ol>
            </article>
        </section>

        <section class="mt-10 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Status proses</p><h2 class="mt-1 text-2xl font-bold text-slate-900">Arti setiap status</h2></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Pantau melalui cek status</span></div>
            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['label' => 'Menunggu verifikasi', 'class' => 'bg-amber-50 text-amber-800 border-amber-200', 'text' => 'Berkas atau pembayaran sedang diperiksa.'],
                    ['label' => 'Perlu perbaikan', 'class' => 'bg-orange-50 text-orange-800 border-orange-200', 'text' => 'Ada catatan yang perlu diperbaiki atau dilengkapi.'],
                    ['label' => 'Terverifikasi', 'class' => 'bg-emerald-50 text-emerald-800 border-emerald-200', 'text' => 'Syarat administrasi sudah dinyatakan lengkap.'],
                    ['label' => 'Hasil seleksi', 'class' => 'bg-indigo-50 text-indigo-800 border-indigo-200', 'text' => 'Menampilkan keputusan akhir setelah diumumkan.'],
                ] as $status)
                    <div class="rounded-lg border p-4 {{ $status['class'] }}"><p class="text-sm font-semibold">{{ $status['label'] }}</p><p class="mt-2 text-xs leading-5 opacity-80">{{ $status['text'] }}</p></div>
                @endforeach
            </div>
        </section>

        <section class="mt-10 grid gap-5 lg:grid-cols-[1fr_0.85fr]">
            <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Persiapan</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-900">Sebelum mengirim pendaftaran</h2>
                <ul class="mt-5 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span><span>Data identitas calon siswa sudah benar.</span></li>
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span><span>Nomor telepon dan email dapat dihubungi.</span></li>
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span><span>Dokumen sesuai persyaratan jalur.</span></li>
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span><span>File terbaca dan tidak melebihi ukuran maksimum.</span></li>
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span><span>Nomor pendaftaran dan kode akses disimpan.</span></li>
                    <li class="flex gap-3"><span class="text-emerald-600">✓</span><span>Jadwal verifikasi dan daftar ulang dicatat.</span></li>
                </ul>
            </article>
            <article class="rounded-xl border border-indigo-100 bg-indigo-50 p-6">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-700">Butuh bantuan?</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-900">Gunakan halaman yang tepat</h2>
                <div class="mt-5 space-y-3">
                    <a href="{{ route('public.ppdb.index') }}" class="flex items-center justify-between rounded-lg border border-indigo-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:border-indigo-400"><span>Lihat periode & jalur</span><span class="text-indigo-600">→</span></a>
                    <a href="{{ route('public.ppdb.status') }}" class="flex items-center justify-between rounded-lg border border-indigo-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:border-indigo-400"><span>Cek status pendaftaran</span><span class="text-indigo-600">→</span></a>
                    <a href="{{ route('public.ppdb.reregistration') }}" class="flex items-center justify-between rounded-lg border border-indigo-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:border-indigo-400"><span>Konfirmasi daftar ulang</span><span class="text-indigo-600">→</span></a>
                </div>
            </article>
        </section>

        <p class="mt-8 text-center text-xs leading-5 text-slate-500">Informasi jadwal, jalur, kuota, biaya, dan persyaratan mengikuti periode PPDB yang dipublikasikan sekolah.</p>
    </div>
</div>

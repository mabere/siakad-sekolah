@props([
    'title',
    'content',
    'updatedAt' => null,
])

<div class="pt-24 pb-16 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-indigo-600 transition-colors">Beranda</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-slate-800 font-medium">{{ $title }}</span>
        </nav>

        <div class="mb-8 border-b border-slate-200 pb-6">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">{{ $title }}</h1>
            @if ($updatedAt)
                <p class="mt-2 text-sm text-slate-500">
                    Diperbarui: {{ \Illuminate\Support\Carbon::parse($updatedAt)->format('d F Y') }}
                </p>
            @endif
        </div>

        <div class="prose prose-slate prose-lg max-w-none
            prose-headings:font-bold
            prose-a:text-indigo-600 prose-a:no-underline hover:prose-a:underline
            prose-img:rounded-xl prose-img:shadow-md
            [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:my-1">
            {!! \App\Support\SafeHtml::clean($content) !!}
        </div>
    </div>
</div>

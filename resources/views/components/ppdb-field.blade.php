@props(['label', 'model', 'type' => 'text'])

@php
    $fieldId = 'ppdb-'.str_replace(['.', '[', ']'], '-', $model);
    $hasError = $errors->has($model);
@endphp

<div>
    <label for="{{ $fieldId }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>
    <input
        id="{{ $fieldId }}"
        type="{{ $type }}"
        wire:model="{{ $model }}"
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif
        class="h-11 w-full rounded-md border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 {{ $hasError ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100' }}"
    >
    @error($model) <p id="{{ $fieldId }}-error" role="alert" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
</div>

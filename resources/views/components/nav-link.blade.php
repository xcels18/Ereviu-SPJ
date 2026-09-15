@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold text-brand-700 bg-brand-50 transition-colors duration-150'
    : 'inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

@props(['title', 'value', 'sub' => null, 'color' => 'indigo'])
@php
    $border = match ($color) {
        'green'  => 'border-green-500',
        'amber'  => 'border-amber-500',
        'sky'    => 'border-sky-500',
        'rose'   => 'border-rose-500',
        default  => 'border-indigo-500',
    };
@endphp
<div class="bg-white shadow-sm sm:rounded-lg p-6 border-l-4 {{ $border }}">
    <div class="text-sm text-gray-500">{{ $title }}</div>
    <div class="mt-1 text-3xl font-bold text-gray-900">{{ $value }}</div>
    @if ($sub) <div class="mt-1 text-xs text-gray-500">{{ $sub }}</div> @endif
</div>

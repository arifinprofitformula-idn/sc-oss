@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-6 py-3 text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 border-l-4 border-indigo-500 transition-colors duration-200'
            : 'flex items-center px-6 py-3 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors duration-200 border-l-4 border-transparent';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

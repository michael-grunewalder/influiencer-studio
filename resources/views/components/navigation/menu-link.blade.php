@props(['label', 'active' => false])

<a href="#" {{ $attributes->merge(['class' => 'bearny-codes-menu-item text-sm tracking-wide group']) }}>
    <span class="group-hover:translate-x-1 transition-transform duration-200">{{ $label }}</span>
    <x-icon name="o-chevron-right" class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-all text-bearny-codes-lime" />
</a>
@props(['label', 'icon' => null, 'link' => '#', 'active' => false])

<a href="{{ $link }}"
   {{ $attributes->merge(['class' => 'bearny-codes-menu-item group ' . ($active ? 'bearny-codes-menu-item-active text-white' : 'text-gray-400')]) }}
   wire:navigate>
    <div class="flex items-center gap-3">
        @if($icon)
            <x-icon name="{{ $icon }}" class="w-4 h-4 {{ $active ? 'text-primary' : 'group-hover:text-accent' }}" />
        @endif
        <span class="text-sm transition-transform duration-200 group-hover:translate-x-1">{{ $label }}</span>
    </div>
    <x-icon name="o-chevron-right" class="w-3 h-3 text-primary opacity-0 group-hover:opacity-100 transition-all" />
</a>
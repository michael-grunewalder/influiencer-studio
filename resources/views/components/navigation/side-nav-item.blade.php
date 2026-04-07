@props(['label', 'icon' => null, 'link' => '#', 'active' => false])
<?php
if ($active) {
    $text_color = 'bearny-codes-menu-item-active text-white'
}
else
{
    $text_color = 'text-gray-400';
}
?>
<a href="{{ $link }}" class="hover:text-primary bearny-codes-menu-item group {{ $text_color }}" wire:navigate>
    <div class="flex items-center gap-3">
        @if($icon)
            @svg("phosphor-$icon", 'w-4 h-4 hover:text-primary')
        @endif
        <span class="text-sm transition-transform duration-200 group-hover:translate-x-1">{{ $label }}</span>
    </div>
    <x-icon name="o-chevron-right" class="w-3 h-3 text-primary opacity-0 group-hover:opacity-100 transition-all" />
</a>
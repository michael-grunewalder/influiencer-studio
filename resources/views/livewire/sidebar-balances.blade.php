<div class="px-5 py-3 border-b border-base-300 flex flex-col gap-2 bg-base-100/30">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-[11px] font-extrabold tracking-widest text-slate-500 uppercase">Own Balance</span>
            <a href="/top-up" class="flex items-center justify-center w-4 h-4 rounded-full bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors" title="Top Up" wire:navigate>
                <x-icon name="o-plus" class="w-2.5 h-2.5" />
            </a>
        </div>
        <span class="font-mono text-xs font-black text-base-content">${{ number_format($userWallet, 2) }}</span>
    </div>
    
    <div class="flex items-center justify-between">
        <span class="text-[11px] font-extrabold tracking-widest text-slate-500 uppercase">Team Balance</span>
        <span class="font-mono text-xs font-black text-base-content">${{ number_format($teamBalance, 2) }}</span>
    </div>
</div>

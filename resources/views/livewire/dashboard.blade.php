<div class="flex flex-col lg:flex-row h-[calc(100vh-4rem)] -mx-6 -my-5 overflow-hidden bg-base-200"
     @if(collect($generationStates)->contains('status', 'generating')) wire:poll.2s="checkGenerationProgress" @endif>
    
    {{-- Left Sidebar: Influencers List --}}
    <aside class="w-full lg:w-80 border-r border-base-300 flex flex-col bg-base-100 flex-shrink-0">
        {{-- User Info Header --}}
        @if(auth()->check())
            <div class="p-4 border-b border-base-300 flex items-center gap-3 bg-base-100/50">
                @if(auth()->user()->avatar)
                    <div class="avatar">
                        <div class="w-10 h-10 rounded-full border border-base-300">
                            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="object-cover rounded-full" />
                        </div>
                    </div>
                @else
                    <div class="avatar placeholder">
                        <div class="bg-neutral text-neutral-content rounded-full w-10 h-10">
                            <span class="text-xs font-bold">{{ auth()->user()->initials() }}</span>
                        </div>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-sm text-base-content truncate">{{ auth()->user()->name }}</h4>
                    <div class="flex gap-2 mt-0.5">
                        <a href="{{ route('profile') }}" class="text-[10px] font-bold text-slate-500 hover:text-primary hover:underline" wire:navigate>Profile</a>
                        <span class="text-[10px] text-slate-400">•</span>
                        <a href="{{ route('logout') }}" class="text-[10px] font-bold text-slate-500 hover:text-error hover:underline">Logout</a>
                    </div>
                </div>
            </div>
        @endif

        <livewire:sidebar-balances />

        {{-- Team Selector --}}
        <div class="p-3 border-b border-base-300 bg-base-100/30">
            <x-select icon="o-users" :options="$this->teams" wire:model.live="selectedTeamId" class="select-sm w-full font-semibold text-xs" />
        </div>

        <div class="p-4 border-b border-base-300 flex justify-between items-center bg-base-100/50">
            <span class="font-extrabold text-xs uppercase tracking-widest text-slate-500">Influencers</span>
            <div class="flex items-center gap-1">
                <x-button icon="o-arrow-down-tray" class="btn-sm btn-ghost btn-circle" tooltip="Import Influencer" wire:click="$set('showImportModal', true)" />
                <x-button icon="o-plus" class="btn-sm btn-ghost btn-circle" tooltip="Add Influencer" link="/influencer/create" />
                <x-button icon="o-chevron-left" class="btn-sm btn-ghost btn-circle hidden lg:inline-flex" />
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto divide-y divide-base-200">
            @forelse($this->influencers as $i)
                <div wire:key="{{ $i->id }}" wire:click="selectInfluencer('{{ $i->id }}')" class="p-4 flex items-center justify-between cursor-pointer hover:bg-base-200/50 transition-colors {{ $selectedId === $i->id ? 'bg-base-200 border-l-4 border-primary' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="avatar">
                            <div class="w-10 h-10 rounded-full border border-base-300">
                                <img src="{{ $i->avatar }}" alt="{{ $i->name }}" class="object-cover rounded-full" />
                            </div>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-base-content leading-tight">{{ $i->name }}</h4>
                            <span class="text-[9px] font-extrabold uppercase tracking-wider {{ ($i->properties->gender ?? 'Female') === 'Female' ? 'text-pink-500' : 'text-blue-500' }}">
                                {{ $i->properties->gender ?? 'Female' }}
                            </span>
                        </div>
                    </div>
                    <div class="text-xs font-bold text-slate-400 bg-base-300 px-2 py-0.5 rounded-md">
                        @php
                            $score = 0;
                            if (!empty($i->name)) $score += 10;
                            if ($i->properties->age) $score += 10;
                            if ($i->properties->gender) $score += 10;
                            if (!empty($i->properties->niche)) $score += 10;
                            if (!empty($i->properties->backstory)) $score += 15;
                            if ($i->properties->character_sheet) $score += 10;
                            if ($i->properties->closeup) $score += 10;
                            if ($i->properties->detail_sheet) $score += 10;
                            if ($i->properties->location) $score += 5;
                            if ($i->properties->physical_description) $score += 10;
                        @endphp
                        {{ $score }}%
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500">
                    <p class="text-sm">Keine Influencer vorhanden.</p>
                </div>
            @endforelse
        </div>
    </aside>

    {{-- Right Main Pane: Selection Details --}}
    <main class="flex-1 flex flex-col overflow-y-auto p-4 md:p-6 min-w-0">
        
        @if(! $selectedId || ! $this->selectedInfluencer)
            {{-- Empty State --}}
            <div class="flex-1 flex flex-col items-center justify-center p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-base-300 flex items-center justify-center text-slate-400 mb-4 shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-base-content">No Influencer Selected</h3>
                <p class="text-slate-400 text-sm max-w-sm mt-1">Start by launching the wizard to build your first high-fidelity digital persona.</p>
                <x-button label="Add Influencer" icon="o-plus" class="btn-primary mt-6 shadow-lg shadow-primary/20" link="/influencer/create" />
            </div>
        @else
            {{-- Selected Influencer Profile Detail --}}
            <div class="space-y-6">
                
                {{-- Profile tabs (Profile, Photos, Videos) --}}
                <div class="flex gap-1 bg-base-300/40 p-1 rounded-xl w-fit">
                    <button wire:click="$set('currentTab', 'profile')" class="px-5 py-2 text-xs font-bold rounded-lg transition-all {{ $currentTab === 'profile' ? 'bg-base-100 text-base-content shadow-sm' : 'text-slate-500 hover:text-base-content' }}">Profile</button>
                    <button wire:click="$set('currentTab', 'photos')" class="px-5 py-2 text-xs font-bold rounded-lg transition-all {{ $currentTab === 'photos' ? 'bg-base-100 text-base-content shadow-sm' : 'text-slate-500 hover:text-base-content' }}">Photos</button>
                    <button wire:click="$set('currentTab', 'videos')" class="px-5 py-2 text-xs font-bold rounded-lg transition-all {{ $currentTab === 'videos' ? 'bg-base-100 text-base-content shadow-sm' : 'text-slate-500 hover:text-base-content' }}">Videos</button>
                </div>

                @if($currentTab === 'profile')
                    
                    {{-- Banner Card --}}
                    <div class="bg-base-100 border border-base-300 rounded-3xl p-6 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm">
                        <div class="flex flex-col md:flex-row items-center gap-5">
                            <label class="relative cursor-pointer group w-20 h-20 rounded-full overflow-hidden border border-base-300 shadow-sm">
                                <img src="{{ $this->selectedInfluencer->avatar }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                </div>
                                <input type="file" wire:model="uploaded_avatar" class="hidden" accept="image/*" />
                            </label>
                            
                            <div class="text-center md:text-left space-y-1">
                                <div class="flex flex-col md:flex-row items-center gap-2">
                                    <h3 class="text-2xl font-bold text-base-content tracking-tight leading-none">{{ $this->selectedInfluencer->name }}</h3>
                                    <x-button label="+ Add title" class="btn-xs btn-outline btn-ghost text-slate-500 rounded-lg" />
                                </div>
                                <div class="flex flex-wrap justify-center md:justify-start gap-1.5 pt-1">
                                    <span class="px-2.5 py-0.5 bg-pink-500/10 border border-pink-500/20 rounded-md text-[10px] font-bold text-pink-400 uppercase tracking-wider">{{ $this->selectedInfluencer->properties->gender ?? 'Female' }}</span>
                                    @foreach($this->selectedInfluencer->properties->niche ?? [] as $nc)
                                        <span class="px-2.5 py-0.5 bg-purple-550/10 border border-purple-550/20 rounded-md text-[10px] font-bold text-purple-400 uppercase tracking-wider">{{ $nc }}</span>
                                    @endforeach
                                    <span class="px-2.5 py-0.5 bg-base-300 border border-base-350 rounded-md text-[10px] font-bold text-base-content uppercase tracking-wider">Age {{ $this->selectedInfluencer->properties->age ?? '22' }}</span>
                                </div>
                                <p class="text-[11px] text-slate-400 font-bold pt-2 uppercase tracking-wide">{{ $this->profileCompleteness }}% profile complete</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <x-button label="Export" icon="o-arrow-up-tray" wire:click="exportInfluencer" class="btn-sm btn-outline rounded-xl" />
                            <x-button label="Delete" icon="o-trash" wire:click="$set('showDeleteConfirmModal', true)" class="btn-sm btn-error text-white rounded-xl" />
                        </div>
                    </div>

                    {{-- Image Sheets Grid --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        {{-- IMAGE (Avatar) --}}
                        <div class="flex flex-col">
                            <span class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2.5 uppercase">Image</span>
                            <div class="bg-base-100 border border-base-300 rounded-3xl overflow-hidden aspect-[4/5] relative group shadow-sm flex items-center justify-center">
                                @if(($generationStates['avatar']['status'] ?? '') === 'generating')
                                    {{-- Generating / Loading State --}}
                                    <div class="absolute inset-0 bg-base-100/80 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3 select-none">
                                        <div class="relative w-12 h-12 flex items-center justify-center">
                                            <div class="absolute inset-0 rounded-full border-4 border-primary/20"></div>
                                            <div class="absolute inset-0 rounded-full border-4 border-t-primary animate-spin"></div>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold tracking-wider text-slate-500 uppercase animate-pulse">
                                                @if(($generationStates['avatar']['queue_status'] ?? '') === 'IN_QUEUE')
                                                    In Queue...
                                                @elseif(($generationStates['avatar']['queue_status'] ?? '') === 'IN_PROGRESS')
                                                    Generating...
                                                @else
                                                    Generating...
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @elseif(($generationStates['avatar']['status'] ?? '') === 'failed')
                                    {{-- Failed State --}}
                                    <div class="absolute inset-0 bg-error/5 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3">
                                        <div class="w-10 h-10 rounded-full bg-error/10 border border-error/20 flex items-center justify-center text-error shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-base-content">Generation failed</h4>
                                            @if(isset($generationStates['avatar']['error']))
                                                <p class="text-[9px] text-slate-500 mt-1 line-clamp-2 max-w-[160px]" title="{{ $generationStates['avatar']['error'] }}">
                                                    {{ str_contains(strtolower($generationStates['avatar']['error']), 'content filter') ? 'Blocked by content safety filter.' : $generationStates['avatar']['error'] }}
                                                </p>
                                            @endif
                                        </div>
                                        <button type="button" wire:click="generateImage('avatar')" class="btn btn-xs btn-error text-white font-bold px-3 py-1 rounded-lg flex items-center gap-1 shadow-sm">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/>
                                            </svg>
                                            Try Again
                                        </button>
                                    </div>
                                @else
                                    {{-- Active Loader for instant browser feedback --}}
                                    <div wire:loading wire:target="generateImage('avatar')" class="absolute inset-0 bg-base-100/80 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3 select-none z-20">
                                        <div class="relative w-12 h-12 flex items-center justify-center">
                                            <div class="absolute inset-0 rounded-full border-4 border-primary/20"></div>
                                            <div class="absolute inset-0 rounded-full border-4 border-t-primary animate-spin"></div>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold tracking-wider text-slate-500 uppercase animate-pulse">Generating...</span>
                                        </div>
                                    </div>

                                    @if($this->selectedInfluencer->avatar)
                                        <img src="{{ $this->selectedInfluencer->avatar }}" class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                                            <div class="flex gap-1.5 justify-center">
                                                <button type="button" wire:click="generateImage('avatar')" class="btn btn-xs btn-ghost text-white border border-white/20">Regenerate</button>
                                                <label class="btn btn-xs btn-ghost text-white border border-white/20 cursor-pointer">
                                                    Replace
                                                    <input type="file" wire:model="uploaded_avatar" class="hidden" accept="image/*" />
                                                </label>
                                                <button type="button" wire:click="downloadImage('avatar')" class="btn btn-xs btn-ghost text-white border border-white/20">Download</button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-6 text-center space-y-4">
                                            <div class="w-12 h-12 rounded-full border border-dashed border-base-350 flex items-center justify-center mx-auto text-slate-400 shadow-inner">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div class="flex flex-col sm:flex-row gap-2 justify-center">
                                                <button type="button" wire:click="generateImage('avatar')" class="btn btn-sm btn-primary shadow shadow-primary/20">
                                                    Generate
                                                </button>
                                                <label class="btn btn-sm btn-outline cursor-pointer">
                                                    Upload
                                                    <input type="file" wire:model="uploaded_avatar" class="hidden" accept="image/*" />
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- CHARACTER SHEET --}}
                        <div class="flex flex-col">
                            <span class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2.5 uppercase">Character Sheet</span>
                            <div class="bg-base-100 border border-base-300 rounded-3xl overflow-hidden aspect-[16/9] relative group shadow-sm flex items-center justify-center">
                                {{-- Active Loader for instant browser feedback --}}
                                <div wire:loading wire:target="generateImage('character_sheet')" class="absolute inset-0 bg-base-100/80 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3 select-none z-20">
                                    <div class="relative w-12 h-12 flex items-center justify-center">
                                        <div class="absolute inset-0 rounded-full border-4 border-primary/20"></div>
                                        <div class="absolute inset-0 rounded-full border-4 border-t-primary animate-spin"></div>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold tracking-wider text-slate-500 uppercase animate-pulse">Generating...</span>
                                    </div>
                                </div>

                                @if(($generationStates['character_sheet']['status'] ?? '') === 'generating')
                                    {{-- Generating / Loading State --}}
                                    <div class="absolute inset-0 bg-base-100/80 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3 select-none">
                                        <div class="relative w-12 h-12 flex items-center justify-center">
                                            <div class="absolute inset-0 rounded-full border-4 border-primary/20"></div>
                                            <div class="absolute inset-0 rounded-full border-4 border-t-primary animate-spin"></div>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold tracking-wider text-slate-500 uppercase animate-pulse">
                                                @if(($generationStates['character_sheet']['queue_status'] ?? '') === 'IN_QUEUE')
                                                    In Queue...
                                                @elseif(($generationStates['character_sheet']['queue_status'] ?? '') === 'IN_PROGRESS')
                                                    Generating...
                                                @else
                                                    Generating...
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @elseif(($generationStates['character_sheet']['status'] ?? '') === 'failed')
                                    {{-- Failed State --}}
                                    <div class="absolute inset-0 bg-error/5 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3">
                                        <div class="w-10 h-10 rounded-full bg-error/10 border border-error/20 flex items-center justify-center text-error shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-base-content">Generation failed</h4>
                                            @if(isset($generationStates['character_sheet']['error']))
                                                <p class="text-[9px] text-slate-500 mt-1 line-clamp-2 max-w-[160px]" title="{{ $generationStates['character_sheet']['error'] }}">
                                                    {{ str_contains(strtolower($generationStates['character_sheet']['error']), 'content filter') ? 'Blocked by content safety filter.' : $generationStates['character_sheet']['error'] }}
                                                </p>
                                            @endif
                                        </div>
                                        <button type="button" wire:click="generateImage('character_sheet')" class="btn btn-xs btn-error text-white font-bold px-3 py-1 rounded-lg flex items-center gap-1 shadow-sm">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/>
                                            </svg>
                                            Try Again
                                        </button>
                                    </div>
                                @elseif($this->selectedInfluencer->properties->character_sheet)
                                    <img src="{{ $this->selectedInfluencer->properties->character_sheet }}" class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                                        <div class="flex gap-1.5 justify-center">
                                            <button type="button" wire:click="generateImage('character_sheet')" class="btn btn-xs btn-ghost text-white border border-white/20">Regenerate</button>
                                            <label class="btn btn-xs btn-ghost text-white border border-white/20 cursor-pointer">
                                                Replace
                                                <input type="file" wire:model="uploaded_character_sheet" class="hidden" accept="image/*" />
                                            </label>
                                            <button type="button" wire:click="downloadImage('character_sheet')" class="btn btn-xs btn-ghost text-white border border-white/20">Download</button>
                                        </div>
                                    </div>
                                @else
                                    <div class="p-6 text-center space-y-4">
                                        <div class="w-12 h-12 rounded-full border border-dashed border-base-350 flex items-center justify-center mx-auto text-slate-400 shadow-inner">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col sm:flex-row gap-2 justify-center">
                                            <button type="button" wire:click="generateImage('character_sheet')" class="btn btn-sm btn-primary shadow shadow-primary/20">
                                                Generate
                                            </button>
                                            <label class="btn btn-sm btn-outline cursor-pointer">
                                                Upload
                                                <input type="file" wire:model="uploaded_character_sheet" class="hidden" accept="image/*" />
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- CLOSE UPS (Closeup & Detail sheet) --}}
                        <div class="flex flex-col gap-4">
                            <span class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-0.5 uppercase">Close Ups</span>
                            
                            {{-- Closeup --}}
                            <div class="flex-1 flex flex-col min-h-0">
                                <div class="bg-base-100 border border-base-300 rounded-3xl overflow-hidden flex-1 min-h-[160px] relative group shadow-sm flex items-center justify-center">
                                    {{-- Active Loader for instant browser feedback --}}
                                    <div wire:loading wire:target="generateImage('closeup')" class="absolute inset-0 bg-base-100/80 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3 select-none z-20">
                                        <div class="relative w-10 h-10 flex items-center justify-center">
                                            <div class="absolute inset-0 rounded-full border-4 border-primary/20"></div>
                                            <div class="absolute inset-0 rounded-full border-4 border-t-primary animate-spin"></div>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold tracking-wider text-slate-500 uppercase animate-pulse">Generating...</span>
                                        </div>
                                    </div>

                                    @if(($generationStates['closeup']['status'] ?? '') === 'generating')
                                        {{-- Generating / Loading State --}}
                                        <div class="absolute inset-0 bg-base-100/80 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3 select-none">
                                            <div class="relative w-10 h-10 flex items-center justify-center">
                                                <div class="absolute inset-0 rounded-full border-4 border-primary/20"></div>
                                                <div class="absolute inset-0 rounded-full border-4 border-t-primary animate-spin"></div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-bold tracking-wider text-slate-500 uppercase animate-pulse">
                                                    @if(($generationStates['closeup']['queue_status'] ?? '') === 'IN_QUEUE')
                                                        In Queue...
                                                    @elseif(($generationStates['closeup']['queue_status'] ?? '') === 'IN_PROGRESS')
                                                        Generating...
                                                    @else
                                                        Generating...
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    @elseif(($generationStates['closeup']['status'] ?? '') === 'failed')
                                        {{-- Failed State --}}
                                        <div class="absolute inset-0 bg-error/5 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3">
                                            <div class="w-10 h-10 rounded-full bg-error/10 border border-error/20 flex items-center justify-center text-error shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-bold text-base-content">Failed</h4>
                                                @if(isset($generationStates['closeup']['error']))
                                                    <p class="text-[9px] text-slate-500 mt-1 line-clamp-1 max-w-[160px]" title="{{ $generationStates['closeup']['error'] }}">
                                                        {{ str_contains(strtolower($generationStates['closeup']['error']), 'content filter') ? 'Safety filter.' : $generationStates['closeup']['error'] }}
                                                    </p>
                                                @endif
                                            </div>
                                            <button type="button" wire:click="generateImage('closeup')" class="btn btn-[10px] btn-error text-white font-bold px-2 py-0.5 rounded-lg flex items-center gap-1 shadow-sm">
                                                Try Again
                                            </button>
                                        </div>
                                    @elseif($this->selectedInfluencer->properties->closeup)
                                        <img src="{{ $this->selectedInfluencer->properties->closeup }}" class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                                            <div class="flex gap-1.5 justify-center">
                                                <button type="button" wire:click="generateImage('closeup')" class="btn btn-xs btn-ghost text-white border border-white/20">Regenerate</button>
                                                <label class="btn btn-xs btn-ghost text-white border border-white/20 cursor-pointer">
                                                    Replace
                                                    <input type="file" wire:model="uploaded_closeup" class="hidden" accept="image/*" />
                                                </label>
                                                <button type="button" wire:click="downloadImage('closeup')" class="btn btn-xs btn-ghost text-white border border-white/20">Download</button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-6 text-center space-y-3">
                                            <div class="flex gap-2 justify-center">
                                                <button type="button" wire:click="generateImage('closeup')" class="btn btn-xs btn-primary shadow shadow-primary/20">
                                                    Generate
                                                </button>
                                                <label class="btn btn-xs btn-outline cursor-pointer">
                                                    Upload
                                                    <input type="file" wire:model="uploaded_closeup" class="hidden" accept="image/*" />
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Detail Sheet --}}
                            <div class="flex-1 flex flex-col min-h-0">
                                <div class="bg-base-100 border border-base-300 rounded-3xl overflow-hidden flex-1 min-h-[160px] relative group shadow-sm flex items-center justify-center">
                                    {{-- Active Loader for instant browser feedback --}}
                                    <div wire:loading wire:target="generateImage('detail_sheet')" class="absolute inset-0 bg-base-100/80 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3 select-none z-20">
                                        <div class="relative w-10 h-10 flex items-center justify-center">
                                            <div class="absolute inset-0 rounded-full border-4 border-primary/20"></div>
                                            <div class="absolute inset-0 rounded-full border-4 border-t-primary animate-spin"></div>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold tracking-wider text-slate-500 uppercase animate-pulse">Generating...</span>
                                        </div>
                                    </div>

                                    @if(($generationStates['detail_sheet']['status'] ?? '') === 'generating')
                                        {{-- Generating / Loading State --}}
                                        <div class="absolute inset-0 bg-base-100/80 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3 select-none">
                                            <div class="relative w-10 h-10 flex items-center justify-center">
                                                <div class="absolute inset-0 rounded-full border-4 border-primary/20"></div>
                                                <div class="absolute inset-0 rounded-full border-4 border-t-primary animate-spin"></div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-bold tracking-wider text-slate-500 uppercase animate-pulse">
                                                    @if(($generationStates['detail_sheet']['queue_status'] ?? '') === 'IN_QUEUE')
                                                        In Queue...
                                                    @elseif(($generationStates['detail_sheet']['queue_status'] ?? '') === 'IN_PROGRESS')
                                                        Generating...
                                                    @else
                                                        Generating...
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    @elseif(($generationStates['detail_sheet']['status'] ?? '') === 'failed')
                                        {{-- Failed State --}}
                                        <div class="absolute inset-0 bg-error/5 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center space-y-3">
                                            <div class="w-10 h-10 rounded-full bg-error/10 border border-error/20 flex items-center justify-center text-error shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-bold text-base-content">Failed</h4>
                                                @if(isset($generationStates['detail_sheet']['error']))
                                                    <p class="text-[9px] text-slate-500 mt-1 line-clamp-1 max-w-[160px]" title="{{ $generationStates['detail_sheet']['error'] }}">
                                                        {{ str_contains(strtolower($generationStates['detail_sheet']['error']), 'content filter') ? 'Safety filter.' : $generationStates['detail_sheet']['error'] }}
                                                    </p>
                                                @endif
                                            </div>
                                            <button type="button" wire:click="generateImage('detail_sheet')" class="btn btn-[10px] btn-error text-white font-bold px-2 py-0.5 rounded-lg flex items-center gap-1 shadow-sm">
                                                Try Again
                                            </button>
                                        </div>
                                    @elseif($this->selectedInfluencer->properties->detail_sheet)
                                        <img src="{{ $this->selectedInfluencer->properties->detail_sheet }}" class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                                            <div class="flex gap-1.5 justify-center">
                                                <button type="button" wire:click="generateImage('detail_sheet')" class="btn btn-xs btn-ghost text-white border border-white/20">Regenerate</button>
                                                <label class="btn btn-xs btn-ghost text-white border border-white/20 cursor-pointer">
                                                    Replace
                                                    <input type="file" wire:model="uploaded_detail_sheet" class="hidden" accept="image/*" />
                                                </label>
                                                <button type="button" wire:click="downloadImage('detail_sheet')" class="btn btn-xs btn-ghost text-white border border-white/20">Download</button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-6 text-center space-y-3">
                                            <div class="flex gap-2 justify-center">
                                                <button type="button" wire:click="generateImage('detail_sheet')" class="btn btn-xs btn-primary shadow shadow-primary/20">
                                                    Generate
                                                </button>
                                                <label class="btn btn-xs btn-outline cursor-pointer">
                                                    Upload
                                                    <input type="file" wire:model="uploaded_detail_sheet" class="hidden" accept="image/*" />
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Prompt panel --}}
                    <div class="bg-base-100 border border-base-300 rounded-3xl p-5 md:p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <span class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase">Prompt</span>
                            <div class="flex items-center gap-3">
                                @if($this->selectedInfluencer?->team?->claude_api_key)
                                    <label class="flex items-center gap-1.5 cursor-pointer bg-violet-500/10 border border-violet-500/20 px-2.5 py-1 rounded-lg text-[9px] font-bold text-violet-650 uppercase">
                                        <input type="checkbox" wire:model.live="use_prompt_enhancer" class="checkbox checkbox-primary checkbox-xs rounded-md" />
                                        <span>Enhance with Claude</span>
                                    </label>
                                @endif
                                <button type="button" x-on:click="navigator.clipboard.writeText($refs.promptText.value); Toaster.success('Prompt in die Zwischenablage kopiert!')" class="btn btn-xs btn-ghost flex items-center gap-1.5 text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                    </svg>
                                    Copy Prompt
                                </button>
                            </div>
                        </div>
                        <textarea x-ref="promptText" readonly class="w-full bg-base-200 border border-base-300 rounded-2xl p-4 text-xs text-base-content font-mono focus:outline-none leading-relaxed resize-none h-28">{{ $this->generatedPrompt }}</textarea>
                    </div>

                    {{-- Bottom Tabs & Details Panel --}}
                    <div class="bg-base-100 border border-base-300 rounded-3xl p-5 md:p-6 shadow-sm">
                        
                        {{-- Tab list --}}
                        <div class="border-b border-base-300 flex flex-wrap gap-5 md:gap-6 mb-6">
                            @foreach([
                                'overview' => 'Overview', 
                                'scripts' => 'Scripts', 
                                'wardrobe' => 'Wardrobe', 
                                'home' => 'Home', 
                                'brand_deals' => 'Brand Deals', 
                                'history' => 'History'
                            ] as $tabKey => $tabLabel)
                                <button type="button" wire:click="$set('detailTab', '{{ $tabKey }}')" class="pb-3 text-xs font-bold border-b-2 transition-all {{ $detailTab === $tabKey ? 'border-primary text-base-content' : 'border-transparent text-slate-500 hover:text-slate-400' }}">{{ $tabLabel }}</button>
                            @endforeach
                        </div>

                        {{-- Tab contents --}}
                        @if($detailTab === 'overview')
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    
                                    {{-- Identity card --}}
                                    <div class="bg-base-200/40 border border-base-300 rounded-2xl p-4 space-y-4">
                                        <span class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase">Identity</span>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[9px] font-bold text-slate-500 mb-1.5 uppercase">Gender</label>
                                                <div class="flex rounded-lg border border-base-300 overflow-hidden bg-base-100 p-0.5">
                                                    <button type="button" wire:click="$set('edit_gender', 'Female')" class="flex-1 py-1 text-xs font-semibold rounded {{ $edit_gender === 'Female' ? 'bg-primary text-primary-content shadow-sm' : 'text-slate-500 hover:text-slate-400' }}">Female</button>
                                                    <button type="button" wire:click="$set('edit_gender', 'Male')" class="flex-1 py-1 text-xs font-semibold rounded {{ $edit_gender === 'Male' ? 'bg-primary text-primary-content shadow-sm' : 'text-slate-500 hover:text-slate-400' }}">Male</button>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-bold text-slate-500 mb-1.5 uppercase">Age</label>
                                                <input type="number" wire:model="edit_age" class="w-full bg-base-100 border border-base-300 rounded-lg px-3 py-1 text-xs focus:outline-none focus:border-primary text-base-content font-medium h-7" />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[9px] font-bold text-slate-500 mb-1.5 uppercase">Location</label>
                                                <input type="text" wire:model="edit_location" placeholder="e.g. NYC" class="w-full bg-base-100 border border-base-300 rounded-lg px-3 py-1 text-xs focus:outline-none focus:border-primary text-base-content font-medium h-7" />
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-bold text-slate-500 mb-1.5 uppercase">Niche</label>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($available_niches as $n)
                                                        <button type="button" wire:click="toggleNiche('{{ $n }}')" class="px-2 py-0.5 text-[9px] font-bold rounded-md border {{ in_array($n, $edit_niches) ? 'border-primary bg-primary/10 text-primary' : 'border-base-300 text-slate-500 hover:border-slate-450' }}">
                                                            {{ $n }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Backstory --}}
                                    <div class="flex flex-col">
                                        <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2 uppercase">Backstory</label>
                                        <textarea wire:model="edit_backstory" rows="6" class="w-full bg-base-200 border border-base-300 rounded-2xl p-4 text-xs focus:outline-none focus:border-primary text-base-content flex-1" placeholder="Their background and vibe description..."></textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- Personality --}}
                                    <div class="space-y-3">
                                        <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase">Personality</label>
                                        <div class="py-2">
                                            <input type="range" wire:model.live="edit_personality" min="0" max="100" class="w-full h-1.5 bg-base-300 rounded-lg appearance-none cursor-pointer accent-primary" />
                                            <div class="flex justify-between text-[9px] text-slate-500 font-extrabold tracking-wider mt-2.5">
                                                <span>INTROVERT</span>
                                                <span class="text-primary uppercase">
                                                    @if($edit_personality < 35) Quiet & Thoughtful @elseif($edit_personality >= 35 && $edit_personality <= 65) Balanced & versatile @else Outgoing & Energetic @endif
                                                </span>
                                                <span>EXTROVERT</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Target Audience --}}
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2 uppercase">Target Audience</label>
                                        <input type="text" wire:model="edit_target_audience" placeholder="e.g. a woman, 18-34, interested in fashion" class="w-full bg-base-200 border border-base-300 rounded-2xl px-4 py-3 text-xs focus:outline-none focus:border-primary text-base-content h-10" />
                                    </div>
                                </div>

                                {{-- Physical Description --}}
                                <div>
                                    <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2 uppercase">Physical Description</label>
                                    <textarea wire:model="edit_physical_description" rows="3" class="w-full bg-base-200 border border-base-300 rounded-2xl p-4 text-xs focus:outline-none focus:border-primary text-base-content" placeholder="Latina, medium-length wavy brunette hair with side-swept bangs..."></textarea>
                                </div>

                                {{-- Save changes button --}}
                                <div class="flex justify-end pt-2">
                                    <x-button label="Save Changes" wire:click="saveOverview" class="btn-primary rounded-2xl font-bold px-8 shadow shadow-primary/10" />
                                </div>
                            </div>
                        @elseif($detailTab === 'wardrobe')
                            <div class="space-y-6">
                                {{-- Form Card --}}
                                <div class="bg-base-200/40 border border-base-300 rounded-2xl p-5 space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase">Garderobe bearbeiten & generieren</span>
                                        <x-button label="Add Wardrobe" icon="o-plus" wire:click="addOutfitPrompt" class="btn-xs btn-outline btn-ghost text-xs" />
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <x-input label="Top" wire:model="outfit_top" placeholder="z.B. Weißes T-Shirt" />
                                        </div>
                                        <div>
                                            <x-input label="Bottom" wire:model="outfit_bottom" placeholder="z.B. Blaue Jeans" />
                                        </div>
                                        <div>
                                            <x-input label="Hairstyle" wire:model="outfit_hairstyle" placeholder="z.B. Pferdeschwanz" />
                                        </div>
                                    </div>
                                    <div>
                                        <x-textarea label="Full Look Description" wire:model="outfit_description" placeholder="Beschreibe den Look im Detail..." rows="3" />
                                    </div>

                                    <div class="flex justify-end pt-2">
                                        <x-button label="Generate Look" wire:click="generateOutfit" spinner="generateOutfit" class="btn-primary rounded-2xl font-bold px-8 shadow shadow-primary/10" />
                                    </div>
                                </div>

                                {{-- Grid Card --}}
                                <div class="space-y-4">
                                    <span class="block text-[10px] font-extrabold text-slate-555 tracking-wider uppercase">Gespeicherte Looks</span>
                                    
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                        @forelse($this->selectedInfluencer->outfits as $outfit)
                                            <div wire:key="{{ $outfit->id }}" class="bg-base-100 border border-base-300 rounded-2xl overflow-hidden relative group shadow-sm flex flex-col cursor-pointer" wire:click="selectOutfit('{{ $outfit->id }}')">
                                                <div class="aspect-[3/4] overflow-hidden relative">
                                                    <img src="{{ $outfit->image_path }}" alt="{{ $outfit->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                                                    
                                                    {{-- Delete button --}}
                                                    <button type="button" wire:click.stop="deleteOutfit('{{ $outfit->id }}')" class="absolute top-2 right-2 w-6 h-6 rounded-full bg-black/60 hover:bg-red-600 text-white flex items-center justify-center transition-colors focus:outline-none z-10" title="Löschen">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="p-3 bg-base-100 flex-1 flex flex-col justify-center border-t border-base-200">
                                                    <h4 class="font-bold text-xs text-base-content truncate text-center">{{ $outfit->name }}</h4>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-span-full py-12 border border-dashed border-base-300 rounded-2xl text-center text-slate-500">
                                                <svg class="w-8 h-8 text-slate-400 mx-auto mb-2.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                                <p class="text-xs font-bold text-slate-400 mb-1">Keine Outfits gespeichert</p>
                                                <p class="text-[11px] text-slate-555">Trage oben Details ein und klicke auf "Generate Look", oder füge einen mit "+ Add Wardrobe" hinzu.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Other tabs placeholder --}}
                            <div class="py-12 border-2 border-dashed border-base-300 rounded-2xl text-center text-slate-500 max-w-md mx-auto">
                                <svg class="w-8 h-8 text-slate-400 mx-auto mb-2.5 opacity-60 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <p class="text-xs font-extrabold uppercase tracking-widest text-slate-400 mb-1">Coming Soon</p>
                                <p class="text-[11px] text-slate-500">Der Bereich <strong>{{ $detailTab }}</strong> wird in Kürze freigeschaltet.</p>
                            </div>
                        @endif

                    </div>

                @elseif($currentTab === 'photos')
                    <livewire:photo-studio :influencer="$this->selectedInfluencer" :key="'photo-studio-' . $selectedId" />
                @elseif($currentTab === 'videos')
                    {{-- Videos Placeholder --}}
                    <div class="bg-base-100 border border-base-300 rounded-3xl p-12 text-center text-slate-550 shadow-sm">
                        <svg class="w-10 h-10 text-slate-400 mx-auto mb-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="font-bold text-base text-base-content">Videos Gallery</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">Higgsfield integration will allow you to generate and view cinematic video clips of {{ $this->selectedInfluencer->name }} here.</p>
                    </div>
                @endif

            </div>
        @endif

    </main>

    {{-- Save Outfit Modal --}}
    @if($showAddOutfitModal)
        <div class="modal modal-open backdrop-blur-sm bg-black/40 fixed inset-0 z-50 flex items-center justify-center">
            <div class="modal-box bg-base-100 border border-base-300 p-6 rounded-2xl max-w-sm w-full">
                <h3 class="font-bold text-lg mb-4 text-base-content">Outfit speichern</h3>
                <div class="space-y-4">
                    <x-input label="Outfit-Name" wire:model="newOutfitName" placeholder="z.B. Lässiges Sommeroutfit" />
                </div>
                <div class="modal-action flex justify-end gap-2 mt-6">
                    <x-button label="Abbrechen" wire:click="$set('showAddOutfitModal', false)" class="btn-ghost" />
                    <x-button label="Speichern" wire:click="saveNewOutfit" class="btn-primary" />
                </div>
            </div>
        </div>
    @endif

    {{-- Outfit Detail Zoom Overlay --}}
    @if($showOutfitOverlay && $activeOutfitId)
        @php
            $activeOutfit = \App\Models\Outfit::find($activeOutfitId);
        @endphp
        @if($activeOutfit)
            <div class="modal modal-open backdrop-blur-md bg-black/60 fixed inset-0 z-50 flex items-center justify-center" wire:click="$set('showOutfitOverlay', false)">
                <div class="modal-box bg-base-100 border border-base-300 p-0 rounded-2xl max-w-lg w-full overflow-hidden relative shadow-2xl" wire:click.stop>
                    {{-- Close button --}}
                    <button type="button" wire:click="$set('showOutfitOverlay', false)" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/50 hover:bg-black/80 text-white flex items-center justify-center transition-colors focus:outline-none z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="aspect-[3/4] w-full relative">
                        <img src="{{ $activeOutfit->image_path }}" alt="{{ $activeOutfit->name }}" class="w-full h-full object-cover" />
                    </div>
                    
                    <div class="p-6 space-y-3 font-sans">
                        <h3 class="font-extrabold text-lg text-base-content">{{ $activeOutfit->name }}</h3>
                        
                        @if($activeOutfit->top || $activeOutfit->bottom || $activeOutfit->hairstyle)
                            <div class="flex flex-wrap gap-2 pt-1">
                                @if($activeOutfit->top)
                                    <span class="px-2.5 py-1 bg-base-200 border border-base-300 rounded-lg text-xs font-semibold text-base-content">Top: {{ $activeOutfit->top }}</span>
                                @endif
                                @if($activeOutfit->bottom)
                                    <span class="px-2.5 py-1 bg-base-200 border border-base-300 rounded-lg text-xs font-semibold text-base-content">Bottom: {{ $activeOutfit->bottom }}</span>
                                @endif
                                @if($activeOutfit->hairstyle)
                                    <span class="px-2.5 py-1 bg-base-200 border border-base-300 rounded-lg text-xs font-semibold text-base-content">Haar: {{ $activeOutfit->hairstyle }}</span>
                                @endif
                            </div>
                        @endif
                        
                        @if($activeOutfit->full_look_description)
                            <p class="text-xs text-slate-500 leading-relaxed pt-2 border-t border-base-200">{{ $activeOutfit->full_look_description }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- Import Influencer Modal --}}
    @if($showImportModal)
        <div class="modal modal-open backdrop-blur-sm bg-black/40 fixed inset-0 z-50 flex items-center justify-center">
            <div class="modal-box bg-base-100 border border-base-300 p-6 rounded-2xl max-w-md w-full">
                <h3 class="font-bold text-lg mb-4 text-base-content">Influencer importieren</h3>
                
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label font-bold text-xs text-slate-500 uppercase tracking-wider">Lade .isdata-Datei hoch</label>
                        <input type="file" wire:model="importFile" class="file-input file-input-bordered w-full text-sm" accept=".isdata" />
                        @error('importFile') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="divider text-xs text-slate-400 font-bold uppercase">ODER</div>

                    <div class="form-control">
                        <x-input label="Download-Link (URL)" wire:model="importUrl" placeholder="https://example.com/influencer.isdata" />
                        @error('importUrl') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="modal-action flex justify-end gap-2 mt-6">
                    <x-button label="Abbrechen" wire:click="$set('showImportModal', false)" class="btn-ghost" />
                    <x-button label="Importieren" wire:click="importInfluencer" class="btn-primary" />
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal (SweetAlert style) --}}
    @if($showDeleteConfirmModal && $this->selectedInfluencer)
        <div class="modal modal-open backdrop-blur-sm bg-black/40 fixed inset-0 z-50 flex items-center justify-center">
            <div class="modal-box bg-base-100 border border-base-300 p-6 rounded-3xl max-w-md w-full text-center relative overflow-hidden">
                {{-- Alert icon --}}
                <div class="w-16 h-16 rounded-full bg-error/10 border border-error/20 flex items-center justify-center mx-auto text-error mb-4 shadow-sm">
                    <x-icon name="o-exclamation-triangle" class="w-8 h-8" />
                </div>

                <h3 class="font-extrabold text-xl mb-2 text-base-content">Löschen bestätigen</h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    Achtung: Alle Daten für <strong class="text-base-content font-bold">{{ $this->selectedInfluencer->name }}</strong> (inkl. aller generierten Fotos, Outfits und Einstellungen) gehen unwiderruflich verloren. Möchtest du den Influencer vor dem Löschen als Backup exportieren?
                </p>

                <div class="flex flex-col gap-2">
                    <x-button label="Export & Delete" icon="o-arrow-down-tray" wire:click="exportAndDelete" class="btn-primary w-full py-2.5 rounded-xl text-xs font-bold" />
                    <x-button label="Delete without backup" icon="o-trash" wire:click="deleteOnly" class="btn-error text-white w-full py-2.5 rounded-xl text-xs font-bold" />
                    <x-button label="Cancel" wire:click="$set('showDeleteConfirmModal', false)" class="btn-ghost w-full py-2.5 rounded-xl text-xs font-bold" />
                </div>
            </div>
        </div>
    @endif

</div>

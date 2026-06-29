<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 h-full font-sans text-base-content"
     @if($generating) wire:poll.2s="checkGenerationProgress" @endif>

    {{-- Left Sidebar: Settings Panel (xl:col-span-4) --}}
    <div class="xl:col-span-4 space-y-6 flex flex-col h-full overflow-y-auto pr-1">
        <div class="card bg-base-100 border border-base-300 rounded-3xl p-5 md:p-6 shadow-sm space-y-5">
            <div class="flex justify-between items-center pb-2 border-b border-base-200">
                <div>
                    <h2 class="text-lg font-bold tracking-tight">Photo Studio</h2>
                    <p class="text-[11px] text-slate-500 font-medium">Generate high-fidelity influencer content</p>
                </div>
                <div class="flex gap-1.5">
                    <button type="button" wire:click="randomize" class="btn btn-xs btn-outline rounded-lg flex items-center gap-1 text-[10px]" tooltip="Randomize Params">
                        🎲 Random
                    </button>
                    <button type="button" wire:click="resetParams" class="btn btn-xs btn-ghost rounded-lg text-[10px] text-slate-500">
                        Reset
                    </button>
                </div>
            </div>

            {{-- Model Selection (Extensible for Nano Banana etc.) --}}
            <div class="space-y-1.5">
                <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase">AI Model</label>
                <select wire:model.live="selectedModel" class="select select-sm select-bordered w-full rounded-xl text-xs font-semibold bg-base-200 focus:outline-none focus:border-primary">
                    @foreach($availableModels as $key => $name)
                        <option value="{{ $key }}">{{ $name }}</option>
                    @endforeach
                </select>
                <div class="text-[9px] text-slate-500 italic mt-1 leading-normal">
                    @if($selectedModel === 'openai/gpt-image-2/edit')
                        Highly detailed edit using multi-reference identity layers.
                    @else
                        Alternative generation engine. Make sure reference image paths are public.
                    @endif
                </div>
            </div>

            {{-- Stance (Standing / Sitting) --}}
            <div class="space-y-1.5">
                <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase">Stance / Body Position</label>
                <div class="grid grid-cols-2 gap-2 bg-base-200/50 p-1 rounded-xl border border-base-300">
                    <button type="button" wire:click="$set('stance', 'standing')" class="py-1.5 text-xs font-bold rounded-lg transition-all {{ $stance === 'standing' ? 'bg-base-100 text-primary shadow-xs border border-base-300' : 'text-slate-500 hover:text-base-content' }}">
                        🧍 Standing
                    </button>
                    <button type="button" wire:click="$set('stance', 'sitting')" class="py-1.5 text-xs font-bold rounded-lg transition-all {{ $stance === 'sitting' ? 'bg-base-100 text-primary shadow-xs border border-base-300' : 'text-slate-500 hover:text-base-content' }}">
                        🧎 Sitting
                    </button>
                </div>
            </div>

            {{-- Time & Vibe --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase">Time of Day</label>
                    <select wire:model.live="timeOfDay" class="select select-sm select-bordered w-full rounded-xl text-xs font-semibold bg-base-200">
                        <option value="morning">🌅 Morning</option>
                        <option value="afternoon">☀️ Afternoon</option>
                        <option value="golden-hour">🌇 Golden Hour</option>
                        <option value="night">🌙 Night</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase">Vibe / Style</label>
                    <select wire:model.live="vibe" class="select select-sm select-bordered w-full rounded-xl text-xs font-semibold bg-base-200">
                        <option value="candid">Candid (Natural)</option>
                        <option value="editorial">Editorial (Clean)</option>
                        <option value="luxury">Luxury (Sharp)</option>
                        <option value="street">Street (Urban)</option>
                        <option value="cozy">Cozy (Soft)</option>
                    </select>
                </div>
            </div>

            {{-- Wardrobe Outfit Selector (Influencer's Saved Wardrobe) --}}
            <div class="space-y-1.5">
                <div class="flex justify-between items-center">
                    <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase">Wardrobe Outfit</label>
                    @if($selectedOutfitId)
                        <button type="button" wire:click="$set('selectedOutfitId', null)" class="text-[9px] font-bold text-error hover:underline">Clear Outfit</button>
                    @endif
                </div>
                <select wire:model.live="selectedOutfitId" class="select select-sm select-bordered w-full rounded-xl text-xs font-semibold bg-base-200">
                    <option value="">Current look (Character Sheet / Avatar)</option>
                    @foreach($influencer->outfits as $outfit)
                        <option value="{{ $outfit->id }}">{{ $outfit->name }} ({{ $outfit->top ?: 'No Top' }} / {{ $outfit->bottom ?: 'No Bottom' }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Advanced Settings --}}
            <div class="space-y-4 pt-2 border-t border-base-200">
                <span class="block text-[10px] font-extrabold text-slate-400 tracking-wider uppercase">Prompt Overrides & Props</span>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Custom Location Description</label>
                    <textarea wire:model.live="locationText" rows="2" placeholder="Describe background/setting details if not using a location preset..." class="textarea textarea-sm textarea-bordered w-full rounded-xl text-xs bg-base-200 leading-normal resize-none focus:outline-none focus:border-primary"></textarea>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Custom Pose Description</label>
                    <textarea wire:model.live="poseText" rows="2" placeholder="Describe body posture, stance, and camera framing details..." class="textarea textarea-sm textarea-bordered w-full rounded-xl text-xs bg-base-200 leading-normal resize-none focus:outline-none focus:border-primary"></textarea>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Custom Outfit Text</label>
                    <textarea wire:model="wardrobeText" rows="2" placeholder="Describe clothing details if not using a wardrobe preset..." class="textarea textarea-sm textarea-bordered w-full rounded-xl text-xs bg-base-200 leading-normal resize-none focus:outline-none focus:border-primary"></textarea>
                </div>

                <div class="space-y-1.5">
                    <div class="flex justify-between items-center">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Custom Hairstyle</label>
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="checkbox" wire:model="hairstyleLocked" class="checkbox checkbox-xs rounded-md" />
                            <span class="text-[9px] text-slate-500 font-bold uppercase">Lock hairstyle</span>
                        </label>
                    </div>
                    <input type="text" wire:model="hairstyleText" placeholder="e.g. high messy bun, wavy blonde highlights..." class="input input-sm input-bordered w-full rounded-xl text-xs bg-base-200 focus:outline-none focus:border-primary" />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Held Products / Props</label>
                    <input type="text" wire:model="propText" placeholder="e.g. holding a white ceramic coffee cup in left hand..." class="input input-sm input-bordered w-full rounded-xl text-xs bg-base-200 focus:outline-none focus:border-primary" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Expression</label>
                        <select wire:model="expression" class="select select-sm select-bordered w-full rounded-xl text-xs bg-base-200">
                            <option value="natural">Natural</option>
                            <option value="smiling">Smiling</option>
                            <option value="laughing">Mid-Laugh</option>
                            <option value="serious">Serious</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Gaze Direction</label>
                        <select wire:model="gaze" class="select select-sm select-bordered w-full rounded-xl text-xs bg-base-200">
                            <option value="at-camera">At Camera</option>
                            <option value="looking-away">Looking Away</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Output Dimensions & Credit Estimation --}}
            <div class="space-y-4 pt-3 border-t border-base-200">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase">Aspect Ratio</label>
                        <select wire:model.live="aspectRatio" class="select select-sm select-bordered w-full rounded-xl text-xs bg-base-200">
                            <option value="9:16">9:16 (Portrait)</option>
                            <option value="16:9">16:9 (Landscape)</option>
                            <option value="1:1">1:1 (Square)</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase">Batch Count</label>
                        <select wire:model.live="outputCount" class="select select-sm select-bordered w-full rounded-xl text-xs bg-base-200">
                            <option value="1">1 Image</option>
                            <option value="2">2 Images</option>
                            <option value="3">3 Images</option>
                            <option value="4">4 Images</option>
                        </select>
                    </div>
                </div>

                {{-- Action & Charges --}}
                <div class="space-y-2 pt-2">
                    @if($this->influencer->team?->claude_api_key)
                        <div class="flex items-center gap-2 px-1 py-1 bg-violet-500/10 border border-violet-500/20 rounded-2xl mb-2">
                            <label class="flex items-center gap-2 cursor-pointer w-full py-1 px-2.5">
                                <input type="checkbox" wire:model="use_prompt_enhancer" class="checkbox checkbox-primary checkbox-xs rounded-md" />
                                <div class="text-left">
                                    <span class="block text-[10px] font-extrabold text-violet-650 uppercase tracking-wide">Claude Prompt Enhancer</span>
                                    <span class="block text-[8px] text-slate-500">Refine using official FAL.ai model prompting guides</span>
                                </div>
                            </label>
                        </div>
                    @endif
                    <button type="button" wire:click="generate" @disabled($generating) class="btn btn-primary w-full rounded-2xl font-bold py-3 shadow-lg shadow-primary/20 text-white flex items-center justify-center gap-2">
                        @if($generating)
                            <span class="loading loading-spinner loading-xs"></span>
                            <span>
                                @if($queueStatus === 'IN_QUEUE')
                                    In Queue...
                                @elseif($queueStatus === 'IN_PROGRESS')
                                    Generating...
                                @else
                                    Generating...
                                @endif
                            </span>
                        @else
                            <span>📸 Generate Content</span>
                        @endif
                    </button>
                    <div class="flex justify-between items-center text-[10px] text-slate-500 font-medium px-1">
                        <span>Model Cost: ${{ number_format($outputCount * 0.35, 2) }}</span>
                        @if($influencer->team->hasFalApiKey())
                            <span class="text-emerald-500 font-bold uppercase tracking-wider">Custom API Key Active</span>
                        @else
                            <span>Balance: ${{ number_format(floatval($influencer->team->credits ?? 0), 2) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Center Panel: Previews, Outputs & Gallery (xl:col-span-8) --}}
    <div class="xl:col-span-8 space-y-6 flex flex-col h-full overflow-y-auto">

        @if($reUseAssetId)
            <div class="alert alert-info rounded-3xl flex justify-between items-center py-2.5 px-4 shadow-sm border border-info/30">
                <div class="flex items-center gap-3">
                    <span class="text-lg">📸</span>
                    <div class="text-left">
                        <p class="text-xs font-bold leading-tight">Re-using Reference Photo</p>
                        <p class="text-[10px] text-slate-500">The selected photo will be used as the pose and composition reference.</p>
                    </div>
                </div>
                <button type="button" wire:click="clearReUse" class="btn btn-xs btn-outline btn-info rounded-lg">
                    Clear Reference
                </button>
            </div>
        @endif

        {{-- Top Interactive Previews Section --}}
        <div class="card bg-base-100 border border-base-300 rounded-3xl p-5 md:p-6 shadow-sm">
            <div class="flex justify-between items-center pb-3 border-b border-base-200 mb-4">
                <div class="flex gap-2">
                    <button type="button" wire:click="$set('rightMode', 'location')" class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all {{ $rightMode === 'location' ? 'bg-primary/10 text-primary border border-primary/20' : 'text-slate-500 hover:text-base-content' }}">
                        📍 Location Previews
                    </button>
                    <button type="button" wire:click="$set('rightMode', 'pose')" class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all {{ $rightMode === 'pose' ? 'bg-primary/10 text-primary border border-primary/20' : 'text-slate-500 hover:text-base-content' }}">
                        🕺 Pose Previews
                    </button>
                </div>
                <div class="text-[10px] text-slate-500 font-extrabold uppercase tracking-wide">
                    @if($rightMode === 'location')
                        Selected: <span class="text-primary">{{ $location ? str_replace('-', ' ', $location) : 'None (Custom)' }}</span>
                    @else
                        Selected: <span class="text-primary">{{ $pose ? str_replace('_', ' ', $pose) : 'None (Custom)' }}</span>
                    @endif
                </div>
            </div>

            {{-- Location Previews Grid --}}
            @if($rightMode === 'location')
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 overflow-y-auto pr-1">
                    <div wire:key="loc-card-none" wire:click="selectLocation(null)" class="relative border-2 rounded-2xl overflow-hidden cursor-pointer group aspect-[4/3] flex flex-col items-center justify-center p-3 text-center transition-all hover:scale-[1.02] {{ $location === null ? 'border-primary bg-primary/5 shadow-md shadow-primary/15' : 'border-base-300' }}">
                        <span class="text-xl mb-1">❌</span>
                        <span class="text-[10px] font-bold leading-tight text-slate-500 uppercase group-hover:text-base-content">None (Custom)</span>
                    </div>
                    @foreach(['coffee-shop', 'city-street', 'beach', 'rooftop', 'bedroom', 'bathroom', 'mall', 'gym', 'park', 'restaurant', 'hotel', 'studio'] as $loc)
                        <div wire:key="loc-card-{{ $loc }}" wire:click="selectLocation('{{ $loc }}')" class="relative border-2 rounded-2xl overflow-hidden cursor-pointer group aspect-[4/3] flex items-center justify-center transition-all hover:scale-[1.02] {{ $location === $loc ? 'border-primary shadow-md shadow-primary/15' : 'border-base-300' }}">
                            <img src="{{ $this->getLocationPreviewUrl($loc, $timeOfDay) }}" alt="{{ $loc }}" class="w-full h-full object-cover transition-transform group-hover:scale-105" />
                            <div class="absolute inset-0 bg-black/45 flex flex-col justify-end p-2.5">
                                <span class="text-[11px] font-bold text-white capitalize leading-tight">{{ str_replace('-', ' ', $loc) }}</span>
                            </div>
                            @if($location === $loc)
                                <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-primary flex items-center justify-center text-white border border-white/20 z-10 shadow">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Pose Previews Grid --}}
            @if($rightMode === 'pose')
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 overflow-y-auto pr-1">
                    <div wire:key="pose-card-none" wire:click="selectPose(null)" class="relative border-2 rounded-2xl overflow-hidden cursor-pointer group aspect-[3/4] flex flex-col items-center justify-center p-3 text-center transition-all hover:scale-[1.02] {{ $pose === null ? 'border-primary bg-primary/5 shadow-md shadow-primary/15' : 'border-base-300' }}">
                        <span class="text-xl mb-1">❌</span>
                        <span class="text-[10px] font-bold leading-tight text-slate-500 uppercase group-hover:text-base-content">None (Custom)</span>
                    </div>
                    @foreach($this->getAvailablePoses() as $p)
                        <div wire:key="pose-card-{{ $p['id'] }}" wire:click="selectPose('{{ $p['id'] }}')" class="relative border-2 rounded-2xl overflow-hidden cursor-pointer group aspect-[3/4] flex items-center justify-center transition-all hover:scale-[1.02] {{ $pose === $p['id'] ? 'border-primary shadow-md shadow-primary/15' : 'border-base-300' }}">
                            <img src="{{ $this->getPosePreviewUrl($p['id']) }}" alt="{{ $p['label'] }}" class="w-full h-full object-cover transition-transform group-hover:scale-105" />
                            <div class="absolute inset-0 bg-black/45 flex flex-col justify-end p-2.5">
                                <span class="text-[11px] font-bold text-white leading-tight">{{ $p['label'] }}</span>
                            </div>
                            @if($pose === $p['id'])
                                <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-primary flex items-center justify-center text-white border border-white/20 z-10 shadow">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Active Generation Output / Display Area --}}
        <div class="card bg-base-100 border border-base-300 rounded-3xl p-5 md:p-6 shadow-sm space-y-4 {{ $generating || count($currentImgs) > 0 || $error ? '' : 'hidden' }}">
            <div class="flex justify-between items-center pb-2 border-b border-base-200">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Output Gallery</span>
                @if(!$generating)
                    @if(count($currentImgs) > 0)
                        <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wide">✓ Success</span>
                    @endif
                @endif
            </div>

            {{-- Image Generation Progress / Error / Grid --}}
            <div class="relative min-h-[220px] bg-base-200/50 rounded-2xl flex items-center justify-center overflow-hidden border border-base-300">
                {{-- Loading State --}}
                @if($generating)
                    <div class="flex flex-col items-center justify-center p-8 text-center space-y-3">
                        <div class="relative w-12 h-12 flex items-center justify-center">
                            <div class="absolute inset-0 rounded-full border-4 border-primary/20"></div>
                            <div class="absolute inset-0 rounded-full border-4 border-t-primary animate-spin"></div>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-base-content uppercase tracking-wider animate-pulse">
                                @if($queueStatus === 'IN_QUEUE')
                                    In Queue...
                                @elseif($queueStatus === 'IN_PROGRESS')
                                    Generating High-Fidelity Photos...
                                @else
                                    Generating High-Fidelity Photos...
                                @endif
                            </h4>
                            <p class="text-[10px] text-slate-500 mt-1 max-w-[240px]">This takes up to 45 seconds per batch.</p>
                        </div>
                    </div>
                @endif

                {{-- Non-Loading Results State --}}
                @if(!$generating)
                    <div class="w-full h-full flex items-center justify-center">
                        @if($error)
                            <div class="flex flex-col items-center justify-center p-8 text-center space-y-3">
                                <div class="w-12 h-12 rounded-full bg-error/10 border border-error/20 flex items-center justify-center text-error">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-base-content">Generation Failed</h4>
                                    <p class="text-[10px] text-slate-500 mt-1 max-w-sm line-clamp-3 leading-normal border border-error/20 p-2.5 rounded-xl bg-error/5">{{ $error }}</p>
                                </div>
                            </div>
                        @elseif(count($currentImgs) > 0)
                            {{-- Output Grid --}}
                            <div class="grid gap-4 p-4 w-full h-full {{ count($currentImgs) === 1 ? 'grid-cols-1 max-w-sm mx-auto' : (count($currentImgs) === 2 ? 'grid-cols-2' : 'grid-cols-2 md:grid-cols-3') }}">
                                @foreach($currentImgs as $img)
                                    <div wire:key="output-{{ $loop->index }}" wire:click="$set('expandedImg', '{{ $this->resolvePhotoUrl($img) }}')" class="relative rounded-xl overflow-hidden border border-base-300 {{ $this->getAspectClass($img) }} cursor-zoom-in group shadow shadow-black/10">
                                        <img src="{{ $this->resolvePhotoUrl($img) }}" class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-2.5">
                                            <span class="text-[9px] font-bold text-white uppercase tracking-wider text-center">Zoom Photo</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Photos History / Gallery --}}
        <div x-data="{ hoverImg: null, hoverAspect: 'aspect-[3/4]' }" class="card bg-base-100 border border-base-300 rounded-3xl p-5 md:p-6 shadow-sm space-y-4 flex-1 relative">
            <div class="flex justify-between items-center pb-2 border-b border-base-200">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Photo Studio Gallery</span>
                <span class="text-[10px] font-bold text-slate-400 font-mono">{{ $galleryPhotos->count() }} Photo(s)</span>
            </div>

            @if($galleryPhotos->isEmpty())
                <div class="flex-1 flex flex-col items-center justify-center p-12 text-center text-slate-500">
                    <svg class="w-10 h-10 text-slate-400 mb-2.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.684 10.742l-1.684 1.684m0 0l-1.684-1.684m1.684 1.684V3.75m0 16.5h10.5a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0017.25 4.5h-10.5A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    <p class="text-xs font-bold text-slate-400 mb-1">No studio photos generated yet</p>
                    <p class="text-[11px] text-slate-500">Adjust the settings on the left and click "Generate Content" to build your gallery.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 overflow-y-auto pr-1 max-h-[500px]">
                    @foreach($galleryPhotos as $photo)
                        @php
                            $resolvedUrl = $this->resolvePhotoUrl($photo->local_url);
                            $aspectClass = $this->getAspectClass($photo->local_url);
                        @endphp
                        <div wire:key="gallery-photo-{{ $photo->id }}" 
                             @mouseenter="hoverImg = '{{ $resolvedUrl }}'; hoverAspect = '{{ $aspectClass }}'" 
                             @mouseleave="hoverImg = null"
                             class="relative rounded-2xl overflow-hidden border border-base-350 aspect-[3/4] group shadow-xs">
                            <img src="{{ $resolvedUrl }}" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-center items-center gap-2 p-2.5">
                                <button type="button" wire:click="$set('expandedImg', '{{ $resolvedUrl }}')" class="btn btn-xs btn-primary rounded-xl text-[10px] w-11/12 text-white font-bold py-1 shadow">
                                    🔍 Zoom
                                </button>
                                @if($photo->meta_data)
                                    <button type="button" wire:click="reUse('{{ $photo->id }}')" class="btn btn-xs btn-accent rounded-xl text-[10px] w-11/12 text-white font-bold py-1 shadow">
                                        🔄 Re-use
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Floating Hover Popup (outside the scrollable grid wrapper but inside relative card) --}}
            <div x-show="hoverImg" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-30 w-72 md:w-80 rounded-2xl bg-base-100 border border-base-300 p-2 shadow-2xl backdrop-blur-md bg-opacity-95"
                 style="display: none;">
                 <div class="w-full overflow-hidden rounded-xl border border-base-200 bg-black/5 flex items-center justify-center" :class="hoverAspect">
                     <img :src="hoverImg" class="w-full h-full object-contain" />
                 </div>
            </div>
        </div>
    </div>

    {{-- Expanded Image Modal / Zoom --}}
    @if($expandedImg)
        <div class="modal modal-open backdrop-blur-md bg-black/75 fixed inset-0 z-50 flex items-center justify-center" wire:click="$set('expandedImg', null)">
            <div class="modal-box bg-base-100 border border-base-300 p-0 rounded-2xl max-w-lg w-full overflow-hidden relative shadow-2xl" wire:click.stop>
                {{-- Close button --}}
                <button type="button" wire:click="$set('expandedImg', null)" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center transition-colors focus:outline-none z-10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="aspect-[3/4] w-full relative bg-black flex items-center justify-center">
                    <img src="{{ $this->resolvePhotoUrl($expandedImg) }}" class="max-w-full max-h-full object-contain" />
                </div>
                
                <div class="p-4 flex gap-3 justify-end bg-base-100 border-t border-base-200">
                    <a href="{{ $this->resolvePhotoUrl($expandedImg) }}" download="photo-studio-output.png" class="btn btn-sm btn-primary rounded-xl text-xs font-bold text-white px-5 shadow shadow-primary/20">
                        📥 Download Photo
                    </a>
                    <button type="button" wire:click="$set('expandedImg', null)" class="btn btn-sm btn-ghost rounded-xl text-xs font-bold px-4">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

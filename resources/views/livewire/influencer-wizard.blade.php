<div class="min-h-screen text-base-content flex flex-col justify-between py-6 px-4 relative overflow-hidden bg-base-200 selection:bg-primary selection:text-primary-content" x-data
     @if(collect($generated_variations)->contains('status', 'processing')) wire:poll.2s="checkWizardGenerationProgress" @endif>
    
    {{-- Decorative Side Slideshows --}}
    {{-- Left Side --}}
    <div class="fixed left-6 top-24 bottom-24 w-44 hidden xl:flex flex-col justify-around items-center z-10 pointer-events-none">
        <div x-data="{
            images: @js($slideshow_images),
            currentIdx: Math.floor(Math.random() * 20),
            fade: false,
            init() {
                setInterval(() => {
                    this.fade = true;
                    setTimeout(() => {
                        this.currentIdx = (this.currentIdx + 2) % this.images.length;
                        this.fade = false;
                    }, 500);
                }, 5000 + Math.random() * 2000);
            }
        }" class="relative h-64 w-40 rounded-2xl overflow-hidden shadow-2xl transition-all duration-500 transform border border-base-300 rotate-[-6deg]"
           :class="fade ? 'opacity-25 scale-95' : 'opacity-100 scale-100'">
            <img :src="images[currentIdx]" class="h-full w-full object-cover rounded-2xl" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        </div>

        <div x-data="{
            images: @js($slideshow_images),
            currentIdx: Math.floor(Math.random() * 20) + 20,
            fade: false,
            init() {
                setInterval(() => {
                    this.fade = true;
                    setTimeout(() => {
                        this.currentIdx = (this.currentIdx + 3) % this.images.length;
                        this.fade = false;
                    }, 500);
                }, 6000 + Math.random() * 2000);
            }
        }" class="relative h-64 w-40 rounded-2xl overflow-hidden shadow-2xl transition-all duration-500 transform border border-base-300 rotate-[5deg] mt-12"
           :class="fade ? 'opacity-25 scale-95' : 'opacity-100 scale-100'">
            <img :src="images[currentIdx]" class="h-full w-full object-cover rounded-2xl" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        </div>
    </div>

    {{-- Right Side --}}
    <div class="fixed right-6 top-24 bottom-24 w-44 hidden xl:flex flex-col justify-around items-center z-10 pointer-events-none">
        <div x-data="{
            images: @js($slideshow_images),
            currentIdx: Math.floor(Math.random() * 15) + 5,
            fade: false,
            init() {
                setInterval(() => {
                    this.fade = true;
                    setTimeout(() => {
                        this.currentIdx = (this.currentIdx + 4) % this.images.length;
                        this.fade = false;
                    }, 500);
                }, 5500 + Math.random() * 2000);
            }
        }" class="relative h-64 w-40 rounded-2xl overflow-hidden shadow-2xl transition-all duration-500 transform border border-base-300 rotate-[8deg]"
           :class="fade ? 'opacity-25 scale-95' : 'opacity-100 scale-100'">
            <img :src="images[currentIdx]" class="h-full w-full object-cover rounded-2xl" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        </div>

        <div x-data="{
            images: @js($slideshow_images),
            currentIdx: Math.floor(Math.random() * 15) + 25,
            fade: false,
            init() {
                setInterval(() => {
                    this.fade = true;
                    setTimeout(() => {
                        this.currentIdx = (this.currentIdx + 1) % this.images.length;
                        this.fade = false;
                    }, 500);
                }, 6500 + Math.random() * 2000);
            }
        }" class="relative h-64 w-40 rounded-2xl overflow-hidden shadow-2xl transition-all duration-500 transform border border-base-300 rotate-[-5deg] mt-12"
           :class="fade ? 'opacity-25 scale-95' : 'opacity-100 scale-100'">
            <img :src="images[currentIdx]" class="h-full w-full object-cover rounded-2xl" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        </div>
    </div>

    {{-- Top Navbar --}}
    <header class="max-w-7xl w-full mx-auto flex justify-between items-center px-4 mb-8">
        <a href="/" class="flex items-center gap-2 group">
            <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center shadow-md shadow-primary/10 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-primary-content" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </div>
            <span class="font-extrabold text-xl tracking-tight text-base-content">Influencer Studio</span>
        </a>
        <div class="flex items-center gap-6">
            <nav class="hidden md:flex items-center gap-6 text-sm font-bold text-slate-500">
                <a href="/" class="hover:text-base-content transition-colors">Influencers</a>
                <a href="#" class="hover:text-base-content transition-colors">Inspiration</a>
                <a href="#" class="hover:text-base-content transition-colors">Brand Deals</a>
            </nav>
            <a href="/influencer/create" class="btn btn-sm btn-primary font-bold shadow-md shadow-primary/15 flex items-center gap-1.5 rounded-xl">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Create
            </a>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="flex-1 flex flex-col justify-start items-center max-w-4xl w-full mx-auto relative z-20 pb-16">
        
        {{-- Progress/Step indicator (Steps 1-5 using standard DaisyUI steps) --}}
        <div class="w-full max-w-xl flex justify-center mb-8 px-4">
            <ul class="steps steps-horizontal w-full">
                <li class="step {{ $step >= 1 ? 'step-primary text-primary font-extrabold' : 'text-slate-500' }} text-[10px] tracking-wider">BASICS</li>
                <li class="step {{ $step >= 2 ? 'step-primary text-primary font-extrabold' : 'text-slate-500' }} text-[10px] tracking-wider">REFERENCES</li>
                <li class="step {{ $step >= 3 ? 'step-primary text-primary font-extrabold' : 'text-slate-500' }} text-[10px] tracking-wider">STORY</li>
                <li class="step {{ $step >= 4 ? 'step-primary text-primary font-extrabold' : 'text-slate-500' }} text-[10px] tracking-wider">LOOK</li>
                <li class="step {{ $step >= 5 ? 'step-primary text-primary font-extrabold' : 'text-slate-500' }} text-[10px] tracking-wider">GENERATE</li>
            </ul>
        </div>

        {{-- Glowing Top Promo Banners --}}
        @if($step <= 4 && (! $this->activeTeam?->fal_api_key || ! $this->activeTeam?->claude_api_key))
            <div class="w-full max-w-2xl flex flex-col gap-3 mb-8 px-4">
                @if(! $this->activeTeam?->fal_api_key)
                    <div class="alert alert-info shadow-sm rounded-2xl p-4 flex items-center justify-between gap-4 border border-info/20 bg-info/5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-info text-info-content flex items-center justify-center shadow">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h4 class="font-extrabold text-sm text-info tracking-wide uppercase">{{ __('dialogs.api.fal.label') }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('dialogs.api.fal.description') }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="openConnectModal" class="btn btn-sm btn-info text-info-content font-bold px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1 shadow-sm">
                            {{__('dialogs.api.fal.button')}} <span class="font-mono">→</span>
                        </button>
                    </div>
                @endif

                @if(! $this->activeTeam?->claude_api_key)
                    <div class="alert alert-warning shadow-sm rounded-2xl p-4 flex items-center justify-between gap-4 border border-warning/20 bg-warning/5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-warning text-warning-content flex items-center justify-center shadow">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.25.59 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.17 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.1c-.77-.56-.37-1.81.59-1.81h4.907a1 1 0 00.95-.69l1.52-4.674z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h4 class="font-extrabold text-sm text-warning tracking-wide uppercase">{{ __('dialogs.api.claude.label') }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('dialogs.api.claude.description') }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="openConnectModal" class="btn btn-sm btn-warning text-warning-content font-bold px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1 shadow-sm">
                            {{ __('dialogs.api.claude.button') }} <span class="font-mono">→</span>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Form Card wrapper using base classes --}}
        <div class="w-full max-w-2xl bg-base-100 border border-base-300 rounded-3xl p-6 md:p-8 shadow-md relative">
            
            {{-- Step 1: Basics --}}
            @if($step === 1)
                <div class="space-y-6">
                    <div class="text-left">
                        <h2 class="text-2xl font-bold text-base-content tracking-tight">Name your influencer</h2>
                        <p class="text-slate-500 text-sm mt-1">Start with the basics — you can always refine later.</p>
                    </div>

                    <div class="space-y-5">
                        {{-- Name --}}
                        <div class="text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2 uppercase">Name</label>
                            <input type="text" wire:model="name" placeholder="e.g. Luna Rose" class="w-full bg-base-200 border border-base-300 rounded-xl px-4 py-3 text-base-content placeholder-slate-500 focus:outline-none focus:border-primary transition-all text-sm h-11" />
                            @error('name') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Gender --}}
                        <div class="text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2 uppercase">Gender</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" wire:click="$set('gender', 'Female')" class="flex flex-col items-center justify-center p-4 rounded-xl border transition-all {{ $gender === 'Female' ? 'border-primary bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-200/50 text-slate-500 hover:border-slate-400' }}">
                                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4a6 6 0 016 6c0 2.92-2.09 5.36-4.87 5.89L13 19h2a1 1 0 110 2h-6a1 1 0 110-2h2v-3.11l-.13-.02C8.09 15.36 6 12.92 6 10a6 6 0 016-6z"/>
                                    </svg>
                                    <span class="text-xs font-bold uppercase tracking-wider">Female</span>
                                </button>
                                <button type="button" wire:click="$set('gender', 'Male')" class="flex flex-col items-center justify-center p-4 rounded-xl border transition-all {{ $gender === 'Male' ? 'border-primary bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-200/50 text-slate-500 hover:border-slate-400' }}">
                                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5h5v5m0-5l-5 5M10 14a5 5 0 11-7-7 5 5 0 017 7z"/>
                                    </svg>
                                    <span class="text-xs font-bold uppercase tracking-wider">Male</span>
                                </button>
                            </div>
                        </div>

                        {{-- Age --}}
                        <div class="text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2 uppercase">Age</label>
                            <input type="number" wire:model="age" placeholder="e.g. 24" class="w-full bg-base-200 border border-base-300 rounded-xl px-4 py-3 text-base-content placeholder-slate-500 focus:outline-none focus:border-primary transition-all text-sm h-11" />
                            @error('age') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Niche --}}
                        <div class="text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2 uppercase">Niche <span class="text-[9px] text-slate-500 normal-case font-semibold">(pick all that apply)</span></label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($available_niches as $n)
                                    <button type="button" wire:click="toggleNiche('{{ $n }}')" class="px-4 py-2 text-xs font-bold rounded-full transition-all border {{ in_array($n, $this->niches) ? 'border-primary bg-primary text-primary-content shadow-sm' : 'border-base-300 bg-base-200/50 text-slate-500 hover:border-slate-450 hover:text-base-content' }}">
                                        {{ $n }}
                                    </button>
                                @endforeach
                            </div>
                            @error('niches') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex justify-end">
                        <button type="button" wire:click="nextStep" class="btn btn-primary font-bold px-8 shadow-md shadow-primary/10">
                            Continue <span class="font-mono">→</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 2: References --}}
            @if($step === 2)
                <div class="space-y-6">
                    <div class="text-left">
                        <h2 class="text-2xl font-bold text-base-content tracking-tight">Add references</h2>
                        <p class="text-slate-500 text-sm mt-1">Both optional — the more you give, the closer the result.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Face Reference --}}
                        <div class="flex flex-col text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2.5 uppercase">Face Reference <span class="text-[9px] text-slate-550 normal-case font-semibold">(optional)</span></label>
                            <div class="flex-1 min-h-[200px] border-2 border-dashed border-base-300 hover:border-primary bg-base-200/30 rounded-2xl flex flex-col items-center justify-center p-6 text-center cursor-pointer relative overflow-hidden group transition-colors">
                                <input type="file" wire:model="face_reference" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" />
                                
                                @if($face_reference)
                                    <img src="{{ $face_reference->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-cover rounded-2xl" />
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <button type="button" wire:click="$set('face_reference', null)" class="btn btn-circle btn-error text-white btn-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-full bg-base-300 border border-base-350 flex items-center justify-center text-slate-500 mb-3 group-hover:scale-105 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-base-content">Upload photo</span>
                                    <span class="text-[10px] text-slate-500 mt-1">A photo of the face you want</span>
                                @endif
                            </div>
                            @error('face_reference') <span class="text-xs text-error mt-2 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Style Reference --}}
                        <div class="flex flex-col text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2.5 uppercase">Style Reference <span class="text-[9px] text-slate-550 normal-case font-semibold">(optional)</span></label>
                            <div class="flex-1 min-h-[200px] border-2 border-dashed border-base-300 hover:border-primary bg-base-200/30 rounded-2xl flex flex-col items-center justify-center p-6 text-center cursor-pointer relative overflow-hidden group transition-colors">
                                <input type="file" wire:model="style_reference" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" />
                                
                                @if($style_reference)
                                    <img src="{{ $style_reference->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-cover rounded-2xl" />
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <button type="button" wire:click="$set('style_reference', null)" class="btn btn-circle btn-error text-white btn-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-full bg-base-300 border border-base-350 flex items-center justify-center text-slate-500 mb-3 group-hover:scale-105 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-base-content">Upload photo</span>
                                    <span class="text-[10px] text-slate-500 mt-1">Outfit, aesthetic, or vibe inspo</span>
                                @endif
                            </div>
                            @error('style_reference') <span class="text-xs text-error mt-2 block font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex justify-between">
                        <button type="button" wire:click="previousStep" class="btn btn-sm btn-outline border-base-300 font-bold px-6">
                            ← Back
                        </button>
                        <button type="button" wire:click="nextStep" class="btn btn-primary font-bold px-8 shadow-md shadow-primary/10">
                            Continue <span class="font-mono">→</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 3: Story --}}
            @if($step === 3)
                <div class="space-y-6">
                    <div class="text-left">
                        <h2 class="text-2xl font-bold text-base-content tracking-tight">Who are they?</h2>
                        <p class="text-slate-500 text-sm mt-1">Their story, vibe, what makes them different.</p>
                    </div>

                    <div class="space-y-5">
                        {{-- Backstory --}}
                        <div class="text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2 uppercase">Backstory <span class="text-[9px] text-slate-550 normal-case font-semibold">(optional)</span></label>
                            <textarea wire:model="backstory" placeholder="Their background, what drives them, what makes them unique..." rows="6" class="w-full bg-base-200 border border-base-300 rounded-xl p-4 text-xs text-base-content placeholder-slate-550 focus:outline-none focus:border-primary transition-all"></textarea>
                            @error('backstory') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Personality --}}
                        <div class="text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2 uppercase">Personality</label>
                            
                            {{-- Range Slider --}}
                            <div class="py-4 px-2">
                                <input type="range" wire:model.live="personality" min="0" max="100" class="w-full h-1.5 bg-base-300 rounded-lg appearance-none cursor-pointer accent-primary" />
                                <div class="flex justify-between text-[10px] text-slate-500 font-extrabold tracking-wider mt-3">
                                    <span>INTROVERT</span>
                                    <span>EXTROVERT</span>
                                </div>
                            </div>
                            
                            {{-- Dynamic personality badge --}}
                            <div class="flex justify-center mt-1">
                                <div class="bg-primary/10 border border-primary/20 text-primary font-bold text-xs rounded-xl px-4 py-2 shadow-inner">
                                    @if($personality < 35)
                                        Quiet & Thoughtful
                                    @elseif($personality >= 35 && $personality <= 65)
                                        Balanced & versatile
                                    @else
                                        Outgoing & Energetic
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex justify-between">
                        <button type="button" wire:click="previousStep" class="btn btn-sm btn-outline border-base-300 font-bold px-6">
                            ← Back
                        </button>
                        <button type="button" wire:click="nextStep" class="btn btn-primary font-bold px-8 shadow-md shadow-primary/10">
                            Continue <span class="font-mono">→</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 4: Physical appearance --}}
            @if($step === 4)
                <div class="space-y-6">
                    <div class="flex justify-between items-start">
                        <div class="text-left">
                            <h2 class="text-2xl font-bold text-base-content tracking-tight">Physical appearance</h2>
                        </div>
                        <button type="button" wire:click="randomizeLook" class="btn btn-sm btn-outline border-base-300 font-bold text-xs rounded-xl px-4 py-2 transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Randomize
                        </button>
                    </div>

                    <div class="space-y-6 max-h-[50vh] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-base-300">
                        {{-- Ethnicity --}}
                        <div class="text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2.5 uppercase flex items-center gap-1">
                                <span>🌐</span> ETHNICITY
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($ethnicities as $eth)
                                    <button type="button" wire:click="$set('ethnicity', '{{ $eth }}')" class="px-3.5 py-2 text-xs font-bold rounded-full border transition-all {{ $ethnicity === $eth ? 'border-primary bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-200/50 text-slate-500 hover:border-slate-400 hover:text-base-content' }}">
                                        {{ $eth }}
                                    </button>
                                @endforeach
                            </div>
                            @error('ethnicity') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Skin Tone --}}
                        <div class="text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2.5 uppercase flex items-center gap-1">
                                <span>🤎</span> SKIN TONE
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($skin_tones as $tone)
                                    @php
                                        $toneColors = [
                                            'Fair' => '#ffedd5',
                                            'Light' => '#fed7aa',
                                            'Medium' => '#fdba74',
                                            'Tan' => '#f97316',
                                            'Brown' => '#c2410c',
                                            'Deep' => '#7c2d12',
                                            'Ebony' => '#451a03',
                                        ];
                                    @endphp
                                    <button type="button" wire:click="$set('skin_tone', '{{ $tone }}')" class="px-3.5 py-2 text-xs font-bold rounded-full border transition-all flex items-center gap-1.5 {{ $skin_tone === $tone ? 'border-primary bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-200/50 text-slate-500 hover:border-slate-400 hover:text-base-content' }}">
                                        <span class="w-3.5 h-3.5 rounded-full border border-black/10" style="background-color: {{ $toneColors[$tone] ?? '#fff' }}"></span>
                                        {{ $tone }}
                                    </button>
                                @endforeach
                            </div>
                            @error('skin_tone') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Hair --}}
                        <div class="space-y-4 border-t border-base-300/40 pt-4 text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider uppercase flex items-center gap-1">
                                <span>💇</span> HAIR
                            </label>
                            
                            {{-- Color --}}
                            <div class="flex flex-wrap gap-2">
                                @foreach($hair_colors as $color)
                                    @php
                                        $hairColors = [
                                            'Blonde' => '#fde047',
                                            'Brunette' => '#854d0e',
                                            'Black' => '#18181b',
                                            'Auburn' => '#c2410c',
                                            'Red' => '#ef4444',
                                            'Silver' => '#cbd5e1',
                                            'Dyed' => '#ec4899',
                                        ];
                                    @endphp
                                    <button type="button" wire:click="$set('hair_color', '{{ $color }}')" class="px-3.5 py-2 text-xs font-bold rounded-full border transition-all flex items-center gap-1.5 {{ $hair_color === $color ? 'border-primary bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-200/50 text-slate-500 hover:border-slate-400 hover:text-base-content' }}">
                                        <span class="w-3.5 h-3.5 rounded-full border border-black/10" style="background-color: {{ $hairColors[$color] ?? '#fff' }}"></span>
                                        {{ $color }}
                                    </button>
                                @endforeach
                            </div>
                            @error('hair_color') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror

                            {{-- Length & Texture Row --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2">LENGTH</span>
                                    <div class="grid grid-cols-4 gap-1 bg-base-200 p-1 border border-base-300 rounded-xl">
                                        @foreach($hair_lengths as $len)
                                            <button type="button" wire:click="$set('hair_length', '{{ $len }}')" class="py-2 text-[10px] font-bold rounded-lg transition-all {{ $hair_length === $len ? 'bg-primary text-primary-content shadow-sm' : 'text-slate-500 hover:text-base-content' }}">
                                                {{ $len }}
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('hair_length') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <span class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2">TEXTURE</span>
                                    <div class="grid grid-cols-4 gap-1 bg-base-200 p-1 border border-base-300 rounded-xl">
                                        @foreach($hair_textures as $textur)
                                            <button type="button" wire:click="$set('hair_texture', '{{ $textur }}')" class="py-2 text-[10px] font-bold rounded-lg transition-all {{ $hair_texture === $textur ? 'bg-primary text-primary-content shadow-sm' : 'text-slate-500 hover:text-base-content' }}">
                                                {{ $textur }}
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('hair_texture') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Eye Color --}}
                        <div class="border-t border-base-300/40 pt-4 text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2.5 uppercase flex items-center gap-1">
                                <span>👁️</span> EYE COLOR
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($eye_colors as $eye)
                                    @php
                                        $eyeColors = [
                                            'Blue' => '#3b82f6',
                                            'Green' => '#22c55e',
                                            'Brown' => '#78350f',
                                            'Hazel' => '#84cc16',
                                            'Dark' => '#1c1917',
                                            'Grey' => '#94a3b8',
                                        ];
                                    @endphp
                                    <button type="button" wire:click="$set('eye_color', '{{ $eye }}')" class="px-3.5 py-2 text-xs font-bold rounded-full border transition-all flex items-center gap-1.5 {{ $eye_color === $eye ? 'border-primary bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-200/50 text-slate-500 hover:border-slate-400 hover:text-base-content' }}">
                                        <span class="w-3.5 h-3.5 rounded-full border border-black/10" style="background-color: {{ $eyeColors[$eye] ?? '#fff' }}"></span>
                                        {{ $eye }}
                                    </button>
                                @endforeach
                            </div>
                            @error('eye_color') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Build --}}
                        <div class="border-t border-base-300/40 pt-4 text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2.5 uppercase flex items-center gap-1">
                                <span>💪</span> BUILD
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($builds as $b)
                                    <button type="button" wire:click="$set('build', '{{ $b }}')" class="px-3.5 py-2 text-xs font-bold rounded-full border transition-all {{ $build === $b ? 'border-primary bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-200/50 text-slate-500 hover:border-slate-400 hover:text-base-content' }}">
                                        {{ $b }}
                                    </button>
                                @endforeach
                            </div>
                            @error('build') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Custom Description --}}
                        <div class="border-t border-base-300/40 pt-4 text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-2 uppercase flex items-center gap-1">
                                <span>✍️</span> CUSTOM DESCRIPTION <span class="text-[9px] text-slate-550 normal-case font-semibold">(optional)</span>
                            </label>
                            <textarea wire:model="custom_description" placeholder="Anything else — freckles, dimples, beauty mark, tattoos..." rows="3" class="w-full bg-base-200 border border-base-300 rounded-xl px-4 py-3 text-base-content placeholder-slate-500 focus:outline-none focus:border-primary transition-all text-xs"></textarea>
                            @error('custom_description') <span class="text-xs text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Aesthetic Vibe --}}
                        <div class="border-t border-base-300/40 pt-4 text-left">
                            <label class="block text-[10px] font-extrabold text-slate-500 tracking-wider mb-3 uppercase flex items-center gap-1">
                                <span>✨</span> AESTHETIC VIBE <span class="text-[9px] text-slate-550 normal-case font-semibold">(optional)</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($aesthetic_vibes as $v)
                                    <button type="button" wire:click="$set('aesthetic_vibe', '{{ $v['name'] }}')" class="p-4 text-left border rounded-2xl transition-all {{ $aesthetic_vibe === $v['name'] ? 'border-primary bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-200/50 text-slate-500 hover:border-slate-400 hover:text-base-content' }}">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="w-5 h-5 flex items-center justify-center bg-base-300 border border-base-350 rounded-lg text-primary">
                                                <x-icon name="{{ $v['icon'] }}" class="w-3.5 h-3.5" />
                                            </span>
                                            <span class="font-bold text-sm text-base-content">{{ $v['name'] }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-normal">{{ $v['desc'] }}</p>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @if($this->getActiveTeam()?->claude_api_key || $claude_api_key)
                            <div class="border-t border-base-300/40 pt-4 text-left mb-4">
                                <div class="flex items-center gap-2 px-1 py-1 bg-violet-500/10 border border-violet-500/20 rounded-2xl max-w-sm">
                                    <label class="flex items-center gap-2 cursor-pointer w-full py-1.5 px-3">
                                        <input type="checkbox" wire:model="use_prompt_enhancer" class="checkbox checkbox-primary checkbox-xs rounded-md" />
                                        <div class="text-left">
                                            <span class="block text-[10px] font-extrabold text-violet-650 uppercase tracking-wide">Claude Prompt Enhancer</span>
                                            <span class="block text-[8px] text-slate-500">Auto-refine influencer prompts for higher fidelity</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        @endif
                        <button type="button" wire:click="nextStep" class="btn btn-primary font-bold px-8 shadow-md shadow-primary/15">
                            Continue to Generate <span class="font-mono">→</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 5: Generate & Success --}}
            @if($step === 5)
                @if(! $is_done)
                    {{-- Choose a Look Screen --}}
                    <div class="py-4 space-y-8 flex flex-col items-center" wire:init="generate">
                        <div class="text-center">
                            <h2 class="text-3xl font-extrabold text-base-content tracking-tight">Choose a Look</h2>
                            <p class="text-slate-500 text-sm mt-1 max-w-md">Select your favorite look variation from the options generated below.</p>
                        </div>

                        {{-- Look Variations Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl w-full">
                            @foreach($generated_variations as $index => $var)
                                <div class="h-96 w-full relative rounded-3xl overflow-hidden shadow-lg border transition-all duration-300 bg-base-200/40
                                    {{ ($var['status'] ?? '') === 'success' ? 'cursor-pointer hover:shadow-2xl' : '' }}
                                    {{ $selected_variation_index === $index && ($var['status'] ?? '') === 'success' ? 'border-primary ring-4 ring-primary/20 scale-[1.02]' : 'border-base-300' }}">
                                    
                                    @if(in_array($var['status'] ?? '', ['pending', 'processing']))
                                        {{-- Loading Frame --}}
                                        <div class="absolute inset-0 bg-base-300/30 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center space-y-4">
                                            <div class="relative w-16 h-16 flex items-center justify-center">
                                                <div class="absolute inset-0 bg-primary rounded-full blur animate-pulse opacity-25"></div>
                                                <div class="w-12 h-12 bg-base-100 rounded-full flex items-center justify-center shadow-inner relative">
                                                    <svg class="w-5 h-5 text-primary animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-extrabold tracking-wider text-slate-500 uppercase animate-pulse">
                                                    @if(($var['queue_status'] ?? '') === 'IN_QUEUE')
                                                        In Queue...
                                                    @elseif(($var['queue_status'] ?? '') === 'IN_PROGRESS')
                                                        Generating...
                                                    @else
                                                        Generating...
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    @elseif(($var['status'] ?? '') === 'failed')
                                        {{-- Failed Frame --}}
                                        <div class="absolute inset-0 bg-error/5 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center space-y-4">
                                            <div class="w-12 h-12 rounded-full bg-error/10 border border-error/20 flex items-center justify-center text-error shadow-sm">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-bold text-base-content">Generation failed</h4>
                                                @if(isset($var['error']))
                                                    <p class="text-[10px] text-slate-500 mt-1 line-clamp-2 max-w-[180px]" title="{{ $var['error'] }}">
                                                        {{ str_contains(strtolower($var['error']), 'content filter') ? 'Blocked by content safety filter.' : $var['error'] }}
                                                    </p>
                                                @endif
                                            </div>
                                            <button type="button" wire:click="retryGeneration({{ $index }})" class="btn btn-xs btn-error text-white font-bold px-3 py-1 rounded-lg flex items-center gap-1 shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/>
                                                </svg>
                                                Try Again
                                            </button>
                                        </div>
                                    @else
                                        {{-- Successful Image Frame --}}
                                        <div class="absolute inset-0" wire:click="$set('selected_variation_index', {{ $index }})">
                                            <img src="{{ $var['url'] }}" class="h-full w-full object-cover select-none" />
                                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent p-4 flex justify-between items-center">
                                                <span class="text-xs font-bold text-white uppercase tracking-wider">Option {{ $index + 1 }}</span>
                                                @if($selected_variation_index === $index)
                                                    <span class="w-6 h-6 rounded-full bg-primary flex items-center justify-center text-white border border-white">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Actions --}}
                        @php
                            $isAnyPending = collect($generated_variations)->contains('status', 'pending');
                            $isSelectedSuccess = ($generated_variations[$selected_variation_index]['status'] ?? '') === 'success';
                        @endphp
                        <div class="flex flex-col sm:flex-row gap-3 w-full max-w-sm pt-4">
                            <button type="button" 
                                    @if($isAnyPending) disabled @endif
                                    wire:click="previousStep" 
                                    class="flex-1 btn btn-outline border-base-300 font-bold rounded-xl btn-ghost {{ $isAnyPending ? 'btn-disabled opacity-50' : '' }}">
                                ← Re-configure
                            </button>
                            <button type="button" 
                                    @if($isAnyPending || !$isSelectedSuccess) disabled @endif
                                    wire:click="finishGeneration" 
                                    class="flex-1 btn btn-primary font-bold shadow-md shadow-primary/10 rounded-xl {{ ($isAnyPending || !$isSelectedSuccess) ? 'btn-disabled opacity-50' : '' }}">
                                Save & Finish →
                            </button>
                        </div>
                    </div>
                @else
                    {{-- Success Screen --}}
                    <div class="py-4 space-y-8 flex flex-col items-center">
                        <div class="text-center">
                            {{-- Success Checkmark --}}
                            <div class="w-16 h-16 rounded-full bg-success/10 border-2 border-success flex items-center justify-center text-success mx-auto mb-4 shadow-sm shadow-success/15 scale-105">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-extrabold text-base-content tracking-tight">Persona Synthesized!</h2>
                            <p class="text-slate-500 text-sm mt-1">Your new digital influencer is ready to be used.</p>
                        </div>

                        {{-- Influencer card preview --}}
                        <div class="w-72 bg-base-100 border border-base-300 rounded-3xl overflow-hidden shadow-2xl relative group">
                            <div class="h-96 w-full relative overflow-hidden">
                                <img src="{{ $generated_avatar }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-102" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                                
                                {{-- Stage info overlay --}}
                                <div class="absolute bottom-6 left-6 right-6 text-left">
                                    <h3 class="text-xl font-bold text-white tracking-tight leading-tight">{{ $name }}</h3>
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        @foreach(array_slice($niches, 0, 2) as $nc)
                                            <span class="px-2 py-0.5 bg-primary/20 border border-primary/30 rounded-md text-[10px] font-bold text-white uppercase tracking-wider">{{ $nc }}</span>
                                        @endforeach
                                        <span class="px-2 py-0.5 bg-slate-800/80 border border-slate-700 rounded-md text-[10px] font-bold text-slate-300 uppercase tracking-wider">{{ $age }} Years</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-col sm:flex-row gap-3 w-full max-w-sm pt-4">
                            <a href="/" class="flex-1 btn btn-primary font-bold shadow-md shadow-primary/10 rounded-xl">
                                Go to Dashboard
                            </a>
                            <button type="button" wire:click="$set('step', 1); $set('is_done', false); $set('name', ''); $set('age', ''); $set('niches', []); $set('face_reference', null); $set('style_reference', null); $set('backstory', ''); $set('personality', 50); $set('ethnicity', 'White'); $set('skin_tone', 'Fair'); $set('hair_color', 'Blonde'); $set('hair_length', 'Long'); $set('hair_texture', 'Straight'); $set('eye_color', 'Blue'); $set('build', 'Petite'); $set('custom_description', ''); $set('aesthetic_vibe', '');" class="flex-1 btn btn-outline border-base-300 font-bold rounded-xl btn-ghost">
                                Create Another
                            </button>
                        </div>
                    </div>
                @endif
            @endif

        </div>

    </main>

    {{-- Footer --}}
    <footer class="max-w-7xl w-full mx-auto text-center text-xs text-slate-500 px-4 mt-8 relative z-20">
        <p>&copy; {{ date('Y') }} Influencer Studio. All rights reserved. Powered by Higgsfield & Claude.</p>
    </footer>

    <!-- API Keys Modal -->
    <x-modal wire:model="showConnectModal" title="API-Schlüssel verbinden">
        <x-form wire:submit="saveApiKeys">
            <x-input label="FAL.AI API Key" wire:model="fal_api_key" type="password" placeholder="fal_..." />
            <x-input label="Claude API Key" wire:model="claude_api_key" type="password" placeholder="sk-ant-..." />

            <x-slot:actions>
                <x-button label="Abbrechen" wire:click="$set('showConnectModal', false)" class="btn-ghost" />
                <x-button label="Speichern" type="submit" class="btn-primary font-bold rounded-xl px-5" spinner="saveApiKeys" />
            </x-slot:actions>
        </x-form>
    </x-modal>

</div>

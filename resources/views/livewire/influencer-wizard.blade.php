<div class="min-h-screen text-slate-100 flex flex-col justify-between py-6 px-4 relative overflow-hidden bg-slate-950 selection:bg-purple-500 selection:text-white" x-data>
    
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
        }" class="relative h-64 w-40 rounded-2xl overflow-hidden shadow-2xl transition-all duration-500 transform border border-white/5 rotate-[-6deg]"
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
        }" class="relative h-64 w-40 rounded-2xl overflow-hidden shadow-2xl transition-all duration-500 transform border border-white/5 rotate-[5deg] mt-12"
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
        }" class="relative h-64 w-40 rounded-2xl overflow-hidden shadow-2xl transition-all duration-500 transform border border-white/5 rotate-[8deg]"
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
        }" class="relative h-64 w-40 rounded-2xl overflow-hidden shadow-2xl transition-all duration-500 transform border border-white/5 rotate-[-5deg] mt-12"
           :class="fade ? 'opacity-25 scale-95' : 'opacity-100 scale-100'">
            <img :src="images[currentIdx]" class="h-full w-full object-cover rounded-2xl" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        </div>
    </div>

    {{-- Top Navbar --}}
    <header class="max-w-7xl w-full mx-auto flex justify-between items-center px-4 mb-8">
        <a href="/" class="flex items-center gap-2 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-pink-500 to-purple-600 flex items-center justify-center shadow-lg shadow-purple-500/20 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </div>
            <span class="font-bold text-xl tracking-tight bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">Influencer Studio</span>
        </a>
        <div class="flex items-center gap-6">
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-400">
                <a href="/" class="hover:text-white transition-colors">Influencers</a>
                <a href="#" class="hover:text-white transition-colors">Inspiration</a>
                <a href="#" class="hover:text-white transition-colors">Brand Deals</a>
            </nav>
            <a href="/influencer/create" class="bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold text-sm px-4 py-2 rounded-xl shadow-lg shadow-purple-500/20 hover:opacity-95 transition-opacity flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create
            </a>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="flex-1 flex flex-col justify-start items-center max-w-4xl w-full mx-auto relative z-20 pb-16">
        
        {{-- Progress/Step indicator (Steps 1-5) --}}
        <div class="w-full max-w-xl flex items-center justify-between mb-8 px-4">
            @php
                $steps = [
                    1 => ['name' => 'BASICS', 'num' => '1'],
                    2 => ['name' => 'REFERENCES', 'num' => '2'],
                    3 => ['name' => 'STORY', 'num' => '3'],
                    4 => ['name' => 'LOOK', 'num' => '4'],
                    5 => ['name' => 'GENERATE', 'num' => '5']
                ];
            @endphp
            @foreach($steps as $sIdx => $sData)
                <div class="flex flex-col items-center flex-1 relative">
                    {{-- Connecting Line --}}
                    @if($sIdx > 1)
                        <div class="absolute right-[50%] top-4 w-full h-[2px] -z-10 {{ $step >= $sIdx ? 'bg-purple-500' : 'bg-slate-800' }}"></div>
                    @endif
                    
                    {{-- Step Circle --}}
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $step === $sIdx ? 'bg-purple-500 text-white ring-4 ring-purple-500/20' : ($step > $sIdx ? 'bg-purple-600 text-white' : 'bg-slate-900 border-2 border-slate-800 text-slate-500') }}">
                        @if($step > $sIdx)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            {{ $sData['num'] }}
                        @endif
                    </div>
                    <span class="text-[10px] font-bold tracking-wider mt-2 {{ $step === $sIdx ? 'text-white' : ($step > $sIdx ? 'text-purple-400' : 'text-slate-500') }}">{{ $sData['name'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- Glowing Top Promo Banners --}}
        @if($step <= 4)
            <div class="w-full max-w-2xl flex flex-col gap-3 mb-8 px-4">
                <div class="border border-[#c0ff00]/40 bg-[#c0ff00]/5 shadow-[0_0_15px_-3px_rgba(192,255,0,0.1)] rounded-2xl p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#c0ff00] flex items-center justify-center shadow-lg shadow-[#c0ff00]/10 text-black">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-[#c0ff00] tracking-wide uppercase">Connect to Higgsfield</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Integrate video generation capabilities directly.</p>
                        </div>
                    </div>
                    <button class="bg-[#c0ff00] hover:bg-[#a6db00] text-black font-bold text-xs px-3.5 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                        Connect <span class="font-mono">→</span>
                    </button>
                </div>

                @if($step === 1)
                    <div class="border border-amber-500/40 bg-amber-500/5 shadow-[0_0_15px_-3px_rgba(245,158,11,0.1)] rounded-2xl p-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center shadow-lg shadow-amber-500/10 text-black">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.25.59 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.17 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.1c-.77-.56-.37-1.81.59-1.81h4.907a1 1 0 00.95-.69l1.52-4.674z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-amber-500 tracking-wide uppercase">Connect Claude for smarter prompts</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Let AI enhance your backstory and visual traits automatically.</p>
                            </div>
                        </div>
                        <button class="bg-amber-500 hover:bg-amber-600 text-black font-bold text-xs px-3.5 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                            Connect <span class="font-mono">→</span>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Form Cards --}}
        <div class="w-full max-w-2xl bg-slate-900/60 backdrop-blur-md border border-white/5 rounded-3xl p-6 md:p-8 shadow-2xl relative">
            
            {{-- Step 1: Basics --}}
            @if($step === 1)
                <div class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-white tracking-tight">Name your influencer</h2>
                        <p class="text-slate-400 text-sm mt-1">Start with the basics — you can always refine later.</p>
                    </div>

                    <div class="space-y-5">
                        {{-- Name --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2 uppercase">Name</label>
                            <input type="text" wire:model="name" placeholder="e.g. Luna Rose" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all" />
                            @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Gender --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2 uppercase">Gender</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" wire:click="$set('gender', 'Female')" class="flex flex-col items-center justify-center p-4 rounded-xl border transition-all {{ $gender === 'Female' ? 'border-purple-500 bg-purple-950/20 text-purple-200 shadow-lg shadow-purple-500/10' : 'border-slate-800 bg-slate-950/40 text-slate-500 hover:border-slate-700' }}">
                                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4a6 6 0 016 6c0 2.92-2.09 5.36-4.87 5.89L13 19h2a1 1 0 110 2h-6a1 1 0 110-2h2v-3.11l-.13-.02C8.09 15.36 6 12.92 6 10a6 6 0 016-6z"/>
                                    </svg>
                                    <span class="text-sm font-semibold">Female</span>
                                </button>
                                <button type="button" wire:click="$set('gender', 'Male')" class="flex flex-col items-center justify-center p-4 rounded-xl border transition-all {{ $gender === 'Male' ? 'border-purple-500 bg-purple-950/20 text-purple-200 shadow-lg shadow-purple-500/10' : 'border-slate-800 bg-slate-950/40 text-slate-500 hover:border-slate-700' }}">
                                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5h5v5m0-5l-5 5M10 14a5 5 0 11-7-7 5 5 0 017 7z"/>
                                    </svg>
                                    <span class="text-sm font-semibold">Male</span>
                                </button>
                            </div>
                        </div>

                        {{-- Age --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2 uppercase">Age</label>
                            <input type="number" wire:model="age" placeholder="e.g. 24" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all" />
                            @error('age') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Niche --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2 uppercase">Niche <span class="text-[10px] text-slate-500 normal-case">(pick all that apply)</span></label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($available_niches as $n)
                                    <button type="button" wire:click="toggleNiche('{{ $n }}')" class="px-4 py-2 text-xs font-semibold rounded-full transition-all border {{ in_array($n, $this->niches) ? 'border-purple-500 bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg shadow-purple-500/15' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700 hover:text-white' }}">
                                        {{ $n }}
                                    </button>
                                @endforeach
                            </div>
                            @error('niches') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex justify-end">
                        <button type="button" wire:click="nextStep" class="bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold rounded-xl px-8 py-3.5 shadow-lg shadow-purple-500/20 hover:opacity-95 transition-all flex items-center gap-2">
                            Continue <span class="font-mono">→</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 2: References --}}
            @if($step === 2)
                <div class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-white tracking-tight">Add references</h2>
                        <p class="text-slate-400 text-sm mt-1">Both optional — the more you give, the closer the result.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Face Reference --}}
                        <div class="flex flex-col">
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2 uppercase">Face Reference <span class="text-[10px] text-slate-500 normal-case">(optional)</span></label>
                            <div class="flex-1 min-h-[200px] border-2 border-dashed border-slate-800 hover:border-slate-700 bg-slate-950/40 rounded-2xl flex flex-col items-center justify-center p-6 text-center cursor-pointer relative overflow-hidden group">
                                <input type="file" wire:model="face_reference" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" />
                                
                                @if($face_reference)
                                    <img src="{{ $face_reference->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-cover rounded-2xl" />
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <button type="button" wire:click="$set('face_reference', null)" class="bg-rose-500 text-white rounded-full p-2 hover:bg-rose-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-white">Upload photo</span>
                                    <span class="text-[10px] text-slate-500 mt-1">A photo of the face you want</span>
                                @endif
                            </div>
                            @error('face_reference') <span class="text-xs text-rose-500 mt-2 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Style Reference --}}
                        <div class="flex flex-col">
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2 uppercase">Style Reference <span class="text-[10px] text-slate-500 normal-case">(optional)</span></label>
                            <div class="flex-1 min-h-[200px] border-2 border-dashed border-slate-800 hover:border-slate-700 bg-slate-950/40 rounded-2xl flex flex-col items-center justify-center p-6 text-center cursor-pointer relative overflow-hidden group">
                                <input type="file" wire:model="style_reference" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" />
                                
                                @if($style_reference)
                                    <img src="{{ $style_reference->temporaryUrl() }}" class="absolute inset-0 h-full w-full object-cover rounded-2xl" />
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <button type="button" wire:click="$set('style_reference', null)" class="bg-rose-500 text-white rounded-full p-2 hover:bg-rose-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-white">Upload photo</span>
                                    <span class="text-[10px] text-slate-500 mt-1">Outfit, aesthetic, or vibe inspo</span>
                                @endif
                            </div>
                            @error('style_reference') <span class="text-xs text-rose-500 mt-2 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex justify-between">
                        <button type="button" wire:click="previousStep" class="border border-slate-850 hover:bg-slate-900 bg-slate-950/40 text-slate-300 font-semibold rounded-xl px-6 py-3.5 transition-colors">
                            ← Back
                        </button>
                        <button type="button" wire:click="nextStep" class="bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold rounded-xl px-8 py-3.5 shadow-lg shadow-purple-500/20 hover:opacity-95 transition-all flex items-center gap-2">
                            Continue <span class="font-mono">→</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 3: Story --}}
            @if($step === 3)
                <div class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-white tracking-tight">Who are they?</h2>
                        <p class="text-slate-400 text-sm mt-1">Their story, vibe, what makes them different.</p>
                    </div>

                    <div class="space-y-5">
                        {{-- Backstory --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2 uppercase">Backstory <span class="text-[10px] text-slate-500 normal-case">(optional)</span></label>
                            <textarea wire:model="backstory" placeholder="Their background, what drives them, what makes them unique..." rows="6" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all"></textarea>
                            @error('backstory') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Personality --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2 uppercase">Personality</label>
                            
                            {{-- Range Slider --}}
                            <div class="py-4 px-2">
                                <input type="range" wire:model.live="personality" min="0" max="100" class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-purple-500" />
                                <div class="flex justify-between text-[11px] text-slate-500 font-bold tracking-wider mt-3">
                                    <span>INTROVERT</span>
                                    <span>EXTROVERT</span>
                                </div>
                            </div>
                            
                            {{-- Dynamic personality badge --}}
                            <div class="flex justify-center mt-1">
                                <div class="bg-purple-950/40 border border-purple-500/30 text-purple-300 font-bold text-xs rounded-xl px-4 py-2 shadow-inner">
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
                        <button type="button" wire:click="previousStep" class="border border-slate-850 hover:bg-slate-900 bg-slate-950/40 text-slate-300 font-semibold rounded-xl px-6 py-3.5 transition-colors">
                            ← Back
                        </button>
                        <button type="button" wire:click="nextStep" class="bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold rounded-xl px-8 py-3.5 shadow-lg shadow-purple-500/20 hover:opacity-95 transition-all flex items-center gap-2">
                            Continue <span class="font-mono">→</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 4: Physical appearance --}}
            @if($step === 4)
                <div class="space-y-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-white tracking-tight">Physical appearance</h2>
                        </div>
                        <button type="button" wire:click="randomizeLook" class="border border-slate-850 hover:bg-slate-900 bg-slate-950/40 text-slate-300 font-bold text-xs rounded-xl px-4 py-2 transition-colors flex items-center gap-1.5 shadow-md">
                            {{-- Dice icon --}}
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Randomize
                        </button>
                    </div>

                    <div class="space-y-6 max-h-[50vh] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-slate-800">
                        {{-- Ethnicity --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2.5 uppercase flex items-center gap-1">
                                <span>🌐</span> ETHNICITY
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($ethnicities as $eth)
                                    <button type="button" wire:click="$set('ethnicity', '{{ $eth }}')" class="px-3.5 py-2 text-xs font-semibold rounded-full border transition-all {{ $ethnicity === $eth ? 'border-purple-500 bg-purple-950/20 text-purple-200 shadow shadow-purple-500/10' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700 hover:text-slate-200' }}">
                                        {{ $eth }}
                                    </button>
                                @endforeach
                            </div>
                            @error('ethnicity') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Skin Tone --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2.5 uppercase flex items-center gap-1">
                                <span>🤎</span> SKIN TONE
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($skin_tones as $tone)
                                    @php
                                        // Colors mapping for swatches
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
                                    <button type="button" wire:click="$set('skin_tone', '{{ $tone }}')" class="px-3.5 py-2 text-xs font-semibold rounded-full border transition-all flex items-center gap-1.5 {{ $skin_tone === $tone ? 'border-purple-500 bg-purple-950/20 text-purple-200 shadow shadow-purple-500/10' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700 hover:text-slate-200' }}">
                                        <span class="w-3.5 h-3.5 rounded-full border border-black/20" style="background-color: {{ $toneColors[$tone] ?? '#fff' }}"></span>
                                        {{ $tone }}
                                    </button>
                                @endforeach
                            </div>
                            @error('skin_tone') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Hair --}}
                        <div class="space-y-4 border-t border-slate-800/40 pt-4">
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider uppercase flex items-center gap-1">
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
                                    <button type="button" wire:click="$set('hair_color', '{{ $color }}')" class="px-3.5 py-2 text-xs font-semibold rounded-full border transition-all flex items-center gap-1.5 {{ $hair_color === $color ? 'border-purple-500 bg-purple-950/20 text-purple-200 shadow shadow-purple-500/10' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700 hover:text-slate-200' }}">
                                        <span class="w-3.5 h-3.5 rounded-full border border-black/20" style="background-color: {{ $hairColors[$color] ?? '#fff' }}"></span>
                                        {{ $color }}
                                    </button>
                                @endforeach
                            </div>
                            @error('hair_color') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror

                            {{-- Length & Texture Row --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-[10px] font-bold text-slate-500 tracking-wider mb-2">LENGTH</span>
                                    <div class="grid grid-cols-4 gap-1 bg-slate-950/80 p-1 border border-slate-850 rounded-xl">
                                        @foreach($hair_lengths as $len)
                                            <button type="button" wire:click="$set('hair_length', '{{ $len }}')" class="py-2 text-[10px] font-bold rounded-lg transition-all {{ $hair_length === $len ? 'bg-purple-950/80 border border-purple-500/20 text-purple-300 shadow-sm' : 'text-slate-400 hover:text-slate-200' }}">
                                                {{ $len }}
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('hair_length') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-slate-500 tracking-wider mb-2">TEXTURE</span>
                                    <div class="grid grid-cols-4 gap-1 bg-slate-950/80 p-1 border border-slate-850 rounded-xl">
                                        @foreach($hair_textures as $textur)
                                            <button type="button" wire:click="$set('hair_texture', '{{ $textur }}')" class="py-2 text-[10px] font-bold rounded-lg transition-all {{ $hair_texture === $textur ? 'bg-purple-950/80 border border-purple-500/20 text-purple-300 shadow-sm' : 'text-slate-400 hover:text-slate-200' }}">
                                                {{ $textur }}
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('hair_texture') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Eye Color --}}
                        <div class="border-t border-slate-800/40 pt-4">
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2.5 uppercase flex items-center gap-1">
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
                                    <button type="button" wire:click="$set('eye_color', '{{ $eye }}')" class="px-3.5 py-2 text-xs font-semibold rounded-full border transition-all flex items-center gap-1.5 {{ $eye_color === $eye ? 'border-purple-500 bg-purple-950/20 text-purple-200 shadow shadow-purple-500/10' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700 hover:text-slate-200' }}">
                                        <span class="w-3.5 h-3.5 rounded-full border border-black/20" style="background-color: {{ $eyeColors[$eye] ?? '#fff' }}"></span>
                                        {{ $eye }}
                                    </button>
                                @endforeach
                            </div>
                            @error('eye_color') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Build --}}
                        <div class="border-t border-slate-800/40 pt-4">
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2.5 uppercase flex items-center gap-1">
                                <span>💪</span> BUILD
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($builds as $b)
                                    <button type="button" wire:click="$set('build', '{{ $b }}')" class="px-3.5 py-2 text-xs font-semibold rounded-full border transition-all {{ $build === $b ? 'border-purple-500 bg-purple-950/20 text-purple-200 shadow shadow-purple-500/10' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700 hover:text-slate-200' }}">
                                        {{ $b }}
                                    </button>
                                @endforeach
                            </div>
                            @error('build') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Custom Description --}}
                        <div class="border-t border-slate-800/40 pt-4">
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-2 uppercase flex items-center gap-1">
                                <span>✍️</span> CUSTOM DESCRIPTION <span class="text-[10px] text-slate-500 normal-case">(optional)</span>
                            </label>
                            <textarea wire:model="custom_description" placeholder="Anything else — freckles, dimples, beauty mark, tattoos..." rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all"></textarea>
                            @error('custom_description') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Aesthetic Vibe --}}
                        <div class="border-t border-slate-800/40 pt-4">
                            <label class="block text-[11px] font-bold text-slate-400 tracking-wider mb-3 uppercase flex items-center gap-1">
                                <span>✨</span> AESTHETIC VIBE <span class="text-[10px] text-slate-500 normal-case">(optional)</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($aesthetic_vibes as $v)
                                    <button type="button" wire:click="$set('aesthetic_vibe', '{{ $v['name'] }}')" class="p-4 text-left border rounded-2xl transition-all {{ $aesthetic_vibe === $v['name'] ? 'border-purple-500 bg-purple-950/20 text-purple-200 shadow-lg shadow-purple-500/10' : 'border-slate-850 bg-slate-950/40 text-slate-400 hover:border-slate-700 hover:text-slate-200' }}">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="w-5 h-5 flex items-center justify-center bg-slate-900 border border-slate-800 rounded-lg text-purple-400">
                                                <x-icon name="{{ $v['icon'] }}" class="w-3.5 h-3.5" />
                                            </span>
                                            <span class="font-bold text-sm text-white">{{ $v['name'] }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500">{{ $v['desc'] }}</p>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex justify-between">
                        <button type="button" wire:click="previousStep" class="border border-slate-850 hover:bg-slate-900 bg-slate-950/40 text-slate-300 font-semibold rounded-xl px-6 py-3.5 transition-colors">
                            ← Back
                        </button>
                        <button type="button" wire:click="nextStep" class="bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold rounded-xl px-8 py-3.5 shadow-lg shadow-purple-500/20 hover:opacity-95 transition-all flex items-center gap-2">
                            Continue to Generate <span class="font-mono">→</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 5: Generate & Success --}}
            @if($step === 5)
                @if($is_generating)
                    {{-- Generation Animation --}}
                    <div class="py-12 flex flex-col items-center justify-center text-center space-y-6"
                         x-data="{ progress: 0 }"
                         x-init="let interval = setInterval(() => { 
                             if (progress < 100) { 
                                 progress += 2.5; 
                             } else { 
                                 clearInterval(interval); 
                                 $wire.finishGeneration(); 
                             } 
                         }, 75)">
                        
                        {{-- Glowing Sphere --}}
                        <div class="relative w-28 h-28 flex items-center justify-center">
                            <div class="absolute inset-0 bg-gradient-to-tr from-pink-500 to-purple-600 rounded-full blur-xl animate-pulse opacity-40"></div>
                            <div class="w-20 h-20 bg-slate-950 border border-purple-500/30 rounded-full flex items-center justify-center shadow-2xl relative z-10">
                                <svg class="w-8 h-8 text-purple-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                </svg>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-2xl font-bold text-white tracking-tight animate-pulse">Generating influencer...</h2>
                            <p class="text-slate-400 text-sm mt-1 max-w-sm">We are synthesizing physical traits, backstory, and style references to build the digital persona.</p>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="w-full max-w-sm space-y-1">
                            <div class="h-2 w-full bg-slate-900 border border-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-pink-500 to-purple-500 rounded-full transition-all duration-75" :style="{ width: progress + '%' }"></div>
                            </div>
                            <div class="flex justify-between text-[10px] text-slate-500 font-bold tracking-wider pt-1">
                                <span>SYNTHESIZING</span>
                                <span x-text="Math.floor(progress) + '%'"></span>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Success Screen --}}
                    <div class="py-4 space-y-8 flex flex-col items-center">
                        <div class="text-center">
                            {{-- Success Checkmark --}}
                            <div class="w-16 h-16 rounded-full bg-emerald-500/10 border-2 border-emerald-500 flex items-center justify-center text-emerald-400 mx-auto mb-4 shadow-lg shadow-emerald-500/10 scale-110 transition-transform duration-500">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-extrabold text-white tracking-tight">Persona Synthesized!</h2>
                            <p class="text-slate-400 text-sm mt-1">Your new digital influencer is ready to be used.</p>
                        </div>

                        {{-- Influencer card preview --}}
                        <div class="w-72 bg-slate-950 border border-white/5 rounded-3xl overflow-hidden shadow-2xl relative group">
                            <div class="h-96 w-full relative overflow-hidden">
                                <img src="{{ $generated_avatar }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                                
                                {{-- Stage info overlay --}}
                                <div class="absolute bottom-6 left-6 right-6">
                                    <h3 class="text-xl font-bold text-white tracking-tight">{{ $name }}</h3>
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        @foreach(array_slice($niches, 0, 2) as $nc)
                                            <span class="px-2 py-0.5 bg-purple-500/20 border border-purple-500/30 rounded-md text-[10px] font-bold text-purple-300 uppercase tracking-wider">{{ $nc }}</span>
                                        @endforeach
                                        <span class="px-2 py-0.5 bg-slate-800/80 border border-slate-700 rounded-md text-[10px] font-bold text-slate-300 uppercase tracking-wider">{{ $age }} Years</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-col sm:flex-row gap-3 w-full max-w-sm pt-4">
                            <a href="/" class="flex-1 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold rounded-xl py-3.5 text-center shadow-lg shadow-purple-500/20 hover:opacity-95 transition-all">
                                Go to Dashboard
                            </a>
                            <button type="button" wire:click="$set('step', 1); $set('is_done', false); $set('name', ''); $set('age', ''); $set('niches', []); $set('face_reference', null); $set('style_reference', null); $set('backstory', ''); $set('personality', 50); $set('ethnicity', ''); $set('skin_tone', ''); $set('hair_color', ''); $set('hair_length', ''); $set('hair_texture', ''); $set('eye_color', ''); $set('build', ''); $set('custom_description', ''); $set('aesthetic_vibe', '');" class="flex-1 border border-slate-850 hover:bg-slate-900 bg-slate-950/40 text-slate-300 font-semibold rounded-xl py-3.5 transition-colors">
                                Create Another
                            </button>
                        </div>
                    </div>
                @endif
            @endif

        </div>

    </main>

    {{-- Footer --}}
    <footer class="max-w-7xl w-full mx-auto text-center text-xs text-slate-600 px-4 mt-8 relative z-20">
        <p>&copy; {{ date('Y') }} Influencer Studio. All rights reserved. Powered by Higgsfield & Claude.</p>
    </footer>

</div>

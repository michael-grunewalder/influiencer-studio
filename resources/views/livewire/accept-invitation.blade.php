<div class="flex items-center justify-center min-h-screen bg-base-200">
    <x-card shadow class="w-full max-w-md bg-base-100 p-8">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-4">
                <x-app-brand />
            </div>
            <h2 class="text-2xl font-bold text-base-content">{{ __('Team-Einladung') }}</h2>
            <p class="text-sm text-slate-500 mt-1">
                {{ __('Du wurdest eingeladen, dem Team :team beizutreten.', ['team' => $invitation->team->name]) }}
            </p>
        </div>

        @if(auth()->check())
            @if(strcasecmp(auth()->user()->email, $invitation->email) === 0)
                <div class="space-y-4">
                    <div class="alert alert-info shadow-sm py-3 px-4 rounded-xl text-xs">
                        <div>
                            <span>{{ __('Angemeldet als:') }} <strong>{{ auth()->user()->email }}</strong></span>
                        </div>
                    </div>
                    <x-button 
                        label="Einladung annehmen und beitreten" 
                        class="btn-primary w-full font-bold rounded-xl py-3 shadow shadow-primary/20" 
                        wire:click="accept" 
                        spinner="accept"
                    />
                </div>
            @else
                <div class="space-y-4">
                    <div class="alert alert-warning shadow-sm py-3 px-4 rounded-xl text-xs">
                        <div>
                            <span>{{ __('Diese Einladung ist für :email bestimmt. Du bist aktuell als :current angemeldet.', ['email' => $invitation->email, 'current' => auth()->user()->email]) }}</span>
                        </div>
                    </div>
                    <x-button 
                        label="Abmelden & mit anderer E-Mail anmelden" 
                        link="/logout" 
                        class="btn-outline btn-error w-full font-bold rounded-xl py-3" 
                        no-wire-navigate
                    />
                </div>
            @endif
        @else
            <div class="space-y-4">
                <div class="alert alert-info shadow-sm py-3 px-4 rounded-xl text-xs">
                    <div>
                        <span>{{ __('Einladung für:') }} <strong>{{ $invitation->email }}</strong></span>
                    </div>
                </div>
                
                @if($isRegistered)
                    <p class="text-xs text-slate-500 text-center">
                        {{ __('Du hast bereits ein Konto. Bitte melde dich an, um dem Team beizutreten.') }}
                    </p>
                    <x-button 
                        label="Mit bestehendem Konto anmelden" 
                        link="/login" 
                        class="btn-primary w-full font-bold rounded-xl py-3 shadow shadow-primary/20" 
                    />
                @else
                    <p class="text-xs text-slate-500 text-center">
                        {{ __('Erstelle ein Konto, um der Einladung zu folgen.') }}
                    </p>
                    <x-button 
                        label="Konto erstellen & beitreten" 
                        link="/register?invitation={{ $token }}" 
                        class="btn-primary w-full font-bold rounded-xl py-3 shadow shadow-primary/20" 
                    />
                    <div class="divider text-xs">{{ __('ODER') }}</div>
                    <x-button 
                        label="Bereits registriert? Hier einloggen" 
                        link="/login" 
                        class="btn-outline btn-sm w-full font-semibold rounded-xl" 
                    />
                @endif
            </div>
        @endif
    </x-card>
</div>

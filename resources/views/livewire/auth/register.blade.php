<div class="flex items-center justify-center min-h-screen bg-transparent p-4">

    {{-- SCHRITT 1: Registrierung (Breit, ohne Logo) --}}
    @if($step === 1)
        <x-card wire:key="step-1" shadow class="w-full max-w-2xl overflow-hidden !p-0 border-none bg-gray-700">
            <div class="p-8 md:p-12 text-white">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold">Neues Konto</h2>
                    <p class="text-gray-400">Erstelle dein Profil, um loszulegen.</p>
                </div>

                <x-form wire:submit="sendOtp">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input label="Name" wire:model="name" icon="o-user" class="text-white" inline />
                        <x-input label="E-Mail" wire:model="email" icon="o-envelope" class="text-white" inline />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <x-input label="Passwort" wire:model="password" type="password" icon="o-key" class="text-white" inline />
                        <x-input label="Bestätigen" wire:model="password_confirmation" type="password" icon="o-key" class="text-white" inline />
                    </div>

                    <x-slot:actions class="flex-col gap-2 mt-6">
                        <x-button label="Bestätigungscode anfordern" type="submit" class="btn-primary w-full" spinner="sendOtp" />
                        <x-button label="Bereits ein Konto? Zum Login" link="/login" class="btn-ghost btn-sm text-gray-400" />
                    </x-slot:actions>
                </x-form>
            </div>
        </x-card>

    {{-- SCHRITT 2: OTP-Verifizierung (Zweispaltig, mit Logo wie Login) --}}
    @else
        <x-card wire:key="step-2" shadow class="w-full max-w-4xl overflow-hidden !p-0 border-none">
            <div class="flex flex-col md:flex-row">
                
                {{-- Linke Seite: OTP Eingabe (bg-gray-700) --}}
                <div class="w-full md:w-1/2 bg-gray-700 p-8 md:p-12 text-white">
                    <div class="mb-10 text-center md:text-left">
                        <h2 class="text-2xl font-bold text-white">Verifizierung</h2>
                        <p class="text-gray-400">Prüfe dein Postfach für <strong>{{ $email }}</strong></p>
                    </div>

                    <x-form wire:submit="verifyAndRegister">
                        <div class="flex justify-center my-8">
                            {{-- Die x-pin Komponente braucht meist schwarzen Text für die Ziffern --}}
                            <x-pin wire:model="otp" size="6" numeric class="bg-white text-gray-900" />
                        </div>

                        <x-slot:actions class="flex-col gap-3">
                            <x-button label="Konto verifizieren" type="submit" class="btn-primary w-full" spinner="verifyAndRegister" />
                            <x-button label="E-Mail korrigieren" wire:click="$set('step', 1)" class="btn-ghost btn-sm text-gray-400" />
                        </x-slot:actions>
                    </x-form>
                </div>

                {{-- Rechte Seite: Branding / Logo (bg-primary) --}}
                <div class="hidden md:flex w-1/2 bg-primary items-center justify-center p-12 text-primary-content">
                    <div class="text-center">
                        <x-icon name="o-shield-check" class="w-24 h-24 mb-6 mx-auto opacity-90" />
                        <h3 class="text-3xl font-black italic uppercase">Fast Verifiziert</h3>
                        <p class="mt-4 opacity-75 max-w-xs mx-auto text-sm">
                            Nur noch ein kleiner Schritt, um dein {{config('app.name', 'Laravel')}}-Erlebnis zu starten.
                        </p>
                    </div>
                </div>
            </div>
        </x-card>
    @endif

</div>

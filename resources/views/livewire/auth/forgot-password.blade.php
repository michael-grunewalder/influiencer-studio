<div class="flex items-center justify-center min-h-screen">
    <x-card shadow class="w-full max-w-4xl bg-base-100 overflow-hidden !p-0">
        <div class="flex flex-col md:flex-row">

            {{-- Linke Spalte: Formular --}}
            <div class="w-full md:w-1/2 p-8 md:p-12 bg-gray-700">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold">Passwort vergessen?</h2>
                    <p class="text-sm text-gray-500">Kein Problem. Gib uns einfach deine E-Mail-Adresse und wir senden dir einen Link zum Zurücksetzen deines Passworts zu.</p>
                </div>

                <x-form wire:submit="sendResetLink">
                    <x-input label="E-Mail" wire:model="email" icon="o-envelope" inline />

                    <x-slot:actions class="flex-col gap-3">
                        <x-button label="Link zum Zurücksetzen senden" type="submit" class="btn-primary w-full" spinner="sendResetLink" />
                        <x-button label="Zurück zum Login" link="/login" class="btn-ghost btn-sm w-full" />
                    </x-slot:actions>
                </x-form>
            </div>

            {{-- Rechte Spalte: Logo & Branding --}}
            <div class="hidden md:flex w-1/2 bg-primary items-center justify-center p-12 text-primary-content">
                <div class="text-center">
                    <div class="flex justify-center mb-6">
                        <x-app-brand />
                    </div>
                    <p class="mt-4 opacity-80 max-w-xs mx-auto">
                        Wir helfen dir, schnell wieder Zugriff auf dein Konto zu erhalten.
                    </p>
                </div>
            </div>

        </div>
    </x-card>
</div>
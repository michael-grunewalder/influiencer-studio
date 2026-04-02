<div class="flex items-center justify-center min-h-screen">
    {{-- Die Card dient als Hauptcontainer --}}
    <x-card shadow class="w-full max-w-4xl bg-base-100 overflow-hidden !p-0">
        <div class="flex flex-col md:flex-row">

            {{-- Linke Spalte: Login Formular --}}
            <div class="w-full md:w-1/2 p-8 md:p-12 bg-gray-700">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold">Willkommen zurück</h2>
                    <p class="text-sm text-gray-500">Bitte melde dich mit deinem Konto an.</p>
                </div>

                <x-form wire:submit="login">
                    <x-input label="E-Mail" wire:model="email" icon="o-envelope" inline />
                    <x-input label="Passwort" wire:model="password" type="password" icon="o-key" inline />

                    <div class="flex justify-between items-center mt-2">
                        <x-checkbox label="Angemeldet bleiben" wire:model="remember" />
                        <a href="/forgot-password" class="text-sm link link-primary">Passwort vergessen?</a>
                    </div>

                    <x-slot:actions class="flex-col gap-3">
                        <x-button label="Anmelden" type="submit" class="btn-primary w-full" spinner="login" />
                        <div class="divider text-xs">ODER</div>
                        <x-button label="Neues Konto erstellen" link="/register" class="btn-outline btn-sm w-full" />
                    </x-slot:actions>
                </x-form>
                <x-authenticate-passkey />
            </div>

            {{-- Rechte Spalte: Logo & Branding --}}
            <div class="hidden md:flex w-1/2 bg-primary items-center justify-center p-12 text-primary-content">
                <div class="text-center">
                    {{-- Hier dein Logo-Pfad --}}
                    <div class="flex justify-center mb-6">
                        <x-app-brand />
                    </div>
                    <p class="mt-4 opacity-80 max-w-xs mx-auto">
                        Die effizienteste Art, deine Laravel-Projekte ohne Volt-Ballast zu verwalten.
                    </p>
                </div>
            </div>

        </div>
    </x-card>
</div>

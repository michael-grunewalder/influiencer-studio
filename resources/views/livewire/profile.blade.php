<div>
    <x-header title="Profil" subtitle="Verwalte deine Kontoeinstellungen" separator />

    <div class="grid gap-8 lg:grid-cols-2">
        <x-card title="Benutzerinformationen" shadow separator>
            <div class="flex items-center gap-4">
                <x-avatar :placeholder="auth()->user()->initials()" class="!w-16" />
                <div>
                    <div class="font-bold text-lg">{{ $user->name }}</div>
                    <div class="text-gray-500">{{ $user->email }}</div>
                </div>
            </div>
        </x-card>

        <x-card title="Passwort ändern" shadow separator>
            <x-form wire:submit="updatePassword">
                <x-input
                    label="Aktuelles Passwort"
                    wire:model="current_password"
                    type="password"
                    icon="o-key"
                />

                <x-input
                    label="Neues Passwort"
                    wire:model="password"
                    type="password"
                    icon="o-lock-closed"
                />

                <x-input
                    label="Neues Passwort bestätigen"
                    wire:model="password_confirmation"
                    type="password"
                    icon="o-lock-closed"
                />

                <x-slot:actions>
                    <x-button label="Abbrechen" link="/dashboard" />
                    <x-button label="Speichern" type="submit" class="btn-primary" spinner="updatePassword" />
                </x-slot:actions>
            </x-form>
        </x-card>
        <x-card title="{{__('Passkey Einrichten')}}" shadow separator>
            <livewire:passkeys />
        </x-card>
    </div>
</div>

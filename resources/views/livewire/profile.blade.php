 <div>
        <x-header title="Profil" subtitle="Verwalte deine Kontoeinstellungen" separator />
        <div class="grid gap-8 lg:grid-cols-2">
            <x-card title="Benutzerinformationen" shadow separator>
                <div class="flex items-center gap-4">
                    <label class="relative cursor-pointer group w-16 h-16 rounded-full overflow-hidden border border-base-300 shadow-sm flex items-center justify-center flex-shrink-0 bg-base-200">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                        @else
                            <div class="font-bold text-lg text-slate-500">{{ auth()->user()->initials() }}</div>
                        @endif
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <input type="file" wire:model="uploaded_avatar" class="hidden" accept="image/*" />
                    </label>
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
                            wire:model.live="current_password"
                            type="password"
                            icon="o-key"
                    />

                    <x-input
                            label="Neues Passwort"
                            wire:model="password"
                            type="password"
                            icon="o-lock-closed"
                            :disabled="empty($current_password)"
                    />

                    <x-input
                            label="Neues Passwort bestätigen"
                            wire:model="password_confirmation"
                            type="password"
                            icon="o-lock-closed"
                            :disabled="empty($current_password)"
                    />

                    <x-slot:actions>
                        <x-button label="Abbrechen" link="/dashboard" />
                        <x-button label="Speichern" type="submit" class="btn-primary" spinner="updatePassword" />
                    </x-slot:actions>
                </x-form>
            </x-card>
            <x-card title="Standard-Team & Login-Einstellungen" shadow separator>
                <x-form wire:submit="updateTeamSettings">
                    <x-select
                        label="Standard-Team"
                        placeholder="Kein Standard-Team ausgewählt"
                        icon="o-users"
                        :options="$this->teams"
                        wire:model="default_team_id"
                    />

                    <div class="mt-4">
                        <x-radio
                            label="Team-Auswahl beim Login"
                            wire:model="team_selection_mode"
                            :options="[
                                ['id' => 'default', 'name' => 'Immer Standard-Team auswählen'],
                                ['id' => 'last_used', 'name' => 'Zuletzt verwendetes Team aktivieren']
                            ]"
                        />
                    </div>

                    <x-slot:actions>
                        <x-button label="Speichern" type="submit" class="btn-primary" spinner="updateTeamSettings" />
                    </x-slot:actions>
                </x-form>
            </x-card>

            <x-card title="{{__('Passkey Einrichten')}}" shadow separator>
                <livewire:passkeys />
            </x-card>
        </div>
    </div>
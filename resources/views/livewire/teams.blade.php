<div>
    <x-header title="Teams" subtitle="Verwalte deine Teams und lade neue Mitglieder ein" separator />
    
    <div class="grid gap-8 lg:grid-cols-3">
        <!-- Left Pane: Teams List -->
        <x-card title="Meine Teams" shadow separator class="lg:col-span-1">
            <x-menu class="p-0">
                @forelse($this->teams as $team)
                    <x-menu-item 
                        title="{{ $team->name }}" 
                        icon="o-users" 
                        wire:click="selectTeam('{{ $team->id }}')"
                        class="{{ $selectedTeamId === $team->id ? 'bg-base-200 font-bold border-l-4 border-primary' : '' }}" 
                    />
                @empty
                    <div class="p-4 text-slate-500 text-sm text-center">
                        {{ __('Du gehörst noch keinem Team an.') }}
                    </div>
                @endforelse
            </x-menu>
        </x-card>

        <!-- Right Pane: Team Details -->
        <div class="lg:col-span-2">
            @if($this->selectedTeam)
                <div class="space-y-6">
                    <x-card shadow separator>
                        <x-slot:title>
                            <div class="flex justify-between items-center w-full">
                                <span class="font-bold text-lg">{{ __('Team-Details') }}</span>
                                @if($this->canInvite)
                                    <x-button 
                                        label="Mitglied einladen" 
                                        icon="o-user-plus" 
                                        class="btn-primary btn-sm rounded-xl font-bold" 
                                        wire:click="openInviteModal" 
                                    />
                                @endif
                            </div>
                        </x-slot:title>

                        @if($this->isAdmin)
                            <!-- Edit Team Form -->
                            <x-form wire:submit="saveTeam">
                                <x-input label="Team-Name" wire:model="team_name" />
                                <x-textarea label="Beschreibung" wire:model="team_description" rows="3" />
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-input label="FAL.AI API Key" wire:model="team_fal_api_key" type="password" placeholder="fal_..." />
                                    <x-input label="CLAUDE API Key" wire:model="team_claude_api_key" type="password" placeholder="sk-ant-..." />
                                </div>
                                @if($fal_account_balance)
                                    <div class="p-3 bg-base-200/50 rounded-xl flex justify-between items-center text-xs mt-2 border border-base-300">
                                        <span class="font-bold text-slate-500">FAL.AI Account Balance:</span>
                                        <span class="font-mono font-black text-success">
                                            ${{ number_format($fal_account_balance['credits']['current_balance'] ?? 0.00, 2) }} {{ $fal_account_balance['credits']['currency'] ?? 'USD' }}
                                            <span class="text-[10px] text-slate-450 font-bold">({{ $fal_account_balance['username'] ?? 'FAL' }})</span>
                                        </span>
                                    </div>
                                @endif
                                <x-slot:actions>
                                    <x-button label="Speichern" type="submit" class="btn-primary rounded-xl font-bold px-6" spinner="saveTeam" />
                                </x-slot:actions>
                            </x-form>
                        @else
                            <!-- Read-only details -->
                            <div class="space-y-4">
                                <div>
                                    <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wide">{{ __('Name') }}</div>
                                    <div class="font-bold text-lg text-base-content">{{ $this->selectedTeam->name }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wide">{{ __('Beschreibung') }}</div>
                                    <div class="text-sm text-slate-500 leading-relaxed">{{ $this->selectedTeam->description ?? __('Keine Beschreibung vorhanden.') }}</div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wide">{{ __('FAL.AI API Key') }}</div>
                                        <div class="text-sm font-mono text-base-content break-all">{{ $this->maskedFalApiKey }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wide">{{ __('CLAUDE API Key') }}</div>
                                        <div class="text-sm font-mono text-base-content break-all">{{ $this->maskedClaudeApiKey }}</div>
                                    </div>
                                </div>
                                @if($fal_account_balance)
                                    <div class="p-3 bg-base-200/50 rounded-xl flex justify-between items-center text-xs mt-2 border border-base-300">
                                        <span class="font-bold text-slate-500">FAL.AI Account Balance:</span>
                                        <span class="font-mono font-black text-success">
                                            ${{ number_format($fal_account_balance['credits']['current_balance'] ?? 0.00, 2) }} {{ $fal_account_balance['credits']['currency'] ?? 'USD' }}
                                            <span class="text-[10px] text-slate-450 font-bold">({{ $fal_account_balance['username'] ?? 'FAL' }})</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </x-card>

                    <!-- Members list -->
                    <x-card title="Mitglieder" shadow separator>
                        <div class="divide-y divide-base-200">
                            @forelse($this->selectedTeam->users as $member)
                                <div class="py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8 h-8">
                                                <span class="text-xs font-bold">{{ $member->initials() }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm text-base-content">{{ $member->name }}</div>
                                            <div class="text-xs text-slate-400">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-4 text-center text-slate-500 text-sm">
                                    {{ __('Keine Mitglieder gefunden.') }}
                                </div>
                            @endforelse
                        </div>
                    </x-card>

                    <!-- Influencers list -->
                    <x-card title="Influencer" shadow separator>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @forelse($this->selectedTeam->influencers as $inf)
                                <div class="border border-base-300 rounded-xl p-3 flex items-center gap-3 bg-base-100/50">
                                    <div class="avatar">
                                        <div class="w-8 h-8 rounded-full border border-base-300">
                                            <img src="{{ $inf->avatar }}" alt="{{ $inf->name }}" class="object-cover rounded-full" />
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs truncate text-base-content">{{ $inf->name }}</div>
                                        <div class="text-[9px] font-semibold text-slate-500 uppercase tracking-wide">
                                            {{ $inf->properties->gender ?? 'Female' }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-4 text-center text-slate-500 text-sm">
                                    {{ __('Dieses Team besitzt noch keine Influencer.') }}
                                </div>
                            @endforelse
                        </div>
                    </x-card>
                </div>
            @else
                <div class="bg-base-100 border border-base-300 rounded-2xl p-12 text-center text-slate-500 shadow-sm">
                    <svg class="w-10 h-10 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <h3 class="font-bold text-base text-base-content">{{ __('Kein Team ausgewählt') }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ __('Wähle links ein Team aus, um Details anzuzeigen.') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Invitation Modal -->
    <x-modal wire:model="showInviteModal" title="Mitglied einladen">
        <x-form wire:submit="sendInvitation">
            <x-input label="Name des Empfängers" wire:model="invite_name" placeholder="z.B. Jane Doe" />
            <x-input label="E-Mail-Adresse" wire:model="invite_email" placeholder="z.B. jane.doe@example.com" type="email" />

            <x-slot:actions>
                <x-button label="Abbrechen" wire:click="$set('showInviteModal', false)" class="btn-ghost" />
                <x-button label="Einladung senden" type="submit" class="btn-primary font-bold rounded-xl px-5" spinner="sendInvitation" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>

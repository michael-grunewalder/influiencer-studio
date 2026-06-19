<div class="max-w-4xl mx-auto py-6">
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-black text-base-content tracking-tight">Credit Manager & History</h1>
            <p class="text-slate-500 text-sm">Verwalten Sie Ihr Guthaben, transferieren Sie Credits zu Teams und sehen Sie Transaktionen ein.</p>
        </div>
    </div>

    {{-- Tabs Header --}}
    <div class="flex gap-1 bg-base-300/40 p-1 rounded-xl w-fit mb-6">
        <button wire:click="$set('activeTab', 'topup')" class="px-5 py-2 text-xs font-bold rounded-lg transition-all {{ $activeTab === 'topup' ? 'bg-base-100 text-base-content shadow-sm' : 'text-slate-500 hover:text-base-content' }}">
            Aufladen & Transferieren
        </button>
        <button wire:click="$set('activeTab', 'history')" class="px-5 py-2 text-xs font-bold rounded-lg transition-all {{ $activeTab === 'history' ? 'bg-base-100 text-base-content shadow-sm' : 'text-slate-500 hover:text-base-content' }}">
            Wallet Journal (Eigenes)
        </button>
        <button wire:click="$set('activeTab', 'team_journal')" class="px-5 py-2 text-xs font-bold rounded-lg transition-all {{ $activeTab === 'team_journal' ? 'bg-base-100 text-base-content shadow-sm' : 'text-slate-500 hover:text-base-content' }}">
            Team Journal (Aktiv)
        </button>
    </div>

    @if($activeTab === 'topup')
        {{-- Tab 1: Top Up & Transfer --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            {{-- Left Column: Top-Up Form (Col-7) --}}
            <div class="md:col-span-7">
                <form wire:submit.prevent="processPayment" class="bg-base-100 border border-base-300 rounded-3xl p-6 shadow-sm space-y-5">
                    <div class="space-y-4">
                        <h3 class="font-bold text-sm text-base-content tracking-tight uppercase border-b border-base-300 pb-2">Guthaben aufladen</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input label="Aufladebetrag ($)" type="number" step="0.01" min="0.01" wire:model.live="amount" placeholder="e.g. 20.00" class="font-mono font-bold" />
                            </div>
                            <div>
                                <x-select label="Guthaben-Zuweisung" wire:model.live="allocationMode" :options="[
                                    ['id' => 'all_team', 'name' => 'Komplett dem Team gutschreiben'],
                                    ['id' => 'all_personal', 'name' => 'Komplett persönlichem Wallet gutschreiben'],
                                    ['id' => 'split', 'name' => 'Aufteilen...']
                                ]" />
                            </div>
                        </div>

                        @if($allocationMode === 'split')
                            <div class="p-4 bg-base-200/40 border border-base-300 rounded-2xl space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-slate-500">Guthaben-Aufteilung</span>
                                    <span class="text-[10px] font-extrabold uppercase text-primary">Split Mode</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                                    <x-input label="Betrag für das Team ($)" type="number" step="0.01" min="0" wire:model.live="teamAmount" placeholder="e.g. 12.00" class="font-mono font-bold" />
                                    <div class="text-xs text-slate-500 pt-3">
                                        @php
                                            $total = (float)$amount;
                                            $team = (float)$teamAmount;
                                            $personal = max(0, $total - $team);
                                        @endphp
                                        <div class="flex justify-between py-1 border-b border-base-300">
                                            <span>Team-Guthaben:</span>
                                            <span class="font-bold text-base-content">${{ number_format($team, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between py-1">
                                            <span>Eigenes Wallet:</span>
                                            <span class="font-bold text-base-content">${{ number_format($personal, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Credit Card Details --}}
                    <div class="space-y-4">
                        <h3 class="font-bold text-sm text-base-content tracking-tight uppercase border-b border-base-300 pb-2">Kreditkarte</h3>

                        <x-input label="Karteninhaber" wire:model.live="cardholder_name" placeholder="Max Mustermann" />

                        <x-input label="Kartennummer" wire:model.live="card_number" placeholder="4111 2222 3333 4444" />

                        <div class="grid grid-cols-2 gap-4">
                            <x-input label="Ablaufdatum" wire:model.live="card_expiry" placeholder="MM/YY" maxlength="5" />
                            <x-input label="CVC / CVV" wire:model.live="card_cvc" placeholder="123" maxlength="4" />
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <x-button type="submit" label="Zahlung bestätigen" class="btn-primary rounded-2xl font-bold px-8 shadow shadow-primary/20" spinner="processPayment" />
                    </div>
                </form>
            </div>

            {{-- Right Column: Card graphic + Credit Transfer UI (Col-5) --}}
            <div class="md:col-span-5 space-y-6">
                {{-- Card Graphic Mockup --}}
                <div class="relative overflow-hidden aspect-[1.58/1] rounded-2xl bg-gradient-to-br from-violet-600 via-indigo-600 to-blue-700 text-white p-6 shadow-xl flex flex-col justify-between border border-white/10">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                    <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-indigo-500/30 rounded-full blur-xl"></div>

                    <div class="flex justify-between items-center z-10">
                        <span class="font-black text-sm tracking-widest uppercase opacity-90">VividPersona Premium</span>
                        <span class="font-extrabold italic text-sm text-yellow-350">PAYMENT MOCK</span>
                    </div>

                    <div class="flex items-center gap-3 z-10">
                        <div class="w-9 h-7 bg-gradient-to-r from-amber-300 to-yellow-400 rounded-md shadow-inner flex items-center justify-center opacity-90">
                            <div class="grid grid-cols-3 gap-0.5 w-6 h-5 opacity-40">
                                <div class="border border-black/20"></div>
                                <div class="border border-black/20"></div>
                                <div class="border border-black/20"></div>
                            </div>
                        </div>
                        <svg class="w-6 h-6 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>

                    <div class="z-10">
                        <div class="font-mono text-base tracking-widest font-black text-white/95">
                            {{ $card_number ? $card_number : '•••• •••• •••• ••••' }}
                        </div>
                    </div>

                    <div class="flex justify-between items-end z-10">
                        <div>
                            <div class="text-[9px] uppercase tracking-wider text-white/60 font-bold">Inhaber</div>
                            <div class="font-bold text-xs uppercase tracking-wide truncate max-w-[150px]">
                                {{ $cardholder_name ? $cardholder_name : 'Karteninhaber' }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[9px] uppercase tracking-wider text-white/60 font-bold">Gültig bis</div>
                            <div class="font-mono text-xs font-bold">
                                {{ $card_expiry ? $card_expiry : 'MM/YY' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Transfer UI Card --}}
                <div class="bg-base-100 border border-base-300 rounded-3xl p-5 shadow-sm space-y-4">
                    <h3 class="font-bold text-sm text-base-content tracking-tight uppercase border-b border-base-300 pb-2 flex justify-between items-center">
                        <span>Zum Team transferieren</span>
                        <x-icon name="o-arrow-path" class="w-4 h-4 text-slate-400" />
                    </h3>
                    <p class="text-slate-500 text-xs leading-relaxed">
                        Verschieben Sie Guthaben von Ihrem persönlichen Wallet direkt in das Budget des aktuell ausgewählten Teams.
                    </p>

                    <form wire:submit.prevent="transferToTeam" class="space-y-4">
                        <x-input label="Transferbetrag ($)" type="number" step="0.01" min="0.01" wire:model="transferAmount" placeholder="e.g. 10.00" class="font-mono font-bold" />
                        <x-button type="submit" label="Jetzt transferieren" class="btn-outline btn-primary rounded-2xl w-full font-bold" spinner="transferToTeam" />
                    </form>
                </div>
            </div>
        </div>
    @elseif($activeTab === 'history')
        {{-- Tab 2: Wallet Journal (Eigenes) --}}
        <div class="bg-base-100 border border-base-300 rounded-3xl p-6 shadow-sm">
            <h3 class="font-bold text-lg text-base-content tracking-tight mb-4 uppercase">Eigenes Wallet Transaktionsjournal</h3>
            
            <div class="overflow-x-auto">
                <table class="table table-compact w-full text-xs">
                    <thead>
                        <tr class="border-b border-base-300 text-slate-450 uppercase font-black tracking-wider">
                            <th class="py-3">Datum / Uhrzeit</th>
                            <th>Art</th>
                            <th>Beschreibung</th>
                            <th class="text-right">Betrag</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse($this->personalTransactions as $tx)
                            <tr class="hover:bg-base-250/20">
                                <td class="py-3 font-semibold text-slate-500">{{ $tx->created_at->format('d.m.Y H:i:s') }}</td>
                                <td>
                                    @if($tx->type === 'topup')
                                        <span class="badge badge-success badge-outline font-bold uppercase text-[9px] px-2">Top-Up</span>
                                    @elseif($tx->type === 'transfer')
                                        <span class="badge badge-primary badge-outline font-bold uppercase text-[9px] px-2">Transfer</span>
                                    @else
                                        <span class="badge badge-ghost font-bold uppercase text-[9px] px-2">{{ $tx->type }}</span>
                                    @endif
                                </td>
                                <td class="font-medium text-base-content">{{ $tx->description }}</td>
                                <td class="text-right font-mono font-bold text-sm">
                                    @if($tx->type === 'topup')
                                        <span class="text-success">+${{ number_format((float)$tx->amount, 2) }}</span>
                                    @elseif($tx->type === 'transfer')
                                        <span class="text-slate-500">-${{ number_format((float)$tx->amount, 2) }}</span>
                                    @else
                                        <span class="text-base-content">${{ number_format((float)$tx->amount, 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-slate-500 font-medium">
                                    Keine Transaktionen in Ihrem Wallet aufgezeichnet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($activeTab === 'team_journal')
        {{-- Tab 3: Team Journal (Aktiv) --}}
        <div class="bg-base-100 border border-base-300 rounded-3xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg text-base-content tracking-tight uppercase">Team-Transaktionsjournal</h3>
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-450 bg-base-200 px-3 py-1 rounded-lg">
                    Team-Budget
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-compact w-full text-xs">
                    <thead>
                        <tr class="border-b border-base-300 text-slate-450 uppercase font-black tracking-wider">
                            <th class="py-3">Datum / Uhrzeit</th>
                            <th>Ausgelöst durch</th>
                            <th>Art</th>
                            <th>Beschreibung</th>
                            <th class="text-right">Betrag</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse($this->teamTransactions as $tx)
                            <tr class="hover:bg-base-250/20">
                                <td class="py-3 font-semibold text-slate-500">{{ $tx->created_at->format('d.m.Y H:i:s') }}</td>
                                <td class="font-bold text-base-content/85">
                                    @if($tx->user)
                                        {{ $tx->user->name }}
                                    @else
                                        <span class="text-slate-400 italic">System</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tx->type === 'topup' || $tx->amount > 0)
                                        <span class="badge badge-success badge-outline font-bold uppercase text-[9px] px-2">Einnahme</span>
                                    @elseif($tx->type === 'transfer')
                                        <span class="badge badge-primary badge-outline font-bold uppercase text-[9px] px-2">Transfer</span>
                                    @elseif($tx->type === 'spending')
                                        <span class="badge badge-error badge-outline font-bold uppercase text-[9px] px-2">Ausgabe</span>
                                    @else
                                        <span class="badge badge-ghost font-bold uppercase text-[9px] px-2">{{ $tx->type }}</span>
                                    @endif
                                </td>
                                <td class="font-medium text-base-content">{{ $tx->description }}</td>
                                <td class="text-right font-mono font-bold text-sm">
                                    @if((float)$tx->amount > 0)
                                        <span class="text-success">+${{ number_format(abs((float)$tx->amount), 2) }}</span>
                                    @else
                                        <span class="text-error">-${{ number_format(abs((float)$tx->amount), 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-500 font-medium">
                                    Keine Transaktionslogs für das aktive Team aufgezeichnet (oder Sie haben keine Berechtigung, Logs anderer Mitglieder zu sehen).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

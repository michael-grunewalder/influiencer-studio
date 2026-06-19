<div class="max-w-2xl mx-auto py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-black text-base-content tracking-tight">Top-Up Credits</h1>
        <p class="text-slate-500 text-sm">Fügen Sie Ihrem Guthaben neue Credits hinzu, um die Medien-Generierung zu bezahlen.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
        {{-- Card Graphic Mockup & Info (Col-5) --}}
        <div class="md:col-span-5 space-y-4">
            <div class="relative overflow-hidden aspect-[1.58/1] rounded-2xl bg-gradient-to-br from-violet-600 via-indigo-600 to-blue-700 text-white p-6 shadow-xl flex flex-col justify-between border border-white/10">
                {{-- Decorative background glow --}}
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-indigo-500/30 rounded-full blur-xl"></div>

                <div class="flex justify-between items-center z-10">
                    <span class="font-black text-sm tracking-widest uppercase opacity-90">VividPersona Premium</span>
                    <span class="font-extrabold italic text-sm text-yellow-350">PAYMENT MOCK</span>
                </div>

                {{-- Card Chip & Contactless --}}
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

                {{-- Card Number --}}
                <div class="z-10">
                    <div class="font-mono text-base tracking-widest font-black text-white/95">
                        {{ $card_number ? $card_number : '•••• •••• •••• ••••' }}
                    </div>
                </div>

                {{-- Cardholder and Expiry --}}
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

            <div class="bg-base-100 border border-base-300 rounded-2xl p-4 text-xs text-slate-500 space-y-2.5 shadow-sm">
                <div class="flex gap-2.5">
                    <x-icon name="o-information-circle" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                    <div>
                        <p class="font-bold text-base-content mb-0.5">Demo-Zahlungsgateway</p>
                        <p class="leading-relaxed">Dieses Projekt befindet sich im Testmodus. Es werden keine echten Beträge von Ihrer Kreditkarte abgebucht. Nutzen Sie Testwerte (z.B. ein beliebiges MM/YY und einen 3-stelligen CVC).</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Card (Col-7) --}}
        <div class="md:col-span-7">
            <form wire:submit.prevent="processPayment" class="bg-base-100 border border-base-300 rounded-3xl p-6 shadow-sm space-y-5">
                {{-- Payment Amount & Allocation --}}
                <div class="space-y-4">
                    <h3 class="font-bold text-sm text-base-content tracking-tight uppercase border-b border-base-300 pb-2">Zahlungsdetails</h3>

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
    </div>
</div>

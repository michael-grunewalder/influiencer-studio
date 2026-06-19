<?php

namespace App\Livewire;

use App\Models\Team;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class TopUp extends Component
{
    public string $amount = '';

    public string $allocationMode = 'all_team'; // all_team, all_personal, split

    public string $teamAmount = '';

    public string $transferAmount = '';

    public string $activeTab = 'topup'; // topup, history, team_journal

    // Card Details
    public string $cardholder_name = '';

    public string $card_number = '';

    public string $card_expiry = '';

    public string $card_cvc = '';

    public function mount(): void
    {
        // Set default values if needed
    }

    public function processPayment(PaymentGatewayService $paymentGateway): void
    {
        $rules = [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'allocationMode' => ['required', 'in:all_team,all_personal,split'],
            'cardholder_name' => ['required', 'string', 'min:3', 'max:100'],
            'card_number' => ['required', 'string'],
            'card_expiry' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/([0-9]{2})$/'],
            'card_cvc' => ['required', 'string', 'regex:/^\d{3,4}$/'],
        ];

        if ($this->allocationMode === 'split') {
            $rules['teamAmount'] = ['required', 'numeric', 'min:0', 'max:'.$this->amount];
        }

        $this->validate($rules, [
            'card_expiry.regex' => 'Das Ablaufdatum muss im Format MM/YY sein.',
            'card_cvc.regex' => 'Der CVC-Code muss 3 oder 4 Ziffern lang sein.',
            'teamAmount.max' => 'Der Teambetrag darf den gesamten Top-Up-Betrag nicht überschreiten.',
        ]);

        $totalAmount = (float) $this->amount;
        $teamAllocation = 0.0;
        $userAllocation = 0.0;

        if ($this->allocationMode === 'all_team') {
            $teamAllocation = $totalAmount;
        } elseif ($this->allocationMode === 'all_personal') {
            $userAllocation = $totalAmount;
        } else {
            $teamAllocation = (float) $this->teamAmount;
            $userAllocation = $totalAmount - $teamAllocation;
        }

        $cardDetails = [
            'cardholder_name' => $this->cardholder_name,
            'card_number' => $this->card_number,
            'card_expiry' => $this->card_expiry,
            'card_cvc' => $this->card_cvc,
        ];

        try {
            $paymentGateway->charge($totalAmount, $cardDetails);

            $user = auth()->user();
            if ($userAllocation > 0) {
                $user->increment('credits', $userAllocation);
                Transaction::create([
                    'user_id' => $user->id,
                    'team_id' => null,
                    'type' => 'topup',
                    'amount' => $userAllocation,
                    'description' => 'Aufladung des persönlichen Wallets',
                ]);
            }

            if ($teamAllocation > 0) {
                $activeTeamId = session('active_team_id');
                $team = $activeTeamId ? $user->teams()->where('team_id', $activeTeamId)->first() : null;
                if (! $team) {
                    $team = $user->teams()->first();
                }

                if (! $team) {
                    $team = Team::create(['name' => $user->last_name ? $user->last_name."'s Team" : 'Personal Team']);
                    $user->teams()->attach($team);
                    session(['active_team_id' => $team->id]);
                }

                $team->increment('credits', $teamAllocation);
                Transaction::create([
                    'user_id' => $user->id,
                    'team_id' => $team->id,
                    'type' => 'topup',
                    'amount' => $teamAllocation,
                    'description' => 'Direkte Guthaben-Aufladung des Teams: '.$team->name,
                ]);
            }

            $this->dispatch('credits-updated');

            Toaster::success(__('Zahlung erfolgreich! Credits wurden Ihrem Konto gutgeschrieben.'));

            $this->reset([
                'amount',
                'allocationMode',
                'teamAmount',
                'cardholder_name',
                'card_number',
                'card_expiry',
                'card_cvc',
            ]);
        } catch (\Exception $e) {
            Toaster::error(__('Zahlung fehlgeschlagen: :message', ['message' => $e->getMessage()]));
        }
    }

    public function transferToTeam(): void
    {
        $user = auth()->user();
        $this->validate([
            'transferAmount' => ['required', 'numeric', 'min:0.01', 'max:'.$user->credits],
        ], [
            'transferAmount.max' => 'Der Transferbetrag darf Ihr persönliches Wallet-Guthaben nicht überschreiten.',
        ]);

        $activeTeamId = session('active_team_id');
        $team = $activeTeamId ? $user->teams()->where('team_id', $activeTeamId)->first() : null;
        if (! $team) {
            $team = $user->teams()->first();
        }

        if (! $team) {
            $team = Team::create(['name' => $user->last_name ? $user->last_name."'s Team" : 'Personal Team']);
            $user->teams()->attach($team);
            session(['active_team_id' => $team->id]);
        }

        $amount = (float) $this->transferAmount;

        try {
            $user->transferCreditsToTeam($team, $amount);

            $this->dispatch('credits-updated');

            Toaster::success(__(':amount Credits erfolgreich auf das Team ":team" übertragen!', [
                'amount' => '$'.number_format($amount, 2),
                'team' => $team->name,
            ]));

            $this->reset('transferAmount');
        } catch (\Exception $e) {
            Toaster::error(__('Transfer fehlgeschlagen: :message', ['message' => $e->getMessage()]));
        }
    }

    #[Computed]
    public function personalTransactions()
    {
        return Transaction::where('user_id', auth()->id())
            ->latest()
            ->get();
    }

    #[Computed]
    public function teamTransactions()
    {
        $user = auth()->user();
        $activeTeamId = session('active_team_id');
        $team = $activeTeamId ? $user->teams()->where('team_id', $activeTeamId)->first() : null;
        if (! $team) {
            $team = $user->teams()->first();
        }

        if (! $team) {
            return collect();
        }

        // Admins & Managers can see all transactions
        if ($user->hasTeamRole($team, ['admin', 'manage'])) {
            return Transaction::where('team_id', $team->id)
                ->with('user')
                ->latest()
                ->get();
        }

        // Standard members can only see their own transactions
        return Transaction::where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->with('user')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.top-up');
    }
}

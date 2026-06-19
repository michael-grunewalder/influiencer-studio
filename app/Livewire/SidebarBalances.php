<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class SidebarBalances extends Component
{
    #[On('credits-updated')]
    public function refreshBalances(): void
    {
        // Reactively re-renders when event is fired
    }

    public function render()
    {
        $user = auth()->user();

        $activeTeamId = session('active_team_id');
        $team = null;
        if ($user) {
            $team = $activeTeamId ? $user->teams()->where('team_id', $activeTeamId)->first() : null;
            if (! $team) {
                $team = $user->teams()->first();
            }
        }

        return view('livewire.sidebar-balances', [
            'userWallet' => $user ? (float) $user->credits : 0.00,
            'teamBalance' => $team ? (float) $team->credits : 0.00,
        ]);
    }
}

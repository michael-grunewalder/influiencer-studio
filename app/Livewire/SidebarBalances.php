<?php

namespace App\Livewire;

use App\Services\FalAiService;
use Livewire\Attributes\On;
use Livewire\Component;

class SidebarBalances extends Component
{
    #[On('credits-updated')]
    public function refreshBalances(): void
    {
        $user = auth()->user();
        $activeTeamId = session('active_team_id');
        if ($user) {
            $team = $activeTeamId ? $user->teams()->where('team_id', $activeTeamId)->first() : null;
            if (! $team) {
                $team = $user->teams()->first();
            }
            if ($team && $team->fal_api_key) {
                cache()->forget('fal_balance_'.md5($team->fal_api_key));
            }
        }
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

        $falBalance = null;
        if ($team && $team->fal_api_key) {
            $cacheKey = 'fal_balance_'.md5($team->fal_api_key);
            $falBalance = cache()->remember($cacheKey, now()->addMinutes(2), function () use ($team) {
                return app(FalAiService::class)->getAccountBalance($team->fal_api_key);
            });
        }

        return view('livewire.sidebar-balances', [
            'userWallet' => $user ? (float) $user->credits : 0.00,
            'teamBalance' => $team ? (float) $team->credits : 0.00,
            'falBalance' => $falBalance,
        ]);
    }
}

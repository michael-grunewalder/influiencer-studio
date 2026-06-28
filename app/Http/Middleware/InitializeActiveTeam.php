<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeActiveTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $activeTeamId = session('active_team_id');
            $validTeam = null;

            // 1. If we already have a session active_team_id, check if it's still valid
            if ($activeTeamId) {
                if ($user->can('team.view-all')) {
                    $validTeam = Team::find($activeTeamId);
                } else {
                    $validTeam = $user->teams()->where('team_id', $activeTeamId)->first();
                }
            }

            // 2. If no valid team in session, determine one
            if (! $validTeam) {
                $chosenTeamId = null;

                if ($user->team_selection_mode === 'last_used' && $user->last_active_team_id) {
                    $chosenTeamId = $user->last_active_team_id;
                } elseif ($user->team_selection_mode === 'default' && $user->default_team_id) {
                    $chosenTeamId = $user->default_team_id;
                }

                if ($chosenTeamId) {
                    if ($user->can('team.view-all')) {
                        $validTeam = Team::find($chosenTeamId);
                    } else {
                        $validTeam = $user->teams()->where('team_id', $chosenTeamId)->first();
                    }
                }

                // Fallback 1: Default team if specified and valid (even if mode is last_used but it was missing)
                if (! $validTeam && $user->default_team_id) {
                    if ($user->can('team.view-all')) {
                        $validTeam = Team::find($user->default_team_id);
                    } else {
                        $validTeam = $user->teams()->where('team_id', $user->default_team_id)->first();
                    }
                }

                // Fallback 2: First team the user belongs to
                if (! $validTeam) {
                    $validTeam = $user->teams()->first();
                }

                // Fallback 3: If user has no teams, create a default personal team
                if (! $validTeam) {
                    $validTeam = Team::create(['name' => $user->last_name ? $user->last_name."'s Team" : 'Personal Team']);
                    $user->teams()->attach($validTeam->id);
                }

                session(['active_team_id' => $validTeam->id]);
            }

            // 3. Keep last_active_team_id in sync in database if it doesn't match
            if ($validTeam && $user->last_active_team_id !== $validTeam->id) {
                $user->update(['last_active_team_id' => $validTeam->id]);
            }
        }

        return $next($request);
    }
}

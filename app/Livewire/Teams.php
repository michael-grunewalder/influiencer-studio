<?php

namespace App\Livewire;

use App\Mail\TeamInvitationMail;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Services\FalAiService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app')]
class Teams extends Component
{
    public ?string $selectedTeamId = null;

    // Invitation fields
    public bool $showInviteModal = false;

    public string $invite_name = '';

    public string $invite_email = '';

    // Edit fields for team.admin
    public string $team_name = '';

    public string $team_description = '';

    public string $team_fal_api_key = '';

    public string $team_claude_api_key = '';

    public ?array $fal_account_balance = null;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->hasPermissionTo('team.view-all') && ! $user->hasPermissionTo('team.view')) {
            abort(403, 'Unauthorized action.');
        }

        $firstTeam = $this->teams->first();
        if ($firstTeam) {
            $this->selectTeam($firstTeam->id);
        }
    }

    #[Computed]
    public function teams()
    {
        $user = auth()->user();
        if ($user->hasPermissionTo('team.view-all')) {
            return Team::all();
        }

        if ($user->hasPermissionTo('team.view')) {
            return $user->teams()->get();
        }

        return collect();
    }

    #[Computed]
    public function selectedTeam()
    {
        if (! $this->selectedTeamId) {
            return null;
        }

        return Team::find($this->selectedTeamId);
    }

    #[Computed]
    public function canInvite(): bool
    {
        if (! $this->selectedTeamId) {
            return false;
        }
        $user = auth()->user();

        return $user && $user->hasTeamRole($this->selectedTeamId, 'manage');
    }

    #[Computed]
    public function isAdmin(): bool
    {
        if (! $this->selectedTeamId) {
            return false;
        }
        $user = auth()->user();

        return $user && $user->hasTeamRole($this->selectedTeamId, 'admin');
    }

    #[Computed]
    public function maskedFalApiKey(): string
    {
        return $this->maskApiKey($this->selectedTeam?->fal_api_key);
    }

    #[Computed]
    public function maskedClaudeApiKey(): string
    {
        return $this->maskApiKey($this->selectedTeam?->claude_api_key);
    }

    private function maskApiKey(?string $key): string
    {
        if (empty($key)) {
            return __('Nicht konfiguriert');
        }

        if (strlen($key) <= 8) {
            return $key;
        }

        return substr($key, 0, 8).str_repeat('*', strlen($key) - 8);
    }

    public function selectTeam(string $id): void
    {
        $this->selectedTeamId = $id;
        $team = $this->selectedTeam;
        if ($team) {
            $this->team_name = $team->name;
            $this->team_description = $team->description ?? '';
            $this->team_fal_api_key = $team->fal_api_key ?? '';
            $this->team_claude_api_key = $team->claude_api_key ?? '';

            $service = app(FalAiService::class);
            $this->fal_account_balance = $service->getAccountBalance($team->fal_api_key);
        }
    }

    public function saveTeam(): void
    {
        if (! $this->isAdmin) {
            abort(403, 'Unauthorized.');
        }

        $this->validate([
            'team_name' => 'required|string|min:2|max:100',
            'team_description' => 'nullable|string|max:500',
            'team_fal_api_key' => 'nullable|string|max:255',
            'team_claude_api_key' => 'nullable|string|max:255',
        ]);

        $team = $this->selectedTeam;
        if ($team) {
            $team->update([
                'name' => $this->team_name,
                'description' => $this->team_description ?: null,
                'fal_api_key' => $this->team_fal_api_key ?: null,
                'claude_api_key' => $this->team_claude_api_key ?: null,
            ]);

            $service = app(FalAiService::class);
            $this->fal_account_balance = $service->getAccountBalance($team->fal_api_key);
            $this->dispatch('credits-updated');

            Toaster::success(__('Teaminformationen erfolgreich gespeichert!'));
        }
    }

    public function openInviteModal(): void
    {
        if (! $this->canInvite) {
            abort(403, 'Unauthorized.');
        }

        $this->invite_name = '';
        $this->invite_email = '';
        $this->showInviteModal = true;
    }

    public function sendInvitation(): void
    {
        if (! $this->canInvite) {
            abort(403, 'Unauthorized.');
        }

        $this->validate([
            'invite_email' => 'required|email|max:255',
            'invite_name' => 'nullable|string|max:255',
        ]);

        $team = $this->selectedTeam;
        if (! $team) {
            return;
        }

        // Check if user is already a member
        if ($team->users()->where('email', $this->invite_email)->exists()) {
            $this->addError('invite_email', __('Dieser Benutzer ist bereits Mitglied in diesem Team.'));

            return;
        }

        // Generate token and create invitation
        $token = Str::random(40);

        $invitation = TeamInvitation::updateOrCreate(
            ['team_id' => $team->id, 'email' => $this->invite_email],
            [
                'name' => $this->invite_name ?: null,
                'token' => $token,
                'invited_by' => auth()->id(),
            ]
        );

        // Generate signed URL
        $signedUrl = URL::signedRoute('teams.accept', ['token' => $token]);

        // Send email
        Mail::to($this->invite_email)->send(new TeamInvitationMail($invitation, $signedUrl));

        $this->showInviteModal = false;
        Toaster::success(__('Einladung wurde erfolgreich gesendet!'));
    }

    public function render()
    {
        return view('livewire.teams');
    }
}

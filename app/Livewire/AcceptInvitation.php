<?php

namespace App\Livewire;

use App\Models\TeamInvitation;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.blank')]
class AcceptInvitation extends Component
{
    public string $token;

    public ?TeamInvitation $invitation = null;

    public bool $isRegistered = false;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->invitation = TeamInvitation::where('token', $token)->first();

        if (! $this->invitation) {
            abort(404, 'Einladung nicht gefunden oder bereits abgelaufen.');
        }

        // Store signed URL in session so user redirects back after login/registration
        session(['pending_invitation_url' => request()->fullUrl()]);

        $this->isRegistered = User::where('email', $this->invitation->email)->exists();
    }

    public function accept()
    {
        if (! auth()->check()) {
            Toaster::error(__('Bitte logge dich ein, um die Einladung anzunehmen.'));

            return redirect()->route('login');
        }

        $user = auth()->user();
        if (strcasecmp($user->email, $this->invitation->email) !== 0) {
            Toaster::error(__('Diese Einladung ist für eine andere E-Mail-Adresse bestimmt.'));

            return;
        }

        $team = $this->invitation->team;
        if (! $team->users()->where('user_id', $user->id)->exists()) {
            $team->users()->attach($user->id, ['role' => 'view']);
        }
        if (! $user->hasPermissionTo('team.view')) {
            $user->givePermissionTo('team.view');
        }

        $this->invitation->delete();
        session()->forget('pending_invitation_url');

        Toaster::success(__('Erfolgreich dem Team beigetreten!'));

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.accept-invitation');
    }
}

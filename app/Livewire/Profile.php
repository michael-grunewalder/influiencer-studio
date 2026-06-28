<?php

namespace App\Livewire;

use App\Models\Team;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class Profile extends Component
{
    use WithFileUploads;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public $uploaded_avatar;

    public ?string $default_team_id = null;

    public string $team_selection_mode = 'default';

    public function mount(): void
    {
        $user = auth()->user();
        $this->default_team_id = $user->default_team_id;
        $this->team_selection_mode = $user->team_selection_mode ?? 'default';
    }

    #[Computed]
    public function teams()
    {
        $user = auth()->user();
        if ($user->can('team.view-all')) {
            return Team::all();
        }

        return $user->teams()->get();
    }

    public function updateTeamSettings(): void
    {
        $user = auth()->user();

        $this->validate([
            'team_selection_mode' => 'required|in:default,last_used',
            'default_team_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
                        $isMember = $user->can('team.view-all') || $user->teams()->where('teams.id', $value)->exists();
                        if (! $isMember) {
                            $fail(__('Du musst Mitglied des ausgewählten Teams sein.'));
                        }
                    }
                },
            ],
        ]);

        $user->update([
            'team_selection_mode' => $this->team_selection_mode,
            'default_team_id' => $this->default_team_id ?: null,
        ]);

        Toaster::success(__('Teameinstellungen erfolgreich aktualisiert.'));
    }

    public function updatePassword()
    {
        if (empty($this->current_password)) {
            return;
        }

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        Toaster::success(__('Passwort erfolgreich aktualisiert.'));
    }

    public function updatedUploadedAvatar(): void
    {
        $this->validate([
            'uploaded_avatar' => 'image|max:5120',
        ]);

        $path = $this->uploaded_avatar->store('avatars', 'public');

        auth()->user()->update([
            'avatar' => '/storage/'.$path,
        ]);

        Toaster::success(__('Profilbild erfolgreich aktualisiert!'));
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.profile', [
            'user' => auth()->user(),
        ]);
    }
}

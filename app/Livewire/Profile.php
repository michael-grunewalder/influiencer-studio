<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
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
